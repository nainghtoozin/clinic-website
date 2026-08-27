<?php

namespace App\Http\Controllers;

use App\Models\Investigation;
use App\Models\LabTest;
use App\Models\Patient;
use App\Services\AuditService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class InvestigationController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('investigation.view');

        $query = Investigation::with(['patient', 'doctor', 'labTest', 'consultation']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('patient', fn ($pq) => $pq->where('name', 'like', "%{$search}%")
                    ->orWhere('patient_number', 'like', "%{$search}%"))
                  ->orWhereHas('labTest', fn ($tq) => $tq->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('doctor_id')) {
            $query->where('doctor_id', $request->doctor_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('requested_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('requested_date', '<=', $request->date_to);
        }

        $investigations = $query->latest()->paginate(15)->withQueryString();
        $doctors = \App\Models\Doctor::orderBy('name')->get();

        return view('investigations.index', compact('investigations', 'doctors'));
    }

    public function create(Request $request)
    {
        Gate::authorize('investigation.create');

        $patient = null;
        $consultation = null;

        if ($request->filled('patient_id')) {
            $patient = Patient::findOrFail($request->patient_id);
        }

        if ($request->filled('consultation_id')) {
            $consultation = \App\Models\Consultation::with(['patient', 'doctor'])
                ->findOrFail($request->consultation_id);
            $patient = $consultation->patient;
        }

        $patients = Patient::active()->orderBy('name')->get();
        $doctors = \App\Models\Doctor::orderBy('name')->get();
        $labTests = LabTest::active()->orderBy('name')->get();

        return view('investigations.create', compact('patient', 'consultation', 'patients', 'doctors', 'labTests'));
    }

    public function store(Request $request)
    {
        Gate::authorize('investigation.create');

        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'doctor_id' => 'required|exists:doctors,id',
            'consultation_id' => 'nullable|exists:consultations,id',
            'lab_test_id' => 'required|exists:lab_tests,id',
            'requested_date' => 'required|date|after_or_equal:today',
            'priority' => 'required|in:routine,stat,urgent',
            'clinical_notes' => 'nullable|string|max:2000',
        ]);

        $investigation = Investigation::create($validated);
        AuditService::logCreated($investigation, 'Investigation');

        NotificationService::notify(
            $validated['doctor_id'],
            'investigation',
            'Investigation Requested',
            "A new {$investigation->priority} priority investigation has been requested for patient.",
            $investigation,
            'investigation',
            'created',
            route('investigations.show', $investigation)
        );

        return redirect()->route('investigations.index')
            ->with('success', 'Investigation request created successfully.');
    }

    public function show(Investigation $investigation)
    {
        Gate::authorize('investigation.view');

        $investigation->load(['patient', 'doctor', 'labTest', 'consultation', 'invoice']);

        return view('investigations.show', compact('investigation'));
    }

    public function edit(Investigation $investigation)
    {
        Gate::authorize('investigation.edit');

        $investigation->load(['patient', 'doctor', 'labTest', 'consultation']);

        $patients = Patient::active()->orderBy('name')->get();
        $doctors = \App\Models\Doctor::orderBy('name')->get();
        $labTests = LabTest::active()->orderBy('name')->get();

        return view('investigations.edit', compact('investigation', 'patients', 'doctors', 'labTests'));
    }

    public function update(Request $request, Investigation $investigation)
    {
        Gate::authorize('investigation.edit');

        $validated = $request->validate([
            'clinical_notes' => 'nullable|string|max:2000',
        ]);

        $old = $investigation->toArray();
        $investigation->update($validated);
        AuditService::logUpdated($investigation, 'Investigation', $old, $validated);

        return redirect()->route('investigations.show', $investigation)
            ->with('success', 'Investigation updated successfully.');
    }

    public function updateStatus(Request $request, Investigation $investigation)
    {
        Gate::authorize('investigation.edit');

        $validated = $request->validate([
            'status' => 'required|in:requested,in_progress,completed,cancelled',
        ]);

        $newStatus = $validated['status'];

        if (!$investigation->canTransitionTo($newStatus)) {
            return back()->with('error', "Cannot transition from '{$investigation->status}' to '{$newStatus}'.");
        }

        $oldStatus = $investigation->status;

        $updateData = ['status' => $newStatus];

        if ($newStatus === 'completed') {
            $updateData['resulted_at'] = now();
        }

        $investigation->update($updateData);

        AuditService::logStatusChange($investigation, 'Investigation', $oldStatus, $newStatus);

        $statusLabels = [
            'in_progress' => 'In Progress',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
        ];

        NotificationService::notify(
            $investigation->doctor->user_id ?? auth()->id(),
            'investigation',
            'Investigation ' . ($statusLabels[$newStatus] ?? ucfirst($newStatus)),
            "Investigation status changed from {$oldStatus} to {$newStatus}.",
            $investigation,
            'investigation',
            $newStatus,
            route('investigations.show', $investigation)
        );

        return redirect()->route('investigations.show', $investigation)
            ->with('success', 'Investigation status updated successfully.');
    }

    public function enterResult(Request $request, Investigation $investigation)
    {
        Gate::authorize('investigation.edit');

        $validated = $request->validate([
            'result_value' => 'required|string|max:5000',
            'result_unit' => 'nullable|string|max:50',
            'result_reference_range' => 'nullable|string|max:255',
            'interpretation' => 'nullable|string|max:2000',
        ]);

        $investigation->update(array_merge($validated, [
            'result_status' => 'entered',
            'resulted_at' => now(),
        ]));

        NotificationService::notify(
            $investigation->doctor->user_id ?? auth()->id(),
            'investigation',
            'Lab Results Available',
            "Lab results for investigation have been entered and are ready for review.",
            $investigation,
            'investigation',
            'result_entered',
            route('investigations.show', $investigation)
        );

        return redirect()->route('investigations.show', $investigation)
            ->with('success', 'Test result entered successfully.');
    }

    public function destroy(Investigation $investigation)
    {
        Gate::authorize('investigation.delete');

        if (!$investigation->isRequested()) {
            return back()->with('error', 'Only requested investigations can be deleted.');
        }

        $investigation->delete();

        return redirect()->route('investigations.index')
            ->with('success', 'Investigation deleted successfully.');
    }
}
