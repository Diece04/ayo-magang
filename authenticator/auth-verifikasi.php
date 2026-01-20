<?php
session_start();
include '../koneksi/config.php';

// Jika tidak login atau bukan mahasiswa, redirect ke login
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 2) {
    header('Location: auth-login-basic.php');
    exit;
}

// Ambil data mahasiswa untuk cek apakah sudah approved atau masih pending
$stmt = $pdo->prepare("SELECT m.*, j.nama as nama_jurusan FROM mahasiswa m 
                      LEFT JOIN jurusan j ON m.jurusan_id = j.id 
                      WHERE m.user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$mahasiswa = $stmt->fetch(PDO::FETCH_ASSOC);

// Jika sudah approved, redirect ke dashboard
if ($mahasiswa && $mahasiswa['status_verifikasi'] === 'approved') {
    header('Location: ../dashboard/mahasiswa_dashboard.php');
    exit;
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: auth-login-basic.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="id" class="light-style" dir="ltr" data-theme="theme-default" data-assets-path="../assets/" data-template="vertical-menu-template-free">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <title>Verifikasi Data - Ayo Magang</title>
    <link rel="icon" type="image/x-icon" href="../assets/img/PNP.png" />
    <link rel="stylesheet" href="../assets/vendor/css/core.css" />
    <link rel="stylesheet" href="../assets/vendor/css/theme-default.css" />
    <link rel="stylesheet" href="../assets/css/demo.css" />
    <style>
        .verification-wrapper {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
        }

        .verification-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            padding: 40px;
            max-width: 500px;
            width: 100%;
            text-align: center;
        }

        .verification-icon {
            font-size: 80px;
            margin-bottom: 20px;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.6;
            }
        }

        .verification-title {
            font-size: 24px;
            font-weight: 600;
            color: #333;
            margin-bottom: 15px;
        }

        .verification-message {
            font-size: 16px;
            color: #666;
            margin-bottom: 15px;
            line-height: 1.6;
        }

        .verification-contact {
            background: #f5f5f5;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            color: #555;
            font-size: 14px;
        }

        .student-info {
            text-align: left;
            background: #f9f9f9;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid #667eea;
        }

        .student-info-item {
            margin: 8px 0;
            font-size: 14px;
        }

        .student-info-item strong {
            color: #333;
        }

        .info-label {
            color: #666;
        }

        .logout-btn {
            margin-top: 20px;
            display: inline-block;
        }

        .logout-btn a {
            background: #667eea;
            color: white;
            padding: 10px 25px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 14px;
            transition: background 0.3s;
        }

        .logout-btn a:hover {
            background: #764ba2;
        }
    </style>
</head>

<body>
    <div class="verification-wrapper">
        <div class="verification-card">
            <div class="verification-icon">⏳</div>

            <div class="verification-title">Menunggu Verifikasi Data</div>

            <div class="verification-message">
                Menunggu Verifikasi Data Oleh Jurusan<br>
                <span style="font-size: 14px; color: #999;">Mohon untuk menunggu sebelum bisa memasuki dashboard</span>
            </div>

            <?php if ($mahasiswa): ?>
                <div class="student-info">
                    <div class="student-info-item">
                        <strong>Nama:</strong> <span class="info-label"><?php echo htmlspecialchars($mahasiswa['nama']); ?></span>
                    </div>
                    <div class="student-info-item">
                        <strong>NIM:</strong> <span class="info-label"><?php echo htmlspecialchars($mahasiswa['nim']); ?></span>
                    </div>
                    <div class="student-info-item">
                        <strong>Jurusan:</strong> <span class="info-label"><?php echo htmlspecialchars($mahasiswa['nama_jurusan'] ?? 'Belum ditentukan'); ?></span>
                    </div>
                </div>
            <?php endif; ?>

            <div class="verification-contact">
                <strong>ℹ️ Informasi</strong><br>
                Hubungi jurusan jika perlu
            </div>

            <div class="logout-btn">
                <a href="auth-verifikasi.php?logout=1">Logout</a>
            </div>
        </div>
    </div>

    <script src="../assets/vendor/libs/jquery/jquery.js"></script>
    <script src="../assets/vendor/libs/popper/popper.js"></script>
    <script src="../assets/vendor/js/bootstrap.js"></script>
</body>

</html>