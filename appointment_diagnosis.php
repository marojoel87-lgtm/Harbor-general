<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
requireLogin();

$id = (int) ($_GET['id'] ?? 0);
$stmt = $pdo->prepare("
    SELECT a.*, p.full_name AS patient_name, d.full_name AS doctor_name
    FROM appointments a
    JOIN patients p ON p.id = a.patient_id
    JOIN doctors d ON d.id = a.doctor_id
    WHERE a.id = ?
");
$stmt->execute([$id]);
$appointment = $stmt->fetch();

if (!$appointment) {
    setFlash('error', 'Appointment not found.');
    header('Location: appointments.php');
    exit;
}

$pageTitle = 'Diagnosis & Outcome';
$activeNav = 'appointments';

$outcomes = ['Pending', 'Treatment Successful', 'Referred to Another Hospital', 'Not Resolved'];

$errors = [];
$old = [
    'diagnosis' => $appointment['diagnosis'] ?? '',
    'outcome' => $appointment['outcome'] ?? 'Pending',
    'referral_hospital' => $appointment['referral_hospital'] ?? '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old['diagnosis'] = trim($_POST['diagnosis'] ?? '');
    $old['outcome'] = trim($_POST['outcome'] ?? '');
    $old['referral_hospital'] = trim($_POST['referral_hospital'] ?? '');

    if (!in_array($old['outcome'], $outcomes, true)) {
        $errors['outcome'] = 'Please select a valid outcome.';
    }
    if ($old['outcome'] === 'Referred to Another Hospital' && $old['referral_hospital'] === '') {
        $errors['referral_hospital'] = 'Please name the hospital the patient was referred to.';
    }

    if (empty($errors)) {
        // Only keep a referral hospital value when the outcome actually is a referral
        $referralToSave = $old['outcome'] === 'Referred to Another Hospital' ? $old['referral_hospital'] : null;

        $stmt = $pdo->prepare("UPDATE appointments SET diagnosis = ?, outcome = ?, referral_hospital = ? WHERE id = ?");
        $stmt->execute([$old['diagnosis'] ?: null, $old['outcome'], $referralToSave, $id]);
        setFlash('success', 'Diagnosis and outcome saved for ' . $appointment['patient_name'] . '.');
        header('Location: patient_view.php?id=' . $appointment['patient_id']);
        exit;
    }
}

require_once 'includes/app_header.php';
?>

<a href="patient_view.php?id=<?= $appointment['patient_id'] ?>" class="back-link"><i class="fa-solid fa-arrow-left"></i> Back to Patient Profile</a>

<div class="panel">
  <div class="panel-head">
    <h2>Diagnosis &amp; Outcome</h2>
  </div>
  <div class="panel-body">
    <div class="detail-grid" style="margin-bottom:22px;">
      <div class="detail-item"><div class="label">Patient</div><div class="value"><?= htmlspecialchars($appointment['patient_name']) ?></div></div>
      <div class="detail-item"><div class="label">Doctor</div><div class="value">Dr. <?= htmlspecialchars($appointment['doctor_name']) ?></div></div>
      <div class="detail-item"><div class="label">Visit Date</div><div class="value"><?= htmlspecialchars(date('M j, Y', strtotime($appointment['appointment_date']))) ?></div></div>
      <div class="detail-item"><div class="label">Reason for Visit</div><div class="value"><?= htmlspecialchars($appointment['reason']) ?></div></div>
    </div>

    <?php if (!empty($errors)): ?>
      <div class="alert alert-error"><i class="fa-solid fa-circle-exclamation"></i> Please fix the highlighted fields below.</div>
    <?php endif; ?>

    <?php if ($appointment['status'] !== 'Completed'): ?>
      <div class="alert alert-info"><i class="fa-solid fa-circle-info"></i> This appointment's status is currently "<?= htmlspecialchars($appointment['status']) ?>." You can still record a diagnosis, but outcomes are only shown to the patient once the appointment is marked Completed on the Appointments page.</div>
    <?php endif; ?>

    <form method="POST" action="appointment_diagnosis.php?id=<?= $id ?>" novalidate>
      <div class="form-group">
        <label for="diagnosis">Diagnosis</label>
        <textarea id="diagnosis" name="diagnosis" placeholder="What was found during this visit?"><?= htmlspecialchars($old['diagnosis']) ?></textarea>
        <div class="form-hint">Visible to the patient in their portal once this appointment is marked Completed.</div>
      </div>

      <div class="form-group">
        <label class="required" for="outcome">Outcome</label>
        <select id="outcome" name="outcome" class="<?= isset($errors['outcome']) ? 'invalid' : '' ?>">
          <?php foreach ($outcomes as $o): ?>
            <option value="<?= $o ?>" <?= $old['outcome'] === $o ? 'selected' : '' ?>><?= $o ?></option>
          <?php endforeach; ?>
        </select>
        <?php if (isset($errors['outcome'])): ?><div class="field-error"><?= $errors['outcome'] ?></div><?php endif; ?>
      </div>

      <div class="form-group" id="referralGroup">
        <label for="referral_hospital">Referral Hospital</label>
        <input type="text" id="referral_hospital" name="referral_hospital" value="<?= htmlspecialchars($old['referral_hospital']) ?>" class="<?= isset($errors['referral_hospital']) ? 'invalid' : '' ?>" placeholder="e.g. Lagos University Teaching Hospital (LUTH)">
        <?php if (isset($errors['referral_hospital'])): ?><div class="field-error"><?= $errors['referral_hospital'] ?></div><?php endif; ?>
        <div class="form-hint">Only needed if the outcome is "Referred to Another Hospital".</div>
      </div>

      <div style="display:flex;gap:12px;">
        <button type="submit" class="btn btn-primary">Save Diagnosis</button>
        <a href="patient_view.php?id=<?= $appointment['patient_id'] ?>" class="btn" style="color:var(--hg-ink-soft);">Cancel</a>
      </div>
    </form>
  </div>
</div>

<script>
  // Small convenience: only show the referral field when it's relevant
  const outcomeSelect = document.getElementById('outcome');
  const referralGroup = document.getElementById('referralGroup');
  function toggleReferralField() {
    referralGroup.style.display = outcomeSelect.value === 'Referred to Another Hospital' ? 'block' : 'none';
  }
  outcomeSelect.addEventListener('change', toggleReferralField);
  toggleReferralField();
</script>

<?php require_once 'includes/app_footer.php'; ?>
