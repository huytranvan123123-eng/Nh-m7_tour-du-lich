<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

require_once __DIR__ . '/../functions/db_connection.php';
$conn = getDbConnection();

// --- DEBUG START: hiển thị thông tin kết nối / trạng thái bảng (XÓA SAU KHI FIX) ---
echo '<div style="position:fixed;right:10px;top:10px;z-index:9999;padding:10px;background:#fff;border:1px solid #ddd;font-size:13px;max-width:420px">';
echo '<strong>DEBUG DB</strong><br>';
if (!$conn) {
    echo 'No DB connection.<br>';
} else {
    if (isset($conn->connect_errno) && $conn->connect_errno) {
        echo 'Connect error: ' . htmlspecialchars($conn->connect_error) . '<br>';
    } else {
        echo 'Connected OK<br>';
        // kiểm tra bảng users tồn tại
        $q = $conn->query("SHOW TABLES LIKE 'users'");
        if ($q && $q->num_rows > 0) {
            echo "Table 'users' exists<br>";
            // đếm số user
            $r = $conn->query("SELECT COUNT(*) AS c FROM users");
            if ($r) {
                $c = $r->fetch_assoc()['c'];
                echo 'Users count: ' . intval($c) . '<br>';
            } else {
                echo 'Count query error: ' . htmlspecialchars($conn->error) . '<br>';
            }
        } else {
            echo "Table 'users' NOT found (check DB name / prefix).<br>";
        }
    }
}
echo '</div>';
// --- DEBUG END ---
 
// tạo CSRF token nếu chưa có
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$currentId = $_SESSION['user_id'] ?? null;
$currentUser = $_SESSION['username'] ?? null;

// Thay truy vấn lấy user (bỏ cột balance/created_at nếu DB không có)
$users = [];
$res = $conn->query("SELECT id, username, email, role FROM users ORDER BY id DESC");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        // đảm bảo có key 'balance' và 'created_at' để giao diện không lỗi
        if (!isset($row['balance'])) $row['balance'] = 0;
        if (!isset($row['created_at'])) $row['created_at'] = '-';
        $users[] = $row;
    }
} else {
    // nếu truy vấn lỗi, hiển thị lỗi (tạm)
    echo '<div style="color:red;padding:10px">Query error: ' . htmlspecialchars($conn->error) . '</div>';
}
$conn->close();
?>
<!doctype html>
<html lang="vi">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Quản lý người dùng</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
</head>
<body class="p-4">
<div class="container" style="max-width:1100px">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h4><i class="fa-solid fa-users"></i> Quản lý người dùng</h4>
    <div>
      <a href="/btlt5_17_09_2/views/tour/list_tour.php" class="btn btn-secondary btn-sm">← Quay lại admin</a>
      <!-- ADDED: nút Thêm user -->
      <a href="/btlt5_17_09_2/views/add_user.php" class="btn btn-success btn-sm ms-2"><i class="fa-solid fa-user-plus"></i> Thêm user</a>
    </div>
  </div>

  <div id="alert-area"></div>

  <div class="card mb-4">
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-striped align-middle" id="users-table">
          <thead>
            <tr>
              <th>#</th>
              <th>Username</th>
              <th>Email</th>
              <th>Role</th>
              <th>Số dư</th>
              <th>Ngày đăng ký</th>
              <th>Hành động</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($users as $u): ?>
              <?php
                $isSelf = ($currentId && $currentId == $u['id']) || ($currentUser && $currentUser === $u['username']);
              ?>
              <tr id="user-row-<?= htmlspecialchars($u['id']) ?>">
                <td><?= htmlspecialchars($u['id']) ?></td>
                <td><?= htmlspecialchars($u['username']) ?></td>
                <td><?= htmlspecialchars($u['email'] ?? '-') ?></td>
                <td><?= htmlspecialchars($u['role']) ?></td>
                <td><?= number_format($u['balance'],0,',','.') ?>đ</td>
                <td><?= htmlspecialchars($u['created_at']) ?></td>
                <td>
                  <a href="edit_user.php?id=<?= $u['id'] ?>" class="btn btn-outline-primary btn-sm">Sửa</a>

                  <button
                    class="btn btn-danger btn-sm btn-delete-user"
                    data-user-id="<?= $u['id'] ?>"
                    data-username="<?= htmlspecialchars($u['username'], ENT_QUOTES) ?>"
                    <?= $isSelf ? 'disabled title="Không thể xóa chính bạn"' : '' ?>>
                    <i class="fa-solid fa-trash"></i> Xóa
                  </button>
                </td>
              </tr>
            <?php endforeach; ?>
            <?php if (empty($users)): ?>
              <tr><td colspan="7" class="text-center text-muted">Chưa có user nào</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- simple alert template -->
<div id="toast-template" style="display:none">
  <div class="position-fixed top-0 end-0 p-3" style="z-index: 1080;">
    <div id="liveToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
      <div class="toast-header">
        <strong class="me-auto">Thông báo</strong>
        <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
      </div>
      <div class="toast-body"></div>
    </div>
  </div>
</div>

<!-- ADD: Edit user modal -->
<div class="modal fade" id="editUserModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-sm">
    <div class="modal-content">
      <form id="editUserForm">
        <div class="modal-header">
          <h5 class="modal-title">Sửa user</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="user_id" id="edit_user_id">
          <div class="mb-2">
            <label class="form-label">Username</label>
            <input class="form-control" name="username" id="edit_username" required>
          </div>
          <div class="mb-2">
            <label class="form-label">Email</label>
            <input class="form-control" name="email" id="edit_email" type="email">
          </div>
          <div class="mb-2">
            <label class="form-label">Role</label>
            <select class="form-select" name="role" id="edit_role">
              <option value="user">user</option>
              <option value="admin">admin</option>
            </select>
          </div>
          <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Hủy</button>
          <button type="submit" class="btn btn-primary btn-sm">Lưu</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
const csrfToken = "<?= htmlspecialchars($_SESSION['csrf_token']) ?>";

function showToast(message, ok = true) {
  const container = document.createElement('div');
  container.className = 'position-fixed top-0 end-0 p-3';
  container.style.zIndex = 1080;
  container.innerHTML = `
    <div class="toast align-items-center text-bg-${ok ? 'success' : 'danger'} border-0 show" role="alert" aria-live="assertive" aria-atomic="true">
      <div class="d-flex">
        <div class="toast-body">${message}</div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
      </div>
    </div>
  `;
  document.body.appendChild(container);
  setTimeout(()=> container.remove(), 3500);
}

document.addEventListener('click', function(e){
  const btn = e.target.closest('.btn-delete-user');
  if (!btn) return;
  const userId = btn.getAttribute('data-user-id');
  const username = btn.getAttribute('data-username');
  if (!userId) return;
  if (!confirm(`Xác nhận xóa user "${username}" ?`)) return;

  btn.disabled = true;
  fetch('/btlt5_17_09_2/handle/delete_user.php', {
    method: 'POST',
    body: new URLSearchParams({
      user_id: userId,
      csrf_token: csrfToken
    }),
    headers: {
      'X-Requested-With': 'XMLHttpRequest'
    }
  }).then(r => r.json().catch(()=>({ok:false, message:'Không nhận được phản hồi từ server'})))
    .then(data => {
      if (data.ok) {
        // remove row
        const row = document.getElementById('user-row-' + userId);
        if (row) row.remove();
        showToast(data.message || 'Xóa thành công', true);
      } else {
        showToast(data.message || 'Xóa thất bại', false);
        btn.disabled = false;
      }
    })
    .catch(err => {
      console.error(err);
      showToast('Lỗi kết nối', false);
      btn.disabled = false;
    });
});

// Mở modal sửa khi bấm Sửa
document.querySelectorAll('a[href^="edit_user.php"], a.btn-edit-user').forEach(a=>{
  a.addEventListener('click', function(e){
    e.preventDefault();
    // lấy id từ href hoặc data attribute
    let id = null;
    const href = this.getAttribute('href') || '';
    const m = href.match(/[?&]id=(\d+)/);
    if (m) id = m[1];
    // hoặc data-user-id
    if(!id && this.dataset && this.dataset.userId) id = this.dataset.userId;
    if(!id) return alert('Không xác định user id');
    // lấy dữ liệu hàng hiện có từ DOM
    const row = document.getElementById('user-row-'+id);
    if (!row) return alert('Không tìm thấy hàng user');
    const cols = row.querySelectorAll('td');
    // mapping: assuming col order: id,username,email,role,...
    const username = cols[1]?.innerText.trim() || '';
    const email = cols[2]?.innerText.trim() || '';
    const role = cols[3]?.innerText.trim() || 'user';

    document.getElementById('edit_user_id').value = id;
    document.getElementById('edit_username').value = username;
    document.getElementById('edit_email').value = (email === '-') ? '' : email;
    document.getElementById('edit_role').value = role;

    const modal = new bootstrap.Modal(document.getElementById('editUserModal'));
    modal.show();
  });
});

// Xử lý submit sửa (AJAX)
document.getElementById('editUserForm').addEventListener('submit', function(e){
  e.preventDefault();
  const form = e.target;
  const data = new URLSearchParams(new FormData(form));
  fetch('/btlt5_17_09_2/handle/update_user.php', {
    method: 'POST',
    body: data,
    headers: {'X-Requested-With': 'XMLHttpRequest'}
  }).then(r=>r.json()).then(json=>{
    if (json.ok) {
      // update row in table
      const id = document.getElementById('edit_user_id').value;
      const row = document.getElementById('user-row-'+id);
      if (row) {
        row.querySelectorAll('td')[1].innerText = json.data.username;
        row.querySelectorAll('td')[2].innerText = json.data.email || '-';
        row.querySelectorAll('td')[3].innerText = json.data.role;
      }
      bootstrap.Modal.getInstance(document.getElementById('editUserModal')).hide();
      // toast
      (function(){ const t=document.createElement('div'); t.className='position-fixed top-0 end-0 p-3'; t.style.zIndex=1080; t.innerHTML=`<div class="toast align-items-center text-bg-success border-0 show"><div class="d-flex"><div class="toast-body">${json.message}</div><button class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div></div>`; document.body.appendChild(t); setTimeout(()=>t.remove(),3000); })();
    } else {
      alert(json.message || 'Cập nhật thất bại');
    }
  }).catch(err=>{
    console.error(err); alert('Lỗi kết nối');
  });
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>