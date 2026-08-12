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
            User Wise OPD Report
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
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
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
                        <label for="department_id" class="block text-gray-700 font-bold mb-2">Specialist</label>
                        <select name="department_id" id="department_id"
                            class="w-full px-3 py-2 border rounded-md text-gray-700 focus:outline-none focus:border-blue-500">
                            <option value="">All Specialists</option>
                            @foreach ($departments as $dept)
                                <option value="{{ $dept->id }}"
                                    {{ request('department_id') == $dept->id ? 'selected' : '' }}>
                                    {{ $dept->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="user_id" class="block text-gray-700 font-bold mb-2">User</label>
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

                    <div class="flex items-center pt-7">
                        <input type="checkbox" name="specialists_only" id="specialists_only" value="on"
                            {{ request('specialists_only') == 'on' ? 'checked' : '' }}
                            class="rounded border-gray-300">
                        <label for="specialists_only" class="block text-gray-700 font-bold ml-2">Specialists Only</label>
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
                        User Wise OPD Report from {{ \Carbon\Carbon::parse($start_date)->format('d-M-Y') }} to
                        {{ \Carbon\Carbon::parse($end_date)->format('d-M-Y') }}
                        <br>
                        <span>Software Developed By SeeChange Innovative - Contact No: 0300-8169924</span>
                    </p>

                    <table class="report-table">
                        <thead>
                            <tr class="bg-gray-50">
                                <th style="width: 20px;">No</th>
                                <th style="width: 60px;">UserName</th>
                                <th style="width: 90px;">MR Number</th>
                                <th style="width: 100px;">Patient Name</th>
                                <th style="width: 80px;">Address</th>
                                <th style="width: 75px;">Department</th>
                                <th style="width: 35px;">Age/Sex</th>
                                <th style="width: 85px;">Issue Date</th>
                                <th style="width: 25px;">Ent.</th>
                                <th style="width: 25px;">Non-Ent.</th>
                                <th style="width: 50px;">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $groupedChits = $chits->groupBy('user_id');
                                $counter = 1;
                            @endphp
                            @foreach ($groupedChits as $userId => $userChits)
                                @php
                                    $userTotal = 0;
                                    $entitledCount = 0;
                                    $nonEntitledCount = 0;
                                    $specialistCount = 0;
                                    $uSpecEnt = 0;
                                    $uSpecNon = 0;
                                    $uOtherEnt = 0;
                                    $uOtherNon = 0;
                                @endphp
                                @foreach ($userChits as $index => $chit)
                                    @php
                                        $userTotal += $chit->amount;
                                        $isSpec = str_contains(strtolower($chit->department->name), 'specialist');
                                        
                                        if ($chit->government_non_gov == 1) {
                                            $entitledCount++;
                                            if ($isSpec) $uSpecEnt++; else $uOtherEnt++;
                                        } else {
                                            $nonEntitledCount++;
                                            if ($isSpec) $uSpecNon++; else $uOtherNon++;
                                        }

                                        if ($isSpec) {
                                            $specialistCount++;
                                        }
                                    @endphp
                                    <tr>
                                        <td class="text-center" style="vertical-align: middle;">
                                            {{ $counter++ }}
                                        </td>
                                        @if ($index === 0)
                                            <td class="font-bold text-center" style="vertical-align: middle;"
                                                rowspan="{{ $userChits->count() + 1 }}">
                                                {{ $chit->user->name ?? 'N/A' }}
                                            </td>
                                        @endif
                                        <td class="mr-no" style="vertical-align: middle;">
                                            {{ \Carbon\Carbon::parse($chit->patient->registration_date ?? $chit->issued_date)->format('y') }}-{{ $chit->id }}-{{ $chit->patient_id }}
                                        </td>
                                        <td style="vertical-align: middle;">
                                            <div>
                                                {{ $chit->patient->title }} {{ $chit->patient->first_name }}
                                                {{ $chit->patient->last_name }}
                                                @if ($chit->patient->father_husband_name)
                                                    <span class="text-gray-600 font-normal" style="font-size: 0.9em;">
                                                        ({{ $chit->patient->relationship_title ?? 'S/O' }}
                                                        {{ $chit->patient->father_husband_name }})
                                                    </span>
                                                @endif
                                            </div>
                                        </td>
                                        <td style="vertical-align: middle;">
                                            {{ $chit->address }}
                                        </td>
                                        <td style="vertical-align: middle;">
                                            {{ $chit->department->name }}</td>
                                        <td class="text-center" style="vertical-align: middle;">
                                            {{ $chit->patient->age }} /
                                            {{ $chit->patient->sex == 1 ? 'M' : ($chit->patient->sex == 0 ? 'F' : '-') }}
                                        </td>
                                        <td class="text-center" style="vertical-align: middle;">
                                            {{ \Carbon\Carbon::parse($chit->issued_date)->format('d-M-y H:i') }}</td>
                                        <td class="text-center" style="vertical-align: middle;">
                                            {{ $chit->government_non_gov == 1 ? 'Yes' : '-' }}
                                        </td>
                                        <td class="text-center" style="vertical-align: middle;">
                                            {{ $chit->government_non_gov == 0 ? 'Yes' : '-' }}
                                        </td>
                                        <td class="text-right" style="vertical-align: middle;">
                                            {{ number_format($chit->amount, 2) }}</td>
                                    </tr>
                                @endforeach
                                <tr class="bg-gray-50 font-bold" style="font-size: 9px;">
                                    <td class="text-center">-</td>
                                    {{-- UserName column is rowspanned from the first row of this user --}}
                                    <td colspan="6" class="text-right">
                                        User Total ({{ $userChits->first()->user->name ?? 'N/A' }}):
                                        <span class="ml-2">Spec(E:{{ $uSpecEnt }}, N:{{ $uSpecNon }})</span>
                                        <span class="ml-2">Others(E:{{ $uOtherEnt }}, N:{{ $uOtherNon }})</span>
                                    </td>
                                    <td class="text-center">{{ $entitledCount }}</td>
                                    <td class="text-center">{{ $nonEntitledCount }}</td>
                                    <td class="text-right">{{ number_format($userTotal, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-gray-50">
                            <tr>
                                <th colspan="8" class="text-right font-bold" style="font-size: 9px;">
                                    Grand Total:
                                    @php
                                        $gSpecEnt = $chits->filter(fn($c) => str_contains(strtolower($c->department->name), 'specialist') && $c->government_non_gov == 1)->count();
                                        $gSpecNon = $chits->filter(fn($c) => str_contains(strtolower($c->department->name), 'specialist') && $c->government_non_gov == 0)->count();
                                        $gOtherEnt = $chits->filter(fn($c) => !str_contains(strtolower($c->department->name), 'specialist') && $c->government_non_gov == 1)->count();
                                        $gOtherNon = $chits->filter(fn($c) => !str_contains(strtolower($c->department->name), 'specialist') && $c->government_non_gov == 0)->count();
                                    @endphp
                                    <span class="ml-2">Spec(E:{{ $gSpecEnt }}, N:{{ $gSpecNon }})</span>
                                    <span class="ml-2">Others(E:{{ $gOtherEnt }}, N:{{ $gOtherNon }})</span>
                                </th>
                                <th class="text-center font-bold">{{ $chits->where('government_non_gov', 1)->count() }}</th>
                                <th class="text-center font-bold">{{ $chits->where('government_non_gov', 0)->count() }}</th>
                                <th class="text-right font-bold">{{ number_format($chits->sum('amount'), 2) }}</th>
                            </tr>
                        </tfoot>
                    </table>

                    @php
                        $specialistChits = $chits->filter(fn($c) => str_contains(strtolower($c->department->name), 'specialist'));
                        $entitledSpecialists = $specialistChits->where('government_non_gov', 1);
                        $nonEntitledSpecialists = $specialistChits->where('government_non_gov', 0);
                    @endphp

                    <div class="mt-8">
                        <h3 class="font-bold text-lg mb-2">User Wise Statistics Summary</h3>
                        <table class="report-table">
                            <thead>
                                <tr class="bg-gray-100">
                                    <th rowspan="2" class="text-center">User Name</th>
                                    <th colspan="2" class="text-center">Specialist</th>
                                    <th colspan="2" class="text-center">Others (Normal OPD)</th>
                                    <th rowspan="2" class="text-center">Total Count</th>
                                    <th rowspan="2" class="text-center">Total Amount</th>
                                </tr>
                                <tr class="bg-gray-50">
                                    <th class="text-center">Ent.</th>
                                    <th class="text-center">Non-Ent.</th>
                                    <th class="text-center">Ent.</th>
                                    <th class="text-center">Non-Ent.</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($groupedChits as $userId => $userChits)
                                    @php
                                        $uSpecEnt = $userChits->filter(fn($c) => str_contains(strtolower($c->department->name), 'specialist') && $c->government_non_gov == 1)->count();
                                        $uSpecNon = $userChits->filter(fn($c) => str_contains(strtolower($c->department->name), 'specialist') && $c->government_non_gov == 0)->count();
                                        $uOtherEnt = $userChits->filter(fn($c) => !str_contains(strtolower($c->department->name), 'specialist') && $c->government_non_gov == 1)->count();
                                        $uOtherNon = $userChits->filter(fn($c) => !str_contains(strtolower($c->department->name), 'specialist') && $c->government_non_gov == 0)->count();
                                    @endphp
                                    <tr>
                                        <td class="px-2 py-1 font-bold">{{ $userChits->first()->user->name ?? 'N/A' }}</td>
                                        <td class="text-center px-2 py-1">{{ $uSpecEnt }}</td>
                                        <td class="text-center px-2 py-1">{{ $uSpecNon }}</td>
                                        <td class="text-center px-2 py-1">{{ $uOtherEnt }}</td>
                                        <td class="text-center px-2 py-1">{{ $uOtherNon }}</td>
                                        <td class="text-center px-2 py-1 font-bold">{{ $userChits->count() }}</td>
                                        <td class="text-right px-2 py-1 font-bold">{{ number_format($userChits->sum('amount'), 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-gray-100 font-extrabold">
                                <tr>
                                    <td class="px-2 py-1">Grand Total</td>
                                    <td class="text-center px-2 py-1">{{ $chits->filter(fn($c) => str_contains(strtolower($c->department->name), 'specialist') && $c->government_non_gov == 1)->count() }}</td>
                                    <td class="text-center px-2 py-1">{{ $chits->filter(fn($c) => str_contains(strtolower($c->department->name), 'specialist') && $c->government_non_gov == 0)->count() }}</td>
                                    <td class="text-center px-2 py-1">{{ $chits->filter(fn($c) => !str_contains(strtolower($c->department->name), 'specialist') && $c->government_non_gov == 1)->count() }}</td>
                                    <td class="text-center px-2 py-1">{{ $chits->filter(fn($c) => !str_contains(strtolower($c->department->name), 'specialist') && $c->government_non_gov == 0)->count() }}</td>
                                    <td class="text-center px-2 py-1">{{ $chits->count() }}</td>
                                    <td class="text-right px-2 py-1">{{ number_format($chits->sum('amount'), 2) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <div class="mt-8 flex justify-end">
                        <div class="w-full md:w-1/2 lg:w-1/3">
                            <table class="report-table">
                                <thead>
                                    <tr class="bg-gray-100">
                                        <th colspan="2" class="text-center py-2">Specialist Statistics Summary</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="px-2 py-1">Total Specialist Count</td>
                                        <td class="text-center font-bold px-2 py-1">{{ $specialistChits->count() }}</td>
                                    </tr>
                                    <tr>
                                        <td class="px-2 py-1">Entitled Specialist Count</td>
                                        <td class="text-center font-bold px-2 py-1">{{ $entitledSpecialists->count() }}</td>
                                    </tr>
                                    <tr>
                                        <td class="px-2 py-1">Non-Entitled Specialist Count</td>
                                        <td class="text-center font-bold px-2 py-1">{{ $nonEntitledSpecialists->count() }}</td>
                                    </tr>
                                    <tr class="bg-gray-50">
                                        <td class="px-2 py-1">Entitled Specialist Amount</td>
                                        <td class="text-right font-bold px-2 py-1">{{ number_format($entitledSpecialists->sum('amount'), 2) }}</td>
                                    </tr>
                                    <tr class="bg-gray-50">
                                        <td class="px-2 py-1">Non-Entitled Specialist Amount</td>
                                        <td class="text-right font-bold px-2 py-1">{{ number_format($nonEntitledSpecialists->sum('amount'), 2) }}</td>
                                    </tr>
                                    <tr class="bg-gray-100 font-extrabold">
                                        <td class="px-2 py-1">Total Specialist Revenue</td>
                                        <td class="text-right px-2 py-1">{{ number_format($specialistChits->sum('amount'), 2) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @section('custom_script')
        <script>
            document.getElementById('toggle').addEventListener('click', function() {
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
