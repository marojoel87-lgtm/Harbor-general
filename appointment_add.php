<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
requireLogin();

$pageTitle = 'Book Appointment';
$activeNav = 'appointments';

$patients = $pdo->query("SELECT id, full_name FROM patients ORDER BY full_name ASC")->fetchAll();
$doctors  = $pdo->query("SELECT id, full_name, specialty, status FROM doctors ORDER BY full_name ASC")->fetchAll();

$errors = [];
$old = [
    'patient_id' => $_GET['patient_id'] ?? '',
    'doctor_id' => '',
    'appointment_date' => '',
    'appointment_time' => '',
    'reason' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($old as $key => $v) {
        $old[$key] = trim($_POST[$key] ?? '');
    }

    if ($old['patient_id'] === '') $errors['patient_id'] = 'Please select a patient.';
    if ($old['doctor_id'] === '') $errors['doctor_id'] = 'Please select a doctor.';
    if ($old['appointment_date'] === '') {
        $errors['appointment_date'] = 'Please select a date.';
    } elseif (strtotime($old['appointment_date']) < strtotime(date('Y-m-d'))) {
        $errors['appointment_date'] = 'Appointment date cannot be in the past.';
    }
    if ($old['appointment_time'] === '') $errors['appointment_time'] = 'Please select a time.';
    if ($old['reason'] === '') $errors['reason'] = 'Please enter a reason for the visit.';

    if (empty($errors)) {
        $stmt = $pdo->prepare("INSERT INTO appointments (patient_id, doctor_id, appointment_date, appointment_time, reason, status)
                                VALUES (?, ?, ?, ?, ?, 'Scheduled')");
        $stmt->execute([$old['patient_id'], $old['doctor_id'], $old['appointment_date'], $old['appointment_time'], $old['reason']]);
        setFlash('success', 'Appointment booked successfully.');
        header('Location: appointments.php');
        exit;
    }
}

require_once 'includes/app_header.php';
?>

<a href="appointments.php" class="back-link"><i class="fa-solid fa-arrow-left"></i> Back to Appointments</a>

<div class="panel">
  <div class="panel-head"><h2>New Appointment</h2></div>
  <div class="panel-body">
    <?php if (!empty($errors)): ?>
      <div class="alert alert-error"><i class="fa-solid fa-circle-exclamation"></i> Please fix the highlighted fields below.</div>
    <?php endif; ?>

    <?php if (count($patients) === 0 || count($doctors) === 0): ?>
      <div class="alert alert-info"><i class="fa-solid fa-circle-info"></i> You need at least one patient and one doctor before booking an appointment.
        <?php if (count($patients) === 0): ?> <a href="patient_add.php">Register a patient</a>.<?php endif; ?>
        <?php if (count($doctors) === 0): ?> <a href="doctor_add.php">Add a doctor</a>.<?php endif; ?>
      </div>
    <?php else: ?>
      <form method="POST" action="appointment_add.php" novalidate>
        <div class="form-row">
          <div class="form-group">
            <label class="required" for="patient_id">Patient</label>
            <select id="patient_id" name="patient_id" class="<?= isset($errors['patient_id']) ? 'invalid' : '' ?>">
              <option value="">Select a patient...</option>
              <?php foreach ($patients as $p): ?>
                <option value="<?= $p['id'] ?>" <?= (string) $old['patient_id'] === (string) $p['id'] ? 'selected' : '' ?>><?= htmlspecialchars($p['full_name']) ?></option>
              <?php endforeach; ?>
            </select>
            <?php if (isset($errors['patient_id'])): ?><div class="field-error"><?= $errors['patient_id'] ?></div><?php endif; ?>
          </div>
          <div class="form-group">
            <label class="required" for="doctor_id">Doctor</label>
            <select id="doctor_id" name="doctor_id" class="<?= isset($errors['doctor_id']) ? 'invalid' : '' ?>">
              <option value="">Select a doctor...</option>
              <?php foreach ($doctors as $d): ?>
                <option value="<?= $d['id'] ?>" <?= (string) $old['doctor_id'] === (string) $d['id'] ? 'selected' : '' ?>>
                  Dr. <?= htmlspecialchars($d['full_name']) ?> — <?= htmlspecialchars($d['specialty']) ?><?= $d['status'] !== 'Available' ? ' (Unavailable)' : '' ?>
                </option>
              <?php endforeach; ?>
            </select>
            <?php if (isset($errors['doctor_id'])): ?><div class="field-error"><?= $errors['doctor_id'] ?></div><?php endif; ?>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label class="required" for="appointment_date">Appointment Date</label>
            <input type="date" id="appointment_date" name="appointment_date" class="<?= isset($errors['appointment_date']) ? 'invalid' : '' ?>" value="<?= htmlspecialchars($old['appointment_date']) ?>" min="<?= date('Y-m-d') ?>">
            <?php if (isset($errors['appointment_date'])): ?><div class="field-error"><?= $errors['appointment_date'] ?></div><?php endif; ?>
          </div>
          <div class="form-group">
            <label class="required" for="appointment_time">Appointment Time</label>
            <input type="time" id="appointment_time" name="appointment_time" class="<?= isset($errors['appointment_time']) ? 'invalid' : '' ?>" value="<?= htmlspecialchars($old['appointment_time']) ?>">
            <?php if (isset($errors['appointment_time'])): ?><div class="field-error"><?= $errors['appointment_time'] ?></div><?php endif; ?>
          </div>
        </div>

        <div class="form-group">
          <label class="required" for="reason">Reason for Visit</label>
          <textarea id="reason" name="reason" class="<?= isset($errors['reason']) ? 'invalid' : '' ?>" placeholder="e.g. Routine check-up, follow-up consultation..."><?= htmlspecialchars($old['reason']) ?></textarea>
          <?php if (isset($errors['reason'])): ?><div class="field-error"><?= $errors['reason'] ?></div><?php endif; ?>
        </div>

        <div style="display:flex;gap:12px;">
          <button type="submit" class="btn btn-primary">Book Appointment</button>
          <a href="appointments.php" class="btn" style="color:var(--hg-ink-soft);">Cancel</a>
        </div>
      </form>
    <?php endif; ?>
  </div>
</div>

<?php require_once 'includes/app_footer.php'; ?>
