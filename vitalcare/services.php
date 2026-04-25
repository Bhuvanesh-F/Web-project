<?php
$pageTitle = 'Our Services';
$navActive  = 'services';
include __DIR__ . '/includes/header.php';
?>
<main>
<div class="container page-content">
  <h1>VitalCare</h1>
  <h2>Our Services</h2>

  <div class="services-overview" style="margin-top:32px;">

    <div class="service-card">
      <div class="option-icon">🩺</div>
      <h3>General Medicine</h3>
      <p>Comprehensive primary healthcare for all ages — routine check-ups, diagnosis, and treatment of common illnesses.</p>
    </div>

    <div class="service-card">
      <div class="option-icon">❤️</div>
      <h3>Cardiology</h3>
      <p>Specialist heart care including ECG, echocardiography, and management of cardiovascular conditions.</p>
    </div>

    <div class="service-card">
      <div class="option-icon">🧴</div>
      <h3>Dermatology</h3>
      <p>Diagnosis and treatment of skin, hair, and nail conditions, including cosmetic procedures.</p>
    </div>

    <div class="service-card">
      <div class="option-icon">👶</div>
      <h3>Pediatrics</h3>
      <p>Dedicated healthcare for infants, children, and adolescents — from vaccinations to developmental checks.</p>
    </div>

    <div class="service-card">
      <div class="option-icon">🦴</div>
      <h3>Orthopedics</h3>
      <p>Treatment of bone, joint, and muscle disorders including sports injuries and rehabilitation.</p>
    </div>

    <div class="service-card">
      <div class="option-icon">🐾</div>
      <h3>Veterinary Care</h3>
      <p>Full-service animal healthcare including vaccinations, surgery, and emergency treatment for pets.</p>
    </div>

    <div class="service-card">
      <div class="option-icon">🚑</div>
      <h3>24/7 Emergency</h3>
      <p>Round-the-clock emergency medical services for human and animal patients.</p>
      <p class="emergency-contact">Ambulance – 118</p>
    </div>

    <div class="service-card">
      <div class="option-icon">🧪</div>
      <h3>Laboratory &amp; Diagnostics</h3>
      <p>In-house blood tests, X-ray, ultrasound, and other diagnostic investigations with fast turnaround.</p>
    </div>

  </div>

  <div style="text-align:center;margin-top:40px;">
    <a href="/vitalcare/book-appointment.php" class="btn">Book an Appointment</a>
  </div>
</div>
</main>
<?php include __DIR__ . '/includes/footer.php'; ?>
