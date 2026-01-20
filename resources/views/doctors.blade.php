<x-app-layout>
    <!-- Page Title -->
    <div class="page-title">
        <div class="heading">
            <div class="container">
                <div class="row d-flex justify-content-center text-center">
                    <div class="col-lg-8">
                        <h1 class="heading-title">Doctors</h1>
                        <p class="mb-0">
                            Odio et unde deleniti. Deserunt numquam exercitationem. Officiis quo
                            odio sint voluptas consequatur ut a odio voluptatem. Sit dolorum
                            debitis veritatis natus dolores. Quasi ratione sint. Sit quaerat
                            ipsum dolorem.
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <nav class="breadcrumbs">
            <div class="container">
                <ol>
                    <li><a href="index.html">Home</a></li>
                    <li class="current">Doctors</li>
                </ol>
            </div>
        </nav>
    </div><!-- End Page Title -->

    <!-- Doctors Section -->
    <section id="doctors" class="doctors section">

        <div class="container" data-aos="fade-up" data-aos-delay="100">

            <!-- Filterable Doctor Directory -->
            <div class="doctor-directory mb-5">
                <div class="directory-bar p-3 p-md-4 rounded-3">
                    <div class="row g-3 align-items-center">
                        {{-- <div class="col-lg-4">
                            <label for="doctor-search" class="form-label mb-1">Search Doctors</label>
                            <div class="position-relative">
                                <i class="bi bi-search search-icon"></i>
                                <input id="doctor-search" type="text" class="form-control search-input"
                                    placeholder="Type a name or keyword">
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <label class="form-label mb-1">Department</label>
                            <select class="form-select">
                                <option value="*">All Departments</option>
                                <option value=".filter-cardiology">Cardiology</option>
                                <option value=".filter-pediatrics">Pediatrics</option>
                                <option value=".filter-dermatology">Dermatology</option>
                                <option value=".filter-orthopedics">Orthopedics</option>
                            </select>
                        </div>
                        <div class="col-lg-3">
                            <label class="form-label mb-1">Location</label>
                            <select class="form-select">
                                <option>All Locations</option>
                                <option>Downtown Clinic</option>
                                <option>Westside Center</option>
                                <option>Riverside Campus</option>
                            </select>
                        </div>
                        <div class="col-lg-2 d-grid">
                            <button class="btn btn-appointment">Apply Filters</button>
                        </div> --}}
                        <form method="GET" action="{{ route('public.doctor-list') }}">
                            <div class="card shadow-sm border-0 mb-4">
                                <div class="card-body">
                                    <div class="row g-3 align-items-end">

                                        {{-- Search --}}
                                        <div class="col-md-3">
                                            <label class="form-label">Search Name</label>
                                            <input type="text" name="search" class="form-control"
                                                placeholder="Doctor name..." value="{{ request('search') }}">
                                        </div>

                                        {{-- Department --}}
                                        <div class="col-md-3">
                                            <label class="form-label">Department</label>
                                            <select name="department_id" class="form-select">
                                                <option value="">All Departments</option>
                                                @foreach ($departments as $department)
                                                    <option value="{{ $department->id }}"
                                                        {{ request('department_id') == $department->id ? 'selected' : '' }}>
                                                        {{ $department->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        {{-- Location --}}
                                        <div class="col-md-3">
                                            <label class="form-label">Location</label>
                                            <select name="location_id" class="form-select">
                                                <option value="">All Locations</option>
                                                @foreach ($locations as $location)
                                                    <option value="{{ $location->id }}"
                                                        {{ request('location_id') == $location->id ? 'selected' : '' }}>
                                                        {{ $location->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        {{-- Status --}}
                                        <div class="col-md-2">
                                            <label class="form-label">Status</label>
                                            <select name="status" class="form-select">
                                                <option value="">All</option>
                                                <option value="1"
                                                    {{ request('status') === '1' ? 'selected' : '' }}>
                                                    Active
                                                </option>
                                                <option value="0"
                                                    {{ request('status') === '0' ? 'selected' : '' }}>
                                                    Inactive
                                                </option>
                                            </select>
                                        </div>

                                        {{-- Buttons --}}
                                        <div class="col-md-1 d-grid">
                                            <button class="btn btn-primary">
                                                Filter
                                            </button>
                                        </div>

                                    </div>

                                    {{-- Reset --}}
                                    @if (request()->hasAny(['search', 'department_id', 'location_id', 'status']))
                                        <div class="mt-3">
                                            <a href="{{ route('public.doctor-list') }}"
                                                class="btn btn-sm btn-outline-secondary">
                                                Reset Filters
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="isotope-layout" data-default-filter="*" data-layout="masonry" data-sort="original-order">
                    <ul class="directory-filters isotope-filters" data-aos="fade-up" data-aos-delay="200">
                        <li data-filter="*" class="filter-active">All</li>
                        <li data-filter=".filter-cardiology">Cardiology</li>
                        <li data-filter=".filter-pediatrics">Pediatrics</li>
                        <li data-filter=".filter-dermatology">Dermatology</li>
                        <li data-filter=".filter-orthopedics">Orthopedics</li>
                    </ul><!-- End Directory Filters -->

                    <div class="row gy-4 isotope-container" data-aos="fade-up" data-aos-delay="300">

                        @foreach ($doctors as $doctor)
                            <div class="col-lg-3 col-md-6 doctor-item isotope-item filter-cardiology">
                                <article class="doctor-card h-100">
                                    <figure class="doctor-media">
                                        <img src="{{ $doctor->profile_image ? asset('storage/' . $doctor->profile_image) : 'https://ui-avatars.com/api/?name=' . urlencode($doctor->name) }}"
                                            class="img-fluid" alt="{{ $doctor->name }}" loading="lazy">
                                        <span class="tag"> {{ $doctor->specialty }} </span>
                                    </figure>
                                    <div class="doctor-content">
                                        <h3 class="doctor-name"> {{ $doctor->name }} </h3>
                                        <p class="doctor-title"> {{ $doctor->role }} </p>
                                        <p class="doctor-desc"> {{ Str::limit($doctor->biography, 100) }} </p>
                                        <div class="doctor-meta">
                                            <span class="badge dept"> {{ $doctor->department->name }} </span>
                                        </div>
                                        <div class="doctor-actions">
                                            <a href="#" class="btn btn-sm btn-appointment">Book Appointment</a>
                                            <button type="button" class="btn btn-sm btn-soft" data-bs-toggle="modal"
                                                data-bs-target="#viewDoctor{{ $doctor->id }}">
                                                View Profile
                                            </button>

                                        </div>
                                    </div>
                                </article>
                            </div><!-- End Directory Item -->
                        @endforeach

                    </div><!-- End Directory Items Container -->
                </div>
            </div><!-- End Filterable Doctor Directory -->

            @foreach ($doctors as $doctor)
                @include('doctor-details', ['doctor' => $doctor])
            @endforeach

            <!-- Single Doctor Profile -->
            <div class="single-profile mt-5">
                <div class="row align-items-center g-4">
                    <div class="col-lg-5" data-aos="fade-right" data-aos-delay="150">
                        <div class="profile-media">
                            <img src="assets/img/health/staff-12.webp" class="img-fluid" alt="Dr. Natalia Rivera">
                            <div class="availability">
                                <i class="bi bi-circle-fill me-1"></i> Available this week
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-7" data-aos="fade-left" data-aos-delay="200">
                        <div class="profile-content">
                            <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                                <span class="badge role">Chief Surgeon</span>
                                <span class="badge years">12+ Years Experience</span>
                                <span class="badge cert">Board Certified</span>
                            </div>
                            <h3 class="name mb-1">Dr. Natalia Rivera</h3>
                            <p class="title mb-3">General Surgery • MD, FACS</p>
                            <p class="bio mb-3">Commodo incididunt aliqua minim, eiusmod in laboris nulla. Amet do
                                occaecat quis, excepteur in magna id dolore incididunt. Tempor in aute ullamco, irure
                                officia aliqua nostrud.</p>
                            <ul class="list-unstyled highlights mb-4">
                                <li><i class="bi bi-mortarboard"></i> Residency: St. Mary’s Medical Center</li>
                                <li><i class="bi bi-hospital"></i> Fellowship: Advanced Laparoscopy</li>
                                <li><i class="bi bi-award"></i> Publications: 14 peer-reviewed articles</li>
                            </ul>
                            <div class="d-flex flex-wrap gap-2">
                                <a href="#" class="btn btn-appointment"><i
                                        class="bi bi-calendar2-check me-1"></i> Book Appointment</a>
                                <a href="#" class="btn btn-soft"><i class="bi bi-file-earmark-text me-1"></i>
                                    View CV</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div><!-- End Single Doctor Profile -->

            <!-- Minimal Card / Compact View -->
            <div class="compact-view mt-5">
                <div class="row g-3">
                    <div class="col-6 col-md-4 col-lg-2" data-aos="fade-up" data-aos-delay="100">
                        <div class="minimal-card text-center">
                            <img src="assets/img/health/staff-2.webp" alt="Dr. Oliver Hayes" class="avatar img-fluid"
                                loading="lazy">
                            <div class="info">
                                <h4 class="mb-0">Dr. Oliver Hayes</h4>
                                <small> Pediatrics </small>
                            </div>
                        </div>
                    </div><!-- End Minimal Item -->
                    <div class="col-6 col-md-4 col-lg-2" data-aos="fade-up" data-aos-delay="150">
                        <div class="minimal-card text-center">
                            <img src="assets/img/health/staff-7.webp" alt="Dr. Noah Turner" class="avatar img-fluid"
                                loading="lazy">
                            <div class="info">
                                <h4 class="mb-0">Dr. Noah Turner</h4>
                                <small> Pediatrics </small>
                            </div>
                        </div>
                    </div><!-- End Minimal Item -->
                    <div class="col-6 col-md-4 col-lg-2" data-aos="fade-up" data-aos-delay="200">
                        <div class="minimal-card text-center">
                            <img src="assets/img/health/staff-6.webp" alt="Dr. Liam Carter" class="avatar img-fluid"
                                loading="lazy">
                            <div class="info">
                                <h4 class="mb-0">Dr. Liam Carter</h4>
                                <small> Orthopedics </small>
                            </div>
                        </div>
                    </div><!-- End Minimal Item -->
                    <div class="col-6 col-md-4 col-lg-2" data-aos="fade-up" data-aos-delay="250">
                        <div class="minimal-card text-center">
                            <img src="assets/img/health/staff-3.webp" alt="Dr. Amelia Brooks"
                                class="avatar img-fluid" loading="lazy">
                            <div class="info">
                                <h4 class="mb-0">Dr. Amelia Brooks</h4>
                                <small> Cardiology </small>
                            </div>
                        </div>
                    </div><!-- End Minimal Item -->
                    <div class="col-6 col-md-4 col-lg-2" data-aos="fade-up" data-aos-delay="300">
                        <div class="minimal-card text-center">
                            <img src="assets/img/health/staff-9.webp" alt="Dr. Harper Lane" class="avatar img-fluid"
                                loading="lazy">
                            <div class="info">
                                <h4 class="mb-0">Dr. Harper Lane</h4>
                                <small> Dermatology </small>
                            </div>
                        </div>
                    </div><!-- End Minimal Item -->
                    <div class="col-6 col-md-4 col-lg-2" data-aos="fade-up" data-aos-delay="350">
                        <div class="minimal-card text-center">
                            <img src="assets/img/health/staff-11.webp" alt="Dr. Lucas Grant" class="avatar img-fluid"
                                loading="lazy">
                            <div class="info">
                                <h4 class="mb-0">Dr. Lucas Grant</h4>
                                <small> Pulmonology </small>
                            </div>
                        </div>
                    </div><!-- End Minimal Item -->
                </div>
            </div><!-- End Minimal / Compact -->

            <!-- Doctor Profile with Tabs -->
            <div class="profile-tabs mt-5">
                <div class="row g-4">
                    <div class="col-lg-4" data-aos="fade-right" data-aos-delay="150">
                        <div class="tab-profile-card">
                            <img src="assets/img/health/staff-4.webp" class="img-fluid rounded-3"
                                alt="Dr. Henry James" loading="lazy">
                            <div class="pt-3">
                                <h3 class="mb-1">Dr. Henry James</h3>
                                <p class="mb-2">Oncology • MBBS, MD</p>
                                <div class="d-flex gap-2">
                                    <span class="badge cert">Board Certified</span>
                                    <span class="badge years">8 Years</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-8" data-aos="fade-left" data-aos-delay="200">
                        <ul class="nav nav-pills mb-3" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" data-bs-toggle="pill"
                                    data-bs-target="#health-doctors-3-tab-1" type="button" role="tab"
                                    aria-controls="health-doctors-3-tab-1" aria-selected="true">Bio</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" data-bs-toggle="pill"
                                    data-bs-target="#health-doctors-3-tab-2" type="button" role="tab"
                                    aria-controls="health-doctors-3-tab-2" aria-selected="false">Schedule</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" data-bs-toggle="pill"
                                    data-bs-target="#health-doctors-3-tab-3" type="button" role="tab"
                                    aria-controls="health-doctors-3-tab-3" aria-selected="false">Reviews</button>
                            </li>
                        </ul>
                        <div class="tab-content">
                            <div class="tab-pane fade show active" id="health-doctors-3-tab-1" role="tabpanel">
                                <p>Fugiat proident aliqua laboris, excepteur sunt ad pariatur occaecat. Veniam minim eu
                                    laboris, magna irure velit anim excepteur exercitation.</p>
                                <ul class="list-unstyled mb-0">
                                    <li><i class="bi bi-check2-circle me-1"></i> Special interest in immunotherapy</li>
                                    <li><i class="bi bi-check2-circle me-1"></i> Member of ASCO</li>
                                    <li><i class="bi bi-check2-circle me-1"></i> Community outreach programs</li>
                                </ul>
                            </div>
                            <div class="tab-pane fade" id="health-doctors-3-tab-2" role="tabpanel">
                                <div class="schedule-grid">
                                    <div class="row g-2">
                                        <div class="col-6">
                                            <div class="slot">
                                                <strong>Mon</strong>
                                                <span>9:00 AM - 1:00 PM</span>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="slot">
                                                <strong>Tue</strong>
                                                <span>12:00 PM - 6:00 PM</span>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="slot">
                                                <strong>Wed</strong>
                                                <span>9:00 AM - 3:00 PM</span>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="slot">
                                                <strong>Thu</strong>
                                                <span>10:00 AM - 4:00 PM</span>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="slot">
                                                <strong>Fri</strong>
                                                <span>Closed</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <a href="#" class="btn btn-appointment"><i
                                            class="bi bi-calendar-event me-1"></i> Reserve a Slot</a>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="health-doctors-3-tab-3" role="tabpanel">
                                <div class="review">
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="stars text-warning me-2">
                                            <i class="bi bi-star-fill"></i>
                                            <i class="bi bi-star-fill"></i>
                                            <i class="bi bi-star-fill"></i>
                                            <i class="bi bi-star-fill"></i>
                                            <i class="bi bi-star-half"></i>
                                        </div>
                                        <small>4.5/5 • 32 reviews</small>
                                    </div>
                                    <p class="mb-0">Id magna consequat minim in, lorem dolore fugiat. Officia irure
                                        ex anim, velit nulla cupidatat laboris enim.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div><!-- End Doctor Profile with Tabs -->

        </div>

    </section><!-- /Doctors Section -->
</x-app-layout>
