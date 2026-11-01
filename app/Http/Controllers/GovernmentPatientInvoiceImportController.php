<?php

namespace App\Http\Controllers;

use App\Exports\GovernmentPatientImportTemplateExport;
use App\Http\Requests\GovernmentPatientInvoiceImportRequest;
use App\Imports\GovernmentPatientExcelImport;
use App\Models\GovernmentDepartment;
use App\Services\GovernmentPatientInvoiceImport;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class GovernmentPatientInvoiceImportController extends Controller
{
    public function index(): View
    {
        $duplicateDepartments = GovernmentDepartment::query()
            ->orderBy('name')
            ->get(['id', 'name'])
            ->groupBy(fn (GovernmentDepartment $department): string => mb_strtolower(trim($department->name)))
            ->filter(fn ($departments): bool => $departments->count() > 1)
            ->values();

        return view('patient.government-import', [
            'defaults' => GovernmentPatientInvoiceImport::defaultOptions(),
            'duplicateDepartments' => $duplicateDepartments,
            'result' => session('government_patient_import_result'),
        ]);
    }

    public function help(): View
    {
        return view('patient.government-import-help');
    }

    public function template(): BinaryFileResponse|StreamedResponse
    {
        return Excel::download(
            new GovernmentPatientImportTemplateExport,
            'government_patient_import_template.xlsx',
        );
    }

    public function run(GovernmentPatientInvoiceImportRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $dryRun = (bool) ($validated['dry_run'] ?? false);
        $options = array_merge(GovernmentPatientInvoiceImport::defaultOptions(), $validated);
        unset($options['import_file'], $options['dry_run'], $options['confirm_execute']);

        $import = new GovernmentPatientExcelImport(
            new GovernmentPatientInvoiceImport($options, $dryRun),
        );

        try {
            $processImport = function () use ($import, $request): void {
                Excel::import($import, $request->file('import_file'));
            };

            if ($dryRun) {
                $processImport();
            } else {
                DB::transaction($processImport);
            }
        } catch (Throwable $exception) {
            Log::error('Government patient invoice import failed', [
                'error' => $exception->getMessage(),
                'user_id' => $request->user()?->id,
            ]);

            return back()
                ->withInput()
                ->withErrors(['import_file' => 'The workbook could not be processed. Check its format and try again.']);
        }

        $result = $import->result();
        $statusType = $result['counts']['errors'] > 0 || $result['counts']['selected'] === 0
            ? 'warning'
            : 'success';
        $statusMessage = $dryRun
            ? 'Analysis completed; no database changes were made.'
            : "{$result['counts']['updated']} patient(s) updated successfully.";

        if (! $dryRun && $result['archive']['batch_id'] !== null) {
            $archivedRows = $result['archive']['rows'];
            $statusMessage .= sprintf(
                ' Pre-import copies saved: %d %s, %d %s, and %d patient tests. Batch: %s.',
                $archivedRows['patients'],
                $archivedRows['patients'] === 1 ? 'patient' : 'patients',
                $archivedRows['invoices'],
                $archivedRows['invoices'] === 1 ? 'invoice' : 'invoices',
                $archivedRows['patient_tests'],
                $result['archive']['batch_id'],
            );
        }

        if ($result['counts']['errors'] > 0) {
            $statusMessage .= " {$result['counts']['errors']} Excel row error(s) need review.";
        }

        if ($result['counts']['selected'] === 0) {
            $statusMessage .= ' No eligible patients were selected.';
        }

        return to_route('patient.government-import')
            ->with('government_patient_import_result', $result)
            ->with('government_patient_import_status', [
                'type' => $statusType,
                'message' => $statusMessage,
            ]);
    }
}
