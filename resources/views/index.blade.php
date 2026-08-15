<x-app-layout>
    <!-- Hero Section -->
    <section id="hero" class="hero section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-5">
                    <div class="hero-image" data-aos="fade-right" data-aos-delay="100">
                        <img src="assets/img/health/staff-8.webp" alt="Healthcare Professional"
                            class="img-fluid main-image">
                        <div class="floating-card emergency-card" data-aos="fade-up" data-aos-delay="300">
                            <div class="card-content">
                                <i class="bi bi-telephone-fill"></i>
                                <div class="text">
                                    <span class="label">24/7 Emergency</span>
                                    <span class="number">{{ setting('site.phone') ?: '+1 (555) 911-2468' }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="floating-card stats-card" data-aos="fade-up" data-aos-delay="400">
                            <div class="stat-item">
                                <span class="number">{{ $stats['patients'] }}+</span>
                                <span class="label">Patients Treated</span>
                            </div>
                            <div class="stat-item">
                                <span class="number">{{ $stats['doctors'] }}+</span>
                                <span class="label">Medical Specialists</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-7">
                    <div class="hero-content" data-aos="fade-left" data-aos-delay="200">
                        <div class="badge-container">
                            <span class="hero-badge">Trusted Healthcare Provider</span>
                        </div>

                        <h1 class="hero-title">Excellence in Medical Care Since 1985</h1>
                        <p class="hero-description">Mauris blandit aliquet elit, eget tincidunt nibh pulvinar a.
                            Pellentesque in ipsum id orci porta dapibus. Vivamus magna justo, lacinia eget
                            consectetur sed, convallis at tellus.</p>

                        <div class="hero-stats">
                            <div class="stat-group">
                                <div class="stat">
                                    <i class="bi bi-award"></i>
                                    <div class="stat-text">
                                        <span class="number">{{ $stats['departments'] }}+</span>
                                        <span class="label">Medical Departments</span>
                                    </div>
                                </div>
                                <div class="stat">
                                    <i class="bi bi-people"></i>
                                    <div class="stat-text">
                                        <span class="number">{{ $stats['doctors'] }}+</span>
                                        <span class="label">Medical Specialists</span>
                                    </div>
                                </div>
                                <div class="stat">
                                    <i class="bi bi-geo-alt"></i>
                                    <div class="stat-text">
                                        <span class="number">{{ $stats['locations'] }}</span>
                                        <span class="label">Clinic Locations</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="cta-section">
                        <div class="cta-buttons">
                            <a href="{{ route('public.appointment.create') }}" class="btn btn-primary">Schedule Consultation</a>
                            <a href="https://www.youtube.com/watch?v=Y7f98aduVJ8"
                                class="btn btn-secondary glightbox">
                                <i class="bi bi-play-circle"></i>
                                Watch Our Story
                            </a>
                        </div>

                        <div class="quick-actions">
                            <a href="{{ route('public.appointment.create') }}" class="action-link">
                                <i class="bi bi-calendar-check"></i>
                                <span>Find Available Times</span>
                            </a>
                                <a href="#" class="action-link">
                                    <i class="bi bi-chat-dots"></i>
                                    <span>Chat with Support</span>
                                </a>
                                <a href="#" class="action-link">
                                    <i class="bi bi-file-medical"></i>
                                    <span>Patient Portal</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="background-elements">
            <div class="bg-shape shape-1"></div>
            <div class="bg-shape shape-2"></div>
            <div class="bg-pattern"></div>
        </div>
    </section><!-- /Hero Section -->

    <!-- Home About Section -->
    <section id="home-about" class="home-about section">

        <div class="container" data-aos="fade-up" data-aos-delay="100">

            <div class="row">
                <div class="col-lg-8 mx-auto text-center mb-5" data-aos="fade-up" data-aos-delay="150">
                    <h2 class="section-heading">Excellence in Healthcare Since 1985</h2>
                    <p class="lead-description">We are committed to providing world-class medical care through
                        innovation, compassion, and unwavering dedication to our patients' wellbeing and recovery.
                    </p>
                </div>
            </div>

            <div class="row align-items-center gy-5">
                <div class="col-lg-7" data-aos="fade-right" data-aos-delay="200">
                    <div class="image-grid">
                        <div class="primary-image">
                            <img src="assets/img/health/facilities-6.webp" alt="Modern hospital facility"
                                class="img-fluid">
                            <div class="certification-badge">
                                <i class="bi bi-award"></i>
                                <span>JCI Accredited</span>
                            </div>
                        </div>
                        <div class="secondary-images">
                            <div class="small-image">
                                <img src="assets/img/health/consultation-3.webp" alt="Doctor consultation"
                                    class="img-fluid">
                            </div>
                            <div class="small-image">
                                <img src="assets/img/health/surgery-2.webp" alt="Medical procedure"
                                    class="img-fluid">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-5" data-aos="fade-left" data-aos-delay="300">
                    <div class="content-wrapper">
                        <div class="highlight-box">
                            <div class="highlight-icon">
                                <i class="bi bi-heart-pulse-fill"></i>
                            </div>
                            <div class="highlight-content">
                                <h4>Patient-Centered Approach</h4>
                                <p>Every treatment plan is carefully customized to meet individual patient needs and
                                    medical history.</p>
                            </div>
                        </div>

                        <div class="feature-list">
                            <div class="feature-item">
                                <div class="feature-icon">
                                    <i class="bi bi-check-circle-fill"></i>
                                </div>
                                <div class="feature-text">Advanced diagnostic technology and imaging</div>
                            </div>
                            <div class="feature-item">
                                <div class="feature-icon">
                                    <i class="bi bi-check-circle-fill"></i>
                                </div>
                                <div class="feature-text">Board-certified physicians and specialists</div>
                            </div>
                            <div class="feature-item">
                                <div class="feature-icon">
                                    <i class="bi bi-check-circle-fill"></i>
                                </div>
                                <div class="feature-text">Comprehensive rehabilitation programs</div>
                            </div>
                            <div class="feature-item">
                                <div class="feature-icon">
                                    <i class="bi bi-check-circle-fill"></i>
                                </div>
                                <div class="feature-text">24/7 emergency and critical care services</div>
                            </div>
                        </div>

                        <div class="metrics-row">
                            <div class="metric-box">
                                <div class="metric-number">
                                    <span class="purecounter" data-purecounter-start="0" data-purecounter-end="98"
                                        data-purecounter-duration="0">98</span>%
                                </div>
                                <div class="metric-label">Patient Satisfaction</div>
                            </div>
                            <div class="metric-box">
                                <div class="metric-number">
                                    <span class="purecounter" data-purecounter-start="0" data-purecounter-end="35"
                                        data-purecounter-duration="0">35</span>K+
                                </div>
                                <div class="metric-label">Lives Improved</div>
                            </div>
                        </div>

                        <div class="action-buttons">
                            <a href="{{ route('public.about') }}" class="btn-explore">Explore Our Services</a>
                            <a href="{{ route('public.contact') }}" class="btn-contact">
                                <i class="bi bi-telephone"></i>
                                Schedule Consultation
                            </a>
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </section><!-- /Home About Section -->

    <!-- Featured Departments Section -->
    <section id="featured-departments" class="featured-departments section">

        <!-- Section Title -->
        <div class="container section-title" data-aos="fade-up">
            <h2>Featured Departments</h2>
            <p>Necessitatibus eius consequatur ex aliquid fuga eum quidem sint consectetur velit</p>
        </div><!-- End Section Title -->

        <div class="container" data-aos="fade-up" data-aos-delay="100">

            <div class="departments-showcase">

                <div class="featured-department" data-aos="fade-up" data-aos-delay="200">
                    <div class="row align-items-center">
                        <div class="col-lg-6 order-lg-1">
                            <div class="department-content">
                                <div class="department-category">Emergency Medicine</div>
                                <h2 class="department-title">24/7 Emergency Care Services</h2>
                                <p class="department-description">Sed ut perspiciatis unde omnis iste natus error
                                    sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa
                                    quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt
                                    explicabo.</p>
                                <div class="department-features">
                                    <div class="feature-item">
                                        <i class="fas fa-check-circle"></i>
                                        <span>24/7 Emergency Response</span>
                                    </div>
                                    <div class="feature-item">
                                        <i class="fas fa-check-circle"></i>
                                        <span>Advanced Life Support</span>
                                    </div>
                                    <div class="feature-item">
                                        <i class="fas fa-check-circle"></i>
                                        <span>Trauma Care Specialists</span>
                                    </div>
                                </div>
                                <a href="#" class="cta-link">Learn More <i class="fas fa-arrow-right"></i></a>
                            </div>
                        </div>
                        <div class="col-lg-6 order-lg-2">
                            <div class="department-visual">
                                <div class="image-wrapper">
                                    <img src="assets/img/health/emergency-3.webp" alt="Emergency Department"
                                        class="img-fluid">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="departments-grid">
                    <div class="row">
                        @foreach ($departments as $department)
                            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="300">
                                <div class="department-card">
                                    <div class="card-icon">
                                        <i class="{{ $department->icon }}"></i>
                                    </div>
                                    <div class="card-content">
                                        <h3 class="card-title">{{ $department->name }}</h3>
                                        <p class="card-description">{{ $department->description }}</p>
                                        <div class="card-stats">
                                            <div class="stat-item">
                                                <span class="stat-number">{{ $department->doctors_count }}</span>
                                                <span class="stat-label">Doctors</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach

                        {{-- <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="350">
                            <div class="department-card">
                                <div class="card-icon">
                                    <i class="fas fa-brain"></i>
                                </div>
                                <div class="card-content">
                                    <h3 class="card-title">Neurology</h3>
                                    <p class="card-description">Eos qui ratione voluptatem sequi nesciunt neque
                                        porro quisquam est, qui dolorem ipsum quia dolor sit amet.</p>
                                    <div class="card-stats">
                                        <div class="stat-item">
                                            <span class="stat-number">8+</span>
                                            <span class="stat-label">Specialists</span>
                                        </div>
                                        <div class="stat-item">
                                            <span class="stat-number">200+</span>
                                            <span class="stat-label">Treatments</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="400">
                            <div class="department-card">
                                <div class="card-icon">
                                    <i class="fas fa-cut"></i>
                                </div>
                                <div class="card-content">
                                    <h3 class="card-title">Surgery</h3>
                                    <p class="card-description">Consectetur adipisci velit sed quia non numquam
                                        eius modi tempora incidunt ut labore et dolore magnam aliquam.</p>
                                    <div class="card-stats">
                                        <div class="stat-item">
                                            <span class="stat-number">12+</span>
                                            <span class="stat-label">Surgeons</span>
                                        </div>
                                        <div class="stat-item">
                                            <span class="stat-number">1000+</span>
                                            <span class="stat-label">Operations</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="450">
                            <div class="department-card">
                                <div class="card-icon">
                                    <i class="fas fa-baby"></i>
                                </div>
                                <div class="card-content">
                                    <h3 class="card-title">Pediatrics</h3>
                                    <p class="card-description">Quaerat voluptatem ut enim ad minima veniam quis
                                        nostrum exercitationem ullam corporis suscipit laboriosam.</p>
                                    <div class="card-stats">
                                        <div class="stat-item">
                                            <span class="stat-number">10+</span>
                                            <span class="stat-label">Pediatricians</span>
                                        </div>
                                        <div class="stat-item">
                                            <span class="stat-number">2000+</span>
                                            <span class="stat-label">Young Patients</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="500">
                            <div class="department-card">
                                <div class="card-icon">
                                    <i class="fas fa-eye"></i>
                                </div>
                                <div class="card-content">
                                    <h3 class="card-title">Ophthalmology</h3>
                                    <p class="card-description">Nisi ut aliquid ex ea commodi consequatur quis
                                        autem vel eum iure reprehenderit qui in ea voluptate velit esse.</p>
                                    <div class="card-stats">
                                        <div class="stat-item">
                                            <span class="stat-number">6+</span>
                                            <span class="stat-label">Eye Doctors</span>
                                        </div>
                                        <div class="stat-item">
                                            <span class="stat-number">800+</span>
                                            <span class="stat-label">Eye Exams</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="550">
                            <div class="department-card">
                                <div class="card-icon">
                                    <i class="fas fa-band-aid"></i>
                                </div>
                                <div class="card-content">
                                    <h3 class="card-title">Dermatology</h3>
                                    <p class="card-description">Quam nihil molestiae consequatur vel illum qui
                                        dolorem eum fugiat quo voluptas nulla pariatur at vero eos.</p>
                                    <div class="card-stats">
                                        <div class="stat-item">
                                            <span class="stat-number">7+</span>
                                            <span class="stat-label">Dermatologists</span>
                                        </div>
                                        <div class="stat-item">
                                            <span class="stat-number">600+</span>
                                            <span class="stat-label">Skin Treatments</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div> --}}
                    </div>
                </div>

                <div class="departments-cta" data-aos="fade-up" data-aos-delay="600">
                    <div class="cta-content">
                        <h3 class="cta-title">Explore All Our Medical Departments</h3>
                        <p class="cta-description">Et accusamus et iusto odio dignissimos ducimus qui blanditiis
                            praesentium voluptatum deleniti atque corrupti quos dolores.</p>
                        <a href="{{ route('public.department') }}" class="btn btn-primary">View All Departments</a>
                    </div>
                </div>

            </div>

        </div>

    </section><!-- /Featured Departments Section -->

    <!-- Featured Services Section -->
    <section id="featured-services" class="featured-services section">

        <!-- Section Title -->
        <div class="container section-title" data-aos="fade-up">
            <h2>Featured Services</h2>
            <p>Necessitatibus eius consequatur ex aliquid fuga eum quidem sint consectetur velit</p>
        </div><!-- End Section Title -->

        <div class="container" data-aos="fade-up" data-aos-delay="100">

            <div class="row gy-4">

                @forelse ($services as $service)
                    <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="{{ 200 + $loop->index * 100 }}">
                        <div class="service-card">
                            <div class="service-icon">
                                <i class="{{ $service->icon ?: 'fas fa-heartbeat' }}"></i>
                            </div>
                            @if ($service->service_image)
                                <div class="service-image">
                                    <img src="{{ asset('storage/' . $service->service_image) }}" alt="{{ $service->title }}"
                                        class="img-fluid" loading="lazy">
                                </div>
                            @endif
                            <div class="service-content">
                                <h3>{{ $service->title }}</h3>
                                <p>{{ $service->description }}</p>
                                <a href="{{ route('public.service_details', $service) }}" class="service-link">Learn More <i class="fas fa-arrow-right"></i></a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="text-center text-muted py-5">
                            <i class="fas fa-stethoscope fs-1 d-block mb-2"></i>
                            <p class="mb-0">Our services are being updated. Please check back soon.</p>
                        </div>
                    </div>
                @endforelse

            </div>

        </div>

    </section><!-- /Featured Services Section -->

    <!-- Find A Doctor Section -->
    <section id="find-a-doctor" class="find-a-doctor section">

        <!-- Section Title -->
        <div class="container section-title" data-aos="fade-up">
            <h2>Find A Doctor</h2>
            <p>Necessitatibus eius consequatur ex aliquid fuga eum quidem sint consectetur velit</p>
        </div><!-- End Section Title -->

        <div class="container" data-aos="fade-up" data-aos-delay="100">

            <div class="row justify-content-center" data-aos="fade-up" data-aos-delay="200">
                <div class="col-lg-10">
                    <div class="search-header">
                        <h2>Discover Your Ideal Medical Specialist</h2>
                        <p>Connect with our network of certified healthcare professionals across all medical
                            disciplines</p>
                    </div>


                </div>
            </div>

            <div class="specialists-showcase" data-aos="fade-up" data-aos-delay="300">
                @foreach ($doctors as $doctor)
                    <div class="specialist-card featured" data-aos="slide-up" data-aos-delay="100">
                        <div class="card-content">
                            <div class="specialist-info">
                                <div class="profile-section">
                                    <div class="profile-image">
                                        @if ($doctor->profile_image)
                                            <img src="{{ Storage::url($doctor->profile_image) }}" class="img-fluid"
                                                alt="{{ $doctor->name }}" loading="lazy">
                                        @else
                                            <span class="profile-placeholder" style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:2rem;color:var(--accent-color,#0d6efd);background:color-mix(in srgb,var(--accent-color,#0d6efd),transparent 85%);">
                                                {{ initials($doctor->name) }}
                                            </span>
                                        @endif
                                        @if ($doctor->is_available === true)
                                            <div class="online-status active"></div>
                                        @endif

                                    </div>
                                    <div class="specialist-data">
                                        <h3>{{ $doctor->name }}</h3>
                                        <p class="specialty">{{ $doctor->qualifications ?: ($doctor->title ?: ($doctor->role ?: 'Medical Specialist')) }}</p>
                                        <div class="credentials">
                                            @if ($doctor->board_certified)
                                                <span class="badge">Board Certified</span>
                                            @endif
                                            @if ($doctor->experience_years !== null)
                                                <span class="experience">{{ $doctor->experience_years }} years</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="rating-info">
                                    <div class="stars-display">
                                        <i class="bi bi-hospital"></i>
                                    </div>
                                    <span class="score">{{ $doctor->department?->name ?? 'General' }}</span>
                                    <small>{{ $doctor->is_available ? 'Available' : 'Currently unavailable' }}</small>
                                </div>
                            </div>
                            <div class="quick-actions">
                                <button type="button" class="action-btn primary" data-bs-toggle="modal"
                                    data-bs-target="#viewDoctor{{ $doctor->id }}">
                                    View Profile
                                </button>
                                <a href="#" class="action-btn primary" x-data
                                    @click.prevent="alert('ဒီလုပ်ဆောင်ချက်မရရှိသေးပါဘူး ခင်ဗျာ။')">
                                    Consult
                                </a>

                            </div>
                        </div>
                    </div>
                @endforeach

                {{-- <div class="specialist-card" data-aos="slide-up" data-aos-delay="200">
                    <div class="card-content">
                        <div class="specialist-info">
                            <div class="profile-section">
                                <div class="profile-image">
                                    <img src="assets/img/health/staff-3.webp" alt="Dr. Robert Kim" class="img-fluid">
                                    <div class="online-status busy"></div>
                                </div>
                                <div class="specialist-data">
                                    <h3>Dr. Robert Kim</h3>
                                    <p class="specialty">Neurosurgeon</p>
                                    <div class="credentials">
                                        <span class="badge">MD, PhD</span>
                                        <span class="experience">24 years</span>
                                    </div>
                                </div>
                            </div>
                            <div class="rating-info">
                                <div class="stars-display">
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star-half"></i>
                                </div>
                                <span class="score">4.8</span>
                                <small>(98 patients)</small>
                            </div>
                        </div>
                        <div class="quick-actions">
                            <a href="#" class="action-btn outline">Profile</a>
                            <a href="#" class="action-btn primary">Schedule</a>
                        </div>
                    </div>
                </div>

                <div class="specialist-card" data-aos="slide-up" data-aos-delay="300">
                    <div class="card-content">
                        <div class="specialist-info">
                            <div class="profile-section">
                                <div class="profile-image">
                                    <img src="assets/img/health/staff-5.webp" alt="Dr. Sarah Thompson"
                                        class="img-fluid">
                                    <div class="online-status active"></div>
                                </div>
                                <div class="specialist-data">
                                    <h3>Dr. Sarah Thompson</h3>
                                    <p class="specialty">Pediatric Specialist</p>
                                    <div class="credentials">
                                        <span class="badge">MD, FAAP</span>
                                        <span class="experience">12 years</span>
                                    </div>
                                </div>
                            </div>
                            <div class="rating-info">
                                <div class="stars-display">
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star-fill"></i>
                                </div>
                                <span class="score">5.0</span>
                                <small>(156 patients)</small>
                            </div>
                        </div>
                        <div class="quick-actions">
                            <a href="#" class="action-btn outline">Profile</a>
                            <a href="#" class="action-btn primary">Book Now</a>
                        </div>
                    </div>
                </div>

                <div class="specialist-card" data-aos="slide-up" data-aos-delay="400">
                    <div class="card-content">
                        <div class="specialist-info">
                            <div class="profile-section">
                                <div class="profile-image">
                                    <img src="assets/img/health/staff-7.webp" alt="Dr. Michael Rivera"
                                        class="img-fluid">
                                    <div class="online-status offline"></div>
                                </div>
                                <div class="specialist-data">
                                    <h3>Dr. Michael Rivera</h3>
                                    <p class="specialty">Orthopedic Surgeon</p>
                                    <div class="credentials">
                                        <span class="badge">MD, FAAOS</span>
                                        <span class="experience">20 years</span>
                                    </div>
                                </div>
                            </div>
                            <div class="rating-info">
                                <div class="stars-display">
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star-half"></i>
                                </div>
                                <span class="score">4.7</span>
                                <small>(134 patients)</small>
                            </div>
                        </div>
                        <div class="quick-actions">
                            <a href="#" class="action-btn outline">Profile</a>
                            <a href="#" class="action-btn primary">Request</a>
                        </div>
                    </div>
                </div>

                <div class="specialist-card" data-aos="slide-up" data-aos-delay="500">
                    <div class="card-content">
                        <div class="specialist-info">
                            <div class="profile-section">
                                <div class="profile-image">
                                    <img src="assets/img/health/staff-9.webp" alt="Dr. Lisa Garcia"
                                        class="img-fluid">
                                    <div class="online-status active"></div>
                                </div>
                                <div class="specialist-data">
                                    <h3>Dr. Lisa Garcia</h3>
                                    <p class="specialty">Dermatologist</p>
                                    <div class="credentials">
                                        <span class="badge">MD, FAAD</span>
                                        <span class="experience">15 years</span>
                                    </div>
                                </div>
                            </div>
                            <div class="rating-info">
                                <div class="stars-display">
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star"></i>
                                </div>
                                <span class="score">4.6</span>
                                <small>(89 patients)</small>
                            </div>
                        </div>
                        <div class="quick-actions">
                            <a href="#" class="action-btn outline">Profile</a>
                            <a href="#" class="action-btn primary">Consult</a>
                        </div>
                    </div>
                </div>

                <div class="specialist-card" data-aos="slide-up" data-aos-delay="600">
                    <div class="card-content">
                        <div class="specialist-info">
                            <div class="profile-section">
                                <div class="profile-image">
                                    <img src="assets/img/health/staff-12.webp" alt="Dr. Daniel Wong"
                                        class="img-fluid">
                                    <div class="online-status active"></div>
                                </div>
                                <div class="specialist-data">
                                    <h3>Dr. Daniel Wong</h3>
                                    <p class="specialty">Oncology Expert</p>
                                    <div class="credentials">
                                        <span class="badge">MD, FASCO</span>
                                        <span class="experience">21 years</span>
                                    </div>
                                </div>
                            </div>
                            <div class="rating-info">
                                <div class="stars-display">
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star-fill"></i>
                                </div>
                                <span class="score">4.9</span>
                                <small>(211 patients)</small>
                            </div>
                        </div>
                        <div class="quick-actions">
                            <a href="#" class="action-btn outline">Profile</a>
                            <a href="#" class="action-btn primary">Appointment</a>
                        </div>
                    </div>
                </div> --}}

            </div>

            @foreach ($doctors as $doctor)
                @include('doctor-details', ['doctor' => $doctor])
            @endforeach

            <div class="text-center mt-5" data-aos="fade-up" data-aos-delay="700">
                <a href="{{ route('public.doctor-list') }}" class="view-all-link">
                    View All Doctors
                    <i class="bi bi-chevron-right"></i>
                </a>
            </div>

        </div>

    </section><!-- /Find A Doctor Section -->

    <!-- Call To Action Section -->
    <section id="call-to-action" class="call-to-action section light-background">

        <div class="container" data-aos="fade-up" data-aos-delay="100">

            <div class="hero-content" data-aos="fade-up" data-aos-delay="200">
                <div class="row align-items-center">
                    <div class="col-lg-6">
                        <div class="content-wrapper">
                            <h2>Exceptional Care for Your Health Journey</h2>
                            <p>Discover comprehensive healthcare services delivered with compassion and expertise.
                                Our dedicated team is committed to providing personalized medical care that puts you
                                first.</p>

                            <div class="action-buttons">
                                <a href="{{ route('public.appointment.create') }}" class="primary-btn">Schedule Consultation</a>
                                <a href="{{ route('public.services') }}" class="secondary-link">
                                    <span>Explore Services</span>
                                    <i class="fas fa-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="hero-image" data-aos="zoom-in" data-aos-delay="300">
                            <img src="assets/img/health/showcase-2.webp" alt="Healthcare Excellence"
                                class="img-fluid">
                        </div>
                    </div>
                </div>
            </div>

            <div class="stats-section" data-aos="fade-up" data-aos-delay="400">
                <div class="row">
                    <div class="col-lg-3 col-md-6">
                        <div class="stat-item">
                            <div class="stat-number">{{ $stats['departments'] }}+</div>
                            <div class="stat-label">Medical Departments</div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="stat-item">
                            <div class="stat-number">{{ $stats['patients'] }}+</div>
                            <div class="stat-label">Happy Patients</div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="stat-item">
                            <div class="stat-number">{{ $stats['doctors'] }}+</div>
                            <div class="stat-label">Medical Experts</div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="stat-item">
                            <div class="stat-number">24/7</div>
                            <div class="stat-label">Emergency Care</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="services-grid" data-aos="fade-up" data-aos-delay="500">
                <div class="row">

                    @forelse ($services->take(3) as $service)
                        <div class="col-lg-4 col-md-6">
                            <div class="service-card" data-aos="fade-up" data-aos-delay="{{ 100 + $loop->index * 100 }}">
                                <div class="service-icon">
                                    <i class="{{ $service->icon ?: 'fas fa-heartbeat' }}"></i>
                                </div>
                                <h4>{{ $service->title }}</h4>
                                <p>{{ Str::limit($service->description, 140) }}</p>
                                <a href="{{ route('public.service_details', $service) }}" class="service-link">Learn More</a>
                            </div>
                        </div>
                    @empty
                        <div class="col-12 text-center text-muted py-4">
                            Our services are being updated.
                        </div>
                    @endforelse

                </div>
            </div>

            <div class="contact-banner" data-aos="zoom-in" data-aos-delay="600">
                <div class="banner-content">
                    <div class="contact-info">
                        <div class="contact-icon">
                            <i class="fas fa-phone"></i>
                        </div>
                        <div class="contact-text">
                            <h5>Need Immediate Assistance?</h5>
                            <p>Our medical team is available around the clock for urgent consultations and emergency
                                support.</p>
                        </div>
                    </div>
                    <div class="contact-actions">
                        <a href="tel:{{ setting('site.phone') ? preg_replace('/[^0-9+]/', '', setting('site.phone')) : '+15551234567' }}" class="call-btn">
                            <i class="fas fa-phone"></i>
                            {{ setting('site.phone') ?: '(555) 123-4567' }}
                        </a>
                        <a href="{{ route('public.contact') }}" class="contact-link">Get Directions</a>
                    </div>
                </div>
            </div>

        </div>

    </section><!-- /Call To Action Section -->
</x-app-layout>
