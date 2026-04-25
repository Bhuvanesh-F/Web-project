<?php

namespace App\Http\Controllers\Receptionist;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

/** ReceptionistWebController */
class ReceptionistWebController extends Controller
{
    public function dashboard(): View          { return view('receptionist.dashboard'); }
    public function appointments(): View       { return view('receptionist.appointments'); }
    public function scheduleAppointment(): View { return view('receptionist.schedule-appointment'); }
}
