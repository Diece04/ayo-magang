<?php
session_start();
include 'koneksi/config.php';

// jika tidak login -> ke login
if (!isset($_SESSION['user_id'])) {
  header("Location: authenticator/auth-login-basic.php");
  exit;
}

// jika bukan admin -> redirect sesuai role
if ($_SESSION['role'] != 1) {
  switch ($_SESSION['role']) {
    case 2:
      header("Location: dashboard/mahasiswa_dashboard.php");
      break;
    case 3:
      header("Location: dashboard/perusahaan_dashboard.php");
      break;
    case 4:
      header("Location: dashboard/jurusan_dashboard.php");
      break;
    default:
      header("Location: authenticator/auth-login-basic.php");
      break;
  }
  exit;
}

// Fetch data for cards
$stmt = $pdo->query("SELECT COUNT(*) as total FROM mahasiswa");
$mahasiswa_count = $stmt->fetch()['total'];
$stmt = $pdo->query("SELECT COUNT(*) as total FROM perusahaan where status = 'approved'");
$perusahaan_count = $stmt->fetch()['total'];
$stmt = $pdo->query("SELECT COUNT(*) as total FROM jurusan");
$jurusan_count = $stmt->fetch()['total'];
$stmt = $pdo->query("SELECT COUNT(*) as total FROM pengajuan_magang where status = 'pengajuan'");
$pengajuan_count = $stmt->fetch()['total'];

// Add query for notification count (pending companies)
$stmtNotif = $pdo->query("SELECT COUNT(*) as total FROM perusahaan WHERE status = 'pending'");
$notif_count = $stmtNotif->fetch()['total'];

// Handle accept/reject actions by perusahaan/admin
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && isset($_POST['pengajuan_id'])) {
  $action = $_POST['action'];
  $pengajuan_id = intval($_POST['pengajuan_id']);
  if ($action === 'reject') {
    // Update by pengajuan id only. Previously code used $perusahaan['id'] which is undefined here,
    // causing the UPDATE to affect 0 rows. Use id match and optionally add checks/authorization.
    $upd = $pdo->prepare("UPDATE pengajuan_magang SET status = 'ditolak' WHERE id = ?");
    $upd->execute([$pengajuan_id]);
    // optional: check if a row was actually updated
    if ($upd->rowCount() === 0) {
      // for debugging: you can log or set a session message
      // error_log("Tolak pengajuan gagal: id {$pengajuan_id} tidak ditemukan atau tidak diubah");
    }
  }
  // reload to reflect changes
  header("Location: index.php");
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
  data-assets-path="assets/"
  data-template="vertical-menu-template-free">

<head>
  <meta charset="utf-8" />
  <meta
    name="viewport"
    content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />

  <title>Dashboard</title>

  <meta name="description" content="" />

  <!-- Favicon -->
  <link rel="icon" type="image/x-icon" href="assets/img/PNP.png" />

  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link
    href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap"
    rel="stylesheet" />

  <!-- Icons. Uncomment required icon fonts -->
  <link rel="stylesheet" href="assets/vendor/fonts/boxicons.css" />

  <!-- Core CSS -->
  <link rel="stylesheet" href="assets/vendor/css/core.css" class="template-customizer-core-css" />
  <link rel="stylesheet" href="assets/vendor/css/theme-default.css" class="template-customizer-theme-css" />
  <link rel="stylesheet" href="assets/css/demo.css" />

  <!-- Vendors CSS -->
  <link rel="stylesheet" href="assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" />

  <link rel="stylesheet" href="assets/vendor/libs/apex-charts/apex-charts.css" />

  <!-- Page CSS -->

  <!-- Helpers -->
  <script src="assets/vendor/js/helpers.js"></script>

  <!--! Template customizer & Theme config files MUST be included after core stylesheets and helpers.js in the <head> section -->
  <!--? Config:  Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file.  -->
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
            <a href="index.php" class="menu-link">
              <i class="menu-icon tf-icons bx bx-home-circle"></i>
              <div data-i18n="Analytics">Dashboard</div>
            </a>
          </li>
          <!-- New Menus -->
          <li class="menu-item">
            <a href="admin/approve_perusahaan.php" class="menu-link">
              <i class="menu-icon tf-icons bx bx-check-circle"></i>
              <div>Perusahaan Mendaftar</div>
            </a>
          </li>
          <li class="menu-item">
            <a href="admin/create_jurusan.php" class="menu-link">
              <i class="menu-icon tf-icons bx bx-plus-circle"></i>
              <div>Buat Akun Jurusan</div>
            </a>
          </li>
          <li class="menu-item">
            <a href="#perusahaan" class="menu-link">
              <i class="menu-icon tf-icons bx bx-building"></i>
              <div>Perusahaan</div>
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
                  aria-label="Star themeselection/sneat-html-admin-template-free on GitHub">Admin</a>
              </li>

              <!-- User -->
              <li class="nav-item navbar-dropdown dropdown-user dropdown">
                <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown">
                  <div class="avatar avatar-online">
                    <img src="assets/img/avatars/1.png" alt class="w-px-40 h-auto rounded-circle" />
                  </div>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                  <li>
                    <a class="dropdown-item" href="#">
                      <div class="d-flex">
                        <div class="flex-shrink-0 me-3">
                          <div class="avatar avatar-online">
                            <img src="assets/img/avatars/1.png" alt class="w-px-40 h-auto rounded-circle" />
                          </div>
                        </div>
                        <div class="flex-grow-1">
                          <span class="fw-semibold d-block">Admin</span>
                          <small class="text-muted">Admin</small>
                        </div>
                      </div>
                    </a>
                  </li>
                  <li>
                    <div class="dropdown-divider"></div>
                  </li>
                  <li>
                    <a class="dropdown-item" href="profile.php">
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
                    <a class="dropdown-item" href="notifications.php">
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
                    <a class="dropdown-item" href="logout.php">
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
              <div class="col-12 mb-4 order-0">
                <div class="card">
                  <div class="d-flex align-items-end row">
                    <div class="col-sm-7">
                      <div class="card-body">
                        <h5 class="card-title text-primary">Selamat Datang, Admin!</h5>
                        <a href="javascript:;" class="btn btn-sm btn-outline-primary">View Profile</a>
                      </div>
                    </div>
                    <div class="col-sm-5 text-center text-sm-left">
                      <div class="card-body pb-0 px-0 px-md-4">
                        <img
                          src="assets/img/illustrations/man-with-laptop-light.png"
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
                            <span class="fw-semibold d-block mb-1">Total Mahasiswa</span>
                            <h3 class="card-title mb-0"><?php echo $mahasiswa_count; ?></h3>
                          </div>
                        </div>
                      </div>

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

                      <div class="col-12 d-flex">
                        <div class="card flex-fill">
                          <div class="card-body">
                            <span class="fw-semibold d-block mb-1">Total Pengajuan</span>
                            <h3 class="card-title text-nowrap mb-0"><?php echo $pengajuan_count; ?></h3>
                          </div>
                        </div>
                      </div>

                    </div>
                  </div>
                </div>
              </div>

              <!-- New sections for data display -->
              <div id="perusahaan" class="col-12 mb-4">
                <h5>Perusahaan</h5>
                <div class="d-flex gap-3 overflow-auto pb-2">
                  <?php
                  $stmt = $pdo->query("SELECT * FROM perusahaan");
                  while ($row = $stmt->fetch()) {

                    // tentukan warna badge status
                    $status = $row['status'];
                    $badgeClass = 'bg-secondary';
                    if ($status === 'approved') {
                      $badgeClass = 'bg-success';
                    } elseif ($status === 'pending') {
                      $badgeClass = 'bg-warning text-dark';
                    } elseif ($status === 'rejected' || $status === 'ditolak') {
                      $badgeClass = 'bg-danger';
                    }

                    echo '<div class="card flex-shrink-0" style="min-width:280px;">
              <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-2">
                  <div>
                    <h5 class="card-title mb-1">' . htmlspecialchars($row['nama']) . '</h5>
                    <p class="card-text mb-0">' . htmlspecialchars($row['deskripsi']) . '</p>
                  </div>
                  <span class="badge ' . $badgeClass . ' ms-2">'
                      . strtoupper($status) .
                      '</span>
                </div>
              </div>
            </div>';
                  }
                  ?>
                </div>
              </div>

              <div id="mahasiswa" class="col-12 mb-4">
                <h5>Mahasiswa</h5>
                <div class="d-flex gap-3 overflow-auto pb-2">
                  <?php
                  // Ambil 1 pengajuan per mahasiswa, prioritaskan status diterima
                  $stmt = $pdo->query("
                    SELECT
                        m.*,
                        j.nama AS jurusan,
                        pm_sel.id        AS pengajuan_id,
                        pm_sel.status    AS status_pengajuan,
                        p.nama           AS nama_perusahaan
                    FROM mahasiswa m
                    JOIN jurusan j ON m.jurusan_id = j.id
                    LEFT JOIN (
                        SELECT pm1.*
                        FROM pengajuan_magang pm1
                        JOIN (
                            -- pilih id pengajuan terbaik per mahasiswa
                            SELECT
                                mahasiswa_id,
                                MAX(
                                    CASE 
                                        WHEN status = 'diterima' THEN 3
                                        WHEN status = 'ditolak'  THEN 2
                                        ELSE 1
                                    END
                                ) AS prioritas
                            FROM pengajuan_magang
                            GROUP BY mahasiswa_id
                        ) prio
                          ON pm1.mahasiswa_id = prio.mahasiswa_id
                        AND (
                                (status = 'diterima' AND prio.prioritas = 3) OR
                                (status = 'ditolak'  AND prio.prioritas = 2) OR
                                (status NOT IN ('diterima','ditolak') AND prio.prioritas = 1)
                            )
                    ) pm_sel
                      ON pm_sel.mahasiswa_id = m.id
                    LEFT JOIN perusahaan p
                      ON pm_sel.perusahaan_id = p.id
                ");

                  while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

                    // status tampilan
                    $status_text  = 'Belum mengajukan';
                    $status_class = 'bg-secondary';

                    if (!empty($row['status_pengajuan'])) {
                      if ($row['status_pengajuan'] === 'diterima') {
                        $status_text  = 'DITERIMA';
                        $status_class = 'bg-success';
                      } elseif ($row['status_pengajuan'] === 'ditolak') {
                        $status_text  = 'DITOLAK';
                        $status_class = 'bg-danger';
                      } else {
                        $status_text  = strtoupper($row['status_pengajuan']); // misal: pengajuan
                        $status_class = 'bg-warning text-dark';
                      }
                    }

                    echo '<div class="card flex-shrink-0" style="min-width:280px;">
                <div class="card-body">
                  <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                      <h5 class="card-title mb-1">' . htmlspecialchars($row['nama']) . '</h5>
                      <p class="card-text mb-0">' . htmlspecialchars($row['nim']) . '</p>
                      <p class="card-text mb-0">Jurusan: ' . htmlspecialchars($row['jurusan']) . '</p>';

                    if (!empty($row['nama_perusahaan'])) {
                      echo '<p class="card-text mb-0">Perusahaan: ' . htmlspecialchars($row['nama_perusahaan']) . '</p>';
                    }

                    echo        '</div>
                    <span class="badge ' . $status_class . ' ms-2">' . $status_text . '</span>
                  </div>';

                    // jika pengajuan, tampilkan tombol cetak
                    if (!empty($row['pengajuan_id']) && $row['status_pengajuan'] === 'pengajuan') {
                      echo '<a href="admin/buat_surat_pengantar.php?id=' . (int)$row['pengajuan_id'] . '"
                              class="btn btn-sm btn-primary mt-2" target="_blank">
                              Cetak Surat Permohonan Magang
                            </a>
                            <form method="post" class="d-flex gap-2">
                              <input type="hidden" name="pengajuan_id" value="' . intval($row['pengajuan_id']) . '">
                              <button type="submit" name="action" value="reject" class="btn btn-danger btn-sm mt-2">Hapus</button>
                            </form>
                            ';
                    }

                    // jika diterima, tampilkan tombol cetak
                    if (!empty($row['pengajuan_id']) && $row['status_pengajuan'] === 'diterima') {
                      echo '<a href="admin/buat_surat_pelaksanaan.php?id=' . (int)$row['pengajuan_id'] . '"
                      class="btn btn-sm btn-primary mt-2" target="_blank">
                    Cetak Surat Pelaksanaan Magang
                  </a>';
                    }

                    echo '  </div>
              </div>';
                  }
                  ?>
                </div>
              </div>




              <div id="jurusan" class="col-12 mb-4">
                <h5>Jurusan</h5>
                <div class="d-flex gap-3 overflow-auto pb-2">
                  <?php
                  $stmt = $pdo->query("SELECT * FROM jurusan");
                  while ($row = $stmt->fetch()) {
                    echo '<div class="card flex-shrink-0" style="min-width:280px;">
                          <div class="card-body">
                            <h5 class="card-title mb-1">' . $row['nama'] . '</h5>
                            <p class="card-text mb-0">Fakultas: ' . $row['fakultas'] . '</p>
                          </div>
                        </div>';
                  }
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
  <script src="assets/vendor/libs/jquery/jquery.js"></script>
  <script src="assets/vendor/libs/popper/popper.js"></script>
  <script src="assets/vendor/js/bootstrap.js"></script>
  <script src="assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>

  <script src="assets/vendor/js/menu.js"></script>
  <!-- endbuild -->

  <!-- Vendors JS -->
  <script src="assets/vendor/libs/apex-charts/apexcharts.js"></script>

  <!-- Main JS -->
  <script src="assets/js/main.js"></script>

  <!-- Page JS -->
  <script src="assets/js/dashboards-analytics.js"></script>

  <!-- Place this tag in your head or just before your close body tag. -->
  <script async defer src="https://buttons.github.io/buttons.js"></script>
</body>

</html>