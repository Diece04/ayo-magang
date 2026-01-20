<?php
include 'koneksi/config.php';

// Redirect to new auth register page
header('Location: authenticator/auth-register-basic.php');
exit;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $email = $_POST['email'];
    $role = $_POST['role'];
    $nama = $_POST['nama'];

    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare("INSERT INTO users (username, password, role, email) VALUES (?, ?, ?, ?)");
        $stmt->execute([$username, $password, $role, $email]);
        $user_id = $pdo->lastInsertId();

        if ($role == 2) { // Mahasiswa
            $jurusan_id = $_POST['jurusan_id'];
            $nim = $_POST['nim'];
            $alamat = $_POST['alamat'];
            $stmt = $pdo->prepare("INSERT INTO mahasiswa (user_id, nama, jurusan_id, nim, alamat) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$user_id, $nama, $jurusan_id, $nim, $alamat]);
        } elseif ($role == 3) { // Perusahaan
            $alamat = $_POST['alamat'];
            $deskripsi = $_POST['deskripsi'];
            $stmt = $pdo->prepare("INSERT INTO perusahaan (user_id, nama, alamat, deskripsi, status) VALUES (?, ?, ?, ?, 'pending')");
            $stmt->execute([$user_id, $nama, $alamat, $deskripsi]);
        }
        $pdo->commit();
        $success = "Registration successful. " . ($role == 3 ? "Await admin approval." : "");
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = "Error: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Register</title>
    <link rel="stylesheet" href="assets/vendor/css/core.css">
    <link rel="stylesheet" href="assets/vendor/css/theme-default.css">
    <link rel="stylesheet" href="assets/css/demo.css">
    <link rel="icon" type="image/x-icon" href="assets/img/PNP.png" />
</head>

<body>
    <div class="container-xxl">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Register</h5>
                        <?php if (isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
                        <?php if (isset($success)) echo "<div class='alert alert-success'>$success</div>"; ?>
                        <form method="post">
                            <div class="mb-3">
                                <label>Username</label>
                                <input type="text" name="username" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label>Password</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label>Email</label>
                                <input type="email" name="email" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label>Role</label>
                                <select name="role" class="form-control" required onchange="toggleFields(this.value)">
                                    <option value="2">Mahasiswa</option>
                                    <option value="3">Perusahaan</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label>Nama</label>
                                <input type="text" name="nama" class="form-control" required>
                            </div>
                            <div id="mahasiswa-fields" style="display: block;">
                                <div class="mb-3">
                                    <label>Jurusan</label>
                                    <select name="jurusan_id" class="form-control">
                                        <?php
                                        $stmt = $pdo->query("SELECT * FROM jurusan");
                                        while ($row = $stmt->fetch()) {
                                            echo "<option value='{$row['id']}'>{$row['nama']}</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label>NIM</label>
                                    <input type="text" name="nim" class="form-control">
                                </div>
                                <div class="mb-3">
                                    <label>Alamat</label>
                                    <textarea name="alamat" class="form-control"></textarea>
                                </div>
                            </div>
                            <div id="perusahaan-fields" style="display: none;">
                                <div class="mb-3">
                                    <label>Alamat</label>
                                    <textarea name="alamat" class="form-control"></textarea>
                                </div>
                                <div class="mb-3">
                                    <label>Deskripsi</label>
                                    <textarea name="deskripsi" class="form-control"></textarea>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary">Register</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="assets/vendor/libs/jquery/jquery.js"></script>
    <script src="assets/vendor/js/bootstrap.js"></script>
    <script>
        function toggleFields(role) {
            document.getElementById('mahasiswa-fields').style.display = role == 2 ? 'block' : 'none';
            document.getElementById('perusahaan-fields').style.display = role == 3 ? 'block' : 'none';
        }
    </script>
</body>

</html>