<x-app-layout>
    <x-slot name="header">
        <div class="ak-head">
            <div>
                <nav class="ak-crumbs" aria-label="Breadcrumb">
                    <a href="{{ route('patient.index') }}">Patients</a><span aria-hidden="true">›</span>
                    <a href="{{ route('patient.government-import') }}">Government Excel Import</a><span aria-hidden="true">›</span>
                    <span>How it works</span>
                </nav>
                <h1 class="ak-title">How the Government Patient Import Works</h1>
                <p class="ak-sub">A guide to preparing the workbook, reviewing the random selection, and safely applying the updates.</p>
            </div>
            <div class="ak-actions">
                <a href="{{ route('patient.government-import') }}" class="ak-btn">Back to Import</a>
                @can('view patient import archive')
                    <a href="{{ route('patient-import.index') }}" class="ak-btn">Patient Import Archive</a>
                @endcan
                <a href="{{ route('patient.government-import.template') }}" class="ak-btn ak-btn-primary">Download Excel Template</a>
            </div>
        </div>
    </x-slot>

    @include('partials.access-style')

    <div class="ak-page space-y-5">
        <section class="ak-card ak-card-body space-y-3">
            <h2 class="ak-card-head">1. What this tool does</h2>
            <p class="text-sm leading-6 text-gray-700 dark:text-gray-200">
                The database query first filters and randomly selects eligible invoices and patient IDs. The Excel sheet supplies
                replacement profile information only; it must not contain patient IDs. Each valid profile is assigned to one
                randomly selected patient unless profile reuse is enabled.
            </p>
            <div class="grid grid-cols-1 gap-3 text-center text-sm font-semibold md:grid-cols-4">
                <div class="rounded-md bg-gray-100 p-3 dark:bg-gray-800">Set filters and target amount</div>
                <div class="rounded-md bg-gray-100 p-3 dark:bg-gray-800">Select eligible invoices randomly</div>
                <div class="rounded-md bg-gray-100 p-3 dark:bg-gray-800">Assign Excel profiles to patients</div>
                <div class="rounded-md bg-gray-100 p-3 dark:bg-gray-800">Analyze first, then confirm updates</div>
            </div>
        </section>

        <section class="ak-card ak-card-body space-y-3">
            <h2 class="ak-card-head">2. Import lifecycle</h2>
            <p class="text-sm leading-6 text-gray-700 dark:text-gray-200">
                Follow the flow from workbook checks through random selection. The analysis path only reports planned changes;
                the execution path writes the selected changes inside a database transaction.
            </p>
            <div class="overflow-x-auto">
                <svg viewBox="0 0 1180 620" class="w-full" role="img" aria-labelledby="gov-import-flow-title gov-import-flow-description" style="min-width: 900px">
                    <title id="gov-import-flow-title">Government patient import lifecycle</title>
                    <desc id="gov-import-flow-description">Upload and validate an Excel workbook, randomly select eligible invoices and patients, assign profiles, then either analyze without changes or confirm execution, archive original rows, update live records, and show the result.</desc>
                    <defs>
                        <marker id="gov-import-arrow" viewBox="0 0 10 10" refX="9" refY="5" markerWidth="7" markerHeight="7" orient="auto">
                            <path d="M0 0 L10 5 L0 10 z" fill="#475569" />
                        </marker>
                    </defs>
                    <style>
                        .gov-flow-box { fill: #fff; stroke: #172554; stroke-width: 2; }
                        .gov-flow-check { fill: #f1f5f9; stroke: #64748b; stroke-width: 2; }
                        .gov-flow-analysis { fill: #fef3c7; stroke: #b45309; stroke-width: 2; }
                        .gov-flow-execute { fill: #dcfce7; stroke: #15803d; stroke-width: 2; }
                        .gov-flow-error { fill: #fee2e2; stroke: #b91c1c; stroke-width: 2; }
                        .gov-flow-result { fill: #e0e7ff; stroke: #172554; stroke-width: 2; }
                        .gov-flow-line { stroke: #475569; stroke-width: 2; fill: none; marker-end: url(#gov-import-arrow); }
                        .gov-flow-branch { stroke: #475569; stroke-width: 2; fill: none; }
                        .gov-flow-text { font-size: 15px; fill: #0f172a; font-family: ui-sans-serif, system-ui, sans-serif; }
                        .gov-flow-bold { font-weight: 700; }
                        .gov-flow-small { font-size: 12px; fill: #475569; font-family: ui-sans-serif, system-ui, sans-serif; }
                    </style>

                    <rect class="gov-flow-box" x="20" y="35" width="200" height="82" rx="12" />
                    <text class="gov-flow-text gov-flow-bold" x="120" y="68" text-anchor="middle">Upload workbook</text>
                    <text class="gov-flow-small" x="120" y="90" text-anchor="middle">Set dates, filters and target</text>

                    <rect class="gov-flow-check" x="270" y="35" width="210" height="82" rx="12" />
                    <text class="gov-flow-text gov-flow-bold" x="375" y="68" text-anchor="middle">Validate profiles</text>
                    <text class="gov-flow-small" x="375" y="90" text-anchor="middle">Check fields and departments</text>

                    <rect class="gov-flow-box" x="530" y="35" width="250" height="82" rx="12" />
                    <text class="gov-flow-text gov-flow-bold" x="655" y="68" text-anchor="middle">Select randomly</text>
                    <text class="gov-flow-small" x="655" y="90" text-anchor="middle">Eligible invoices and patients</text>

                    <rect class="gov-flow-box" x="830" y="35" width="310" height="82" rx="12" />
                    <text class="gov-flow-text gov-flow-bold" x="985" y="68" text-anchor="middle">Assign profiles</text>
                    <text class="gov-flow-small" x="985" y="90" text-anchor="middle">Reuse off: once · on: balanced reuse</text>

                    <path class="gov-flow-line" d="M220 76 H266" />
                    <path class="gov-flow-line" d="M480 76 H526" />
                    <path class="gov-flow-line" d="M780 76 H826" />

                    <path class="gov-flow-line" d="M375 117 V157" />
                    <rect class="gov-flow-error" x="270" y="160" width="210" height="75" rx="12" />
                    <text class="gov-flow-text gov-flow-bold" x="375" y="189" text-anchor="middle">Errors found?</text>
                    <text class="gov-flow-small" x="375" y="211" text-anchor="middle">Review rows and fix workbook</text>

                    <path class="gov-flow-branch" d="M985 117 V157 M700 157 H985" />
                    <path class="gov-flow-line" d="M700 157 V195" />
                    <path class="gov-flow-line" d="M985 157 V195" />

                    <rect class="gov-flow-analysis" x="575" y="200" width="250" height="85" rx="12" />
                    <text class="gov-flow-text gov-flow-bold" x="700" y="234" text-anchor="middle">Analysis only</text>
                    <text class="gov-flow-small" x="700" y="257" text-anchor="middle">No database changes are saved</text>

                    <rect class="gov-flow-execute" x="860" y="200" width="250" height="85" rx="12" />
                    <text class="gov-flow-text gov-flow-bold" x="985" y="234" text-anchor="middle">Execute + confirm</text>
                    <text class="gov-flow-small" x="985" y="257" text-anchor="middle">Start one database transaction</text>

                    <path class="gov-flow-line" d="M700 285 V495 H840 V525" />
                    <path class="gov-flow-line" d="M985 285 V321" />

                    <rect class="gov-flow-check" x="860" y="325" width="250" height="70" rx="12" />
                    <text class="gov-flow-text gov-flow-bold" x="985" y="354" text-anchor="middle">Archive selected originals</text>
                    <text class="gov-flow-small" x="985" y="376" text-anchor="middle">Keep every version with a batch ID</text>

                    <path class="gov-flow-line" d="M985 395 V416" />
                    <rect class="gov-flow-execute" x="860" y="420" width="250" height="70" rx="12" />
                    <text class="gov-flow-text gov-flow-bold" x="985" y="449" text-anchor="middle">Update live records</text>
                    <text class="gov-flow-small" x="985" y="471" text-anchor="middle">Commit archive and updates together</text>
                    <path class="gov-flow-line" d="M985 490 V510 H840 V525" />

                    <rect class="gov-flow-result" x="570" y="530" width="540" height="70" rx="12" />
                    <text class="gov-flow-text gov-flow-bold" x="840" y="559" text-anchor="middle">Flash message and import report</text>
                    <text class="gov-flow-small" x="840" y="581" text-anchor="middle">Batch ID · patient IDs · amounts · errors · affected tables and columns</text>
                </svg>
            </div>
            <div class="grid grid-cols-1 gap-3 text-sm text-gray-700 dark:text-gray-200 md:grid-cols-2">
                <p class="rounded-md bg-amber-50 p-3 dark:bg-amber-950">If validation errors appear, correct the Excel rows or department names and upload the workbook again. No records are updated during analysis.</p>
                <p class="rounded-md bg-green-50 p-3 dark:bg-green-950">Execution requires both unchecking Analysis only and checking the permanent-update confirmation. If it fails, database changes roll back.</p>
            </div>
        </section>

        <section class="ak-card ak-card-body space-y-3">
            <h2 class="ak-card-head">3. Prepare the Excel workbook</h2>
            <p class="text-sm text-gray-700 dark:text-gray-200">
                Download the template and keep its column headings unchanged. The <code>#</code> column is only a sheet-row
                reference, not a patient ID. Fill one patient profile per row.
            </p>
            <div class="overflow-x-auto">
                <table class="ak-table">
                    <thead>
                        <tr>
                            <th>Excel column</th>
                            <th>What to enter</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td>#</td><td>Optional row number/reference; it is not used to identify a database patient.</td></tr>
                        <tr><td>title</td><td><code>Mr.</code> or <code>Miss</code>. Sex is inferred from this value.</td></tr>
                        <tr><td>first_name</td><td>Required; up to 60 characters.</td></tr>
                        <tr><td>last_name</td><td>Optional; blank is saved as NULL.</td></tr>
                        <tr><td>relationship_title</td><td>One of <code>H/O</code>, <code>W/O</code>, <code>S/O</code>, <code>D/O</code>, <code>M/O</code>, or <code>F/O</code>.</td></tr>
                        <tr><td>father_husband_name</td><td>Required; up to 60 characters.</td></tr>
                        <tr><td>age</td><td>Enter <code>31</code> or <code>31Y</code> for years, or <code>6m</code> for months. Do not add a separate years/months column.</td></tr>
                        <tr><td>address</td><td>Required; up to 255 characters.</td></tr>
                        <tr><td>government_department_name</td><td>Must match exactly one existing department name (case-insensitive).</td></tr>
                        <tr><td>designation</td><td>Required; up to 255 characters.</td></tr>
                        <tr><td>government_card_no</td><td>Required; up to 10 characters. Format this column as text to preserve leading zeroes.</td></tr>
                    </tbody>
                </table>
            </div>
            <p class="text-sm text-gray-700 dark:text-gray-200">
                Mobile is not taken from Excel; it is set to <code>0300-1234567</code>. DOB is calculated from the selected
                patient's existing creation date and the age in the sheet.
            </p>
        </section>

        <section class="ak-card ak-card-body space-y-3">
            <h2 class="ak-card-head">4. Choose the invoice-selection filters</h2>
            <ul class="list-inside list-disc space-y-2 text-sm leading-6 text-gray-700 dark:text-gray-200">
                <li>Start and end dates filter patient-test creation time. Both default to the first and last day of the current month.</li>
                <li>Eligible invoices contain normal, not-yet-government patient tests within the date range and meet the minimum invoice amount.</li>
                <li>The creator's eligible invoice total must meet the minimum user total. The random selection then aims to stay between the target minimum and maximum.</li>
                <li>Use the optional switches to include driving invoices, patients with admissions, or fee types excluded by default.</li>
                <li>Ignore-list switches exclude the listed user, patient, or fee-type IDs. Their text fields accept comma-separated numeric IDs.</li>
            </ul>
            <p class="rounded-md border border-amber-300 bg-amber-50 p-3 text-sm text-amber-900 dark:border-amber-700 dark:bg-amber-950 dark:text-amber-100">
                The query chooses patients from the database, not from Excel. A patient may have more than one selected invoice;
                unrelated, unselected invoices are not part of this import.
            </p>
        </section>

        <section class="ak-card ak-card-body space-y-3">
            <h2 class="ak-card-head">5. Decide how Excel profiles are assigned</h2>
            <div class="ak-grid text-sm leading-6 text-gray-700 dark:text-gray-200">
                <div class="rounded-md border border-gray-200 p-4 dark:border-gray-700">
                    <h3 class="font-semibold text-gray-900 dark:text-gray-100">Reuse unchecked (default)</h3>
                    <p class="mt-2">Each valid Excel row is used at most once. Extra Excel rows are reported as unused. If more patients are selected than there are valid rows, the remaining patients keep their demographics and receive a randomly chosen department and a unique generated card number.</p>
                    <p class="mt-2">Duplicate card numbers in the workbook are rejected in this mode.</p>
                </div>
                <div class="rounded-md border border-gray-200 p-4 dark:border-gray-700">
                    <h3 class="font-semibold text-gray-900 dark:text-gray-100">Reuse checked</h3>
                    <p class="mt-2">Valid rows are shuffled and distributed as evenly as possible across all selected patients. If there are fewer rows than patients, profile fields and card numbers repeat; duplicate workbook card numbers are allowed.</p>
                </div>
            </div>
            <p class="text-sm text-gray-700 dark:text-gray-200">
                Department names that are missing or match duplicate database records are reported as errors. Fix those rows or
                department records and analyze the workbook again before executing.
            </p>
        </section>

        <section class="ak-card ak-card-body space-y-3">
            <h2 class="ak-card-head">6. Analyze, review, and execute</h2>
            <ol class="list-inside list-decimal space-y-2 text-sm leading-6 text-gray-700 dark:text-gray-200">
                <li>Upload the completed workbook and set the filters and assignment options.</li>
                <li>Leave <b>Analysis only</b> checked and submit. This performs selection and validation without saving database changes.</li>
                <li>Review the flash message, row errors, selected patient IDs, Excel row assignments, before/after amounts, and affected-table report.</li>
                <li>To apply the import, upload the workbook again with the same options, uncheck <b>Analysis only</b>, check the permanent-update confirmation, and submit.</li>
            </ol>
            <p class="rounded-md border border-red-300 bg-red-50 p-3 text-sm text-red-900 dark:border-red-700 dark:bg-red-950 dark:text-red-100">
                Execution permanently updates the selected records. Only a Super-Admin can open this page or execute an import.
                If execution fails, the transaction is rolled back and an error is shown.
            </p>
        </section>

        <section class="ak-card ak-card-body space-y-3">
            <h2 class="ak-card-head">7. Understand the amounts and database impact</h2>
            <p class="text-sm leading-6 text-gray-700 dark:text-gray-200">
                Amounts in the patient assignment report are totals for that patient's selected eligible patient tests only.
                The report has separate <b>Amount before import</b> and <b>Amount after import</b> columns. During analysis, the after amount remains the
                same because nothing is saved. After execution, the selected amount becomes zero; other invoices and tests not
                selected by the query are not included in that patient's displayed amount.
            </p>
            <div class="overflow-x-auto">
                <table class="ak-table">
                    <thead>
                        <tr><th>Table</th><th>What changes</th></tr>
                    </thead>
                    <tbody>
                        <tr><td>patients</td><td>All selected patients get the government flag, department, card number, and default mobile. Excel-assigned patients also get profile, age, DOB, sex, address, and designation fields.</td></tr>
                        <tr><td>invoices</td><td>Government flag, department, card number, total_amount, hif_amount, and govt_amount.</td></tr>
                        <tr><td>patient_tests</td><td>Government flag, department, card number, total_amount, hif_amount, and govt_amount for eligible tests in the selected invoices.</td></tr>
                    </tbody>
                </table>
            </div>
            <p class="text-sm text-gray-700 dark:text-gray-200">
                The result also reports selected invoice/patient counts, the total amount before and after, validation errors,
                patient IDs, and the affected row counts and columns. The import form and help page are Super-Admin-only.
            </p>
        </section>

        <section class="ak-card ak-card-body space-y-3">
            <h2 class="ak-card-head">8. Pre-import archive and history</h2>
            <p class="text-sm leading-6 text-gray-700 dark:text-gray-200">
                When execution is confirmed, the current selected rows are copied into archive tables before any live record
                changes. The archive and live updates run in the same transaction, so an error rolls both back. Analysis-only
                runs never write to the archive.
            </p>
            <div class="overflow-x-auto">
                <table class="ak-table">
                    <thead>
                        <tr><th>Archive table</th><th>Source table</th><th>What is saved</th></tr>
                    </thead>
                    <tbody>
                        <tr><td>patients_import</td><td>patients</td><td>Complete selected patient rows before profile/government fields are changed.</td></tr>
                        <tr><td>invoices_improt</td><td>invoices</td><td>Complete selected invoice rows before their government flags and amounts are updated.</td></tr>
                        <tr><td>patient_tests_import</td><td>patient_tests</td><td>Complete eligible patient-test rows before their government flags and amounts are updated.</td></tr>
                    </tbody>
                </table>
            </div>
            <p class="text-sm leading-6 text-gray-700 dark:text-gray-200">
                Each archive row keeps the source record's original <code>id</code> and has an additional
                <code>import_batch_id</code> UUID. IDs are not unique in the archive tables so the same source record can be
                saved again on a later execution without deleting its earlier snapshot. Use the batch ID shown in the success
                message to find rows from one execution across all three tables. These are complete copies of the source
                rows affected by the import, not partial field copies. They stay available until an authorized user
                restores that patient snapshot from the Patient Import Archive.
            </p>
        </section>

        <section class="ak-card ak-card-body space-y-3">
            <h2 class="ak-card-head">9. Archived patient search and access lifecycle</h2>
            <p class="text-sm leading-6 text-gray-700 dark:text-gray-200">
                Every non-Super-Admin user gets the newest archived patient version by original patient ID in patient
                search and invoice screens. If there is no snapshot for a patient or invoice, the live row is used.
                Super-Admin always sees live patient, invoice, and patient-test data.
            </p>
            <div class="overflow-x-auto">
                <svg viewBox="0 0 1000 300" class="w-full" role="img" aria-labelledby="archive-search-flow-title archive-search-flow-description" style="min-width: 760px">
                    <title id="archive-search-flow-title">Role-based patient search lifecycle</title>
                    <desc id="archive-search-flow-description">All users except Super-Admin see the newest patient archive snapshot when available, otherwise live patient data. Super-Admin always uses live patient data and can browse and restore archived snapshots.</desc>
                    <defs>
                        <marker id="archive-search-arrow" viewBox="0 0 10 10" refX="9" refY="5" markerWidth="7" markerHeight="7" orient="auto">
                            <path d="M0 0 L10 5 L0 10 z" fill="#475569" />
                        </marker>
                    </defs>
                    <style>
                        .archive-flow-box { fill: #fff; stroke: #172554; stroke-width: 2; }
                        .archive-flow-green { fill: #dcfce7; stroke: #15803d; stroke-width: 2; }
                        .archive-flow-amber { fill: #fef3c7; stroke: #b45309; stroke-width: 2; }
                        .archive-flow-blue { fill: #e0e7ff; stroke: #172554; stroke-width: 2; }
                        .archive-flow-line { stroke: #475569; stroke-width: 2; fill: none; marker-end: url(#archive-search-arrow); }
                        .archive-flow-branch { stroke: #475569; stroke-width: 2; fill: none; }
                        .archive-flow-text { font-size: 15px; fill: #0f172a; font-family: ui-sans-serif, system-ui, sans-serif; }
                        .archive-flow-bold { font-weight: 700; }
                        .archive-flow-small { font-size: 12px; fill: #475569; font-family: ui-sans-serif, system-ui, sans-serif; }
                    </style>

                    <rect class="archive-flow-box" x="30" y="35" width="220" height="70" rx="12" />
                    <text class="archive-flow-text archive-flow-bold" x="140" y="65" text-anchor="middle">Search Patients</text>
                    <text class="archive-flow-small" x="140" y="87" text-anchor="middle">ID, name, mobile and filters</text>

                    <rect class="archive-flow-amber" x="330" y="35" width="270" height="70" rx="12" />
                    <text class="archive-flow-text archive-flow-bold" x="465" y="61" text-anchor="middle">Choose search source</text>
                    <text class="archive-flow-small" x="465" y="84" text-anchor="middle">Role and snapshot availability</text>
                    <path class="archive-flow-line" d="M250 70 H326" />

                    <rect class="archive-flow-green" x="690" y="20" width="270" height="85" rx="12" />
                    <text class="archive-flow-text archive-flow-bold" x="825" y="52" text-anchor="middle">Newest archived version</text>
                    <text class="archive-flow-small" x="825" y="75" text-anchor="middle">All other users, if present</text>
                    <path class="archive-flow-line" d="M600 55 H686" />

                    <rect class="archive-flow-blue" x="690" y="145" width="270" height="75" rx="12" />
                    <text class="archive-flow-text archive-flow-bold" x="825" y="175" text-anchor="middle">Live patient row</text>
                    <text class="archive-flow-small" x="825" y="198" text-anchor="middle">Super-Admin, or no snapshot</text>
                    <path class="archive-flow-line" d="M465 105 V180 H686" />

                    <rect class="archive-flow-box" x="330" y="190" width="270" height="75" rx="12" />
                    <text class="archive-flow-text archive-flow-bold" x="465" y="220" text-anchor="middle">Permissioned archive page</text>
                    <text class="archive-flow-small" x="465" y="243" text-anchor="middle">/patient-import · search and restore</text>
                    <path class="archive-flow-branch" d="M140 105 V225 H326" />
                </svg>
            </div>
            <div class="grid grid-cols-1 gap-3 text-sm text-gray-700 dark:text-gray-200 md:grid-cols-2">
                <p class="rounded-md bg-amber-50 p-3 dark:bg-amber-950">Every non-Super-Admin patient search, invoice history, invoice page, and print view resolves archived/live records by original ID. The newest <code>archived_at</code> snapshot is shown when present; otherwise the live row is used. Super-Admin always sees live data.</p>
                <p class="rounded-md bg-gray-100 p-3 dark:bg-gray-800">The searchable <code>/patient-import</code> page and restore action require the <code>view patient import archive</code> permission. The migration grants this permission to Super-Admin only; other users need it explicitly assigned.</p>
            </div>
            <p class="rounded-md bg-indigo-50 p-3 text-sm leading-6 text-indigo-900 dark:bg-indigo-950 dark:text-indigo-100">
                Government Excel Import is separately restricted to Super-Admin and is enabled only when
                <code>IMPORT_ACCESS=true</code> is configured. When disabled, its navigation button is hidden and all
                import, help, template, and execution URLs return a forbidden response, including for Super-Admin.
            </p>
            <ol class="grid grid-cols-1 gap-3 text-sm leading-6 text-gray-700 dark:text-gray-200 md:grid-cols-3">
                <li class="rounded-md border border-gray-200 p-4 dark:border-gray-700">
                    <strong class="block text-gray-900 dark:text-white">1. Save complete source rows</strong>
                    A confirmed import copies every source column for its affected patient, invoice, and patient-test rows
                    into <code>patients_import</code>, <code>invoices_improt</code>, and
                    <code>patient_tests_import</code>, tagged with the same batch ID.
                </li>
                <li class="rounded-md border border-gray-200 p-4 dark:border-gray-700">
                    <strong class="block text-gray-900 dark:text-white">2. Select a patient snapshot</strong>
                    A user with the archive permission searches and chooses Restore this version for the exact patient ID and
                    import batch. A confirmation prompt is shown before any changes.
                </li>
                <li class="rounded-md border border-gray-200 p-4 dark:border-gray-700">
                    <strong class="block text-gray-900 dark:text-white">3. Restore atomically and remove that snapshot</strong>
                    In one database transaction, all archived columns are written back to the original IDs (or recreated
                    if a row is missing). Only that patient's rows for the selected batch are then deleted from all
                    three archive tables. Other patients and snapshots are kept; on failure, the restore and deletion
                    are rolled back together.
                </li>
            </ol>
        </section>
    </div>
</x-app-layout>
