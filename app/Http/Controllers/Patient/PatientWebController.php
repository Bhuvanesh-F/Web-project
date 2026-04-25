<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

/** PatientWebController */
class PatientWebController extends Controller
{
    public function dashboard(): View      { return view('patient.dashboard'); }
    public function appointments(): View   { return view('patient.appointments'); }
    public function medicalRecords(): View { return view('patient.medical-records'); }
    public function profile(): View        { return view('patient.profile'); }
    public function bookAppointment(): View { return view('patient.book-appointment'); }
}
