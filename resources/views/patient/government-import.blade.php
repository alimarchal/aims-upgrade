<x-app-layout>
    <x-slot name="header">
        <div class="ak-head">
            <div>
                <nav class="ak-crumbs" aria-label="Breadcrumb">
                    <a href="{{ route('patient.index') }}">Patients</a><span aria-hidden="true">›</span><span>Government Excel Import</span>
                </nav>
                <h1 class="ak-title">Government Patient Excel Import</h1>
                <p class="ak-sub">Review the random invoice selection and Excel profile assignments before updating records.</p>
            </div>
            <div class="ak-actions">
                <a href="{{ route('patient.government-import.help') }}" class="ak-btn">How it works</a>
                @can('view patient import archive')
                    <a href="{{ route('patient-import.index') }}" class="ak-btn">Patient Import Archive</a>
                @endcan
                <a href="{{ route('patient.government-import.template') }}" class="ak-btn ak-btn-primary">Download Excel Template</a>
                <a href="{{ route('patient.index') }}" class="ak-btn">Back to Patients</a>
            </div>
        </div>
    </x-slot>

    @include('partials.access-style')

    <div class="ak-page space-y-5">
        <x-validation-errors />

        @if ($status = session('government_patient_import_status'))
            <div role="{{ $status['type'] === 'warning' ? 'alert' : 'status' }}"
                class="ak-alert {{ $status['type'] === 'warning' ? 'border border-amber-300 bg-amber-50 text-amber-900 dark:border-amber-700 dark:bg-amber-950 dark:text-amber-100' : 'ak-alert-ok' }}">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <p class="font-semibold">{{ $status['message'] }}</p>
                    <a href="#import-result" class="font-semibold underline">View full results</a>
                </div>
            </div>
        @endif

        <div class="ak-card ak-card-body text-sm">
                <p class="font-semibold text-slate-900">Excel columns and matching</p>
                <p class="mt-2">
                    The query first filters and randomly selects eligible invoices and their patients from the whole database.
                    Excel rows are profile data only: valid profiles are randomly assigned to selected patients.
                    When reuse is off, each Excel row is used at most once; remaining patients get a random department and a unique card number.
                    When reuse is on, Excel profiles are spread as evenly as possible across all selected patients; a whole profile, including its card number, may repeat.
                    Extra Excel profiles are not used; the report lists each selected patient ID and assigned Excel row.
                </p>
                <p class="mt-2">
                    Title must be <code>Mr.</code> or <code>Miss</code>; it determines sex automatically. Use one of
                    <code>H/O</code>, <code>W/O</code>, <code>S/O</code>, <code>D/O</code>, <code>M/O</code>, or <code>F/O</code>
                    for relationship title. Enter the age in one column: a number such as <code>31</code> means years, or add
                    <code>y</code>/<code>years</code> or <code>m</code>/<code>months</code> (for example <code>31Y</code> or <code>6m</code>).
                    The database age unit and DOB are derived automatically. DOB uses the patient's existing creation date. Mobile is not an Excel column; updates use
                    <code>0300-1234567</code>.
                </p>
                <details class="mt-3">
                    <summary class="cursor-pointer font-semibold">Excel column guide</summary>
                    <dl class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <div><dt class="font-semibold">#</dt><dd>Optional Excel row/serial reference; it does not identify a database patient.</dd></div>
                        <div><dt class="font-semibold">title</dt><dd>Use Mr. or Miss. Sex is set automatically from this value.</dd></div>
                        <div><dt class="font-semibold">first_name</dt><dd>Required patient first name; maximum 60 characters.</dd></div>
                        <div><dt class="font-semibold">last_name</dt><dd>Patient last name; leave blank to save NULL.</dd></div>
                        <div><dt class="font-semibold">relationship_title</dt><dd>Use H/O, W/O, S/O, D/O, M/O, or F/O.</dd></div>
                        <div><dt class="font-semibold">father_husband_name</dt><dd>Required father/husband name; maximum 60 characters.</dd></div>
                        <div><dt class="font-semibold">age</dt><dd>Age in one cell: 31 means years; 31y/31Y or 31 years also means years; 6m or 6 months means months. Both database age fields are set automatically.</dd></div>
                        <div><dt class="font-semibold">address</dt><dd>Required patient address; maximum 255 characters.</dd></div>
                        <div><dt class="font-semibold">government_department_name</dt><dd>Existing department name, matched without case sensitivity; duplicate names are rejected.</dd></div>
                        <div><dt class="font-semibold">designation</dt><dd>Required government employee designation; maximum 255 characters.</dd></div>
                        <div><dt class="font-semibold">government_card_no</dt><dd>Required card number; keep it as text to preserve leading zeroes.</dd></div>
                    </dl>
                </details>
                @if ($duplicateDepartments->isNotEmpty())
                    <div class="mt-3">
                        <p class="font-semibold">Department names that are ambiguous in the database:</p>
                        <ul class="mt-1 list-inside list-disc">
                            @foreach ($duplicateDepartments as $departments)
                                <li>
                                    {{ $departments->first()->name }}
                                    (IDs: {{ $departments->pluck('id')->implode(', ') }})
                                </li>
                            @endforeach
                        </ul>
                        <p class="mt-1">These names must be made unique before their rows can be imported.</p>
                    </div>
                @endif
        </div>

            <form action="{{ route('patient.government-import.run') }}" method="POST" enctype="multipart/form-data"
                class="ak-card ak-card-body space-y-6">
                @csrf

                <section class="space-y-4">
                    <h3 class="ak-card-head">Workbook and date filters</h3>
                    <div class="ak-grid">
                        <div class="ak-field">
                            <label for="import_file" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">Patient Excel file</label>
                            <input id="import_file" name="import_file" type="file" accept=".xlsx,.xls,.csv" required
                                class="ak-input block w-full rounded-md border border-gray-300 p-2 text-sm text-gray-700 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-200">
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Upload the filled template (.xlsx, .xls, or CSV). Each valid row is randomly assigned to one selected patient.</p>
                        </div>
                        <div class="ak-field">
                            <label for="start_date" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">Start date</label>
                            <input id="start_date" name="start_date" type="date" value="{{ old('start_date', $defaults['start_date']) }}" required
                                class="ak-input w-full rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100">
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Inclusive start date for patient-test creation time. Defaults to the first day of this month.</p>
                        </div>
                        <div class="ak-field">
                            <label for="end_date" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">End date</label>
                            <input id="end_date" name="end_date" type="date" value="{{ old('end_date', $defaults['end_date']) }}" required
                                class="ak-input w-full rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100">
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Inclusive end date for patient-test creation time. Defaults to the last day of this month.</p>
                        </div>
                    </div>
                </section>

                <section class="space-y-4">
                    <h3 class="ak-card-head">Invoice selection</h3>
                    <div class="ak-grid">
                        <div class="ak-field">
                            <label for="target_min" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">Target minimum</label>
                            <input id="target_min" name="target_min" type="number" min="0" step="0.01" value="{{ old('target_min', $defaults['target_min']) }}" required
                                class="ak-input w-full rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100">
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Desired lower bound for the randomly selected invoice total.</p>
                        </div>
                        <div class="ak-field">
                            <label for="target_max" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">Target maximum</label>
                            <input id="target_max" name="target_max" type="number" min="0" step="0.01" value="{{ old('target_max', $defaults['target_max']) }}" required
                                class="ak-input w-full rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100">
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Upper limit for the picked invoice total; invoices above the remaining amount are not picked.</p>
                        </div>
                        <div class="ak-field">
                            <label for="min_invoice_amount" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">Minimum invoice amount</label>
                            <input id="min_invoice_amount" name="min_invoice_amount" type="number" min="0" step="0.01" value="{{ old('min_invoice_amount', $defaults['min_invoice_amount']) }}" required
                                class="ak-input w-full rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100">
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">An invoice must reach this amount after ignored fee types are removed.</p>
                        </div>
                        <div class="ak-field">
                            <label for="min_user_total" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">Minimum user total</label>
                            <input id="min_user_total" name="min_user_total" type="number" min="0" step="0.01" value="{{ old('min_user_total', $defaults['min_user_total']) }}" required
                                class="ak-input w-full rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100">
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">The invoice creator's eligible total in the date range must reach this amount.</p>
                        </div>
                        <div class="ak-field">
                            <label for="driving_pick_count" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">Driving invoice count</label>
                            <input id="driving_pick_count" name="driving_pick_count" type="number" min="0" max="1000" value="{{ old('driving_pick_count', $defaults['driving_pick_count']) }}" required
                                class="ak-input w-full rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100">
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Maximum number of random driving-licence invoices when that option is enabled.</p>
                        </div>
                    </div>

                    @foreach ([
                        'include_driving' => [
                            'Include the random driving-licence invoice pick',
                            'When checked, randomly pick up to the driving invoice count above.',
                        ],
                        'include_admission' => [
                            'Include patients who have an admission',
                            'When unchecked, patients with any admission are excluded, as in the SQL.',
                        ],
                        'include_excluded_fee_types' => [
                            'Include fee types excluded by default',
                            'Overrides the ignored fee-type list and includes those fee types in selection and updates.',
                        ],
                        'reuse_excel_profiles' => [
                            'Reuse Excel profiles for all selected patients',
                            'When checked, randomly distribute valid Excel rows as evenly as possible across every selected patient. If there are fewer rows than patients, profile fields and card numbers repeat. When unchecked, each row is used at most once and remaining patients get query-generated departments and unique card numbers.',
                        ],
                    ] as $field => $label)
                        <div>
                            <label for="{{ $field }}" class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-200">
                                <input type="hidden" name="{{ $field }}" value="0">
                                <input id="{{ $field }}" name="{{ $field }}" type="checkbox" value="1"
                                    @checked(old($field, $defaults[$field]))
                                    class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                {{ $label[0] }}
                            </label>
                            <p class="ml-6 mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $label[1] }}</p>
                        </div>
                    @endforeach
                </section>

                <section class="space-y-4">
                    <h3 class="ak-card-head">Ignore lists</h3>
                    <div class="ak-grid">
                        @foreach ([
                            'ignore_users' => [
                                'Ignore users',
                                'ignore_user_ids',
                                'User IDs (comma-separated)',
                                'Exclude invoices created by these users. Default: user 13 from the SQL.',
                            ],
                            'ignore_patients' => [
                                'Ignore patients',
                                'ignore_patient_ids',
                                'Patient IDs (comma-separated)',
                                'Exclude these patient IDs from the database-wide invoice selection. Default list is empty.',
                            ],
                            'ignore_fee_types' => [
                                'Ignore fee types',
                                'ignore_fee_type_ids',
                                'Fee type IDs (comma-separated)',
                                'Exclude these fee types from invoice totals and updates unless the include-fees option is checked.',
                            ],
                        ] as $toggle => [$toggleLabel, $field, $fieldLabel, $description])
                            <div class="ak-field space-y-2 rounded-md border border-gray-200 p-4 dark:border-gray-700">
                                <label for="{{ $toggle }}" class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-200">
                                    <input type="hidden" name="{{ $toggle }}" value="0">
                                    <input id="{{ $toggle }}" name="{{ $toggle }}" type="checkbox" value="1"
                                        @checked(old($toggle, $defaults[$toggle]))
                                        class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                    {{ $toggleLabel }}
                                </label>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $description }}</p>
                                <label for="{{ $field }}" class="block text-xs text-gray-600 dark:text-gray-300">{{ $fieldLabel }}</label>
                                <input id="{{ $field }}" name="{{ $field }}" type="text" value="{{ old($field, $defaults[$field]) }}"
                                    class="ak-input w-full rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100">
                                <p class="text-xs text-gray-500 dark:text-gray-400">Enter positive numeric IDs separated by commas.</p>
                            </div>
                        @endforeach
                    </div>
                </section>

                <section class="space-y-3 rounded-md border border-amber-300 bg-amber-50 p-4 dark:border-amber-700 dark:bg-amber-950">
                    <label for="dry_run" class="flex items-start gap-3 text-sm font-semibold text-amber-950 dark:text-amber-100">
                        <input type="hidden" name="dry_run" value="0">
                        <input id="dry_run" name="dry_run" type="checkbox" value="1" @checked(old('dry_run', true))
                            class="mt-0.5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        <span>Analysis only (recommended; checked by default). No database records will be changed.</span>
                    </label>
                    <label for="confirm_execute" class="flex items-start gap-3 text-sm text-amber-950 dark:text-amber-100">
                        <input id="confirm_execute" name="confirm_execute" type="checkbox" value="1" @checked(old('confirm_execute'))
                            class="mt-0.5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        <span>I confirm permanent updates to selected patients, invoices, and patient tests if Analysis only is unchecked.</span>
                    </label>
                    <p class="ml-6 text-xs text-amber-900 dark:text-amber-200">This second confirmation is required for execution; it has no effect during analysis.</p>
                </section>

                <div class="flex flex-wrap items-center gap-3">
                    <button type="submit"
                        class="inline-flex items-center rounded-md bg-indigo-700 px-5 py-3 text-sm font-semibold text-white hover:bg-indigo-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                        Analyze / Execute Import
                    </button>
                    <p class="text-sm text-gray-600 dark:text-gray-300">Execution requires unchecking Analysis only and confirming the permanent update.</p>
                </div>
            </form>

            @if ($result)
                <section id="import-result" class="ak-card ak-card-body space-y-5">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                            {{ $result['dry_run'] ? 'Analysis result (no changes saved)' : 'Import executed' }}
                        </h3>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                            {{ $result['counts']['rows'] }} Excel rows ·
                            {{ $result['counts']['selected'] }} randomly selected patients ·
                            {{ $result['counts']['profiles_assigned'] }} Excel profiles assigned ·
                            {{ $result['counts']['query_only'] }} query-only assignments ·
                            {{ $result['counts']['skipped'] }} extra profiles unused ·
                            {{ $result['counts']['errors'] }} errors
                        </p>
                        @if ($result['archive']['batch_id'])
                            <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">
                                Pre-import snapshots saved under batch
                                <code class="rounded bg-gray-100 px-1 py-0.5 dark:bg-gray-800">{{ $result['archive']['batch_id'] }}</code>.
                            </p>
                        @elseif ($result['dry_run'] && array_sum($result['archive']['rows']) > 0)
                            <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">
                                Analysis only: {{ array_sum($result['archive']['rows']) }} source rows are planned for archive; no snapshots have been saved.
                            </p>
                        @endif
                    </div>

                    <div class="grid grid-cols-2 gap-3 md:grid-cols-4">
                        @foreach ([
                            'picked_invoices_count' => 'Invoices selected',
                            'picked_patients_count' => 'Patients selected',
                            'picked_total_amount' => 'Selected amount before import',
                            'picked_total_after_import' => 'Selected amount after import',
                            'impacted_patient_tests_count' => 'Patient tests affected',
                            'total_removed' => 'Total amount zeroed',
                            'hif_removed' => 'HIF amount zeroed',
                            'govt_removed' => 'Government amount zeroed',
                        ] as $key => $label)
                            <div class="rounded-md bg-gray-50 p-3 dark:bg-gray-900">
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $label }}</p>
                                <p class="mt-1 font-semibold text-gray-900 dark:text-gray-100">
                                    {{ str_contains($key, 'amount') || str_ends_with($key, '_removed') ? number_format((float) $result['summary'][$key], 2) : $result['summary'][$key] }}
                                </p>
                            </div>
                        @endforeach
                    </div>

                    @if ($result['assignments'] !== [])
                        <div class="overflow-x-auto">
                            <h4 class="mb-2 font-semibold text-gray-900 dark:text-gray-100">Selected patients and assignments (up to 500 rows)</h4>
                            <p class="mb-2 text-sm text-gray-600 dark:text-gray-300">
                                These are the patient IDs whose selected invoices were updated or will be updated. Amounts include only the selected eligible patient tests; other invoices for the patient are not included. Excel row is blank for query-only assignments.
                            </p>
                            <table class="ak-table">
                                <thead class="bg-gray-50 dark:bg-gray-900">
                                    <tr>
                                        <th class="px-3 py-2 text-left">Patient ID</th>
                                        <th class="px-3 py-2 text-left">Excel row</th>
                                        <th class="px-3 py-2 text-left">Excel profile</th>
                                        <th class="px-3 py-2 text-left">Assignment</th>
                                        <th class="px-3 py-2 text-left">Government department</th>
                                        <th class="px-3 py-2 text-left">Card number</th>
                                        <th class="px-3 py-2 text-right">Amount before import</th>
                                        <th class="px-3 py-2 text-right">Amount after import</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach ($result['assignments'] as $assignment)
                                        <tr>
                                            <td class="px-3 py-2">{{ $assignment['patient_id'] }}</td>
                                            <td class="px-3 py-2">{{ $assignment['profile_row'] ?? '' }}</td>
                                            <td class="px-3 py-2">{{ $assignment['excel_name'] ?? '' }}</td>
                                            <td class="px-3 py-2">{{ $assignment['assignment_type'] === 'excel' ? 'Excel profile' : 'Query random' }}</td>
                                            <td class="px-3 py-2">{{ $assignment['department_name'] }} (ID: {{ $assignment['department_id'] }})</td>
                                            <td class="px-3 py-2">{{ $assignment['card_no'] }}</td>
                                            <td class="px-3 py-2 text-right">{{ number_format((float) $assignment['amount_before'], 2) }}</td>
                                            <td class="px-3 py-2 text-right">{{ number_format((float) $assignment['amount_after'], 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif

                    <div class="space-y-2">
                        <h4 class="font-semibold text-gray-900 dark:text-gray-100">Database tables and columns affected</h4>
                        <p class="text-sm text-gray-600 dark:text-gray-300">
                            {{ $result['dry_run'] ? 'Planned changes (analysis only; nothing was saved).' : 'Changes applied by this run.' }}
                            Archive rows retain the complete pre-update source records, including their original IDs. A shared import batch ID lets you find the same run across the archive tables.
                        </p>
                        <div class="overflow-x-auto">
                            <table class="ak-table">
                                <thead class="bg-gray-50 dark:bg-gray-900">
                                    <tr>
                                        <th class="px-3 py-2 text-left">Database table</th>
                                        <th class="px-3 py-2 text-right">Rows</th>
                                        <th class="px-3 py-2 text-left">Columns updated</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach ($result['impacts'] as $impact)
                                        <tr>
                                            <td class="px-3 py-2 font-medium">{{ $impact['table'] }}</td>
                                            <td class="px-3 py-2 text-right">{{ $impact['rows'] }}</td>
                                            <td class="px-3 py-2">{{ implode(', ', $impact['columns']) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            <code>patients.years_months</code> remains a database field and is derived from the Excel age value. Timestamp columns are not updated.
                        </p>
                    </div>

                    @if ($result['report'] !== [])
                        <div class="overflow-x-auto">
                            <h4 class="mb-2 font-semibold text-gray-900 dark:text-gray-100">Excel profile report (up to 500 rows)</h4>
                            <table class="ak-table">
                                <thead class="bg-gray-50 dark:bg-gray-900">
                                    <tr>
                                        <th class="px-3 py-2 text-left">#</th>
                                        <th class="px-3 py-2 text-left">Patient ID</th>
                                        <th class="px-3 py-2 text-left">Status</th>
                                        <th class="px-3 py-2 text-left">Details</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach ($result['report'] as $row)
                                        <tr>
                                            <td class="px-3 py-2">{{ $row['row'] ?? '' }}</td>
                                            <td class="px-3 py-2">{{ $row['patient_id'] ?? '' }}</td>
                                            <td class="px-3 py-2">{{ ucfirst($row['status'] ?? '') }}</td>
                                            <td class="px-3 py-2">{{ $row['message'] ?? '' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif

                    @if ($result['invoices'] !== [])
                        <div class="overflow-x-auto">
                            <h4 class="mb-2 font-semibold text-gray-900 dark:text-gray-100">Selected invoices (up to 500 rows)</h4>
                            <table class="ak-table">
                                <thead class="bg-gray-50 dark:bg-gray-900">
                                    <tr>
                                        <th class="px-3 py-2 text-left">Invoice</th>
                                        <th class="px-3 py-2 text-left">Patient</th>
                                        <th class="px-3 py-2 text-left">User</th>
                                        <th class="px-3 py-2 text-left">Driving</th>
                                        <th class="px-3 py-2 text-right">Amount</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach ($result['invoices'] as $invoice)
                                        <tr>
                                            <td class="px-3 py-2">{{ $invoice['invoice_id'] }}</td>
                                            <td class="px-3 py-2">{{ $invoice['patient_id'] }}</td>
                                            <td class="px-3 py-2">{{ $invoice['user_name'] }}</td>
                                            <td class="px-3 py-2">{{ $invoice['is_driving'] ? 'Yes' : 'No' }}</td>
                                            <td class="px-3 py-2 text-right">{{ number_format((float) $invoice['invoice_non_entitled_total'], 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </section>
            @endif
    </div>
</x-app-layout>
