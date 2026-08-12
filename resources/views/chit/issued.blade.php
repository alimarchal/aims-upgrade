<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Issued Chits
            <div class="flex justify-center items-center float-right">
                <div class="flex justify-center items-center float-right">
                    <button onclick="window.print()" class="flex items-center px-4 py-2 text-gray-600 bg-white border rounded-lg focus:outline-none hover:bg-gray-100 transition-colors duration-200 transform dark:text-gray-200 dark:border-gray-200  dark:hover:bg-gray-700 ml-2" title="Members List">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                        </svg>
                    </button>
                </div>

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

    <div class="max-w-7xl mx-auto mt-12 px-4 sm:px-6 lg:px-8" style="display: none" id="filters">
        <div class="rounded-xl p-4 bg-white shadow-lg">
            <form action="">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">

                    <div>
                        <label for="start_date" class="block text-gray-700 font-bold mb-2">Start Date</label>
                        <input type="date" name="start_date" value="{{ request('filter.start_date') }}" id="start_date" class="w-full px-3 py-2 border rounded-md text-gray-700 focus:outline-none focus:border-blue-500" placeholder="Enter name">
                    </div>

                    <div>
                        <label for="end_date" class="block text-gray-700 font-bold mb-2">End Date</label>
                        <input type="date" name="end_date" value="{{ request('filter.end_date') }}" id="end_date" class="w-full px-3 py-2 border rounded-md text-gray-700 focus:outline-none focus:border-blue-500" placeholder="Enter name">
                    </div>

                    <div>
                        <x-label for="department_id" value="Department" :required="false"/>
                        <select name="filter[department_id]" id="department_id" class="w-full px-3 py-2 border rounded-md text-gray-700 focus:outline-none focus:border-blue-500">
                            <option value="">None</option>
                            @foreach(\App\Models\Department::orderBy('name')->get() as $department)
                                <option value="{{ $department->id }}">{{ $department->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <x-label for="fee_type_id" value="Hospital Fee" :required="false"/>
                        <select name="filter[fee_type_id]" id="fee_type_id" class="w-full px-3 py-2 border rounded-md text-gray-700 focus:outline-none focus:border-blue-500">
                            <option value="">None</option>
                            @foreach(\App\Models\FeeType::orderBy('type')->get() as $ft)
                                <option value="{{ $ft->id }}">{{ $ft->type }}</option>
                            @endforeach
                        </select>
                    </div>


                    <div>
                        <x-label for="government_department_id" value="Government Department" :required="false"/>
                        <select name="filter[government_department_id]" id="government_department_id" class="w-full px-3 py-2 border rounded-md text-gray-700 focus:outline-none focus:border-blue-500">
                            <option value="">None</option>
                            @foreach(\App\Models\GovernmentDepartment::orderBy('name')->get() as $ft)
                                <option value="{{ $ft->id }}">{{ $ft->name }}</option>
                            @endforeach
                        </select>
                    </div>


                    <div>
                        <x-label for="government_non_gov" value="Entitled/Non-Entitled" :required="false"/>
                        <select name="filter[government_non_gov]" id="government_non_gov" class="w-full px-3 py-2 border rounded-md text-gray-700 focus:outline-none focus:border-blue-500">
                            <option value="">None</option>
                            <option value="1">Entitled</option>
                            <option value="0">Non-Entitled</option>
                        </select>
                    </div>

                    <div>
                        <x-label for="user_id" value="User" :required="false"/>
                        <select name="filter[user_id]" id="user_id" class="w-full px-3 py-2 border rounded-md text-gray-700 focus:outline-none focus:border-blue-500">
                            <option value="">None</option>
                            @foreach(\App\Models\User::role('Front Desk/Receptionist')->get() as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>


                    <div>
                        <x-label for="government_card_no" value="Government Card No" :required="false"/>
                        <input type="text" name="filter[government_card_no]" id="category" class="w-full px-3 py-2 border rounded-md text-gray-700 focus:outline-none focus:border-blue-500">
                    </div>

                    <div>
                        <x-label for="gender" value="Sex/Gender" :required="false"/>
                        <select name="filter[patient.sex]" id="gender" class="w-full px-3 py-2 border rounded-md text-gray-700 focus:outline-none focus:border-blue-500">
                            <option value="">None</option>
                            <option value="1">Male</option>
                            <option value="0">Female</option>
                        </select>
                    </div>

                    <div></div>
                    <div></div>
                    <div></div>
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


        <div class="max-w-8xl mx-auto sm:px-6 lg:px-8">
            <x-validation-errors class="mb-4"/>
            <x-success-message class="mb-4"/>
            <div class="bg-white overflow-hidden sm:rounded-lg p-2">
                <div class="overflow-x-auto">
                    <table class="table-auto w-full border-collapse border border-black">
                        <thead>
                        <tr class="border-black">
                            <th class="border-black border px-4 py-2">No</th>
                            <th class="border-black border px-4 py-2">MR Number</th>
                            <th class="border-black border px-4 py-2">Department</th>
                            <th class="border-black border px-4 py-2">Age/Sex</th>
                            <th class="border-black border px-4 py-2">Issue Date</th>
                            <th class="border-black border px-4 py-2">IPD/OPD</th>
                            <th class="border-black border px-4 py-2">Amount</th>
                            <th class="border-black border px-4 py-2">Print</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($issued_chits ?? [] as $chit)
                            <tr class="border-black">
                                <td class="border-black border px-4 py-2">{{$loop->iteration}}</td>
                                <td class="border-black border px-4 py-2 text-center">{{date('y')}}-{{ $chit->id .'-' . $chit->patient_id}}</td>
                                <td class="border-black border px-4 py-2 text-center">
                                    @if(!empty($chit->department))
                                        {{$chit->department->name}}
                                    @endif
                                </td>
                                <td class="border-black border px-4 py-2 text-center">
                                    {{ $chit->patient->age . ' ' . $chit->patient->years_months }}/{{ ($chit->patient->sex == 1?'Male':'Female') }}
                                </td>
                                <td class="border-black border px-4 py-2 text-center">
                                    {{ \Carbon\Carbon::parse($chit->issued_date)->format('d-M-y h:i:s') }}
                                </td>

                                <td class="border-black border px-4 py-2 text-center">
                                    @if($chit->ipd_opd == 1)
                                        OPD
                                    @else
                                        Emergency
                                    @endif
                                </td>
                                <td class="border-black border px-4 py-2 text-center">
                                    {{$chit->amount}}
                                </td>
                                <td class="border-black border px-4 py-2 text-center">
                                    <a href="{{route('chit.print',[$chit->patient_id, $chit->id])}}" class="text-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 m-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                                        </svg>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                        <tr class="border-black bg-black">
                            <td class="border-black text-white border px-4 py-2 text-right font-extrabold" colspan="6">Total Amount</td>
                            <td class="border-black text-white border px-4 py-2 text-center font-extrabold">Rs. {{ number_format($issued_chits?->sum('amount') ?? 0, 2) }}</td>
                            <td class="border-black text-white border px-4 py-2 text-center font-extrabold"></td>
                        </tr>
                        </tbody>
                    </table>

                    <div class="mt-4">
                        {{ $issued_chits?->links() }}
                    </div>
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
                const scale = Math.min(window.innerWidth / table.offsetWidth, 1); // Calculate scale factor
                table.style.transform = 'scale(' + scale + ')'; // Apply the scale
            }

            window.onbeforeprint = scaleTable; // Run before printing
        </script>

    @endsection
</x-app-layout>
