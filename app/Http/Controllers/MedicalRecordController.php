<?php

namespace App\Http\Controllers;

use App\Models\Communication;
use App\Models\Consultation;
use App\Models\Investigation;
use App\Models\Patient;
use App\Models\VitalSign;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class MedicalRecordController extends Controller
{
    public function show(Request $request, Patient $patient)
    {
        Gate::authorize('patient.view');

        $patient->load([
            'appointments' => function ($query) {
                $query->with(['doctor', 'department'])->latest('date')->latest('created_at');
            },
            'prescriptions' => function ($query) {
                $query->with(['doctor', 'items.medicine'])->latest('prescribed_date');
            },
            'invoices' => function ($query) {
                $query->with(['payments'])->latest();
            },
        ]);

        $consultationsQuery = Consultation::with(['doctor', 'appointment.department', 'vitalSign', 'prescriptions.items.medicine', 'invoice'])
            ->where('patient_id', $patient->id)
            ->latest();

        $this->applyFilters($consultationsQuery, $request);

        $consultations = $consultationsQuery->paginate(15)->withQueryString();

        $vitalSigns = VitalSign::with('consultation.doctor')
            ->whereHas('consultation', fn ($q) => $q->where('patient_id', $patient->id))
            ->latest('recorded_at')
            ->get();

        $investigations = Investigation::with(['doctor', 'labTest', 'consultation'])
            ->where('patient_id', $patient->id)
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $activeQueueTicket = \App\Models\QueueTicket::with(['doctor'])
            ->where('patient_id', $patient->id)
            ->whereDate('queue_date', now()->toDateString())
            ->whereIn('status', ['waiting', 'called', 'in_consultation'])
            ->first();

        $doctors = \App\Models\Doctor::orderBy('name')->get();

        $patientComms = Communication::with(['appointment', 'user'])
            ->where('patient_id', $patient->id)
            ->latest('contacted_at')
            ->limit(10)
            ->get();

        return view('patients.medical-record', compact(
            'patient',
            'consultations',
            'vitalSigns',
            'investigations',
            'activeQueueTicket',
            'doctors',
            'patientComms'
        ));
    }

    private function applyFilters($query, Request $request)
    {
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('doctor_id')) {
            $query->where('doctor_id', $request->doctor_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('record_type')) {
            switch ($request->record_type) {
                case 'consultation':
                    $query->whereNotNull('diagnosis');
                    break;
                case 'prescription':
                    $query->whereHas('prescriptions');
                    break;
                case 'vitals':
                    $query->whereHas('vitalSign');
                    break;
                case 'invoice':
                    $query->whereHas('invoice');
                    break;
                case 'followup':
                    $query->whereNotNull('follow_up_date');
                    break;
            }
        }
    }
}