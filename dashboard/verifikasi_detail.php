<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 4) {
    header("Location: ../authenticator/auth-login-basic.php");
    exit;
}
include '../koneksi/config.php';

// Ambil ID mahasiswa
$mahasiswa_id = intval($_GET['id'] ?? 0);

// Ambil data jurusan pemilik session
$stmt = $pdo->prepare("SELECT * FROM jurusan WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$jurusan = $stmt->fetch();

// Ambil data mahasiswa dengan verifikasi bahwa mahasiswa tersebut dari jurusan ini
$stmt = $pdo->prepare("
    SELECT m.*, j.nama as nama_jurusan, u.email, u.username
    FROM mahasiswa m
    LEFT JOIN jurusan j ON m.jurusan_id = j.id
    LEFT JOIN users u ON m.user_id = u.id
    WHERE m.id = ? AND m.jurusan_id = ?
");
$stmt->execute([$mahasiswa_id, $jurusan['id']]);
$mahasiswa = $stmt->fetch();

if (!$mahasiswa) {
    echo "Data mahasiswa tidak ditemukan atau bukan dari jurusan Anda.";
    exit;
}
?>
<!DOCTYPE html>
<html lang="id" class="light-style layout-menu-fixed" dir="ltr" data-theme="theme-default" data-assets-path="../assets/" data-template="vertical-menu-template-free">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <title>Detail Verifikasi - Ayo Magang</title>
    <link rel="icon" type="image/x-icon" href="../assets/img/PNP.png" />
    <link rel="stylesheet" href="../assets/vendor/css/core.css" />
    <link rel="stylesheet" href="../assets/vendor/css/theme-default.css" />
    <link rel="stylesheet" href="../assets/css/demo.css" />
</head>
<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            <!-- Menu -->
            <aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
                <div class="app-brand demo">
                    <a href="jurusan_dashboard.php" class="app-brand-link">
                        <span class="app-brand-text demo menu-text fw-bolder ms-2">Ayo Magang</span>
                    </a>
                </div>
                <div class="menu-inner-shadow"></div>
                <ul class="menu-inner py-1">
                    <li class="menu-item">
                        <a href="jurusan_dashboard.php" class="menu-link">
                            <i class="menu-icon tf-icons bx bx-home-circle"></i>
                            <div>Dashboard</div>
                        </a>
                    </li>
                    <li class="menu-item active">
                        <a href="jurusan_dashboard.php#verifikasi_mahasiswa" class="menu-link">
                            <i class="menu-icon tf-icons bx bx-check-circle"></i>
                            <div>Verifikasi Data Mahasiswa</div>
                        </a>
                    </li>
                </ul>
            </aside>

            <!-- Layout page -->
            <div class="layout-page">
                <!-- Content wrapper -->
                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="card mb-4">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <h5 class="mb-0">Detail Data Mahasiswa</h5>
                                        <a href="jurusan_dashboard.php#verifikasi_mahasiswa" class="btn btn-sm btn-outline-secondary">Kembali</a>
                                    </div>
                                    <div class="card-body">
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Nama Lengkap</label>
                                                    <div class="form-control-plaintext"><?php echo htmlspecialchars($mahasiswa['nama']); ?></div>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">NIM</label>
                                                    <div class="form-control-plaintext"><?php echo htmlspecialchars($mahasiswa['nim']); ?></div>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Email</label>
                                                    <div class="form-control-plaintext"><?php echo htmlspecialchars($mahasiswa['email']); ?></div>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Username</label>
                                                    <div class="form-control-plaintext"><?php echo htmlspecialchars($mahasiswa['username']); ?></div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Jurusan</label>
                                                    <div class="form-control-plaintext"><?php echo htmlspecialchars($mahasiswa['nama_jurusan']); ?></div>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Alamat</label>
                                                    <div class="form-control-plaintext"><?php echo htmlspecialchars($mahasiswa['alamat']); ?></div>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Status Verifikasi</label>
                                                    <div>
                                                        <?php
                                                        if ($mahasiswa['status_verifikasi'] === 'pending') {
                                                            echo '<span class="badge bg-warning text-dark">Menunggu Verifikasi</span>';
                                                        } else {
                                                            echo '<span class="badge bg-success">Terverifikasi</span>';
                                                        }
                                                        ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="d-flex gap-2">
                                            <a href="verifikasi_update.php?id=<?php echo $mahasiswa['id']; ?>" class="btn btn-primary">
                                                <i class="bx bx-pencil"></i> Edit Data
                                            </a>
                                            <?php if ($mahasiswa['status_verifikasi'] === 'pending'): ?>
                                            <a href="proses_verifikasi.php?id=<?php echo $mahasiswa['id']; ?>&action=approve" class="btn btn-success" onclick="return confirm('Approve mahasiswa ini?')">
                                                <i class="bx bx-check"></i> Approve
                                            </a>
                                            <?php endif; ?>
                                            <a href="jurusan_dashboard.php#verifikasi_mahasiswa" class="btn btn-outline-secondary">Batal</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/vendor/libs/jquery/jquery.js"></script>
    <script src="../assets/vendor/libs/popper/popper.js"></script>
    <script src="../assets/vendor/js/bootstrap.js"></script>
</body>
</html>
