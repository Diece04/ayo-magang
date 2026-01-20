<?php
session_start();
include 'koneksi/config.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
  header("Location: authenticator/auth-login-basic.php");
  exit;
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

$message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = $_POST['username'] ?? '';
  $password = $_POST['password'] ?? '';
  $nama = $_POST['nama'] ?? '';
  $deskripsi = $_POST['deskripsi'] ?? '';
  $fakultas = $_POST['fakultas'] ?? '';

  // Update users table
  if (!empty($password)) {
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("UPDATE users SET username = ?, password = ? WHERE id = ?");
    $stmt->execute([$username, $hashed_password, $user_id]);
  } else {
    $stmt = $pdo->prepare("UPDATE users SET username = ? WHERE id = ?");
    $stmt->execute([$username, $user_id]);
  }

  // Update role-specific table
  if ($role == 2) { // Mahasiswa
    $stmt = $pdo->prepare("UPDATE mahasiswa SET nama = ? WHERE user_id = ?");
    $stmt->execute([$nama, $user_id]);
  } elseif ($role == 3) { // Perusahaan
    $stmt = $pdo->prepare("UPDATE perusahaan SET nama = ?, deskripsi = ? WHERE user_id = ?");
    $stmt->execute([$nama, $deskripsi, $user_id]);
  } elseif ($role == 4) { // Jurusan
    $stmt = $pdo->prepare("UPDATE jurusan SET nama = ?, fakultas = ? WHERE user_id = ?");
    $stmt->execute([$nama, $fakultas, $user_id]);
  }

  $message = "Profile updated successfully.";
}

// Fetch current data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

$data = [];
if ($role == 2) {
  $stmt = $pdo->prepare("SELECT * FROM mahasiswa WHERE user_id = ?");
  $stmt->execute([$user_id]);
  $data = $stmt->fetch();
} elseif ($role == 3) {
  $stmt = $pdo->prepare("SELECT * FROM perusahaan WHERE user_id = ?");
  $stmt->execute([$user_id]);
  $data = $stmt->fetch();
} elseif ($role == 4) {
  $stmt = $pdo->prepare("SELECT * FROM jurusan WHERE user_id = ?");
  $stmt->execute([$user_id]);
  $data = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="en" class="light-style layout-menu-fixed" dir="ltr" data-theme="theme-default" data-assets-path="../assets/" data-template="vertical-menu-template-free">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
  <title>My Profile</title>
  <meta name="description" content="" />
  <!-- Favicon -->
  <link rel="icon" type="image/x-icon" href="assets/img/PNP.png" />
  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap" rel="stylesheet" />
  <!-- Icons -->
  <link rel="stylesheet" href="assets/vendor/fonts/boxicons.css" />
  <!-- Core CSS -->
  <link rel="stylesheet" href="assets/vendor/css/core.css" class="template-customizer-core-css" />
  <link rel="stylesheet" href="assets/vendor/css/theme-default.css" class="template-customizer-theme-css" />
  <link rel="stylesheet" href="assets/css/demo.css" />
  <!-- Vendors CSS -->
  <link rel="stylesheet" href="assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" />
  <link rel="stylesheet" href="assets/vendor/libs/apex-charts/apex-charts.css" />
  <!-- Helpers -->
  <script src="assets/vendor/js/helpers.js"></script>
  <script src="assets/js/config.js"></script>
</head>
<body>
  <!-- Layout wrapper -->
  <div class="layout-wrapper layout-content-navbar">
    <div class="layout-container">
      <!-- Menu -->
      <aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
        <div class="app-brand demo">
          <a href="index.php" class="app-brand-link">
            <span class="app-brand-logo demo">
              <svg width="25" viewBox="0 0 25 42" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                <!-- SVG content -->
              </svg>
            </span>
            <span class="app-brand-text demo menu-text fw-bolder ms-2">Ayo Magang</span>
          </a>
          <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-block d-xl-none">
            <i class="bx bx-chevron-left bx-sm align-middle"></i>
          </a>
        </div>
        <div class="menu-inner-shadow"></div>
        <ul class="menu-inner py-1">
          <li class="menu-item">
            <a href="<?php echo ($role == 1 ? 'index.php' : ($role == 2 ? 'mahasiswa_dashboard.php' : ($role == 3 ? 'perusahaan_dashboard.php' : 'jurusan_dashboard.php'))); ?>" class="menu-link">
              <i class="menu-icon tf-icons bx bx-home-circle"></i>
              <div>Dashboard</div>
            </a>
          </li>
          <!-- Other menu items based on role -->
        </ul>
      </aside>
      <!-- / Menu -->
      <!-- Layout container -->
      <div class="layout-page">
        <!-- Navbar -->
        <nav class="layout-navbar container-xxl navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme" id="layout-navbar">
          <!-- Navbar content -->
        </nav>
        <!-- / Navbar -->
        <!-- Content wrapper -->
        <div class="content-wrapper">
          <div class="container-xxl flex-grow-1 container-p-y">
            <h4>My Profile</h4>
            <?php if ($message): ?>
              <div class="alert alert-success"><?php echo $message; ?></div>
            <?php endif; ?>
            <form method="post">
              <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
              </div>
              <div class="mb-3">
                <label for="password" class="form-label">New Password (leave blank to keep current)</label>
                <input type="password" class="form-control" id="password" name="password">
              </div>
              <?php if ($role == 2): ?>
                <div class="mb-3">
                  <label for="nama" class="form-label">Nama</label>
                  <input type="text" class="form-control" id="nama" name="nama" value="<?php echo htmlspecialchars($data['nama'] ?? ''); ?>" required>
                </div>
              <?php elseif ($role == 3): ?>
                <div class="mb-3">
                  <label for="nama" class="form-label">Nama Perusahaan</label>
                  <input type="text" class="form-control" id="nama" name="nama" value="<?php echo htmlspecialchars($data['nama'] ?? ''); ?>" required>
                </div>
                <div class="mb-3">
                  <label for="deskripsi" class="form-label">Deskripsi</label>
                  <textarea class="form-control" id="deskripsi" name="deskripsi"><?php echo htmlspecialchars($data['deskripsi'] ?? ''); ?></textarea>
                </div>
              <?php elseif ($role == 4): ?>
                <div class="mb-3">
                  <label for="nama" class="form-label">Nama Jurusan</label>
                  <input type="text" class="form-control" id="nama" name="nama" value="<?php echo htmlspecialchars($data['nama'] ?? ''); ?>" required>
                </div>
                <div class="mb-3">
                  <label for="fakultas" class="form-label">Fakultas</label>
                  <input type="text" class="form-control" id="fakultas" name="fakultas" value="<?php echo htmlspecialchars($data['fakultas'] ?? ''); ?>" required>
                </div>
              <?php endif; ?>
              <button type="submit" class="btn btn-primary">Update Profile</button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- Core JS -->
  <script src="assets/vendor/libs/jquery/jquery.js"></script>
  <script src="assets/vendor/libs/popper/popper.js"></script>
  <script src="assets/vendor/js/bootstrap.js"></script>
  <script src="assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>
  <script src="assets/vendor/js/menu.js"></script>
  <script src="assets/vendor/libs/apex-charts/apexcharts.js"></script>
  <script src="assets/js/main.js"></script>
  <script src="assets/js/dashboards-analytics.js"></script>
</body>
</html>
