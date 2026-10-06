<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class TrainingController extends BaseApiController
{
    /**
     * List all HR training programs.
     */
    public function index(Request $request): JsonResponse
    {
        $trainings = DB::table('trainings')->orderBy('start_date', 'asc')->get();

        return response()->json([
            'status' => true,
            'count' => $trainings->count(),
            'data' => $trainings,
            'message' => 'Trainings retrieved successfully',
        ]);
    }

    /**
     * Store a new training program.
     */
    public function store(Request $request): JsonResponse
    {
        $title = $request->input('title', 'Corporate Training Program');
        $startDate = $request->input('start_date', date('Y-m-d'));
        $endDate = $request->input('end_date', date('Y-m-d', strtotime('+3 days')));
        $venue = $request->input('venue', 'Smart Corporate HQ');
        $duration = $request->input('duration', '3 Days');
        $trainer = $request->input('trainer', 'Lead Specialist');
        $message = $request->input('message');

        $id = DB::table('trainings')->insertGetId([
            'title' => $title,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'venue' => $venue,
            'duration' => $duration,
            'trainer' => $trainer,
            'status' => 'Upcoming',
            'enrolled_count' => 0,
            'message' => $message,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $created = DB::table('trainings')->where('id', $id)->first();

        return response()->json([
            'status' => true,
            'data' => $created,
            'message' => 'Training program created successfully',
        ], Response::HTTP_CREATED);
    }

    /**
     * Enroll an employee in a training program.
     */
    public function enroll(Request $request): JsonResponse
    {
        $trainingId = $request->input('training_id', 110);
        $empId = $request->input('employee_id', 468);
        $subject = $request->input('subject', 'Training Enrolment Confirmation');
        $body = $request->input('body', 'Staff nominated for professional development program.');

        DB::table('trainings')->where('id', $trainingId)->increment('enrolled_count');

        return response()->json([
            'status' => true,
            'data' => [
                'training_id' => $trainingId,
                'employee_id' => $empId,
                'subject' => $subject,
                'status' => 'Enrolled',
            ],
            'message' => 'Employee nomination recorded successfully',
        ]);
    }
}
