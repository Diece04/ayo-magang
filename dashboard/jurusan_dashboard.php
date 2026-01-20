<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 4) {
  header("Location: ../authenticator/auth-login-basic.php");
  exit;
}
include '../koneksi/config.php';
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM jurusan WHERE user_id = ?");
$stmt->execute([$user_id]);
$jurusan = $stmt->fetch();

$stmtUsers = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmtUsers->execute([$user_id]);
$users = $stmtUsers->fetch();

// Handle ajukan kerja sama
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajukan_kerja_sama'])) {
  $perusahaan_id = intval($_POST['perusahaan_id']);
  $ins = $pdo->prepare("INSERT INTO kerja_sama (perusahaan_id, jurusan_id, status) VALUES (?, ?, 'proposed')");
  $ins->execute([$perusahaan_id, $jurusan['id']]);
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

// Add query for notification count (new applications from students in this jurusan)
$stmtNotif = $pdo->prepare("SELECT COUNT(DISTINCT pm.mahasiswa_id) as total FROM pengajuan_magang pm JOIN mahasiswa m ON pm.mahasiswa_id = m.id WHERE m.jurusan_id = ?");
$stmtNotif->execute([$jurusan['id']]);
$notif_count = $stmtNotif->fetch()['total'];

// Ambil pesan dari session jika ada
$message = '';
$message_type = '';
if (isset($_SESSION['message'])) {
  $message = $_SESSION['message'];
  $message_type = $_SESSION['message_type'] ?? 'info';
  unset($_SESSION['message']);
  unset($_SESSION['message_type']);
}
?>

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
          <a href="../index.php" class="app-brand-link">
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
            <a href="../index.php" class="menu-link">
              <i class="menu-icon tf-icons bx bx-home-circle"></i>
              <div data-i18n="Analytics">Dashboard</div>
            </a>
          </li>
          <!-- New Menus -->
          <li class="menu-item">
            <a href="#verifikasi_mahasiswa" class="menu-link">
              <i class="menu-icon tf-icons bx bx-check-circle"></i>
              <div>Verifikasi Data Mahasiswa</div>
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
          <li class="menu-item">
            <a href="#ajukan_kerja_sama" class="menu-link">
              <i class="menu-icon tf-icons bx bx-handshake"></i>
              <div>Ajukan Kerja Sama</div>
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
                  aria-label="Star themeselection/sneat-html-admin-template-free on GitHub">Jurusan</a>
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
                          <span class="fw-semibold d-block"><?php echo htmlspecialchars(string: $jurusan['nama']); ?></span>
                          <small class="text-muted"><?php echo htmlspecialchars(string: $users['username']); ?></small>
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
            <?php if ($message): ?>
              <div class="alert alert-<?php echo htmlspecialchars($message_type); ?> alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              </div>
            <?php endif; ?>

            <div class="row">
              <div class="col-lg-12 mb-4 order-0">
                <div class="card">
                  <div class="d-flex align-items-end row">
                    <div class="col-sm-7">
                      <div class="card-body">
                        <h5 class="card-title text-primary">Selamat Datang, <?php echo htmlspecialchars(string: $jurusan['nama']); ?>!</h5>
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
              <div id="verifikasi_mahasiswa" class="col-12 mb-4">
                <h5>Verifikasi Data Mahasiswa</h5>
                <div class="card">
                  <div class="table-responsive text-nowrap">
                    <table class="table table-hover">
                      <thead>
                        <tr>
                          <th>Nama</th>
                          <th>NIM</th>
                          <th>Status</th>
                          <th>Aksi</th>
                        </tr>
                      </thead>
                      <tbody class="table-border-bottom-0">
                        <?php
                        // Ambil data mahasiswa dari jurusan ini
                        $stmt = $pdo->prepare("
                          SELECT m.*, j.nama as nama_jurusan 
                          FROM mahasiswa m 
                          LEFT JOIN jurusan j ON m.jurusan_id = j.id 
                          WHERE m.jurusan_id = ?
                          ORDER BY m.status_verifikasi ASC, m.id DESC
                        ");
                        $stmt->execute([$jurusan['id']]);

                        if ($stmt->rowCount() == 0) {
                          echo '<tr><td colspan="4" class="text-center py-3">Tidak ada mahasiswa</td></tr>';
                        } else {
                          while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            $status_badge = '';
                            if ($row['status_verifikasi'] === 'pending') {
                              $status_badge = '<span class="badge bg-warning text-dark">Menunggu Verifikasi</span>';
                            } else {
                              $status_badge = '<span class="badge bg-success">Terverifikasi</span>';
                            }

                            echo '<tr>
                              <td><strong>' . htmlspecialchars($row['nama']) . '</strong></td>
                              <td>' . htmlspecialchars($row['nim']) . '</td>
                              <td>' . $status_badge . '</td>
                              <td>
                                <div class="dropdown">
                                  <button type="button" class="btn btn-sm btn-icon btn-text-secondary rounded-pill dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                    <i class="bx bx-dots-vertical-rounded"></i>
                                  </button>
                                  <div class="dropdown-menu dropdown-menu-end m-0">
                                    <a href="verifikasi_detail.php?id=' . $row['id'] . '" class="dropdown-item">
                                      <i class="bx bx-show me-2"></i>Lihat Detail
                                    </a>
                                    <a href="verifikasi_update.php?id=' . $row['id'] . '" class="dropdown-item">
                                      <i class="bx bx-pencil me-2"></i>Edit Data
                                    </a>';

                            if ($row['status_verifikasi'] === 'pending') {
                              echo '<a href="proses_verifikasi.php?id=' . $row['id'] . '&action=approve" class="dropdown-item" onclick="return confirm(\'Approve mahasiswa ini?\')">
                                        <i class="bx bx-check me-2"></i>Approve
                                      </a>';
                            }

                            echo '</div>
                                </div>
                              </td>
                            </tr>';
                          }
                        }
                        ?>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>

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

                    echo '  </div>
              </div>';
                  }
                  ?>
                </div>
              </div>
              <div id="jurusan" class="col-12 mb-4">
                <h5>Jurusan</h5>
                <div class="row">
                  <?php
                  $stmt = $pdo->query("SELECT * FROM jurusan");
                  while ($row = $stmt->fetch()) {
                    echo '<div class="col-md-4 mb-4">
                        <div class="card">
                          <div class="card-body">
                            <h5 class="card-title">' . $row['nama'] . '</h5>
                            <p class="card-text">Fakultas: ' . $row['fakultas'] . '</p>
                          </div>
                        </div>
                      </div>';
                  }
                  ?>
                </div>
              </div>
              <div id="ajukan_kerja_sama" class="col-12 mb-4">
                <h5>Ajukan Kerja Sama</h5>
                <form method="post">
                  <div class="mb-3">
                    <label for="perusahaan_id" class="form-label">Pilih Perusahaan</label>
                    <select class="form-select" id="perusahaan_id" name="perusahaan_id" required>
                      <?php
                      $stmt = $pdo->query("SELECT * FROM perusahaan WHERE status='approved'");
                      while ($row = $stmt->fetch()) {
                        echo '<option value="' . $row['id'] . '">' . htmlspecialchars($row['nama']) . '</option>';
                      }
                      ?>
                    </select>
                  </div>
                  <button type="submit" name="ajukan_kerja_sama" class="btn btn-primary">Ajukan Kerja Sama</button>
                </form>
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