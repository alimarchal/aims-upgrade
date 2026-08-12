<x-app-layout>
    @section('custom_header')
        <style>
            .report-table {
                width: 100%;
                border-collapse: collapse;
                border: 1px solid black;
                font-size: 11px;
                line-height: 1.2;
            }

            .report-table th,
            .report-table td {
                border: 1px solid black;
                padding: 3px 4px;
                word-wrap: break-word;
            }

            .mr-no {
                font-family: monospace;
                font-weight: bold;
                font-size: 9px;
                word-break: break-all;
            }

            @media print {
                @page {
                    size: portrait;
                    margin: 10mm;
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

                .report-table {
                    font-size: 10px !important;
                    width: 100% !important;
                    table-layout: fixed;
                }

                .report-table th,
                .report-table td {
                    padding: 2px !important;
                }
            }
        </style>
    @endsection
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight inline-block">
            Daily Summary of Specialist Fees
        </h2>
        <div class="flex justify-center items-center float-right no-print">
            <div class="flex justify-center items-center float-right">
                <button onclick="window.print()"
                    class="flex items-center px-4 py-2 text-gray-600 bg-white border rounded-lg focus:outline-none hover:bg-gray-100 transition-colors duration-200 transform dark:text-gray-200 dark:border-gray-200  dark:hover:bg-gray-700 ml-2"
                    title="Print">
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

    <div class="max-w-7xl mx-auto mt-12 px-4 sm:px-6 lg:px-8 no-print" style="display: none" id="filters">
        <div class="rounded-xl p-4 bg-white shadow-lg">
            <form action="">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="start_date" class="block text-gray-700 font-bold mb-2">Start Date</label>
                        <input type="date" name="start_date" value="{{ $start_date }}" id="start_date"
                            class="w-full px-3 py-2 border rounded-md text-gray-700 focus:outline-none focus:border-blue-500">
                    </div>

                    <div>
                        <label for="end_date" class="block text-gray-700 font-bold mb-2">End Date</label>
                        <input type="date" name="end_date" value="{{ $end_date }}" id="end_date"
                            class="w-full px-3 py-2 border rounded-md text-gray-700 focus:outline-none focus:border-blue-500">
                    </div>

                    <div>
                        <label for="department_id" class="block text-gray-700 font-bold mb-2">Specialist
                            Department</label>
                        <select name="department_id" id="department_id"
                            class="w-full px-3 py-2 border rounded-md text-gray-700 focus:outline-none focus:border-blue-500">
                            <option value="">All Specialist Departments</option>
                            @foreach ($departments as $dept)
                                @if (str_contains(strtolower($dept->name), 'specialist'))
                                    <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>
                                        {{ $dept->name }}
                                    </option>
                                @endif
                            @endforeach
                        </select>
                    </div>

                    <div class="flex items-center justify-between mt-4 col-span-full">
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
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-validation-errors class="mb-4" />
            <x-success-message class="mb-4" />
            <div class="bg-white overflow-hidden p-4">
                <div class="overflow-x-auto">
                    <div class="grid grid-cols-3 gap-4 mb-6">
                        <div></div>
                        <div class="flex items-center justify-center">
                            <img src="{{ \Illuminate\Support\Facades\Storage::url('Aimsa8 copy 2.png') }}" alt="Logo"
                                style="width: 300px;">
                        </div>
                        <div class="flex flex-col items-end">
                            @php
                                $reporting_date = now()->format('d-M-Y h:i:s');
                                $reporting_data = (string) "Reporting Date: $reporting_date\nDHQ Hattian, AJK";
                            @endphp
                            {!! DNS2D::getBarcodeSVG($reporting_data, 'QRCODE', 3, 3) !!}
                        </div>
                    </div>

                    <p class="text-center font-extrabold mb-4">
                        Daily Summary of Specialist Fees<br>
                        {{ config('app.name') }}<br>
                        For the date starting {{ \Carbon\Carbon::parse($start_date)->format('d-M-Y') }} to
                        {{ \Carbon\Carbon::parse($end_date)->format('d-M-Y') }}
                        <br>
                        <span>Software Developed By SeeChange Innovative - Contact No: 0300-8169924</span>
                    </p>

                    <table class="report-table">
                        <thead>
                            <tr class="bg-gray-50">
                                <th style="width: 40px;">Sr#</th>
                                <th style="width: 200px;">Name of OPD (Only Specialist OPDs)</th>
                                <th style="width: 80px;">Total Patients</th>
                                <th style="width: 100px;">Total G/S Patients<br>(Entitled)</th>
                                <th style="width: 100px;">Total Paid Patients<br>(Non Entitled)</th>
                                <th style="width: 80px;">Total Fees</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $counter = 1;
                            @endphp
                            @foreach ($departmentStats as $departmentId => $stats)
                                <tr>
                                    <td class="text-center" style="vertical-align: middle;">{{ $counter++ }}</td>
                                    <td style="vertical-align: middle;">{{ $stats['department']->name }}</td>
                                    <td class="text-center" style="vertical-align: middle;">{{ $stats['total_patients'] }}
                                    </td>
                                    <td class="text-center" style="vertical-align: middle;">
                                        {{ $stats['entitled_patients'] }}
                                    </td>
                                    <td class="text-center" style="vertical-align: middle;">
                                        {{ $stats['non_entitled_patients'] }}
                                    </td>
                                    <td class="text-right" style="vertical-align: middle;">
                                        {{ number_format($stats['total_fees'], 2) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-gray-100 font-extrabold">
                            <tr>
                                <td colspan="2" class="text-center px-2 py-1">Grand Total</td>
                                <td class="text-center px-2 py-1">{{ $grandTotals['total_patients'] }}</td>
                                <td class="text-center px-2 py-1">{{ $grandTotals['entitled_patients'] }}</td>
                                <td class="text-center px-2 py-1">{{ $grandTotals['non_entitled_patients'] }}</td>
                                <td class="text-right px-2 py-1">{{ number_format($grandTotals['total_fees'], 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @section('custom_script')
        <script>
            document.getElementById('toggle').addEventListener('click', function () {
                var filters = document.getElementById('filters');
                if (filters.style.display === 'none') {
                    filters.style.display = 'block';
                } else {
                    filters.style.display = 'none';
                }
            });
        </script>
    @endsection
</x-app-layout>