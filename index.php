<?php
require_once 'includes/db.php';
$pageTitle = 'Home';
$activePage = 'home';

$previewDoctors = [];
try {
    $stmt = $pdo->query("SELECT full_name, specialty, department, photo FROM doctors ORDER BY id DESC LIMIT 3");
    $previewDoctors = $stmt->fetchAll();
} catch (Exception $e) {
    $previewDoctors = [];
}

require_once 'includes/header.php';
?>

<!-- HERO -->
<section class="hero">
  <div class="container hero-grid">
    <div>
      <h1>Care that puts your health first, every single visit.</h1>
      <p class="lede">Harbor General is a modern healthcare facility offering general medicine, emergency care, and specialist services — backed by a team that treats every patient like family.</p>
      <div class="hero-actions">
        <a href="contact.php" class="btn btn-accent">Book an Appointment</a>
        <a href="services.php" class="btn btn-outline">Explore Services</a>
      </div>
      <div class="hero-stats">
        <div class="hero-stat"><strong>15+</strong><span>Specialist Departments</span></div>
        <div class="hero-stat"><strong>30+</strong><span>Qualified Doctors</span></div>
        <div class="hero-stat"><strong>24/7</strong><span>Emergency Care</span></div>
      </div>
    </div>
    <div class="hero-art">
      <div class="hero-art-header">
        <span class="dot"><i class="fa-solid fa-calendar-check"></i></span>
        <div>
          <strong>Today's Care Snapshot</strong>
          <p class="small" style="margin:0;">Live overview inside our staff dashboard</p>
        </div>
      </div>
      <ul class="hero-art-list">
        <li><i class="fa-solid fa-user-check"></i>&nbsp; Patient registration &amp; records</li>
        <li><i class="fa-solid fa-user-doctor"></i>&nbsp; Doctor scheduling by department</li>
        <li><i class="fa-solid fa-calendar-days"></i>&nbsp; Appointment tracking &amp; status</li>
        <li><i class="fa-solid fa-notes-medical"></i>&nbsp; Patients can view their own visit history</li>
      </ul>
    </div>
  </div>
</section>

<!-- INTRO -->
<section class="section">
  <div class="container">
    <div class="section-header">
      <h2>Welcome to Harbor General</h2>
      <p>For years, Harbor General has provided accessible, high-quality healthcare to our community. Our facility combines experienced medical staff with a calm, modern environment designed around patient comfort.</p>
    </div>
    <div class="grid grid-3">
      <div class="card">
        <span class="icon-badge"><i class="fa-solid fa-heart-pulse"></i></span>
        <h3>Patient-Centered Care</h3>
        <p>Every treatment plan starts with listening. Our staff take the time to understand each patient's needs.</p>
      </div>
      <div class="card">
        <span class="icon-badge"><i class="fa-solid fa-user-doctor"></i></span>
        <h3>Experienced Specialists</h3>
        <p>Our doctors span general medicine, pediatrics, cardiology and more — all under one roof.</p>
      </div>
      <div class="card">
        <span class="icon-badge"><i class="fa-solid fa-shield-heart"></i></span>
        <h3>Safe &amp; Reliable</h3>
        <p>Clean facilities and clear processes, from registration to follow-up appointments.</p>
      </div>
    </div>
  </div>
</section>

<!-- WHY CHOOSE US -->
<section class="section section-tint">
  <div class="container">
    <div class="section-header">
      <h2>Why Choose Harbor General</h2>
      <p>We built our services around three things patients consistently ask for: speed, clarity, and trust.</p>
    </div>
    <div class="grid grid-4">
      <div class="card">
        <span class="icon-badge"><i class="fa-solid fa-clock"></i></span>
        <h3 style="font-size:1.05rem;">Fast Appointments</h3>
        <p style="font-size:0.92rem;">Simple booking with clear scheduling and status updates.</p>
      </div>
      <div class="card">
        <span class="icon-badge"><i class="fa-solid fa-hospital"></i></span>
        <h3 style="font-size:1.05rem;">Modern Facility</h3>
        <p style="font-size:0.92rem;">Clean, well-equipped departments across every specialty.</p>
      </div>
      <div class="card">
        <span class="icon-badge"><i class="fa-solid fa-file-medical"></i></span>
        <h3 style="font-size:1.05rem;">Organized Records</h3>
        <p style="font-size:0.92rem;">Your visit history is tracked accurately for better follow-up care.</p>
      </div>
      <div class="card">
        <span class="icon-badge"><i class="fa-solid fa-hand-holding-medical"></i></span>
        <h3 style="font-size:1.05rem;">Compassionate Staff</h3>
        <p style="font-size:0.92rem;">A team that treats every patient with patience and respect.</p>
      </div>
    </div>
  </div>
</section>

<!-- DOCTORS PREVIEW -->
<section class="section">
  <div class="container">
    <div class="pagetitle-row">
      <div class="section-header" style="margin-bottom:0;">
        <h2>Meet Our Medical Team</h2>
        <p>A snapshot of the specialists ready to care for you.</p>
      </div>
      <a href="doctors.php" class="btn btn-outline">View All Doctors</a>
    </div>

    <?php if (count($previewDoctors) > 0): ?>
      <div class="grid grid-3">
        <?php foreach ($previewDoctors as $doc): ?>
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
          </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div class="empty-state">
        <span class="icon-badge"><i class="fa-solid fa-user-doctor"></i></span>
        <p>Doctor profiles will appear here once added by staff.</p>
        <a href="doctors.php" class="btn btn-outline btn-sm">Visit Doctors Page</a>
      </div>
    <?php endif; ?>
  </div>
</section>

<!-- PATIENT PORTAL CTA -->
<section class="section section-tint">
  <div class="container">
    <div class="grid grid-2" style="align-items:center;">
      <div>
        <h2>Already a Harbor General patient?</h2>
        <p>Log in to your patient portal to see your visit history, diagnoses, and treatment outcomes from your past appointments.</p>
        <div style="display:flex;gap:12px;flex-wrap:wrap;">
          <a href="login.php" class="btn btn-primary">Log In</a>
          <a href="patient_signup.php" class="btn btn-outline">New Patient? Sign Up</a>
        </div>
      </div>
      <div class="card">
        <span class="icon-badge"><i class="fa-solid fa-notes-medical"></i></span>
        <h3>What you'll find in your portal</h3>
        <ul style="padding-left:18px;color:var(--hg-ink-soft);margin:0;">
          <li>Every past and upcoming appointment</li>
          <li>The diagnosis recorded for each visit</li>
          <li>Whether treatment was successful or you were referred elsewhere</li>
        </ul>
      </div>
    </div>
  </div>
</section>

<!-- CTA -->
<section class="section">
  <div class="container text-center">
    <h2>Ready to schedule your visit?</h2>
    <p style="max-width:52ch;margin:0 auto 24px;">Reach out to our team and we'll get you connected with the right department.</p>
    <a href="contact.php" class="btn btn-accent">Contact Us</a>
  </div>
</section>

<?php require_once 'includes/footer.php'; ?>
