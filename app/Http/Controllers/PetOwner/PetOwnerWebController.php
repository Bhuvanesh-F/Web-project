<?php

namespace App\Http\Controllers\PetOwner;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

/** PetOwnerWebController */
class PetOwnerWebController extends Controller
{
    public function dashboard(): View    { return view('pet-owner.dashboard'); }
    public function myPets(): View       { return view('pet-owner.my-pets'); }
    public function appointments(): View { return view('pet-owner.appointments'); }
    public function profile(): View      { return view('pet-owner.profile'); }
}
