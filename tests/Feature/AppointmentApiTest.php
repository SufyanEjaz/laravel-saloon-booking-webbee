<?php

namespace Tests\Feature;

use App\Models\Service;
use App\Models\Appointment;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class AppointmentApiTest extends TestCase
{
    use DatabaseMigrations;
    
    /**
     * Test getting available slots.
     */
    public function testGetAvailableSlots()
    {
        // Create test data
        $service = Service::factory()->create(['name' => 'Men Haircut']);
        $appointment1 = Appointment::factory()->create([
            'service_id' => $service->id,
            'start_time' => '2023-06-15 10:00:00',
            'num_clients' => 2,
        ]);
        $appointment2 = Appointment::factory()->create([
            'service_id' => $service->id,
            'start_time' => '2023-06-15 12:00:00',
            'num_clients' => 3,
        ]);
        
        // Mock the request
        $requestData = [
            'date' => '2023-06-15',
            'service_id' => $service->id,
        ];
        
        // Make the API call
        $response = $this->json('GET', '/api/available-slots', $requestData);
        
        // Assertions
        $response->assertStatus(200);
        $response->assertJson([
            'data' => [
                'available_slots' => [
                    $service->name => [
                        [
                            'slot_id' => $appointment1->id,
                            'start_time' => '2023-06-15 10:00:00',
                            'client_capacity' => 2,
                            'service_id' => $service->id,
                        ],
                        [
                            'slot_id' => $appointment2->id,
                            'start_time' => '2023-06-15 12:00:00',
                            'client_capacity' => 3,
                            'service_id' => $service->id,
                        ],
                    ],
                ],
            ],
        ]);
    }
    
    /**
     * Test storing an appointment.
     */
    public function testStoreAppointment()
    {
        // Create test data
        $service = Service::factory()->create(['name' => 'Men Haircut']);
        $appointment = Appointment::factory()->create([
            'service_id' => $service->id,
            'start_time' => '2023-06-15 10:00:00',
            'num_clients' => 2,
        ]);
        
        // Mock the request
        $requestData = [
            'slots' => [
                [
                    'appointment_id' => $appointment->id,
                    'start_time' => '2023-06-15 10:00:00',
                    'email' => 'test@example.com',
                    'first_name' => 'John',
                    'last_name' => 'Doe',
                ],
            ],
        ];
        
        // Make the API call
        $response = $this->json('POST', '/api/appointments', $requestData);
        
        // Assertions
        $response->assertStatus(200);
        $response->assertJson([
            'data' => [
                'appointments' => [
                    [
                        'appointment_id' => $appointment->id,
                        'service_name' => $appointment->service->name,
                        'start_time' => '2023-06-15 10:00:00',
                        'email' => 'test@example.com',
                        'first_name' => 'John',
                        'last_name' => 'Doe',
                    ],
                ],
            ],
        ]);
        
        // Verify that the appointment is updated in the database
        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'num_clients' => 1,
        ]);
        
        // Verify that the user and user appointment are created in the database
        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'name' => 'John Doe',
        ]);
        $this->assertDatabaseHas('user_appointments', [
            'appointment_id' => $appointment->id,
            'user_id' => User::where('email', 'test@example.com')->value('id'),
            'start_time' => '2023-06-15 10:00:00',
        ]);
    }
}
