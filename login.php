<?php
session_start();
require_once __DIR__ . '/db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim((string)($_POST['username'] ?? ''));
    $password = (string)($_POST['password'] ?? '');

    if ($username === '' || $password === '') {
        $error = 'Please enter both username and password.';
    } else {
        try {
            $db = get_db_connection();
            $stmt = $db->prepare("SELECT * FROM admins WHERE username = :u");
            $stmt->execute([':u' => $username]);
            $admin = $stmt->fetch();

            if ($admin && password_verify($password, $admin['password_hash'])) {
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_user'] = $admin['username'];
                $_SESSION['admin_role'] = $admin['role'];
                header('Location: admin.php');
                exit;
            } else {
                $error = 'Invalid username or password.';
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
  <title>Admin Login | Apex Sports Club</title>
  <link rel="stylesheet" href="style.css" />
  <style>
    body {
      background: #09130d;
      color: #edf7ee;
      font-family: Inter, Arial, sans-serif;
      min-height: 100vh;
      display: grid;
      place-items: center;
      padding: 20px;
    }
    .login-card {
      width: min(440px, 100%);
      background: rgba(18, 32, 22, 0.95);
      border: 1px solid rgba(123, 255, 146, 0.25);
      border-radius: 16px;
      padding: 36px 30px;
      box-shadow: 0 20px 50px rgba(0, 0, 0, 0.5);
    }
    .login-brand {
      text-align: center;
      margin-bottom: 24px;
    }
    .login-brand img {
      max-width: 140px;
      height: auto;
    }
    .login-title {
      font-family: 'Space Grotesk', sans-serif;
      font-size: 1.6rem;
      margin: 12px 0 6px;
      text-align: center;
    }
    .login-sub {
      color: #a4baa9;
      font-size: 0.9rem;
      text-align: center;
      margin-bottom: 24px;
    }
    .error-box {
      background: rgba(255, 77, 77, 0.15);
      border: 1px solid rgba(255, 77, 77, 0.4);
      color: #ff9999;
      padding: 12px 14px;
      border-radius: 8px;
      font-size: 0.9rem;
      margin-bottom: 20px;
    }
    .form-group {
      margin-bottom: 18px;
    }
    .form-label {
      display: block;
      margin-bottom: 6px;
      font-weight: 500;
      color: #9ef2a6;
      font-size: 0.9rem;
    }
    .form-control {
      width: 100%;
      box-sizing: border-box;
      padding: 12px 14px;
      border-radius: 8px;
      border: 1px solid rgba(123, 255, 146, 0.3);
      background: rgba(9, 19, 13, 0.9);
      color: #edf7ee;
      font-size: 1rem;
    }
    .form-control:focus {
      outline: none;
      border-color: #61e07c;
      box-shadow: 0 0 0 2px rgba(97, 224, 124, 0.2);
    }
    .btn-submit {
      width: 100%;
      padding: 13px;
      background: #61e07c;
      color: #07130a;
      border: none;
      border-radius: 999px;
      font-weight: 700;
      font-size: 1rem;
      cursor: pointer;
      transition: transform 0.15s ease, background 0.15s ease;
      margin-top: 10px;
    }
    .btn-submit:hover {
      background: #7bff92;
      transform: translateY(-1px);
    }
    .demo-creds {
      margin-top: 24px;
      padding-top: 16px;
      border-top: 1px solid rgba(123, 255, 146, 0.15);
      font-size: 0.85rem;
      color: #a4baa9;
      text-align: center;
      line-height: 1.5;
    }
    .demo-creds code {
      background: rgba(97, 224, 124, 0.15);
      color: #9ef2a6;
      padding: 2px 6px;
      border-radius: 4px;
      font-weight: 600;
    }
    .back-link {
      display: block;
      text-align: center;
      margin-top: 18px;
      color: #9ef2a6;
      text-decoration: none;
      font-size: 0.88rem;
    }
    .back-link:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>
  <div class="login-card">
    <div class="login-brand">
      <img src="assets/Apex logo.png" alt="Apex Sports Club" />
      <h1 class="login-title">Staff &amp; Admin Portal</h1>
      <p class="login-sub">Sign in to review student memberships &amp; inquiries</p>
    </div>

    <?php if ($error): ?>
      <div class="error-box" role="alert">
        <?php echo htmlspecialchars($error); ?>
      </div>
    <?php endif; ?>

    <form method="POST" action="login.php" novalidate>
      <div class="form-group">
        <label for="username" class="form-label">Username</label>
        <input type="text" id="username" name="username" class="form-control" required autocomplete="username" autofocus />
      </div>

      <div class="form-group">
        <label for="password" class="form-label">Password</label>
        <input type="password" id="password" name="password" class="form-control" required autocomplete="current-password" />
      </div>

      <button type="submit" class="btn-submit">Sign In</button>
    </form>

    <div class="demo-creds">
      <strong>Default Admin Credentials:</strong><br>
      Username: <code>admin</code><br>
      Password: <code>Password123!</code>
    </div>

    <a href="index.html" class="back-link">&larr; Return to Public Website</a>
  </div>
</body>
</html>
