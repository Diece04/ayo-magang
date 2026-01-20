<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 4) {
    header("Location: ../authenticator/auth-login-basic.php");
    exit;
}
include '../koneksi/config.php';

// Ambil ID mahasiswa dan action
$mahasiswa_id = intval($_GET['id'] ?? 0);
$action = trim($_GET['action'] ?? '');

// Ambil data jurusan pemilik session
$stmt = $pdo->prepare("SELECT * FROM jurusan WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$jurusan = $stmt->fetch();

// Ambil data mahasiswa dengan verifikasi bahwa mahasiswa tersebut dari jurusan ini
$stmt = $pdo->prepare("
    SELECT m.id, m.user_id
    FROM mahasiswa m
    WHERE m.id = ? AND m.jurusan_id = ?
");
$stmt->execute([$mahasiswa_id, $jurusan['id']]);
$mahasiswa = $stmt->fetch();

if (!$mahasiswa) {
    die('Data mahasiswa tidak ditemukan atau bukan dari jurusan Anda.');
}

try {
    if ($action === 'approve') {
        // Update status verifikasi mahasiswa menjadi approved
        $stmt = $pdo->prepare("
            UPDATE mahasiswa 
            SET status_verifikasi = 'approved' 
            WHERE id = ?
        ");
        $stmt->execute([$mahasiswa_id]);
        
        $_SESSION['message'] = 'Mahasiswa berhasil diverifikasi!';
        $_SESSION['message_type'] = 'success';
    } else {
        $_SESSION['message'] = 'Aksi tidak valid.';
        $_SESSION['message_type'] = 'warning';
    }
} catch (Exception $e) {
    $_SESSION['message'] = 'Terjadi kesalahan: ' . $e->getMessage();
    $_SESSION['message_type'] = 'danger';
}

// Redirect kembali ke dashboard
header('Location: jurusan_dashboard.php#verifikasi_mahasiswa');
exit;
