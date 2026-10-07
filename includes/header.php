<?php
// Expects optional $pageTitle and $activePage to be set before including this file.
require_once __DIR__ . '/auth.php';
$pageTitle = $pageTitle ?? 'Harbor General';
$activePage = $activePage ?? '';
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($pageTitle) ?> | Harbor General</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="css/style.css">
</head>
<body>

<header class="site-header">
  <div class="nav-wrap">
    <a href="index.php" class="brand">
      <span class="brand-mark"><i class="fa-solid fa-house-medical"></i></span>
      Harbor <span>General</span>
    </a>

    <ul class="nav-links" id="navLinks">
      <li><a href="index.php" class="<?= $activePage === 'home' ? 'active' : '' ?>">Home</a></li>
      <li><a href="about.php" class="<?= $activePage === 'about' ? 'active' : '' ?>">About</a></li>
      <li><a href="services.php" class="<?= $activePage === 'services' ? 'active' : '' ?>">Services</a></li>
      <li><a href="doctors.php" class="<?= $activePage === 'doctors' ? 'active' : '' ?>">Doctors</a></li>
      <li><a href="contact.php" class="<?= $activePage === 'contact' ? 'active' : '' ?>">Contact</a></li>
      <li class="nav-actions">
        <?php if (isPatientLoggedIn()): ?>
          <a href="patient_portal.php" class="btn btn-outline btn-sm">My Visits</a>
          <a href="logout.php" class="btn btn-sm" style="color:var(--hg-ink-soft);">Logout</a>
        <?php elseif (isLoggedIn()): ?>
          <a href="dashboard.php" class="btn btn-outline btn-sm">Dashboard</a>
          <a href="logout.php" class="btn btn-sm" style="color:var(--hg-ink-soft);">Logout</a>
        <?php else: ?>
          <a href="patient_signup.php" class="btn btn-outline btn-sm">Sign Up</a>
          <a href="login.php" class="btn btn-primary btn-sm">Login</a>
        <?php endif; ?>
      </li>
    </ul>

    <button class="nav-toggle" id="navToggle" aria-label="Toggle navigation">
      <i class="fa-solid fa-bars"></i>
    </button>
  </div>
</header>
