<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GovernmentPatientInvoiceImport
{
    public const DEFAULT_OPTIONS = [
        'target_min' => 200000,
        'target_max' => 305000,
        'min_invoice_amount' => 300,
        'min_user_total' => 5000,
        'include_driving' => false,
        'include_admission' => false,
        'include_excluded_fee_types' => false,
        'driving_pick_count' => 10,
        'ignore_users' => true,
        'ignore_user_ids' => '13',
        'ignore_patients' => true,
        'ignore_patient_ids' => '',
        'ignore_fee_types' => true,
        'ignore_fee_type_ids' => '9,10,23,24,25,26,27,28,29,30,31,32,33,34,35,36,37,38,39,40,41,42,131,132,133,134,135,2',
    ];

    /**
     * @return array<string, mixed>
     */
    public static function defaultOptions(): array
    {
        $today = Carbon::today();

        return array_merge(self::DEFAULT_OPTIONS, [
            'start_date' => $today->copy()->startOfMonth()->toDateString(),
            'end_date' => $today->copy()->endOfMonth()->toDateString(),
            'reuse_excel_profiles' => false,
        ]);
    }

    /**
     * @param  array<string, mixed>  $options
     */
    public function __construct(
        private readonly array $options,
        private readonly bool $dryRun,
    ) {}

    /**
     * @return array{
     *     dry_run: bool,
     *     counts: array{rows: int, selected: int, profiles_assigned: int, query_only: int, updated: int, skipped: int, errors: int},
     *     summary: array<string, int|float>,
     *     archive: array{batch_id: string|null, rows: array{patients: int, invoices: int, patient_tests: int}},
     *     report: list<array<string, mixed>>,
     *     invoices: list<array<string, mixed>>,
     *     assignments: list<array<string, mixed>>,
     *     impacts: list<array{table: string, rows: int, columns: list<string>}>
     * }
     */
    public function process(Collection $rows): array
    {
        $input = $this->validatedRows($rows);

        if ($input['missing_columns'] !== []) {
            $input['report'][] = [
                'row' => 1,
                'status' => 'error',
                'message' => 'Missing required columns: '.implode(', ', $input['missing_columns']).'.',
            ];

            return $this->result($input['report'], [], collect(), [], []);
        }

        if ($input['profiles'] === []) {
            if ($input['report'] === []) {
                $input['report'][] = [
                    'row' => 0,
                    'status' => 'error',
                    'message' => 'The workbook has no valid patient profiles to assign.',
                ];
            }

            return $this->result($input['report'], [], collect(), [], []);
        }

        $profiles = $this->matchDepartments($input['profiles'], $input['report']);

        if (! $this->option('reuse_excel_profiles')) {
            $profiles = $this->removeDuplicateCardNumbers($profiles, $input['report']);
        }

        if ($profiles === []) {
            return $this->result($input['report'], [], collect(), [], []);
        }

        $candidates = $this->candidateInvoices();
        $pickedInvoices = $this->pickInvoices($candidates);
        $pickedInvoiceIds = $pickedInvoices->pluck('invoice_id')->map(fn ($id): int => (int) $id)->all();
        $selectedPatientIds = $pickedInvoices->pluck('patient_id')->map(fn ($id): int => (int) $id)->unique()->all();

        $reuseExcelProfiles = (bool) $this->option('reuse_excel_profiles');

        if (
            ! $reuseExcelProfiles
            && count($selectedPatientIds) > count($profiles)
            && DB::table('government_departments')->doesntExist()
        ) {
            $input['report'][] = [
                'row' => 0,
                'status' => 'error',
                'message' => 'No government departments are available for the random assignments.',
            ];

            return $this->result($input['report'], [], collect(), [], []);
        }

        $assignments = $this->assignProfiles($profiles, $selectedPatientIds, $input['report'], $reuseExcelProfiles);
        $snapshot = $this->snapshot($pickedInvoiceIds);
        $archiveBatchId = null;
        $archiveCounts = null;

        if (! $this->dryRun && $pickedInvoiceIds !== []) {
            $archiveBatchId = (string) Str::uuid();
            $archiveCounts = $this->applyUpdates($assignments, $pickedInvoices, $archiveBatchId);
        }

        return $this->result(
            $input['report'],
            $pickedInvoices->all(),
            $snapshot,
            $assignments,
            $selectedPatientIds,
            $archiveBatchId,
            $archiveCounts,
        );
    }

    /**
     * @return array{profiles: list<array<string, mixed>>, report: list<array<string, mixed>>, missing_columns: list<string>}
     */
    private function validatedRows(Collection $rows): array
    {
        $requiredColumns = [
            'title',
            'first_name',
            'relationship_title',
            'father_husband_name',
            'age',
            'address',
            'government_department_name',
            'designation',
            'government_card_no',
        ];
        $headers = array_map(fn ($header): string => strtolower(trim((string) $header)), $rows->first()?->keys()->all() ?? []);
        $missingColumns = array_values(array_diff($requiredColumns, $headers));
        $profiles = [];
        $report = [];

        foreach ($rows as $offset => $rawRow) {
            $row = $rawRow instanceof Collection ? $rawRow->all() : (array) $rawRow;

            if (collect($row)->every(fn ($value): bool => $value === null || trim((string) $value) === '')) {
                continue;
            }

            $sheetRow = $offset + 2;

            if ($missingColumns !== []) {
                continue;
            }

            $errors = [];

            $title = match (strtolower(rtrim($this->text($row['title'] ?? null), '.'))) {
                'mr' => 'Mr.',
                'miss' => 'Miss',
                default => null,
            };

            if ($title === null) {
                $errors[] = 'Title must be Mr. or Miss.';
            }

            $firstName = $this->text($row['first_name'] ?? null);
            if ($firstName === '' || mb_strlen($firstName) > 60) {
                $errors[] = 'First name is required and must be 60 characters or fewer.';
            }

            $lastName = $this->text($row['last_name'] ?? null);
            if (mb_strlen($lastName) > 60) {
                $errors[] = 'Last name must be 60 characters or fewer.';
            }

            $relationshipTitle = strtoupper($this->text($row['relationship_title'] ?? null));
            if (! in_array($relationshipTitle, ['H/O', 'W/O', 'S/O', 'D/O', 'M/O', 'F/O'], true)) {
                $errors[] = 'Relationship title must be H/O, W/O, S/O, D/O, M/O, or F/O.';
            }

            $fatherHusbandName = $this->text($row['father_husband_name'] ?? null);
            if ($fatherHusbandName === '' || mb_strlen($fatherHusbandName) > 60) {
                $errors[] = 'Father/husband name is required and must be 60 characters or fewer.';
            }

            [$age, $ageUnit, $ageError] = $this->parseAge($row['age'] ?? null);
            if ($ageError !== null) {
                $errors[] = $ageError;
            }

            $address = $this->text($row['address'] ?? null);
            if ($address === '' || mb_strlen($address) > 255) {
                $errors[] = 'Address is required and must be 255 characters or fewer.';
            }

            $mobile = '0300-1234567';

            $departmentName = $this->text($row['government_department_name'] ?? null);
            $designation = $this->text($row['designation'] ?? null);
            $governmentCardNo = $this->text($row['government_card_no'] ?? null);

            if ($departmentName === '') {
                $errors[] = 'Government department name is required.';
            }

            if ($designation === '' || mb_strlen($designation) > 255) {
                $errors[] = 'Designation is required and must be 255 characters or fewer.';
            }

            if ($governmentCardNo === '' || mb_strlen($governmentCardNo) > 10) {
                $errors[] = 'Government card number is required and must be 10 characters or fewer.';
            }

            if ($errors !== []) {
                $report[] = [
                    'row' => $sheetRow,
                    'status' => 'error',
                    'message' => implode(' ', $errors),
                ];

                continue;
            }

            $profiles[] = [
                'row' => $sheetRow,
                'title' => $title,
                'first_name' => $firstName,
                'last_name' => $lastName === '' ? null : $lastName,
                'relationship_title' => $relationshipTitle,
                'father_husband_name' => $fatherHusbandName,
                'age' => $age,
                'years_months' => $ageUnit,
                'address' => $address,
                'mobile' => $mobile,
                'government_department_name' => $departmentName,
                'designation' => $designation,
                'government_card_no' => $governmentCardNo,
            ];
        }

        return [
            'profiles' => $profiles,
            'report' => $report,
            'missing_columns' => $missingColumns,
        ];
    }

    /**
     * @return array{0: int|null, 1: string|null, 2: string|null}
     */
    private function parseAge(mixed $ageValue): array
    {
        $ageValue = $this->text($ageValue);
        if (preg_match('/^(\d{1,3})\s*(y|m|years?|year\(s\)|months?|month\(s\))?$/i', $ageValue, $ageMatch) !== 1) {
            return [null, null, 'Age must be a whole number, optionally followed by y/years or m/months.'];
        }

        $unit = strtolower($ageMatch[2] ?? '');
        $ageUnit = str_starts_with($unit, 'm') ? 'Month(s)' : 'Year(s)';

        return [(int) $ageMatch[1], $ageUnit, null];
    }

    /**
     * @param  list<array<string, mixed>>  $profiles
     * @param  list<array<string, mixed>>  $report
     * @return list<array<string, mixed>>
     */
    private function matchDepartments(array $profiles, array &$report): array
    {
        $departments = DB::table('government_departments')
            ->orderBy('id')
            ->get(['id', 'name'])
            ->groupBy(fn (object $department): string => $this->normaliseDepartmentName($department->name));

        $matchedProfiles = [];

        foreach ($profiles as $profile) {
            $departmentMatches = $departments->get($this->normaliseDepartmentName($profile['government_department_name']), collect());

            if ($departmentMatches->count() === 0) {
                $report[] = [
                    'row' => $profile['row'],
                    'status' => 'error',
                    'message' => 'Government department was not found: '.$profile['government_department_name'].'.',
                ];

                continue;
            }

            if ($departmentMatches->count() > 1) {
                $departmentIds = $departmentMatches->pluck('id')->implode(', ');
                $report[] = [
                    'row' => $profile['row'],
                    'status' => 'error',
                    'message' => 'Government department name is ambiguous (matching IDs: '.$departmentIds.'); rename duplicate departments before importing.',
                ];

                continue;
            }

            $profile['government_department_id'] = (int) $departmentMatches->first()->id;
            $matchedProfiles[] = $profile;
        }

        return $matchedProfiles;
    }

    /**
     * @return Collection<int, object>
     */
    private function candidateInvoices(): Collection
    {
        $eligibleUserIds = DB::query()
            ->fromSub($this->candidateInvoiceQuery(), 'gov_candidate_invoices')
            ->select('user_id')
            ->groupBy('user_id')
            ->havingRaw('SUM(invoice_non_entitled_total) >= ?', [$this->option('min_user_total')])
            ->pluck('user_id')
            ->map(fn ($userId): int => (int) $userId)
            ->all();

        if ($eligibleUserIds === []) {
            return collect();
        }

        return $this->candidateInvoiceQuery()
            ->whereIn('i.user_id', $eligibleUserIds)
            ->get();
    }

    private function candidateInvoiceQuery(): Builder
    {
        return DB::table('invoices as i')
            ->join('users as u', 'u.id', '=', 'i.user_id')
            ->join('patient_tests as pt', 'pt.invoice_id', '=', 'i.id')
            ->join('fee_types as ft', 'ft.id', '=', 'pt.fee_type_id')
            ->where('pt.created_at', '>=', $this->option('start_date'))
            ->where('pt.created_at', '<', Carbon::parse($this->option('end_date'))->addDay()->startOfDay())
            ->where('pt.status', 'Normal')
            ->where('pt.government_non_gov', false)
            ->when(
                $this->option('ignore_users') && $this->ids('ignore_user_ids') !== [],
                fn ($query) => $query->whereNotIn('i.user_id', $this->ids('ignore_user_ids'))
            )
            ->when(
                $this->option('ignore_patients') && $this->ids('ignore_patient_ids') !== [],
                fn ($query) => $query->whereNotIn('i.patient_id', $this->ids('ignore_patient_ids'))
            )
            ->when(
                ! $this->option('include_admission'),
                fn ($query) => $query->whereNotExists(function ($subquery): void {
                    $subquery->selectRaw('1')
                        ->from('admissions as a')
                        ->whereColumn('a.patient_id', 'i.patient_id');
                })
            )
            ->when(
                ! $this->option('include_excluded_fee_types') && $this->option('ignore_fee_types') && $this->ids('ignore_fee_type_ids') !== [],
                fn ($query) => $query->whereNotIn('pt.fee_type_id', $this->ids('ignore_fee_type_ids'))
            )
            ->selectRaw('
                i.id AS invoice_id,
                i.patient_id,
                i.user_id,
                u.name AS user_name,
                MAX(CASE WHEN lower(ft.type) LIKE ? OR lower(ft.type) LIKE ? OR lower(ft.type) LIKE ? THEN 1 ELSE 0 END) AS is_driving,
                SUM(pt.total_amount) AS invoice_non_entitled_total
            ', ['%driving licence%', '%driving license%', '%driving%'])
            ->groupBy('i.id', 'i.patient_id', 'i.user_id', 'u.name')
            ->havingRaw('SUM(pt.total_amount) >= ?', [$this->option('min_invoice_amount')]);
    }

    /**
     * @param  Collection<int, object>  $candidates
     * @return Collection<int, object>
     */
    private function pickInvoices(Collection $candidates): Collection
    {
        $driving = $candidates->filter(fn (object $invoice): bool => (int) $invoice->is_driving === 1);
        $nonDriving = $candidates->reject(fn (object $invoice): bool => (int) $invoice->is_driving === 1);
        $pickedDriving = $this->option('include_driving')
            ? $driving->shuffle()->take((int) $this->option('driving_pick_count'))
            : collect();
        $picked = $pickedDriving->values();
        $pickedTotal = (float) $picked->sum(fn (object $invoice): float => (float) $invoice->invoice_non_entitled_total);
        $capacity = max(0, (float) $this->option('target_max') - $pickedTotal);
        $runningTotal = 0.0;
        foreach ($nonDriving->shuffle() as $invoice) {
            $amount = (float) $invoice->invoice_non_entitled_total;

            if ($runningTotal + $amount > $capacity) {
                break;
            }

            $picked->push($invoice);
            $runningTotal += $amount;
        }

        $pickedIds = $picked->pluck('invoice_id')->all();
        $currentTotal = $pickedTotal + $runningTotal;
        $remainingCapacity = max(0, (float) $this->option('target_max') - $currentTotal);
        $neededToReachMinimum = max(0, (float) $this->option('target_min') - $currentTotal);

        if ($currentTotal < (float) $this->option('target_min')) {
            $bestFit = $nonDriving
                ->reject(fn (object $invoice): bool => in_array($invoice->invoice_id, $pickedIds, true))
                ->filter(fn (object $invoice): bool => (float) $invoice->invoice_non_entitled_total <= $remainingCapacity)
                ->sortBy(fn (object $invoice): float => abs((float) $invoice->invoice_non_entitled_total - $neededToReachMinimum))
                ->first();

            if ($bestFit !== null) {
                $picked->push($bestFit);
            }
        }

        return $picked->values();
    }

    /**
     * @param  list<int>  $invoiceIds
     * @return Collection<int, object>
     */
    private function snapshot(array $invoiceIds): Collection
    {
        if ($invoiceIds === []) {
            return collect();
        }

        $query = DB::table('patient_tests')
            ->whereIn('invoice_id', $invoiceIds)
            ->where('government_non_gov', false)
            ->when(
                ! $this->option('include_excluded_fee_types') && $this->option('ignore_fee_types') && $this->ids('ignore_fee_type_ids') !== [],
                fn ($query) => $query->whereNotIn('fee_type_id', $this->ids('ignore_fee_type_ids'))
            );

        return $query->get(['patient_id', 'invoice_id', 'total_amount', 'hif_amount', 'govt_amount']);
    }

    /**
     * @param  array<int, array<string, mixed>>  $assignments
     * @param  Collection<int, object>  $invoices
     * @return array{patients: int, invoices: int, patient_tests: int}
     */
    private function applyUpdates(array $assignments, Collection $invoices, string $archiveBatchId): array
    {
        return DB::transaction(function () use ($assignments, $invoices, $archiveBatchId): array {
            $archiveCounts = $this->archiveBeforeUpdates($assignments, $invoices, $archiveBatchId);
            $patientCreationDates = DB::table('patients')
                ->whereIn('id', array_keys($assignments))
                ->pluck('created_at', 'id');

            foreach ($assignments as $patientId => $assignment) {
                $invoiceIds = $invoices
                    ->where('patient_id', $patientId)
                    ->pluck('invoice_id')
                    ->all();

                if ($invoiceIds === []) {
                    continue;
                }

                $profile = $assignment['profile'];
                $governmentDepartmentId = $assignment['government_department_id'];
                $governmentDepartmentIdString = (string) $governmentDepartmentId;
                $governmentCardNo = $assignment['government_card_no'];
                $patientUpdates = [
                    'government_non_gov' => true,
                    'government_department_id' => $governmentDepartmentId,
                    'government_card_no' => $governmentCardNo,
                    'mobile' => '0300-1234567',
                ];

                if ($profile !== null) {
                    $createdAt = $patientCreationDates->get($patientId);

                    if ($createdAt === null) {
                        throw new \RuntimeException('A selected patient no longer exists or has no creation date.');
                    }

                    $dobDate = Carbon::parse($createdAt);
                    $dobDate = $profile['years_months'] === 'Month(s)'
                        ? $dobDate->subMonths($profile['age'])
                        : $dobDate->subYears($profile['age']);

                    $patientUpdates = array_merge($patientUpdates, [
                        'title' => $profile['title'],
                        'first_name' => $profile['first_name'],
                        'last_name' => $profile['last_name'],
                        'relationship_title' => $profile['relationship_title'],
                        'father_husband_name' => $profile['father_husband_name'],
                        'age' => $profile['age'],
                        'years_months' => $profile['years_months'],
                        'dob' => $dobDate->toDateString(),
                        'sex' => $profile['title'] === 'Mr.',
                        'address' => $profile['address'],
                        'mobile' => $profile['mobile'],
                        'designation' => $profile['designation'],
                    ]);
                }

                DB::table('patients')->where('id', $patientId)->update($patientUpdates);

                DB::table('invoices')
                    ->whereIn('id', $invoiceIds)
                    ->update([
                        'government_non_government' => true,
                        'government_department_id' => $governmentDepartmentIdString,
                        'government_card_no' => $governmentCardNo,
                        'total_amount' => 0,
                        'hif_amount' => 0,
                        'govt_amount' => 0,
                    ]);

                $testQuery = DB::table('patient_tests')
                    ->whereIn('invoice_id', $invoiceIds)
                    ->where('government_non_gov', false)
                    ->when(
                        ! $this->option('include_excluded_fee_types') && $this->option('ignore_fee_types') && $this->ids('ignore_fee_type_ids') !== [],
                        fn ($query) => $query->whereNotIn('fee_type_id', $this->ids('ignore_fee_type_ids'))
                    );

                $testQuery->update([
                    'government_non_gov' => true,
                    'government_department_id' => $governmentDepartmentIdString,
                    'government_card_no' => $governmentCardNo,
                    'total_amount' => 0,
                    'hif_amount' => 0,
                    'govt_amount' => 0,
                ]);
            }

            return $archiveCounts;
        });
    }

    /**
     * @param  array<int, array<string, mixed>>  $assignments
     * @param  Collection<int, object>  $invoices
     * @return array{patients: int, invoices: int, patient_tests: int}
     */
    private function archiveBeforeUpdates(array $assignments, Collection $invoices, string $archiveBatchId): array
    {
        $invoiceIds = $invoices->pluck('invoice_id')->map(fn ($id): int => (int) $id)->all();
        $archiveRows = [];

        foreach ([
            'patients' => ['patients_import', array_keys($assignments)],
            'invoices' => ['invoices_improt', $invoiceIds],
        ] as $sourceTable => [$archiveTable, $recordIds]) {
            $columns = DB::getSchemaBuilder()->getColumnListing($sourceTable);
            $records = DB::table($sourceTable)
                ->whereIn('id', $recordIds)
                ->select($columns)
                ->selectRaw('?::uuid as import_batch_id, CURRENT_TIMESTAMP as archived_at', [$archiveBatchId]);

            $archiveRows[$sourceTable] = $recordIds === []
                ? 0
                : DB::table($archiveTable)->insertUsing([...$columns, 'import_batch_id', 'archived_at'], $records);
        }

        $patientTestColumns = DB::getSchemaBuilder()->getColumnListing('patient_tests');
        $patientTests = DB::table('patient_tests')
            ->whereIn('invoice_id', $invoiceIds)
            ->where('government_non_gov', false)
            ->when(
                ! $this->option('include_excluded_fee_types') && $this->option('ignore_fee_types') && $this->ids('ignore_fee_type_ids') !== [],
                fn ($query) => $query->whereNotIn('fee_type_id', $this->ids('ignore_fee_type_ids'))
            )
            ->select($patientTestColumns)
            ->selectRaw('?::uuid as import_batch_id, CURRENT_TIMESTAMP as archived_at', [$archiveBatchId]);

        $archiveRows['patient_tests'] = $invoiceIds === []
            ? 0
            : DB::table('patient_tests_import')->insertUsing(
                [...$patientTestColumns, 'import_batch_id', 'archived_at'],
                $patientTests,
            );

        return [
            'patients' => $archiveRows['patients'],
            'invoices' => $archiveRows['invoices'],
            'patient_tests' => $archiveRows['patient_tests'],
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $profiles
     * @param  list<array<string, mixed>>  $report
     * @param  list<int>  $patientIds
     * @return array<int, array{government_department_id: int, government_card_no: string, profile: array<string, mixed>|null, profile_row: int|null, assignment_type: string, department_name: string}>
     */
    private function assignProfiles(array $profiles, array $patientIds, array &$report, bool $reuseExcelProfiles): array
    {
        $departmentIds = DB::table('government_departments')
            ->orderBy('id')
            ->pluck('id')
            ->map(fn ($id): int => (int) $id)
            ->all();
        $departmentNames = DB::table('government_departments')
            ->whereIn('id', $departmentIds)
            ->pluck('name', 'id');

        shuffle($patientIds);
        shuffle($profiles);
        $assignments = [];
        $usedCardNumbers = [];

        foreach ($patientIds as $index => $patientId) {
            $profile = $reuseExcelProfiles
                ? ($profiles === [] ? null : $profiles[$index % count($profiles)])
                : ($profiles[$index] ?? null);

            if ($profile !== null) {
                $usedCardNumbers[$this->normaliseCardNumber($profile['government_card_no'])] = true;
                $assignment = [
                    'government_department_id' => $profile['government_department_id'],
                    'government_card_no' => $profile['government_card_no'],
                    'profile' => $profile,
                    'profile_row' => $profile['row'],
                    'assignment_type' => 'excel',
                    'department_name' => $profile['government_department_name'],
                ];
                $report[] = [
                    'row' => $profile['row'],
                    'patient_id' => $patientId,
                    'status' => 'assigned',
                    'message' => 'Excel profile assigned to a randomly selected patient.',
                ];
            } else {
                $cardNumber = $this->uniqueCardNumber($usedCardNumbers);
                $departmentId = $departmentIds[array_rand($departmentIds)];
                $usedCardNumbers[$this->normaliseCardNumber($cardNumber)] = true;
                $assignment = [
                    'government_department_id' => $departmentId,
                    'government_card_no' => $cardNumber,
                    'profile' => null,
                    'profile_row' => null,
                    'assignment_type' => 'query',
                    'department_name' => (string) $departmentNames->get($departmentId),
                ];
            }

            $assignments[$patientId] = $assignment;
        }

        $usedProfileCount = min(count($profiles), count($patientIds));

        foreach (array_slice($profiles, $usedProfileCount) as $profile) {
            $report[] = [
                'row' => $profile['row'],
                'patient_id' => null,
                'status' => 'unassigned',
                'message' => 'No additional selected patient was available; this profile was not used.',
            ];
        }

        return $assignments;
    }

    /**
     * @param  array<string, bool>  $usedCardNumbers
     */
    private function uniqueCardNumber(array $usedCardNumbers): string
    {
        do {
            $cardNumber = (string) random_int(100, 9_999_999_999);
        } while (isset($usedCardNumbers[$this->normaliseCardNumber($cardNumber)]));

        return $cardNumber;
    }

    private function normaliseCardNumber(string $cardNumber): string
    {
        return ltrim($cardNumber, '0') ?: '0';
    }

    /**
     * @param  list<array<string, mixed>>  $report
     * @param  list<array<string, mixed>>  $invoices
     * @param  Collection<int, object>  $snapshot
     * @param  array<int, array<string, mixed>>  $assignments
     * @param  list<int>  $selectedPatientIds
     * @return array{
     *     dry_run: bool,
     *     counts: array{rows: int, selected: int, profiles_assigned: int, query_only: int, updated: int, skipped: int, errors: int},
     *     summary: array<string, int|float>,
     *     archive: array{batch_id: string|null, rows: array{patients: int, invoices: int, patient_tests: int}},
     *     report: list<array<string, mixed>>,
     *     invoices: list<array<string, mixed>>,
     *     assignments: list<array<string, mixed>>,
     *     impacts: list<array{table: string, rows: int, columns: list<string>}>
     * }
     */
    private function result(
        array $report,
        array $invoices,
        Collection $snapshot,
        array $assignments,
        array $selectedPatientIds,
        ?string $archiveBatchId = null,
        ?array $archiveCounts = null,
    ): array {
        $pickedTotal = (float) collect($invoices)->sum(fn (array|object $invoice): float => (float) $invoice->invoice_non_entitled_total);
        $amountsByPatient = collect($invoices)
            ->groupBy(fn (array|object $invoice): int => (int) $invoice->patient_id)
            ->map(function (Collection $patientInvoices): array {
                $amountBefore = (float) $patientInvoices->sum(
                    fn (array|object $invoice): float => (float) $invoice->invoice_non_entitled_total
                );

                return [
                    'before' => $amountBefore,
                    'after' => $this->dryRun ? $amountBefore : 0.0,
                ];
            });
        $summary = [
            'picked_invoices_count' => count($invoices),
            'picked_patients_count' => count($selectedPatientIds),
            'picked_total_amount' => $pickedTotal,
            'picked_total_after_import' => $this->dryRun ? $pickedTotal : 0.0,
            'impacted_patient_tests_count' => $snapshot->count(),
            'total_removed' => (float) $snapshot->sum(fn (object $test): float => (float) $test->total_amount),
            'hif_removed' => (float) $snapshot->sum(fn (object $test): float => (float) $test->hif_amount),
            'govt_removed' => (float) $snapshot->sum(fn (object $test): float => (float) $test->govt_amount),
        ];
        $profilesAssigned = collect($assignments)->where('assignment_type', 'excel')->count();
        $skipped = collect($report)->where('status', 'unassigned')->count();
        $errors = collect($report)->where('status', 'error')->count();
        $departmentNames = DB::table('government_departments')
            ->whereIn('id', array_column($assignments, 'government_department_id'))
            ->pluck('name', 'id');
        $assignmentReport = [];

        foreach ($assignments as $patientId => $assignment) {
            $assignmentReport[] = [
                'patient_id' => $patientId,
                'profile_row' => $assignment['profile_row'],
                'excel_name' => $assignment['profile'] === null
                    ? null
                    : trim($assignment['profile']['first_name'].' '.($assignment['profile']['last_name'] ?? '')),
                'assignment_type' => $assignment['assignment_type'],
                'department_id' => $assignment['government_department_id'],
                'department_name' => $departmentNames->get($assignment['government_department_id'], $assignment['department_name']),
                'card_no' => $assignment['government_card_no'],
                'amount_before' => $amountsByPatient->get($patientId, ['before' => 0.0])['before'],
                'amount_after' => $amountsByPatient->get($patientId, ['after' => 0.0])['after'],
            ];
        }

        $patientColumns = [
            'government_non_gov',
            'government_department_id',
            'government_card_no',
            'mobile',
        ];

        if ($profilesAssigned > 0) {
            $patientColumns = array_merge($patientColumns, [
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
        }

        $archiveRows = $archiveCounts ?? [
            'patients' => count($assignments),
            'invoices' => count($invoices),
            'patient_tests' => $snapshot->count(),
        ];
        $archiveImpacts = [];

        foreach ([
            'patients' => 'patients_import',
            'invoices' => 'invoices_improt',
            'patient_tests' => 'patient_tests_import',
        ] as $sourceTable => $archiveTable) {
            $archiveImpacts[] = [
                'table' => $archiveTable,
                'rows' => $archiveRows[$sourceTable],
                'columns' => [...DB::getSchemaBuilder()->getColumnListing($sourceTable), 'import_batch_id', 'archived_at'],
            ];
        }

        return [
            'dry_run' => $this->dryRun,
            'counts' => [
                'rows' => collect($report)
                    ->pluck('row')
                    ->filter(fn (mixed $row): bool => (int) $row > 0)
                    ->unique()
                    ->count(),
                'selected' => count($selectedPatientIds),
                'profiles_assigned' => $profilesAssigned,
                'query_only' => count($selectedPatientIds) - $profilesAssigned,
                'updated' => $this->dryRun ? 0 : count($selectedPatientIds),
                'skipped' => $skipped,
                'errors' => $errors,
            ],
            'summary' => $summary,
            'archive' => [
                'batch_id' => $archiveBatchId,
                'rows' => $archiveRows,
            ],
            'report' => array_slice($report, 0, 500),
            'invoices' => array_map(fn (array|object $invoice): array => (array) $invoice, array_slice($invoices, 0, 500)),
            'assignments' => $assignmentReport,
            'impacts' => [
                [
                    'table' => 'patients',
                    'rows' => count($selectedPatientIds),
                    'columns' => $patientColumns,
                ],
                [
                    'table' => 'invoices',
                    'rows' => count($invoices),
                    'columns' => [
                        'government_non_government',
                        'government_department_id',
                        'government_card_no',
                        'total_amount',
                        'hif_amount',
                        'govt_amount',
                    ],
                ],
                [
                    'table' => 'patient_tests',
                    'rows' => $snapshot->count(),
                    'columns' => [
                        'government_non_gov',
                        'government_department_id',
                        'government_card_no',
                        'total_amount',
                        'hif_amount',
                        'govt_amount',
                    ],
                ],
                ...$archiveImpacts,
            ],
        ];
    }

    private function option(string $key): mixed
    {
        return $this->options[$key] ?? self::defaultOptions()[$key] ?? null;
    }

    /**
     * @return list<int>
     */
    private function ids(string $key): array
    {
        $value = trim((string) $this->option($key));

        if ($value === '') {
            return [];
        }

        return array_values(array_unique(array_map('intval', preg_split('/\s*,\s*/', $value) ?: [])));
    }

    private function normaliseDepartmentName(string $name): string
    {
        return mb_strtolower(trim($name));
    }

    /**
     * @param  list<array<string, mixed>>  $profiles
     * @param  list<array<string, mixed>>  $report
     * @return list<array<string, mixed>>
     */
    private function removeDuplicateCardNumbers(array $profiles, array &$report): array
    {
        $duplicateCards = collect($profiles)
            ->groupBy(fn (array $profile): string => $profile['government_card_no'])
            ->filter(fn (Collection $matches): bool => $matches->count() > 1)
            ->keys()
            ->all();

        if ($duplicateCards === []) {
            return $profiles;
        }

        foreach ($profiles as $profile) {
            if (in_array($profile['government_card_no'], $duplicateCards, true)) {
                $report[] = [
                    'row' => $profile['row'],
                    'status' => 'error',
                    'message' => 'Card number is duplicated in the workbook; duplicate card numbers are only allowed when Excel profile reuse is enabled.',
                ];
            }
        }

        return array_values(array_filter(
            $profiles,
            fn (array $profile): bool => ! in_array($profile['government_card_no'], $duplicateCards, true),
        ));
    }

    private function text(mixed $value): string
    {
        if ($value === null || is_bool($value)) {
            return '';
        }

        return trim((string) $value);
    }
}
