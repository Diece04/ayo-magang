<?php
$host = 'localhost';
$dbname = 'ayo_magang';
$username = 'root'; // Adjust as needed
$password = ''; // Adjust as needed

try {
    // Connect without specifying database to create it
    $pdo = new PDO("mysql:host=$host", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Drop and recreate database
    $pdo->exec("DROP DATABASE IF EXISTS `$dbname`");
    $pdo->exec("CREATE DATABASE `$dbname`");
    $pdo->exec("USE `$dbname`");
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Create tables
$tables = [
    "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        role TINYINT NOT NULL CHECK (role IN (1,2,3,4)),
        email VARCHAR(100) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",
    "CREATE TABLE IF NOT EXISTS jurusan (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        nama VARCHAR(100) NOT NULL,
        fakultas VARCHAR(100) NOT NULL,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )",
    "CREATE TABLE IF NOT EXISTS perusahaan (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        nama VARCHAR(100) NOT NULL,
        alamat TEXT,
        deskripsi TEXT,
        status ENUM('pending', 'approved') DEFAULT 'approved',
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )",
    "CREATE TABLE IF NOT EXISTS mahasiswa (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        nama VARCHAR(100) NOT NULL,
        jurusan_id INT NOT NULL,
        nim VARCHAR(20) UNIQUE NOT NULL,
        alamat TEXT,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (jurusan_id) REFERENCES jurusan(id) ON DELETE CASCADE
    )",
    "CREATE TABLE IF NOT EXISTS kerja_sama (
        id INT AUTO_INCREMENT PRIMARY KEY,
        jurusan_id INT NOT NULL,
        perusahaan_id INT NOT NULL,
        FOREIGN KEY (jurusan_id) REFERENCES jurusan(id) ON DELETE CASCADE,
        FOREIGN KEY (perusahaan_id) REFERENCES perusahaan(id) ON DELETE CASCADE
    )",
    "CREATE TABLE IF NOT EXISTS pengajuan_magang (
        id INT AUTO_INCREMENT PRIMARY KEY,
        mahasiswa_id INT NOT NULL,
        perusahaan_id INT NOT NULL,
        status ENUM('pengajuan', 'permohonan', 'diterima', 'ditolak') DEFAULT 'pengajuan',
        tanggal_pengajuan TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (mahasiswa_id) REFERENCES mahasiswa(id) ON DELETE CASCADE,
        FOREIGN KEY (perusahaan_id) REFERENCES perusahaan(id) ON DELETE CASCADE
    )"
];

foreach ($tables as $sql) {
    $pdo->exec($sql);
}

// Insert sample data
$sampleData = [
    // Users
    "INSERT INTO users (username, password, role, email) VALUES 
        ('admin', '" . password_hash('admin123', PASSWORD_DEFAULT) . "', 1, 'admin@example.com'),
        ('mahasiswa1', '" . password_hash('pass123', PASSWORD_DEFAULT) . "', 2, 'mahasiswa1@example.com'),
        ('perusahaan1', '" . password_hash('pass123', PASSWORD_DEFAULT) . "', 3, 'perusahaan1@example.com'),
        ('jurusan1', '" . password_hash('pass123', PASSWORD_DEFAULT) . "', 4, 'jurusan1@example.com')",
    
    // Jurusan
    "INSERT INTO jurusan (user_id, nama, fakultas) VALUES 
        (4, 'Teknik Informatika', 'Fakultas Teknik')",
    
    // Perusahaan
    "INSERT INTO perusahaan (user_id, nama, alamat, deskripsi) VALUES 
        (3, 'PT ABC', 'Jakarta', 'Perusahaan teknologi')",
    
    // Mahasiswa
    "INSERT INTO mahasiswa (user_id, nama, jurusan_id, nim, alamat) VALUES 
        (2, 'John Doe', 1, '12345678', 'Bandung')",
    
    // Kerja Sama
    "INSERT INTO kerja_sama (jurusan_id, perusahaan_id) VALUES (1, 1)",
    
    // Pengajuan Magang
    "INSERT INTO pengajuan_magang (mahasiswa_id, perusahaan_id, status) VALUES 
        (1, 1, 'pengajuan')"
];

foreach ($sampleData as $sql) {
    try {
        $pdo->exec($sql);
    } catch (PDOException $e) {
        echo "Error inserting sample data: " . $e->getMessage() . "<br>";
    }
}

echo "Database setup complete. Sample data inserted. Delete this file after use.";
?>
