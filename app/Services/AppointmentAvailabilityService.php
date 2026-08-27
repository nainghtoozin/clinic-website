<?php

namespace App\Services;

use App\Enums\AppointmentStatus;
use App\Enums\DayOfWeek;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Services\ClinicSettingsService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Single source of truth for doctor availability.
 *
 * Both the public availability-first appointment form and the admin/reception
 * appointment form must use exactly this logic so they never disagree.
 *
 * Schedule model (doctors table): a doctor has one daily window described by
 * `available_days` (ISO 1=Mon..7=Sun), `start_time` and `end_time`. Overnight
 * schedules (end_time <= start_time) are NOT supported and are treated as an
 * invalid / unconfigured schedule.
 *
 * Break support: optional `break_start` and `break_end` columns define a daily
 * lunch/break window. Slots overlapping the break are excluded.
 *
 * Unavailability: the `doctor_unavailable_dates` table stores specific dates
 * when a doctor is not available (leave, holiday, training, emergency, etc.).
 */
class AppointmentAvailabilityService
{
    /**
     * Default appointment duration in minutes used to generate public slots.
     *
     * No global duration setting currently exists in the application, so the
     * standard slot length matches the 30 minute default already used by the
     * admin appointment form.
     */
    public const DEFAULT_DURATION_MINUTES = 30;

    /**
     * ISO day values (1=Monday .. 7=Sunday) the doctor works, sorted+deduplicated.
     */
    public function workingDays(Doctor $doctor): array
    {
        $days = $doctor->available_days ?? [];

        return collect($days)
            ->map(fn ($day) => (int) $day)
            ->filter(fn ($day) => in_array($day, range(1, 7), true))
            ->unique()
            ->sort()
            ->values()
            ->all();
    }

    public function workingDayLabels(Doctor $doctor): array
    {
        return collect($this->workingDays($doctor))
            ->map(fn ($day) => DayOfWeek::from($day)->label())
            ->all();
    }

    public function isWorkingDay(Doctor $doctor, Carbon|string $date): bool
    {
        $date = $date instanceof Carbon ? $date : Carbon::parse($date);

        return in_array((int) $date->dayOfWeekIso, $this->workingDays($doctor), true);
    }

    /**
     * Normalized working hours as ['start' => 'H:i', 'end' => 'H:i'].
     *
     * Returns null when the schedule is missing or invalid (end_time not after
     * start_time) so the UI never displays a nonsensical range such as
     * "17:39 - 11:14". The doctor is simply treated as having no availability.
     */
    public function workingHours(Doctor $doctor): ?array
    {
        $start = $this->toMinutes($doctor->start_time);
        $end = $this->toMinutes($doctor->end_time);

        if ($start === null || $end === null || $end <= $start) {
            return null;
        }

        return [
            'start' => $this->minutesToTime($start),
            'end' => $this->minutesToTime($end),
        ];
    }

    /**
     * Normalized break hours as ['start' => 'H:i', 'end' => 'H:i'] or null.
     *
     * Returns null if no break is configured or if the break is invalid.
     */
    public function breakHours(Doctor $doctor): ?array
    {
        $start = $this->toMinutes($doctor->break_start);
        $end = $this->toMinutes($doctor->break_end);

        if ($start === null || $end === null || $end <= $start) {
            return null;
        }

        return [
            'start' => $this->minutesToTime($start),
            'end' => $this->minutesToTime($end),
        ];
    }

    /**
     * Check if a time slot overlaps with the doctor's break time.
     */
    public function defaultDuration(): int
    {
        return ClinicSettingsService::getInt('appointment.default_duration', 30);
    }

    public function isDuringBreak(Doctor $doctor, string $time, int $duration = 0): bool
    {
        $duration = $duration ?: $this->defaultDuration();

        $break = $this->breakHours($doctor);

        if ($break === null) {
            return false;
        }

        $timeMinutes = $this->toMinutes($time);
        $breakStart = $this->toMinutes($break['start']);
        $breakEnd = $this->toMinutes($break['end']);

        return $timeMinutes !== null
            && $timeMinutes < $breakEnd
            && ($timeMinutes + $duration) > $breakStart;
    }

    /**
     * Check if a date is unavailable for the doctor.
     */
    public function isUnavailableDate(Doctor $doctor, Carbon|string $date): bool
    {
        $date = $date instanceof Carbon ? $date->toDateString() : Carbon::parse($date)->toDateString();

        return \App\Models\DoctorUnavailableDate::where('doctor_id', $doctor->id)
            ->whereDate('date', $date)
            ->exists();
    }

    /**
     * Get the unavailable date record if it exists.
     */
    public function getUnavailableDate(Doctor $doctor, Carbon|string $date): ?\App\Models\DoctorUnavailableDate
    {
        $date = $date instanceof Carbon ? $date->toDateString() : Carbon::parse($date)->toDateString();

        return \App\Models\DoctorUnavailableDate::where('doctor_id', $doctor->id)
            ->whereDate('date', $date)
            ->first();
    }

    /**
     * True when a booking starting at $time of length $duration minutes finishes
     * within the doctor's working hours.
     */
    public function isWithinWorkingHours(Doctor $doctor, string $time, int $duration = 0): bool
    {
        $duration = $duration ?: $this->defaultDuration();

        $hours = $this->workingHours($doctor);

        if ($hours === null) {
            return false;
        }

        $timeMinutes = $this->toMinutes($time);

        return $timeMinutes !== null
            && $timeMinutes >= $this->toMinutes($hours['start'])
            && ($timeMinutes + $duration) <= $this->toMinutes($hours['end']);
    }

    /**
     * List of bookable start times ("H:i") for a doctor on a given calendar date.
     *
     * Slots that would overlap an existing non-cancelled appointment are excluded.
     * Slots that overlap the break time are excluded.
     */
    public function availableSlots(Doctor $doctor, Carbon|string $date, int $duration = 0): array
    {
        $duration = $duration ?: $this->defaultDuration();

        if (! $this->isWorkingDay($doctor, $date)) {
            return [];
        }

        $hours = $this->workingHours($doctor);

        if ($hours === null) {
            return [];
        }

        if ($this->isUnavailableDate($doctor, $date)) {
            return [];
        }

        $blocked = $this->blockedRanges($doctor, $date);
        $start = $this->toMinutes($hours['start']);
        $end = $this->toMinutes($hours['end']);

        $slots = [];

        for ($cursor = $start; $cursor + $duration <= $end; $cursor += $duration) {
            if (! $this->rangeOverlapsBlocked($cursor, $cursor + $duration, $blocked)) {
                $slots[] = $this->minutesToTime($cursor);
            }
        }

        return $slots;
    }

    /**
     * True when the given start time is a valid, currently available slot.
     */
    public function isAvailableSlot(Doctor $doctor, Carbon|string $date, string $time, int $duration = 0): bool
    {
        $duration = $duration ?: $this->defaultDuration();

        return in_array(substr($time, 0, 5), $this->availableSlots($doctor, $date, $duration), true);
    }

    /**
     * Conflict detection shared by public and admin flows.
     *
     * Mirrors the previous DB query: an existing, non-cancelled appointment for
     * the same doctor and date overlaps [start, start+duration).
     */
    public function hasConflict(int $doctorId, string $date, string $time, int $duration, ?int $excludeId = null): bool
    {
        $query = Appointment::query()
            ->where('doctor_id', $doctorId)
            ->whereDate('date', $date)
            ->whereNotIn('status', [AppointmentStatus::Cancelled->value]);

        if ($excludeId !== null) {
            $query->where('id', '!=', $excludeId);
        }

        $start = $this->toMinutes($time);

        if ($start === null) {
            return false;
        }

        $appointments = $query->get(['time', 'duration']);

        foreach ($appointments as $appointment) {
            $existingStart = $this->toMinutes(substr($appointment->time, 0, 5));
            $existingEnd = ($existingStart ?? 0) + (int) $appointment->duration;

            if ($existingStart < $start + $duration && $existingEnd > $start) {
                return true;
            }
        }

        return false;
    }

    /**
     * Doctor is bookable: active, has a valid schedule and works at least one day.
     */
    public function isBookable(Doctor $doctor): bool
    {
        if (! $doctor->is_available) {
            return false;
        }

        return $this->workingHours($doctor) !== null && count($this->workingDays($doctor)) > 0;
    }

    /**
     * Parses a time string (H, HH:mm, HH:mm:ss) to minutes past midnight or null.
     */
    public function toMinutes(mixed $time): ?int
    {
        if ($time === null || $time === '') {
            return null;
        }

        $parts = explode(':', (string) $time);

        if (count($parts) < 2) {
            return null;
        }

        $hours = (int) $parts[0];
        $minutes = (int) $parts[1];

        if ($hours < 0 || $hours > 23 || $minutes < 0 || $minutes > 59) {
            return null;
        }

        return $hours * 60 + $minutes;
    }

    protected function minutesToTime(int $minutes): string
    {
        return sprintf('%02d:%02d', intdiv($minutes, 60), $minutes % 60);
    }

    /**
     * Occupied time ranges for a doctor/date (minutes since midnight).
     *
     * Includes existing appointments AND break time.
     */
    protected function blockedRanges(Doctor $doctor, Carbon|string $date): array
    {
        $date = $date instanceof Carbon ? $date->toDateString() : Carbon::parse($date)->toDateString();

        $ranges = Appointment::query()
            ->where('doctor_id', $doctor->id)
            ->whereDate('date', $date)
            ->whereNotIn('status', [AppointmentStatus::Cancelled->value])
            ->get(['time', 'duration'])
            ->map(fn (Appointment $appointment) => [
                'start' => $this->toMinutes(substr((string) $appointment->time, 0, 5)) ?? 0,
                'end' => ($this->toMinutes(substr((string) $appointment->time, 0, 5)) ?? 0) + (int) $appointment->duration,
            ])
            ->filter(fn (array $range) => $range['end'] > $range['start'])
            ->values()
            ->all();

        $break = $this->breakHours($doctor);
        if ($break !== null) {
            $ranges[] = [
                'start' => $this->toMinutes($break['start']),
                'end' => $this->toMinutes($break['end']),
            ];
        }

        return $ranges;
    }

    protected function rangeOverlapsBlocked(int $start, int $end, array $blocked): bool
    {
        foreach ($blocked as $range) {
            if ($range['start'] < $end && $range['end'] > $start) {
                return true;
            }
        }

        return false;
    }
}
