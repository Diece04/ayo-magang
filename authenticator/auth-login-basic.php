<?php
session_start();
include '../koneksi/config.php';

// jika sudah login, redirect sesuai role
if (isset($_SESSION['user_id']) && isset($_SESSION['role'])) {
    switch (intval($_SESSION['role'])) {
        case 1: header('Location: ../index.php'); exit;
        case 2: header('Location: ../dashboard/mahasiswa_dashboard.php'); exit;
        case 3: header('Location: ../dashboard/perusahaan_dashboard.php'); exit;
        case 4: header('Location: ../dashboard/jurusan_dashboard.php'); exit;
    }
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = "Lengkapi username dan password.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            $error = "Username salah";
        } else {
            $stored = $user['password'];
            $password_ok = false;

            // verify hashed password or fallback to plain-text (migrate to hash)
            if (strpos($stored, '$2y$') === 0 || strpos($stored, '$2a$') === 0 || strpos($stored, '$argon') === 0) {
                if (password_verify($password, $stored)) {
                    $password_ok = true;
                    if (password_needs_rehash($stored, PASSWORD_DEFAULT)) {
                        $newHash = password_hash($password, PASSWORD_DEFAULT);
                        $pdo->prepare("UPDATE users SET password = ? WHERE id = ?")->execute([$newHash, $user['id']]);
                    }
                }
            } else {
                if ($password === $stored) {
                    $password_ok = true;
                    $newHash = password_hash($password, PASSWORD_DEFAULT);
                    $pdo->prepare("UPDATE users SET password = ? WHERE id = ?")->execute([$newHash, $user['id']]);
                }
            }

            if (!$password_ok) {
                $error = "Password salah";
            } else {
                // For mahasiswa: check verification status BEFORE creating session
                if (intval($user['role']) === 2) {
                    $stmt2 = $pdo->prepare("SELECT status_verifikasi FROM mahasiswa WHERE user_id = ?");
                    $stmt2->execute([$user['id']]);
                    $statusRow = $stmt2->fetch(PDO::FETCH_ASSOC);
                    $status = $statusRow['status_verifikasi'] ?? 'pending';
                    if ($status !== 'approved') {
                        session_regenerate_id(true);
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['role'] = $user['role'];
                        header("Location: auth-verifikasi.php");
                        exit;
                    } else {
                        session_regenerate_id(true);
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['role'] = $user['role'];
                        header("Location: ../dashboard/mahasiswa_dashboard.php");
                        exit;
                    }
                } elseif (intval($user['role']) === 3) {
                    // For perusahaan: check approval BEFORE creating session
                    $stmt2 = $pdo->prepare("SELECT status FROM perusahaan WHERE user_id = ?");
                    $stmt2->execute([$user['id']]);
                    $statusRow = $stmt2->fetch(PDO::FETCH_ASSOC);
                    $status = $statusRow['status'] ?? null;
                    if ($status !== 'approved') {
                        $error = "Account pending approval.";
                    } else {
                        session_regenerate_id(true);
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['role'] = $user['role'];
                        header("Location: ../dashboard/perusahaan_dashboard.php");
                        exit;
                    }
                } else {
                    session_regenerate_id(true);
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['role'] = $user['role'];
                    switch (intval($user['role'])) {
                        case 1: header("Location: ../index.php"); exit;
                        case 4: header("Location: ../dashboard/jurusan_dashboard.php"); exit;
                        default:
                            header("Location: auth-login-basic.php"); exit;
                    }
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en" class="light-style customizer-hide" dir="ltr" data-theme="theme-default" data-assets-path="../assets/" data-template="vertical-menu-template-free">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Login</title>
    <link rel="icon" type="image/x-icon" href="../assets/img/PNP.png" />
    <link rel="stylesheet" href="../assets/vendor/css/core.css" />
    <link rel="stylesheet" href="../assets/vendor/css/theme-default.css" />
    <link rel="stylesheet" href="../assets/css/demo.css" />
    <link rel="stylesheet" href="../assets/vendor/css/pages/page-auth.css" />
    <script src="../assets/vendor/js/helpers.js"></script>
    <script src="../assets/js/config.js"></script>
  </head>
  <body>
    <div class="container-xxl">
      <div class="authentication-wrapper authentication-basic container-p-y">
        <div class="authentication-inner">
          <div class="card">
            <div class="card-body">
              <div class="app-brand justify-content-center">
                <a href="#" class="app-brand-link gap-2">
                  <span class="app-brand-logo demo">
                    <!-- ...existing logo svg... -->
                  </span>
                  <span class="app-brand-text demo text-body fw-bolder">Ayo Magang</span>
                </a>
              </div>

              <h4 class="mb-2">Selamat Datang Di Ayo Magang!</h4>
              <p class="mb-4">Silahkan login untuk melanjutkan</p>

              <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
              <?php endif; ?>

              <form id="formAuthentication" class="mb-3" action="" method="POST">
                <div class="mb-3">
                  <label for="username" class="form-label">Username</label>
                  <input type="text" class="form-control" id="username" name="username" placeholder="Enter your username" autofocus />
                </div>

                <div class="mb-3 form-password-toggle">
                  <div class="d-flex justify-content-between">
                    <label class="form-label" for="password">Password</label>
                    <a href="auth-forgot-password-basic.html"><small>Forgot Password?</small></a>
                  </div>
                  <div class="input-group input-group-merge">
                    <input type="password" id="password" class="form-control" name="password" placeholder="••••••••••" aria-describedby="password" />
                    <span class="input-group-text cursor-pointer"><i class="bx bx-hide"></i></span>
                  </div>
                </div>

                <div class="mb-3">
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="remember-me" />
                    <label class="form-check-label" for="remember-me"> Remember Me </label>
                  </div>
                </div>

                <div class="mb-3">
                  <button class="btn btn-primary d-grid w-100" type="submit">Sign in</button>
                </div>
              </form>

              <p class="text-center">
                <span>New on our platform?</span>
                <a href="auth-register-basic.php"><span>Create an account</span></a>
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <script src="../assets/vendor/libs/jquery/jquery.js"></script>
    <script src="../assets/vendor/libs/popper/popper.js"></script>
    <script src="../assets/vendor/js/bootstrap.js"></script>
    <script src="../assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>
    <script src="../assets/vendor/js/menu.js"></script>
    <script src="../assets/js/main.js"></script>
  </body>
</html>
