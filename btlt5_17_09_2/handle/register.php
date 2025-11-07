<?php
// Clean, simple shim to the proper register handler.
// This replaces the broken file that had duplicated "<?php" and caused a parse error.
session_start();

// If form submitted via POST, include the proper processing script so POST data is preserved.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Safe include of the main processor
    require_once __DIR__ . '/register_process.php';
    exit;
}

// Otherwise redirect back to the register form
header('Location: ../register.php');
exit;
```