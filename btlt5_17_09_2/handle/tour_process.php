<?php
session_start();
require_once __DIR__ . '/../functions/db_connection.php';

// chỉ admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../views/tour/list_tour.php');
    exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../views/tour/list_tour.php');
    exit;
}

// CSRF optional check if form sends it
if (isset($_POST['csrf_token'])) {
    $token = $_POST['csrf_token'];
    if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
        header('Location: ../views/tour/add_tour.php?msg=csrf');
        exit;
    }
}

// map form inputs -> expected column names
$map = [
    'name' => 'name',
    'price' => 'price',
    'location' => 'location',
    'start_date' => 'start_date',
    'end_date' => 'end_date',
    'description' => 'description',
    'image' => 'image'
];

$conn = getDbConnection();
if (!$conn) {
    header('Location: ../views/tour/add_tour.php?msg=dberror');
    exit;
}

// Get actual columns and types for "tours" table
$dbName = $conn->real_escape_string($conn->query("SELECT DATABASE()")->fetch_row()[0] ?? '');
$colsRes = $conn->query("SELECT COLUMN_NAME, DATA_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = '{$dbName}' AND TABLE_NAME = 'tours'");
$cols = [];
if ($colsRes) {
    while ($c = $colsRes->fetch_assoc()) {
        $cols[$c['COLUMN_NAME']] = $c['DATA_TYPE']; // e.g. varchar, int, decimal, datetime
    }
} else {
    // fallback: try SHOW COLUMNS
    $q = $conn->query("SHOW COLUMNS FROM tours");
    if ($q) {
        while ($r = $q->fetch_assoc()) {
            // parse type to get basic type name
            $type = $r['Type'];
            if (preg_match('/^([a-zA-Z]+)/', $type, $m)) $t = $m[1];
            else $t = 'varchar';
            $cols[$r['Field']] = $t;
        }
    }
}

// Determine which mapped columns exist
$insertCols = [];
foreach ($map as $formKey => $colName) {
    if (array_key_exists($colName, $cols)) $insertCols[$colName] = $formKey;
}

if (empty($insertCols)) {
    $conn->close();
    header('Location: ../views/tour/add_tour.php?msg=nocols');
    exit;
}

// Handle image upload only if 'image' column exists
$imageValue = null;
if (isset($insertCols['image']) && isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $tmp = $_FILES['image']['tmp_name'];
    $orig = basename($_FILES['image']['name']);
    $ext = strtolower(pathinfo($orig, PATHINFO_EXTENSION));
    $allowed = ['jpg','jpeg','png','gif','webp'];
    if (in_array($ext, $allowed)) {
        $uploadDir = __DIR__ . '/../uploads';
        if (!is_dir($uploadDir)) @mkdir($uploadDir, 0777, true);
        $safeBase = preg_replace('/[^a-z0-9_\-]/i','_', pathinfo($orig, PATHINFO_FILENAME));
        $imageValue = time() . '_' . substr($safeBase,0,40) . '.' . $ext;
        $dest = $uploadDir . '/' . $imageValue;
        if (!move_uploaded_file($tmp, $dest)) {
            // failed; set null to insert default
            $imageValue = null;
        }
    }
}

// Build insert columns and params in consistent order
$columns = [];
$placeholders = [];
$values = [];
$types = ''; // bind types string

foreach ($insertCols as $colName => $formKey) {
    // skip image here; handle separately to use $imageValue
    if ($colName === 'image') {
        $columns[] = $colName;
        $placeholders[] = '?';
        $values[] = ($imageValue !== null ? $imageValue : '');
        // image treated as string
        $types .= 's';
        continue;
    }

    // get posted value (or default)
    $val = null;
    if (isset($_POST[$formKey])) $val = trim($_POST[$formKey]);
    // If column type suggests numeric, coerce to number
    $ctype = strtolower($cols[$colName] ?? '');
    if (in_array($ctype, ['int','tinyint','smallint','mediumint','bigint'])) {
        $types .= 'i';
        $values[] = ($val === '' ? 0 : intval($val));
    } elseif (in_array($ctype, ['decimal','float','double'])) {
        $types .= 'd';
        $values[] = ($val === '' ? 0.0 : floatval($val));
    } else {
        // strings, dates, text, datetime etc.
        $types .= 's';
        $values[] = ($val === null ? '' : $val);
    }
    $columns[] = $colName;
    $placeholders[] = '?';
}

// Build SQL
$colList = implode(', ', array_map(function($c){ return "`$c`"; }, $columns));
$placeList = implode(', ', $placeholders);
$sql = "INSERT INTO `tours` ({$colList}) VALUES ({$placeList})";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    // prepare failed -> likely mismatched column names/types
    $err = $conn->error;
    $conn->close();
    header('Location: ../views/tour/add_tour.php?msg=prepareerr');
    exit;
}

// bind params dynamically
if (!empty($values)) {
    // create refs
    $bindParams = [];
    $bindParams[] = $types;
    foreach ($values as $k => $v) $bindParams[] = &$values[$k];
    call_user_func_array([$stmt, 'bind_param'], $bindParams);
}

$ok = $stmt->execute();
if (!$ok) {
    // error during insert
    $stmt->close();
    $conn->close();
    header('Location: ../views/tour/add_tour.php?msg=execerr');
    exit;
}

$stmt->close();
$conn->close();
header('Location: ../views/tour/list_tour.php?created=1');
exit;
