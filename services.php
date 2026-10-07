<?php
require_once 'includes/db.php';
$pageTitle = 'Services & Departments';
$activePage = 'services';
require_once 'includes/header.php';

$services = [
    ['icon' => 'fa-stethoscope',      'name' => 'General Medicine',    'desc' => 'Routine check-ups, diagnosis, and treatment for everyday health concerns.'],
    ['icon' => 'fa-truck-medical',    'name' => 'Emergency Care',      'desc' => '24/7 urgent care for accidents, injuries, and critical conditions.'],
    ['icon' => 'fa-child-reaching',   'name' => 'Pediatrics',          'desc' => 'Specialized healthcare for infants, children, and adolescents.'],
    ['icon' => 'fa-heart-pulse',      'name' => 'Cardiology',          'desc' => 'Diagnosis and management of heart and cardiovascular conditions.'],
    ['icon' => 'fa-vial',             'name' => 'Laboratory Services', 'desc' => 'Accurate diagnostic testing to support fast, informed treatment.'],
    ['icon' => 'fa-pills',            'name' => 'Pharmacy',            'desc' => 'On-site dispensing of prescribed medication with pharmacist guidance.'],
    ['icon' => 'fa-user-clock',       'name' => 'Outpatient Services', 'desc' => 'Scheduled consultations and follow-ups without hospital admission.'],
    ['icon' => 'fa-bone',             'name' => 'Orthopedics',         'desc' => 'Care for bones, joints, and muscles, from injury to recovery.'],
];
?>

<section class="page-banner">
  <div class="container">
    <h1>Services &amp; Departments</h1>
    <p>Comprehensive care across the departments you need most.</p>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="grid grid-3">
      <?php foreach ($services as $s): ?>
        <div class="card service-card">
          <span class="icon-badge"><i class="fa-solid <?= $s['icon'] ?>"></i></span>
          <h3><?= htmlspecialchars($s['name']) ?></h3>
          <p><?= htmlspecialchars($s['desc']) ?></p>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section section-tint text-center">
  <div class="container">
    <h2>Not sure which department you need?</h2>
    <p style="max-width:50ch;margin:0 auto 22px;">Contact our front desk and we'll help direct you to the right specialist.</p>
    <a href="contact.php" class="btn btn-accent">Get in Touch</a>
  </div>
</section>

<?php require_once 'includes/footer.php'; ?>
