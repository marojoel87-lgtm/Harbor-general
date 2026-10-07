<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
requireLogin();

$pageTitle = 'Doctors';
$pageSubtitle = 'Manage medical staff records.';
$activeNav = 'doctors';

// Handle delete
if (isset($_GET['delete'])) {
    $delId = (int) $_GET['delete'];
    $check = $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE doctor_id = ?");
    $check->execute([$delId]);
    if ($check->fetchColumn() > 0) {
        setFlash('error', 'This doctor cannot be deleted because they have existing appointments.');
    } else {
        $pdo->prepare("DELETE FROM doctors WHERE id = ?")->execute([$delId]);
        setFlash('success', 'Doctor record deleted.');
    }
    header('Location: doctors_manage.php');
    exit;
}

$search = trim($_GET['q'] ?? '');
$dept = trim($_GET['department'] ?? '');

$sql = "SELECT * FROM doctors WHERE 1=1";
$params = [];
if ($search !== '') {
    $sql .= " AND (full_name LIKE ? OR specialty LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if ($dept !== '') {
    $sql .= " AND department = ?";
    $params[] = $dept;
}
$sql .= " ORDER BY full_name ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$doctors = $stmt->fetchAll();

$departments = $pdo->query("SELECT DISTINCT department FROM doctors ORDER BY department ASC")->fetchAll(PDO::FETCH_COLUMN);

require_once 'includes/app_header.php';
?>

<div class="panel">
  <div class="panel-head">
    <h2>All Doctors (<?= count($doctors) ?>)</h2>
    <a href="doctor_add.php" class="btn btn-primary btn-sm"><i class="fa-solid fa-plus"></i> Add Doctor</a>
  </div>
  <div class="panel-body">
    <form method="GET" action="doctors_manage.php" class="filters-bar">
      <input type="text" name="q" placeholder="Search by name or specialty..." value="<?= htmlspecialchars($search) ?>" style="max-width:260px;">
      <select name="department" onchange="this.form.submit()">
        <option value="">All Departments</option>
        <?php foreach ($departments as $d): ?>
          <option value="<?= htmlspecialchars($d) ?>" <?= $dept === $d ? 'selected' : '' ?>><?= htmlspecialchars($d) ?></option>
        <?php endforeach; ?>
      </select>
      <button type="submit" class="btn btn-outline btn-sm">Filter</button>
      <?php if ($search !== '' || $dept !== ''): ?><a href="doctors_manage.php" class="btn btn-sm" style="color:var(--hg-ink-soft);">Clear</a><?php endif; ?>
    </form>
  </div>

  <?php if (count($doctors) === 0): ?>
    <div class="empty-state">
      <span class="icon-badge"><i class="fa-solid fa-user-doctor"></i></span>
      <h3>No doctors found</h3>
      <p>Add a doctor to the system to get started.</p>
      <a href="doctor_add.php" class="btn btn-accent btn-sm">Add Doctor</a>
    </div>
  <?php else: ?>
    <div class="table-wrap">
      <table class="data-table">
        <thead>
          <tr>
            <th>Doctor</th>
            <th>Specialty</th>
            <th>Department</th>
            <th>Phone</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($doctors as $d): ?>
            <tr>
              <td>
                <div class="name-cell">
                  <span class="avatar-circle">
                    <?php $photoSrc = doctorPhotoSrc($d['photo']); ?>
                    <?php if ($photoSrc): ?>
                      <img src="<?= htmlspecialchars($photoSrc) ?>" alt="<?= htmlspecialchars($d['full_name']) ?>">
                    <?php else: ?>
                      <?= htmlspecialchars(strtoupper(substr($d['full_name'], 0, 1))) ?>
                    <?php endif; ?>
                  </span>
                  <div style="font-weight:600;">Dr. <?= htmlspecialchars($d['full_name']) ?></div>
                </div>
              </td>
              <td><?= htmlspecialchars($d['specialty']) ?></td>
              <td><?= htmlspecialchars($d['department']) ?></td>
              <td><?= htmlspecialchars($d['phone']) ?></td>
              <td><span class="badge <?= $d['status'] === 'Available' ? 'badge-available' : 'badge-unavailable' ?>"><?= htmlspecialchars($d['status']) ?></span></td>
              <td>
                <div class="row-actions">
                  <a href="doctor_edit.php?id=<?= $d['id'] ?>" class="btn btn-sm" style="background:var(--hg-info-bg);color:var(--hg-info);">Edit</a>
                  <a href="doctors_manage.php?delete=<?= $d['id'] ?>" class="btn btn-danger btn-sm" data-confirm="Delete Dr. <?= htmlspecialchars(addslashes($d['full_name'])) ?>? This cannot be undone.">Delete</a>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<?php require_once 'includes/app_footer.php'; ?>
