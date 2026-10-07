<?php
require_once 'includes/db.php';
$pageTitle = 'Doctors';
$activePage = 'doctors';

$doctors = [];
$dbError = false;
try {
    $stmt = $pdo->query("SELECT full_name, specialty, department, status, photo FROM doctors ORDER BY full_name ASC");
    $doctors = $stmt->fetchAll();
} catch (Exception $e) {
    $dbError = true;
}

require_once 'includes/header.php';
?>

<section class="page-banner">
  <div class="container">
    <h1>Our Medical Team</h1>
    <p>Experienced doctors across every department at Harbor General.</p>
  </div>
</section>

<section class="section">
  <div class="container">
    <?php if ($dbError): ?>
      <div class="alert alert-error">Doctor information is temporarily unavailable. Please make sure the database is set up correctly.</div>
    <?php elseif (count($doctors) === 0): ?>
      <div class="empty-state">
        <span class="icon-badge"><i class="fa-solid fa-user-doctor"></i></span>
        <h3>No doctors listed yet</h3>
        <p>Our medical team profiles will appear here once staff add them through the dashboard.</p>
      </div>
    <?php else: ?>
      <div class="grid grid-3">
        <?php foreach ($doctors as $doc): ?>
          <div class="card doctor-card">
            <div class="doctor-photo">
              <?php $photoSrc = doctorPhotoSrc($doc['photo']); ?>
              <?php if ($photoSrc): ?>
                <img src="<?= htmlspecialchars($photoSrc) ?>" alt="<?= htmlspecialchars($doc['full_name']) ?>">
              <?php else: ?>
                <?= htmlspecialchars(strtoupper(substr($doc['full_name'], 0, 1))) ?>
              <?php endif; ?>
            </div>
            <div class="doctor-name" style="font-weight:600;"><?= htmlspecialchars($doc['full_name']) ?></div>
            <div class="doctor-specialty"><?= htmlspecialchars($doc['specialty']) ?></div>
            <div class="doctor-meta"><?= htmlspecialchars($doc['department']) ?> Department</div>
            <?php $status = $doc['status'] ?? 'Available'; ?>
            <span class="badge <?= $status === 'Available' ? 'badge-available' : 'badge-unavailable' ?>"><?= htmlspecialchars($status) ?></span>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>

<?php require_once 'includes/footer.php'; ?>
