<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\User;
use App\Models\UserAppointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AppointmentController extends Controller
{
    // Endpoint to get all available slots
    public function getAvailableSlots(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'date' => 'date_format:Y-m-d',
            'service_id' => 'numeric|exists:services,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $serviceId = $request->input('service_id');
        $date = $request->input('date');

        $query = Appointment::select('id as slot_id','num_clients as client_capacity','start_time','service_id')->where('num_clients', '>', 0)->orderBy('start_time');

        if ($serviceId) {
            $query->where('service_id', $serviceId);
        }

        if ($date) {
            $query->whereDate('start_time', $date);
        }

        $appointments = $query->get();
        // Group the appointments by service if needed
        $availableSlots = $appointments->groupBy('service_id')->toArray();

        if (empty($availableSlots)) {
            return response()->json(['message' => 'No slots found for this service'], 404);
        }

        return response()->json(['data' => ['available_slots' => $availableSlots], 'message' => 'Available slots listed successfully'], 200);
    }

    // Endpoint to book available slots
    public function storeAppointment(Request $request)
    {
        // Validate the inputs
        $validator = Validator::make($request->all(), [
            'slots' => 'required|array|min:1',
            'slots.*.appointment_id' => 'required|exists:appointments,id',
            'slots.*.start_time' => 'required|date_format:Y-m-d H:i:s',
            'slots.*.email' => 'required|email',
            'slots.*.first_name' => 'required|string',
            'slots.*.last_name' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $slots = $request->input('slots');
        $bookedAppointments = [];

        foreach ($slots as $slot) {
            $email = $slot['email'];
            $name = $slot['first_name'] . ' ' . $slot['last_name'];

            $appointment = Appointment::where('id', $slot['appointment_id'])
                ->where('num_clients', '>', 0)
                ->where('start_time', $slot['start_time'])
                ->first();

            if (!$appointment) {
                return response()->json(['message' => 'Appointment not found'], 404);
            }

            $appointment->decrement('num_clients');

            //Create the booking and save the client details
            $user = User::firstOrCreate(['email' => $email], ['name' => $name]);

            UserAppointment::create([
                'appointment_id' => $slot['appointment_id'],
                'user_id' => $user->id,
                'start_time' => $slot['start_time'],
            ]);

            $bookedAppointments[] = [
                'appointment_id' => $slot['appointment_id'],
                'service_id' => $appointment['service_id'],
                'start_time' => $slot['start_time'],
                'email' => $email,
                'first_name' => $slot['first_name'],
                'last_name' => $slot['last_name'],
            ];
        }

        return response()->json(['data' => ['appointments' => $bookedAppointments], 'message' => 'Booked appointments listed successfully'], 200);
    }
}
