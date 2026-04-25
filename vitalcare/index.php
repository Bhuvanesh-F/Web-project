<?php
// index.php – VitalCare Homepage
$pageTitle = 'Home';
$navActive  = 'home';
include __DIR__ . '/includes/header.php';
?>

<main>
  <div class="container page-content">

    <!-- Hero -->
    <section class="hero-section">
      <h2>Welcome to VitalCare</h2>
      <p class="tagline">Your Health. Their Health. One Clinic.</p>
      <p>A modern clinic offering comprehensive healthcare services to both humans and animals.</p>
      <div class="cta-buttons" style="margin-top:28px;">
        <a href="/vitalcare/book-appointment.php" class="btn">Request an Appointment</a>
        <a href="/vitalcare/services.php"         class="btn btn-secondary">Our Services</a>
      </div>
    </section>

    <!-- Service cards -->
    <div class="services-overview">
      <div class="service-card">
        <div class="option-icon">🏥</div>
        <h3>Human Healthcare</h3>
        <p>Complete medical services for all ages with experienced physicians and modern equipment.</p>
        <a href="/vitalcare/login.php" class="btn" style="margin-top:14px;">Patient Portal</a>
      </div>
      <div class="service-card">
        <div class="option-icon">🐾</div>
        <h3>Veterinary Care</h3>
        <p>Professional animal healthcare from routine checkups to emergency treatments.</p>
        <a href="/vitalcare/login.php" class="btn" style="margin-top:14px;">Pet Owner Portal</a>
      </div>
      <div class="service-card">
        <div class="option-icon">🚑</div>
        <h3>24/7 Emergency</h3>
        <p>Round-the-clock emergency services for both human and animal patients.</p>
        <p class="emergency-contact">Ambulance – 118</p>
      </div>
    </div>

    <!-- Testimonials -->
    <section class="testimonials">
      <h2>What Our Clients Say</h2>
      <div class="testimonial-grid">
        <div class="testimonial-card">
          <p>"The human clinic provided excellent care for my family. The doctors are knowledgeable and the staff is very helpful."</p>
          <p class="client-name">– N****a</p>
        </div>
        <div class="testimonial-card">
          <p>"The pet clinic saved my dog's life. Incredible team – fast, professional and caring."</p>
          <p class="client-name">– S*****sh</p>
        </div>
        <div class="testimonial-card">
          <p>"The online booking system is so convenient. I can schedule appointments for my family and our pets in one place."</p>
          <p class="client-name">– J****e</p>
        </div>
      </div>
    </section>

  </div>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
