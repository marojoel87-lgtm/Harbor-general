<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
requireLogin();

$pageTitle = 'Register Patient';
$activeNav = 'patients';

$errors = [];
$old = ['full_name' => '', 'gender' => 'Male', 'dob' => '', 'phone' => '', 'email' => '', 'address' => '', 'emergency_contact' => '', 'portal_username' => '', 'portal_password' => ''];

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

    // Portal username/password are optional, but if either is given, both are required
    if ($old['portal_username'] !== '' || $old['portal_password'] !== '') {
        if ($old['portal_username'] === '') $errors['portal_username'] = 'Please enter a username, or leave both fields blank.';
        if ($old['portal_password'] === '') {
            $errors['portal_password'] = 'Please enter a password, or leave both fields blank.';
        } elseif (strlen($old['portal_password']) < 6) {
            $errors['portal_password'] = 'Password must be at least 6 characters.';
        }
        if ($old['portal_username'] !== '' && !preg_match('/^[a-zA-Z0-9._]{3,50}$/', $old['portal_username'])) {
            $errors['portal_username'] = 'Username must be 3-50 characters: letters, numbers, dots, or underscores only.';
        }
    }

    // Username must be unique across both patients and staff
    if ($old['portal_username'] !== '' && !isset($errors['portal_username'])) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM patients WHERE username = ?");
        $stmt->execute([$old['portal_username']]);
        $taken = (int) $stmt->fetchColumn() > 0;

        $stmt = $pdo->prepare("SELECT COUNT(*) FROM staff WHERE username = ?");
        $stmt->execute([$old['portal_username']]);
        $taken = $taken || (int) $stmt->fetchColumn() > 0;

        if ($taken) $errors['portal_username'] = 'That username is already taken.';
    }

    if (empty($errors)) {
        $usernameToStore = $old['portal_username'] !== '' ? $old['portal_username'] : null;
        $hashedPassword = $old['portal_password'] !== '' ? password_hash($old['portal_password'], PASSWORD_DEFAULT) : null;

        $stmt = $pdo->prepare("INSERT INTO patients (full_name, gender, dob, phone, email, address, emergency_contact, username, password, registration_date)
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, CURDATE())");
        $stmt->execute([
            $old['full_name'], $old['gender'], $old['dob'], $old['phone'],
            $old['email'] ?: null, $old['address'] ?: null, $old['emergency_contact'] ?: null,
            $usernameToStore, $hashedPassword
        ]);
        setFlash('success', 'Patient "' . $old['full_name'] . '" was registered successfully.');
        header('Location: patients.php');
        exit;
    }
}

require_once 'includes/app_header.php';
?>

<a href="patients.php" class="back-link"><i class="fa-solid fa-arrow-left"></i> Back to Patients</a>

<div class="panel">
  <div class="panel-head"><h2>Patient Registration Form</h2></div>
  <div class="panel-body">
    <?php if (!empty($errors)): ?>
      <div class="alert alert-error"><i class="fa-solid fa-circle-exclamation"></i> Please fix the highlighted fields below.</div>
    <?php endif; ?>

    <form method="POST" action="patient_add.php" novalidate>
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

      <div class="form-row">
        <div class="form-group">
          <label for="portal_username">Patient Portal Username</label>
          <input type="text" id="portal_username" name="portal_username" value="<?= htmlspecialchars($old['portal_username']) ?>" class="<?= isset($errors['portal_username']) ? 'invalid' : '' ?>" placeholder="Leave blank to disable portal login">
          <?php if (isset($errors['portal_username'])): ?><div class="field-error"><?= $errors['portal_username'] ?></div><?php endif; ?>
        </div>
        <div class="form-group">
          <label for="portal_password">Patient Portal Password</label>
          <input type="text" id="portal_password" name="portal_password" value="<?= htmlspecialchars($old['portal_password']) ?>" class="<?= isset($errors['portal_password']) ? 'invalid' : '' ?>">
          <?php if (isset($errors['portal_password'])): ?><div class="field-error"><?= $errors['portal_password'] ?></div><?php endif; ?>
        </div>
      </div>
      <div class="form-hint" style="margin-top:-10px;margin-bottom:18px;">Both optional — leave both blank to disable portal login. If set, the patient can log in from the main Login page with this username and password to view their own visit history.</div>

      <div style="display:flex;gap:12px;">
        <button type="submit" class="btn btn-primary">Register Patient</button>
        <a href="patients.php" class="btn" style="color:var(--hg-ink-soft);">Cancel</a>
      </div>
    </form>
  </div>
</div>

<?php require_once 'includes/app_footer.php'; ?>
