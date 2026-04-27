<?php
session_start();
require_once 'config.php';
cekLogin();

$mode = $_GET['mode'] ?? 'list';
$id   = intval($_GET['id'] ?? 0);

// hapus
if ($mode === 'hapus' && $id) {
    // balikin status mobil jadi tersedia
    $row = $conn->query("SELECT id_mobil FROM transaksi WHERE id=$id")->fetch_assoc();
    if ($row) {
        $conn->query("UPDATE mobil SET status='tersedia' WHERE id={$row['id_mobil']}");
    }
    $conn->query("DELETE FROM transaksi WHERE id=$id");
    header('Location: transaksi.php?pesan=hapus_ok');
    exit;
}

// post status
if ($mode === 'selesai' && $id) {
    $row = $conn->query("SELECT id_mobil FROM transaksi WHERE id=$id")->fetch_assoc();
    if ($row) {
        $conn->query("UPDATE mobil SET status='tersedia' WHERE id={$row['id_mobil']}");
    }
    $conn->query("UPDATE transaksi SET status='selesai' WHERE id=$id");
    header('Location: transaksi.php?pesan=selesai_ok');
    exit;
}

// simpan
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_pelanggan = intval($_POST['id_pelanggan']);
    $id_mobil     = intval($_POST['id_mobil']);
    $tgl_mulai    = $_POST['tgl_mulai'];
    $tgl_selesai  = $_POST['tgl_selesai'];

    $dt1 = new DateTime($tgl_mulai);
    $dt2 = new DateTime($tgl_selesai);
    $total_hari = max(1, $dt2->diff($dt1)->days);

    $harga = $conn->query("SELECT harga_sewa FROM mobil WHERE id=$id_mobil")->fetch_assoc()['harga_sewa'] ?? 0;
    $total_bayar = $total_hari * $harga;

    $stmt = $conn->prepare("INSERT INTO transaksi (id_pelanggan,id_mobil,tgl_mulai,tgl_selesai,total_hari,total_bayar,status) VALUES (?,?,?,?,?,?,'aktif')");
    $stmt->bind_param('iissii', $id_pelanggan,$id_mobil,$tgl_mulai,$tgl_selesai,$total_hari,$total_bayar);
    $stmt->execute();

    // Set status mobil jadi disewa
    $conn->query("UPDATE mobil SET status='disewa' WHERE id=$id_mobil");

    header('Location: transaksi.php?pesan=tambah_ok');
    exit;
}

// form input data
$pelanggan_list = $conn->query("SELECT id, nama FROM pelanggan ORDER BY nama");
$mobil_tersedia = $conn->query("SELECT id, nama_mobil, merek, harga_sewa FROM mobil WHERE status='tersedia' ORDER BY nama_mobil");
$harga_mobil    = [];
$tmp = $conn->query("SELECT id, harga_sewa FROM mobil");
while ($r = $tmp->fetch_assoc()) $harga_mobil[$r['id']] = $r['harga_sewa'];

$search = trim($_GET['q'] ?? '');
$sql = "SELECT t.*, p.nama AS nama_pelanggan, m.nama_mobil, m.merek
        FROM transaksi t
        JOIN pelanggan p ON t.id_pelanggan=p.id
        JOIN mobil m ON t.id_mobil=m.id";
if ($search) {
    $s = $conn->real_escape_string($search);
    $sql .= " WHERE p.nama LIKE '%$s%' OR m.nama_mobil LIKE '%$s%'";
}
$sql .= " ORDER BY t.id DESC";
$list = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Transaksi — DriveNow</title>
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
  #preview-harga { background:#f0f8f0; border-radius:10px; padding:12px 16px; border:1px dashed #198754; }
</style>
</head>
<body>
<?php require_once 'includes/navbar.php'; ?>

<div class="container-fluid px-4 py-4">

  <?php
    $pesan_map = [
      'tambah_ok'  => ['success','Transaksi berhasil dicatat!'],
      'selesai_ok' => ['success','Transaksi ditandai selesai. Mobil kembali tersedia.'],
      'hapus_ok'   => ['danger', 'Transaksi berhasil dihapus!'],
    ];
    $pk = $_GET['pesan'] ?? '';
    if (isset($pesan_map[$pk])): [$type, $msg] = $pesan_map[$pk]; ?>
  <div class="alert alert-<?= $type ?> alert-dismissible fade show" style="border-radius:10px;font-size:13px;">
    <i class="bi bi-check-circle me-1"></i> <?= $msg ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
  <?php endif; ?>

  <div class="d-flex justify-content-between align-items-center mb-3">
    <div>
      <h4 class="page-title mb-0">Transaksi Sewa</h4>
      <p class="text-muted mb-0" style="font-size:13px;">Catat dan pantau penyewaan mobil</p>
    </div>
    <a href="transaksi.php?mode=tambah" class="btn btn-primary-custom px-3 py-2">
      <i class="bi bi-plus-lg me-1"></i> Catat Transaksi Baru
    </a>
  </div>

  <!-- FORM TAMBAH TRANSAKSI -->
  <?php if ($mode === 'tambah'): ?>
  <div class="card card-main shadow-sm mb-4">
    <div class="card-body p-4">
      <h6 class="page-title mb-3" style="font-size:16px;">Transaksi Sewa Baru</h6>
      <form method="POST" id="frmTransaksi">
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">Pelanggan</label>
            <select name="id_pelanggan" class="form-select" required>
              <option value="">-- Pilih Pelanggan --</option>
              <?php while ($p = $pelanggan_list->fetch_assoc()): ?>
              <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['nama']) ?></option>
              <?php endwhile; ?>
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label">Mobil (Tersedia)</label>
            <select name="id_mobil" class="form-select" id="selMobil" required>
              <option value="">-- Pilih Mobil --</option>
              <?php while ($m = $mobil_tersedia->fetch_assoc()): ?>
              <option value="<?= $m['id'] ?>" data-harga="<?= $m['harga_sewa'] ?>">
                <?= htmlspecialchars($m['merek'].' '.$m['nama_mobil']) ?> — <?= rupiah($m['harga_sewa']) ?>/hari
              </option>
              <?php endwhile; ?>
            </select>
          </div>
          <div class="col-md-4">
            <label class="form-label">Tanggal Mulai</label>
            <input type="date" name="tgl_mulai" id="tglMulai" class="form-control" required value="<?= date('Y-m-d') ?>">
          </div>
          <div class="col-md-4">
            <label class="form-label">Tanggal Selesai</label>
            <input type="date" name="tgl_selesai" id="tglSelesai" class="form-control" required value="<?= date('Y-m-d', strtotime('+1 day')) ?>">
          </div>
          <div class="col-md-4 d-flex align-items-end">
            <div id="preview-harga" class="w-100">
              <div style="font-size:11px;color:#6c757d;text-transform:uppercase;letter-spacing:.5px;">Estimasi Total</div>
              <div id="totalHarga" style="font-family:'Sora',sans-serif;font-weight:700;font-size:20px;color:#198754;">Rp 0</div>
              <div id="totalHari" style="font-size:11px;color:#6c757d;">0 hari × Rp 0/hari</div>
            </div>
          </div>
          <div class="col-12 d-flex gap-2">
            <button type="submit" class="btn btn-primary-custom px-4"><i class="bi bi-save me-1"></i> Simpan Transaksi</button>
            <a href="transaksi.php" class="btn btn-outline-secondary px-4" style="border-radius:8px;font-size:13px;">Batal</a>
          </div>
        </div>
      </form>
    </div>
  </div>
  <?php endif; ?>

  <!-- TABEL TRANSAKSI -->
  <div class="card card-main shadow-sm">
    <div class="card-body p-3">
      <form method="GET" class="mb-3">
        <div class="input-group" style="max-width:360px;">
          <input type="text" name="q" class="form-control" placeholder="Cari pelanggan atau mobil..." value="<?= htmlspecialchars($search) ?>" style="border-radius:8px 0 0 8px;">
          <button class="btn btn-primary-custom" type="submit" style="border-radius:0 8px 8px 0;"><i class="bi bi-search"></i></button>
        </div>
      </form>

      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="font-size:13px;">
          <thead class="tbl-head">
            <tr>
              <th class="py-2 px-3">#</th>
              <th>Pelanggan</th>
              <th>Mobil</th>
              <th>Periode</th>
              <th>Hari</th>
              <th>Total Bayar</th>
              <th class="text-center">Status</th>
              <th class="text-center">Aksi</th>
            </tr>
          </thead>
          <tbody>
          <?php $no=1; while ($row = $list->fetch_assoc()): ?>
            <tr>
              <td class="px-3"><?= $no++ ?></td>
              <td><?= htmlspecialchars($row['nama_pelanggan']) ?></td>
              <td><?= htmlspecialchars($row['merek'].' '.$row['nama_mobil']) ?></td>
              <td><?= date('d/m/y', strtotime($row['tgl_mulai'])) ?> – <?= date('d/m/y', strtotime($row['tgl_selesai'])) ?></td>
              <td><?= $row['total_hari'] ?> hari</td>
              <td><?= rupiah($row['total_bayar']) ?></td>
              <td class="text-center">
                <?php $badge=['aktif'=>'warning','selesai'=>'success','dibatalkan'=>'danger']; ?>
                <span class="badge badge-status bg-<?= $badge[$row['status']]??'secondary' ?>"><?= ucfirst($row['status']) ?></span>
              </td>
              <td class="text-center" style="white-space:nowrap;">
                <?php if ($row['status'] === 'aktif'): ?>
                <a href="transaksi.php?mode=selesai&id=<?= $row['id'] ?>"
                   class="btn btn-sm btn-outline-success me-1" style="border-radius:6px;font-size:12px;"
                   onclick="return confirm('Tandai transaksi ini selesai?')" title="Selesaikan">
                  <i class="bi bi-check-lg"></i>
                </a>
                <?php endif; ?>
                <a href="transaksi.php?mode=hapus&id=<?= $row['id'] ?>"
                   class="btn btn-sm btn-outline-danger" style="border-radius:6px;font-size:12px;"
                   onclick="return confirm('Hapus transaksi ini?')" title="Hapus">
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
<script>
// Kalkulasi otomatis total harga
const selMobil   = document.getElementById('selMobil');
const tglMulai   = document.getElementById('tglMulai');
const tglSelesai = document.getElementById('tglSelesai');

function hitungTotal() {
  if (!selMobil || !tglMulai || !tglSelesai) return;
  const opt    = selMobil.selectedOptions[0];
  const harga  = parseInt(opt?.dataset.harga || 0);
  const d1     = new Date(tglMulai.value);
  const d2     = new Date(tglSelesai.value);
  const hari   = Math.max(1, Math.round((d2 - d1) / 86400000));
  const total  = hari * harga;

  document.getElementById('totalHarga').textContent = 'Rp ' + total.toLocaleString('id-ID');
  document.getElementById('totalHari').textContent  = hari + ' hari × Rp ' + harga.toLocaleString('id-ID') + '/hari';
}

selMobil?.addEventListener('change', hitungTotal);
tglMulai?.addEventListener('change', hitungTotal);
tglSelesai?.addEventListener('change', hitungTotal);
hitungTotal();
</script>
</body>
</html>
