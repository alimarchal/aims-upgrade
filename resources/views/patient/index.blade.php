<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight inline-block">
            Patients
        </h2>


        <div class="flex justify-center items-center float-right">
            <a href="{{route('patient.create-opd')}}" class="mx-2 float-right inline-flex items-center px-4 py-2 bg-red-800 border border-transparent
            rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-900
            focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150" \>
                Issue Chit OPD
            </a>


            <a href="{{ route('patient.create') }}"
               class="flex items-center px-4 py-2 text-gray-600 bg-white border rounded-lg focus:outline-none hover:bg-gray-100 transition-colors duration-200 transform dark:text-gray-200 dark:border-gray-200  dark:hover:bg-gray-700 ml-2"
               title="Members List">

                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="#000000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16c0 1.1.9 2 2 2h12a2 2 0 0 0 2-2V8l-6-6z"/><path d="M14 3v5h5M12 18v-6M9 15h6"/></svg>

            </a>


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


    </x-slot>


    <div class="max-w-7xl mx-auto mt-12 px-4 sm:px-6 lg:px-8" style="display: none" id="filters">
        <div class="rounded-xl p-4 bg-white shadow-lg">
            <form action="">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="id" class="block text-gray-700 font-bold mb-2">Patient ID</label>
                        <input type="text" name="filter[id]" value="{{ request('filter.id') }}" id="id" class="w-full px-3 py-2 border rounded-md text-gray-700 focus:outline-none focus:border-blue-500" placeholder="Patient ID Type here">
                    </div>

                    <div>
                        <label for="first_name" class="block text-gray-700 font-bold mb-2">Name</label>
                        <input type="text" name="filter[first_name]" value="{{ request('filter.first_name') }}" id="first_name" class="w-full px-3 py-2 border rounded-md text-gray-700 focus:outline-none focus:border-blue-500" placeholder="Enter name">
                    </div>
                    <div>
                        <label for="father_husband_name" class="block text-gray-700 font-bold mb-2">Father/Son/Do</label>
                        <input type="text" name="filter[father_husband_name]" value="{{ request('filter.father_husband_name') }}" id="father_husband_name" class="w-full px-3 py-2 border rounded-md text-gray-700 focus:outline-none focus:border-blue-500" placeholder="Enter father/son/do">
                    </div>
                    <div>
                        <label class="block text-gray-700 font-bold mb-2">Sex</label>
                        <div class="mt-2">
                            <label class="inline-flex items-center">
                                <input type="radio" name="filter[sex]" id="male" value="1" >
                                <span class="ml-2">Male</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="radio" name="filter[sex]" id="female" value="0" >
                                <span class="ml-2">Female</span>
                            </label>

                        </div>
                    </div>
                    <div>
                        <label for="cnic" class="block text-gray-700 font-bold mb-2">CNIC</label>
                        <input type="text" name="filter[cnic]" id="cnic" value="{{ request('filter.cnic') }}" class="w-full px-3 py-2 border rounded-md text-gray-700 focus:outline-none focus:border-blue-500" placeholder="Enter CNIC (00000-0000000-0)">
                    </div>
                    <div>
                        <label for="mobile" class="block text-gray-700 font-bold mb-2">Mobile No.</label>
                        <input type="text" name="filter[mobile]" id="mobile_no" value="{{ request('filter.mobile') }}" class="w-full px-3 py-2 border rounded-md text-gray-700 focus:outline-none focus:border-blue-500"
                               placeholder="Enter mobile no. (0000-0000000)">
                    </div>
                    <div>
                        <label class="block text-gray-700 font-bold mb-2">Government/Non-Government</label>
                        <div class="mt-2">
                            <label class="inline-flex items-center">
                                <input type="radio" class="form-radio" name="filter[government_non_gov]" value="1">
                                <span class="ml-2">Government</span>
                            </label>
                            <label class="inline-flex items-center ml-6">
                                <input type="radio" class="form-radio" name="filter[government_non_gov]" value="0">
                                <span class="ml-2">Non-Government</span>
                            </label>
                        </div>
                    </div>

                    <div>
                        <label for="government_card_no" class="block text-gray-700 font-bold mb-2">Government Card No</label>
                        <input type="text" name="filter[government_card_no]" id="government_card_no" value="{{ request('filter.government_card_no') }}" class="w-full px-3 py-2 border rounded-md text-gray-700 focus:outline-none focus:border-blue-500"
                               placeholder="Enter Card No: eg: 503">
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
            <x-success-message class="mb-4"/>
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-4">
                <div class="overflow-x-auto">
                    <table class="table-auto w-full border-collapse border border-black">
                        <thead>
                        <tr class="border-black">
                            <th class="border-black border px-4 py-2 text-left">P.#</th>
                            <th class="border-black border px-4 py-2 text-left">Name</th>
{{--                            <th class="border-black border px-4 py-2 text-left">F/S/D/W</th>--}}
                            <th class="border-black border px-4 py-2 text-center">Mobile No.</th>
                            <th class="border-black border px-4 py-2 text-center">Age</th>
                            <th class="border-black border px-4 py-2">Type</th>
                            <th class="border-black border px-4 py-2">Actions</th>
{{--                            <th class="border-black border px-4 py-2">History</th>--}}
                            {{--                            <th class="border-black border px-4 py-2">Delete</th>--}}
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($patients as $patient)
                            <tr class="border-black">
                                <td class="border-black border px-4 py-2">{{$loop->iteration}}</td>
                                <td class="border-black border px-4 py-2">
                                    <a href="{{ route('patient.edit', $patient->id) }}" class="text-indigo-600 hover:text-indigo-900 hover:underline">
                                        {{ $patient->title }} {{$patient->first_name . ' ' . $patient->last_name}}
                                    </a>
                                </td>
{{--                                <td class="border-black border px-4 py-2">{{$patient->father_husband_name}}</td>--}}
                                <td class="border-black border px-4 py-2 text-center">{{$patient->mobile}}</td>
                                <td class="border-black border px-4 py-2 text-center">
                                    {{$patient->age}} {{$patient->years_months}}
                                </td>
                                <td class="border-black border px-4 py-2 text-center">
                                    @if($patient->government_non_gov == 1)
                                        Entitiled
                                    @else
                                        Non-Entitiled
                                    @endif
                                </td>

                                <td class="border-black border px-4 py-2 text-center">


                                    <a href="{{ route('patient.actions', $patient->id) }}">
                                        <img src="{{ Storage::url('settings.png') }}" alt="actions" class="w-8 inline  hover:scale-110">
                                    </a>

{{--                                    <a href="{{ route('patient.issue-new-chit', $patient->id) }}">--}}
{{--                                        <img src="{{ Storage::url('new_2.png') }}" alt="actions" class="w-6 inline  hover:scale-110">--}}
{{--                                    </a>--}}


                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>


                </div>

                <div class="mt-4">
                    {{ $patients->links() }}
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
