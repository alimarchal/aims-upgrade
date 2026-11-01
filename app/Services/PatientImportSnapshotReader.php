<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Patient;
use App\Models\PatientTest;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class PatientImportSnapshotReader
{
    public function shouldUseArchivedData(): bool
    {
        return ! auth()->user()->hasRole('Super-Admin');
    }

    public function patient(Patient $patient): Patient
    {
        if (! $this->shouldUseArchivedData()) {
            return $patient;
        }

        $snapshot = DB::table('patients_import')
            ->where('id', $patient->id)
            ->whereNull('deleted_at')
            ->orderByDesc('archived_at')
            ->orderByDesc('import_batch_id')
            ->first();

        return $snapshot === null
            ? $patient
            : (new Patient)->newFromBuilder((array) $snapshot);
    }

    /**
     * @return Collection<int, Invoice>
     */
    public function invoicesForPatient(Patient $patient): Collection
    {
        if (! $this->shouldUseArchivedData()) {
            return $patient->invoices()->orderByDesc('created_at')->get();
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('invoices');
        $archivedRows = DB::table('invoices_improt as archived')
            ->select(array_map(fn (string $column): string => 'archived.'.$column, $columns))
            ->where('archived.patient_id', $patient->id)
            ->whereNull('archived.deleted_at')
            ->whereNotExists(function ($query): void {
                $query->selectRaw('1')
                    ->from('invoices_improt as newer_archive')
                    ->whereColumn('newer_archive.id', 'archived.id')
                    ->whereNull('newer_archive.deleted_at')
                    ->where(function ($query): void {
                        $query->whereColumn('newer_archive.archived_at', '>', 'archived.archived_at')
                            ->orWhere(function ($query): void {
                                $query->whereColumn('newer_archive.archived_at', '=', 'archived.archived_at')
                                    ->whereColumn('newer_archive.import_batch_id', '>', 'archived.import_batch_id');
                            });
                    });
            });
        $liveRows = DB::table('invoices as live')
            ->select(array_map(fn (string $column): string => 'live.'.$column, $columns))
            ->where('live.patient_id', $patient->id)
            ->whereNull('live.deleted_at')
            ->whereNotExists(function ($query): void {
                $query->selectRaw('1')
                    ->from('invoices_improt as archived')
                    ->whereColumn('archived.id', 'live.id')
                    ->whereNull('archived.deleted_at');
            });

        return Invoice::hydrate(
            $archivedRows->unionAll($liveRows)
                ->orderByDesc('created_at')
                ->get()
                ->map(fn (object $row): array => (array) $row)
                ->all(),
        );
    }

    public function invoice(Patient $patient, Invoice $invoice): Invoice
    {
        abort_unless((int) $invoice->patient_id === (int) $patient->id, 404);

        if ($this->shouldUseArchivedData()) {
            $snapshot = DB::table('invoices_improt')
                ->where('id', $invoice->id)
                ->where('patient_id', $patient->id)
                ->whereNull('deleted_at')
                ->orderByDesc('archived_at')
                ->orderByDesc('import_batch_id')
                ->first();

            if ($snapshot !== null) {
                $invoice = (new Invoice)->newFromBuilder((array) $snapshot);
            }
        }

        $invoice->setRelation('patient_test', $this->patientTestsForInvoice($invoice));
        $invoice->setRelation(
            'patient_test_latest',
            $invoice->patient_test->sortByDesc('id')->first(),
        );

        return $invoice;
    }

    /**
     * @return Collection<int, PatientTest>
     */
    private function patientTestsForInvoice(Invoice $invoice): Collection
    {
        if (! $this->shouldUseArchivedData()) {
            return $invoice->patient_test()->with('fee_type.feeCategory')->get();
        }

        $columns = DB::getSchemaBuilder()->getColumnListing('patient_tests');
        $archivedRows = DB::table('patient_tests_import as archived')
            ->select(array_map(fn (string $column): string => 'archived.'.$column, $columns))
            ->where('archived.invoice_id', $invoice->id)
            ->whereNull('archived.deleted_at')
            ->whereNotExists(function ($query): void {
                $query->selectRaw('1')
                    ->from('patient_tests_import as newer_archive')
                    ->whereColumn('newer_archive.id', 'archived.id')
                    ->whereNull('newer_archive.deleted_at')
                    ->where(function ($query): void {
                        $query->whereColumn('newer_archive.archived_at', '>', 'archived.archived_at')
                            ->orWhere(function ($query): void {
                                $query->whereColumn('newer_archive.archived_at', '=', 'archived.archived_at')
                                    ->whereColumn('newer_archive.import_batch_id', '>', 'archived.import_batch_id');
                            });
                    });
            });
        $liveRows = DB::table('patient_tests as live')
            ->select(array_map(fn (string $column): string => 'live.'.$column, $columns))
            ->where('live.invoice_id', $invoice->id)
            ->whereNull('live.deleted_at')
            ->whereNotExists(function ($query): void {
                $query->selectRaw('1')
                    ->from('patient_tests_import as archived')
                    ->whereColumn('archived.id', 'live.id')
                    ->whereNull('archived.deleted_at');
            });

        return PatientTest::hydrate(
            $archivedRows->unionAll($liveRows)
                ->get()
                ->map(fn (object $row): array => (array) $row)
                ->all(),
        )->load('fee_type.feeCategory');
    }
}
