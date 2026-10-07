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

$errors = [];
$old = [
    'full_name' => '', 'gender' => 'Male', 'dob' => '', 'phone' => '',
    'email' => '', 'address' => '', 'emergency_contact' => '',
    'username' => '', 'password' => '', 'confirm_password' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($old as $key => $v) {
        $old[$key] = trim($_POST[$key] ?? '');
    }

    if ($old['full_name'] === '') $errors['full_name'] = 'Full name is required.';
    if (!in_array($old['gender'], ['Male', 'Female', 'Other'], true)) $errors['gender'] = 'Please select a valid gender.';
    if ($old['dob'] === '') {
        $errors['dob'] = 'Date of birth is required.';
    } elseif (strtotime($old['dob']) > time()) {
        $errors['dob'] = 'Date of birth cannot be in the future.';
    }
    if ($old['phone'] === '') $errors['phone'] = 'Phone number is required.';
    if ($old['email'] !== '' && !filter_var($old['email'], FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Please enter a valid email address.';

    if ($old['username'] === '') {
        $errors['username'] = 'Please choose a username.';
    } elseif (!preg_match('/^[a-zA-Z0-9._]{3,50}$/', $old['username'])) {
        $errors['username'] = 'Username must be 3-50 characters: letters, numbers, dots, or underscores only.';
    }
    if ($old['password'] === '') {
        $errors['password'] = 'Please choose a password.';
    } elseif (strlen($old['password']) < 6) {
        $errors['password'] = 'Password must be at least 6 characters.';
    }
    if ($old['confirm_password'] !== $old['password']) {
        $errors['confirm_password'] = 'Passwords do not match.';
    }

    // Username must be unique across both patients and staff, so the
    // login page never has to guess which table a username belongs to.
    if ($old['username'] !== '' && !isset($errors['username'])) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM patients WHERE username = ?");
        $stmt->execute([$old['username']]);
        $takenByPatient = (int) $stmt->fetchColumn() > 0;

        $stmt = $pdo->prepare("SELECT COUNT(*) FROM staff WHERE username = ?");
        $stmt->execute([$old['username']]);
        $takenByStaff = (int) $stmt->fetchColumn() > 0;

        if ($takenByPatient || $takenByStaff) {
            $errors['username'] = 'That username is already taken. Please choose another.';
        }
    }

    if (empty($errors)) {
        $hashedPassword = password_hash($old['password'], PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("INSERT INTO patients (full_name, gender, dob, phone, email, address, emergency_contact, username, password, registration_date)
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, CURDATE())");
        $stmt->execute([
            $old['full_name'], $old['gender'], $old['dob'], $old['phone'],
            $old['email'] ?: null, $old['address'] ?: null, $old['emergency_contact'] ?: null,
            $old['username'], $hashedPassword
        ]);

        $newPatientId = (int) $pdo->lastInsertId();
        $_SESSION['patient_id'] = $newPatientId;
        $_SESSION['patient_name'] = $old['full_name'];
        setFlash('success', 'Welcome to Harbor General! Your portal account is ready.');
        header('Location: patient_portal.php');
        exit;
    }
}
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Patient Sign Up | Harbor General</title>
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
  <div class="auth-card" style="max-width:560px;">
    <a href="index.php" class="brand">
      <span class="brand-mark"><i class="fa-solid fa-house-medical"></i></span>
      Harbor <span>General</span>
    </a>
    <h2>Create Your Patient Account</h2>
    <p class="sub">Sign up to view your visit history, diagnoses, and treatment outcomes.</p>

    <?php if (!empty($errors)): ?>
      <div class="alert alert-error"><i class="fa-solid fa-circle-exclamation"></i> Please fix the highlighted fields below.</div>
    <?php endif; ?>

    <form method="POST" action="patient_signup.php" novalidate>
      <div class="form-row">
        <div class="form-group">
          <label class="required" for="full_name">Full Name</label>
          <input type="text" id="full_name" name="full_name" class="<?= isset($errors['full_name']) ? 'invalid' : '' ?>" value="<?= htmlspecialchars($old['full_name']) ?>">
          <?php if (isset($errors['full_name'])): ?><div class="field-error"><?= $errors['full_name'] ?></div><?php endif; ?>
        </div>
        <div class="form-group">
          <label class="required" for="gender">Gender</label>
          <select id="gender" name="gender">
            <?php foreach (['Male', 'Female', 'Other'] as $g): ?>
              <option value="<?= $g ?>" <?= $old['gender'] === $g ? 'selected' : '' ?>><?= $g ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label class="required" for="dob">Date of Birth</label>
          <input type="date" id="dob" name="dob" class="<?= isset($errors['dob']) ? 'invalid' : '' ?>" value="<?= htmlspecialchars($old['dob']) ?>">
          <?php if (isset($errors['dob'])): ?><div class="field-error"><?= $errors['dob'] ?></div><?php endif; ?>
        </div>
        <div class="form-group">
          <label class="required" for="phone">Phone Number</label>
          <input type="tel" id="phone" name="phone" class="<?= isset($errors['phone']) ? 'invalid' : '' ?>" value="<?= htmlspecialchars($old['phone']) ?>">
          <?php if (isset($errors['phone'])): ?><div class="field-error"><?= $errors['phone'] ?></div><?php endif; ?>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label for="email">Email Address</label>
          <input type="email" id="email" name="email" class="<?= isset($errors['email']) ? 'invalid' : '' ?>" value="<?= htmlspecialchars($old['email']) ?>">
          <?php if (isset($errors['email'])): ?><div class="field-error"><?= $errors['email'] ?></div><?php endif; ?>
        </div>
        <div class="form-group">
          <label for="emergency_contact">Emergency Contact</label>
          <input type="text" id="emergency_contact" name="emergency_contact" value="<?= htmlspecialchars($old['emergency_contact']) ?>" placeholder="Name and phone number">
        </div>
      </div>

      <div class="form-group">
        <label for="address">Address</label>
        <textarea id="address" name="address"><?= htmlspecialchars($old['address']) ?></textarea>
      </div>

      <hr style="border:none;border-top:1px solid var(--hg-border);margin:22px 0;">

      <div class="form-row">
        <div class="form-group">
          <label class="required" for="username">Choose a Username</label>
          <input type="text" id="username" name="username" class="<?= isset($errors['username']) ? 'invalid' : '' ?>" value="<?= htmlspecialchars($old['username']) ?>" placeholder="e.g. amaka.johnson">
          <?php if (isset($errors['username'])): ?><div class="field-error"><?= $errors['username'] ?></div><?php endif; ?>
        </div>
        <div></div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label class="required" for="password">Choose a Password</label>
          <input type="password" id="password" name="password" class="<?= isset($errors['password']) ? 'invalid' : '' ?>">
          <?php if (isset($errors['password'])): ?><div class="field-error"><?= $errors['password'] ?></div><?php endif; ?>
        </div>
        <div class="form-group">
          <label class="required" for="confirm_password">Confirm Password</label>
          <input type="password" id="confirm_password" name="confirm_password" class="<?= isset($errors['confirm_password']) ? 'invalid' : '' ?>">
          <?php if (isset($errors['confirm_password'])): ?><div class="field-error"><?= $errors['confirm_password'] ?></div><?php endif; ?>
        </div>
      </div>

      <button type="submit" class="btn btn-primary-light btn-block" style="margin-top:8px;">Create Account</button>
    </form>

    <p class="small text-center" style="margin-top:20px;">Already have an account? <a href="login.php">Log in instead</a>.</p>
  </div>
</div>

<script src="js/script.js"></script>
</body>
</html>
