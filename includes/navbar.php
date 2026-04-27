<?php

$halaman_aktif = basename($_SERVER['PHP_SELF']);
?>
<nav class="navbar navbar-expand-lg" style="background:#0f2d4a; font-family:'DM Sans',sans-serif;">
  <div class="container-fluid px-4">

    <a class="navbar-brand d-flex align-items-center gap-2" href="dashboard.php">
      <div style="width:32px;height:32px;background:#e8a020;border-radius:8px;display:flex;align-items:center;justify-content:center;">
        <i class="bi bi-car-front-fill text-white" style="font-size:15px;"></i>
      </div>
      <span style="font-family:'Sora',sans-serif;font-weight:700;color:#fff;font-size:17px;">DriveNow</span>
    </a>

    <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
      <i class="bi bi-list text-white fs-4"></i>
    </button>

    <div class="collapse navbar-collapse" id="navMenu">
      <ul class="navbar-nav me-auto ms-3 gap-1">
        <li class="nav-item">
          <a class="nav-link px-3 py-2 rounded <?= $halaman_aktif=='dashboard.php'?'active':'' ?>"
             href="dashboard.php" style="color:rgba(255,255,255,.75);font-size:14px;">
            <i class="bi bi-speedometer2 me-1"></i> Dashboard
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link px-3 py-2 rounded <?= $halaman_aktif=='mobil.php'?'active':'' ?>"
             href="mobil.php" style="color:rgba(255,255,255,.75);font-size:14px;">
            <i class="bi bi-car-front me-1"></i> Data Mobil
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link px-3 py-2 rounded <?= $halaman_aktif=='pelanggan.php'?'active':'' ?>"
             href="pelanggan.php" style="color:rgba(255,255,255,.75);font-size:14px;">
            <i class="bi bi-people me-1"></i> Pelanggan
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link px-3 py-2 rounded <?= $halaman_aktif=='transaksi.php'?'active':'' ?>"
             href="transaksi.php" style="color:rgba(255,255,255,.75);font-size:14px;">
            <i class="bi bi-receipt me-1"></i> Transaksi
          </a>
        </li>
      </ul>
      <div class="d-flex align-items-center gap-3">
        <span style="color:rgba(255,255,255,.6);font-size:13px;">
          <i class="bi bi-person-circle me-1"></i>
          <?= htmlspecialchars($_SESSION['nama_lengkap'] ?? $_SESSION['username']) ?>
        </span>
        <a href="logout.php" class="btn btn-sm"
           style="background:rgba(255,255,255,.1);color:#fff;border:1px solid rgba(255,255,255,.2);border-radius:7px;font-size:13px;padding:5px 14px;">
          <i class="bi bi-box-arrow-right me-1"></i> Logout
        </a>
      </div>
    </div>
  </div>
</nav>

<style>
.navbar .nav-link.active, .navbar .nav-link:hover { color:#fff !important; background:rgba(255,255,255,.1) !important; }
</style>
