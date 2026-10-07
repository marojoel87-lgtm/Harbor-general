<?php
// Expects: $pageTitle, $activeNav (dashboard|patients|doctors|appointments), optional $pageSubtitle
$pageTitle = $pageTitle ?? 'Dashboard';
$activeNav = $activeNav ?? '';
$flash = getFlash();
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

<div class="app-shell">
  <aside class="app-sidebar" id="appSidebar">
    <a href="dashboard.php" class="brand">
      <span class="brand-mark"><i class="fa-solid fa-house-medical"></i></span>
      Harbor <span>General</span>
    </a>
    <ul class="side-nav">
      <li><a href="dashboard.php" class="<?= $activeNav === 'dashboard' ? 'active' : '' ?>"><span class="icon"><i class="fa-solid fa-gauge"></i></span> Dashboard</a></li>
      <li><a href="patients.php" class="<?= $activeNav === 'patients' ? 'active' : '' ?>"><span class="icon"><i class="fa-solid fa-hospital-user"></i></span> Patients</a></li>
      <li><a href="doctors_manage.php" class="<?= $activeNav === 'doctors' ? 'active' : '' ?>"><span class="icon"><i class="fa-solid fa-user-doctor"></i></span> Doctors</a></li>
      <li><a href="appointments.php" class="<?= $activeNav === 'appointments' ? 'active' : '' ?>"><span class="icon"><i class="fa-solid fa-calendar-days"></i></span> Appointments</a></li>
    </ul>
    <div class="sidebar-foot">
      <span class="staff-name"><?= htmlspecialchars(currentStaffName()) ?></span>
      <span><?= htmlspecialchars(currentStaffRole()) ?></span>
      <div style="margin-top:10px;">
        <a href="logout.php"><i class="fa-solid fa-arrow-right-from-bracket"></i> Logout</a>
      </div>
    </div>
  </aside>

  <div class="sidebar-overlay" id="sidebarOverlay" style="display:none;"></div>

  <main class="app-main">
    <div class="mobile-topbar">
      <button id="sidebarToggle" aria-label="Open menu"><i class="fa-solid fa-bars"></i></button>
      <span class="brand" style="color:#fff;font-size:1.05rem;">Harbor <span style="color:var(--hg-accent);">General</span></span>
      <span style="width:24px;"></span>
    </div>

    <div class="app-topbar">
      <div>
        <h1><?= htmlspecialchars($pageTitle) ?></h1>
        <?php if (!empty($pageSubtitle)): ?><p class="small mb-0"><?= htmlspecialchars($pageSubtitle) ?></p><?php endif; ?>
      </div>
    </div>

    <div class="app-content">
      <?php if ($flash): ?>
        <div class="alert alert-<?= htmlspecialchars($flash['type']) ?>" data-autohide>
          <i class="fa-solid fa-circle-<?= $flash['type'] === 'success' ? 'check' : 'exclamation' ?>"></i>
          <?= htmlspecialchars($flash['message']) ?>
        </div>
      <?php endif; ?>
