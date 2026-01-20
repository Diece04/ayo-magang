<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 3) {
  header("Location: ../authenticator/auth-login-basic.php");
  exit;
}
include '../koneksi/config.php';
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM perusahaan WHERE user_id = ?");
$stmt->execute([$user_id]);
$perusahaan = $stmt->fetch();

$stmtUsers = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmtUsers->execute([$user_id]);
$users = $stmtUsers->fetch();

// Add query to count total mahasiswa who applied
$stmtTotalMahasiswa = $pdo->prepare("SELECT COUNT(DISTINCT mahasiswa_id) as total FROM pengajuan_magang WHERE perusahaan_id = ?");
$stmtTotalMahasiswa->execute([$perusahaan['id']]);
$totalMahasiswa = $stmtTotalMahasiswa->fetch()['total'];

// Add query for notification count (new applications + pending kerja_sama)
$stmtNotif = $pdo->prepare("SELECT COUNT(*) as total FROM pengajuan_magang WHERE perusahaan_id = ? AND status = 'permohonan'");
$stmtNotif->execute([$perusahaan['id']]);
$notif_count = $stmtNotif->fetch()['total'];
$stmtNotif2 = $pdo->prepare("SELECT COUNT(*) as total FROM kerja_sama WHERE perusahaan_id = ? AND status = 'proposed'");
$stmtNotif2->execute([$perusahaan['id']]);
$notif_count += $stmtNotif2->fetch()['total'];

// Handle accept/reject actions by perusahaan
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && isset($_POST['pengajuan_id'])) {
  $action = $_POST['action'];
  $pengajuan_id = intval($_POST['pengajuan_id']);
  if ($action === 'accept') {
    $upd = $pdo->prepare("UPDATE pengajuan_magang SET status = 'diterima' WHERE id = ? AND perusahaan_id = ?");
    $upd->execute([$pengajuan_id, $perusahaan['id']]);
  } elseif ($action === 'reject') {
    $upd = $pdo->prepare("UPDATE pengajuan_magang SET status = 'ditolak' WHERE id = ? AND perusahaan_id = ?");
    $upd->execute([$pengajuan_id, $perusahaan['id']]);
  }
  // reload to reflect changes
  header("Location: perusahaan_dashboard.php");
  exit;
}

// Handle kerja sama actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accept_ks'])) {
  $ks_id = intval($_POST['ks_id']);
  $upd = $pdo->prepare("UPDATE kerja_sama SET status = 'approved' WHERE id = ? AND perusahaan_id = ?");
  $upd->execute([$ks_id, $perusahaan['id']]);
  header("Location: perusahaan_dashboard.php");
  exit;
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reject_ks'])) {
  $ks_id = intval($_POST['ks_id']);
  $upd = $pdo->prepare("UPDATE kerja_sama SET status = 'rejected' WHERE id = ? AND perusahaan_id = ?");
  $upd->execute([$ks_id, $perusahaan['id']]);
  header("Location: perusahaan_dashboard.php");
  exit;
}
?>
<!DOCTYPE html>

<!-- =========================================================
* Sneat - Bootstrap 5 HTML Admin Template - Pro | v1.0.0
==============================================================

* Product Page: https://themeselection.com/products/sneat-bootstrap-html-admin-template/
* Created by: ThemeSelection
* License: You must have a valid license purchased in order to legally use the theme for your project.
* Copyright ThemeSelection (https://themeselection.com)

=========================================================
-->
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

  <title>Dashboard</title>

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
          <li class="menu-item active">
            <a href="perusahaan_dashboard.php" class="menu-link">
              <i class="menu-icon tf-icons bx bx-home-circle"></i>
              <div>Dashboard</div>
            </a>
          </li>
          <li class="menu-item">
            <a href="#mahasiswa" class="menu-link">
              <i class="menu-icon tf-icons bx bx-user"></i>
              <div>Mahasiswa</div>
            </a>
          </li>
          <li class="menu-item">
            <a href="#jurusan" class="menu-link">
              <i class="menu-icon tf-icons bx bx-graduation-cap"></i>
              <div>Jurusan</div>
            </a>
          </li>
          <li class="menu-item">
            <a href="#kerja_sama" class="menu-link">
              <i class="menu-icon tf-icons bx bx-handshake"></i>
              <div>Kerja Sama</div>
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
                  aria-label="Star themeselection/sneat-html-admin-template-free on GitHub">Perusahaan</a>
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
                          <span class="fw-semibold d-block"><?php echo htmlspecialchars(string: $perusahaan['nama']); ?></span>
                          <small class="text-muted"><?php echo htmlspecialchars($users['username']); ?></small>
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
              <div class="col-lg-8 mb-4 order-0 d-flex">
                <div class="card flex-fill">
                  <div class="d-flex align-items-end row h-100">
                    <div class="col-sm-7">
                      <div class="card-body">
                        <h5 class="card-title text-primary">Selamat Datang, <?php echo htmlspecialchars($perusahaan['nama']); ?>!</h5>
                        <!-- <p class="mb-4>
                            You have done <span class="fw-bold">72%</span> more sales today. Check your new badge in
                            your profile.
                          </p> -->

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
              <div class="col-lg-4 col-md-6 mb-4 d-flex">
                <div class="card flex-fill">
                  <div class="card-body">
                    <div class="card-title d-flex align-items-start justify-content-between">
                      <div class="avatar flex-shrink-0"></div>

                      <div class="dropdown">
                        <button
                          class="btn p-0"
                          type="button"
                          data-bs-toggle="dropdown">
                          <i class="bx bx-dots-vertical-rounded"></i>
                        </button>

                        <div class="dropdown-menu dropdown-menu-end">
                          <a class="dropdown-item" href="#">View More</a>
                          <a class="dropdown-item" href="#">Delete</a>
                        </div>
                      </div>
                    </div>

                    <div class="d-flex flex-column justify-content-end">
                      <span class="fw-semibold d-block mb-1 mt-3">Total Mahasiswa</span>
                      <h3 class="card-title mb-0"><?= $totalMahasiswa ?></h3>
                    </div>
                  </div>
                </div>
              </div>

            </div>
            <div id="mahasiswa" class="col-12 mb-4">
              <h5>Mahasiswa Mengajukan</h5>
              <div class="d-flex gap-3 overflow-auto pb-2">
                <?php
                $stmt = $pdo->prepare("SELECT 
                                                  pm.*,
                                                  m.id        AS mahasiswa_id,
                                                  m.nama      AS mahasiswa_nama,
                                                  s.id        AS surat_id,
                                                  s.status    AS status_surat,
                                                  s.no_surat
                                              FROM pengajuan_magang pm
                                              JOIN mahasiswa m 
                                                  ON pm.mahasiswa_id = m.id
                                              LEFT JOIN surat s 
                                                  ON s.mahasiswa_id = pm.mahasiswa_id
                                                AND s.perusahaan_id = pm.perusahaan_id
                                              WHERE pm.perusahaan_id = ?
                                              ");
                $stmt->execute([$perusahaan['id']]);
                while ($row = $stmt->fetch()) {
                  echo '<div class="card flex-shrink-0" style="min-width:280px;">
                      <div class="card-body">
                        <h5 class="card-title mb-1">' . htmlspecialchars($row['mahasiswa_nama']) . '</h5>
                        <p class="card-text mb-2">Status: ' . htmlspecialchars(ucfirst($row['status'])) . '</p>';
                  // show accept/reject only if status is permohonan (company decides only after admin sets 'permohonan')
                  if ($row['status'] === 'pengajuan') {
                    echo '<form method="post" class="d-flex gap-2">
                            <input type="hidden" name="pengajuan_id" value="' . intval($row['id']) . '">
                            <button type="submit" name="action" value="accept" class="btn btn-success btn-sm">Terima</button>
                            <button type="submit" name="action" value="reject" class="btn btn-danger btn-sm">Tolak</button>';
                    if ($row['surat_id'] !== null) {
                      echo '
                              <a href="../admin/view_surat_pengantar.php?id=' . $row['surat_id'] . '&mode=view"
                                class="btn btn-outline-primary flex-fill me-1">
                                <i class="bx bx-eye me-1"></i>Lihat Surat
                              </a>';
                    }
                    echo '</form>';
                  }
                  echo '</div></div>';
                }
                ?>
              </div>
            </div>

            <div id="jurusan" class="col-12 mb-4">
              <h5>Jurusan Kerja Sama</h5>
              <div class="d-flex gap-3 overflow-auto pb-2">
                <?php
                $stmt = $pdo->prepare("SELECT j.* FROM kerja_sama ks JOIN jurusan j ON ks.jurusan_id = j.id WHERE ks.perusahaan_id = ?");
                $stmt->execute([$perusahaan['id']]);
                while ($row = $stmt->fetch()) {
                  echo '<div class="card flex-shrink-0" style="min-width:280px;"><div class="card-body"><h5 class="card-title">' . htmlspecialchars($row['nama']) . '</h5><p class="card-text">Fakultas: ' . htmlspecialchars($row['fakultas']) . '</p></div></div>';
                }
                ?>
              </div>
            </div>

            <div id="kerja_sama" class="col-12 mb-4">
              <h5>Proposal Kerja Sama</h5>
              <div class="row">
                <?php
                $stmt = $pdo->prepare("SELECT ks.*, j.nama as jurusan_nama FROM kerja_sama ks JOIN jurusan j ON ks.jurusan_id = j.id WHERE ks.perusahaan_id = ? AND ks.status = 'proposed'");
                $stmt->execute([$perusahaan['id']]);
                while ($row = $stmt->fetch()) {
                  echo '<div class="col-md-4 mb-4">
                    <div class="card">
                      <div class="card-body">
                        <h5 class="card-title">' . htmlspecialchars($row['jurusan_nama']) . '</h5>
                        <form method="post" class="d-flex gap-2">
                          <input type="hidden" name="ks_id" value="' . intval($row['id']) . '">
                          <button type="submit" name="accept_ks" class="btn btn-success btn-sm">Terima</button>
                          <button type="submit" name="reject_ks" class="btn btn-danger btn-sm">Tolak</button>
                        </form>
                      </div>
                    </div>
                  </div>';
                }
                ?>
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