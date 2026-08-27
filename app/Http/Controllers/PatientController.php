<?php

namespace App\Http\Controllers;

use App\Models\Consultation;
use App\Models\Patient;
use App\Models\QueueTicket;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Services\AuditService;
use Illuminate\Support\Facades\Gate;

class PatientController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('patient.view');

        $query = Patient::withCount('appointments');

        // Search by patient number, name, phone, or email
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('patient_number', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by gender
        if ($request->filled('gender')) {
            $query->where('gender', $request->gender);
        }

        $patients = $query->latest()->paginate(15)->withQueryString();

        return view('patients.index', compact('patients'));
    }

    public function create()
    {
        Gate::authorize('patient.create');

        return view('patients.create');
    }

    public function store(Request $request)
    {
        Gate::authorize('patient.create');

        $validated = $request->validate([
            'name'                  => 'required|string|max:255',
            'email'                 => 'nullable|email|max:255',
            'phone'                 => 'nullable|string|max:20',
            'date_of_birth'         => 'nullable|date|before:today',
            'gender'                => 'nullable|in:male,female,other',
            'address'               => 'nullable|string|max:500',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:20',
            'blood_group'           => 'nullable|in:A+,A-,B+,B-,AB+,AB-,O+,O-',
            'allergies'             => 'nullable|string|max:1000',
            'medical_history'       => 'nullable|string|max:2000',
            'status'                => 'nullable|in:active,inactive,archived',
        ]);

        $validated['patient_number'] = Patient::generatePatientNumber();

        $patient = Patient::create($validated);
        AuditService::logCreated($patient, 'Patient');

        return redirect()->route('patients.index')
            ->with('success', 'Patient registered successfully');
    }

    public function show(Patient $patient)
    {
        Gate::authorize('patient.view');

        $patient->load(['appointments' => function ($query) {
            $query->with(['doctor', 'department'])
                  ->latest('date')
                  ->latest('created_at');
        }]);

        $patient->load(['prescriptions' => function ($query) {
            $query->with(['doctor', 'items'])
                  ->latest('prescribed_date');
        }]);

        $patient->load(['invoices' => function ($query) {
            $query->latest();
        }]);

        $activeQueueTicket = QueueTicket::with(['doctor'])
            ->where('patient_id', $patient->id)
            ->whereDate('queue_date', now()->toDateString())
            ->whereIn('status', ['waiting', 'called', 'in_consultation'])
            ->first();

        $consultations = Consultation::with(['doctor', 'appointment'])
            ->where('patient_id', $patient->id)
            ->latest()
            ->paginate(10);

        return view('patients.show', compact('patient', 'activeQueueTicket', 'consultations'));
    }

    public function edit(Patient $patient)
    {
        Gate::authorize('patient.edit');

        return view('patients.edit', compact('patient'));
    }

    public function update(Request $request, Patient $patient)
    {
        Gate::authorize('patient.edit');

        $validated = $request->validate([
            'name'                  => 'required|string|max:255',
            'email'                 => 'nullable|email|max:255',
            'phone'                 => 'nullable|string|max:20',
            'date_of_birth'         => 'nullable|date|before:today',
            'gender'                => 'nullable|in:male,female,other',
            'address'               => 'nullable|string|max:500',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:20',
            'blood_group'           => 'nullable|in:A+,A-,B+,B-,AB+,AB-,O+,O-',
            'allergies'             => 'nullable|string|max:1000',
            'medical_history'       => 'nullable|string|max:2000',
            'status'                => 'required|in:active,inactive,archived',
        ]);

        $old = $patient->toArray();
        $patient->update($validated);
        AuditService::logUpdated($patient, 'Patient', $old, $validated);

        return redirect()->route('patients.index')
            ->with('success', 'Patient updated successfully');
    }

    public function destroy(Patient $patient)
    {
        Gate::authorize('patient.delete');

        AuditService::logDeleted($patient, 'Patient');
        $patient->delete();

        return redirect()->route('patients.index')
            ->with('success', 'Patient archived successfully');
    }

    public function restore($id)
    {
        Gate::authorize('patient.delete');

        $patient = Patient::withTrashed()->findOrFail($id);
        $patient->restore();
        AuditService::logRestored($patient, 'Patient');

        return redirect()->route('patients.index')
            ->with('success', 'Patient restored successfully');
    }

    public function appointmentsJson(Patient $patient)
    {
        Gate::authorize('patient.view');

        $appointments = $patient->appointments()
            ->select('id', 'appointment_number', 'date', 'time')
            ->latest('date')
            ->limit(20)
            ->get();

        return response()->json($appointments);
    }
}
