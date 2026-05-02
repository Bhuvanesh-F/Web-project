<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use Illuminate\Http\Request;

class DoctorController extends Controller
{
    public function index()
    {
        return Doctor::all();
    }

    public function store(Request $request)
{
    $request->validate([
        'name' => 'required',
        'email' => 'required|email',
        'specialization' => 'required'
    ]);

    $doctor = Doctor::create([
        'name' => $request->name,
        'email' => $request->email,
        'specialization' => $request->specialization
    ]);

    return response()->json($doctor, 201);
}

    public function show($id)
    {
        return Doctor::findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $doctor = Doctor::findOrFail($id);

        $doctor->update([
            'name' => $request->name,
            'email' => $request->email,
            'specialization' => $request->specialization
        ]);

        return response()->json($doctor);
    }

    public function destroy($id)
    {
        Doctor::destroy($id);

        return response()->json(['message' => 'Doctor deleted']);
    }
}