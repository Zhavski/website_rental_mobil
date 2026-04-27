<?php
session_start();
require_once 'config.php';

// redirect to dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username && $password) {
        $stmt = $conn->prepare("SELECT id, username, password, nama_lengkap FROM users WHERE username = ?");
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user && $user['password'] === md5($password)) {
            $_SESSION['user_id']      = $user['id'];
            $_SESSION['username']     = $user['username'];
            $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
            header('Location: dashboard.php');
            exit;
        } else {
            $error = 'Username atau password salah!';
        }
    } else {
        $error = 'Username dan password wajib diisi!';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login — DriveNow Rental</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;600;700&family=DM+Sans:wght@400;500&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
<style>
  body { font-family: 'DM Sans', sans-serif; background: #f0f4f8; min-height: 100vh; display: flex; align-items: center; justify-content: center; }
  .login-wrap { display: flex; width: 860px; min-height: 500px; border-radius: 18px; overflow: hidden; box-shadow: 0 20px 60px rgba(0,0,0,0.12); }
  .login-left { width: 42%; background: #0f2d4a; padding: 2.5rem; display: flex; flex-direction: column; justify-content: space-between; position: relative; overflow: hidden; }
  .login-left::before { content:''; position:absolute; bottom:-80px; left:-60px; width:280px; height:280px; border-radius:50%; background:rgba(255,255,255,0.04); }
  .login-left::after  { content:''; position:absolute; top:-50px; right:-80px; width:220px; height:220px; border-radius:50%; background:rgba(255,255,255,0.05); }
  .brand-icon { width:40px; height:40px; background:#e8a020; border-radius:10px; display:flex; align-items:center; justify-content:center; }
  .brand-name { font-family:'Sora',sans-serif; font-weight:700; font-size:20px; color:#fff; }
  .brand-sub  { font-size:10px; color:rgba(255,255,255,0.5); letter-spacing:2px; text-transform:uppercase; }
  .tagline h2 { font-family:'Sora',sans-serif; font-size:22px; color:#fff; font-weight:700; line-height:1.35; }
  .tagline p  { font-size:13px; color:rgba(255,255,255,0.5); line-height:1.7; }
  .login-right { flex:1; background:#fff; padding:3rem 2.5rem; display:flex; flex-direction:column; justify-content:center; }
  .login-right h3 { font-family:'Sora',sans-serif; font-weight:700; font-size:22px; color:#0f2d4a; }
  .form-label { font-size:12px; font-weight:500; letter-spacing:.5px; text-transform:uppercase; color:#6c757d; }
  .form-control { border-radius:8px; border:1px solid #dee2e6; padding:10px 14px; font-size:14px; background:#f8f9fa; }
  .form-control:focus { border-color:#0f2d4a; box-shadow:0 0 0 3px rgba(15,45,74,.1); background:#fff; }
  .btn-login { background:#0f2d4a; color:#fff; border:none; border-radius:8px; padding:11px; font-family:'Sora',sans-serif; font-weight:600; font-size:14px; letter-spacing:.3px; transition:.15s; }
  .btn-login:hover { background:#1a4270; color:#fff; }
  .demo-box { background:#f8f9fa; border-radius:8px; padding:10px 14px; border:1px solid #e9ecef; font-size:12px; color:#6c757d; }
  .dot-accent { display:inline-block; width:7px; height:7px; border-radius:50%; background:#e8a020; margin-right:6px; }
</style>
</head>
<body>
<div class="login-wrap">

  <!-- KIRI -->
  <div class="login-left">
    <div class="d-flex align-items-center gap-2">
      <div class="brand-icon">
        <i class="bi bi-car-front-fill text-white fs-5"></i>
      </div>
      <div>
        <div class="brand-name">DriveNow</div>
        <div class="brand-sub">Rental Mobil</div>
      </div>
    </div>

    <div style="text-align:center; padding:1rem 0;">
      <i class="bi bi-car-front" style="font-size:72px; color:#e8a020; opacity:.85;"></i>
    </div>

    <div class="tagline">
      <h2>Kelola rental mobilmu dengan mudah</h2>
      <p class="mt-2">Armada, pelanggan, dan transaksi — semua dalam satu dashboard.</p>
    </div>
  </div>

  <!-- KANAN -->
  <div class="login-right">
    <div class="mb-4">
      <h3>Selamat datang kembali</h3>
      <p class="text-muted" style="font-size:14px;">Masuk ke panel admin DriveNow</p>
    </div>

    <?php if ($error): ?>
    <div class="alert alert-danger d-flex align-items-center gap-2 py-2" style="font-size:13px; border-radius:8px;">
      <i class="bi bi-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
    </div>
    <?php endif; ?>

    <form method="POST">
      <div class="mb-3">
        <label class="form-label">Username</label>
        <div class="input-group">
          <span class="input-group-text" style="background:#f8f9fa; border-radius:8px 0 0 8px;"><i class="bi bi-person"></i></span>
          <input type="text" name="username" class="form-control" placeholder="Masukkan username..."
                 value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required style="border-radius:0 8px 8px 0;">
        </div>
      </div>

      <div class="mb-4">
        <label class="form-label">Password</label>
        <div class="input-group">
          <span class="input-group-text" style="background:#f8f9fa; border-radius:8px 0 0 8px;"><i class="bi bi-lock"></i></span>
          <input type="password" name="password" class="form-control" placeholder="Masukkan password..." required style="border-radius:0 8px 8px 0;">
        </div>
      </div>

      <button type="submit" class="btn btn-login w-100">
        <i class="bi bi-box-arrow-in-right me-1"></i> Masuk ke Dashboard
      </button>
    </form>

    <div class="demo-box mt-4">
      <span class="dot-accent"></span>
      Demo login: <strong>admin</strong> / <strong>admin123</strong>
    </div>
  </div>

</div>
</body>
</html>
