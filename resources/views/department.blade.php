<x-app-layout>
    <!-- Page Title -->
    <div class="page-title">
        <div class="heading">
            <div class="container">
                <div class="row d-flex justify-content-center text-center">
                    <div class="col-lg-8">
                        <h1 class="heading-title">Departments</h1>
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
                    <li class="current">Departments</li>
                </ol>
            </div>
        </nav>
    </div><!-- End Page Title -->

    <!-- Departments Section -->
    <section id="departments" class="departments section">

        <div class="container" data-aos="fade-up" data-aos-delay="100">

            <div class="row gy-4">
                @foreach ($departments as $department)
                    <!-- Department Item -->
                    <div class="col-lg-6" data-aos="zoom-in" data-aos-delay="100">
                        <div class="department-item">
                            <div class="department-content">
                                <div class="department-header">
                                    <div class="department-icon">
                                        <i class="{{ $department->icon }}"></i>
                                    </div>
                                    <div class="department-info">
                                        <h3> {{ $department->name }}</h3>
                                        <span class="department-category"> {{ $department->category }}</span>
                                    </div>
                                </div>
                                <p> {{ $department->description }}</p>
                                <div class="department-features">
                                    <span class="feature-badge"> 24/7 Emergency </span>
                                    <span class="feature-badge">Advanced Diagnostics</span>
                                </div>
                            </div>
                            <div class="department-image">
                                <img src="{{ asset('storage/' . $department->image) }}" alt="{{ $department->name }}"
                                    class="img-fluid">
                                <div class="department-overlay">
                                    <a href="#" class="department-link">
                                        <i class="fas fa-arrow-right"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div><!-- End Department Item -->
                @endforeach

            </div>

        </div>

    </section><!-- /Departments Section -->
</x-app-layout>
