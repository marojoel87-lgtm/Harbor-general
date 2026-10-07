<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
requireLogin();

$id = (int) ($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM patients WHERE id = ?");
$stmt->execute([$id]);
$patient = $stmt->fetch();

if (!$patient) {
    setFlash('error', 'Patient not found.');
    header('Location: patients.php');
    exit;
}

$pageTitle = 'Edit Patient';
$activeNav = 'patients';
$errors = [];
$old = $patient;
$old['portal_username'] = $patient['username'] ?? ''; // username is safe to show
$old['portal_password'] = ''; // never pre-fill a password field with anything real

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach (['full_name', 'gender', 'dob', 'phone', 'email', 'address', 'emergency_contact', 'portal_username', 'portal_password'] as $key) {
        $old[$key] = trim($_POST[$key] ?? '');
    }

    if ($old['full_name'] === '') $errors['full_name'] = 'Full name is required.';
    if (!in_array($old['gender'], ['Male', 'Female', 'Other'], true)) $errors['gender'] = 'Please select a valid gender.';
    if ($old['dob'] === '') $errors['dob'] = 'Date of birth is required.';
    if ($old['phone'] === '') $errors['phone'] = 'Phone number is required.';
    if ($old['email'] !== '' && !filter_var($old['email'], FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Please enter a valid email address.';

    if ($old['portal_username'] !== '' && !preg_match('/^[a-zA-Z0-9._]{3,50}$/', $old['portal_username'])) {
        $errors['portal_username'] = 'Username must be 3-50 characters: letters, numbers, dots, or underscores only.';
    }
    if ($old['portal_password'] !== '' && strlen($old['portal_password']) < 6) {
        $errors['portal_password'] = 'Password must be at least 6 characters.';
    }
    // Clearing the username while a password exists (or vice versa) would leave a broken half-state
    if ($old['portal_username'] === '' && $old['portal_password'] !== '') {
        $errors['portal_username'] = 'A username is required when setting a new password.';
    }
    // Setting a brand-new username with no password, and no existing password to fall back on
    if ($old['portal_username'] !== '' && $old['portal_password'] === '' && empty($patient['password'])) {
        $errors['portal_password'] = 'Please set a password for this new username.';
    }

    // Username must be unique across both patients (excluding this patient) and staff
    if ($old['portal_username'] !== '' && !isset($errors['portal_username']) && $old['portal_username'] !== ($patient['username'] ?? '')) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM patients WHERE username = ? AND id != ?");
        $stmt->execute([$old['portal_username'], $id]);
        $taken = (int) $stmt->fetchColumn() > 0;

        $stmt = $pdo->prepare("SELECT COUNT(*) FROM staff WHERE username = ?");
        $stmt->execute([$old['portal_username']]);
        $taken = $taken || (int) $stmt->fetchColumn() > 0;

        if ($taken) $errors['portal_username'] = 'That username is already taken.';
    }

    if (empty($errors)) {
        $usernameToStore = $old['portal_username'] !== '' ? $old['portal_username'] : null;

        // Only overwrite the stored password if staff actually typed a new one;
        // an empty field here means "leave the existing portal password as it is."
        $passwordToStore = $patient['password'];
        if ($old['portal_password'] !== '') {
            $passwordToStore = password_hash($old['portal_password'], PASSWORD_DEFAULT);
        }
        // If the username was cleared entirely, portal access should be fully disabled
        if ($usernameToStore === null) {
            $passwordToStore = null;
        }

        $stmt = $pdo->prepare("UPDATE patients SET full_name=?, gender=?, dob=?, phone=?, email=?, address=?, emergency_contact=?, username=?, password=? WHERE id=?");
        $stmt->execute([
            $old['full_name'], $old['gender'], $old['dob'], $old['phone'],
            $old['email'] ?: null, $old['address'] ?: null, $old['emergency_contact'] ?: null,
            $usernameToStore, $passwordToStore, $id
        ]);
        setFlash('success', 'Patient record updated successfully.');
        header('Location: patient_view.php?id=' . $id);
        exit;
    }
}

require_once 'includes/app_header.php';
?>

<a href="patient_view.php?id=<?= $id ?>" class="back-link"><i class="fa-solid fa-arrow-left"></i> Back to Patient Profile</a>

<div class="panel">
  <div class="panel-head"><h2>Edit Patient — <?= htmlspecialchars($patient['full_name']) ?></h2></div>
  <div class="panel-body">
    <?php if (!empty($errors)): ?>
      <div class="alert alert-error"><i class="fa-solid fa-circle-exclamation"></i> Please fix the highlighted fields below.</div>
    <?php endif; ?>

    <form method="POST" action="patient_edit.php?id=<?= $id ?>" novalidate>
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
          <input type="date" id="dob" name="dob" class="<?= isset($errors['dob']) ? 'invalid' : '' ?>" value="<?= htmlspecialchars(date('Y-m-d', strtotime($old['dob']))) ?>">
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
          <input type="email" id="email" name="email" class="<?= isset($errors['email']) ? 'invalid' : '' ?>" value="<?= htmlspecialchars($old['email'] ?? '') ?>">
          <?php if (isset($errors['email'])): ?><div class="field-error"><?= $errors['email'] ?></div><?php endif; ?>
        </div>
        <div class="form-group">
          <label for="emergency_contact">Emergency Contact</label>
          <input type="text" id="emergency_contact" name="emergency_contact" value="<?= htmlspecialchars($old['emergency_contact'] ?? '') ?>">
        </div>
      </div>

      <div class="form-group">
        <label for="address">Address</label>
        <textarea id="address" name="address"><?= htmlspecialchars($old['address'] ?? '') ?></textarea>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label for="portal_username">Patient Portal Username</label>
          <input type="text" id="portal_username" name="portal_username" value="<?= htmlspecialchars($old['portal_username']) ?>" class="<?= isset($errors['portal_username']) ? 'invalid' : '' ?>">
          <?php if (isset($errors['portal_username'])): ?><div class="field-error"><?= $errors['portal_username'] ?></div><?php endif; ?>
        </div>
        <div class="form-group">
          <label for="portal_password">Patient Portal Password</label>
          <input type="text" id="portal_password" name="portal_password" value="" class="<?= isset($errors['portal_password']) ? 'invalid' : '' ?>" placeholder="<?= !empty($patient['password']) ? 'Leave blank to keep current password' : 'Required if setting a username' ?>">
          <?php if (isset($errors['portal_password'])): ?><div class="field-error"><?= $errors['portal_password'] ?></div><?php endif; ?>
        </div>
      </div>
      <div class="form-hint" style="margin-top:-10px;margin-bottom:18px;">
        Portal login is currently
        <strong><?= !empty($patient['password']) ? 'enabled' : 'disabled' ?></strong>
        for this patient. Clear the username entirely to disable portal login, or leave the password blank to keep their current one.
      </div>

      <div style="display:flex;gap:12px;">
        <button type="submit" class="btn btn-primary">Save Changes</button>
        <a href="patient_view.php?id=<?= $id ?>" class="btn" style="color:var(--hg-ink-soft);">Cancel</a>
      </div>
    </form>
  </div>
</div>

<?php require_once 'includes/app_footer.php'; ?>
