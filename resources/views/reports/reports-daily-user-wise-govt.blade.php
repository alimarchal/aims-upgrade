<x-app-layout>
    @section('custom_header')
        <style>
            @media print {
                @page {
                    margin-top: -30px;
                    size: landscape;
                }

                table {
                    font-size: 12px !important;
                    width: 100%;
                    table-layout: auto;
                }

                th,
                td {
                    white-space: nowrap;
                }
            }
        </style>
    @endsection
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight inline-block">
            Patients
        </h2>


        <div class="flex justify-center items-center float-right">
            <div class="flex justify-center items-center float-right">
                <button onclick="window.print()"
                    class="flex items-center px-4 py-2 text-gray-600 bg-white border rounded-lg focus:outline-none hover:bg-gray-100 transition-colors duration-200 transform dark:text-gray-200 dark:border-gray-200  dark:hover:bg-gray-700 ml-2"
                    title="Members List">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z">
                        </path>
                    </svg>
                </button>
            </div>

            <a href="javascript:;" id="toggle"
                class="flex items-center px-4 py-2 text-gray-600 bg-white border rounded-lg focus:outline-none hover:bg-gray-100 transition-colors duration-200 transform dark:text-gray-200 dark:border-gray-200  dark:hover:bg-gray-700 ml-2"
                title="Members List">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                </svg>
                <span class="hidden md:inline-block ml-2" style="font-size: 14px;">Search Filters</span>
            </a>
        </div>


    </x-slot>


    <div class="max-w-7xl mx-auto mt-12 px-4 sm:px-6 lg:px-8" style="display: none" id="filters">
        <div class="rounded-xl p-4 bg-white shadow-lg">
            <form action="">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label for="user_id" class="block text-gray-700 font-bold mb-2">User Name</label>
                        <select name="user_id" id="user_id"
                            class="w-full px-3 py-2 border rounded-md text-gray-700 focus:outline-none focus:border-blue-500">
                            <option value="">None</option>
                            @foreach(\App\Models\User::role('Front Desk/Receptionist')->get() as $user)
                                <option value="{{ $user->id }}" @selected(request('user_id') == $user->id)>{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="start_date" class="block text-gray-700 font-bold mb-2">Start Date</label>
                        <input type="date" name="start_date" value="{{ request('start_date') }}" id="start_date"
                            class="w-full px-3 py-2 border rounded-md text-gray-700 focus:outline-none focus:border-blue-500"
                            placeholder="Enter name">
                    </div>

                    <div>
                        <label for="end_date" class="block text-gray-700 font-bold mb-2">End Date</label>
                        <input type="date" name="end_date" value="{{ request('end_date') }}" id="end_date"
                            class="w-full px-3 py-2 border rounded-md text-gray-700 focus:outline-none focus:border-blue-500"
                            placeholder="Enter name">
                    </div>



                    <div></div>
                    <div></div>


                    <div class="flex items-center justify-between">
                        <button
                            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline"
                            type="submit">
                            Search
                        </button>
                    </div>


                </div>


            </form>
        </div>
    </div>

    <div class="py-12">


        <div class="max-w-8xl mx-auto sm:px-6 lg:px-8">
            <x-validation-errors class="mb-4" />
            <x-success-message class="mb-4" />
            <div class="bg-white overflow-hidden p-4">
                <div class="overflow-x-auto">
                    <div class="grid grid-cols-3 gap-4">
                        <div></div> <!-- Empty column for spacing -->
                        <div class="flex items-center justify-center">
                            <img src="{{ \Illuminate\Support\Facades\Storage::url('Aimsa8 copy 2.png') }}" alt="Logo"
                                style="width: 300px;">
                        </div>
                        <div class="flex flex-col items-end">
                            @php
                                $date = null;
                                if (request()->has('date')) {
                                    $date = \Carbon\Carbon::parse(request('date'))->format('d-M-Y');
                                } else {
                                    $date = now()->format('d-M-Y h:m:s');
                                }
                                $reporting_data = (string) "Reporting Date: $date\nAIMS, Muzaffarabad, AJK";
                            @endphp
                            {!! DNS2D::getBarcodeSVG($reporting_data, 'QRCODE', 3, 3) !!}
                        </div>
                    </div>

                    @if(request()->has('start_date'))
                        <p class="text-center font-extrabold mb-4">
                            Govt Revenue User Wise from {{ \Carbon\Carbon::parse(request('start_date'))->format('d-M-Y') }} to
                            {{ \Carbon\Carbon::parse(request('end_date'))->format('d-M-Y') }}
                            <br>
                            <span>Software Developed By SeeChange Innovative - Contact No: 0300-8169924</span>
                        </p>
                    @else
                        <p class="text-center font-extrabold mb-4">
                            Report as of {{ now()->format('d-M-Y h:m:s') }} - Govt Revenue User Wise
                            <br>
                            <span>Software Developed By SeeChange Innovative - Contact No: 0300-8169924</span>
                        </p>
                    @endif
                    <table class="table-auto w-full border-collapse border border-black"
                        style="font-size: 12px!important;">
                        <thead>
                            <tr class="border-black">
                                <th class="border-black border px-4 py-2 text-left" colspan="2"></th>
                                <th class="border-black border px-4 py-2 text-center" colspan="3">Invoices</th>
                                <th class="border-black border px-4 py-2 text-center" colspan="3">Chits</th>
                                <th class="border-black border px-4 py-2 text-center" rowspan="2">Grand Revenue</th>
                                <th class="border-black border px-4 py-2 text-center hidden print:table-cell" rowspan="2">
                                    Signature</th>
                            </tr>
                            <tr class="border-black">
                                <th class="border-black border px-4 py-2 text-left">No</th>
                                <th class="border-black border px-4 py-2 text-left">Name</th>
                                <th class="border-black border px-4 py-2 text-center">Entitled</th>
                                <th class="border-black border px-4 py-2 text-center">Non Entitled</th>
                                <th class="border-black border px-4 py-2 text-center">Govt</th>
                                <th class="border-black border px-4 py-2 text-center">Entitled</th>
                                <th class="border-black border px-4 py-2 text-center">Non Entitled</th>
                                <th class="border-black border px-4 py-2 text-center">Govt</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $invoicesEntitledTotal = 0;
                                $invoicesNonEntitledTotal = 0;
                                $invoicesReturnsTotal = 0;
                                $invoicesTotal = 0;
                                $chitsEntitledTotal = 0;
                                $chitsNonEntitledTotal = 0;
                                $chitsTotal = 0;
                            @endphp

                            @foreach($data as $value)
                                <tr class="border-black">
                                    <td class="border-black border px-4 py-2">{{ $loop->iteration }}</td>
                                    <td class="border-black border px-4 py-2 text-left">
                                        {{ $value['Name'] }}
                                        @if($value['Invoices Returns'] > 0)
                                            <br>
                                            <small class="text-gray-600">
                                                New tests: {{ number_format($value['Invoices'] - $value['Invoices Returns Amount'], 2) }}
                                                | Refund of {{ number_format($value['Invoices Returns'], 0) }} test(s): {{ number_format($value['Invoices Returns Amount'], 2) }}
                                                = {{ number_format($value['Invoices'], 2) }}
                                            </small>
                                        @endif
                                    </td>
                                    <td class="border-black border px-4 py-2 text-center">{{ number_format($value['Invoices Entitled'], 0) }}</td>
                                    <td class="border-black border px-4 py-2 text-center">{{ number_format($value['Invoices Non Entitled'], 0) }}</td>
                                    <td class="border-black border px-4 py-2 text-center">{{ number_format($value['Invoices'], 2) }}</td>
                                    <td class="border-black border px-4 py-2 text-center">{{ number_format($value['Chit Entitled'], 0) }}</td>
                                    <td class="border-black border px-4 py-2 text-center">{{ number_format($value['Chit Non Entitled'], 0) }}</td>
                                    <td class="border-black border px-4 py-2 text-center">{{ number_format($value['Chits'], 2) }}</td>
                                    <td class="border-black border px-4 py-2 text-center">{{ number_format($value['Invoices'] + $value['Chits'], 2) }}</td>
                                    <td class="border-black border px-4 py-2 text-center hidden print:table-cell">&nbsp;</td>
                                </tr>
                                @php
                                    $invoicesEntitledTotal += $value['Invoices Entitled'];
                                    $invoicesNonEntitledTotal += $value['Invoices Non Entitled'];
                                    $invoicesReturnsTotal += $value['Invoices Returns'];
                                    $invoicesTotal += $value['Invoices'];
                                    $chitsEntitledTotal += $value['Chit Entitled'];
                                    $chitsNonEntitledTotal += $value['Chit Non Entitled'];
                                    $chitsTotal += $value['Chits'];
                                @endphp
                            @endforeach

                            <tr class="border-black">
                                <td class="border-black border px-4 py-2 text-right font-bold" colspan="2">Total:</td>
                                <td class="border-black border px-4 py-2 text-center font-bold">{{ number_format($invoicesEntitledTotal, 0) }}</td>
                                <td class="border-black border px-4 py-2 text-center font-bold">{{ number_format($invoicesNonEntitledTotal, 0) }}</td>
                                <td class="border-black border px-4 py-2 text-center font-bold">{{ number_format($invoicesTotal, 2) }}</td>
                                <td class="border-black border px-4 py-2 text-center font-bold">{{ number_format($chitsEntitledTotal, 0) }}</td>
                                <td class="border-black border px-4 py-2 text-center font-bold">{{ number_format($chitsNonEntitledTotal, 0) }}</td>
                                <td class="border-black border px-4 py-2 text-center font-bold">{{ number_format($chitsTotal, 2) }}</td>
                                <td class="border-black border px-4 py-2 text-center font-bold">{{ number_format($invoicesTotal + $chitsTotal, 2) }}</td>
                                <td class="border-black border px-4 py-2 text-center hidden print:table-cell">&nbsp;</td>
                            </tr>
                            <tr class="border-black">
                                <td class="border-black border px-4 py-2 text-right font-bold" colspan="2">Grand Total:</td>
                                <td class="border-black border px-4 py-2 text-center font-bold" colspan="2">
                                    Entitled: {{ number_format($invoicesEntitledTotal + $chitsEntitledTotal, 0) }}</td>
                                <td class="border-black border px-4 py-2 text-center font-bold" colspan="3">
                                    Non Entitled: {{ number_format($invoicesNonEntitledTotal + $chitsNonEntitledTotal, 0) }}</td>
                                <td class="border-black border px-4 py-2 text-center font-bold" colspan="2">
                                    Govt: {{ number_format($invoicesTotal + $chitsTotal, 2) }}</td>
                                <td class="border-black border px-4 py-2 text-center hidden print:table-cell">&nbsp;</td>
                            </tr>
                        </tbody>
                    </table>

                    @if($reconciliation)
                        <table class="table-auto w-full md:w-2/3 mx-auto mt-6 border-collapse border border-black"
                            style="font-size: 12px!important;">
                            <thead>
                                <tr class="border-black">
                                    <th class="border-black border px-4 py-2 text-center" colspan="2">
                                        Reconciliation with Monthly Income Statement</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="border-black">
                                    <td class="border-black border px-4 py-2 font-bold">Total as per Department Wise Audit Report (above)</td>
                                    <td class="border-black border px-4 py-2 text-right font-bold">{{ number_format($reconciliation['audit_total'], 2) }}</td>
                                </tr>
                                @foreach($reconciliation['items'] as $name => $amount)
                                    <tr class="border-black">
                                        <td class="border-black border px-4 py-2">{{ $amount < 0 ? 'Less' : 'Add' }}: {{ $name }} (not included in audit report)</td>
                                        <td class="border-black border px-4 py-2 text-right">{{ number_format($amount, 2) }}</td>
                                    </tr>
                                @endforeach
                                <tr class="border-black">
                                    <td class="border-black border px-4 py-2 font-bold">Total as per Monthly Income Statement</td>
                                    <td class="border-black border px-4 py-2 text-right font-bold">{{ number_format($reconciliation['income_statement_total'], 2) }}</td>
                                </tr>
                                @if($invoicesReturnsTotal > 0)
                                    <tr class="border-black">
                                        <td class="border-black border px-4 py-2" colspan="2">
                                            Note: {{ number_format($invoicesReturnsTotal, 0) }} returned test(s) in this period. Their
                                            amount is deducted in Govt, but they are not counted in Entitled / Non Entitled, as in
                                            the Department Wise Audit Report.
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    @endif


                </div>



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
        </script>
    @endsection
</x-app-layout>