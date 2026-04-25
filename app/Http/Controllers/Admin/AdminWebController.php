<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

/**
 * AdminWebController
 * Routes to admin Blade views (views owned by Sehun, M6).
 */
class AdminWebController extends Controller
{
    public function dashboard(): View  { return view('admin.dashboard'); }
    public function staff(): View      { return view('admin.staff'); }
    public function appointments(): View { return view('admin.appointments'); }
    public function addDoctor(): View  { return view('admin.add-doctor'); }
    public function addNurse(): View   { return view('admin.add-nurse'); }
    public function auditLogs(): View  { return view('admin.audit-logs'); }
}
