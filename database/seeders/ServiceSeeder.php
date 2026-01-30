<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Service;
use Illuminate\Support\Str;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        $services = [
            [
                'title' => 'General Consultation',
                'category' => 'General',
                'price' => 30,
                'icon' => 'fa-user-md',
                'features' => [
                    'Experienced Doctors',
                    'Quick Diagnosis',
                    'Affordable Price'
                ],
            ],
            [
                'title' => 'Dental Care',
                'category' => 'Dental',
                'price' => 50,
                'icon' => 'fa-tooth',
                'features' => [
                    'Teeth Cleaning',
                    'Cavity Treatment',
                    'Modern Dental Equipment'
                ],
            ],
            [
                'title' => 'Eye Checkup',
                'category' => 'Eye Care',
                'price' => 40,
                'icon' => 'fa-eye',
                'features' => [
                    'Vision Testing',
                    'Eye Pressure Check',
                    'Specialist Doctors'
                ],
            ],
            [
                'title' => 'Heart Specialist',
                'category' => 'Cardiology',
                'price' => 120,
                'icon' => 'fa-heartbeat',
                'features' => [
                    'ECG Test',
                    'Heart Health Screening',
                    'Expert Cardiologist'
                ],
            ],
            [
                'title' => 'Laboratory Test',
                'category' => 'Laboratory',
                'price' => 25,
                'icon' => 'fa-vials',
                'features' => [
                    'Blood Test',
                    'Urine Test',
                    'Accurate Results'
                ],
            ],
            [
                'title' => 'Emergency Service',
                'category' => 'Emergency',
                'price' => null,
                'icon' => 'fa-ambulance',
                'features' => [
                    '24/7 Service',
                    'Fast Response',
                    'Emergency Care Unit'
                ],
            ],
        ];

        foreach ($services as $service) {
            Service::create([
                'service_image' => 'services/default.png',
                'title' => $service['title'],
                'slug' => Str::slug($service['title']),
                'category' => $service['category'],
                'description' => $service['title'] . ' service provided by our clinic.',
                'icon' => $service['icon'],
                'features' => $service['features'], // json
                'price' => $service['price'],
                'status' => true,
            ]);
        }
    }
}
