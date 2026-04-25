<?php

namespace App\Http\Controllers\Nurse;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

/** NurseWebController */
class NurseWebController extends Controller
{
    public function dashboard(): View { return view('nurse.dashboard'); }
    public function checklist(): View { return view('nurse.checklist'); }
    public function patients(): View  { return view('nurse.patients'); }
}
