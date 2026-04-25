<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

/**
 * PublicController
 *
 * Serves all public-facing pages that require no authentication.
 * These map to the Blade templates owned by Catalina (M5),
 * but the controller routing is set up by Ayman.
 */
class PublicController extends Controller
{
    public function homepage(): View
    {
        return view('public.homepage');
    }

    public function services(): View
    {
        return view('public.services');
    }

    public function contact(): View
    {
        return view('public.contact');
    }

    public function team(): View
    {
        return view('public.team');
    }

    public function reviews(): View
    {
        return view('public.reviews');
    }

    public function bookAppointment(): View
    {
        return view('public.book-appointment');
    }
}
