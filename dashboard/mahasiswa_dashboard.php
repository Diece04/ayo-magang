<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 2) {
  header("Location: ../authenticator/auth-login-basic.php");
  exit;
}
include '../koneksi/config.php';
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM mahasiswa WHERE user_id = ?");
$stmt->execute([$user_id]);
$mahasiswa = $stmt->fetch();

$stmtUsers = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmtUsers->execute([$user_id]);
$users = $stmtUsers->fetch();

// ambil data 
$stmtPengajuan = $pdo->prepare("SELECT count(*) FROM pengajuan_magang pm JOIN mahasiswa m ON pm.mahasiswa_id = m.id WHERE m.user_id = ? && (pm.status = 'pengajuan' || pm.status = 'diterima')");
$stmtPengajuan->execute([$user_id]);
$pengajuan_magang = $stmtPengajuan->fetchColumn();

// Handle apply (Ajukan Magang)
$success = $error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['apply_company_id'])) {
  $companyId = intval($_POST['apply_company_id']);
  // prevent duplicate pengajuan
  $chk = $pdo->prepare("SELECT id FROM pengajuan_magang WHERE mahasiswa_id = ? AND perusahaan_id = ?");
  $chk->execute([$mahasiswa['id'], $companyId]);
  if ($chk->fetch()) {
    $error = "Anda sudah mengajukan ke perusahaan ini.";
  } else {
    $ins = $pdo->prepare("INSERT INTO pengajuan_magang (mahasiswa_id, perusahaan_id, status) VALUES (?, ?, 'pengajuan')");
    $ins->execute([$mahasiswa['id'], $companyId]);
    $success = "Pengajuan berhasil dibuat.";
  }
  header("Location: " . $_SERVER['PHP_SELF']);
  exit;
}

// Fetch data for cards
$stmt = $pdo->query("SELECT COUNT(*) as total FROM mahasiswa");
$mahasiswa_count = $stmt->fetch()['total'];
$stmt = $pdo->query("SELECT COUNT(*) as total FROM perusahaan where status = 'approved'");
$perusahaan_count = $stmt->fetch()['total'];
$stmt = $pdo->query("SELECT COUNT(*) as total FROM jurusan");
$jurusan_count = $stmt->fetch()['total'];

// Add query for notification count (status updates on applications)
$stmtNotif = $pdo->prepare("SELECT COUNT(*) as total FROM pengajuan_magang WHERE mahasiswa_id = ? AND status != 'pengajuan'");
$stmtNotif->execute([$mahasiswa['id']]);
$notif_count = $stmtNotif->fetch()['total'];
?>
<!DOCTYPE html>

<!-- =========================================================
* Sneat - Bootstrap 5 HTML Admin Template - Pro | v1.0.0
==============================================================

* Product Page: https://themeselection.com/products/sneat-bootstrap-html-admin-template/
* Created by: ThemeSelection
* License: You must have a valid license purchased in order to legally use the theme for your project.
* Copyright ThemeSelection (https://themeselection.com)

========================================================= -->
<!-- beautify ignore:start -->
<html
  lang="en"
  class="light-style layout-menu-fixed"
  dir="ltr"
  data-theme="theme-default"
  data-assets-path="../assets/"
  data-template="vertical-menu-template-free">

<head>
  <meta charset="utf-8" />
  <meta
    name="viewport"
    content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />

  <title>Dashboard Mahasiswa</title>

  <meta name="description" content="" />

  <!-- Favicon -->
  <link rel="icon" type="image/x-icon" href="../assets/img/PNP.png" />

  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link
    href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap"
    rel="stylesheet" />

  <!-- Icons. Uncomment required icon fonts -->
  <link rel="stylesheet" href="../assets/vendor/fonts/boxicons.css" />

  <!-- Core CSS -->
  <link rel="stylesheet" href="../assets/vendor/css/core.css" class="template-customizer-core-css" />
  <link rel="stylesheet" href="../assets/vendor/css/theme-default.css" class="template-customizer-theme-css" />
  <link rel="stylesheet" href="../assets/css/demo.css" />

  <!-- Vendors CSS -->
  <link rel="stylesheet" href="../assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" />

  <link rel="stylesheet" href="../assets/vendor/libs/apex-charts/apex-charts.css" />

  <!-- Page CSS -->

  <!-- Helpers -->
  <script src="../assets/vendor/js/helpers.js"></script>

  <!--! Template customizer & Theme config files MUST be included after core stylesheets and helpers.js in the <head> section -->
  <!--? Config:  Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file.  -->
  <script src="../assets/js/config.js"></script>
</head>

<body>
  <!-- Layout wrapper -->
  <div class="layout-wrapper layout-content-navbar">
    <div class="layout-container">
      <!-- Menu -->

      <aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
        <div class="app-brand demo">
          <a href="index.html" class="app-brand-link">
            <span class="app-brand-text demo menu-text fw-bolder ms-2">Ayo Magang</span>
          </a>

          <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-block d-xl-none">
            <i class="bx bx-chevron-left bx-sm align-middle"></i>
          </a>
        </div>

        <div class="menu-inner-shadow"></div>

        <ul class="menu-inner py-1">
          <!-- Dashboard -->
          <li class="menu-item active">
            <a href="mahasiswa_dashboard.php" class="menu-link">
              <i class="menu-icon tf-icons bx bx-home-circle"></i>
              <div data-i18n="Analytics">Dashboard</div>
            </a>
          </li>
          <!-- Mahasiswa menu items -->
          <li class="menu-item">
            <a href="#perusahaan" class="menu-link">
              <i class="menu-icon tf-icons bx bx-building"></i>
              <div>Perusahaan</div>
            </a>
          </li>
          <li class="menu-item">
            <a href="#magang_saya" class="menu-link">
              <i class="menu-icon tf-icons bx bx-briefcase"></i>
              <div>Magang Saya</div>
            </a>
          </li>
          <li class="menu-item">
            <a href="#jurusan" class="menu-link">
              <i class="menu-icon tf-icons bx bx-graduation-cap"></i>
              <div>Jurusan</div>
            </a>
          </li>
        </ul>
      </aside>
      <!-- / Menu -->

      <!-- Layout container -->
      <div class="layout-page">
        <!-- Navbar -->

        <nav
          class="layout-navbar container-xxl navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme"
          id="layout-navbar">
          <div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0 d-xl-none">
            <a class="nav-item nav-link px-0 me-xl-4" href="javascript:void(0)">
              <i class="bx bx-menu bx-sm"></i>
            </a>
          </div>

          <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">
            <!-- Search -->
            <div class="navbar-nav align-items-center">
              <div class="nav-item d-flex align-items-center">
                <i class="bx bx-search fs-4 lh-0"></i>
                <input
                  type="text"
                  class="form-control border-0 shadow-none"
                  placeholder="Search..."
                  aria-label="Search..." />
              </div>
            </div>
            <!-- /Search -->

            <ul class="navbar-nav flex-row align-items-center ms-auto">
              <!-- Place this tag where you want the button to render. -->
              <li class="nav-item lh-1 me-3">
                <a
                  class="github-button"
                  href="#"
                  data-icon="octicon-star"
                  data-size="large"
                  aria-label="Star themeselection/sneat-html-admin-template-free on GitHub">Mahasiswa</a>
              </li>

              <!-- User -->
              <li class="nav-item navbar-dropdown dropdown-user dropdown">
                <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown">
                  <div class="avatar avatar-online">
                    <img src="../assets/img/avatars/1.png" alt class="w-px-40 h-auto rounded-circle" />
                  </div>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                  <li>
                    <a class="dropdown-item" href="#">
                      <div class="d-flex">
                        <div class="flex-shrink-0 me-3">
                          <div class="avatar avatar-online">
                            <img src="../assets/img/avatars/1.png" alt class="w-px-40 h-auto rounded-circle" />
                          </div>
                        </div>
                        <div class="flex-grow-1">
                          <span class="fw-semibold d-block"><?php echo htmlspecialchars($mahasiswa['nama']); ?></span>
                          <small class="text-muted"><?php echo htmlspecialchars($users['username']) ?></small>
                        </div>
                      </div>
                    </a>
                  </li>
                  <li>
                    <div class="dropdown-divider"></div>
                  </li>
                  <li>
                    <a class="dropdown-item" href="../profile.php">
                      <i class="bx bx-user me-2"></i>
                      <span class="align-middle">My Profile</span>
                    </a>
                  </li>
                  <li>
                    <a class="dropdown-item" href="#">
                      <i class="bx bx-cog me-2"></i>
                      <span class="align-middle">Settings</span>
                    </a>
                  </li>
                  <li>
                    <a class="dropdown-item" href="../notifications.php">
                      <span class="d-flex align-items-center align-middle">
                        <i class="flex-shrink-0 bx bx-credit-card me-2"></i>
                        <span class="flex-grow-1 align-middle">Notifikasi</span>
                        <span class="flex-shrink-0 badge badge-center rounded-pill bg-danger w-px-20 h-px-20"><?php echo $notif_count; ?></span>
                      </span>
                    </a>
                  </li>
                  <li>
                    <div class="dropdown-divider"></div>
                  </li>
                  <li>
                    <a class="dropdown-item" href="../logout.php">
                      <i class="bx bx-power-off me-2"></i>
                      <span class="align-middle">Log Out</span>
                    </a>
                  </li>
                </ul>
              </li>
              <!--/ User -->
            </ul>
          </div>
        </nav>

        <!-- / Navbar -->

        <!-- Content wrapper -->
        <div class="content-wrapper">
          <!-- Content -->

          <div class="container-xxl flex-grow-1 container-p-y">
            <div class="row">
              <div class="col-lg-12 mb-4 order-0">
                <div class="card">
                  <div class="d-flex align-items-end row">
                    <div class="col-sm-7">
                      <div class="card-body">
                        <h5 class="card-title text-primary">Selamat Datang, <?php echo htmlspecialchars($mahasiswa['nama']); ?>!</h5>
                        <p class="mb-4">
                          You have done <span class="fw-bold">72%</span> more sales today. Check your new badge in
                          your profile.
                        </p>

                        <a href="javascript:;" class="btn btn-sm btn-outline-primary">View Profile</a>
                      </div>
                    </div>
                    <div class="col-sm-5 text-center text-sm-left">
                      <div class="card-body pb-0 px-0 px-md-4">
                        <img
                          src="../assets/img/illustrations/man-with-laptop-light.png"
                          height="140"
                          alt="View Badge User"
                          data-app-dark-img="illustrations/man-with-laptop-dark.png"
                          data-app-light-img="illustrations/man-with-laptop-light.png" />
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <!-- ROW STATISTIK + STATUS -->
              <div class="col-12 order-1 order-md-1 mb-4">
                <div class="row g-3 align-items-stretch">

                  <!-- KIRI -->
                  <div class="col-lg-6 col-md-6">
                    <div class="row g-3 h-100">

                      <div class="col-12 d-flex">
                        <div class="card flex-fill">
                          <div class="card-body">
                            <span>Perusahaan Terverifikasi</span>
                            <h3 class="card-title text-nowrap mb-0"><?php echo $perusahaan_count; ?></h3>
                          </div>
                        </div>
                      </div>

                    </div>
                  </div>

                  <!-- KANAN -->
                  <div class="col-lg-6 col-md-6">
                    <div class="row g-3 h-100">

                      <div class="col-12 d-flex">
                        <div class="card flex-fill">
                          <div class="card-body">
                            <span class="d-block mb-1">Lowongan Aktif</span>
                            <h3 class="card-title text-nowrap mb-0"><?php echo $perusahaan_count; ?></h3> <!-- perbaiki nanti belum buat lowongan_count dan database lowongan -->
                          </div>
                        </div>
                      </div>

                    </div>
                  </div>
                </div>
              </div>
              <!-- Mahasiswa-specific sections -->
              <div id="perusahaan" class="col-12 mb-4">
                <h5>Perusahaan</h5>
                <div class="row">
                  <?php
                  $stmt = $pdo->query("SELECT * FROM perusahaan WHERE status='approved'");
                  while ($row = $stmt->fetch()) {
                    echo '<div class="col-md-4 mb-4">
                        <div class="card">
                          <div class="card-body">
                            <h5 class="card-title">' . htmlspecialchars($row['nama']) . '</h5>
                            <p class="card-text">' . htmlspecialchars($row['deskripsi']) . '</p>
                            <form method="post">
                              <input type="hidden" name="apply_company_id" value="' . intval($row['id']) . '">';

                    if ($pengajuan_magang > 0) {
                      echo '<button type="button" class="btn btn-secondary" disabled>Sudah Mengajukan</button>';
                    } else {
                      echo '<button type="submit" class="btn btn-primary">Ajukan Magang</button>';
                    }

                    echo '</form>
                          </div>
                        </div>
                      </div>';
                  }
                  ?>
                </div>
              </div>
              <div id="magang_saya" class="col-12 mb-4">
                <h5>Magang Saya</h5>
                <div class="row">
                  <?php
                  $stmt = $pdo->prepare("SELECT pm.*, p.nama as perusahaan FROM pengajuan_magang pm JOIN perusahaan p ON pm.perusahaan_id = p.id WHERE pm.mahasiswa_id = ?");
                  $stmt->execute([$mahasiswa['id']]);
                  while ($row = $stmt->fetch()) {
                    echo '<div class="col-md-4 mb-4">
                        <div class="card">
                          <div class="card-body">
                            <h5 class="card-title">' . htmlspecialchars($row['perusahaan']) . '</h5>
                            <p class="card-text">Status: ' . ucfirst($row['status']) . '</p>
                          </div>
                        </div>
                      </div>';
                  }
                  ?>
                </div>
              </div>
              <div id="jurusan" class="col-12 mb-4">
                <h5>Jurusan</h5>
                <div class="row">
                  <?php
                  $stmt = $pdo->prepare("SELECT j.* FROM jurusan j WHERE j.id = ?");
                  $stmt->execute([$mahasiswa['jurusan_id']]);
                  $jurusan = $stmt->fetch();
                  echo '<div class="col-md-4 mb-4">
                      <div class="card">
                        <div class="card-body">
                          <h5 class="card-title">' . htmlspecialchars($jurusan['nama']) . '</h5>
                          <p class="card-text">Fakultas: ' . htmlspecialchars($jurusan['fakultas']) . '</p>
                        </div>
                      </div>
                    </div>';
                  ?>
                </div>
              </div>
            </div>
          </div>
          <!-- / Content -->

          <!-- Footer -->

          <!-- / Footer -->

          <div class="content-backdrop fade"></div>
        </div>
        <!-- Content wrapper -->
      </div>
      <!-- / Layout page -->
    </div>

    <!-- Overlay -->
    <div class="layout-overlay layout-menu-toggle"></div>
  </div>
  <!-- / Layout wrapper -->

  <!-- Core JS -->
  <!-- build:js assets/vendor/js/core.js -->
  <script src="../assets/vendor/libs/jquery/jquery.js"></script>
  <script src="../assets/vendor/libs/popper/popper.js"></script>
  <script src="../assets/vendor/js/bootstrap.js"></script>
  <script src="../assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>

  <script src="../assets/vendor/js/menu.js"></script>
  <!-- endbuild -->

  <!-- Vendors JS -->
  <script src="../assets/vendor/libs/apex-charts/apexcharts.js"></script>

  <!-- Main JS -->
  <script src="../assets/js/main.js"></script>

  <!-- Page JS -->
  <script src="../assets/js/dashboards-analytics.js"></script>

  <!-- Place this tag in your head or just before your close body tag. -->
  <script async defer src="https://buttons.github.io/buttons.js"></script>
</body>

</html>