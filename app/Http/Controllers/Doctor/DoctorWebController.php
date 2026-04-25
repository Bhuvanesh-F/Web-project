<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

/** DoctorWebController — routes to doctor Blade views */
class DoctorWebController extends Controller
{
    public function dashboard(): View    { return view('doctor.dashboard'); }
    public function patients(): View     { return view('doctor.patients'); }
    public function schedule(): View     { return view('doctor.schedule'); }
    public function profile(): View      { return view('doctor.profile'); }
    public function medicalRecords(): View { return view('doctor.medical-records'); }
    public function vetDashboard(): View { return view('doctor.vet-dashboard'); }
    public function vetPatients(): View  { return view('doctor.vet-patients'); }
    public function vetSchedule(): View  { return view('doctor.vet-schedule'); }
}
