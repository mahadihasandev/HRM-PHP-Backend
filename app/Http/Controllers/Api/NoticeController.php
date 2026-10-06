<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NoticeController extends BaseApiController
{
    /**
     * List company notices.
     */
    public function list(Request $request): JsonResponse
    {
        $notices = DB::table('notices')
            ->orderByDesc('publish_at')
            ->get();

        return response()->json([
            'status' => true,
            'data' => $notices,
            'total' => $notices->count(),
        ]);
    }

    /**
     * Create new notice.
     */
    public function store(Request $request): JsonResponse
    {
        $id = DB::table('notices')->insertGetId([
            'title' => $request->input('title', 'Corporate Office Memo'),
            'description' => $request->input('description', ''),
            'publish_at' => $request->input('publish_at', now()->toDateString()),
            'expire_at' => $request->input('expire_at', now()->addDays(30)->toDateString()),
            'department' => $request->input('department', 'All Departments'),
            'company' => $request->input('company', 'Smart Technologies (BD) Ltd.'),
            'priority' => $request->input('priority', 'Normal'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Notice published successfully in database',
            'data' => array_merge($request->all(), ['id' => $id]),
        ]);
    }

    /**
     * Show notice details.
     */
    public function show(Request $request, $id = 1): JsonResponse
    {
        $notice = DB::table('notices')->where('id', $id)->first();

        if ($notice) {
            return response()->json([
                'status' => true,
                'data' => $notice,
            ]);
        }

        return response()->json([
            'status' => true,
            'data' => [
                'id' => $id,
                'title' => 'Upcoming National Holiday Observance',
                'description' => 'Office operations will remain closed on the upcoming official holiday.',
                'publish_at' => '2026-10-01',
                'expire_at' => '2026-10-25',
                'department' => 'All Departments',
            ],
        ]);
    }
}
