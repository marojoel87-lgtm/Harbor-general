<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';

if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}
if (isPatientLoggedIn()) {
    header('Location: patient_portal.php');
    exit;
}

$error = '';
$oldUsername = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $oldUsername = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($oldUsername === '' || $password === '') {
        $error = 'Please enter both username and password.';
    } else {
        // Try staff first (staff can sign in with their username or email)
        $stmt = $pdo->prepare("SELECT * FROM staff WHERE username = ? OR email = ? LIMIT 1");
        $stmt->execute([$oldUsername, $oldUsername]);
        $staff = $stmt->fetch();

        if ($staff && password_verify($password, $staff['password'])) {
            $_SESSION['staff_id'] = $staff['id'];
            $_SESSION['staff_name'] = $staff['full_name'];
            $_SESSION['staff_role'] = $staff['role'];
            header('Location: dashboard.php');
            exit;
        }

        // Not a staff match — try patients
        $stmt = $pdo->prepare("SELECT * FROM patients WHERE username = ? AND username IS NOT NULL LIMIT 1");
        $stmt->execute([$oldUsername]);
        $patient = $stmt->fetch();

        if ($patient && password_verify($password, $patient['password'])) {
            $_SESSION['patient_id'] = $patient['id'];
            $_SESSION['patient_name'] = $patient['full_name'];
            header('Location: patient_portal.php');
            exit;
        }

        // Neither matched — one generic message either way
        $error = 'Invalid username or password.';
    }
}
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login | Harbor General</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="css/style.css">
</head>
<body>

<a href="index.php" class="back-arrow-btn" aria-label="Back to homepage" title="Back to homepage">
  <i class="fa-solid fa-arrow-left"></i>
</a>

<div class="auth-wrap">
  <div class="auth-card">
    <a href="index.php" class="brand">
      <span class="brand-mark"><i class="fa-solid fa-house-medical"></i></span>
      Harbor <span>General</span>
    </a>
    <h2>Login</h2>
    <p class="sub">Staff and patients both sign in here.</p>

    <?php if ($error): ?>
      <div class="alert alert-error"><i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="login.php" novalidate>
      <div class="form-group">
        <label class="required" for="username">Username</label>
        <input type="text" id="username" name="username" value="<?= htmlspecialchars($oldUsername) ?>" required autofocus>
      </div>
      <div class="form-group">
        <label class="required" for="password">Password</label>
        <input type="password" id="password" name="password" required>
      </div>
      <button type="submit" class="btn btn-primary-light btn-block">Sign In</button>
    </form>

    <p class="small text-center" style="margin-top:20px;">New patient? <a href="patient_signup.php">Create a portal account</a>.</p>
  </div>
</div>

<script src="js/script.js"></script>
</body>
</html>
