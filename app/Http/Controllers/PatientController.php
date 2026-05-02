<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Patient;
use App\Http\Resources\PatientResource;

class PatientController extends Controller
{
    public function index()
    {
        return PatientResource::collection(Patient::all());
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email'
        ]);

        $patient = Patient::create([
            'name' => $request->name,
            'email' => $request->email
        ]);

        return response()->json($patient, 201);
    }

    public function show(string $id)
    {
        return Patient::findOrFail($id);
    }

    public function update(Request $request, string $id)
    {
        $patient = Patient::findOrFail($id);

        $patient->update([
            'name' => $request->name,
            'email' => $request->email
        ]);

        return response()->json($patient);
    }

    public function destroy(string $id)
    {
        Patient::destroy($id);

        return response()->json([
            'message' => 'Patient deleted successfully'
        ]);
    }
}