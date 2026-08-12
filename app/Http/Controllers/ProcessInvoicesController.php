<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\PatientTest;
use Illuminate\Support\Facades\DB;

class ProcessInvoicesController extends Controller
{
    public function process()
    {
        $userId = 35;
        $today = now()->toDateString();

        // Check if invoices have already been deleted today for this user
        $alreadyDeletedToday = Invoice::onlyTrashed()
            ->where('user_id', $userId)
            ->whereDate('deleted_at', $today)
            ->where('government_non_government', 0)
            ->exists();

        if ($alreadyDeletedToday) {
            return response()->json([
                'message' => 'Process already run today - invoices have already been deleted for today',
                'total_deduction' => 0,
            ]);
        }

        // Get all invoices for user 35 for today where government_non_government = 0
        $todayInvoices = Invoice::where('user_id', $userId)
            ->whereDate('created_at', $today)
            ->where('government_non_government', 0)
            ->get();

        if ($todayInvoices->isEmpty()) {
            return response()->json([
                'message' => 'No invoices found for user 35 today',
                'total_deduction' => 0,
            ]);
        }

        // Calculate total amount for today
        $totalAmount = $todayInvoices->sum('total_amount');

        $targetDeduction = 0;

        // Determine target deduction based on total amount
        if ($totalAmount >= 13000 && $totalAmount <= 20000) {
            $targetDeduction = 2000;
        } elseif ($totalAmount > 20000 && $totalAmount < 30000) {
            $targetDeduction = 3000;
        } elseif ($totalAmount > 30000) {
            $targetDeduction = 4000;
        } else {
            return response()->json([
                'message' => 'Total amount does not meet deduction criteria',
                'total_amount' => $totalAmount,
                'total_deduction' => 0,
            ]);
        }

        $actualDeduction = 0;
        $deletedInvoicesDetails = [];

        try {
            DB::beginTransaction();

            // Load patient_tests count for each invoice and sort by HIGHEST total_amount first (descending)
            $invoicesWithCounts = $todayInvoices->map(function ($invoice) {
                $invoice->patient_tests_count = PatientTest::where('invoice_id', $invoice->id)->count();

                return $invoice;
            })->sortByDesc('total_amount');

            foreach ($invoicesWithCounts as $invoice) {
                if ($actualDeduction >= $targetDeduction) {
                    break;
                }

                $remainingNeeded = $targetDeduction - $actualDeduction;

                // If adding this invoice doesn't exceed our target, soft delete it
                if ($invoice->total_amount <= $remainingNeeded) {
                    // Soft delete all related patient tests
                    PatientTest::where('invoice_id', $invoice->id)->delete();

                    // Soft delete the invoice
                    $invoice->delete();

                    $actualDeduction += $invoice->total_amount;
                    $deletedInvoicesDetails[] = [
                        'id' => $invoice->id,
                        'patient_tests_count' => $invoice->patient_tests_count,
                    ];
                }
            }

            // Format invoice display: #ID (X Tests)
            $invoiceDisplay = collect($deletedInvoicesDetails)
                ->map(fn ($inv) => "#{$inv['id']} ({$inv['patient_tests_count']} Tests)")
                ->implode(', ');

            // Calculate percentages
            $amount65Percent = round($actualDeduction * 0.65, 2);
            $amount35Percent = round($actualDeduction * 0.35, 2);

            // Build display message
            $displayMessage = "Invoices: {$invoiceDisplay}\n";
            $displayMessage .= "Deduction Amount: {$actualDeduction}\n";
            $displayMessage .= "65% Amount: {$amount65Percent}\n";
            $displayMessage .= "35% Amount of User ID 35: {$amount35Percent}";

            DB::commit();

            return response()->json([
                'message' => 'Deduction processed successfully',
                'display' => $displayMessage,
                'total_amount_today' => $totalAmount,
                'target_deduction' => $targetDeduction,
                'total_deduction' => $actualDeduction,
                'amount_65_percent' => $amount65Percent,
                'amount_35_percent' => $amount35Percent,
                'deleted_invoices_count' => count($deletedInvoicesDetails),
                // 'deleted_invoices_details' => $deletedInvoicesDetails,
                // 'deleted_invoice_ids' => collect($deletedInvoicesDetails)->pluck('id')->toArray(),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            \Log::error('Process invoices error: '.$e->getMessage(), [
                'exception' => $e,
                'user_id' => $userId,
                'date' => $today,
            ]);

            return response()->json([
                'message' => 'Error processing deduction',
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'total_deduction' => 0,
            ], 500);
        }
    }

    public function restore()
    {
        $userId = 35;
        $today = now()->toDateString();

        try {
            DB::beginTransaction();

            // Get soft deleted invoices for user 35 today
            $deletedInvoices = Invoice::onlyTrashed()
                ->where('user_id', $userId)
                ->whereDate('deleted_at', $today)
                ->get();

            if ($deletedInvoices->isEmpty()) {
                return response()->json([
                    'message' => 'No deleted invoices found for user 35 today',
                    'restored_count' => 0,
                ]);
            }

            $restoredCount = 0;
            $restoredAmount = 0;

            foreach ($deletedInvoices as $invoice) {
                // Restore related patient tests
                PatientTest::onlyTrashed()
                    ->where('invoice_id', $invoice->id)
                    ->restore();

                // Restore the invoice
                $invoice->restore();

                $restoredCount++;
                $restoredAmount += $invoice->total_amount;
            }

            DB::commit();

            return response()->json([
                'message' => 'Invoices restored successfully',
                'restored_count' => $restoredCount,
                'restored_amount' => $restoredAmount,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Error restoring invoices',
                'error' => $e->getMessage(),
                'restored_count' => 0,
            ], 500);
        }
    }
}
