@section('custom_header')
    <style>
        .ssp-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid black;
            font-size: 11px;
            line-height: 1.2;
        }

        .ssp-table th,
        .ssp-table td {
            border: 1px solid black;
            padding: 3px 4px;
            word-wrap: break-word;
        }

        .summary-card {
            background: linear-gradient(135deg, #065f46 0%, #047857 100%);
            color: white;
            border-radius: 0.75rem;
            padding: 1rem 1.25rem;
        }

        @media print {
            @page {
                size: landscape;
                margin: 5mm;
            }

            .no-print {
                display: none !important;
            }

            body {
                margin: 0;
                padding: 0;
            }

            .max-w-7xl {
                max-width: 100% !important;
                width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            .ssp-table {
                font-size: 9px !important;
                width: 100% !important;
                table-layout: fixed;
            }

            .ssp-table th,
            .ssp-table td {
                padding: 2px !important;
            }

            .summary-card {
                background: #065f46 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
@endsection

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight inline-block">
            Sehat Sahulat Program - Insurance Claims Report
        </h2>
        <div class="flex justify-center items-center float-right no-print">
            <button onclick="window.print()"
                class="flex items-center px-4 py-2 text-gray-600 bg-white border rounded-lg focus:outline-none hover:bg-gray-100 transition-colors duration-200 transform ml-2"
                title="Print Report">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z">
                    </path>
                </svg>
            </button>

            <a href="javascript:;" id="toggle"
                class="flex items-center px-4 py-2 text-gray-600 bg-white border rounded-lg focus:outline-none hover:bg-gray-100 transition-colors duration-200 transform ml-2"
                title="Search Filters">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                </svg>
                <span class="hidden md:inline-block ml-2" style="font-size: 14px;">Search Filters</span>
            </a>
        </div>
    </x-slot>

    {{-- ==================== FILTERS PANEL ==================== --}}
    <div class="max-w-7xl mx-auto mt-6 px-4 sm:px-6 lg:px-8 no-print" style="display: none" id="filters">
        <div class="rounded-xl p-4 bg-white shadow-lg">
            <form action="{{ route('reports.ssp.claims') }}" method="GET">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">

                    {{-- Date Range --}}
                    <div>
                        <label for="start_date" class="block text-gray-700 font-bold mb-2">Start Date</label>
                        <input type="date" name="start_date" value="{{ request('start_date', $start_date) }}"
                            id="start_date"
                            class="w-full px-3 py-2 border rounded-md text-gray-700 focus:outline-none focus:border-blue-500">
                    </div>

                    <div>
                        <label for="end_date" class="block text-gray-700 font-bold mb-2">End Date</label>
                        <input type="date" name="end_date" value="{{ request('end_date', $end_date) }}" id="end_date"
                            class="w-full px-3 py-2 border rounded-md text-gray-700 focus:outline-none focus:border-blue-500">
                    </div>

                    {{-- Report Type --}}
                    <div>
                        <label for="report_type" class="block text-gray-700 font-bold mb-2">Report Type</label>
                        <select name="report_type" id="report_type"
                            class="w-full px-3 py-2 border rounded-md text-gray-700 focus:outline-none focus:border-blue-500">
                            <option value="all" {{ request('report_type', 'all') == 'all' ? 'selected' : '' }}>All (OPD +
                                IPD)</option>
                            <option value="opd" {{ request('report_type') == 'opd' ? 'selected' : '' }}>OPD Only</option>
                            <option value="ipd" {{ request('report_type') == 'ipd' ? 'selected' : '' }}>IPD Only</option>
                        </select>
                    </div>

                    {{-- Department --}}
                    <div>
                        <label for="department_id" class="block text-gray-700 font-bold mb-2">Department</label>
                        <select name="department_id" id="department_id"
                            class="w-full px-3 py-2 border rounded-md text-gray-700 focus:outline-none focus:border-blue-500">
                            <option value="">All Departments</option>
                            @foreach ($departments as $dept)
                                <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>
                                    {{ $dept->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Fee Category --}}
                    <div>
                        <label for="fee_category_id" class="block text-gray-700 font-bold mb-2">Fee Category</label>
                        <select name="fee_category_id" id="fee_category_id"
                            class="w-full px-3 py-2 border rounded-md text-gray-700 focus:outline-none focus:border-blue-500">
                            <option value="">All Categories</option>
                            @foreach ($feeCategories as $cat)
                                <option value="{{ $cat->id }}" {{ request('fee_category_id') == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Patient ID --}}
                    <div>
                        <label for="patient_id" class="block text-gray-700 font-bold mb-2">Patient ID</label>
                        <input type="text" name="patient_id" value="{{ request('patient_id') }}" id="patient_id"
                            placeholder="e.g. 175122"
                            class="w-full px-3 py-2 border rounded-md text-gray-700 focus:outline-none focus:border-blue-500">
                    </div>

                    {{-- Patient Name --}}
                    <div>
                        <label for="patient_name" class="block text-gray-700 font-bold mb-2">Patient Name</label>
                        <input type="text" name="patient_name" value="{{ request('patient_name') }}" id="patient_name"
                            placeholder="Search by name..."
                            class="w-full px-3 py-2 border rounded-md text-gray-700 focus:outline-none focus:border-blue-500">
                    </div>

                    {{-- Sex/Gender --}}
                    <div>
                        <label for="sex" class="block text-gray-700 font-bold mb-2">Sex / Gender</label>
                        <select name="sex" id="sex"
                            class="w-full px-3 py-2 border rounded-md text-gray-700 focus:outline-none focus:border-blue-500">
                            <option value="">All</option>
                            <option value="1" {{ request('sex') === '1' ? 'selected' : '' }}>Male</option>
                            <option value="0" {{ request('sex') === '0' ? 'selected' : '' }}>Female</option>
                        </select>
                    </div>

                    {{-- Entered By (User) --}}
                    <div>
                        <label for="user_id" class="block text-gray-700 font-bold mb-2">Entered By</label>
                        <select name="user_id" id="user_id"
                            class="w-full px-3 py-2 border rounded-md text-gray-700 focus:outline-none focus:border-blue-500">
                            <option value="">All Users</option>
                            @foreach ($users as $user)
                                <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Ward (for IPD) --}}
                    <div>
                        <label for="unit_ward" class="block text-gray-700 font-bold mb-2">Ward / Unit (IPD)</label>
                        <select name="unit_ward" id="unit_ward"
                            class="w-full px-3 py-2 border rounded-md text-gray-700 focus:outline-none focus:border-blue-500">
                            <option value="">All Wards</option>
                            @foreach ($admissionWards as $ward)
                                <option value="{{ $ward->name }}" {{ request('unit_ward') == $ward->name ? 'selected' : '' }}>
                                    {{ $ward->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Fee Status --}}
                    <div>
                        <label for="fee_status" class="block text-gray-700 font-bold mb-2">Fee Status</label>
                        <select name="fee_status" id="fee_status"
                            class="w-full px-3 py-2 border rounded-md text-gray-700 focus:outline-none focus:border-blue-500">
                            <option value="">All</option>
                            <option value="Normal" {{ request('fee_status') == 'Normal' ? 'selected' : '' }}>Normal
                            </option>
                            <option value="Return" {{ request('fee_status') == 'Return' ? 'selected' : '' }}>Return
                            </option>
                        </select>
                    </div>

                    {{-- Submit --}}
                    <div class="flex items-end">
                        <button
                            class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2 px-6 rounded focus:outline-none focus:shadow-outline"
                            type="submit">
                            Search
                        </button>
                        <a href="{{ route('reports.ssp.claims') }}"
                            class="ml-2 bg-gray-400 hover:bg-gray-500 text-white font-bold py-2 px-4 rounded focus:outline-none">
                            Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- ==================== REPORT HEADER ==================== --}}
            <div class="bg-white overflow-x-auto p-4 mb-6">
                <div class="grid grid-cols-3 gap-4">
                    <div></div>
                    <div class="flex items-center justify-center">
                        <img src="{{ \Illuminate\Support\Facades\Storage::url('Aimsa8 copy 2.png') }}" alt="Logo"
                            style="width: 300px;">
                    </div>
                    <div class="flex flex-col items-end">
                        @php
                            $dateLabel = \Carbon\Carbon::parse($start_date)->format('d-M-Y') . ' to ' . \Carbon\Carbon::parse($end_date)->format('d-M-Y');
                            $qrData = "SSP Claims Report\nDate: $dateLabel\nAIMS, Muzaffarabad, AJK";
                        @endphp
                        {!! DNS2D::getBarcodeSVG($qrData, 'QRCODE', 3, 3) !!}
                    </div>
                </div>

                <p class="text-center font-extrabold mb-2 mt-4">
                    Sehat Sahulat Program (SSP) - Insurance Claims Report
                    <br>
                    From {{ \Carbon\Carbon::parse($start_date)->format('d-M-Y') }} to
                    {{ \Carbon\Carbon::parse($end_date)->format('d-M-Y') }}
                    <br>
                    <span class="text-sm font-normal text-gray-500">Software Developed By SeeChange Innovative - Contact
                        No: 0300-8169924</span>
                </p>

                {{-- Applied Filters Display --}}
                @if(request()->hasAny(['report_type', 'department_id', 'fee_category_id', 'patient_id', 'patient_name', 'sex', 'user_id', 'unit_ward', 'fee_status']))
                    <div class="mb-4 p-3 bg-emerald-50 border border-emerald-200 rounded no-print">
                        <h3 class="font-bold text-emerald-800 mb-1">Applied Filters:</h3>
                        <ul class="list-disc list-inside text-sm text-emerald-700">
                            @if(request('report_type') && request('report_type') !== 'all')
                                <li>Report Type: {{ strtoupper(request('report_type')) }}</li>
                            @endif
                            @if(request('department_id'))
                                <li>Department: {{ \App\Models\Department::find(request('department_id'))->name ?? 'N/A' }}</li>
                            @endif
                            @if(request('fee_category_id'))
                                <li>Fee Category: {{ \App\Models\FeeCategory::find(request('fee_category_id'))->name ?? 'N/A' }}
                                </li>
                            @endif
                            @if(request('patient_id'))
                                <li>Patient ID: {{ request('patient_id') }}</li>
                            @endif
                            @if(request('patient_name'))
                                <li>Patient Name: {{ request('patient_name') }}</li>
                            @endif
                            @if(request('sex') !== null && request('sex') !== '')
                                <li>Gender: {{ request('sex') == '1' ? 'Male' : 'Female' }}</li>
                            @endif
                            @if(request('user_id'))
                                <li>Entered By: {{ \App\Models\User::find(request('user_id'))->name ?? 'N/A' }}</li>
                            @endif
                            @if(request('unit_ward'))
                                <li>Ward/Unit: {{ request('unit_ward') }}</li>
                            @endif
                            @if(request('fee_status'))
                                <li>Fee Status: {{ request('fee_status') }}</li>
                            @endif
                        </ul>
                    </div>
                @endif
            </div>

            {{-- ==================== SUMMARY CARDS ==================== --}}
            <div class="grid grid-cols-1 md:grid-cols-3 xl:grid-cols-6 gap-4 mb-6">
                <div class="summary-card">
                    <div class="text-xs uppercase tracking-wide opacity-80">OPD Patients</div>
                    <div class="text-2xl font-bold mt-1">{{ number_format($summary['opd_total_patients']) }}</div>
                    <div class="text-xs opacity-70 mt-1">{{ number_format($summary['opd_total_chits']) }} chits</div>
                </div>
                <div class="summary-card">
                    <div class="text-xs uppercase tracking-wide opacity-80">OPD Actual Amount</div>
                    <div class="text-2xl font-bold mt-1">Rs. {{ number_format($summary['opd_actual_amount'], 2) }}</div>
                    <div class="text-xs opacity-70 mt-1">Charged: Rs.
                        {{ number_format($summary['opd_charged_amount'], 2) }}</div>
                </div>
                <div class="summary-card">
                    <div class="text-xs uppercase tracking-wide opacity-80">IPD Patients</div>
                    <div class="text-2xl font-bold mt-1">{{ number_format($summary['ipd_total_patients']) }}</div>
                    <div class="text-xs opacity-70 mt-1">{{ number_format($summary['ipd_total_invoices']) }} invoices /
                        {{ number_format($summary['ipd_total_tests']) }} tests</div>
                </div>
                <div class="summary-card">
                    <div class="text-xs uppercase tracking-wide opacity-80">IPD Actual Amount</div>
                    <div class="text-2xl font-bold mt-1">Rs. {{ number_format($summary['ipd_actual_amount'], 2) }}</div>
                    <div class="text-xs opacity-70 mt-1">Charged: Rs.
                        {{ number_format($summary['ipd_charged_amount'], 2) }}</div>
                </div>
                <div class="summary-card" style="background: linear-gradient(135deg, #7c2d12 0%, #9a3412 100%);">
                    <div class="text-xs uppercase tracking-wide opacity-80">Grand Actual Total</div>
                    <div class="text-2xl font-bold mt-1">Rs. {{ number_format($summary['grand_actual_amount'], 2) }}
                    </div>
                    <div class="text-xs opacity-70 mt-1">Charged: Rs.
                        {{ number_format($summary['grand_charged_amount'], 2) }}</div>
                </div>
                <div class="summary-card" style="background: linear-gradient(135deg, #1e3a5f 0%, #2563eb 100%);">
                    <div class="text-xs uppercase tracking-wide opacity-80">Claimable Amount</div>
                    <div class="text-2xl font-bold mt-1">Rs. {{ number_format($summary['grand_claimable_amount'], 2) }}
                    </div>
                    <div class="text-xs opacity-70 mt-1">Actual - Charged</div>
                </div>
            </div>

            {{-- ==================== OPD CHITS TABLE ==================== --}}
            @if(request('report_type', 'all') !== 'ipd')
                <div class="bg-white overflow-x-auto p-4 mb-6">
                    <h3 class="text-lg font-bold mb-3 text-emerald-800 border-b pb-2">OPD - Chit Details (SSP Patients)</h3>
                    <div class="overflow-x-auto">
                        <table class="ssp-table">
                            <thead>
                                <tr class="bg-emerald-50">
                                    <th class="px-2 py-2">S.No</th>
                                    <th class="px-2 py-2">Date</th>
                                    <th class="px-2 py-2">Chit #</th>
                                    <th class="px-2 py-2">Patient ID</th>
                                    <th class="px-2 py-2">SS Patient ID</th>
                                    <th class="px-2 py-2">SS Visit No</th>
                                    <th class="px-2 py-2">Patient Name</th>
                                    <th class="px-2 py-2">Age/Sex</th>
                                    <th class="px-2 py-2">Department</th>
                                    <th class="px-2 py-2">Fee Type</th>
                                    <th class="px-2 py-2">Charged (Rs.)</th>
                                    <th class="px-2 py-2">Actual Fee (Rs.)</th>
                                    <th class="px-2 py-2">Entered By</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($opdChits as $chit)
                                    <tr class="hover:bg-gray-50">
                                        <td class="text-center px-2 py-1">{{ $loop->iteration }}</td>
                                        <td class="text-center px-2 py-1">
                                            {{ \Carbon\Carbon::parse($chit->issued_date)->format('d-M-Y') }}</td>
                                        <td class="text-center px-2 py-1">{{ $chit->id }}</td>
                                        <td class="text-center px-2 py-1">{{ $chit->patient_id }}</td>
                                        <td class="text-center px-2 py-1 text-blue-700 font-semibold">{{ $chit->patient->sehat_sahulat_patient_id ?? '-' }}</td>
                                        <td class="text-center px-2 py-1 text-blue-700 font-semibold">{{ $chit->sehat_sahulat_visit_no ?? $chit->patient->sehat_sahulat_visit_no ?? '-' }}</td>
                                        <td class="px-2 py-1">
                                            {{ $chit->patient->title . '. ' . $chit->patient->first_name . ' ' . $chit->patient->last_name }}
                                            @if($chit->patient->father_husband_name)
                                                <br><span class="text-gray-500">{{ $chit->patient->relationship_title }}
                                                    {{ $chit->patient->father_husband_name }}</span>
                                            @endif
                                        </td>
                                        <td class="text-center px-2 py-1">{{ $chit->patient->age }}
                                            {{ $chit->patient->years_months }} / {{ $chit->patient->sex == 1 ? 'M' : 'F' }}</td>
                                        <td class="px-2 py-1">{{ $chit->department->name ?? '-' }}</td>
                                        <td class="px-2 py-1">{{ $chit->fee_type->type ?? '-' }}</td>
                                        <td class="text-right px-2 py-1">{{ number_format($chit->amount, 2) }}</td>
                                        <td class="text-right px-2 py-1 font-bold text-emerald-700">
                                            {{ number_format($chit->actual_amount, 2) }}</td>
                                        <td class="px-2 py-1">{{ $chit->user->name ?? '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="13" class="text-center px-4 py-4 text-gray-500">
                                            No OPD chits found for SSP patients in the selected date range.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot>
                                <tr class="bg-emerald-50 font-bold">
                                    <td colspan="10" class="text-right px-2 py-2">OPD Totals ({{ $opdChits->count() }}
                                        chits):</td>
                                    <td class="text-right px-2 py-2">Rs. {{ number_format($opdChits->sum('amount'), 2) }}
                                    </td>
                                    <td class="text-right px-2 py-2 text-emerald-700">Rs.
                                        {{ number_format($opdChits->sum('actual_amount'), 2) }}</td>
                                    <td class="px-2 py-2"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            @endif

            {{-- ==================== IPD INVOICES TABLE ==================== --}}
            @if(request('report_type', 'all') !== 'opd')
                <div class="bg-white overflow-x-auto p-4 mb-6">
                    <h3 class="text-lg font-bold mb-3 text-emerald-800 border-b pb-2">IPD - Invoice Details (SSP Patients)
                    </h3>
                    <div class="overflow-x-auto">
                        <table class="ssp-table">
                            <thead>
                                <tr class="bg-emerald-50">
                                    <th class="px-2 py-2">S.No</th>
                                    <th class="px-2 py-2">Date</th>
                                    <th class="px-2 py-2">Invoice #</th>
                                    <th class="px-2 py-2">Patient ID</th>
                                    <th class="px-2 py-2">SS Patient ID</th>
                                    <th class="px-2 py-2">SS Visit No</th>
                                    <th class="px-2 py-2">Patient Name</th>
                                    <th class="px-2 py-2">Age/Sex</th>
                                    <th class="px-2 py-2">Ward</th>
                                    <th class="px-2 py-2">Tests</th>
                                    <th class="px-2 py-2">Charged (Rs.)</th>
                                    <th class="px-2 py-2">Actual Total (Rs.)</th>
                                    <th class="px-2 py-2">Entered By</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($ipdInvoices as $invoice)
                                    <tr class="hover:bg-gray-50">
                                        <td class="text-center px-2 py-1">{{ $loop->iteration }}</td>
                                        <td class="text-center px-2 py-1">
                                            {{ \Carbon\Carbon::parse($invoice->created_at)->format('d-M-Y') }}</td>
                                        <td class="text-center px-2 py-1">{{ $invoice->id }}</td>
                                        <td class="text-center px-2 py-1">{{ $invoice->patient_id }}</td>
                                        <td class="text-center px-2 py-1 text-blue-700 font-semibold">{{ $invoice->patient->sehat_sahulat_patient_id ?? '-' }}</td>
                                        <td class="text-center px-2 py-1 text-blue-700 font-semibold">{{ $invoice->patient->sehat_sahulat_visit_no ?? '-' }}</td>
                                        <td class="px-2 py-1">
                                            {{ $invoice->patient->title . '. ' . $invoice->patient->first_name . ' ' . $invoice->patient->last_name }}
                                            @if($invoice->patient->father_husband_name)
                                                <br><span class="text-gray-500">{{ $invoice->patient->relationship_title }}
                                                    {{ $invoice->patient->father_husband_name }}</span>
                                            @endif
                                        </td>
                                        <td class="text-center px-2 py-1">{{ $invoice->patient->age }}
                                            {{ $invoice->patient->years_months }} /
                                            {{ $invoice->patient->sex == 1 ? 'M' : 'F' }}</td>
                                        <td class="px-2 py-1">{{ $invoice->admission->unit_ward ?? '-' }}</td>
                                        <td class="px-2 py-1">
                                            @foreach($invoice->patient_test as $pt)
                                                <div class="text-xs {{ $pt->status === 'Return' ? 'text-red-600' : '' }}">
                                                    {{ $pt->fee_type->type ?? '-' }}
                                                    <span
                                                        class="text-gray-400">({{ number_format($pt->actual_total_amount, 2) }})</span>
                                                    @if($pt->status === 'Return')
                                                        <span class="text-red-500 text-xs">[Return]</span>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </td>
                                        <td class="text-right px-2 py-1">{{ number_format($invoice->total_amount, 2) }}</td>
                                        <td class="text-right px-2 py-1 font-bold text-emerald-700">
                                            {{ number_format($invoice->actual_total_amount, 2) }}</td>
                                        <td class="px-2 py-1">{{ $invoice->user->name ?? '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="13" class="text-center px-4 py-4 text-gray-500">
                                            No IPD invoices found for SSP patients in the selected date range.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot>
                                <tr class="bg-emerald-50 font-bold">
                                    <td colspan="10" class="text-right px-2 py-2">IPD Totals ({{ $ipdInvoices->count() }}
                                        invoices):</td>
                                    <td class="text-right px-2 py-2">Rs.
                                        {{ number_format($ipdInvoices->sum('total_amount'), 2) }}</td>
                                    <td class="text-right px-2 py-2 text-emerald-700">Rs.
                                        {{ number_format($ipdInvoices->sum('actual_total_amount'), 2) }}</td>
                                    <td class="px-2 py-2"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            @endif

            {{-- ==================== GRAND TOTAL SUMMARY TABLE ==================== --}}
            <div class="bg-white overflow-x-auto p-4 mb-6">
                <h3 class="text-lg font-bold mb-3 text-emerald-800 border-b pb-2">Grand Summary - Claimable Amounts</h3>
                <table class="ssp-table">
                    <thead>
                        <tr class="bg-emerald-50">
                            <th class="px-4 py-2">Category</th>
                            <th class="px-4 py-2">Total Patients</th>
                            <th class="px-4 py-2">Total Records</th>
                            <th class="px-4 py-2">Charged Amount (Rs.)</th>
                            <th class="px-4 py-2">Actual Amount (Rs.)</th>
                            <th class="px-4 py-2">Claimable Amount (Rs.)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(request('report_type', 'all') !== 'ipd')
                            <tr>
                                <td class="px-4 py-2 font-bold">OPD (Chits)</td>
                                <td class="text-center px-4 py-2">{{ number_format($summary['opd_total_patients']) }}</td>
                                <td class="text-center px-4 py-2">{{ number_format($summary['opd_total_chits']) }}</td>
                                <td class="text-right px-4 py-2">{{ number_format($summary['opd_charged_amount'], 2) }}</td>
                                <td class="text-right px-4 py-2">{{ number_format($summary['opd_actual_amount'], 2) }}</td>
                                <td class="text-right px-4 py-2 font-bold text-emerald-700">
                                    {{ number_format($summary['opd_actual_amount'] - $summary['opd_charged_amount'], 2) }}
                                </td>
                            </tr>
                        @endif
                        @if(request('report_type', 'all') !== 'opd')
                            <tr>
                                <td class="px-4 py-2 font-bold">IPD (Invoices)</td>
                                <td class="text-center px-4 py-2">{{ number_format($summary['ipd_total_patients']) }}</td>
                                <td class="text-center px-4 py-2">{{ number_format($summary['ipd_total_invoices']) }}</td>
                                <td class="text-right px-4 py-2">{{ number_format($summary['ipd_charged_amount'], 2) }}</td>
                                <td class="text-right px-4 py-2">{{ number_format($summary['ipd_actual_amount'], 2) }}</td>
                                <td class="text-right px-4 py-2 font-bold text-emerald-700">
                                    {{ number_format($summary['ipd_actual_amount'] - $summary['ipd_charged_amount'], 2) }}
                                </td>
                            </tr>
                        @endif
                    </tbody>
                    <tfoot>
                        <tr class="bg-emerald-100 font-bold text-lg">
                            <td class="px-4 py-3">Grand Total</td>
                            <td class="text-center px-4 py-3">
                                {{ number_format($summary['opd_total_patients'] + $summary['ipd_total_patients']) }}
                            </td>
                            <td class="text-center px-4 py-3">
                                {{ number_format($summary['opd_total_chits'] + $summary['ipd_total_invoices']) }}</td>
                            <td class="text-right px-4 py-3">Rs.
                                {{ number_format($summary['grand_charged_amount'], 2) }}</td>
                            <td class="text-right px-4 py-3">Rs. {{ number_format($summary['grand_actual_amount'], 2) }}
                            </td>
                            <td class="text-right px-4 py-3 text-emerald-800">Rs.
                                {{ number_format($summary['grand_claimable_amount'], 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

        </div>
    </div>

    @section('custom_script')
        <script>
            const targetDiv = document.getElementById("filters");
            const btn = document.getElementById("toggle");
            btn.onclick = function () {
                if (targetDiv.style.display !== "none") {
                    targetDiv.style.display = "none";
                } else {
                    targetDiv.style.display = "block";
                }
            };

            // Auto-show filters if any filter is applied
            @if(request()->hasAny(['report_type', 'department_id', 'fee_category_id', 'patient_id', 'patient_name', 'sex', 'user_id', 'unit_ward', 'fee_status']) || request()->has('start_date'))
                targetDiv.style.display = "block";
            @endif
        </script>
    @endsection
</x-app-layout>