<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PatientImportController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
        ]);
        $search = trim($validated['search'] ?? '');

        $patients = DB::table('patients_import')
            ->whereNull('deleted_at')
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    if (is_numeric($search)) {
                        $query->where('id', (int) $search);
                    }

                    $like = '%'.$search.'%';
                    $query->orWhereRaw("CONCAT_WS(' ', title, first_name, last_name) ILIKE ?", [$like])
                        ->orWhere('father_husband_name', 'ILIKE', $like)
                        ->orWhere('mobile', 'ILIKE', $like)
                        ->orWhere('phone', 'ILIKE', $like)
                        ->orWhere('cnic', 'ILIKE', $like)
                        ->orWhere('government_card_no', 'ILIKE', $like)
                        ->orWhere('import_batch_id', 'ILIKE', $like);
                });
            })
            ->orderByDesc('archived_at')
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        return view('patient.import-index', compact('patients', 'search'));
    }

    public function restore(Request $request, int $patientId, string $batchId): RedirectResponse
    {
        $request->validate([
            'confirm_restore' => ['accepted'],
        ]);

        $restored = DB::transaction(function () use ($patientId, $batchId): array {
            $patientSnapshot = DB::table('patients_import')
                ->where('id', $patientId)
                ->where('import_batch_id', $batchId)
                ->lockForUpdate()
                ->first();

            abort_if($patientSnapshot === null, 404, 'The selected patient snapshot was not found.');

            $invoiceSnapshots = DB::table('invoices_improt')
                ->where('patient_id', $patientId)
                ->where('import_batch_id', $batchId)
                ->lockForUpdate()
                ->get()
                ->all();
            $patientTestSnapshots = DB::table('patient_tests_import')
                ->where('patient_id', $patientId)
                ->where('import_batch_id', $batchId)
                ->lockForUpdate()
                ->get()
                ->all();

            $counts = [
                'patients' => $this->restoreRows('patients', [$patientSnapshot]),
                'invoices' => $this->restoreRows('invoices', $invoiceSnapshots),
                'patient_tests' => $this->restoreRows('patient_tests', $patientTestSnapshots),
            ];

            DB::table('patients_import')
                ->where('id', $patientId)
                ->where('import_batch_id', $batchId)
                ->delete();
            DB::table('invoices_improt')
                ->where('patient_id', $patientId)
                ->where('import_batch_id', $batchId)
                ->delete();
            DB::table('patient_tests_import')
                ->where('patient_id', $patientId)
                ->where('import_batch_id', $batchId)
                ->delete();

            return $counts;
        });

        return to_route('patient-import.index')
            ->with('success', sprintf(
                'Patient %d was restored exactly from this snapshot. Restored %d patient, %d invoice(s), and %d patient test(s); this snapshot was removed from all three archive tables.',
                $patientId,
                $restored['patients'],
                $restored['invoices'],
                $restored['patient_tests'],
            ));
    }

    /**
     * @param  array<int, object>  $archivedRows
     */
    private function restoreRows(string $table, array $archivedRows): int
    {
        foreach ($archivedRows as $archivedRow) {
            $record = (array) $archivedRow;
            unset($record['import_batch_id'], $record['archived_at']);

            $recordId = $record['id'];
            $existingRecord = DB::table($table)
                ->where('id', $recordId)
                ->lockForUpdate()
                ->exists();

            if ($existingRecord) {
                unset($record['id']);
                DB::table($table)->where('id', $recordId)->update($record);
            } else {
                DB::table($table)->insert($record);
            }
        }

        return count($archivedRows);
    }
}
