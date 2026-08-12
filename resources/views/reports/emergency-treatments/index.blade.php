@section('custom_header')
<style>
    @media print {
        .print-table {
            width: 100%;
            max-width: 100%;
            transform-origin: top left;
        }

        .print-table th,
        .print-table td {
            font-size: 10px !important;
            padding: 4px;
            word-break: break-word;
        }

        @page {
            font-size: 10px !important;
            size: landscape;
            margin: 5mm;
        }

        .no-print {
            display: none !important;
        }
    }
</style>
@endsection
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Emergency Treatments Report
            <div class="flex justify-center items-center float-right">
                <button onclick="window.print()"
                    class="flex items-center px-4 py-2 text-gray-600 bg-white border rounded-lg focus:outline-none hover:bg-gray-100 transition-colors duration-200 transform dark:text-gray-200 dark:border-gray-200 dark:hover:bg-gray-700 ml-2 no-print"
                    title="Print Report">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z">
                        </path>
                    </svg>
                </button>

                <a href="javascript:;" id="toggle"
                    class="flex items-center px-4 py-2 text-gray-600 bg-white border rounded-lg focus:outline-none hover:bg-gray-100 transition-colors duration-200 transform dark:text-gray-200 dark:border-gray-200 dark:hover:bg-gray-700 ml-2 no-print"
                    title="Search Filters">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                    </svg>
                    <span class="hidden md:inline-block ml-2" style="font-size: 14px;">Search Filters</span>
                </a>
            </div>
        </h2>
    </x-slot>

    <div class="max-w-7xl mx-auto mt-12 px-4 sm:px-6 lg:px-8 no-print" style="display: none" id="filters">
        <div class="rounded-xl p-4 bg-white shadow-lg">
            <form action="">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">

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

                    <div>
                        <x-label for="patient_id" value="Patient ID" :required="false" />
                        <input type="text" name="filter[patient_id]" value="{{ request('filter.patient_id') }}"
                            id="patient_id"
                            class="w-full px-3 py-2 border rounded-md text-gray-700 focus:outline-none focus:border-blue-500">
                    </div>

                    <div>
                        <x-label for="first_name" value="Patient First Name" :required="false" />
                        <input type="text" name="filter[patient.first_name]"
                            value="{{ request('filter.patient.first_name') }}" id="first_name"
                            class="w-full px-3 py-2 border rounded-md text-gray-700 focus:outline-none focus:border-blue-500">
                    </div>

                    <div>
                        <x-label for="last_name" value="Patient Last Name" :required="false" />
                        <input type="text" name="filter[patient.last_name]"
                            value="{{ request('filter.patient.last_name') }}" id="last_name"
                            class="w-full px-3 py-2 border rounded-md text-gray-700 focus:outline-none focus:border-blue-500">
                    </div>

                    <div>
                        <x-label for="disease_id" value="Disease" :required="false" />
                        <select name="filter[disease_id]" id="disease_id"
                            class="w-full px-3 py-2 border rounded-md text-gray-700 focus:outline-none focus:border-blue-500">
                            <option value="">All Diseases</option>
                            @foreach(\App\Models\Disease::orderBy('name', 'ASC')->get() as $disease)
                            <option value="{{ $disease->id }}" {{ request('filter.disease_id')==$disease->id ?
                                'selected' : '' }}>{{ $disease->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <x-label for="gender" value="Sex/Gender" :required="false" />
                        <select name="filter[patient.sex]" id="gender"
                            class="w-full px-3 py-2 border rounded-md text-gray-700 focus:outline-none focus:border-blue-500">
                            <option value="">All</option>
                            <option value="1" {{ request('filter.patient.sex')=='1' ? 'selected' : '' }}>Male</option>
                            <option value="0" {{ request('filter.patient.sex')=='0' ? 'selected' : '' }}>Female</option>
                        </select>
                    </div>

                    <div>
                        <x-label for="user_id" value="Entered By" :required="false" />
                        <select name="filter[user_id]" id="user_id"
                            class="w-full px-3 py-2 border rounded-md text-gray-700 focus:outline-none focus:border-blue-500">
                            <option value="">All Users</option>
                            @foreach(\App\Models\User::orderBy('name', 'ASC')->get() as $user)
                            <option value="{{ $user->id }}" {{ request('filter.user_id')==$user->id ? 'selected' : ''
                                }}>{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex items-end">
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
            <x-validation-errors class="mb-4 no-print" />
            <x-success-message class="mb-4 no-print" />
            <div class="bg-white overflow-x-auto p-4">
                <div class="overflow-x-auto">
                    <div class="grid grid-cols-3 gap-4">
                        <div></div>
                        <div class="flex items-center justify-center">
                            <img src="{{ \Illuminate\Support\Facades\Storage::url('Aimsa8 copy 2.png') }}" alt="Logo"
                                style="width: 300px;">
                        </div>
                        <div class="flex flex-col items-end">
                            @php
                            $date = null;
                            if(request()->has('start_date')) {
                            $date = \Carbon\Carbon::parse(request('start_date'))->format('d-M-Y') . ' to ' .
                            \Carbon\Carbon::parse(request('end_date'))->format('d-M-Y');
                            } else {
                            $date = \Carbon\Carbon::parse($start_date)->format('d-M-Y');
                            }
                            $reporting_data = "Reporting Date: $date\nAIMS, Muzaffarabad, AJK\nEmergency Treatments
                            Report";
                            @endphp
                            {!! DNS2D::getBarcodeSVG($reporting_data, 'QRCODE',3,3) !!}
                        </div>
                    </div>

                    @if(request()->has('start_date'))
                    <p class="text-center font-extrabold mb-4">
                        Emergency Treatments Report from {{
                        \Carbon\Carbon::parse(request('start_date'))->format('d-M-Y H:i:s') }} to {{
                        \Carbon\Carbon::parse(request('end_date'))->format('d-M-Y H:i:s') }}
                        <br>
                        <span>Software Developed By SeeChange Innovative - Contact No: 0300-8169924</span>
                    </p>
                    @else
                    <p class="text-center font-extrabold mb-4">
                        Emergency Treatments Report as of {{ \Carbon\Carbon::parse($start_date)->format('d-M-Y') }} from
                        00:00:00 to {{ \Carbon\Carbon::now()->format('H:i:s') }}
                        <br>
                        <span>Software Developed By SeeChange Innovative - Contact No: 0300-8169924</span>
                    </p>
                    @endif

                    <!-- Display Active Filters -->
                    @if(request()->hasAny(['filter.patient_id', 'filter.patient.first_name', 'filter.patient.last_name',
                    'filter.disease_id', 'filter.patient.sex', 'filter.user_id']))
                    <div class="mb-4 p-4 bg-blue-50 border border-blue-200 rounded">
                        <h3 class="font-bold text-blue-800 mb-2">Applied Filters:</h3>
                        <ul class="list-disc list-inside text-sm text-blue-700">
                            @if(request('filter.patient_id'))
                            <li>Patient ID: {{ request('filter.patient_id') }}</li>
                            @endif
                            @if(request('filter.patient.first_name'))
                            <li>First Name: {{ request('filter.patient.first_name') }}</li>
                            @endif
                            @if(request('filter.patient.last_name'))
                            <li>Last Name: {{ request('filter.patient.last_name') }}</li>
                            @endif
                            @if(request('filter.disease_id'))
                            <li>Disease: {{ \App\Models\Disease::find(request('filter.disease_id'))->name ?? 'N/A' }}
                            </li>
                            @endif
                            @if(request('filter.patient.sex') !== null && request('filter.patient.sex') !== '')
                            <li>Gender: {{ request('filter.patient.sex') == '1' ? 'Male' : 'Female' }}</li>
                            @endif
                            @if(request('filter.user_id'))
                            <li>Entered By: {{ \App\Models\User::find(request('filter.user_id'))->name ?? 'N/A' }}</li>
                            @endif
                        </ul>
                    </div>
                    @endif

                    <table class="min-w-full divide-y divide-gray-200 print-table border-collapse border border-black"
                        style="font-size: 10px!important;">
                        <thead>
                            <tr class="bg-gray-100 border-black">
                                <th class="border-black border px-2 py-2">S.No</th>
                                <th class="border-black border px-2 py-2">MR-Number</th>
                                <th class="border-black border px-2 py-2">Full Name</th>
                                <th class="border-black border px-2 py-2">Age/Sex</th>
                                <th class="border-black border px-2 py-2">Address</th>
                                <th class="border-black border px-2 py-2">Disease</th>
                                <th class="border-black border px-2 py-2">Treatment Details</th>
                                <th class="border-black border px-2 py-2">Medications</th>
                                <th class="border-black border px-2 py-2">Date & Time</th>
                                <th class="border-black border px-2 py-2">Entered By</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($treatments as $treatment)
                            <tr class="border-black hover:bg-gray-50">
                                <td class="border-black text-center border px-2 py-2">{{ $loop->iteration }}</td>
                                <td class="border-black text-center border px-2 py-2">
                                    {{ \Carbon\Carbon::parse($treatment->created_at)->format('y') }}-{{
                                    $treatment->patient_id }}-{{ $treatment->id }}
                                </td>
                                <td class="border-black border px-2 py-2">
                                    {{ $treatment->patient->title . '. ' . $treatment->patient->first_name . ' ' .
                                    $treatment->patient->last_name }}
                                    @if($treatment->patient->relationship_title &&
                                    $treatment->patient->father_husband_name)
                                    {{ $treatment->patient->relationship_title . ' ' .
                                    $treatment->patient->father_husband_name }}
                                    @endif
                                </td>
                                <td class="border-black text-center border px-2 py-2">
                                    {{ $treatment->patient->age . ' ' . $treatment->patient->years_months }} / {{
                                    ($treatment->patient->sex == 1 ? 'Male' : 'Female') }}
                                </td>
                                <td class="border-black border px-2 py-2">{{ $treatment->patient->address }}</td>
                                <td class="border-black text-center border px-2 py-2">
                                    @if($treatment->disease)
                                    {{ $treatment->disease->name }}
                                    @else
                                    <span class="text-gray-400">-- None --</span>
                                    @endif
                                </td>
                                <td class="border-black border px-2 py-2">
                                    <p class="text-sm whitespace-pre-line">{{ Str::limit($treatment->treatment_details,
                                        150) }}</p>
                                </td>
                                <td class="border-black border px-2 py-2">
                                    <p class="text-sm whitespace-pre-line">{{ Str::limit($treatment->medications, 150)
                                        }}</p>
                                </td>
                                <td class="border-black text-center border px-2 py-2">
                                    {{ \Carbon\Carbon::parse($treatment->created_at)->format('d-M-Y h:i A') }}
                                </td>
                                <td class="border-black text-center border px-2 py-2">{{ $treatment->user->name }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="10" class="border-black text-center border px-4 py-4 text-gray-500">
                                    No emergency treatments found for the selected date range.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr class="bg-gray-100 border-black">
                                <th colspan="10" class="border-black border px-4 py-2 text-right">
                                    Total Records: {{ $treatments->count() }}
                                </th>
                            </tr>
                        </tfoot>
                    </table>
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

            function scaleTable() {
                const table = document.querySelector('.print-table');
                const scale = Math.min(window.innerWidth / table.offsetWidth, 1);
                table.style.transform = 'scale(' + scale + ')';
            }

            window.onbeforeprint = scaleTable;
    </script>
    @endsection
</x-app-layout>