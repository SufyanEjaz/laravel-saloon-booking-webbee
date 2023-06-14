<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class MenAppointmentsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $service = Service::where('name', 'Men Haircut')->first();
    
        $startDate = Carbon::now()->startOfDay();
        $endDate = $startDate->copy()->addDays(7);
    
        $currentDate = $startDate->copy();
        while ($currentDate->lte($endDate)) {
            if (!$currentDate->isSunday() && !$currentDate->isSameDay(Carbon::now()->addDays(2))) {
                if ($currentDate->isSaturday()) {
                    $this->createSlots($service, $currentDate, '10:00', '22:00');
                } else {
                    $this->createSlots($service, $currentDate);
                }
            }
            $currentDate->addDay();
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
    
        $slotDuration = 10; // minutes
        $cleanupBreakDuration = 5; // minutes
        $maxClientsPerSlot = 3;
    
        $currentTime = $openingTime->copy();
        $i = 0;
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
