<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
requireLogin();

$pageTitle = 'Patients';
$pageSubtitle = 'Register, search and manage patient records.';
$activeNav = 'patients';

$search = trim($_GET['q'] ?? '');

if ($search !== '') {
    $stmt = $pdo->prepare("SELECT * FROM patients WHERE full_name LIKE ? OR phone LIKE ? OR email LIKE ? ORDER BY registration_date DESC");
    $like = "%$search%";
    $stmt->execute([$like, $like, $like]);
} else {
    $stmt = $pdo->query("SELECT * FROM patients ORDER BY registration_date DESC");
}
$patients = $stmt->fetchAll();

function initials(string $name): string {
    $parts = preg_split('/\s+/', trim($name));
    $letters = array_map(fn($p) => strtoupper(substr($p, 0, 1)), array_slice($parts, 0, 2));
    return implode('', $letters);
}

require_once 'includes/app_header.php';
?>

<div class="panel">
  <div class="panel-head">
    <h2>All Patients (<?= count($patients) ?>)</h2>
    <a href="patient_add.php" class="btn btn-primary btn-sm"><i class="fa-solid fa-plus"></i> Register Patient</a>
  </div>
  <div class="panel-body">
    <form method="GET" action="patients.php" class="search-bar">
      <input type="text" name="q" placeholder="Search by name, phone or email..." value="<?= htmlspecialchars($search) ?>">
      <button type="submit" class="btn btn-outline btn-sm">Search</button>
      <?php if ($search !== ''): ?><a href="patients.php" class="btn btn-sm" style="color:var(--hg-ink-soft);">Clear</a><?php endif; ?>
    </form>
  </div>

  <?php if (count($patients) === 0): ?>
    <div class="empty-state">
      <span class="icon-badge"><i class="fa-solid fa-hospital-user"></i></span>
      <h3>No patients found</h3>
      <p><?= $search !== '' ? 'Try a different search term.' : 'Register your first patient to get started.' ?></p>
      <?php if ($search === ''): ?><a href="patient_add.php" class="btn btn-accent btn-sm">Register Patient</a><?php endif; ?>
    </div>
  <?php else: ?>
    <div class="table-wrap">
      <table class="data-table">
        <thead>
          <tr>
            <th>Patient</th>
            <th>Gender</th>
            <th>Date of Birth</th>
            <th>Phone</th>
            <th>Portal Access</th>
            <th>Registered</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($patients as $p): ?>
            <tr>
              <td>
                <div class="name-cell">
                  <span class="avatar-circle"><?= htmlspecialchars(initials($p['full_name'])) ?></span>
                  <div>
                    <div style="font-weight:600;"><?= htmlspecialchars($p['full_name']) ?></div>
                    <div class="small"><?= htmlspecialchars($p['email'] ?: '—') ?></div>
                  </div>
                </div>
              </td>
              <td><?= htmlspecialchars($p['gender']) ?></td>
              <td><?= htmlspecialchars(date('M j, Y', strtotime($p['dob']))) ?></td>
              <td><?= htmlspecialchars($p['phone']) ?></td>
              <td>
                <?php if (!empty($p['username']) && !empty($p['password'])): ?>
                  <span class="badge badge-available">Enabled</span>
                <?php else: ?>
                  <span class="badge badge-unavailable">Not set</span>
                <?php endif; ?>
              </td>
              <td><?= htmlspecialchars(date('M j, Y', strtotime($p['registration_date']))) ?></td>
              <td>
                <div class="row-actions">
                  <a href="patient_view.php?id=<?= $p['id'] ?>" class="btn btn-outline btn-sm">View</a>
                  <a href="patient_edit.php?id=<?= $p['id'] ?>" class="btn btn-sm" style="background:var(--hg-info-bg);color:var(--hg-info);">Edit</a>
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
