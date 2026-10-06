<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FilterController extends BaseApiController
{
    /**
     * Companies list.
     */
    public function companies(Request $request): JsonResponse
    {
        return response()->json([
            'status' => true,
            'data' => [
                ['id' => 1, 'name' => 'Smart ERP Solutions Ltd.'],
                ['id' => 2, 'name' => 'Gulf Oil Bangladesh'],
                ['id' => 3, 'name' => 'RM Carpet Limited'],
                ['id' => 7, 'name' => 'Smart Trims & Packaging'],
            ],
        ]);
    }

    /**
     * Departments list.
     */
    public function departments(Request $request): JsonResponse
    {
        return response()->json([
            'status' => true,
            'data' => [
                ['id' => 1, 'name' => 'Human Resources'],
                ['id' => 2, 'name' => 'Engineering & Technology'],
                ['id' => 3, 'name' => 'Sales & Distribution'],
                ['id' => 4, 'name' => 'Finance & Accounts'],
                ['id' => 5, 'name' => 'Operations & Supply Chain'],
            ],
        ]);
    }

    /**
     * Designations list.
     */
    public function designations(Request $request): JsonResponse
    {
        return response()->json([
            'status' => true,
            'data' => [
                ['id' => 1, 'name' => 'Senior Field Sales Manager'],
                ['id' => 2, 'name' => 'Senior HR Specialist'],
                ['id' => 3, 'name' => 'Lead UI/UX Architect'],
                ['id' => 4, 'name' => 'Principal Backend Engineer'],
                ['id' => 5, 'name' => 'Financial Controller'],
            ],
        ]);
    }

    /**
     * Employee groups list.
     */
    public function employeeGroups(Request $request): JsonResponse
    {
        return response()->json([
            'status' => true,
            'data' => [
                ['id' => 1, 'name' => 'Management / Officer Grade'],
                ['id' => 2, 'name' => 'Field Executive Staff'],
                ['id' => 3, 'name' => 'Technical & Engineering'],
            ],
        ]);
    }
}
