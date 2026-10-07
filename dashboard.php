<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
requireLogin();

$pageTitle = 'Dashboard';
$pageSubtitle = 'Welcome back, ' . currentStaffName() . '. Here\'s what\'s happening today.';
$activeNav = 'dashboard';

$totalPatients   = (int) $pdo->query("SELECT COUNT(*) FROM patients")->fetchColumn();
$totalDoctors    = (int) $pdo->query("SELECT COUNT(*) FROM doctors")->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE appointment_date = CURDATE()");
$stmt->execute();
$todayAppointments = (int) $stmt->fetchColumn();

$pendingAppointments = (int) $pdo->query("SELECT COUNT(*) FROM appointments WHERE status = 'Pending'")->fetchColumn();
$completedAppointments = (int) $pdo->query("SELECT COUNT(*) FROM appointments WHERE status = 'Completed'")->fetchColumn();

$stmt = $pdo->prepare("
    SELECT a.id, a.appointment_time, a.reason, a.status,
           p.full_name AS patient_name, d.full_name AS doctor_name
    FROM appointments a
    JOIN patients p ON p.id = a.patient_id
    JOIN doctors d ON d.id = a.doctor_id
    WHERE a.appointment_date = CURDATE()
    ORDER BY a.appointment_time ASC
");
$stmt->execute();
$todayList = $stmt->fetchAll();

function statusBadgeClass(string $status): string {
    return match ($status) {
        'Scheduled' => 'badge-scheduled',
        'Pending'   => 'badge-pending',
        'Completed' => 'badge-completed',
        'Cancelled' => 'badge-cancelled',
        default     => 'badge-scheduled',
    };
}

require_once 'includes/app_header.php';
?>

<div class="grid grid-4" style="margin-bottom:28px;">
  <div class="stat-card">
    <span class="stat-icon teal"><i class="fa-solid fa-hospital-user"></i></span>
    <div>
      <div class="stat-number"><?= $totalPatients ?></div>
      <div class="stat-label">Total Patients</div>
    </div>
  </div>
  <div class="stat-card">
    <span class="stat-icon blue"><i class="fa-solid fa-user-doctor"></i></span>
    <div>
      <div class="stat-number"><?= $totalDoctors ?></div>
      <div class="stat-label">Total Doctors</div>
    </div>
  </div>
  <div class="stat-card">
    <span class="stat-icon amber"><i class="fa-solid fa-calendar-day"></i></span>
    <div>
      <div class="stat-number"><?= $todayAppointments ?></div>
      <div class="stat-label">Today's Appointments</div>
    </div>
  </div>
  <div class="stat-card">
    <span class="stat-icon green"><i class="fa-solid fa-circle-check"></i></span>
    <div>
      <div class="stat-number"><?= $completedAppointments ?></div>
      <div class="stat-label">Completed Appointments</div>
    </div>
  </div>
</div>

<div class="grid grid-2" style="margin-bottom:28px;grid-template-columns:1fr 1fr;">
  <div class="stat-card">
    <span class="stat-icon amber"><i class="fa-solid fa-hourglass-half"></i></span>
    <div>
      <div class="stat-number"><?= $pendingAppointments ?></div>
      <div class="stat-label">Pending Appointments</div>
    </div>
  </div>
  <div class="stat-card">
    <span class="stat-icon teal"><i class="fa-solid fa-plus"></i></span>
    <div style="display:flex;gap:10px;flex-wrap:wrap;">
      <a href="patient_add.php" class="btn btn-primary btn-sm">Register Patient</a>
      <a href="appointment_add.php" class="btn btn-outline btn-sm">Book Appointment</a>
    </div>
  </div>
</div>

<div class="panel">
  <div class="panel-head">
    <h2>Today's Appointments — <?= date('l, F j, Y') ?></h2>
    <a href="appointments.php" class="btn btn-outline btn-sm">View All Appointments</a>
  </div>
  <div class="panel-body" style="padding:0;">
    <?php if (count($todayList) === 0): ?>
      <div class="empty-state">
        <span class="icon-badge"><i class="fa-solid fa-calendar-xmark"></i></span>
        <h3>No appointments today</h3>
        <p>New appointments booked for today will appear here.</p>
        <a href="appointment_add.php" class="btn btn-accent btn-sm">Book Appointment</a>
      </div>
    <?php else: ?>
      <div class="table-wrap">
        <table class="data-table">
          <thead>
            <tr>
              <th>Time</th>
              <th>Patient</th>
              <th>Doctor</th>
              <th>Reason</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($todayList as $row): ?>
              <tr>
                <td><?= htmlspecialchars(date('g:i A', strtotime($row['appointment_time']))) ?></td>
                <td><?= htmlspecialchars($row['patient_name']) ?></td>
                <td><?= htmlspecialchars($row['doctor_name']) ?></td>
                <td><?= htmlspecialchars($row['reason']) ?></td>
                <td><span class="badge <?= statusBadgeClass($row['status']) ?>"><?= htmlspecialchars($row['status']) ?></span></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</div>

<?php require_once 'includes/app_footer.php'; ?>
