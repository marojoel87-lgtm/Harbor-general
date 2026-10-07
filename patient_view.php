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

$pageTitle = 'Patient Profile';
$activeNav = 'patients';

$stmt = $pdo->prepare("
    SELECT a.id, a.appointment_date, a.appointment_time, a.reason, a.status,
           a.diagnosis, a.outcome, a.referral_hospital,
           d.full_name AS doctor_name
    FROM appointments a
    JOIN doctors d ON d.id = a.doctor_id
    WHERE a.patient_id = ?
    ORDER BY a.appointment_date DESC, a.appointment_time DESC
");
$stmt->execute([$id]);
$history = $stmt->fetchAll();

function statusBadgeClass(string $status): string {
    return match ($status) {
        'Scheduled' => 'badge-scheduled',
        'Pending'   => 'badge-pending',
        'Completed' => 'badge-completed',
        'Cancelled' => 'badge-cancelled',
        default     => 'badge-scheduled',
    };
}

function outcomeBadgeClass(string $outcome): string {
    return match ($outcome) {
        'Treatment Successful'          => 'badge-outcome-success',
        'Referred to Another Hospital'  => 'badge-outcome-referred',
        'Not Resolved'                  => 'badge-outcome-unresolved',
        default                         => 'badge-outcome-pending',
    };
}

function ageFromDob(string $dob): int {
    return (new DateTime($dob))->diff(new DateTime('now'))->y;
}

require_once 'includes/app_header.php';
?>

<a href="patients.php" class="back-link"><i class="fa-solid fa-arrow-left"></i> Back to Patients</a>

<div class="pagetitle-row">
  <div>
    <h2 style="margin-bottom:4px;"><?= htmlspecialchars($patient['full_name']) ?></h2>
    <p class="small">Patient ID: #<?= str_pad($patient['id'], 4, '0', STR_PAD_LEFT) ?> &middot; Registered <?= htmlspecialchars(date('M j, Y', strtotime($patient['registration_date']))) ?>
      &middot; Portal access:
      <?php if (!empty($patient['username']) && !empty($patient['password'])): ?>
        <span class="badge badge-available">Enabled (<?= htmlspecialchars($patient['username']) ?>)</span>
      <?php else: ?>
        <span class="badge badge-unavailable">Not set</span>
      <?php endif; ?>
    </p>
  </div>
  <div style="display:flex;gap:10px;">
    <a href="patient_edit.php?id=<?= $id ?>" class="btn btn-outline btn-sm"><i class="fa-solid fa-pen"></i> Edit</a>
    <a href="appointment_add.php?patient_id=<?= $id ?>" class="btn btn-accent btn-sm"><i class="fa-solid fa-calendar-plus"></i> Book Appointment</a>
  </div>
</div>

<div class="panel">
  <div class="panel-head"><h2>Patient Information</h2></div>
  <div class="panel-body">
    <div class="detail-grid">
      <div class="detail-item"><div class="label">Gender</div><div class="value"><?= htmlspecialchars($patient['gender']) ?></div></div>
      <div class="detail-item"><div class="label">Date of Birth</div><div class="value"><?= htmlspecialchars(date('M j, Y', strtotime($patient['dob']))) ?> (<?= ageFromDob($patient['dob']) ?> yrs)</div></div>
      <div class="detail-item"><div class="label">Phone</div><div class="value"><?= htmlspecialchars($patient['phone']) ?></div></div>
      <div class="detail-item"><div class="label">Email</div><div class="value"><?= htmlspecialchars($patient['email'] ?: '—') ?></div></div>
      <div class="detail-item"><div class="label">Emergency Contact</div><div class="value"><?= htmlspecialchars($patient['emergency_contact'] ?: '—') ?></div></div>
      <div class="detail-item"><div class="label">Address</div><div class="value"><?= htmlspecialchars($patient['address'] ?: '—') ?></div></div>
    </div>
  </div>
</div>

<div class="panel">
  <div class="panel-head"><h2>Appointment History</h2></div>
  <div class="panel-body" style="padding:0;">
    <?php if (count($history) === 0): ?>
      <div class="empty-state">
        <span class="icon-badge"><i class="fa-solid fa-notes-medical"></i></span>
        <h3>No appointment history yet</h3>
        <p>This patient's past and upcoming appointments will show up here.</p>
      </div>
    <?php else: ?>
      <ul class="timeline" style="padding:8px 22px;">
        <?php foreach ($history as $h): ?>
          <li>
            <div class="timeline-row">
              <div>
                <strong><?= htmlspecialchars(date('M j, Y', strtotime($h['appointment_date']))) ?></strong>
                at <?= htmlspecialchars(date('g:i A', strtotime($h['appointment_time']))) ?>
                <div class="small">Dr. <?= htmlspecialchars($h['doctor_name']) ?> &middot; <?= htmlspecialchars($h['reason']) ?></div>
              </div>
              <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;">
                <span class="badge <?= statusBadgeClass($h['status']) ?>"><?= htmlspecialchars($h['status']) ?></span>
                <?php if ($h['status'] === 'Completed'): ?>
                  <span class="badge <?= outcomeBadgeClass($h['outcome']) ?>"><?= htmlspecialchars($h['outcome']) ?></span>
                <?php endif; ?>
                <a href="appointment_diagnosis.php?id=<?= $h['id'] ?>" class="btn btn-outline btn-sm">Diagnosis</a>
              </div>
            </div>

            <?php if (!empty($h['diagnosis']) || !empty($h['referral_hospital'])): ?>
              <div class="timeline-detail">
                <?php if (!empty($h['diagnosis'])): ?>
                  <div><strong>Diagnosis:</strong> <?= htmlspecialchars($h['diagnosis']) ?></div>
                <?php endif; ?>
                <?php if (!empty($h['referral_hospital'])): ?>
                  <div style="margin-top:4px;"><strong>Referred to:</strong> <?= htmlspecialchars($h['referral_hospital']) ?></div>
                <?php endif; ?>
              </div>
            <?php endif; ?>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </div>
</div>

<?php require_once 'includes/app_footer.php'; ?>
