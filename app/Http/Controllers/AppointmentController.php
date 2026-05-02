<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use Illuminate\Http\Request;
use App\Http\Resources\AppointmentResource;

class AppointmentController extends Controller
{
    public function index()
    {
        return AppointmentResource::collection(
        Appointment::with(['patient', 'doctor'])->get()
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'doctor_id' => 'required|exists:doctors,id',
            'appointment_date' => 'required|date',
            'status' => 'nullable|string'
        ]);

        $appointment = Appointment::create([
            'patient_id' => $request->patient_id,
            'doctor_id' => $request->doctor_id,
            'appointment_date' => $request->appointment_date,
            'status' => $request->status ?? 'pending',
        ]);

        return response()->json($appointment, 201);
    }
}