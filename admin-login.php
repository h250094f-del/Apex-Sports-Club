<?php
/**
 * Apex Sports Club — Staff & Administrator Login Portal
 * ISE 2102: Web Technologies Semester Project
 * Requirement E & F:
 * - Authenticates administrator against MySQL database `admins` table
 * - Uses secure password_verify() with Bcrypt hashing
 * - Initiates authenticated admin session
 */

session_start();
require_once __DIR__ . '/config.php';

// If already logged in, redirect straight to admin dashboard
if (!empty($_SESSION['admin_logged_in'])) {
    header('Location: admin.php');
    exit;
}

$error = '';
$usernameVal = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usernameVal = trim((string)($_POST['username'] ?? ''));
    $password    = (string)($_POST['password'] ?? '');

    if ($usernameVal === '' || $password === '') {
        $error = 'Please enter both your administrator username and password.';
    } else {
        try {
            $pdo = get_db_connection();
            $stmt = $pdo->prepare("SELECT id, username, password_hash, full_name, role FROM admins WHERE username = :u LIMIT 1");
            $stmt->execute([':u' => $usernameVal]);
            $admin = $stmt->fetch();

            if ($admin && password_verify($password, $admin['password_hash'])) {
                // Update last login timestamp
                $upd = $pdo->prepare("UPDATE admins SET last_login = NOW() WHERE id = :id");
                $upd->execute([':id' => $admin['id']]);

                // Set session
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_id']        = $admin['id'];
                $_SESSION['admin_username']  = $admin['username'];
                $_SESSION['admin_name']      = $admin['full_name'];
                $_SESSION['admin_role']      = $admin['role'];

                header('Location: admin.php');
                exit;
            } else {
                $error = 'Invalid username or password. Please verify credentials.';
            }
        } catch (Exception $e) {
            $error = 'Database connection error: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Staff Login | Apex Sports Club</title>
  <link rel="stylesheet" href="style.css" />
  <link rel="icon" type="image/png" href="assets/Apex icon.png" />
  <style>
    body {
      margin: 0;
      background: #09130d;
      color: #edf7ee;
      font-family: Inter, Arial, sans-serif;
      min-height: 100vh;
      display: grid;
      place-items: center;
      padding: 24px 16px;
    }
    .login-container {
      width: min(440px, 100%);
      background: rgba(18, 32, 22, 0.95);
      border: 1px solid rgba(123, 255, 146, 0.25);
      border-radius: 18px;
      padding: 36px 30px;
      box-shadow: 0 20px 50px rgba(0, 0, 0, 0.55);
    }
    .brand-header {
      text-align: center;
      margin-bottom: 24px;
    }
    .brand-header img {
      max-width: 140px;
      height: auto;
      margin-bottom: 8px;
    }
    .login-title {
      font-family: 'Space Grotesk', sans-serif;
      font-size: 1.55rem;
      margin: 8px 0 4px;
      color: #edf7ee;
    }
    .login-sub {
      color: #a4baa9;
      font-size: 0.88rem;
      margin: 0 0 20px;
    }
    .alert-error {
      background: rgba(255, 77, 77, 0.15);
      border: 1px solid rgba(255, 77, 77, 0.4);
      color: #ff9999;
      padding: 12px 14px;
      border-radius: 8px;
      font-size: 0.88rem;
      margin-bottom: 20px;
    }
    .form-group {
      margin-bottom: 18px;
      text-align: left;
    }
    .form-label {
      display: block;
      margin-bottom: 6px;
      font-weight: 500;
      color: #9ef2a6;
      font-size: 0.88rem;
    }
    .form-control {
      width: 100%;
      box-sizing: border-box;
      padding: 12px 14px;
      border-radius: 8px;
      border: 1px solid rgba(123, 255, 146, 0.25);
      background: rgba(9, 19, 13, 0.8);
      color: #edf7ee;
      font-size: 0.95rem;
    }
    .form-control:focus {
      outline: none;
      border-color: #61e07c;
      box-shadow: 0 0 0 2px rgba(97, 224, 124, 0.25);
    }
    .btn-login {
      width: 100%;
      padding: 13px;
      background: #61e07c;
      color: #07130a;
      border: none;
      border-radius: 999px;
      font-weight: 700;
      font-size: 0.98rem;
      cursor: pointer;
      transition: background 0.15s ease, transform 0.15s ease;
      margin-top: 10px;
    }
    .btn-login:hover {
      background: #7bff92;
      transform: translateY(-1px);
    }
    .credentials-hint {
      margin-top: 24px;
      padding: 14px;
      border-radius: 8px;
      background: rgba(97, 224, 124, 0.08);
      border: 1px dashed rgba(123, 255, 146, 0.3);
      font-size: 0.82rem;
      color: #cfead5;
      line-height: 1.5;
      text-align: center;
    }
    .credentials-hint code {
      background: rgba(97, 224, 124, 0.2);
      color: #9ef2a6;
      padding: 2px 6px;
      border-radius: 4px;
      font-weight: 700;
    }
    .back-link {
      display: block;
      text-align: center;
      margin-top: 20px;
      color: #9ef2a6;
      text-decoration: none;
      font-size: 0.85rem;
    }
    .back-link:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>
  <div class="login-container">
    <div class="brand-header">
      <a href="index.html">
        <img src="assets/Apex logo.png" alt="Apex Sports Club Logo" />
      </a>
      <h1 class="login-title">Staff &amp; Admin Portal</h1>
      <p class="login-sub">ISE 2102 — Student Club Records Management</p>
    </div>

    <?php if ($error !== ''): ?>
      <div class="alert-error" role="alert">
        <?php echo htmlspecialchars($error); ?>
      </div>
    <?php endif; ?>

    <form method="POST" action="admin-login.php" novalidate>
      <div class="form-group">
        <label for="username" class="form-label">Username</label>
        <input
          type="text"
          id="username"
          name="username"
          class="form-control"
          required
          autocomplete="username"
          value="<?php echo htmlspecialchars($usernameVal); ?>"
          autofocus
        />
      </div>

      <div class="form-group">
        <label for="password" class="form-label">Password</label>
        <input
          type="password"
          id="password"
          name="password"
          class="form-control"
          required
          autocomplete="current-password"
        />
      </div>

      <button type="submit" class="btn-login">Sign In to Dashboard</button>
    </form>

    <div class="credentials-hint">
      <strong>Default Admin Credentials :</strong><br>
      Username: <code>admin</code><br>
      Password: <code>Password123!</code>
    </div>

    <a href="index.html" class="back-link">&larr; Return to Public Website</a>
  </div>
</body>
</html>
