<x-app-layout>
    <!-- Page Title -->
    <div class="page-title">
        <div class="heading">
            <div class="container">
                <div class="row d-flex justify-content-center text-center">
                    <div class="col-lg-8">
                        <h1 class="heading-title">Book an Appointment</h1>
                        <p class="mb-0">
                            Choose a department, pick your doctor, then select a date and time that works for you.
                            Our clinic will contact you to confirm your appointment.
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <nav class="breadcrumbs">
            <div class="container">
                <ol>
                    <li><a href="{{ route('public.index') }}">Home</a></li>
                    <li class="current">Book Appointment</li>
                </ol>
            </div>
        </nav>
    </div>

    <style>
        [x-cloak] { display: none !important; }

        .booking-card {
            background-color: var(--surface-color, #fff);
            border-radius: 15px;
            box-shadow: 0 5px 30px rgba(0, 0, 0, 0.08);
            padding: 1.5rem;
            margin-bottom: 1.25rem;
            border: 1px solid rgba(0, 0, 0, 0.05);
        }

        .booking-card .step-label {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            font-size: .82rem;
            font-weight: 600;
            letter-spacing: .04em;
            text-transform: uppercase;
            color: var(--accent-color, #0d6efd);
            margin-bottom: .5rem;
        }

        .booking-card .step-label .step-num {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 1.6rem;
            height: 1.6rem;
            border-radius: 50%;
            background-color: var(--accent-color, #0d6efd);
            color: #fff;
            font-size: .8rem;
        }

        .booking-card h3.card-title {
            font-size: 1.15rem;
            font-weight: 600;
            color: var(--heading-color, #152953);
            margin-bottom: 1rem;
        }

        .step-hint {
            font-size: .9rem;
            color: var(--default-color-muted, #64748b);
            margin-bottom: 0;
        }

        /* --- Doctor info card --- */
        .doctor-summary {
            display: flex;
            gap: 1rem;
            align-items: flex-start;
        }

        .doctor-avatar {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            object-fit: cover;
            flex-shrink: 0;
            background-color: color-mix(in srgb, var(--accent-color, #0d6efd), transparent 85%);
            color: var(--accent-color, #0d6efd);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1.4rem;
            overflow: hidden;
        }

        .doctor-summary h5 { margin-bottom: .15rem; color: var(--heading-color, #152953); }
        .doctor-summary .dept { color: var(--accent-color, #0d6efd); font-size: .92rem; }

        .availability-line {
            display: flex;
            flex-wrap: wrap;
            gap: .35rem;
            margin-top: .5rem;
        }

        .day-badge {
            background-color: color-mix(in srgb, var(--accent-color, #0d6efd), transparent 88%);
            color: var(--accent-color, #0d6efd);
            border: 1px solid color-mix(in srgb, var(--accent-color, #0d6efd), transparent 70%);
            padding: .2rem .6rem;
            border-radius: 999px;
            font-size: .82rem;
            font-weight: 600;
        }

        .hours-line {
            font-size: .95rem;
            color: var(--heading-color, #152953);
            font-weight: 500;
            margin-top: .5rem;
        }

        /* --- Compact date strip --- */
        .date-nav {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: .75rem;
            gap: .5rem;
        }

        .date-nav .date-range { font-weight: 600; color: var(--heading-color, #152953); }

        .dates-strip {
            display: flex;
            flex-wrap: wrap;
            gap: .6rem;
        }

        .date-chip {
            min-width: 104px;
            flex: 1 1 104px;
            padding: .7rem .5rem;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            background: var(--surface-color, #fff);
            text-align: center;
            transition: all .15s ease;
            color: var(--heading-color, #152953);
            line-height: 1.35;
        }

        .date-chip .date-dow {
            display: block;
            font-size: .75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .04em;
            color: var(--default-color-muted, #64748b);
        }

        .date-chip .date-day { display: block; font-size: 1rem; font-weight: 700; }

        .date-chip .date-status { display: block; font-size: .72rem; font-weight: 600; }

        .date-chip:hover:not(:disabled) {
            border-color: var(--accent-color, #0d6efd);
            color: var(--accent-color, #0d6efd);
            background-color: color-mix(in srgb, var(--accent-color, #0d6efd), transparent 94%);
        }

        .date-chip.is-selected {
            background-color: var(--accent-color, #0d6efd);
            border-color: var(--accent-color, #0d6efd);
            color: #fff;
        }

        .date-chip.is-selected .date-dow,
        .date-chip.is-selected .date-status { color: rgba(255, 255, 255, .85); }

        .date-chip:disabled {
            color: #cbd5e1;
            background: #f8fafc;
            border-color: rgba(0, 0, 0, .06);
            cursor: not-allowed;
        }

        .date-chip.is-full:disabled .date-status { color: var(--bs-danger-text-emphasis, #b02a37); }

        .date-chip:focus-visible,
        .slot-chip:focus-visible,
        .cal-btn:focus-visible {
            outline: 3px solid color-mix(in srgb, var(--accent-color, #0d6efd), transparent 55%);
            outline-offset: 1px;
        }

        /* --- Time slots --- */
        .time-group + .time-group { margin-top: 1rem; }

        .time-group-title {
            font-size: .85rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .05em;
            color: var(--default-color-muted, #64748b);
            margin-bottom: .6rem;
        }

        .slots-wrap { display: flex; flex-wrap: wrap; gap: .6rem; }

        .slot-chip {
            min-width: 96px;
            padding: .55rem .8rem;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            background: var(--surface-color, #fff);
            font-weight: 600;
            font-size: .95rem;
            color: var(--heading-color, #152953);
            transition: all .15s ease;
        }

        .slot-chip:hover:not(:disabled) {
            border-color: var(--accent-color, #0d6efd);
            color: var(--accent-color, #0d6efd);
        }

        .slot-chip.is-selected {
            background-color: var(--accent-color, #0d6efd);
            border-color: var(--accent-color, #0d6efd);
            color: #fff;
        }

        .slot-chip:disabled { color: #cbd5e1; cursor: not-allowed; }

        .show-more-btn { margin-top: .85rem; }

        /* --- Summary --- */
        .summary-list { margin: 0; }

        .summary-item {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            padding: .5rem 0;
            border-bottom: 1px dashed rgba(0, 0, 0, .08);
        }

        .summary-item:last-child { border-bottom: 0; }

        .summary-item dt { color: var(--default-color-muted, #64748b); font-weight: 600; margin: 0; }

        .summary-item dd { margin: 0; font-weight: 600; color: var(--heading-color, #152953); text-align: right; }

        /* --- Submit --- */
        .submit-cta {
            background-color: var(--accent-color, #0d6efd);
            border-color: var(--accent-color, #0d6efd);
            color: #fff;
            font-weight: 700;
            font-size: 1.1rem;
            padding: 1rem 1.5rem;
            border-radius: 10px;
            letter-spacing: .02em;
            min-height: 52px;
            border: 0;
            box-shadow: 0 6px 18px color-mix(in srgb, var(--accent-color, #0d6efd), transparent 65%);
        }

        .submit-cta:hover:not(:disabled) { color: #fff; filter: brightness(1.05); }

        .submit-cta:disabled { opacity: .6; cursor: not-allowed; }

        @media (max-width: 576px) {
            .booking-card { padding: 1.15rem; }
            .date-chip { min-width: 96px; flex-basis: calc(50% - .6rem); }
            .slot-chip { min-width: 88px; }
            .submit-cta { min-height: 58px; }
        }
    </style>

    <!-- Appointment Section -->
    <section id="appointment" class="appointment section">
        <div class="container" data-aos="fade-up" data-aos-delay="100">
            <div class="row gy-4">
                <!-- Appointment Info -->
                <div class="col-lg-5">
                    <div class="appointment-info">
                        <h3>Quick & Easy Online Booking</h3>
                        <p class="mb-4">
                            Book your appointment in just a few simple steps. Our healthcare professionals
                            are ready to provide you with the best medical care tailored to your needs.
                        </p>

                        <div class="info-items">
                            <div class="info-item d-flex align-items-center mb-3" data-aos="fade-up" data-aos-delay="200">
                                <div class="icon-wrapper me-3">
                                    <i class="bi bi-calendar-check"></i>
                                </div>
                                <div>
                                    <h5>Flexible Scheduling</h5>
                                    <p class="mb-0">Only real available time slots are shown — no guessing.</p>
                                </div>
                            </div>

                            <div class="info-item d-flex align-items-center mb-3" data-aos="fade-up" data-aos-delay="250">
                                <div class="icon-wrapper me-3">
                                    <i class="bi bi-stopwatch"></i>
                                </div>
                                <div>
                                    <h5>Quick Response</h5>
                                    <p class="mb-0">Our clinic will contact you to confirm your appointment</p>
                                </div>
                            </div>

                            <div class="info-item d-flex align-items-center mb-3" data-aos="fade-up" data-aos-delay="300">
                                <div class="icon-wrapper me-3">
                                    <i class="bi bi-shield-check"></i>
                                </div>
                                <div>
                                    <h5>Expert Medical Care</h5>
                                    <p class="mb-0">Board-certified doctors and specialists at your service</p>
                                </div>
                            </div>
                        </div>

                        <div class="emergency-contact mt-4" data-aos="fade-up" data-aos-delay="350">
                            <div class="emergency-card p-3">
                                <h6 class="mb-2"><i class="bi bi-telephone-fill me-2"></i>Emergency Hotline</h6>
                                <p class="mb-0">Call <strong>{{ setting('site.phone') ?: '+1 (555) 911-4567' }}</strong> for urgent medical assistance</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Appointment Form -->
                <div class="col-lg-7">
                    <div class="appointment-form-wrapper" data-aos="fade-up" data-aos-delay="200">

                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form action="{{ route('public.appointment.store') }}" method="POST"
                            x-data="appointmentBooking()" x-cloak
                            x-on:submit.prevent="onSubmit()">
                            @csrf

                            {{-- State-synced payload. The selects below are pure UI and are NOT named on
                                 purpose: disabled controls are dropped from native form submission, which was
                                 silently stripping doctor_id/department_id from the POST payload. These
                                 hidden fields always carry the current Alpine state. --}}
                            <input type="hidden" name="department_id" :value="departmentId">
                            <input type="hidden" name="doctor_id" :value="doctorId">
                            <input type="hidden" name="date" :value="selectedDate">
                            <input type="hidden" name="time" :value="time">

                            {{-- STEP 1 — DEPARTMENT --}}
                            <div class="booking-card">
                                <div class="step-label">
                                    <span class="step-num">1</span> Choose a Department
                                </div>
                                <h3 class="card-title">Which department do you need?</h3>
                                <label for="department_id" class="form-label">Department <span class="text-danger">*</span></label>
                                <select id="department_id" class="form-select"
                                    x-model="departmentId"
                                    x-on:change="departmentId && loadDoctors()"
                                    :disabled="submitting">
                                    <option value="">Select Department</option>
                                    <template x-for="d in departments" :key="d.id">
                                        <option :value="d.id" x-text="d.name"></option>
                                    </template>
                                </select>
                                <p class="step-hint mt-2">Select a department first so we only show you the right doctors.</p>
                            </div>

                            {{-- STEP 2 — DOCTOR + AVAILABILITY --}}
                            <div class="booking-card">

                                <div class="step-label">
                                    <span class="step-num">2</span> Choose a Doctor
                                </div>
                                <h3 class="card-title">Who would you like to see?</h3>

                                <div x-show="loadingDoctors" class="alert alert-light py-2 mb-3">
                                    <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                    Loading doctors...
                                </div>

                                <label for="doctor_id" class="form-label">Doctor <span class="text-danger">*</span></label>
                                <select id="doctor_id" class="form-select"
                                    x-model="doctorId"
                                    x-on:change="selectDoctor($event.target.value)"
                                    :disabled="!departmentId || loadingDoctors || submitting">
                                    <option value="" x-text="doctorPlaceholder"></option>
                                    <template x-for="d in doctors" :key="d.id">
                                        <option :value="d.id" x-text="d.name + (d.title ? ' — ' + d.title : '')"></option>
                                    </template>
                                </select>

                                <div x-show="departmentId && !loadingDoctors && doctors.length === 0" class="alert alert-warning mt-3 mb-0">
                                    <i class="bi bi-exclamation-triangle me-2"></i>
                                    No doctors are currently available in this department.
                                </div>

                                {{-- Availability summary --}}
                                <div x-show="doctor" x-transition class="mt-3">
                                    <div class="doctor-summary p-3 rounded-3" style="background-color:#f8fafc;">
                                        <template x-if="doctor">
                                            <div>
                                                <div class="doctor-avatar" x-show="doctor.photo_url">
                                                    <img :src="doctor.photo_url" alt="" class="doctor-avatar">
                                                </div>
                                                <div class="doctor-avatar" x-show="!doctor.photo_url"
                                                    x-text="initials(doctor.name)" aria-hidden="true"></div>
                                            </div>
                                        </template>

                                        <div>
                                            <h5 x-text="doctor && doctor.name"></h5>
                                            <div class="dept" x-text="doctor && doctor.department"></div>

                                            <div class="availability-line" x-show="doctor && doctor.working_days && doctor.working_days.length">
                                                <template x-for="label in doctor.working_day_labels" :key="label">
                                                    <span class="day-badge" x-text="label"></span>
                                                </template>
                                            </div>

                                            <div class="hours-line" x-show="doctor && doctor.working_hours">
                                                <i class="bi bi-clock me-1"></i>
                                                <span x-text="formatHours(doctor.working_hours.start)"></span>
                                                &ndash;
                                                <span x-text="formatHours(doctor.working_hours.end)"></span>
                                            </div>

                                            <div class="mt-2" x-show="doctor && !doctor.working_hours">
                                                <span class="badge bg-secondary">Schedule not set up yet</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- STEP 3 — DATE --}}
                            <div class="booking-card" x-show="doctor" x-transition>
                                <div class="step-label">
                                    <span class="step-num">3</span> Choose a Date
                                </div>
                                <h3 class="card-title" x-text="dateTitle"></h3>
                                <p class="step-hint mb-3">These are the next dates <span x-text="doctorName"></span> works.</p>

                                <div class="date-nav">
                                    <button type="button" class="btn btn-outline-secondary btn-sm cal-btn"
                                        @click="pageDates(-1)" :disabled="!canPrevDates || loadingSlots" aria-label="Earlier dates">
                                        <i class="bi bi-chevron-left"></i>
                                    </button>
                                    <span class="date-range" x-text="dateWindowLabel"></span>
                                    <button type="button" class="btn btn-outline-secondary btn-sm cal-btn"
                                        @click="pageDates(1)" :disabled="!canNextDates || loadingSlots" aria-label="Next dates">
                                        <i class="bi bi-chevron-right"></i>
                                    </button>
                                </div>

                                <div class="dates-strip" role="listbox" aria-label="Available appointment dates">
                                    <template x-for="d in dateWindow" :key="d.dateStr">
                                        <button type="button" class="date-chip"
                                            :class="{ 'is-selected': d.dateStr === selectedDate, 'is-full': d.fullyBooked }"
                                            :disabled="d.fullyBooked || loadingSlots"
                                            :aria-pressed="d.dateStr === selectedDate"
                                            :aria-label="d.dayName + ' ' + d.dayLabel + (d.fullyBooked ? ' — fully booked' : ' available')"
                                            @click="selectDate(d.dateStr)">
                                            <span class="date-dow" x-text="d.dayName"></span>
                                            <span class="date-day" x-text="d.dayLabel"></span>
                                            <span class="date-status" x-show="d.fullyBooked">Fully booked</span>
                                        </button>
                                    </template>
                                </div>

                                <p class="mt-3 mb-0 step-hint" x-show="dateWindow.length === 0 && doctor">
                                    No upcoming working dates were found for this doctor.
                                </p>
                            </div>

                            {{-- STEP 4 — TIME --}}
                            <div class="booking-card" x-show="selectedDate" x-transition>
                                <div class="step-label">
                                    <span class="step-num">4</span> Choose a Time
                                </div>
                                <h3 class="card-title" x-text="selectedDate ? 'Available times on ' + humanDate(selectedDate) : ''"></h3>

                                <p class="mb-3" x-show="loadingSlots">
                                    <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                    Checking availability...
                                </p>

                                <div x-show="!loadingSlots && slots.length === 0">
                                    <div class="alert alert-info mb-0">
                                        <p class="mb-2" x-text="slotMessage || 'No appointment times are available for this date.'"></p>
                                        <button type="button" class="btn btn-sm btn-outline-primary" @click="chooseAnotherDate">
                                            <i class="bi bi-calendar-event me-1"></i> Choose another date
                                        </button>
                                    </div>
                                </div>

                                <div x-show="!loadingSlots && slots.length > 0">
                                    <template x-for="g in timeGroups" :key="g.label">
                                        <div class="time-group" x-show="g.slots.length">
                                            <h5 class="time-group-title" x-text="g.label"></h5>
                                            <div class="slots-wrap" role="listbox" :aria-label="g.label + ' time slots'">
                                                <template x-for="s in groupVisibleSlots(g)" :key="g.label + s">
                                                    <button type="button" class="slot-chip"
                                                        :class="{ 'is-selected': time === s }"
                                                        :aria-pressed="time === s"
                                                        :aria-label="'Book at ' + formatHours(s) + ' in the ' + g.label"
                                                        @click="selectTime(s)">
                                                        <span x-text="formatHours(s)"></span>
                                                    </button>
                                                </template>
                                            </div>
                                            <button type="button" class="btn btn-sm btn-link show-more-btn"
                                                x-show="g.slots.length > 8"
                                                x-text="expandedGroups[g.label] ? 'Show fewer times' : 'Show all ' + g.slots.length + ' times'"
                                                @click="toggleGroup(g.label)">
                                            </button>
                                        </div>
                                    </template>

                                    <p class="mt-3 mb-0 step-hint">
                                        <span x-text="slots.length + (slots.length === 1 ? ' appointment time available' : ' appointment times available')"></span>
                                    </p>
                                </div>
                            </div>

                            {{-- STEP 5 — SUMMARY --}}
                            <div class="booking-card" x-show="doctor && selectedDate && time" x-transition>
                                <div class="step-label">
                                    <span class="step-num">5</span> Confirm Booking
                                </div>
                                <h3 class="card-title">Appointment Summary</h3>

                                <dl class="summary-list">
                                    <div class="summary-item">
                                        <dt>Department</dt>
                                        <dd x-text="departmentName"></dd>
                                    </div>
                                    <div class="summary-item">
                                        <dt>Doctor</dt>
                                        <dd x-text="doctor && doctor.name"></dd>
                                    </div>
                                    <div class="summary-item">
                                        <dt>Date</dt>
                                        <dd x-text="humanDate(selectedDate)"></dd>
                                    </div>
                                    <div class="summary-item">
                                        <dt>Time</dt>
                                        <dd x-text="formatHours(time)"></dd>
                                    </div>
                                </dl>
                            </div>

                            {{-- STEP 6 — PATIENT INFORMATION --}}
                            <div class="booking-card">
                                <div class="step-label">
                                    <span class="step-num">6</span> Your Information
                                </div>
                                <h3 class="card-title">Complete your details</h3>

                                <div x-show="!time" class="alert alert-light mb-3">
                                    Select an available time above to complete your information.
                                </div>

                                <div class="row gy-3" x-show="time" x-transition>
                                    <div class="col-md-6">
                                        <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                                        <input type="text" name="name" id="name" class="form-control"
                                            x-model="name" placeholder="Enter your full name" required>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="phone" class="form-label">Phone Number <span class="text-danger">*</span></label>
                                        <input type="tel" name="phone" id="phone" class="form-control"
                                            x-model="phone" placeholder="Enter your phone number" required>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="email" class="form-label">Email <small class="text-muted">(Optional)</small></label>
                                        <input type="email" name="email" id="email" class="form-control"
                                            x-model="email" placeholder="Enter your email address">
                                    </div>

                                    <div class="col-12">
                                        <label for="message" class="form-label">Reason for Visit</label>
                                        <textarea class="form-control" name="message" id="message" rows="4"
                                            x-model="reason"
                                            placeholder="Please describe your symptoms or reason for visit..."></textarea>
                                    </div>
                                </div>
                            </div>

                            <div x-show="notice" class="alert alert-warning" role="alert" x-text="notice"></div>

                            <div class="d-grid mt-2">
                                <button type="submit" class="btn submit-cta" :disabled="submitting || !canSubmit">
                                    <span x-show="!submitting">
                                        <i class="bi bi-calendar-plus me-2"></i> Request Appointment
                                    </span>
                                    <span x-show="submitting">
                                        <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                        Submitting...
                                    </span>
                                </button>
                                <small class="text-muted text-center mt-3">
                                    <i class="bi bi-info-circle me-1"></i>
                                    Submitting sends an appointment request; our clinic will call to confirm.
                                </small>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Process Steps -->
            <div class="process-steps mt-5" data-aos="fade-up" data-aos-delay="300">
                <div class="row text-center gy-4">
                    <div class="col-lg-3 col-md-6">
                        <div class="step-item">
                            <div class="step-number">1</div>
                            <div class="step-icon">
                                <i class="bi bi-person-fill"></i>
                            </div>
                            <h5>Fill Details</h5>
                            <p>Provide your contact information and select your preferred doctor</p>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-6">
                        <div class="step-item">
                            <div class="step-number">2</div>
                            <div class="step-icon">
                                <i class="bi bi-calendar-event"></i>
                            </div>
                            <h5>Choose Date</h5>
                            <p>Select your preferred date and time for the appointment</p>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-6">
                        <div class="step-item">
                            <div class="step-number">3</div>
                            <div class="step-icon">
                                <i class="bi bi-telephone"></i>
                            </div>
                            <h5>Confirmation</h5>
                            <p>Our clinic will contact you to confirm your appointment details</p>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-6">
                        <div class="step-item">
                            <div class="step-number">4</div>
                            <div class="step-icon">
                                <i class="bi bi-heart-pulse"></i>
                            </div>
                            <h5>Get Treatment</h5>
                            <p>Visit our clinic at your confirmed time and receive quality healthcare</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script>
        window.appointmentBooking = function () {
            const WINDOW = 7;

            return {
                departments: @json($departments->map(fn ($d) => ['id' => $d->id, 'name' => $d->name])->values()),
                urls: {
                    doctors: @json(route('public.appointment.doctors')),
                    availability: @json(route('public.appointment.availability')),
                },

                departmentId: @json($preselected['department_id'] ?? ''),
                doctors: [],
                loadingDoctors: false,
                doctorId: @json($preselected['doctor_id'] ?? ''),
                doctor: null,
                datePage: 0,
                dateCache: {},
                prefetching: false,
                selectedDate: @json($preselected['date'] ?? ''),
                loadingSlots: false,
                slots: [],
                slotMessage: null,
                time: @json($preselected['time'] ?? ''),
                expandedGroups: {},
                name: @json(old('name') ?? ''),
                phone: @json(old('phone') ?? ''),
                email: @json(old('email') ?? ''),
                reason: @json(old('message') ?? ''),
                submitting: false,
                notice: '',

                async init() {
                    if (this.departmentId) {
                        const restoreDate = this.selectedDate;
                        const restoreTime = this.time;
                        await this.loadDoctors();
                        if (this.doctorId) {
                            this.selectDoctor(this.doctorId);
                        }
                        if (restoreDate) {
                            await this.selectDate(restoreDate);
                        }
                        this.time = restoreTime;
                    }
                },

                get doctorPlaceholder() {
                    if (!this.departmentId) return 'Please select a department first';
                    if (this.loadingDoctors) return 'Loading doctors...';
                    if (this.doctors.length === 0) return 'No doctors are currently available in this department.';
                    return 'Select a doctor';
                },

                get doctorName() {
                    return this.doctor ? this.doctor.name : 'this doctor';
                },

                get dateTitle() {
                    return this.doctor ? 'Pick a date when ' + this.doctor.name + ' is available' : 'Select a date';
                },

                get departmentName() {
                    const d = this.departments.find(x => String(x.id) === String(this.departmentId));
                    return d ? d.name : '';
                },

                async loadDoctors() {
                    this.loadingDoctors = true;
                    this.doctors = [];
                    this.selectDoctor(null);
                    try {
                        const res = await fetch(this.urls.doctors + '?department_id=' + encodeURIComponent(this.departmentId));
                        const data = await res.json();
                        this.doctors = data.doctors || [];
                    } catch (e) {
                        this.doctors = [];
                    } finally {
                        this.loadingDoctors = false;
                    }
                },

                selectDoctor(id) {
                    this.doctorId = String(id || '');
                    this.doctor = id ? (this.doctors.find(d => String(d.id) === String(id)) || null) : null;
                    if (this.doctor) {
                        this.doctor.working_day_labels = (this.doctor.working_days || []).map(d => this.dayName(d));
                    }
                    this.resetDateState();
                    if (this.doctor) {
                        this.prefetchVisibleDates();
                    }
                },

                resetDateState() {
                    this.selectedDate = '';
                    this.time = '';
                    this.slots = [];
                    this.slotMessage = null;
                    this.datePage = 0;
                    this.dateCache = {};
                    this.expandedGroups = {};
                },

                /* ----- date strip ----- */

                dateInfo(dt) {
                    const dateStr = dt.getFullYear() + '-' + String(dt.getMonth() + 1).padStart(2, '0') + '-' + String(dt.getDate()).padStart(2, '0');
                    return {
                        dateStr,
                        dayName: ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'][dt.getDay()],
                        dayLabel: dt.getDate() + ' ' + ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'][dt.getMonth()],
                    };
                },

                buildWindow(page) {
                    const daysSet = new Set((this.doctor ? this.doctor.working_days || [] : []).map(Number));
                    const all = [];
                    const start = new Date();
                    start.setHours(0, 0, 0, 0);
                    let cur = new Date(start);
                    const need = (page + 1) * WINDOW;
                    let guard = 0;
                    while (all.length < need && guard < 2000) {
                        const iso = cur.getDay() === 0 ? 7 : cur.getDay();
                        if (daysSet.has(iso)) {
                            const info = this.dateInfo(cur);
                            const c = this.dateCache[info.dateStr];
                            info.fullyBooked = !!(c && c.loaded && c.count === 0);
                            all.push(info);
                        }
                        const nxt = new Date(cur);
                        nxt.setDate(cur.getDate() + 1);
                        cur = nxt;
                        guard++;
                    }
                    return all.slice(page * WINDOW, need);
                },

                get dateWindow() {
                    return this.doctor ? this.buildWindow(this.datePage) : [];
                },

                get dateWindowLabel() {
                    const w = this.dateWindow;
                    if (!w.length) return this.doctor ? 'No upcoming working dates' : '';
                    return w[0].dayLabel + ' – ' + w[w.length - 1].dayLabel;
                },

                get canPrevDates() {
                    return this.datePage > 0;
                },

                get canNextDates() {
                    return this.doctor && this.buildWindow(this.datePage + 1).length > 0;
                },

                async pageDates(dir) {
                    const np = this.datePage + dir;
                    if (np < 0) return;
                    this.datePage = np;
                    await this.prefetchVisibleDates();
                },

                async prefetchVisibleDates() {
                    if (this.prefetching || !this.doctorId) return;
                    this.prefetching = true;
                    try {
                        for (const d of this.dateWindow) {
                            if (this.dateCache[d.dateStr] && this.dateCache[d.dateStr].loaded) continue;
                            try {
                                const res = await fetch(this.urls.availability + '?doctor_id=' + encodeURIComponent(this.doctorId) + '&date=' + encodeURIComponent(d.dateStr));
                                const data = await res.json();
                                this.dateCache[d.dateStr] = { loaded: true, count: (data.slots || []).length };
                            } catch (e) {
                                this.dateCache[d.dateStr] = { loaded: true, count: null };
                            }
                        }
                    } finally {
                        this.prefetching = false;
                    }
                },

                async selectDate(dateStr) {
                    this.selectedDate = dateStr;
                    this.time = '';
                    this.slots = [];
                    this.slotMessage = null;
                    if (!this.doctorId || !dateStr) return;
                    this.loadingSlots = true;
                    try {
                        const res = await fetch(this.urls.availability + '?doctor_id=' + encodeURIComponent(this.doctorId) + '&date=' + encodeURIComponent(dateStr));
                        const data = await res.json();
                        this.slots = data.slots || [];
                        this.slotMessage = data.available ? null : (data.message || 'No appointment times are available for this date.');
                    } catch (e) {
                        this.slots = [];
                        this.slotMessage = 'We could not check availability right now. Please try again.';
                    } finally {
                        this.loadingSlots = false;
                    }
                },

                chooseAnotherDate() {
                    this.selectedDate = '';
                    this.time = '';
                    this.slots = [];
                    this.slotMessage = null;
                },

                /* ----- time slots ----- */

                get timeGroups() {
                    const groups = { Morning: [], Afternoon: [], Evening: [] };
                    for (const s of this.slots) {
                        const h = parseInt(String(s).slice(0, 2), 10);
                        if (h < 12) groups.Morning.push(s);
                        else if (h < 17) groups.Afternoon.push(s);
                        else groups.Evening.push(s);
                    }
                    return ['Morning', 'Afternoon', 'Evening']
                        .map(label => ({ label, slots: groups[label] }))
                        .filter(g => g.slots.length > 0);
                },

                groupVisibleSlots(g) {
                    const limit = 8;
                    return this.expandedGroups[g.label] ? g.slots : g.slots.slice(0, limit);
                },

                toggleGroup(label) {
                    this.expandedGroups[label] = !this.expandedGroups[label];
                },

                selectTime(s) {
                    this.time = (this.time === s ? '' : s);
                },

                /* ----- helpers ----- */

                dayName(iso) {
                    return ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'][iso - 1] || '';
                },

                humanDate(dateStr) {
                    const d = new Date(dateStr + 'T00:00:00');
                    return d.toLocaleDateString(undefined, { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
                },

                formatHours(value) {
                    if (!value) return '';
                    const parts = String(value).split(':');
                    let h = parseInt(parts[0], 10);
                    let m = parts[1] || '00';
                    const ampm = h >= 12 ? 'PM' : 'AM';
                    h = h % 12;
                    if (h === 0) h = 12;
                    return h + ':' + m + ' ' + ampm;
                },

                initials(name) {
                    if (!name) return '?';
                    return name.replace('Dr. ', '').split(/\s+/).slice(0, 2).map(w => w[0]).join('').toUpperCase();
                },

                /* ----- submission ----- */

                get canSubmit() {
                    return !!(this.departmentId && this.doctorId && this.selectedDate && this.time &&
                        String(this.name || '').trim() && String(this.phone || '').trim() && !this.submitting);
                },

                onSubmit() {
                    if (!this.canSubmit) {
                        this.notice = 'Please complete every step — including your name and phone number — before requesting an appointment.';
                        return;
                    }
                    this.notice = '';
                    this.submitting = true;
                    this.$nextTick(() => { this.$el.submit(); });
                },
            };
        };
    </script>
</x-app-layout>