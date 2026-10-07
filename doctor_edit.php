<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
requireLogin();

$id = (int) ($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM doctors WHERE id = ?");
$stmt->execute([$id]);
$doctor = $stmt->fetch();

if (!$doctor) {
    setFlash('error', 'Doctor not found.');
    header('Location: doctors_manage.php');
    exit;
}

$pageTitle = 'Edit Doctor';
$activeNav = 'doctors';
$departments = ['General Medicine', 'Emergency Care', 'Pediatrics', 'Cardiology', 'Laboratory Services', 'Pharmacy', 'Outpatient Services', 'Orthopedics'];

$errors = [];
$old = $doctor;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach (['full_name', 'specialty', 'department', 'phone', 'email', 'status'] as $key) {
        $old[$key] = trim($_POST[$key] ?? '');
    }

    if ($old['full_name'] === '') $errors['full_name'] = 'Full name is required.';
    if ($old['specialty'] === '') $errors['specialty'] = 'Specialty is required.';
    if ($old['phone'] === '') $errors['phone'] = 'Phone number is required.';
    if ($old['email'] !== '' && !filter_var($old['email'], FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Please enter a valid email address.';

    if (empty($errors)) {
        // Start with whatever photo the doctor already has
        $photoFilename = $doctor['photo'];

        // If staff checked "remove photo", clear it (only delete the actual file if it's a local upload)
        if (!empty($_POST['remove_photo']) && !empty($doctor['photo'])) {
            if (!str_starts_with($doctor['photo'], 'http')) {
                $oldPath = __DIR__ . '/uploads/doctors/' . $doctor['photo'];
                if (file_exists($oldPath)) unlink($oldPath);
            }
            $photoFilename = null;
        }

        // If a new file was uploaded, it replaces whatever was there before
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
            $fileType = mime_content_type($_FILES['photo']['tmp_name']);

            if (in_array($fileType, $allowedTypes, true)) {
                $extension = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
                $newFilename = uniqid('doc_') . '.' . strtolower($extension);
                $destination = __DIR__ . '/uploads/doctors/' . $newFilename;
                move_uploaded_file($_FILES['photo']['tmp_name'], $destination);

                // Delete the old photo file so uploads/doctors doesn't fill up with unused images
                // (only if the old one was a local upload, not a seeded web URL)
                if (!empty($doctor['photo']) && !str_starts_with($doctor['photo'], 'http')) {
                    $oldPath = __DIR__ . '/uploads/doctors/' . $doctor['photo'];
                    if (file_exists($oldPath)) unlink($oldPath);
                }

                $photoFilename = $newFilename;
            } else {
                $errors['photo'] = 'Please upload a JPG, PNG, or WEBP image.';
            }
        }

        if (empty($errors)) {
            $stmt = $pdo->prepare("UPDATE doctors SET full_name=?, specialty=?, department=?, phone=?, email=?, status=?, photo=? WHERE id=?");
            $stmt->execute([$old['full_name'], $old['specialty'], $old['department'], $old['phone'], $old['email'] ?: null, $old['status'], $photoFilename, $id]);
            setFlash('success', 'Doctor record updated successfully.');
            header('Location: doctors_manage.php');
            exit;
        }
    }
}

require_once 'includes/app_header.php';
?>

<a href="doctors_manage.php" class="back-link"><i class="fa-solid fa-arrow-left"></i> Back to Doctors</a>

<div class="panel">
  <div class="panel-head"><h2>Edit Doctor — Dr. <?= htmlspecialchars($doctor['full_name']) ?></h2></div>
  <div class="panel-body">
    <?php if (!empty($errors)): ?>
      <div class="alert alert-error"><i class="fa-solid fa-circle-exclamation"></i> Please fix the highlighted fields below.</div>
    <?php endif; ?>

    <form method="POST" action="doctor_edit.php?id=<?= $id ?>" enctype="multipart/form-data" novalidate>
      <div class="form-row">
        <div class="form-group">
          <label class="required" for="full_name">Full Name</label>
          <input type="text" id="full_name" name="full_name" class="<?= isset($errors['full_name']) ? 'invalid' : '' ?>" value="<?= htmlspecialchars($old['full_name']) ?>">
          <?php if (isset($errors['full_name'])): ?><div class="field-error"><?= $errors['full_name'] ?></div><?php endif; ?>
        </div>
        <div class="form-group">
          <label class="required" for="specialty">Specialty</label>
          <input type="text" id="specialty" name="specialty" class="<?= isset($errors['specialty']) ? 'invalid' : '' ?>" value="<?= htmlspecialchars($old['specialty']) ?>">
          <?php if (isset($errors['specialty'])): ?><div class="field-error"><?= $errors['specialty'] ?></div><?php endif; ?>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label class="required" for="department">Department</label>
          <select id="department" name="department">
            <?php foreach ($departments as $d): ?>
              <option value="<?= $d ?>" <?= $old['department'] === $d ? 'selected' : '' ?>><?= $d ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label for="status">Availability Status</label>
          <select id="status" name="status">
            <option value="Available" <?= $old['status'] === 'Available' ? 'selected' : '' ?>>Available</option>
            <option value="Unavailable" <?= $old['status'] === 'Unavailable' ? 'selected' : '' ?>>Unavailable</option>
          </select>
        </div>
      </div>

      <div class="form-group">
        <label for="photo">Profile Photo</label>
        <?php $currentPhotoSrc = doctorPhotoSrc($doctor['photo']); ?>
        <?php if ($currentPhotoSrc): ?>
          <div style="display:flex;align-items:center;gap:14px;margin-bottom:10px;">
            <img src="<?= htmlspecialchars($currentPhotoSrc) ?>" alt="Current photo" style="width:64px;height:64px;object-fit:cover;border-radius:var(--hg-radius-sm);border:1px solid var(--hg-border);">
            <label style="font-weight:400;display:flex;align-items:center;gap:6px;margin:0;">
              <input type="checkbox" name="remove_photo" value="1" style="width:auto;"> Remove current photo
            </label>
          </div>
        <?php endif; ?>
        <input type="file" id="photo" name="photo" accept="image/*">
        <?php if (isset($errors['photo'])): ?><div class="field-error"><?= $errors['photo'] ?></div><?php endif; ?>
        <div class="form-hint">Optional. Uploading a new photo replaces the current one.</div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label class="required" for="phone">Phone Number</label>
          <input type="tel" id="phone" name="phone" class="<?= isset($errors['phone']) ? 'invalid' : '' ?>" value="<?= htmlspecialchars($old['phone']) ?>">
          <?php if (isset($errors['phone'])): ?><div class="field-error"><?= $errors['phone'] ?></div><?php endif; ?>
        </div>
        <div class="form-group">
          <label for="email">Email Address</label>
          <input type="email" id="email" name="email" class="<?= isset($errors['email']) ? 'invalid' : '' ?>" value="<?= htmlspecialchars($old['email'] ?? '') ?>">
          <?php if (isset($errors['email'])): ?><div class="field-error"><?= $errors['email'] ?></div><?php endif; ?>
        </div>
      </div>

      <div style="display:flex;gap:12px;">
        <button type="submit" class="btn btn-primary">Save Changes</button>
        <a href="doctors_manage.php" class="btn" style="color:var(--hg-ink-soft);">Cancel</a>
      </div>
    </form>
  </div>
</div>

<?php require_once 'includes/app_footer.php'; ?>
