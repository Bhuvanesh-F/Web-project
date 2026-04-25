<?php
// contact.php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';

$errors  = [];
$success = '';
$old     = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();

    $name    = trim($_POST['name']    ?? '');
    $email   = strtolower(trim($_POST['email']   ?? ''));
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    $old = compact('name','email','subject','message');

    if (strlen($name) < 2)                            $errors['name']    = 'Enter your name.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL))    $errors['email']   = 'Enter a valid email.';
    if (strlen($message) < 10)                         $errors['message'] = 'Message must be at least 10 characters.';

    if (empty($errors)) {
        $stmt = $conn->prepare(
            'INSERT INTO contact_messages (name,email,subject,message) VALUES (?,?,?,?)'
        );
        $stmt->bind_param('ssss', $name, $email, $subject, $message);
        if ($stmt->execute()) {
            $success = 'Thank you! Your message has been sent. We will get back to you shortly.';
            $old = [];
        } else {
            $errors['general'] = 'Could not send message. Please try again.';
        }
        $stmt->close();
    }
}

$pageTitle = 'Contact Us';
$navActive  = 'contact';
include __DIR__ . '/includes/header.php';
?>
<main>
<div class="container page-content">
  <h1>VitalCare</h1>
  <h2>Contact Us</h2>

  <?php if ($success): ?>
    <div class="alert alert-success"><?= h($success) ?></div>
  <?php endif; ?>

  <div class="contact-grid">
    <!-- Contact Form -->
    <div class="card">
      <h3 style="margin-bottom:20px;">Send us a Message</h3>

      <?php if (!empty($errors['general'])): ?>
        <div class="alert alert-danger"><?= h($errors['general']) ?></div>
      <?php endif; ?>

      <form method="POST" action="/vitalcare/contact.php" novalidate>
        <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">

        <div class="form-group">
          <label>Your Name *</label>
          <input type="text" name="name" value="<?= h($old['name'] ?? '') ?>"
                 class="<?= isset($errors['name']) ? 'input-error' : '' ?>" required>
          <span class="form-error <?= isset($errors['name']) ? 'show' : '' ?>"><?= h($errors['name'] ?? '') ?></span>
        </div>

        <div class="form-group">
          <label>Email Address *</label>
          <input type="email" name="email" value="<?= h($old['email'] ?? '') ?>"
                 class="<?= isset($errors['email']) ? 'input-error' : '' ?>" required>
          <span class="form-error <?= isset($errors['email']) ? 'show' : '' ?>"><?= h($errors['email'] ?? '') ?></span>
        </div>

        <div class="form-group">
          <label>Subject</label>
          <input type="text" name="subject" value="<?= h($old['subject'] ?? '') ?>">
        </div>

        <div class="form-group">
          <label>Message *</label>
          <textarea name="message" rows="5"
                    class="<?= isset($errors['message']) ? 'input-error' : '' ?>"><?= h($old['message'] ?? '') ?></textarea>
          <span class="form-error <?= isset($errors['message']) ? 'show' : '' ?>"><?= h($errors['message'] ?? '') ?></span>
        </div>

        <button type="submit" class="btn">Send Message</button>
      </form>
    </div>

    <!-- Contact Info -->
    <div class="contact-info">
      <div class="card" style="margin-bottom:20px;">
        <h3>📍 Find Us</h3>
        <p><strong>Address:</strong><br>VitalCare Clinic, Royal Road, Port Louis, Mauritius</p>
        <p><strong>Opening Hours:</strong><br>Mon–Fri: 8:00 AM – 6:00 PM<br>Sat: 8:00 AM – 1:00 PM<br>Emergency: 24/7</p>
      </div>
      <div class="card">
        <h3>📞 Get in Touch</h3>
        <p><strong>Phone:</strong> (+230) 432 1987</p>
        <p><strong>Emergency:</strong> 118</p>
        <p><strong>Email:</strong> info@vitalcare.com</p>
        <p><strong>WhatsApp:</strong> (+230) 5432 1987</p>
      </div>
    </div>
  </div>
</div>
</main>
<?php include __DIR__ . '/includes/footer.php'; ?>
