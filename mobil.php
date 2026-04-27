<?php
session_start();
require_once 'config.php';
cekLogin();

$pesan = '';
$mode  = $_GET['mode'] ?? 'list';  // list | tambah | edit
$id    = intval($_GET['id'] ?? 0);

// hapus
if ($mode === 'hapus' && $id) {
    $conn->query("DELETE FROM mobil WHERE id = $id");
    header('Location: mobil.php?pesan=hapus_ok');
    exit;
}

// save
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_mobil  = trim($_POST['nama_mobil']);
    $merek       = trim($_POST['merek']);
    $tahun       = intval($_POST['tahun']);
    $warna       = trim($_POST['warna']);
    $plat_nomor  = strtoupper(trim($_POST['plat_nomor']));
    $harga_sewa  = intval(str_replace(['.', ','], '', $_POST['harga_sewa']));
    $status      = $_POST['status'];

    if ($_POST['id']) {
        $eid = intval($_POST['id']);
        $stmt = $conn->prepare("UPDATE mobil SET nama_mobil=?,merek=?,tahun=?,warna=?,plat_nomor=?,harga_sewa=?,status=? WHERE id=?");
        $stmt->bind_param('ssisissi', $nama_mobil,$merek,$tahun,$warna,$plat_nomor,$harga_sewa,$status,$eid);
        $stmt->execute();
        header('Location: mobil.php?pesan=edit_ok');
    } else {
        $stmt = $conn->prepare("INSERT INTO mobil (nama_mobil,merek,tahun,warna,plat_nomor,harga_sewa,status) VALUES (?,?,?,?,?,?,?)");
        $stmt->bind_param('ssissis', $nama_mobil,$merek,$tahun,$warna,$plat_nomor,$harga_sewa,$status);
        $stmt->execute();
        header('Location: mobil.php?pesan=tambah_ok');
    }
    exit;
}

// edit
$data_edit = [];
if ($mode === 'edit' && $id) {
    $data_edit = $conn->query("SELECT * FROM mobil WHERE id=$id")->fetch_assoc() ?? [];
}

// data semua mobil
$search = trim($_GET['q'] ?? '');
$sql = "SELECT * FROM mobil";
if ($search) {
    $s = $conn->real_escape_string($search);
    $sql .= " WHERE nama_mobil LIKE '%$s%' OR merek LIKE '%$s%' OR plat_nomor LIKE '%$s%'";
}
$sql .= " ORDER BY id DESC";
$mobil_list = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Data Mobil — DriveNow</title>
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
  .form-control, .form-select { border-radius:8px; font-size:14px; }
  .form-label { font-size:12px; font-weight:500; text-transform:uppercase; letter-spacing:.5px; color:#6c757d; }
  .badge-status { font-size:11px; padding:4px 10px; border-radius:20px; font-weight:500; }
</style>
</head>
<body>
<?php require_once 'includes/navbar.php'; ?>

<div class="container-fluid px-4 py-4">

  <!-- Pesan sukses -->
  <?php
    $pesan_map = ['tambah_ok'=>['success','Mobil berhasil ditambahkan!'], 'edit_ok'=>['success','Data mobil berhasil diperbarui!'], 'hapus_ok'=>['danger','Mobil berhasil dihapus!']];
    $pk = $_GET['pesan'] ?? '';
    if (isset($pesan_map[$pk])):
      [$type, $msg] = $pesan_map[$pk];
  ?>
  <div class="alert alert-<?= $type ?> alert-dismissible fade show" style="border-radius:10px;font-size:13px;">
    <i class="bi bi-check-circle me-1"></i> <?= $msg ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
  <?php endif; ?>

  <div class="d-flex justify-content-between align-items-center mb-3">
    <div>
      <h4 class="page-title mb-0">Data Mobil</h4>
      <p class="text-muted mb-0" style="font-size:13px;">Kelola armada kendaraan rental</p>
    </div>
    <a href="mobil.php?mode=tambah" class="btn btn-primary-custom px-3 py-2">
      <i class="bi bi-plus-lg me-1"></i> Tambah Mobil
    </a>
  </div>

  <!-- FORM TAMBAH / EDIT -->
  <?php if ($mode === 'tambah' || $mode === 'edit'): ?>
  <div class="card card-main shadow-sm mb-4">
    <div class="card-body p-4">
      <h6 class="page-title mb-3" style="font-size:16px;">
        <?= $mode === 'edit' ? 'Edit Data Mobil' : 'Tambah Mobil Baru' ?>
      </h6>
      <form method="POST">
        <input type="hidden" name="id" value="<?= $data_edit['id'] ?? '' ?>">
        <div class="row g-3">
          <div class="col-md-4">
            <label class="form-label">Nama Mobil</label>
            <input type="text" name="nama_mobil" class="form-control" placeholder="cth: Avanza" required value="<?= htmlspecialchars($data_edit['nama_mobil'] ?? '') ?>">
          </div>
          <div class="col-md-4">
            <label class="form-label">Merek</label>
            <input type="text" name="merek" class="form-control" placeholder="cth: Toyota" required value="<?= htmlspecialchars($data_edit['merek'] ?? '') ?>">
          </div>
          <div class="col-md-2">
            <label class="form-label">Tahun</label>
            <input type="number" name="tahun" class="form-control" min="2000" max="2030" required value="<?= htmlspecialchars($data_edit['tahun'] ?? date('Y')) ?>">
          </div>
          <div class="col-md-2">
            <label class="form-label">Warna</label>
            <input type="text" name="warna" class="form-control" placeholder="cth: Putih" value="<?= htmlspecialchars($data_edit['warna'] ?? '') ?>">
          </div>
          <div class="col-md-3">
            <label class="form-label">Plat Nomor</label>
            <input type="text" name="plat_nomor" class="form-control" placeholder="cth: AB 1234 CD" required value="<?= htmlspecialchars($data_edit['plat_nomor'] ?? '') ?>">
          </div>
          <div class="col-md-4">
            <label class="form-label">Harga Sewa / Hari (Rp)</label>
            <input type="number" name="harga_sewa" class="form-control" placeholder="cth: 350000" required value="<?= htmlspecialchars($data_edit['harga_sewa'] ?? '') ?>">
          </div>
          <div class="col-md-3">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
              <option value="tersedia"   <?= ($data_edit['status']??'')==='tersedia'  ?'selected':'' ?>>Tersedia</option>
              <option value="disewa"     <?= ($data_edit['status']??'')==='disewa'    ?'selected':'' ?>>Disewa</option>
              <option value="perawatan"  <?= ($data_edit['status']??'')==='perawatan' ?'selected':'' ?>>Perawatan</option>
            </select>
          </div>
          <div class="col-12 d-flex gap-2">
            <button type="submit" class="btn btn-primary-custom px-4">
              <i class="bi bi-save me-1"></i> Simpan
            </button>
            <a href="mobil.php" class="btn btn-outline-secondary px-4" style="border-radius:8px;font-size:13px;">Batal</a>
          </div>
        </div>
      </form>
    </div>
  </div>
  <?php endif; ?>

  <!-- TABEL DATA -->
  <div class="card card-main shadow-sm">
    <div class="card-body p-3">
      <!-- Search -->
      <form method="GET" class="mb-3">
        <div class="input-group" style="max-width:360px;">
          <input type="text" name="q" class="form-control" placeholder="Cari mobil, merek, plat..." value="<?= htmlspecialchars($search) ?>" style="border-radius:8px 0 0 8px;">
          <button class="btn btn-primary-custom" type="submit" style="border-radius:0 8px 8px 0;">
            <i class="bi bi-search"></i>
          </button>
        </div>
      </form>

      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="font-size:13px;">
          <thead class="tbl-head">
            <tr>
              <th class="py-2 px-3">#</th>
              <th>Nama Mobil</th>
              <th>Merek</th>
              <th>Tahun</th>
              <th>Plat Nomor</th>
              <th>Harga Sewa/Hari</th>
              <th class="text-center">Status</th>
              <th class="text-center">Aksi</th>
            </tr>
          </thead>
          <tbody>
          <?php $no=1; while ($row = $mobil_list->fetch_assoc()): ?>
            <tr>
              <td class="px-3"><?= $no++ ?></td>
              <td><?= htmlspecialchars($row['nama_mobil']) ?></td>
              <td><?= htmlspecialchars($row['merek']) ?></td>
              <td><?= $row['tahun'] ?></td>
              <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($row['plat_nomor']) ?></span></td>
              <td><?= rupiah($row['harga_sewa']) ?></td>
              <td class="text-center">
                <?php
                  $badge = ['tersedia'=>'success', 'disewa'=>'warning', 'perawatan'=>'danger'];
                  $b = $badge[$row['status']] ?? 'secondary';
                ?>
                <span class="badge badge-status bg-<?= $b ?>"><?= ucfirst($row['status']) ?></span>
              </td>
              <td class="text-center">
                <a href="mobil.php?mode=edit&id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-primary me-1" style="border-radius:6px;font-size:12px;">
                  <i class="bi bi-pencil"></i>
                </a>
                <a href="mobil.php?mode=hapus&id=<?= $row['id'] ?>"
                   class="btn btn-sm btn-outline-danger" style="border-radius:6px;font-size:12px;"
                   onclick="return confirm('Hapus mobil ini?')">
                  <i class="bi bi-trash"></i>
                </a>
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
