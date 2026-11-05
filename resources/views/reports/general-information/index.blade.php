@section('custom_header')
    <style>
        @media print {
            .print-table {
                width: 100%;
                max-width: 100%;
                transform-origin: top left; /* Origin for scaling */
            }

            .print-table th, .print-table td {
                font-size: 10px !important;
                padding: 2px; /* Reduced padding */
                word-break: break-word; /* To prevent text overflow */
            }

            @page {
                font-size: 10px !important;
                size: landscape; /* Allow auto page size */
                margin: 10mm;
            }
        }
    </style>
@endsection
@php $filter = request('filter', []); @endphp
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Fee List
            <div class="flex justify-center items-center float-right">
                <div class="flex justify-center items-center float-right">
                    <button onclick="window.print()" class="flex items-center px-4 py-2 text-gray-600 bg-white border rounded-lg focus:outline-none hover:bg-gray-100 transition-colors duration-200 transform dark:text-gray-200 dark:border-gray-200  dark:hover:bg-gray-700 ml-2" title="Members List">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                        </svg>
                    </button>
                </div>

                @if($searched)
                <a href="{{ route('reports.misc.admission.pdf', request()->query()) }}"
                   class="flex items-center px-4 py-2 text-gray-600 bg-white border rounded-lg hover:bg-gray-100 transition-colors duration-200 ml-2" title="Download PDF">
                    <span style="font-size: 14px;">Download PDF</span>
                </a>
                @endif

                <a href="javascript:;" id="toggle"
                   class="flex items-center px-4 py-2 text-gray-600 bg-white border rounded-lg focus:outline-none hover:bg-gray-100 transition-colors duration-200 transform dark:text-gray-200 dark:border-gray-200  dark:hover:bg-gray-700 ml-2"
                   title="Members List">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                         stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                    </svg>
                    <span class="hidden md:inline-block ml-2" style="font-size: 14px;">Search Filters</span>
                </a>
            </div>
        </h2>
    </x-slot>


    <div class="max-w-7xl mx-auto mt-12 px-4 sm:px-6 lg:px-8" id="filters">
        <div class="rounded-xl p-4 bg-white shadow-lg">
            <form action="">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">

                    <div>
                        <x-label for="period" value="Period" :required="false"/>
                        <select name="period" id="period" class="w-full px-3 py-2 border rounded-md text-gray-700 focus:outline-none focus:border-blue-500">
                            @foreach(['custom' => 'Custom range', 'current_month' => 'Current month', 'last_month' => 'Last month', 'current_quarter' => 'Current quarter', 'last_quarter' => 'Last quarter', 'current_year' => 'Current year (Jan-Dec)', 'last_year' => 'Last year (Jan-Dec)', 'current_tax_year' => 'Current tax year (Jul-Jun)', 'last_tax_year' => 'Last tax year (Jul-Jun)'] as $value => $label)
                                <option value="{{ $value }}" @selected(request('period', 'custom') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="start_date" class="block text-gray-700 font-bold mb-2">Start Date</label>
                        <input type="date" name="start_date" value="{{ request('start_date') }}" required id="start_date" class="w-full px-3 py-2 border rounded-md text-gray-700 focus:outline-none focus:border-blue-500" placeholder="Enter name">
                    </div>

                    <div>
                        <label for="end_date" class="block text-gray-700 font-bold mb-2">End Date</label>
                        <input type="date" name="end_date" value="{{ request('end_date') }}" required id="end_date" class="w-full px-3 py-2 border rounded-md text-gray-700 focus:outline-none focus:border-blue-500" placeholder="Enter name">
                    </div>
                    <div>
                        <x-label for="category" value="Category" :required="false"/>
                        <input type="text" name="filter[category]" value="{{ $filter['category'] ?? '' }}" id="category" class="w-full px-3 py-2 border rounded-md text-gray-700 focus:outline-none focus:border-blue-500">
                    </div>

                    <div>
                        <x-label for="patient_id" value="Patient ID" :required="false"/>
                        <input type="text" name="filter[patient_id]" value="{{ $filter['patient_id'] ?? '' }}" id="patient_id" class="w-full px-3 py-2 border rounded-md text-gray-700 focus:outline-none focus:border-blue-500">
                    </div>

                    <div>
                        <x-label for="invoice_id" value="Invoice ID" :required="false"/>
                        <input type="text" name="filter[invoice_id]" value="{{ $filter['invoice_id'] ?? '' }}" id="invoice_id" class="w-full px-3 py-2 border rounded-md text-gray-700 focus:outline-none focus:border-blue-500">
                    </div>


                    <div>
                        <x-label for="government_non_government" value="Entitled/Non-Entitled" :required="false"/>
                        <select name="filter[invoice.government_non_government]" id="government_non_government" class="w-full px-3 py-2 border rounded-md text-gray-700 focus:outline-none focus:border-blue-500">
                            <option value="">None</option>
                            <option value="1" @selected(($filter['invoice.government_non_government'] ?? '') === '1')>Entitled</option>
                            <option value="0" @selected(($filter['invoice.government_non_government'] ?? '') === '0')>Non-Entitled</option>
                        </select>
                    </div>

                    <div>
                        <x-label for="gender" value="Sex/Gender" :required="false"/>
                        <select name="filter[patient.sex]" id="gender" class="w-full px-3 py-2 border rounded-md text-gray-700 focus:outline-none focus:border-blue-500">
                            <option value="">None</option>
                            <option value="1" @selected(($filter['patient.sex'] ?? '') === '1')>Male</option>
                            <option value="0" @selected(($filter['patient.sex'] ?? '') === '0')>Female</option>
                        </select>
                    </div>

                    <div>
                        <x-label for="unit_ward" value="Unit/Ward" :required="false"/>
                        <select name="filter[unit_ward]" id="unit_ward" class="w-full px-3 py-2 border rounded-md text-gray-700 focus:outline-none focus:border-blue-500">
                            <option value="">None</option>
                            @foreach($wards as $aw)
                                <option value="{{ $aw->name }}" @selected(($filter['unit_ward'] ?? '') === $aw->name)>{{ $aw->name }}</option>
                            @endforeach
                        </select>

                    </div>

                    <div>
                        <x-label for="tehsil" value="Tehsil" :required="false"/>
                        <select name="filter[tehsil]" id="tehsil" class="w-full px-3 py-2 border rounded-md text-gray-700 focus:outline-none focus:border-blue-500">
                            <option value="">None</option>
                            @foreach($tehsils as $aw)
                                <option value="{{ $aw->name }}" @selected(($filter['tehsil'] ?? '') === $aw->name)>{{ $aw->name }}</option>
                            @endforeach
                        </select>
                    </div>


                    <div>
                        <x-label for="district" value="District" :required="false"/>
                        <select name="filter[district]" id="district" class="w-full px-3 py-2 border rounded-md text-gray-700 focus:outline-none focus:border-blue-500">
                            <option value="">None</option>
                            @foreach($districts as $aw)
                                <option value="{{ $aw->name }}" @selected(($filter['district'] ?? '') === $aw->name)>{{ $aw->name }}</option>
                            @endforeach
                        </select>
                    </div>


                    <div>
                        <x-label for="output" value="Output" :required="false"/>
                        <select name="output" id="output" class="w-full px-3 py-2 border rounded-md text-gray-700 focus:outline-none focus:border-blue-500">
                            <option value="screen" @selected(request('output', 'screen') === 'screen')>Show on screen</option>
                            <option value="pdf" @selected(request('output') === 'pdf')>Download PDF</option>
                            <option value="excel" @selected(request('output') === 'excel')>Download Excel</option>
                        </select>
                        <p class="mt-1 text-xs text-gray-500">For large ranges choose Download PDF or Excel instead of showing on screen.</p>
                    </div>


                    <div class="flex items-center justify-between">
                        <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline" type="submit">
                            Search
                        </button>
                    </div>


                </div>


            </form>
        </div>
    </div>

    <div class="py-12">

        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-validation-errors class="mb-4"/>
            <div class="bg-white overflow-x-auto p-4">
                <div class="overflow-x-auto">
                    <div class="grid grid-cols-3 gap-4">
                        <div></div> <!-- Empty column for spacing -->
                        <div class="flex items-center justify-center">
                            <img src="{{ \Illuminate\Support\Facades\Storage::url('Aimsa8 copy 2.png') }}" alt="Logo" style="width: 300px;">
                        </div>
                        <div class="flex flex-col items-end">
                            @php
                                $date = null;
                                if(request()->has('start_date')) { $date = \Carbon\Carbon::parse(request('start_date'))->format('d-M-Y'); }
                                else { $date = now()->format('d-M-Y h:m:s'); }
                                $reporting_data = (string)  "Reporting Date: $date\nAIMS, Muzaffarabad, AJK\nDepartment Wise Report";
                            @endphp
                            {!! DNS2D::getBarcodeSVG($reporting_data, 'QRCODE',3,3) !!}
                        </div>
                    </div>

                    @if(request()->has('start_date'))
                        <p class="text-center font-extrabold mb-4">
                            Report from {{ \Carbon\Carbon::parse(request('start_date'))->format('d-M-Y') }} to {{ \Carbon\Carbon::parse(request('end_date'))->format('d-M-Y') }} - Department Wise Report
                            <br>
                            <span>Software Developed By SeeChange Innovative - Contact No: 0335-9991441</span>
                        </p>
                    @else
                        <p class="text-center font-extrabold mb-4">
                            Report as of {{ now()->format('d-M-Y h:m:s') }} - Department Wise Report
                            <br>
                            <span>Software Developed By SeeChange Innovative - Contact No: 0335-9991441</span>
                        </p>
                    @endif
                    @unless($searched)
                        <p class="text-center text-gray-500 py-12">Select a date range and press Search.</p>
                    @endunless
                    {{--            <table class="table-auto w-full border-collapse border border-black">--}}
                    <table @class(['min-w-full divide-y divide-gray-200 print-table', 'hidden' => ! $searched]) style="font-size: 10px!important;">
                        {{--                <table class="min-w-full divide-y divide-gray-200">--}}
                        <thead>
                        <tr class="border-black">
                            <th class="border-black border px-2 py-2">#</th>
                            <th class="border-black border px-2 py-2">MR Number</th>
                            <th class="border-black border px-2 py-2">Patient Name</th>
                            <th class="border-black border px-2 py-2">Age/Sex</th>
                            <th class="border-black border px-2 py-2">Admission Date</th>
                            <th class="border-black border px-2 py-2">Ward</th>
                            <th class="border-black border px-2 py-2">Address</th>
                            <th class="border-black border px-2 py-2">Category</th>
                            <th class="border-black border px-2 py-2">Return</th>
                            <th class="border-black border px-2 py-2 print:hidden">Amount</th>
                        </tr>
                        </thead>
                        <tbody>

                        {{--                @php $count = 1; $non_entitled = 0; $entitled = 0; $total = 0; @endphp--}}
                        @foreach($admissions as $admision)
                            <tr class="border-black">
                                <!-- Category Name -->
                                <td class="border-black text-center border px-0 py-0">{{ $loop->iteration }}</td>
                                <td class="border-black text-center border">{{ $admision->patient->id }}-{{ $admision->invoice->id }}-{{ $admision->id }}</td>
                                <td class="border-black text-center border px-1 py-1">
                                    {{ $admision->patient->title . ' ' . $admision->patient->first_name . ' ' . $admision->patient->last_name }}
                                </td>
                                <td class="border-black text-center border px-1 py-1">
                                    {{ $admision->patient->age . ' ' . $admision->patient->years_months }} / {{ ($admision->patient->sex == 1?'M':'F') }}
                                </td>

                                <td class="border-black text-center border px-1 py-1">
                                    {{ \Carbon\Carbon::parse($admision->created_at)->format('d-m-Y H:i:s') }}
                                </td>

                                <td class="border-black text-center border px-1 py-1">
                                    <form action="{{ route('reports.misc.admission.ward.update', $admision) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <select name="unit_ward" id="unit_ward_{{ $admision->id }}" aria-label="Ward" class="w-full border-0 bg-transparent p-0 text-center focus:ring-0 print:hidden" onchange="this.form.submit()">
                                            @foreach($wards as $ward)
                                                <option value="{{ $ward->name }}" @selected($admision->unit_ward === $ward->name)>{{ $ward->name }}</option>
                                            @endforeach
                                        </select>
                                        <span class="hidden print:inline">{{ $admision->unit_ward }}</span>
                                    </form>
                                </td>

                                <td class="border-black text-center border px-1 py-1">
                                    {{ $admision->address }}
                                </td>

                                <td class="border-black text-center border px-1 py-1">
                                    {{ $admision->category }}
                                </td>

                                <td class="border-black text-center border px-1 py-1">
                                    {{ $admision->status }}
                                </td>

                                <td class="border-black text-center border px-4 py-2 print:hidden">
                                    @if($admision->status == 'Yes')
                                        -{{ $admision->invoice->total_amount }}
                                    @else
                                        {{ $admision->invoice->total_amount }}
                                    @endif

                                </td>
                            </tr>
                        @endforeach

                        {{--                        <tfoot class="border-black">--}}
                        {{--                        <th colspan="3" class="text-right px-4">Total</th>--}}
                        {{--                        <th class="border-black border text-center px-4 py-2">{{ number_format($non_entitled,0) }}</th>--}}
                        {{--                        <th class="border-black border text-center px-4 py-2">{{ number_format($entitled,0) }}</th>--}}
                        {{--                        <th class="border-black border text-center px-4 py-2">{{ number_format($total,2) }}</th>--}}
                        {{--                        </tfoot>--}}

                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
    @if(session('success'))
        <dialog id="ward-update-confirmation" class="rounded-lg border border-gray-200 p-0 shadow-xl backdrop:bg-black/30">
            <div class="flex min-w-72 flex-col gap-4 p-6 text-center">
                <p class="font-semibold text-gray-800">{{ session('success') }}</p>
                <form method="dialog">
                    <button class="bg-blue-500 px-4 py-2 font-bold text-white hover:bg-blue-700" type="submit">OK</button>
                </form>
            </div>
        </dialog>
    @endif
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

            function scaleTable() {
                const table = document.querySelector('.print-table');
                const scale = Math.min(window.innerWidth / table.offsetWidth, 1); // Calculate scale factor
                table.style.transform = 'scale(' + scale + ')'; // Apply the scale
            }

            window.onbeforeprint = scaleTable; // Run before printing

            const periodSelect = document.getElementById('period');
            const pad = (n) => String(n).padStart(2, '0');
            const fmt = (d) => d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate());

            function periodRange(period) {
                const now = new Date();
                const y = now.getFullYear();
                const m = now.getMonth();
                const q = Math.floor(m / 3);
                const taxStartYear = m >= 6 ? y : y - 1;
                const ranges = {
                    current_month: [new Date(y, m, 1), new Date(y, m + 1, 0)],
                    last_month: [new Date(y, m - 1, 1), new Date(y, m, 0)],
                    current_quarter: [new Date(y, q * 3, 1), new Date(y, q * 3 + 3, 0)],
                    last_quarter: [new Date(y, q * 3 - 3, 1), new Date(y, q * 3, 0)],
                    current_year: [new Date(y, 0, 1), new Date(y, 11, 31)],
                    last_year: [new Date(y - 1, 0, 1), new Date(y - 1, 11, 31)],
                    current_tax_year: [new Date(taxStartYear, 6, 1), new Date(taxStartYear + 1, 5, 30)],
                    last_tax_year: [new Date(taxStartYear - 1, 6, 1), new Date(taxStartYear, 5, 30)],
                };
                return ranges[period] ?? null;
            }

            periodSelect.addEventListener('change', function () {
                const range = periodRange(this.value);
                if (range) {
                    document.getElementById('start_date').value = fmt(range[0]);
                    document.getElementById('end_date').value = fmt(range[1]);
                }
            });

            ['start_date', 'end_date'].forEach((id) => {
                document.getElementById(id).addEventListener('change', () => { periodSelect.value = 'custom'; });
            });

            const wardUpdateConfirmation = document.getElementById('ward-update-confirmation');
            if (wardUpdateConfirmation) {
                wardUpdateConfirmation.showModal();
            }
        </script>

    @endsection
</x-app-layout>
