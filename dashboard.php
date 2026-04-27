<?php
session_start();
require_once 'config.php';
cekLogin();

// Stattrack
$total_mobil       = $conn->query("SELECT COUNT(*) as n FROM mobil")->fetch_assoc()['n'];
$mobil_tersedia    = $conn->query("SELECT COUNT(*) as n FROM mobil WHERE status='tersedia'")->fetch_assoc()['n'];
$total_pelanggan   = $conn->query("SELECT COUNT(*) as n FROM pelanggan")->fetch_assoc()['n'];
$total_transaksi   = $conn->query("SELECT COUNT(*) as n FROM transaksi")->fetch_assoc()['n'];
$transaksi_aktif   = $conn->query("SELECT COUNT(*) as n FROM transaksi WHERE status='aktif'")->fetch_assoc()['n'];
$pendapatan_row    = $conn->query("SELECT SUM(total_bayar) as total FROM transaksi WHERE status='selesai'")->fetch_assoc();
$pendapatan        = $pendapatan_row['total'] ?? 0;

// Transaksi
$transaksi_baru = $conn->query("
    SELECT t.*, p.nama AS nama_pelanggan, m.nama_mobil, m.merek
    FROM transaksi t
    JOIN pelanggan p ON t.id_pelanggan = p.id
    JOIN mobil m ON t.id_mobil = m.id
    ORDER BY t.created_at DESC
    LIMIT 5
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard — DriveNow</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;600;700&family=DM+Sans:wght@400;500&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
<style>
  body { font-family:'DM Sans',sans-serif; background:#f0f4f8; }
  .stat-card { border-radius:14px; border:none; padding:1.4rem 1.6rem; }
  .stat-icon { width:48px; height:48px; border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:22px; }
  .stat-label { font-size:12px; text-transform:uppercase; letter-spacing:.5px; color:#6c757d; margin-bottom:4px; }
  .stat-val   { font-family:'Sora',sans-serif; font-size:26px; font-weight:700; color:#0f2d4a; line-height:1; }
  .stat-sub   { font-size:12px; color:#6c757d; margin-top:4px; }
  .badge-status { font-size:11px; padding:4px 10px; border-radius:20px; font-weight:500; }
  .tbl-head { background:#0f2d4a; color:#fff; font-size:13px; }
  .section-title { font-family:'Sora',sans-serif; font-weight:700; font-size:18px; color:#0f2d4a; }
</style>
</head>
<body>
<?php require_once 'includes/navbar.php'; ?>

<div class="container-fluid px-4 py-4">

  <!-- Greeting -->
  <div class="mb-4">
    <h4 class="section-title mb-0">Dashboard</h4>
    <p class="text-muted" style="font-size:13px;">Selamat datang, <?= htmlspecialchars($_SESSION['nama_lengkap'] ?? 'Admin') ?>! Berikut ringkasan hari ini.</p>
  </div>

  <!-- Stat Cards -->
  <div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
      <div class="card stat-card shadow-sm">
        <div class="d-flex align-items-center gap-3">
          <div class="stat-icon" style="background:#e8f4fd;"><i class="bi bi-car-front-fill" style="color:#0d6efd;"></i></div>
          <div>
            <div class="stat-label">Total Mobil</div>
            <div class="stat-val"><?= $total_mobil ?></div>
            <div class="stat-sub"><?= $mobil_tersedia ?> tersedia</div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-sm-6 col-xl-3">
      <div class="card stat-card shadow-sm">
        <div class="d-flex align-items-center gap-3">
          <div class="stat-icon" style="background:#fff3cd;"><i class="bi bi-people-fill" style="color:#e8a020;"></i></div>
          <div>
            <div class="stat-label">Total Pelanggan</div>
            <div class="stat-val"><?= $total_pelanggan ?></div>
            <div class="stat-sub">terdaftar</div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-sm-6 col-xl-3">
      <div class="card stat-card shadow-sm">
        <div class="d-flex align-items-center gap-3">
          <div class="stat-icon" style="background:#d1f2eb;"><i class="bi bi-receipt-cutoff" style="color:#198754;"></i></div>
          <div>
            <div class="stat-label">Transaksi</div>
            <div class="stat-val"><?= $total_transaksi ?></div>
            <div class="stat-sub"><?= $transaksi_aktif ?> aktif</div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-sm-6 col-xl-3">
      <div class="card stat-card shadow-sm">
        <div class="d-flex align-items-center gap-3">
          <div class="stat-icon" style="background:#fde8e8;"><i class="bi bi-cash-stack" style="color:#dc3545;"></i></div>
          <div>
            <div class="stat-label">Pendapatan</div>
            <div class="stat-val" style="font-size:18px;"><?= rupiah($pendapatan) ?></div>
            <div class="stat-sub">transaksi selesai</div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Tabel transaksi terbaru -->
  <div class="card shadow-sm" style="border-radius:14px; border:none;">
    <div class="card-body">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h6 class="section-title mb-0" style="font-size:15px;">Transaksi Terbaru</h6>
        <a href="transaksi.php" class="btn btn-sm" style="background:#0f2d4a;color:#fff;border-radius:8px;font-size:13px;">Lihat Semua</a>
      </div>
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="font-size:13px;">
          <thead class="tbl-head">
            <tr>
              <th class="py-2 px-3" style="border-radius:8px 0 0 0;">Pelanggan</th>
              <th>Mobil</th>
              <th>Periode</th>
              <th>Total Bayar</th>
              <th class="text-center" style="border-radius:0 8px 0 0;">Status</th>
            </tr>
          </thead>
          <tbody>
          <?php while ($row = $transaksi_baru->fetch_assoc()): ?>
            <tr>
              <td class="px-3"><?= htmlspecialchars($row['nama_pelanggan']) ?></td>
              <td><?= htmlspecialchars($row['merek'] . ' ' . $row['nama_mobil']) ?></td>
              <td><?= date('d/m/Y', strtotime($row['tgl_mulai'])) ?> - <?= date('d/m/Y', strtotime($row['tgl_selesai'])) ?></td>
              <td><?= rupiah($row['total_bayar']) ?></td>
              <td class="text-center">
                <?php
                  $badge = ['aktif'=>'warning', 'selesai'=>'success', 'dibatalkan'=>'danger'];
                  $b = $badge[$row['status']] ?? 'secondary';
                ?>
                <span class="badge badge-status bg-<?= $b ?>"><?= ucfirst($row['status']) ?></span>
              </td>
            </tr>
          <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Shortcut navigasi -->
  <div class="row g-3 mt-3">
    <div class="col-md-4">
      <a href="mobil.php" class="card shadow-sm text-decoration-none p-3 d-flex flex-row align-items-center gap-3" style="border-radius:14px;border:none;">
        <div class="stat-icon" style="background:#e8f4fd;flex-shrink:0;"><i class="bi bi-car-front" style="color:#0d6efd;font-size:20px;"></i></div>
        <div>
          <div style="font-family:'Sora',sans-serif;font-weight:600;color:#0f2d4a;font-size:14px;">Kelola Armada</div>
          <div style="font-size:12px;color:#6c757d;">Tambah, edit, hapus mobil</div>
        </div>
        <i class="bi bi-arrow-right ms-auto text-muted"></i>
      </a>
    </div>
    <div class="col-md-4">
      <a href="pelanggan.php" class="card shadow-sm text-decoration-none p-3 d-flex flex-row align-items-center gap-3" style="border-radius:14px;border:none;">
        <div class="stat-icon" style="background:#fff3cd;flex-shrink:0;"><i class="bi bi-person-plus" style="color:#e8a020;font-size:20px;"></i></div>
        <div>
          <div style="font-family:'Sora',sans-serif;font-weight:600;color:#0f2d4a;font-size:14px;">Kelola Pelanggan</div>
          <div style="font-size:12px;color:#6c757d;">Data pelanggan rental</div>
        </div>
        <i class="bi bi-arrow-right ms-auto text-muted"></i>
      </a>
    </div>
    <div class="col-md-4">
      <a href="transaksi.php" class="card shadow-sm text-decoration-none p-3 d-flex flex-row align-items-center gap-3" style="border-radius:14px;border:none;">
        <div class="stat-icon" style="background:#d1f2eb;flex-shrink:0;"><i class="bi bi-plus-circle" style="color:#198754;font-size:20px;"></i></div>
        <div>
          <div style="font-family:'Sora',sans-serif;font-weight:600;color:#0f2d4a;font-size:14px;">Buat Transaksi</div>
          <div style="font-size:12px;color:#6c757d;">Catat penyewaan baru</div>
        </div>
        <i class="bi bi-arrow-right ms-auto text-muted"></i>
      </a>
    </div>
  </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
