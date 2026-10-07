<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
requireLogin();

$pageTitle = 'Appointments';
$pageSubtitle = "Book, track and update patient appointments.";
$activeNav = 'appointments';

// Handle inline status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['appointment_id'], $_POST['new_status'])) {
    $validStatuses = ['Scheduled', 'Pending', 'Completed', 'Cancelled'];
    $newStatus = $_POST['new_status'];
    if (in_array($newStatus, $validStatuses, true)) {
        $stmt = $pdo->prepare("UPDATE appointments SET status = ? WHERE id = ?");
        $stmt->execute([$newStatus, (int) $_POST['appointment_id']]);
        setFlash('success', 'Appointment status updated to "' . $newStatus . '".');
    }
    header('Location: appointments.php' . (isset($_GET['filter']) ? '?filter=' . urlencode($_GET['filter']) : ''));
    exit;
}

$filter = trim($_GET['filter'] ?? '');
$sql = "SELECT a.*, p.full_name AS patient_name, d.full_name AS doctor_name
        FROM appointments a
        JOIN patients p ON p.id = a.patient_id
        JOIN doctors d ON d.id = a.doctor_id";
$params = [];
if ($filter === 'today') {
    $sql .= " WHERE a.appointment_date = CURDATE()";
} elseif (in_array($filter, ['Scheduled', 'Pending', 'Completed', 'Cancelled'], true)) {
    $sql .= " WHERE a.status = ?";
    $params[] = $filter;
}
$sql .= " ORDER BY a.appointment_date DESC, a.appointment_time DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$appointments = $stmt->fetchAll();

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

require_once 'includes/app_header.php';
?>

<div class="panel">
  <div class="panel-head">
    <h2>All Appointments (<?= count($appointments) ?>)</h2>
    <a href="appointment_add.php" class="btn btn-primary btn-sm"><i class="fa-solid fa-plus"></i> Book Appointment</a>
  </div>
  <div class="panel-body">
    <div class="filters-bar">
      <a href="appointments.php" class="btn btn-sm <?= $filter === '' ? 'btn-primary' : 'btn-outline' ?>">All</a>
      <a href="appointments.php?filter=today" class="btn btn-sm <?= $filter === 'today' ? 'btn-primary' : 'btn-outline' ?>">Today</a>
      <a href="appointments.php?filter=Scheduled" class="btn btn-sm <?= $filter === 'Scheduled' ? 'btn-primary' : 'btn-outline' ?>">Scheduled</a>
      <a href="appointments.php?filter=Pending" class="btn btn-sm <?= $filter === 'Pending' ? 'btn-primary' : 'btn-outline' ?>">Pending</a>
      <a href="appointments.php?filter=Completed" class="btn btn-sm <?= $filter === 'Completed' ? 'btn-primary' : 'btn-outline' ?>">Completed</a>
      <a href="appointments.php?filter=Cancelled" class="btn btn-sm <?= $filter === 'Cancelled' ? 'btn-primary' : 'btn-outline' ?>">Cancelled</a>
    </div>
  </div>

  <?php if (count($appointments) === 0): ?>
    <div class="empty-state">
      <span class="icon-badge"><i class="fa-solid fa-calendar-xmark"></i></span>
      <h3>No appointments found</h3>
      <p>Try a different filter, or book a new appointment.</p>
      <a href="appointment_add.php" class="btn btn-accent btn-sm">Book Appointment</a>
    </div>
  <?php else: ?>
    <div class="table-wrap">
      <table class="data-table">
        <thead>
          <tr>
            <th>Date</th>
            <th>Time</th>
            <th>Patient</th>
            <th>Doctor</th>
            <th>Reason</th>
            <th>Status</th>
            <th>Outcome</th>
            <th>Update Status</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($appointments as $a): ?>
            <tr>
              <td><?= htmlspecialchars(date('M j, Y', strtotime($a['appointment_date']))) ?></td>
              <td><?= htmlspecialchars(date('g:i A', strtotime($a['appointment_time']))) ?></td>
              <td><a href="patient_view.php?id=<?= $a['patient_id'] ?>"><?= htmlspecialchars($a['patient_name']) ?></a></td>
              <td>Dr. <?= htmlspecialchars($a['doctor_name']) ?></td>
              <td><?= htmlspecialchars($a['reason']) ?></td>
              <td><span class="badge <?= statusBadgeClass($a['status']) ?>"><?= htmlspecialchars($a['status']) ?></span></td>
              <td>
                <?php if ($a['status'] === 'Completed'): ?>
                  <span class="badge <?= outcomeBadgeClass($a['outcome']) ?>"><?= htmlspecialchars($a['outcome']) ?></span>
                <?php else: ?>
                  <span class="small">—</span>
                <?php endif; ?>
              </td>
              <td>
                <form method="POST" action="appointments.php<?= $filter !== '' ? '?filter=' . urlencode($filter) : '' ?>" style="display:flex;gap:6px;">
                  <input type="hidden" name="appointment_id" value="<?= $a['id'] ?>">
                  <select name="new_status" onchange="this.form.submit()" style="padding:6px 8px;font-size:0.85rem;">
                    <?php foreach (['Scheduled', 'Pending', 'Completed', 'Cancelled'] as $s): ?>
                      <option value="<?= $s ?>" <?= $a['status'] === $s ? 'selected' : '' ?>><?= $s ?></option>
                    <?php endforeach; ?>
                  </select>
                  <noscript><button type="submit" class="btn btn-outline btn-sm">Save</button></noscript>
                </form>
              </td>
              <td><a href="appointment_diagnosis.php?id=<?= $a['id'] ?>" class="btn btn-outline btn-sm">Diagnosis</a></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<?php require_once 'includes/app_footer.php'; ?>
