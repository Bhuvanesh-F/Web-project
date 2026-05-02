<?php

namespace App\Http\Controllers;

use App\Models\MedicalRecord;
use Illuminate\Http\Request;

class MedicalRecordController extends Controller
{
    public function index()
    {
        return MedicalRecord::all();
    }

    public function store(Request $request)
{
    $request->validate([
        'patient_id' => 'required|exists:patients,id',
        'diagnosis' => 'required',
        'treatment' => 'required'
    ]);

    $record = MedicalRecord::create([
        'patient_id' => $request->patient_id,
        'diagnosis' => $request->diagnosis,
        'treatment' => $request->treatment
    ]);

    return response()->json($record, 201);
}

    public function show($id)
    {
        return MedicalRecord::findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $record = MedicalRecord::findOrFail($id);

        $record->update([
            'patient_id' => $request->patient_id,
            'diagnosis' => $request->diagnosis,
            'treatment' => $request->treatment
        ]);

        return response()->json($record);
    }

    public function destroy($id)
    {
        MedicalRecord::destroy($id);

        return response()->json(['message' => 'Record deleted']);
    }
}