<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
requirePatientLogin();

$pageTitle = 'My Visits';
$activePage = 'portal';

$stmt = $pdo->prepare("SELECT * FROM patients WHERE id = ?");
$stmt->execute([$_SESSION['patient_id']]);
$patient = $stmt->fetch();

if (!$patient) {
    // Account was removed after logging in — clear the session and send them away politely
    unset($_SESSION['patient_id'], $_SESSION['patient_name']);
    header('Location: login.php');
    exit;
}

$stmt = $pdo->prepare("
    SELECT a.appointment_date, a.appointment_time, a.reason, a.status,
           a.diagnosis, a.outcome, a.referral_hospital,
           d.full_name AS doctor_name
    FROM appointments a
    JOIN doctors d ON d.id = a.doctor_id
    WHERE a.patient_id = ?
    ORDER BY a.appointment_date DESC, a.appointment_time DESC
");
$stmt->execute([$patient['id']]);
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

require_once 'includes/header.php';
?>

<section class="page-banner">
  <div class="container">
    <h1>Welcome back, <?= htmlspecialchars(explode(' ', $patient['full_name'])[0]) ?></h1>
    <p>Here's a record of every visit you've had with us at Harbor General.</p>
  </div>
</section>

<section class="section">
  <div class="container">

    <div class="panel">
      <div class="panel-head"><h2>Your Information</h2></div>
      <div class="panel-body">
        <div class="detail-grid">
          <div class="detail-item"><div class="label">Full Name</div><div class="value"><?= htmlspecialchars($patient['full_name']) ?></div></div>
          <div class="detail-item"><div class="label">Username</div><div class="value"><?= htmlspecialchars($patient['username']) ?></div></div>
          <div class="detail-item"><div class="label">Phone</div><div class="value"><?= htmlspecialchars($patient['phone']) ?></div></div>
          <div class="detail-item"><div class="label">Email</div><div class="value"><?= htmlspecialchars($patient['email'] ?: '—') ?></div></div>
          <div class="detail-item"><div class="label">Patient Since</div><div class="value"><?= htmlspecialchars(date('M j, Y', strtotime($patient['registration_date']))) ?></div></div>
        </div>
        <p class="small" style="margin-top:14px;margin-bottom:0;">Need to update your details? Please speak to our front desk on your next visit.</p>
      </div>
    </div>

    <div class="panel">
      <div class="panel-head"><h2>Visit History (<?= count($history) ?>)</h2></div>
      <div class="panel-body" style="padding:0;">
        <?php if (count($history) === 0): ?>
          <div class="empty-state">
            <span class="icon-badge"><i class="fa-solid fa-notes-medical"></i></span>
            <h3>No visits recorded yet</h3>
            <p>Once you've had an appointment with us, it will appear here.</p>
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
                  <div style="display:flex;gap:8px;flex-wrap:wrap;">
                    <span class="badge <?= statusBadgeClass($h['status']) ?>"><?= htmlspecialchars($h['status']) ?></span>
                    <?php if ($h['status'] === 'Completed'): ?>
                      <span class="badge <?= outcomeBadgeClass($h['outcome']) ?>"><?= htmlspecialchars($h['outcome']) ?></span>
                    <?php endif; ?>
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

  </div>
</section>

<?php require_once 'includes/footer.php'; ?>
