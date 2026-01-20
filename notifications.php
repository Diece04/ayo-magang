<?php
session_start();
include 'koneksi/config.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
  header("Location: authenticator/auth-login-basic.php");
  exit;
}

// Handle AJAX kirim surat
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'kirim_surat') {
  $surat_id = intval($_POST['surat_id']);
  $stmt = $pdo->prepare("UPDATE surat SET status = 'sudah dibuat' WHERE id = ?");
  $success = $stmt->execute([$surat_id]);
  header('Content-Type: application/json');
  echo json_encode(['success' => $success]);
  exit;
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

$notifications = [];

if ($role == 1) { // Admin
  $stmt = $pdo->query("SELECT * FROM perusahaan WHERE status = 'pending'");
  $notifications = $stmt->fetchAll();
} elseif ($role == 2) { // Mahasiswa
  $stmt = $pdo->prepare("
    SELECT 
        s.*, 
        p.nama   AS perusahaan_nama, 
        p.alamat AS perusahaan_alamat
    FROM surat s
    JOIN perusahaan p ON s.perusahaan_id = p.id
    WHERE s.mahasiswa_id = (
        SELECT id FROM mahasiswa WHERE user_id = ?
    )
    ORDER BY 
        FIELD(s.status, 'belum dibuat', 'sudah dibuat'), 
        s.tanggal_surat DESC
  ");
  $stmt->execute([$user_id]);
  $surat = $stmt->fetchAll();
  $notifications = array_merge($surat);
} elseif ($role == 3) { // Perusahaan
  $stmt = $pdo->prepare("SELECT 'pengajuan' as type, pm.id, m.nama as nama FROM pengajuan_magang pm JOIN mahasiswa m ON pm.mahasiswa_id = m.id WHERE pm.perusahaan_id = (SELECT id FROM perusahaan WHERE user_id = ?) AND pm.status = 'permohonan'");
  $stmt->execute([$user_id]);
  $notifications = array_merge($notifications ?? [], $stmt->fetchAll());

  $stmt2 = $pdo->prepare("SELECT 'kerja_sama' as type, ks.id, j.nama as nama FROM kerja_sama ks JOIN jurusan j ON ks.jurusan_id = j.id WHERE ks.perusahaan_id = (SELECT id FROM perusahaan WHERE user_id = ?) AND ks.status = 'proposed'");
  $stmt2->execute([$user_id]);
  $notifications = array_merge($notifications, $stmt2->fetchAll());
} elseif ($role == 4) { // Jurusan
  $stmt = $pdo->prepare("SELECT DISTINCT pm.*, m.nama as mahasiswa, p.nama as perusahaan FROM pengajuan_magang pm JOIN mahasiswa m ON pm.mahasiswa_id = m.id JOIN perusahaan p ON pm.perusahaan_id = p.id WHERE m.jurusan_id = (SELECT id FROM jurusan WHERE user_id = ?)");
  $stmt->execute([$user_id]);
  $notifications = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en" class="light-style layout-menu-fixed" dir="ltr" data-theme="theme-default" data-assets-path="assets/" data-template="vertical-menu-template-free">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
  <title>Notifications - Ayo Magang</title>
  <!-- Fonts & CSS sama seperti sebelumnya -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="assets/vendor/fonts/boxicons.css" />
  <link rel="stylesheet" href="assets/vendor/css/core.css" class="template-customizer-core-css" />
  <link rel="stylesheet" href="assets/vendor/css/theme-default.css" class="template-customizer-theme-css" />
  <link rel="stylesheet" href="assets/css/demo.css" />
  <link rel="stylesheet" href="assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" />
  <link rel="icon" type="image/x-icon" href="assets/img/PNP.png" />
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
            <a href="<?php echo ($role == 1 ? 'index.php' : ($role == 2 ? 'dashboard/mahasiswa_dashboard.php' : ($role == 3 ? 'dashboard/perusahaan_dashboard.php' : 'dashboard/jurusan_dashboard.php'))); ?>" class="menu-link">
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

        <div class="content-wrapper">
          <div class="container-xxl flex-grow-1 container-p-y">
            <h4 class="mb-4">Notifikasi <span class="badge bg-primary"><?= count($notifications) ?></span></h4>

            <?php if (empty($notifications)): ?>
              <div class="row">
                <div class="col-12">
                  <div class="card text-center py-5">
                    <div class="card-body">
                      <i class="bx bx-bell fs-1 text-muted mb-3"></i>
                      <h5 class="text-muted">Tidak ada notifikasi</h5>
                      <p class="text-muted mb-0">Semua proses berjalan lancar.</p>
                    </div>
                  </div>
                </div>
              </div>
            <?php else: ?>
              <div class="row">
                <?php foreach ($notifications as $notif): ?>

                  <?php if ($role == 2 && isset($notif['status']) && $notif['status'] == 'belum dibuat'): ?>
                    <!-- CARD SURAT BELUM DIBUAT - PRIORITAS TINGGI -->
                    <div class="col-xl-6 col-md-12 mb-4">
                      <div class="card border-warning shadow-lg animate__animated animate__pulse animate__infinite">
                        <div class="card-header bg-gradient-warning text-white">
                          <div class="d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">
                              <i class="bx bx-file-warning bx-sm me-2"></i>
                              Surat Pengantar Belum Dikirim
                            </h6>
                            <span class="badge bg-light text-warning fs-2x">!</span>
                          </div>
                        </div>
                        <div class="card-body">
                          <div class="d-flex mb-3">
                            <div class="flex-shrink-0">
                              <i class="bx bx-building-house text-primary fs-3"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                              <h6 class="mb-1"><?= htmlspecialchars($notif['perusahaan_nama']) ?></h6>
                              <small class="text-muted"><?= htmlspecialchars($notif['perusahaan_alamat']) ?></small>
                            </div>
                          </div>

                          <div class="row mb-3">
                            <div class="col-6">
                              <small class="text-muted">No. Surat:</small><br>
                              <strong><?= htmlspecialchars($notif['no_surat']) ?></strong>
                            </div>
                            <div class="col-6">
                              <small class="text-muted">Waktu Magang:</small><br>
                              <strong><?= htmlspecialchars($notif['waktu_magang']) ?></strong>
                            </div>
                          </div>

                          <div class="btn-group w-100" role="group">
                            <a href="admin/view_surat_pengantar.php?id=<?= $notif['id'] ?>&mode=view"
                              class="btn btn-outline-primary flex-fill me-1">
                              <i class="bx bx-eye me-1"></i>Lihat Surat
                            </a>
                            <button class="btn btn-warning flex-fill ms-1" onclick="kirimSurat(<?= $notif['id'] ?>)">
                              <i class="bx bx-send me-1"></i>Kirim ke Perusahaan
                            </button>
                          </div>

                          <div class="mt-3 text-center">
                            <small class="text-muted">Klik "Kirim" untuk mengirim surat secara resmi</small>
                          </div>
                        </div>
                      </div>
                    </div>

                  <?php elseif ($role == 2): ?>
                    <!-- CARD PENGJUAN BIASA -->
                    <div class="col-xl-6 col-md-12 mb-4">
                      <div class="card border-warning shadow-lg animate__animated animate__pulse animate__infinite">
                        <div class="card-header bg-gradient-warning text-white">
                          <div class="d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">
                              <i class="bx bx-file-warning bx-sm me-2"></i>
                              Surat Pengantar Sudah Dikirim
                            </h6>
                            <span class="badge bg-light text-warning fs-2x">!</span>
                          </div>
                        </div>
                        <div class="card-body">
                          <div class="d-flex mb-3">
                            <div class="flex-shrink-0">
                              <i class="bx bx-building-house text-primary fs-3"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                              <h6 class="mb-1"><?= htmlspecialchars($notif['perusahaan_nama']) ?></h6>
                              <small class="text-muted"><?= htmlspecialchars($notif['perusahaan_alamat']) ?></small>
                            </div>
                          </div>

                          <div class="row mb-3">
                            <div class="col-6">
                              <small class="text-muted">No. Surat:</small><br>
                              <strong><?= htmlspecialchars($notif['no_surat']) ?></strong>
                            </div>
                            <div class="col-6">
                              <small class="text-muted">Waktu Magang:</small><br>
                              <strong><?= htmlspecialchars($notif['waktu_magang']) ?></strong>
                            </div>
                          </div>

                          <div class="btn-group w-100" role="group">
                            <a href="admin/view_surat_pengantar.php?id=<?= $notif['id'] ?>&mode=view"
                              class="btn btn-outline-primary flex-fill me-1">
                              <i class="bx bx-eye me-1"></i>Lihat Surat
                            </a>
                            <button class="btn btn-success flex-fill ms-1 disabled" onclick="kirimSurat(<?= $notif['id'] ?>)">
                              <i class="bx bx-send me-1"></i>Sudah Dikirim
                            </button>
                          </div>

                          <div class="mt-3 text-center">
                          </div>
                        </div>
                      </div>
                    </div>

                  <?php else: ?>
                    <!-- ROLE LAIN -->
                    <div class="col-md-12 mb-3">
                      <div class="card">
                        <div class="card-body">
                          <?php if ($role == 1): ?>
                            <h6>Perusahaan Baru: <?= htmlspecialchars($notif['nama']) ?></h6>
                          <?php elseif ($role == 3): ?>
                            <h6><?= ucfirst($notif['type']) ?>: <?= htmlspecialchars($notif['nama']) ?></h6>
                          <?php elseif ($role == 4): ?>
                            <h6><?= htmlspecialchars($notif['mahasiswa']) ?> → <?= htmlspecialchars($notif['perusahaan']) ?></h6>
                          <?php endif; ?>
                        </div>
                      </div>
                    </div>
                  <?php endif; ?>

                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Scripts -->
  <script src="assets/vendor/libs/jquery/jquery.js"></script>
  <script src="assets/vendor/libs/popper/popper.js"></script>
  <script src="assets/vendor/js/bootstrap.js"></script>
  <script src="assets/js/main.js"></script>

  <script>
    function kirimSurat(suratId) {
      if (confirm('Kirim surat pengantar ini ke perusahaan?\n\nSetelah dikirim, status akan berubah menjadi "sudah dibuat".')) {
        const btn = event.target.closest('button');
        btn.innerHTML = '<i class="bx bx-loader-alt bx-spin me-1"></i>Mengirim...';
        btn.disabled = true;

        fetch(window.location.href, {
            method: 'POST',
            headers: {
              'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=kirim_surat&surat_id=${suratId}`
          })
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              location.reload();
            } else {
              alert('Gagal mengirim surat. Coba lagi.');
              btn.innerHTML = '<i class="bx bx-send me-1"></i>Kirim ke Perusahaan';
              btn.disabled = false;
            }
          })
          .catch(() => {
            alert('Error koneksi');
            btn.innerHTML = '<i class="bx bx-send me-1"></i>Kirim ke Perusahaan';
            btn.disabled = false;
          });
      }
    }
  </script>
</body>

</html>