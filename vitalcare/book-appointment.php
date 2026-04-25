<?php
// book-appointment.php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';

// Fetch active doctors for dropdown
$doctors = [];
$res = $conn->query('SELECT doctor_id, full_name, speciality FROM human_doctors ORDER BY speciality, full_name');
while ($row = $res->fetch_assoc()) $doctors[] = $row;

$pageTitle = 'Book Appointment';
$navActive  = 'book';
include __DIR__ . '/includes/header.php';
?>

<main>
  <div class="container page-content">
    <h1>VitalCare</h1>

    <div id="flash-msg"></div>

    <!-- Step 1 – choose type -->
    <div class="appointment-selection" id="typeSelection">
      <h2>Who is this appointment for?</h2>
      <p>Select whether you are booking for yourself or your pet.</p>
      <div class="selection-options">
        <div class="option-card" onclick="selectAppointmentType('human')">
          <div class="option-icon">🧑‍⚕️</div>
          <h3>Human Patient</h3>
          <p>Book an appointment for yourself</p>
        </div>
        <div class="option-card" onclick="selectAppointmentType('pet')">
          <div class="option-icon">🐾</div>
          <h3>Pet</h3>
          <p>Book an appointment for your pet</p>
        </div>
      </div>
    </div>

    <!-- Step 2 – fill form -->
    <div class="appointment-form-section" style="display:none;">
      <form id="bookingForm" novalidate>
        <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
        <input type="hidden" id="appointment_type" name="appointment_type" value="human">

        <div class="appointment-form card">
          <h2>Schedule Your Appointment</h2>

          <div class="form-row">
            <div class="form-group">
              <label for="full_name">Full Name *</label>
              <input type="text" id="full_name" name="full_name"
                     value="<?= h($_SESSION['patient_name'] ?? '') ?>"
                     placeholder="Your full name" required>
              <span class="form-error"></span>
            </div>
            <div class="form-group">
              <label for="contact_number">Contact Number *</label>
              <input type="tel" id="contact_number" name="contact_number"
                     placeholder="e.g. 5123 4567" required>
              <span class="form-error"></span>
            </div>
          </div>

          <div class="form-group">
            <label for="doctor_speciality">Doctor Speciality *</label>
            <select id="doctor_speciality" name="doctor_speciality" required>
              <option value="">— Select speciality —</option>
              <option value="General Medicine">General Medicine</option>
              <option value="Cardiology">Cardiology</option>
              <option value="Dermatology">Dermatology</option>
              <option value="Pediatrics">Pediatrics</option>
              <option value="Veterinary">Veterinary</option>
            </select>
            <span class="form-error"></span>
          </div>

          <!-- Doctor dropdown (loaded by AJAX based on speciality) -->
          <div class="form-group" id="doctorGroup" style="display:none;">
            <label for="doctor_id">Preferred Doctor</label>
            <select id="doctor_id" name="doctor_id">
              <option value="">— Any available —</option>
            </select>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label for="preferred_date">Preferred Date *</label>
              <input type="date" id="preferred_date" name="preferred_date"
                     min="<?= date('Y-m-d', strtotime('+1 day')) ?>" required>
              <span class="form-error"></span>
            </div>
            <div class="form-group">
              <label for="preferred_time">Preferred Time Slot *</label>
              <select id="preferred_time" name="preferred_time" required>
                <option value="">— Select slot —</option>
                <option value="09:00">9:00 AM</option>
                <option value="10:00">10:00 AM</option>
                <option value="11:00">11:00 AM</option>
                <option value="14:00">2:00 PM</option>
                <option value="15:00">3:00 PM</option>
                <option value="16:00">4:00 PM</option>
              </select>
              <span class="form-error"></span>
            </div>
          </div>

          <div class="form-group">
            <label for="symptoms">Symptoms / Reason for Visit</label>
            <textarea id="symptoms" name="symptoms" rows="4"
                      placeholder="Briefly describe your symptoms or reason for visiting"></textarea>
          </div>

          <div class="form-actions">
            <button type="button" class="btn btn-secondary" onclick="goBackToSelection()">← Back</button>
            <button type="button" class="btn btn-submit" onclick="submitAppointmentAjax()">
              Book Appointment
            </button>
          </div>
        </div>
      </form>
    </div>

  </div>
</main>

<script>
// Load doctors matching chosen speciality via AJAX
document.getElementById('doctor_speciality').addEventListener('change', function() {
    const spec = this.value;
    const group = document.getElementById('doctorGroup');
    const sel   = document.getElementById('doctor_id');

    if (!spec) { group.style.display = 'none'; return; }

    fetch('/vitalcare/api/get-doctors.php?speciality=' + encodeURIComponent(spec))
        .then(r => r.json())
        .then(data => {
            sel.innerHTML = '<option value="">— Any available —</option>';
            data.forEach(d => {
                const opt = document.createElement('option');
                opt.value = d.doctor_id;
                opt.textContent = d.full_name;
                sel.appendChild(opt);
            });
            group.style.display = data.length ? 'block' : 'none';
        })
        .catch(() => { group.style.display = 'none'; });
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
