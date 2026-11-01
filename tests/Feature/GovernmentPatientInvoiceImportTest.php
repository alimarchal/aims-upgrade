<?php

use App\Exports\GovernmentPatientImportTemplateExport;
use App\Models\FeeCategory;
use App\Models\FeeType;
use App\Models\GovernmentDepartment;
use App\Models\Invoice;
use App\Models\Patient;
use App\Models\PatientTest;
use App\Models\User;
use App\Services\GovernmentPatientInvoiceImport;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Permission::findOrCreate('view patients', 'sanctum');
    Permission::findOrCreate('edit patients', 'sanctum');
    Permission::findOrCreate('view patient import archive', 'sanctum');
    Role::findOrCreate('Super-Admin', 'sanctum');
    config()->set('government_import.access', true);

    $this->user = User::factory()->create();
    $this->user->assignRole('Super-Admin');
    $this->user->givePermissionTo(['view patients', 'edit patients', 'view patient import archive']);
    $this->actingAs($this->user);

    $this->department = GovernmentDepartment::create(['name' => 'HEALTH']);
    $this->feeCategory = FeeCategory::create(['name' => 'Test Category', 'type' => 'Test']);
    $this->feeType = FeeType::create([
        'fee_category_id' => $this->feeCategory->id,
        'type' => 'Laboratory Test',
        'amount' => 6000,
        'hif' => 3000,
        'status' => 'Normal',
    ]);

    $this->patient = Patient::factory()->create([
        'user_id' => $this->user->id,
        'title' => 'Mr.',
        'first_name' => 'Old',
        'last_name' => 'Patient',
        'government_non_gov' => false,
    ]);
    DB::table('patients')->where('id', $this->patient->id)->update([
        'created_at' => '2025-01-15 10:00:00',
        'updated_at' => '2025-01-15 10:00:00',
    ]);
    $this->patient->refresh();

    $this->invoice = Invoice::create([
        'user_id' => $this->user->id,
        'patient_id' => $this->patient->id,
        'total_amount' => 6000,
        'hif_amount' => 3000,
        'govt_amount' => 3000,
        'actual_total_amount' => 6000,
        'government_non_government' => false,
    ]);
    DB::table('invoices')->where('id', $this->invoice->id)->update([
        'created_at' => '2026-09-20 10:00:00',
        'updated_at' => '2026-09-20 10:00:00',
    ]);
    $this->invoice->refresh();

    $this->patientTest = PatientTest::create([
        'patient_id' => $this->patient->id,
        'fee_type_id' => $this->feeType->id,
        'invoice_id' => $this->invoice->id,
        'government_non_gov' => false,
        'total_amount' => 6000,
        'hif_amount' => 3000,
        'govt_amount' => 3000,
        'actual_total_amount' => 6000,
        'status' => 'Normal',
    ]);
    DB::table('patient_tests')->where('id', $this->patientTest->id)->update([
        'created_at' => '2026-09-20 10:00:00',
        'updated_at' => '2026-09-20 10:00:00',
    ]);
    $this->patientTest->refresh();
});

function governmentPatientImportFile(array $rows): UploadedFile
{
    $headers = [
        '#',
        'title',
        'first_name',
        'last_name',
        'relationship_title',
        'father_husband_name',
        'age',
        'address',
        'government_department_name',
        'designation',
        'government_card_no',
    ];
    $lines = [implode(',', $headers)];

    foreach ($rows as $row) {
        $lines[] = implode(',', array_map(
            fn (string $header): string => (string) ($row[$header] ?? ''),
            $headers,
        ));
    }

    return UploadedFile::fake()->createWithContent('patients.csv', implode("\n", $lines));
}

function governmentPatientImportOptions(): array
{
    return array_merge(GovernmentPatientInvoiceImport::DEFAULT_OPTIONS, [
        'import_file' => governmentPatientImportFile([]),
        'start_date' => '2026-09-18',
        'end_date' => '2026-09-29',
        'target_min' => 0,
        'target_max' => 305000,
        'min_invoice_amount' => 1,
        'min_user_total' => 1,
        'ignore_users' => 0,
        'ignore_patients' => 0,
        'ignore_fee_types' => 0,
        'dry_run' => 1,
    ]);
}

function createGovernmentImportCandidate(User $user, FeeType $feeType, string $firstName): Patient
{
    $patient = Patient::factory()->create([
        'user_id' => $user->id,
        'first_name' => $firstName,
        'government_non_gov' => false,
    ]);
    DB::table('patients')->where('id', $patient->id)->update([
        'created_at' => '2025-01-15 10:00:00',
        'updated_at' => '2025-01-15 10:00:00',
    ]);

    $invoice = Invoice::create([
        'user_id' => $user->id,
        'patient_id' => $patient->id,
        'total_amount' => 6000,
        'hif_amount' => 3000,
        'govt_amount' => 3000,
        'actual_total_amount' => 6000,
        'government_non_government' => false,
    ]);
    $patientTest = PatientTest::create([
        'patient_id' => $patient->id,
        'fee_type_id' => $feeType->id,
        'invoice_id' => $invoice->id,
        'government_non_gov' => false,
        'total_amount' => 6000,
        'hif_amount' => 3000,
        'govt_amount' => 3000,
        'actual_total_amount' => 6000,
        'status' => 'Normal',
    ]);

    DB::table('invoices')->where('id', $invoice->id)->update([
        'created_at' => '2026-09-20 10:00:00',
        'updated_at' => '2026-09-20 10:00:00',
    ]);
    DB::table('patient_tests')->where('id', $patientTest->id)->update([
        'created_at' => '2026-09-20 10:00:00',
        'updated_at' => '2026-09-20 10:00:00',
    ]);

    return $patient;
}

it('shows the import screen and downloads an Excel template', function () {
    $this->get(route('patient.government-import'))
        ->assertSuccessful()
        ->assertSee('Government Patient Excel Import')
        ->assertSee('whole database')
        ->assertSee('Desired lower bound for the randomly selected invoice total.')
        ->assertSee('Inclusive start date for patient-test creation time.')
        ->assertSee('Exclude invoices created by these users.')
        ->assertSee('This second confirmation is required for execution')
        ->assertSee('Excel column guide')
        ->assertSee('How it works')
        ->assertSee('Patient Import Archive')
        ->assertSee('The database age unit and DOB are derived automatically.')
        ->assertSee('value="'.now()->startOfMonth()->toDateString().'"', false)
        ->assertSee('value="'.now()->endOfMonth()->toDateString().'"', false)
        ->assertSee('value="13"', false);

    $this->get(route('patient.government-import.help'))
        ->assertSuccessful()
        ->assertSee('How the Government Patient Import Works')
        ->assertSee('Import lifecycle')
        ->assertSee('Archive selected originals')
        ->assertSee('Pre-import archive and history')
        ->assertSee('invoices_improt')
        ->assertSee('Archived patient search and access lifecycle')
        ->assertSee('Every non-Super-Admin user gets the newest archived patient version')
        ->assertSee('Every non-Super-Admin patient search, invoice history, invoice page, and print view')
        ->assertSee('Choose search source')
        ->assertSee('Super-Admin, or no snapshot')
        ->assertSee('/patient-import')
        ->assertSee('Government patient import lifecycle')
        ->assertSee('Execute + confirm')
        ->assertSee('Flash message and import report')
        ->assertSee('Analysis only')
        ->assertSee('Amount after import');

    expect((new GovernmentPatientImportTemplateExport)->headings())
        ->not->toContain('patient_id')
        ->not->toContain('years_months');

    foreach ([
        'patients' => 'patients_import',
        'invoices' => 'invoices_improt',
        'patient_tests' => 'patient_tests_import',
    ] as $sourceTable => $archiveTable) {
        expect(Schema::getColumnListing($archiveTable))
            ->toContain(...Schema::getColumnListing($sourceTable))
            ->toContain('import_batch_id', 'archived_at');
    }

    $this->get(route('patient.government-import.template'))
        ->assertDownload('government_patient_import_template.xlsx');
});

it('analyzes selected patients without changing database records', function () {
    $oldPatientTimestamp = $this->patient->updated_at;
    $oldInvoiceTimestamp = $this->invoice->updated_at;
    $oldPatientTestTimestamp = $this->patientTest->updated_at;

    $options = governmentPatientImportOptions();
    $options['import_file'] = governmentPatientImportFile([[
        '#' => 1,
        'title' => 'Mr.',
        'first_name' => 'Updated',
        'last_name' => '',
        'relationship_title' => 'S/O',
        'father_husband_name' => 'Parent Name',
        'age' => 31,
        'address' => 'New Address',
        'government_department_name' => 'HEALTH',
        'designation' => 'Officer',
        'government_card_no' => '00123',
    ]]);

    $response = $this->post(route('patient.government-import.run'), $options);

    $response->assertRedirect(route('patient.government-import'))
        ->assertSessionHas('government_patient_import_result.dry_run', true)
        ->assertSessionHas('government_patient_import_result.counts.selected', 1)
        ->assertSessionHas('government_patient_import_result.summary.picked_invoices_count', 1)
        ->assertSessionHas('government_patient_import_result.summary.picked_total_after_import', 6000)
        ->assertSessionHas('government_patient_import_result.assignments', fn (array $assignments): bool => count($assignments) === 1
            && $assignments[0]['amount_before'] === 6000.0
            && $assignments[0]['amount_after'] === 6000.0)
        ->assertSessionHas('government_patient_import_status.type', 'success')
        ->assertSessionHas('government_patient_import_status.message', 'Analysis completed; no database changes were made.')
        ->assertSessionHas('government_patient_import_result.archive', fn (array $archive): bool => $archive['batch_id'] === null)
        ->assertSessionHas('government_patient_import_result.archive.rows', [
            'patients' => 1,
            'invoices' => 1,
            'patient_tests' => 1,
        ])
        ->assertSessionHas('government_patient_import_result.impacts', fn (array $impacts): bool => count($impacts) === 6
            && $impacts[0]['table'] === 'patients'
            && $impacts[0]['rows'] === 1
            && in_array('years_months', $impacts[0]['columns'], true)
            && $impacts[1]['table'] === 'invoices'
            && $impacts[1]['columns'] === [
                'government_non_government',
                'government_department_id',
                'government_card_no',
                'total_amount',
                'hif_amount',
                'govt_amount',
            ]
            && $impacts[2]['table'] === 'patient_tests'
            && $impacts[2]['rows'] === 1
            && $impacts[3]['table'] === 'patients_import'
            && $impacts[4]['table'] === 'invoices_improt'
            && $impacts[5]['table'] === 'patient_tests_import');

    expect(DB::table('patients_import')->count())
        ->toBe(0)
        ->and(DB::table('invoices_improt')->count())->toBe(0)
        ->and(DB::table('patient_tests_import')->count())->toBe(0);

    expect($this->patient->fresh()->first_name)->toBe('Old')
        ->and($this->patient->fresh()->government_non_gov)->toBeFalse()
        ->and($this->patient->fresh()->updated_at->toDateTimeString())->toBe($oldPatientTimestamp->toDateTimeString())
        ->and((float) $this->invoice->fresh()->total_amount)->toBe(6000.0)
        ->and($this->invoice->fresh()->updated_at->toDateTimeString())->toBe($oldInvoiceTimestamp->toDateTimeString())
        ->and((float) $this->patientTest->fresh()->total_amount)->toBe(6000.0)
        ->and($this->patientTest->fresh()->updated_at->toDateTimeString())->toBe($oldPatientTestTimestamp->toDateTimeString());
});

it('updates the patient and selected invoice data after explicit confirmation', function () {
    $oldPatientTimestamp = $this->patient->updated_at;
    $oldInvoiceTimestamp = $this->invoice->updated_at;
    $oldPatientTestTimestamp = $this->patientTest->updated_at;
    $originalPatientRow = (array) DB::table('patients')->where('id', $this->patient->id)->first();
    $originalInvoiceRow = (array) DB::table('invoices')->where('id', $this->invoice->id)->first();
    $originalPatientTestRow = (array) DB::table('patient_tests')->where('id', $this->patientTest->id)->first();
    $options = governmentPatientImportOptions();
    $options['import_file'] = governmentPatientImportFile([[
        '#' => 1,
        'title' => 'Miss',
        'first_name' => 'Updated',
        'last_name' => '',
        'relationship_title' => 'D/O',
        'father_husband_name' => 'Parent Name',
        'age' => '31Y',
        'address' => 'New Address',
        'government_department_name' => 'health',
        'designation' => 'Officer',
        'government_card_no' => '00123',
    ]]);
    $options['dry_run'] = 0;
    $options['confirm_execute'] = 1;

    $response = $this->post(route('patient.government-import.run'), $options);

    $response
        ->assertRedirect(route('patient.government-import'))
        ->assertSessionHas('government_patient_import_result.dry_run', false)
        ->assertSessionHas('government_patient_import_result.counts.updated', 1)
        ->assertSessionHas('government_patient_import_result.summary.picked_total_amount', 6000)
        ->assertSessionHas('government_patient_import_result.summary.picked_total_after_import', 0)
        ->assertSessionHas('government_patient_import_result.archive.rows', [
            'patients' => 1,
            'invoices' => 1,
            'patient_tests' => 1,
        ])
        ->assertSessionHas('government_patient_import_result.assignments', fn (array $assignments): bool => count($assignments) === 1
            && $assignments[0]['amount_before'] === 6000.0
            && $assignments[0]['amount_after'] === 0.0)
        ->assertSessionHas('government_patient_import_status.type', 'success')
        ->assertSessionHas('government_patient_import_status.message', fn (string $message): bool => str_starts_with($message, '1 patient(s) updated successfully. Pre-import copies saved: 1 patient, 1 invoice, and 1 patient tests. Batch: '))
        ->assertSessionHas('government_patient_import_result.impacts.0.columns', [
            'government_non_gov',
            'government_department_id',
            'government_card_no',
            'mobile',
            'title',
            'first_name',
            'last_name',
            'relationship_title',
            'father_husband_name',
            'age',
            'years_months',
            'dob',
            'sex',
            'address',
            'designation',
        ]);

    $archive = $response->getSession()->get('government_patient_import_result.archive');
    $archiveBatchId = $archive['batch_id'];
    $archivedPatient = DB::table('patients_import')
        ->where('import_batch_id', $archiveBatchId)
        ->where('id', $this->patient->id)
        ->first();
    $archivedInvoice = DB::table('invoices_improt')
        ->where('import_batch_id', $archiveBatchId)
        ->where('id', $this->invoice->id)
        ->first();
    $archivedTest = DB::table('patient_tests_import')
        ->where('import_batch_id', $archiveBatchId)
        ->where('id', $this->patientTest->id)
        ->first();
    $archivedPatientRow = (array) $archivedPatient;
    $archivedInvoiceRow = (array) $archivedInvoice;
    $archivedTestRow = (array) $archivedTest;
    unset($archivedPatientRow['import_batch_id'], $archivedPatientRow['archived_at']);
    unset($archivedInvoiceRow['import_batch_id'], $archivedInvoiceRow['archived_at']);
    unset($archivedTestRow['import_batch_id'], $archivedTestRow['archived_at']);

    $otherPatientId = $this->patient->id + 100000;
    $otherInvoiceId = $this->invoice->id + 100000;
    $otherTestId = $this->patientTest->id + 100000;
    DB::table('patients_import')->insert(array_merge($originalPatientRow, [
        'id' => $otherPatientId,
        'import_batch_id' => $archiveBatchId,
        'archived_at' => now(),
    ]));
    DB::table('invoices_improt')->insert(array_merge($originalInvoiceRow, [
        'id' => $otherInvoiceId,
        'patient_id' => $otherPatientId,
        'import_batch_id' => $archiveBatchId,
        'archived_at' => now(),
    ]));
    DB::table('patient_tests_import')->insert(array_merge($originalPatientTestRow, [
        'id' => $otherTestId,
        'patient_id' => $otherPatientId,
        'invoice_id' => $otherInvoiceId,
        'import_batch_id' => $archiveBatchId,
        'archived_at' => now(),
    ]));

    expect($archiveBatchId)->toBeString()->not->toBeEmpty()
        ->and($archivedPatientRow)->toBe($originalPatientRow)
        ->and($archivedInvoiceRow)->toBe($originalInvoiceRow)
        ->and($archivedTestRow)->toBe($originalPatientTestRow)
        ->and($archivedPatient->first_name)->toBe('Old')
        ->and((bool) $archivedPatient->government_non_gov)->toBeFalse()
        ->and((float) $archivedInvoice->total_amount)->toBe(6000.0)
        ->and((bool) $archivedInvoice->government_non_government)->toBeFalse()
        ->and((float) $archivedTest->total_amount)->toBe(6000.0)
        ->and((bool) $archivedTest->government_non_gov)->toBeFalse();

    expect($this->patient->fresh())
        ->title->toBe('Miss')
        ->first_name->toBe('Updated')
        ->last_name->toBeNull()
        ->relationship_title->toBe('D/O')
        ->age->toBe(31)
        ->years_months->toBe('Year(s)')
        ->dob->toBe('1994-01-15')
        ->sex->toBeFalse()
        ->mobile->toBe('0300-1234567')
        ->government_non_gov->toBeTrue()
        ->government_department_id->toBe($this->department->id)
        ->designation->toBe('Officer')
        ->government_card_no->toBe('00123')
        ->updated_at->toDateTimeString()->toBe($oldPatientTimestamp->toDateTimeString());

    expect($this->invoice->fresh())
        ->government_non_government->toBeTrue()
        ->government_department_id->toBe((string) $this->department->id)
        ->government_card_no->toBe('00123')
        ->total_amount->toBe('0.00')
        ->hif_amount->toBe('0.00')
        ->govt_amount->toBe('0.00')
        ->updated_at->toDateTimeString()->toBe($oldInvoiceTimestamp->toDateTimeString());

    expect($this->patientTest->fresh())
        ->government_non_gov->toBeTrue()
        ->government_department_id->toBe((string) $this->department->id)
        ->government_card_no->toBe('00123')
        ->total_amount->toBe('0.00')
        ->hif_amount->toBe('0.00')
        ->govt_amount->toBe('0.00')
        ->updated_at->toDateTimeString()->toBe($oldPatientTestTimestamp->toDateTimeString());

    $this->get(route('patient.government-import'))
        ->assertSuccessful()
        ->assertSee('1 patient(s) updated successfully.')
        ->assertSee($archiveBatchId)
        ->assertSee('Amount before import')
        ->assertSee('Amount after import')
        ->assertSee('6,000.00')
        ->assertSee('0.00')
        ->assertSee('Database tables and columns affected')
        ->assertSee('Changes applied by this run.')
        ->assertSee('patients')
        ->assertSee('government_non_government')
        ->assertSee('years_months');

    $this->post(route('patient-import.restore', [$this->patient->id, $archiveBatchId]))
        ->assertSessionHasErrors('confirm_restore');

    $this->post(route('patient-import.restore', [$this->patient->id, $archiveBatchId]), [
        'confirm_restore' => '1',
    ])
        ->assertRedirect(route('patient-import.index'))
        ->assertSessionHas('success', fn (string $message): bool => str_contains($message, 'restored exactly'));

    expect((array) DB::table('patients')->where('id', $this->patient->id)->first())
        ->toBe($originalPatientRow)
        ->and((array) DB::table('invoices')->where('id', $this->invoice->id)->first())->toBe($originalInvoiceRow)
        ->and((array) DB::table('patient_tests')->where('id', $this->patientTest->id)->first())->toBe($originalPatientTestRow)
        ->and(DB::table('patients_import')->where('id', $this->patient->id)->where('import_batch_id', $archiveBatchId)->count())->toBe(0)
        ->and(DB::table('invoices_improt')->where('patient_id', $this->patient->id)->where('import_batch_id', $archiveBatchId)->count())->toBe(0)
        ->and(DB::table('patient_tests_import')->where('patient_id', $this->patient->id)->where('import_batch_id', $archiveBatchId)->count())->toBe(0)
        ->and(DB::table('patients_import')->where('id', $otherPatientId)->where('import_batch_id', $archiveBatchId)->exists())->toBeTrue()
        ->and(DB::table('invoices_improt')->where('id', $otherInvoiceId)->where('import_batch_id', $archiveBatchId)->exists())->toBeTrue()
        ->and(DB::table('patient_tests_import')->where('id', $otherTestId)->where('import_batch_id', $archiveBatchId)->exists())->toBeTrue();
});

it('calculates the date of birth from a month-based age', function () {
    $options = governmentPatientImportOptions();
    $options['import_file'] = governmentPatientImportFile([[
        '#' => 1,
        'title' => 'Mr.',
        'first_name' => 'Updated',
        'relationship_title' => 'S/O',
        'father_husband_name' => 'Parent Name',
        'age' => '6m',
        'address' => 'New Address',
        'government_department_name' => 'HEALTH',
        'designation' => 'Officer',
        'government_card_no' => '123',
    ]]);
    $options['dry_run'] = 0;
    $options['confirm_execute'] = 1;

    $this->post(route('patient.government-import.run'), $options)
        ->assertRedirect(route('patient.government-import'))
        ->assertSessionHas('government_patient_import_result.counts.updated', 1);

    expect($this->patient->fresh())
        ->age->toBe(6)
        ->years_months->toBe('Month(s)')
        ->dob->toBe('2024-07-15');
});

it('selects invoices globally and randomly assigns Excel profiles to selected patients', function () {
    $secondPatient = Patient::factory()->create([
        'user_id' => $this->user->id,
        'government_non_gov' => false,
    ]);
    DB::table('patients')->where('id', $secondPatient->id)->update([
        'created_at' => '2025-01-15 10:00:00',
        'updated_at' => '2025-01-15 10:00:00',
    ]);

    $secondInvoice = Invoice::create([
        'user_id' => $this->user->id,
        'patient_id' => $secondPatient->id,
        'total_amount' => 6000,
        'hif_amount' => 3000,
        'govt_amount' => 3000,
        'actual_total_amount' => 6000,
        'government_non_government' => false,
    ]);
    $secondPatientTest = PatientTest::create([
        'patient_id' => $secondPatient->id,
        'fee_type_id' => $this->feeType->id,
        'invoice_id' => $secondInvoice->id,
        'government_non_gov' => false,
        'total_amount' => 6000,
        'hif_amount' => 3000,
        'govt_amount' => 3000,
        'actual_total_amount' => 6000,
        'status' => 'Normal',
    ]);
    DB::table('invoices')->where('id', $secondInvoice->id)->update([
        'created_at' => '2026-09-20 10:00:00',
        'updated_at' => '2026-09-20 10:00:00',
    ]);
    DB::table('patient_tests')->where('id', $secondPatientTest->id)->update([
        'created_at' => '2026-09-20 10:00:00',
        'updated_at' => '2026-09-20 10:00:00',
    ]);

    $makeRow = fn (int $serial, string $firstName): array => [
        '#' => $serial,
        'title' => 'Mr.',
        'first_name' => $firstName,
        'relationship_title' => 'S/O',
        'father_husband_name' => 'Parent Name',
        'age' => 31,
        'address' => 'New Address',
        'government_department_name' => 'HEALTH',
        'designation' => 'Officer',
        'government_card_no' => '123',
    ];
    $options = governmentPatientImportOptions();
    $options['import_file'] = governmentPatientImportFile([
        $makeRow(1, 'Candidate A'),
        $makeRow(2, 'Candidate B'),
    ]);
    $options['target_max'] = 6000;
    $options['dry_run'] = 0;
    $options['confirm_execute'] = 1;

    $this->post(route('patient.government-import.run'), $options)
        ->assertRedirect(route('patient.government-import'))
        ->assertSessionHas('government_patient_import_result.counts.selected', 1)
        ->assertSessionHas('government_patient_import_result.counts.profiles_assigned', 1)
        ->assertSessionHas('government_patient_import_result.counts.skipped', 1)
        ->assertSessionHas('government_patient_import_result.summary.picked_invoices_count', 1);

    expect(Invoice::query()->where('government_non_government', true)->count())->toBe(1)
        ->and(Invoice::query()->where('government_non_government', false)->count())->toBe(1)
        ->and(Patient::query()->where('government_non_gov', true)->count())->toBe(1)
        ->and(PatientTest::query()->where('government_non_gov', false)->count())->toBe(1)
        ->and(Patient::query()->whereIn('id', [$this->patient->id, $secondPatient->id])->where('government_non_gov', true)->value('first_name'))
        ->toBeIn(['Candidate A', 'Candidate B']);
});

it('uses query-generated assignments for selected patients without profiles', function () {
    $secondPatient = Patient::factory()->create([
        'user_id' => $this->user->id,
        'first_name' => 'Query Only',
        'government_non_gov' => false,
    ]);
    DB::table('patients')->where('id', $secondPatient->id)->update([
        'created_at' => '2025-01-15 10:00:00',
        'updated_at' => '2025-01-15 10:00:00',
    ]);

    $secondInvoice = Invoice::create([
        'user_id' => $this->user->id,
        'patient_id' => $secondPatient->id,
        'total_amount' => 6000,
        'hif_amount' => 3000,
        'govt_amount' => 3000,
        'actual_total_amount' => 6000,
        'government_non_government' => false,
    ]);
    $secondPatientTest = PatientTest::create([
        'patient_id' => $secondPatient->id,
        'fee_type_id' => $this->feeType->id,
        'invoice_id' => $secondInvoice->id,
        'government_non_gov' => false,
        'total_amount' => 6000,
        'hif_amount' => 3000,
        'govt_amount' => 3000,
        'actual_total_amount' => 6000,
        'status' => 'Normal',
    ]);
    DB::table('invoices')->where('id', $secondInvoice->id)->update([
        'created_at' => '2026-09-20 10:00:00',
        'updated_at' => '2026-09-20 10:00:00',
    ]);
    DB::table('patient_tests')->where('id', $secondPatientTest->id)->update([
        'created_at' => '2026-09-20 10:00:00',
        'updated_at' => '2026-09-20 10:00:00',
    ]);

    $existingPatientNames = Patient::query()
        ->whereIn('id', [$this->patient->id, $secondPatient->id])
        ->pluck('first_name', 'id');
    $options = governmentPatientImportOptions();
    $options['import_file'] = governmentPatientImportFile([[
        'title' => 'Miss',
        'first_name' => 'Excel Profile',
        'relationship_title' => 'D/O',
        'father_husband_name' => 'Parent Name',
        'age' => 31,
        'address' => 'New Address',
        'government_department_name' => 'HEALTH',
        'designation' => 'Officer',
        'government_card_no' => '00123',
    ]]);
    $options['target_max'] = 12000;
    $options['dry_run'] = 0;
    $options['confirm_execute'] = 1;

    $response = $this->post(route('patient.government-import.run'), $options);

    $response
        ->assertRedirect(route('patient.government-import'))
        ->assertSessionHas('government_patient_import_result.counts.selected', 2)
        ->assertSessionHas('government_patient_import_result.counts.profiles_assigned', 1)
        ->assertSessionHas('government_patient_import_result.counts.query_only', 1)
        ->assertSessionHas('government_patient_import_result.assignments', fn (array $assignments): bool => count($assignments) === 2
            && collect($assignments)->pluck('patient_id')->unique()->count() === 2
            && collect($assignments)->where('assignment_type', 'excel')->count() === 1
            && collect($assignments)->where('assignment_type', 'query')->count() === 1
            && collect($assignments)->pluck('card_no')->unique()->count() === 2
            && collect($assignments)->firstWhere('assignment_type', 'excel')['excel_name'] === 'Excel Profile');

    $assignments = $response->getSession()->get('government_patient_import_result')['assignments'];
    $excelPatientId = collect($assignments)->firstWhere('assignment_type', 'excel')['patient_id'];
    $queryPatientId = collect($assignments)->firstWhere('assignment_type', 'query')['patient_id'];

    expect(Patient::query()->whereIn('id', [$this->patient->id, $secondPatient->id])->where('government_non_gov', true)->count())->toBe(2)
        ->and(DB::table('patients')->where('id', $excelPatientId)->value('first_name'))->toBe('Excel Profile')
        ->and(DB::table('patients')->where('id', $queryPatientId)->value('first_name'))->toBe($existingPatientNames->get($queryPatientId))
        ->and(DB::table('patients')->whereIn('id', [$excelPatientId, $queryPatientId])->pluck('mobile')->unique()->all())->toBe(['0300-1234567']);

    $this->get(route('patient.government-import'))
        ->assertSuccessful()
        ->assertSee('Selected patients and assignments')
        ->assertSee('Excel Profile')
        ->assertSee((string) $excelPatientId);
});

it('reuses Excel profiles evenly across all patients when the option is checked', function () {
    $secondPatient = createGovernmentImportCandidate($this->user, $this->feeType, 'Second');
    $thirdPatient = createGovernmentImportCandidate($this->user, $this->feeType, 'Third');
    $options = governmentPatientImportOptions();
    $options['import_file'] = governmentPatientImportFile([
        [
            'title' => 'Mr.',
            'first_name' => 'Profile One',
            'relationship_title' => 'S/O',
            'father_husband_name' => 'Parent One',
            'age' => 31,
            'address' => 'Address One',
            'government_department_name' => 'HEALTH',
            'designation' => 'Officer',
            'government_card_no' => '00123',
        ],
        [
            'title' => 'Miss',
            'first_name' => 'Profile Two',
            'relationship_title' => 'D/O',
            'father_husband_name' => 'Parent Two',
            'age' => 24,
            'address' => 'Address Two',
            'government_department_name' => 'HEALTH',
            'designation' => 'Clerk',
            'government_card_no' => '00456',
        ],
    ]);
    $options['target_max'] = 18000;
    $options['reuse_excel_profiles'] = 1;
    $options['dry_run'] = 0;
    $options['confirm_execute'] = 1;

    $response = $this->post(route('patient.government-import.run'), $options);
    $response
        ->assertRedirect(route('patient.government-import'))
        ->assertSessionHas('government_patient_import_result.counts.selected', 3)
        ->assertSessionHas('government_patient_import_result.counts.rows', 2)
        ->assertSessionHas('government_patient_import_result.counts.profiles_assigned', 3)
        ->assertSessionHas('government_patient_import_result.counts.query_only', 0)
        ->assertSessionHas('government_patient_import_result.counts.skipped', 0);

    $assignments = $response->getSession()->get('government_patient_import_result')['assignments'];
    $profileUseCounts = array_values(array_count_values(array_column($assignments, 'profile_row')));
    sort($profileUseCounts);
    $assignedPatientIds = array_column($assignments, 'patient_id');

    expect($profileUseCounts)->toBe([1, 2])
        ->and(array_unique($assignedPatientIds))->toHaveCount(3)
        ->and(Patient::query()->whereIn('id', $assignedPatientIds)->where('government_non_gov', true)->count())->toBe(3)
        ->and(Patient::query()->whereIn('id', $assignedPatientIds)->whereIn('first_name', ['Profile One', 'Profile Two'])->count())->toBe(3)
        ->and(array_count_values(array_column($assignments, 'card_no')))->toHaveCount(2);
});

it('keeps multiple pre-import snapshots for a selected record', function () {
    $runImport = function (string $name, string $cardNumber) {
        $options = governmentPatientImportOptions();
        $options['import_file'] = governmentPatientImportFile([[
            'title' => 'Mr.',
            'first_name' => $name,
            'relationship_title' => 'S/O',
            'father_husband_name' => 'Parent',
            'age' => 31,
            'address' => 'Address',
            'government_department_name' => 'HEALTH',
            'designation' => 'Officer',
            'government_card_no' => $cardNumber,
        ]]);
        $options['dry_run'] = 0;
        $options['confirm_execute'] = 1;

        return $this->post(route('patient.government-import.run'), $options);
    };

    $firstResponse = $runImport('First version', '123');
    $firstBatchId = $firstResponse->getSession()->get('government_patient_import_result.archive.batch_id');

    DB::table('patients')->where('id', $this->patient->id)->update([
        'government_non_gov' => false,
    ]);
    DB::table('invoices')->where('id', $this->invoice->id)->update([
        'government_non_government' => false,
        'total_amount' => 6000,
        'hif_amount' => 3000,
        'govt_amount' => 3000,
    ]);
    DB::table('patient_tests')->where('id', $this->patientTest->id)->update([
        'government_non_gov' => false,
        'total_amount' => 6000,
        'hif_amount' => 3000,
        'govt_amount' => 3000,
    ]);

    $secondResponse = $runImport('Second version', '456');
    $secondBatchId = $secondResponse->getSession()->get('government_patient_import_result.archive.batch_id');
    $archivedPatientVersions = DB::table('patients_import')
        ->where('id', $this->patient->id)
        ->whereIn('import_batch_id', [$firstBatchId, $secondBatchId])
        ->orderBy('import_batch_id')
        ->pluck('first_name')
        ->all();

    expect($firstBatchId)->not->toBe($secondBatchId)
        ->and($archivedPatientVersions)->toHaveCount(2)
        ->and($archivedPatientVersions)->toContain('Old', 'First version')
        ->and(DB::table('invoices_improt')->where('id', $this->invoice->id)->whereIn('import_batch_id', [$firstBatchId, $secondBatchId])->count())->toBe(2)
        ->and(DB::table('patient_tests_import')->where('id', $this->patientTest->id)->whereIn('import_batch_id', [$firstBatchId, $secondBatchId])->count())->toBe(2);
});

it('uses the latest archive in front desk search and falls back to live patient data', function () {
    $livePatient = Patient::factory()->create([
        'user_id' => $this->user->id,
        'first_name' => 'Live Fallback',
    ]);

    $patientBeforeImport = (array) DB::table('patients')->where('id', $this->patient->id)->first();
    $archiveBase = array_merge($patientBeforeImport, [
        'import_batch_id' => (string) Str::uuid(),
        'archived_at' => '2026-10-01 10:00:00+00',
        'first_name' => 'Archived Older',
    ]);
    DB::table('patients_import')->insert($archiveBase);
    DB::table('patients_import')->insert(array_merge($archiveBase, [
        'import_batch_id' => (string) Str::uuid(),
        'archived_at' => '2026-10-02 10:00:00+00',
        'first_name' => 'Archived Latest',
    ]));

    $frontDeskRole = Role::findOrCreate('Front Desk/Receptionist', 'sanctum');
    $frontDeskRole->syncPermissions(['view patients']);
    $frontDesk = User::factory()->create();
    $frontDesk->assignRole($frontDeskRole);
    $this->actingAs($frontDesk);

    $this->get(route('patient.index', ['filter' => ['first_name' => 'Archived']]))
        ->assertSuccessful()
        ->assertSee('Archived Latest')
        ->assertDontSee('Archived Older')
        ->assertDontSee('Patient Import Archive')
        ->assertDontSee('Old');

    $this->get(route('patient.index', ['filter' => ['first_name' => 'Live Fallback']]))
        ->assertSuccessful()
        ->assertSee('Live Fallback');

    $administratorRole = Role::findOrCreate('Administrator', 'sanctum');
    $administratorRole->syncPermissions(['view patients']);
    $administrator = User::factory()->create();
    $administrator->assignRole($administratorRole);
    $this->actingAs($administrator);

    $this->get(route('patient.index', ['filter' => ['first_name' => 'Archived']]))
        ->assertSuccessful()
        ->assertSee('Archived Latest')
        ->assertDontSee('Archived search result');

    $this->get(route('patient.index', ['filter' => ['first_name' => 'Archived']]))
        ->assertSuccessful()
        ->assertSee('Archived Latest')
        ->assertDontSee('Patient Import Archive');

    $this->actingAs($this->user)
        ->get(route('patient.index', ['filter' => ['first_name' => 'Old']]))
        ->assertSuccessful()
        ->assertSee('Old')
        ->assertSee('Patient Import Archive')
        ->assertDontSee('Archived Latest');
});

it('shows archived patient and invoice data to non-super-admin users across search and invoice views', function () {
    $batchId = (string) Str::uuid();
    $patientSnapshot = (array) DB::table('patients')->where('id', $this->patient->id)->first();
    $invoiceSnapshot = (array) DB::table('invoices')->where('id', $this->invoice->id)->first();
    $patientTestSnapshot = (array) DB::table('patient_tests')->where('id', $this->patientTest->id)->first();

    DB::table('patients_import')->insert(array_merge($patientSnapshot, [
        'first_name' => 'Archived',
        'last_name' => 'Patient Name',
        'mobile' => '0300-1234567',
        'import_batch_id' => $batchId,
        'archived_at' => now(),
    ]));
    DB::table('invoices_improt')->insert(array_merge($invoiceSnapshot, [
        'import_batch_id' => $batchId,
        'archived_at' => now(),
    ]));
    DB::table('patient_tests_import')->insert(array_merge($patientTestSnapshot, [
        'import_batch_id' => $batchId,
        'archived_at' => now(),
    ]));

    DB::table('patients')->where('id', $this->patient->id)->update([
        'first_name' => 'Current',
        'last_name' => 'Live Name',
        'mobile' => '0999-0000000',
    ]);
    DB::table('invoices')->where('id', $this->invoice->id)->update(['total_amount' => 0]);
    DB::table('patient_tests')->where('id', $this->patientTest->id)->update(['total_amount' => 0]);

    $administratorRole = Role::findOrCreate('Administrator', 'sanctum');
    $administratorRole->syncPermissions(['view patients']);
    $administrator = User::factory()->create();
    $administrator->assignRole($administratorRole);
    $this->actingAs($administrator);

    $this->get(route('patient.index', ['filter' => ['first_name' => 'Archived']]))
        ->assertSuccessful()
        ->assertSee('Archived')
        ->assertDontSee('Archived search result');

    $this->get(route('patient.issued-invoices', $this->patient->id))
        ->assertSuccessful()
        ->assertSee('Archived')
        ->assertSee('6000');

    $this->get(route('patient.patient_invoice', [$this->patient->id, $this->invoice->id]))
        ->assertSuccessful()
        ->assertSee('Archived')
        ->assertSee('0300-1234567')
        ->assertSee('Total Amount')
        ->assertSee('6,000.00');

    $this->get(route('patient.patient_invoice_thermal_print', [$this->patient->id, $this->invoice->id]))
        ->assertSuccessful()
        ->assertSee('Archived')
        ->assertSee('0300-1234567')
        ->assertSee('6,000.00');

    $this->actingAs($this->user);

    $this->get(route('patient.index', ['filter' => ['first_name' => 'Current']]))
        ->assertSuccessful()
        ->assertSee('Current')
        ->assertDontSee('Archived');

    $this->get(route('patient.issued-invoices', $this->patient->id))
        ->assertSuccessful()
        ->assertSee('Current')
        ->assertSee('0');

    $this->get(route('patient.patient_invoice', [$this->patient->id, $this->invoice->id]))
        ->assertSuccessful()
        ->assertSee('Current')
        ->assertSee('0999-0000000')
        ->assertSee('0.00')
        ->assertDontSee('Archived');

    $this->get(route('patient.patient_invoice_thermal_print', [$this->patient->id, $this->invoice->id]))
        ->assertSuccessful()
        ->assertSee('Current')
        ->assertSee('0999-0000000')
        ->assertSee('0.00')
        ->assertDontSee('Archived');
});

it('allows archive users to search and restore, and blocks users without archive permission', function () {
    $patientBeforeImport = (array) DB::table('patients')->where('id', $this->patient->id)->first();
    DB::table('patients_import')->insert(array_merge($patientBeforeImport, [
        'import_batch_id' => (string) Str::uuid(),
        'archived_at' => '2026-10-02 10:00:00+00',
        'first_name' => 'Archive Search Target',
    ]));

    $this->get(route('patient-import.index'))
        ->assertSuccessful()
        ->assertSee('Patient Import Archive')
        ->assertSee('Archive Search Target')
        ->assertSee('Archived patient versions')
        ->assertSee('Restore this version');

    $this->get(route('patient-import.index', ['search' => 'Archive Search']))
        ->assertSuccessful()
        ->assertSee('Archive Search Target');

    $regularUser = User::factory()->create();
    $regularUser->givePermissionTo(['view patients', 'edit patients']);
    $regularUser->assignRole(Role::findOrCreate('Front Desk/Receptionist', 'sanctum'));

    $this->actingAs($regularUser)
        ->get(route('patient.index'))
        ->assertSuccessful()
        ->assertDontSee('Patient Import Archive');

    $this->get(route('patient-import.index'))
        ->assertForbidden();

    $administratorRole = Role::findOrCreate('Administrator', 'sanctum');
    $administratorRole->syncPermissions(['view patients']);
    $administrator = User::factory()->create();
    $administrator->assignRole($administratorRole);

    $this->actingAs($administrator)
        ->get(route('patient.index'))
        ->assertSuccessful()
        ->assertDontSee('Patient Import Archive');

    $this->get(route('patient-import.index'))
        ->assertForbidden();

    $permissionedUser = User::factory()->create();
    $permissionedUser->givePermissionTo('view patient import archive');

    $this->actingAs($permissionedUser)
        ->get(route('patient-import.index'))
        ->assertSuccessful()
        ->assertSee('Archive Search Target');
});

it('requires IMPORT_ACCESS for government import routes even for super admins', function () {
    config()->set('government_import.access', false);

    $this->get(route('patient.index'))
        ->assertSuccessful()
        ->assertDontSee('Government Excel Import')
        ->assertSee('Patient Import Archive');

    $this->get(route('patient.government-import'))->assertForbidden();
    $this->get(route('patient.government-import.help'))->assertForbidden();
    $this->get(route('patient.government-import.template'))->assertForbidden();

    $options = governmentPatientImportOptions();
    $options['import_file'] = governmentPatientImportFile([]);
    $this->post(route('patient.government-import.run'), $options)->assertForbidden();

    config()->set('government_import.access', true);

    $this->get(route('patient.index'))
        ->assertSuccessful()
        ->assertSee('Government Excel Import');
});

it('restricts the import to super admins and requires execution confirmation', function () {
    $regularUser = User::factory()->create();
    $regularUser->givePermissionTo(['view patients', 'edit patients']);

    $this->actingAs($regularUser);
    $this->get(route('patient.index'))->assertSuccessful()->assertDontSee('Government Excel Import');
    $this->get(route('patient.government-import'))->assertForbidden();
    $this->get(route('patient.government-import.help'))->assertForbidden();
    $this->get(route('patient.government-import.template'))->assertForbidden();

    $options = governmentPatientImportOptions();
    $options['import_file'] = governmentPatientImportFile([]);
    $this->post(route('patient.government-import.run'), $options)->assertForbidden();

    $this->actingAs($this->user);
    $this->get(route('patient.index'))
        ->assertSuccessful()
        ->assertSee('Government Excel Import')
        ->assertSee('Patient Import Archive');
    $options['dry_run'] = 0;

    $this->post(route('patient.government-import.run'), $options)
        ->assertSessionHasErrors('confirm_execute');
});

it('reports unknown and ambiguous departments without making query-only updates', function () {
    GovernmentDepartment::create(['name' => 'CLINIC']);
    GovernmentDepartment::create(['name' => 'CLINIC']);

    $options = governmentPatientImportOptions();
    $options['import_file'] = governmentPatientImportFile([
        [
            '#' => 1,
            'title' => 'Mr.',
            'first_name' => 'Unknown Department',
            'relationship_title' => 'S/O',
            'father_husband_name' => 'Parent',
            'age' => 20,
            'address' => 'Address',
            'government_department_name' => 'MISSING',
            'designation' => 'Officer',
            'government_card_no' => '123',
        ],
        [
            '#' => 2,
            'title' => 'Mr.',
            'first_name' => 'Ambiguous',
            'relationship_title' => 'S/O',
            'father_husband_name' => 'Parent',
            'age' => 20,
            'address' => 'Address',
            'government_department_name' => 'CLINIC',
            'designation' => 'Officer',
            'government_card_no' => '123',
        ],
    ]);

    $this->post(route('patient.government-import.run'), $options)
        ->assertRedirect(route('patient.government-import'))
        ->assertSessionHas('government_patient_import_result.counts.selected', 0)
        ->assertSessionHas('government_patient_import_result.counts.errors', 2)
        ->assertSessionHas('government_patient_import_status.type', 'warning')
        ->assertSessionHas('government_patient_import_status.message', 'Analysis completed; no database changes were made. 2 Excel row error(s) need review. No eligible patients were selected.')
        ->assertSessionHas('government_patient_import_result.summary.picked_invoices_count', 0);

    expect($this->patient->fresh()->first_name)->toBe('Old');
});
