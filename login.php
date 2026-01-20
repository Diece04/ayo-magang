<?php
session_start();
include 'koneksi/config.php';

// Check if users table exists
try {
    $stmt = $pdo->query("SELECT 1 FROM users LIMIT 1");
} catch (PDOException $e) {
    if ($e->getCode() == '42S02') { // Table doesn't exist
        header("Location: setup.php");
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    
    if (!$user) {
        $error = "Username salah";
    } else {
        $stored = $user['password'];
        $password_ok = false;

        // If stored password looks like a bcrypt/argon hash use password_verify
        if (strpos($stored, '$2y$') === 0 || strpos($stored, '$2a$') === 0 || strpos($stored, '$argon') === 0) {
            if (password_verify($password, $stored)) {
                $password_ok = true;
                // optionally rehash if algorithm/params changed
                if (password_needs_rehash($stored, PASSWORD_DEFAULT)) {
                    $newHash = password_hash($password, PASSWORD_DEFAULT);
                    $upd = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                    $upd->execute([$newHash, $user['id']]);
                }
            }
        } else {
            // Fallback: stored password appears to be plain text (existing DB). Compare directly.
            if ($password === $stored) {
                $password_ok = true;
                // Migrate to hashed password
                $newHash = password_hash($password, PASSWORD_DEFAULT);
                $upd = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                $upd->execute([$newHash, $user['id']]);
            }
        }

        if (!$password_ok) {
            $error = "Password salah";
        } else {
            // existing role / approval logic
            if ($user['role'] == 3) {
                $stmt = $pdo->prepare("SELECT status FROM perusahaan WHERE user_id = ?");
                $stmt->execute([$user['id']]);
                $statusRow = $stmt->fetch();
                $status = $statusRow ? $statusRow['status'] : null;
                if ($status != 'approved') {
                    $error = "Account pending approval.";
                } else {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['role'] = $user['role'];
                    header("Location: " . ($user['role'] == 1 ? 'index.php' : ($user['role'] == 2 ? 'dashboard/mahasiswa_dashboard.php' : ($user['role'] == 3 ? 'dashboard/perusahaan_dashboard.php' : 'dashboard/jurusan_dashboard.php'))));
                    exit;
                }
            } else {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['role'] = $user['role'];
                header("Location: " . ($user['role'] == 1 ? 'index.php' : ($user['role'] == 2 ? 'dashboard/mahasiswa_dashboard.php' : ($user['role'] == 3 ? 'dashboard/perusahaan_dashboard.php' : 'dashboard/jurusan_dashboard.php'))));
                exit;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" href="assets/vendor/css/core.css">
    <link rel="stylesheet" href="assets/vendor/css/theme-default.css">
    <link rel="stylesheet" href="assets/css/demo.css">
</head>
<body>
    <div class="container-xxl">
        <div class="row justify-content-center">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Login</h5>
                        <?php if (isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
                        <form method="post">
                            <div class="mb-3">
                                <label>Username</label>
                                <input type="text" name="username" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label>Password</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Login</button>
                        </form>
                        <p class="mt-3">Don't have an account? <a href="register.php">Register here</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="assets/vendor/js/bootstrap.js"></script>
</body>
</html>
