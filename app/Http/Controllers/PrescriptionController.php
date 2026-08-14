<?php

namespace App\Http\Controllers;

use App\Models\Consultation;
use App\Models\Medicine;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class PrescriptionController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('prescription.view');

        $query = Prescription::with(['patient', 'doctor', 'consultation', 'items.medicine']);

        if ($request->filled('patient_id')) {
            $query->where('patient_id', $request->patient_id);
        }

        if ($request->filled('doctor_id')) {
            $query->where('doctor_id', $request->doctor_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('prescription_number', 'like', "%{$search}%")
                  ->orWhereHas('patient', function ($q2) use ($search) {
                      $q2->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $prescriptions = $query->latest()->paginate(15)->withQueryString();

        $patients = \App\Models\Patient::orderBy('name')->get();
        $doctors = \App\Models\Doctor::orderBy('name')->get();

        return view('prescriptions.index', compact('prescriptions', 'patients', 'doctors'));
    }

    public function create(Request $request)
    {
        Gate::authorize('prescription.create');

        if (!$request->filled('consultation_id')) {
            return redirect()->route('prescriptions.index')
                ->with('info', 'Prescriptions are created from a patient\'s consultation. Open a consultation and choose Add Prescription.');
        }

        $consultation = Consultation::with(['patient', 'doctor'])
            ->findOrFail($request->consultation_id);
        $patient = $consultation->patient;
        $doctor = $consultation->doctor;

        $patients = Patient::where('status', 'active')->orderBy('name')->get();
        $medicines = Medicine::active()->orderBy('name')->get();

        return view('prescriptions.create', compact('consultation', 'patient', 'doctor', 'patients', 'medicines'));
    }

    public function store(Request $request)
    {
        Gate::authorize('prescription.create');

        $validated = $request->validate([
            'patient_id'        => 'required|exists:patients,id',
            'doctor_id'         => 'required|exists:doctors,id',
            'consultation_id'   => 'nullable|exists:consultations,id',
            'notes'             => 'nullable|string|max:2000',
            'prescribed_date'   => 'required|date',
            'items'             => 'required|array|min:1',
            'items.*.medicine_id' => 'required|exists:medicines,id',
            'items.*.dosage'    => 'required|string|max:255',
            'items.*.frequency' => 'required|string|max:255',
            'items.*.duration'  => 'nullable|string|max:255',
            'items.*.instructions' => 'nullable|string|max:500',
            'items.*.quantity'  => 'required|integer|min:1',
        ]);

        if (!empty($validated['consultation_id'])) {
            $consultation = Consultation::find($validated['consultation_id']);

            if (!$consultation) {
                return back()->withErrors(['consultation_id' => 'The selected consultation was not found.'])->withInput();
            }

            if ((int) $consultation->patient_id !== (int) $validated['patient_id']) {
                return back()->withErrors([
                    'patient_id' => 'The patient must match the patient on the selected consultation.',
                ])->withInput();
            }

            if ((int) $consultation->doctor_id !== (int) $validated['doctor_id']) {
                return back()->withErrors([
                    'doctor_id' => 'The doctor must match the doctor on the selected consultation.',
                ])->withInput();
            }

            $validated['patient_id'] = $consultation->patient_id;
            $validated['doctor_id'] = $consultation->doctor_id;
        }

        $prescription = DB::transaction(function () use ($validated) {
            $prescription = Prescription::create([
                'patient_id'      => $validated['patient_id'],
                'doctor_id'       => $validated['doctor_id'],
                'consultation_id' => $validated['consultation_id'] ?? null,
                'notes'           => $validated['notes'] ?? null,
                'prescribed_date' => $validated['prescribed_date'],
            ]);

            foreach ($validated['items'] as $item) {
                PrescriptionItem::create([
                    'prescription_id' => $prescription->id,
                    'medicine_id'     => $item['medicine_id'],
                    'dosage'          => $item['dosage'],
                    'frequency'       => $item['frequency'],
                    'duration'        => $item['duration'] ?? null,
                    'instructions'    => $item['instructions'] ?? null,
                    'quantity'        => $item['quantity'],
                ]);
            }

            return $prescription;
        });

        return redirect()->route('prescriptions.show', $prescription)
            ->with('success', 'Prescription created successfully.');
    }

    public function show(Prescription $prescription)
    {
        Gate::authorize('prescription.view');

        $prescription->load(['patient', 'doctor', 'consultation', 'items.medicine']);

        return view('prescriptions.show', compact('prescription'));
    }

    public function edit(Prescription $prescription)
    {
        Gate::authorize('prescription.edit');

        $prescription->load(['patient', 'doctor', 'consultation', 'items.medicine']);
        $medicines = Medicine::active()->orderBy('name')->get();

        return view('prescriptions.edit', compact('prescription', 'medicines'));
    }

    public function update(Request $request, Prescription $prescription)
    {
        Gate::authorize('prescription.edit');

        $validated = $request->validate([
            'patient_id'        => 'required|exists:patients,id',
            'doctor_id'         => 'required|exists:doctors,id',
            'consultation_id'   => 'nullable|exists:consultations,id',
            'notes'             => 'nullable|string|max:2000',
            'prescribed_date'   => 'required|date',
            'items'             => 'required|array|min:1',
            'items.*.medicine_id' => 'required|exists:medicines,id',
            'items.*.dosage'    => 'required|string|max:255',
            'items.*.frequency' => 'required|string|max:255',
            'items.*.duration'  => 'nullable|string|max:255',
            'items.*.instructions' => 'nullable|string|max:500',
            'items.*.quantity'  => 'required|integer|min:1',
        ]);

        DB::transaction(function () use ($prescription, $validated) {
            $prescription->update([
                'patient_id'      => $validated['patient_id'],
                'doctor_id'       => $validated['doctor_id'],
                'consultation_id' => $validated['consultation_id'] ?? null,
                'notes'           => $validated['notes'] ?? null,
                'prescribed_date' => $validated['prescribed_date'],
            ]);

            $prescription->items()->delete();

            foreach ($validated['items'] as $item) {
                PrescriptionItem::create([
                    'prescription_id' => $prescription->id,
                    'medicine_id'     => $item['medicine_id'],
                    'dosage'          => $item['dosage'],
                    'frequency'       => $item['frequency'],
                    'duration'        => $item['duration'] ?? null,
                    'instructions'    => $item['instructions'] ?? null,
                    'quantity'        => $item['quantity'],
                ]);
            }
        });

        return redirect()->route('prescriptions.show', $prescription)
            ->with('success', 'Prescription updated successfully.');
    }

    public function destroy(Prescription $prescription)
    {
        Gate::authorize('prescription.delete');

        $prescription->delete();

        return redirect()->route('prescriptions.index')
            ->with('success', 'Prescription deleted successfully.');
    }
}