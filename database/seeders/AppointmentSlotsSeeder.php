<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class AppointmentSlotsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $services = [
            [
                'name' => 'Men Haircut',
                'startTime' => '08:00',
                'endTime' => '20:00',
                'slotDuration' => 10, // minutes
                'cleanupBreakDuration' => 5, // minutes
                'maxClientsPerSlot' => 3,
                'duration' => 30, // minutes
            ],
            [
                'name' => 'Women Haircut',
                'startTime' => '08:00',
                'endTime' => '20:00',
                'slotDuration' => 60, // minutes
                'cleanupBreakDuration' => 10, // minutes
                'maxClientsPerSlot' => 3,
                'duration' => 60, // minutes
            ],
            // Add more services here if needed
        ];

        foreach ($services as $serviceData) {
            $service = Service::where('name', $serviceData['name'])->first();

            $startDate = Carbon::now()->startOfDay();
            $endDate = $startDate->copy()->addDays(7);

            $currentDate = $startDate->copy();
            while ($currentDate->lte($endDate)) {
                if (!$currentDate->isSunday() && !$currentDate->isSameDay(Carbon::now()->addDays(2))) {
                    if ($currentDate->isSaturday()) {
                        $this->createSlots($service, $currentDate, $serviceData['startTime'], $serviceData['endTime']);
                    } else {
                        $this->createSlots($service, $currentDate);
                    }
                }
                $currentDate->addDay();
            }
        }
    }

    private function createSlots($service, $date, $startTime = '08:00', $endTime = '20:00')
    {
        $openingTime = $date->copy()->setTimeFromTimeString($startTime);
        $closingTime = $date->copy()->setTimeFromTimeString($endTime);
        $lunchBreakStart = $date->copy()->setHour(12)->setMinute(0);
        $lunchBreakEnd = $date->copy()->setHour(13)->setMinute(0);
        $cleaningBreakStart = $date->copy()->setHour(15)->setMinute(0);
        $cleaningBreakEnd = $date->copy()->setHour(16)->setMinute(0);

        $slotDuration = $service->name === 'Men Haircut' ? 10 : 60; // minutes
        $cleanupBreakDuration = $service->name === 'Men Haircut' ? 5 : 10; // minutes
        $maxClientsPerSlot = 3;

        $currentTime = $openingTime->copy();
        while ($currentTime->lt($closingTime)) {
            if ($currentTime->between($lunchBreakStart, $lunchBreakEnd)) {
                $currentTime->setHour(13)->setMinute(0);
            } elseif ($currentTime->between($cleaningBreakStart, $cleaningBreakEnd)) {
                $currentTime->setHour(16)->setMinute(0);
            }
            Appointment::create([
                'service_id' => $service->id,
                'start_time' => $currentTime->toDateTimeString(),
                'duration' => $slotDuration,
                'num_clients' => $maxClientsPerSlot,
            ]);

            $currentTime->addMinutes($slotDuration + $cleanupBreakDuration);
        }
    }
}
