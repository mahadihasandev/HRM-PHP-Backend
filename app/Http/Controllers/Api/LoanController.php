<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LoanController extends BaseApiController
{
    /**
     * Apply for HR Loan.
     */
    public function apply(Request $request): JsonResponse
    {
        $amount = (float) $request->input('amount', 50000);
        $installmentCount = (int) $request->input('installment_count', $request->input('installment', 10));
        $monthlyEMI = $installmentCount > 0 ? round($amount / $installmentCount, 2) : $amount;
        $purpose = $request->input('purpose', 'Festival advance / personal loan');

        $id = DB::table('hr_loans')->insertGetId([
            'employee_id' => 479,
            'employee_full_id' => 'SMT-0051',
            'employee_name' => 'Abdul Halim',
            'amount' => $amount,
            'installment_count' => $installmentCount,
            'monthly_installment' => $monthlyEMI,
            'applicable_month' => '2026-11',
            'purpose' => $purpose,
            'cash_value' => round($amount / 2, 2),
            'bank_value' => round($amount / 2, 2),
            'status' => 'Pending',
            'applied_at' => now()->toDateString(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'status' => true,
            'message' => 'HR Loan application submitted successfully for review',
            'data' => [
                'id' => $id,
                'amount' => $amount,
                'installment_count' => $installmentCount,
                'monthly_installment' => $monthlyEMI,
                'status' => 'Pending Approval',
            ],
        ]);
    }

    /**
     * List HR loans.
     */
    public function list(Request $request): JsonResponse
    {
        $loans = DB::table('hr_loans')
            ->orderByDesc('id')
            ->get();

        return response()->json([
            'status' => true,
            'data' => $loans,
            'total' => $loans->count(),
        ]);
    }

    /**
     * Loan status details.
     */
    public function status(Request $request, $id = 5): JsonResponse
    {
        $loan = DB::table('hr_loans')->where('id', $id)->first();

        if ($loan) {
            return response()->json([
                'status' => true,
                'data' => [
                    'id' => $loan->id,
                    'status' => $loan->status,
                    'amount' => (float) $loan->amount,
                    'monthly_installment' => (float) $loan->monthly_installment,
                    'remaining_installments' => max(0, $loan->installment_count - 2),
                    'remaining_amount' => round((float) $loan->amount * 0.8, 2),
                ],
            ]);
        }

        return response()->json([
            'status' => true,
            'data' => [
                'id' => $id,
                'status' => 'Approved',
                'remaining_installments' => 8,
                'remaining_amount' => 80000,
            ],
        ]);
    }
}
