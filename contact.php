<?php
require_once 'includes/db.php';
$pageTitle = 'Contact Us';
$activePage = 'contact';

$success = false;
$errors = [];
$old = ['name' => '', 'email' => '', 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old['name'] = trim($_POST['name'] ?? '');
    $old['email'] = trim($_POST['email'] ?? '');
    $old['message'] = trim($_POST['message'] ?? '');

    if ($old['name'] === '') $errors[] = 'Please enter your name.';
    if ($old['email'] === '' || !filter_var($old['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Please enter a valid email address.';
    if ($old['message'] === '') $errors[] = 'Please enter a message.';

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO contact_messages (name, email, message, submitted_at) VALUES (?, ?, ?, NOW())");
            $stmt->execute([$old['name'], $old['email'], $old['message']]);
            $success = true;
            $old = ['name' => '', 'email' => '', 'message' => ''];
        } catch (Exception $e) {
            $errors[] = 'Sorry, your message could not be sent right now. Please try again later.';
        }
    }
}

require_once 'includes/header.php';
?>

<section class="page-banner">
  <div class="container">
    <h1>Contact Harbor General</h1>
    <p>We're here to help — reach out with any question or to arrange your visit.</p>
  </div>
</section>

<section class="section">
  <div class="container contact-grid">
    <div>
      <h2>Get in Touch</h2>
      <div class="contact-info-item">
        <span class="icon-badge"><i class="fa-solid fa-location-dot"></i></span>
        <div>
          <strong>Address</strong>
          <p style="margin:0;">24 Harbor Bay Road, Victoria Island, Lagos, Nigeria</p>
        </div>
      </div>
      <div class="contact-info-item">
        <span class="icon-badge"><i class="fa-solid fa-phone"></i></span>
        <div>
          <strong>Phone</strong>
          <p style="margin:0;">+234 800 123 4567</p>
        </div>
      </div>
      <div class="contact-info-item">
        <span class="icon-badge"><i class="fa-solid fa-envelope"></i></span>
        <div>
          <strong>Email</strong>
          <p style="margin:0;">info@harborgeneral.example</p>
        </div>
      </div>
      <div class="contact-info-item">
        <span class="icon-badge"><i class="fa-solid fa-clock"></i></span>
        <div>
          <strong>Opening Hours</strong>
          <p style="margin:0;">Mon – Sat: 8:00 AM – 8:00 PM<br>Emergency Care: 24/7</p>
        </div>
      </div>
      <div class="map-placeholder">
        <span><i class="fa-solid fa-map-location-dot" style="font-size:1.4rem;display:block;margin-bottom:8px;"></i>Map location — 24 Harbor Bay Road, Lagos</span>
      </div>
    </div>

    <div class="card">
      <h2 style="margin-bottom:18px;">Send Us a Message</h2>

      <?php if ($success): ?>
        <div class="alert alert-success"><i class="fa-solid fa-circle-check"></i> Thank you — your message has been received. Our team will get back to you shortly.</div>
      <?php endif; ?>

      <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
          <i class="fa-solid fa-circle-exclamation"></i>
          <span><?= implode('<br>', array_map('htmlspecialchars', $errors)) ?></span>
        </div>
      <?php endif; ?>

      <form method="POST" action="contact.php" novalidate>
        <div class="form-group">
          <label class="required" for="name">Full Name</label>
          <input type="text" id="name" name="name" value="<?= htmlspecialchars($old['name']) ?>" required>
        </div>
        <div class="form-group">
          <label class="required" for="email">Email Address</label>
          <input type="email" id="email" name="email" value="<?= htmlspecialchars($old['email']) ?>" required>
        </div>
        <div class="form-group">
          <label class="required" for="message">Message</label>
          <textarea id="message" name="message" required><?= htmlspecialchars($old['message']) ?></textarea>
        </div>
        <button type="submit" class="btn btn-accent btn-block">Send Message</button>
      </form>
    </div>
  </div>
</section>

<?php require_once 'includes/footer.php'; ?>
