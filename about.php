<?php
require_once 'includes/db.php';
$pageTitle = 'About Us';
$activePage = 'about';
require_once 'includes/header.php';
?>

<section class="page-banner">
  <div class="container">
    <h1>About Harbor General</h1>
    <p>Getting to know the people and principles behind your care.</p>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="grid grid-2" style="align-items:center;">
      <div>
        <h2>Our Story</h2>
        <p>Harbor General was founded to give our community dependable access to quality healthcare — from routine check-ups to urgent care. Over the years, we've grown into a multi-department facility while keeping the same principle at our core: every patient deserves clear communication and attentive treatment.</p>
        <p>Today, Harbor General brings together general medicine, emergency care, and a range of specialist departments under one modern, welcoming roof.</p>
      </div>
      <div class="card">
        <span class="icon-badge"><i class="fa-solid fa-building-circle-check"></i></span>
        <h3>At a Glance</h3>
        <ul style="padding-left:18px;color:var(--hg-ink-soft);">
          <li>Multiple specialist departments</li>
          <li>Qualified, experienced doctors</li>
          <li>Digital patient &amp; appointment records</li>
          <li>Emergency care availability</li>
        </ul>
      </div>
    </div>
  </div>
</section>

<section class="section section-tint">
  <div class="container">
    <div class="grid grid-3">
      <div class="card value-card">
        <span class="icon-badge"><i class="fa-solid fa-bullseye"></i></span>
        <h3>Our Mission</h3>
        <p>To provide accessible, high-quality healthcare through skilled staff, modern processes, and genuine care for every patient who walks through our doors.</p>
      </div>
      <div class="card value-card">
        <span class="icon-badge"><i class="fa-solid fa-eye"></i></span>
        <h3>Our Vision</h3>
        <p>To be a healthcare provider our community trusts first — known for reliability, compassion, and consistently good outcomes.</p>
      </div>
      <div class="card value-card">
        <span class="icon-badge"><i class="fa-solid fa-hands-holding-child"></i></span>
        <h3>Our Core Values</h3>
        <p>Compassion, integrity, and continuous improvement guide every decision we make, from the front desk to the treatment room.</p>
      </div>
    </div>
  </div>
</section>

<section class="section text-center">
  <div class="container">
    <h2>A message from Harbor General</h2>
    <p style="max-width:60ch;margin:0 auto;">"We believe healthcare should feel personal, not procedural. Every patient who visits Harbor General is treated with the attention and respect they deserve." — Harbor General Medical Board</p>
  </div>
</section>

<?php require_once 'includes/footer.php'; ?>
