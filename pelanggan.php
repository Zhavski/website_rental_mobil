<?php
session_start();
require_once 'config.php';
cekLogin();

$mode = $_GET['mode'] ?? 'list';
$id   = intval($_GET['id'] ?? 0);

// hapus
if ($mode === 'hapus' && $id) {
    $conn->query("DELETE FROM pelanggan WHERE id=$id");
    header('Location: pelanggan.php?pesan=hapus_ok');
    exit;
}

// save
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama    = trim($_POST['nama']);
    $no_ktp  = trim($_POST['no_ktp']);
    $no_hp   = trim($_POST['no_hp']);
    $alamat  = trim($_POST['alamat']);

    if ($_POST['id']) {
        $eid = intval($_POST['id']);
        $stmt = $conn->prepare("UPDATE pelanggan SET nama=?,no_ktp=?,no_hp=?,alamat=? WHERE id=?");
        $stmt->bind_param('ssssi', $nama,$no_ktp,$no_hp,$alamat,$eid);
        $stmt->execute();
        header('Location: pelanggan.php?pesan=edit_ok');
    } else {
        $stmt = $conn->prepare("INSERT INTO pelanggan (nama,no_ktp,no_hp,alamat) VALUES (?,?,?,?)");
        $stmt->bind_param('ssss', $nama,$no_ktp,$no_hp,$alamat);
        $stmt->execute();
        header('Location: pelanggan.php?pesan=tambah_ok');
    }
    exit;
}

$data_edit = [];
if ($mode === 'edit' && $id) {
    $data_edit = $conn->query("SELECT * FROM pelanggan WHERE id=$id")->fetch_assoc() ?? [];
}

$search = trim($_GET['q'] ?? '');
$sql = "SELECT * FROM pelanggan";
if ($search) {
    $s = $conn->real_escape_string($search);
    $sql .= " WHERE nama LIKE '%$s%' OR no_ktp LIKE '%$s%' OR no_hp LIKE '%$s%'";
}
$sql .= " ORDER BY id DESC";
$list = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Pelanggan — DriveNow</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;600;700&family=DM+Sans:wght@400;500&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
<style>
  body { font-family:'DM Sans',sans-serif; background:#f0f4f8; }
  .page-title { font-family:'Sora',sans-serif; font-weight:700; color:#0f2d4a; }
  .card-main { border-radius:14px; border:none; }
  .tbl-head { background:#0f2d4a; color:#fff; font-size:13px; }
  .btn-primary-custom { background:#0f2d4a; color:#fff; border:none; border-radius:8px; font-size:13px; }
  .btn-primary-custom:hover { background:#1a4270; color:#fff; }
  .form-control { border-radius:8px; font-size:14px; }
  .form-label { font-size:12px; font-weight:500; text-transform:uppercase; letter-spacing:.5px; color:#6c757d; }
  .avatar { width:36px; height:36px; border-radius:50%; background:#e8f4fd; display:inline-flex; align-items:center; justify-content:center; font-weight:600; font-size:13px; color:#0d6efd; }
</style>
</head>
<body>
<?php require_once 'includes/navbar.php'; ?>

<div class="container-fluid px-4 py-4">

  <?php
    $pesan_map = ['tambah_ok'=>['success','Pelanggan berhasil ditambahkan!'], 'edit_ok'=>['success','Data pelanggan berhasil diperbarui!'], 'hapus_ok'=>['danger','Pelanggan berhasil dihapus!']];
    $pk = $_GET['pesan'] ?? '';
    if (isset($pesan_map[$pk])): [$type, $msg] = $pesan_map[$pk]; ?>
  <div class="alert alert-<?= $type ?> alert-dismissible fade show" style="border-radius:10px;font-size:13px;">
    <i class="bi bi-check-circle me-1"></i> <?= $msg ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
  <?php endif; ?>

  <div class="d-flex justify-content-between align-items-center mb-3">
    <div>
      <h4 class="page-title mb-0">Data Pelanggan</h4>
      <p class="text-muted mb-0" style="font-size:13px;">Kelola data pelanggan rental</p>
    </div>
    <a href="pelanggan.php?mode=tambah" class="btn btn-primary-custom px-3 py-2">
      <i class="bi bi-person-plus me-1"></i> Tambah Pelanggan
    </a>
  </div>

  <?php if ($mode === 'tambah' || $mode === 'edit'): ?>
  <div class="card card-main shadow-sm mb-4">
    <div class="card-body p-4">
      <h6 class="page-title mb-3" style="font-size:16px;">
        <?= $mode === 'edit' ? 'Edit Data Pelanggan' : 'Tambah Pelanggan Baru' ?>
      </h6>
      <form method="POST">
        <input type="hidden" name="id" value="<?= $data_edit['id'] ?? '' ?>">
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">Nama Lengkap</label>
            <input type="text" name="nama" class="form-control" placeholder="Nama pelanggan" required value="<?= htmlspecialchars($data_edit['nama'] ?? '') ?>">
          </div>
          <div class="col-md-6">
            <label class="form-label">No. KTP</label>
            <input type="text" name="no_ktp" class="form-control" placeholder="16 digit NIK" maxlength="16" required value="<?= htmlspecialchars($data_edit['no_ktp'] ?? '') ?>">
          </div>
          <div class="col-md-4">
            <label class="form-label">No. HP</label>
            <input type="text" name="no_hp" class="form-control" placeholder="cth: 081234567890" required value="<?= htmlspecialchars($data_edit['no_hp'] ?? '') ?>">
          </div>
          <div class="col-md-8">
            <label class="form-label">Alamat</label>
            <input type="text" name="alamat" class="form-control" placeholder="Alamat lengkap" value="<?= htmlspecialchars($data_edit['alamat'] ?? '') ?>">
          </div>
          <div class="col-12 d-flex gap-2">
            <button type="submit" class="btn btn-primary-custom px-4"><i class="bi bi-save me-1"></i> Simpan</button>
            <a href="pelanggan.php" class="btn btn-outline-secondary px-4" style="border-radius:8px;font-size:13px;">Batal</a>
          </div>
        </div>
      </form>
    </div>
  </div>
  <?php endif; ?>

  <div class="card card-main shadow-sm">
    <div class="card-body p-3">
      <form method="GET" class="mb-3">
        <div class="input-group" style="max-width:360px;">
          <input type="text" name="q" class="form-control" placeholder="Cari nama, KTP, HP..." value="<?= htmlspecialchars($search) ?>" style="border-radius:8px 0 0 8px;">
          <button class="btn btn-primary-custom" type="submit" style="border-radius:0 8px 8px 0;"><i class="bi bi-search"></i></button>
        </div>
      </form>

      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="font-size:13px;">
          <thead class="tbl-head">
            <tr>
              <th class="py-2 px-3">#</th>
              <th>Nama</th>
              <th>No. KTP</th>
              <th>No. HP</th>
              <th>Alamat</th>
              <th class="text-center">Aksi</th>
            </tr>
          </thead>
          <tbody>
          <?php $no=1; while ($row = $list->fetch_assoc()): ?>
            <tr>
              <td class="px-3"><?= $no++ ?></td>
              <td>
                <div class="d-flex align-items-center gap-2">
                  <div class="avatar"><?= strtoupper(substr($row['nama'],0,1)) ?></div>
                  <?= htmlspecialchars($row['nama']) ?>
                </div>
              </td>
              <td><?= htmlspecialchars($row['no_ktp']) ?></td>
              <td><?= htmlspecialchars($row['no_hp']) ?></td>
              <td><?= htmlspecialchars($row['alamat'] ?? '-') ?></td>
              <td class="text-center">
                <a href="pelanggan.php?mode=edit&id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-primary me-1" style="border-radius:6px;font-size:12px;"><i class="bi bi-pencil"></i></a>
                <a href="pelanggan.php?mode=hapus&id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-danger" style="border-radius:6px;font-size:12px;" onclick="return confirm('Hapus pelanggan ini?')"><i class="bi bi-trash"></i></a>
              </td>
            </tr>
          <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
