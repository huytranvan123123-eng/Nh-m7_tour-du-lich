<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../index.php');
    exit;
}
?>
<!doctype html>
<html lang="vi">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Thêm nhiều Tour</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
.entry { border:1px dashed #e2e8f0; padding:15px; border-radius:8px; margin-bottom:12px; background:#fff; }
</style>
</head>
<body class="p-4">
<div class="container" style="max-width:1000px">
  <h4>Thêm nhiều Tour</h4>
  <p class="text-muted">Tối đa 10 tour/lượt. Điền thông tin và upload ảnh cho từng tour.</p>

  <form id="bulkForm" method="post" action="/btlt5_17_09_2/handle/tour_process.php" enctype="multipart/form-data">
    <input type="hidden" name="action" value="create_multi">

    <div id="entries">
      <div class="entry" data-index="0">
        <div class="d-flex justify-content-between">
          <strong>Tour #1</strong>
          <button type="button" class="btn btn-danger btn-sm btn-remove" onclick="removeEntry(this)" style="display:none">Xóa</button>
        </div>
        <div class="row mt-2 g-2">
          <div class="col-md-6">
            <input class="form-control" name="name[]" placeholder="Tên tour" required>
          </div>
          <div class="col-md-3">
            <input class="form-control" name="price[]" type="number" min="0" placeholder="Giá (VNĐ)" required>
          </div>
          <div class="col-md-3">
            <input class="form-control" name="location[]" placeholder="Địa điểm" required>
          </div>
          <div class="col-md-6 mt-2">
            <input class="form-control" name="start_date[]" type="date" required>
          </div>
          <div class="col-md-6 mt-2">
            <input class="form-control" name="end_date[]" type="date" required>
          </div>
          <div class="col-12 mt-2">
            <textarea class="form-control" name="description[]" rows="3" placeholder="Mô tả"></textarea>
          </div>
          <div class="col-md-6 mt-2">
            <label class="form-label">Ảnh (1 ảnh/tour)</label>
            <input class="form-control" type="file" name="image[]" accept="image/*">
          </div>
        </div>
      </div>
    </div>

    <div class="mt-3 d-flex gap-2">
      <button type="button" class="btn btn-outline-primary" id="addBtn">+ Thêm 1 Tour</button>
      <button type="submit" class="btn btn-success">Lưu tất cả</button>
      <span class="text-muted ms-3">Tối đa 10 tour/lần</span>
    </div>
  </form>
</div>

<script>
let maxEntries = 10;
document.getElementById('addBtn').addEventListener('click', function(){
  const container = document.getElementById('entries');
  const count = container.querySelectorAll('.entry').length;
  if(count >= maxEntries){ alert('Đã đạt giới hạn ' + maxEntries); return; }
  const clone = container.querySelector('.entry').cloneNode(true);
  const idx = count;
  clone.setAttribute('data-index', idx);
  // reset inputs
  clone.querySelectorAll('input, textarea').forEach(function(inp){
    if(inp.type === 'file') inp.value = '';
    else inp.value = '';
  });
  clone.querySelector('.btn-remove').style.display = 'inline-block';
  clone.querySelector('strong').innerText = 'Tour #' + (idx+1);
  container.appendChild(clone);
});

function removeEntry(btn){
  const e = btn.closest('.entry');
  e.remove();
  // reindex titles
  document.querySelectorAll('#entries .entry').forEach((el, i)=> el.querySelector('strong').innerText = 'Tour #' + (i+1));
}
</script>
</body>
</html>