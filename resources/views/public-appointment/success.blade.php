<x-app-layout>
    <!-- Page Title -->
    <div class="page-title">
        <div class="heading">
            <div class="container">
                <div class="row d-flex justify-content-center text-center">
                    <div class="col-lg-8">
                        <h1 class="heading-title">Appointment Request</h1>
                        <p class="mb-0">Thank you for choosing our clinic</p>
                    </div>
                </div>
            </div>
        </div>
        <nav class="breadcrumbs">
            <div class="container">
                <ol>
                    <li><a href="{{ route('public.index') }}">Home</a></li>
                    <li><a href="{{ route('public.appointment.create') }}">Book Appointment</a></li>
                    <li class="current">Success</li>
                </ol>
            </div>
        </nav>
    </div>

    <!-- Success Section -->
    <section id="appointment-success" class="appointment section">
        <div class="container" data-aos="fade-up" data-aos-delay="100">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card shadow-sm border-0">
                        <div class="card-body p-5 text-center">
                            <div class="mb-4">
                                <i class="bi bi-check-circle-fill text-success" style="font-size: 5rem;"></i>
                            </div>

                            <h2 class="mb-3">Appointment Request Received!</h2>

                            <p class="lead mb-4">
                                Thank you, <strong>{{ session('appointment_name', 'Patient') }}</strong>.
                                Your appointment request has been received successfully.
                            </p>

                            <div class="card bg-light border-0 mb-4">
                                <div class="card-body p-4">
                                    <h5 class="mb-3">Request Details</h5>
                                    <div class="row text-start">
                                        <div class="col-md-6 mb-3">
                                            <strong><i class="bi bi-person me-2"></i>Requested Doctor:</strong>
                                            <p class="mb-0">{{ session('doctor_name', '-') }}</p>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <strong><i class="bi bi-calendar me-2"></i>Preferred Date:</strong>
                                            <p class="mb-0">{{ session('appointment_date', '-') }}</p>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <strong><i class="bi bi-clock me-2"></i>Preferred Time:</strong>
                                            <p class="mb-0">{{ session('appointment_time', '-') }}</p>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <strong><i class="bi bi-info-circle me-2"></i>Status:</strong>
                                            <p class="mb-0"><span class="badge bg-warning text-dark">Pending Review</span></p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="alert alert-info text-start">
                                <h6 class="alert-heading"><i class="bi bi-info-circle me-2"></i>What happens next?</h6>
                                <ul class="mb-0">
                                    <li>Our clinic staff will review your appointment request</li>
                                    <li>We will contact you to confirm the appointment</li>
                                    <li>Please ensure your phone number is correct and accessible</li>
                                    <li>If you need immediate assistance, please call us at <strong>{{ setting('site.phone') ?: '+1 (555) 911-4567' }}</strong></li>
                                </ul>
                            </div>

                            <div class="mt-4">
                                <a href="{{ route('public.index') }}" class="btn btn-primary me-2">
                                    <i class="bi bi-house me-1"></i> Back to Home
                                </a>
                                <a href="{{ route('public.appointment.create') }}" class="btn btn-outline-primary">
                                    <i class="bi bi-calendar-plus me-1"></i> Book Another Appointment
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</x-app-layout>
