<?php

namespace App\Http\Controllers;

use App\Models\Communication;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class CommunicationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        Gate::authorize('communication.view');

        $query = Communication::with(['patient', 'appointment', 'user']);

        if ($request->filled('patient_id')) {
            $query->where('patient_id', $request->patient_id);
        }

        if ($request->filled('appointment_id')) {
            $query->where('appointment_id', $request->appointment_id);
        }

        if ($request->filled('contact_method')) {
            $query->where('contact_method', $request->contact_method);
        }

        if ($request->filled('purpose')) {
            $query->where('purpose', $request->purpose);
        }

        if ($request->filled('outcome')) {
            $query->where('outcome', $request->outcome);
        }

        if ($request->filled('date_from')) {
            $query->where('contacted_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('contacted_at', '<=', $request->date_to . ' 23:59:59');
        }

        $communications = $query->latest('contacted_at')->paginate(15)->withQueryString();

        return view('communications.index', compact('communications'));
    }

    public function store(Request $request)
    {
        Gate::authorize('communication.create');

        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'appointment_id' => 'nullable|exists:appointments,id',
            'contact_method' => 'required|in:' . implode(',', array_keys(Communication::CONTACT_METHODS)),
            'purpose' => 'required|in:' . implode(',', array_keys(Communication::PURPOSES)),
            'outcome' => 'required|in:' . implode(',', array_keys(Communication::OUTCOMES)),
            'contacted_at' => 'required|date',
            'note' => 'nullable|string|max:2000',
            'follow_up_date' => 'nullable|date|after_or_equal:today',
            'follow_up_note' => 'nullable|string|max:1000',
        ]);

        $validated['user_id'] = auth()->id();

        Communication::create($validated);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Communication record created.']);
        }

        return back()->with('success', 'Communication record created.');
    }

    public function show(Communication $communication)
    {
        Gate::authorize('communication.view');

        $communication->load(['patient', 'appointment', 'user']);

        return view('communications.show', compact('communication'));
    }

    public function update(Request $request, Communication $communication)
    {
        Gate::authorize('communication.edit');

        $validated = $request->validate([
            'contact_method' => 'required|in:' . implode(',', array_keys(Communication::CONTACT_METHODS)),
            'purpose' => 'required|in:' . implode(',', array_keys(Communication::PURPOSES)),
            'outcome' => 'required|in:' . implode(',', array_keys(Communication::OUTCOMES)),
            'contacted_at' => 'required|date',
            'note' => 'nullable|string|max:2000',
            'follow_up_date' => 'nullable|date',
            'follow_up_note' => 'nullable|string|max:1000',
            'follow_up_completed' => 'nullable|boolean',
        ]);

        $communication->update($validated);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Communication updated.']);
        }

        return back()->with('success', 'Communication updated.');
    }

    public function destroy(Communication $communication)
    {
        Gate::authorize('communication.delete');

        $communication->delete();

        return back()->with('success', 'Communication record deleted.');
    }

    public function completeFollowUp(Request $request, Communication $communication)
    {
        Gate::authorize('communication.edit');

        $communication->update([
            'follow_up_completed' => true,
        ]);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Follow-up completed.']);
        }

        return back()->with('success', 'Follow-up completed.');
    }

    public function followUps(Request $request)
    {
        Gate::authorize('communication.view');

        $query = Communication::with(['patient', 'appointment', 'user'])
            ->whereNotNull('follow_up_date');

        $filter = $request->get('filter', 'upcoming');
        match ($filter) {
            'today' => $query->dueTodayFollowUps(),
            'overdue' => $query->overdueFollowUps(),
            'upcoming' => $query->upcomingFollowUps(),
            'completed' => $query->where('follow_up_completed', true),
            default => $query->pendingFollowUps(),
        };

        if ($request->filled('patient_id')) {
            $query->where('patient_id', $request->patient_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('patient', fn ($q) => $q->where('name', 'like', "%{$search}%")
                ->orWhere('patient_number', 'like', "%{$search}%"));
        }

        $followUps = $query->latest('follow_up_date')->paginate(15)->withQueryString();

        $counts = [
            'overdue' => Communication::overdueFollowUps()->count(),
            'today' => Communication::dueTodayFollowUps()->count(),
            'upcoming' => Communication::upcomingFollowUps()->count(),
            'completed' => Communication::where('follow_up_completed', true)->count(),
        ];

        return view('communications.follow-ups', compact('followUps', 'filter', 'counts'));
    }

    public function patientCommunications(Patient $patient)
    {
        Gate::authorize('communication.view');

        $communications = Communication::with(['appointment', 'user'])
            ->where('patient_id', $patient->id)
            ->latest('contacted_at')
            ->paginate(15);

        return view('communications.patient-history', compact('patient', 'communications'));
    }
}
