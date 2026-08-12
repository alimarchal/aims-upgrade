<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Report
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
        </h2>
    </x-slot>


    <div class="max-w-7xl mx-auto mt-12 px-4 sm:px-6 lg:px-8" style="display: none" id="filters">
        <div class="rounded-xl p-4 bg-white shadow-lg">
            <form action="">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">


                    <div>
                        <label for="start_date" class="block text-gray-700 font-bold mb-2">Start Date</label>
                        <input type="date" name="start_date" value="{{ request('filter.start_date') }}" id="start_date"
                            class="w-full px-3 py-2 border rounded-md text-gray-700 focus:outline-none focus:border-blue-500"
                            placeholder="Enter name">
                    </div>

                    <div>
                        <label for="end_date" class="block text-gray-700 font-bold mb-2">End Date</label>
                        <input type="date" name="end_date" value="{{ request('filter.end_date') }}" id="end_date"
                            class="w-full px-3 py-2 border rounded-md text-gray-700 focus:outline-none focus:border-blue-500"
                            placeholder="Enter name">
                    </div>


                    <div>
                        <x-label for="category_name" value="Category" :required="false" />
                        <select name="filter[name]" id="category_name" style="width: 100%"
                            class="select2 w-full px-3 py-2 border rounded-md text-gray-700 focus:outline-none focus:border-blue-500"
                            multiple>
                            <option value="">None</option>
                            @foreach(\App\Models\FeeCategory::orderBy('name', 'ASC')->get() as $aw)
                                <option value="{{ $aw->name }}" {{ old('name') === $aw->name ? 'selected' : '' }}>
                                    {{ $aw->name }}</option>
                            @endforeach
                        </select>
                    </div>


                    <div>
                        <x-label for="status" value="Status" :required="false" />
                        <select name="status" id="status" style="width: 100%"
                            class="select2 w-full px-3 py-2 border rounded-md text-gray-700 focus:outline-none focus:border-blue-500">
                            <option value="">None</option>
                            <option value="Normal">Normal</option>
                            <option value="Return Fee">Return Fee</option>
                        </select>
                    </div>


                    {{-- <div>--}}
                        {{-- <x-label for="department_id" value="OPD Department" :required="true" />--}}
                        {{-- <select name="department_id"
                            class="select2 w-full px-3 py-2 border rounded-md text-gray-700 focus:outline-none focus:border-blue-500">--}}
                            {{-- <option value="">Select Department</option>--}}
                            {{-- @foreach(\App\Models\Department::orderBy('name', 'ASC')->where('category','OPD')->get()
                            as $dept)--}}
                            {{-- <option value="{{$dept->id}}" {{ old('department_id')===$dept->id ? 'selected' : ''
                                }}>{{ $dept->name }}</option>--}}
                            {{-- @endforeach--}}
                            {{-- </select>--}}
                        {{-- </div>--}}



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
                                if (request()->has('start_date')) {
                                    $date = \Carbon\Carbon::parse(request('start_date'))->format('d-M-Y');
                                } else {
                                    $date = now()->format('d-M-Y h:m:s');
                                }
                                $reporting_data = (string) "Reporting Date: $date\nAIMS, Muzaffarabad, AJK\nDepartment Wise Report";
                            @endphp
                            {!! DNS2D::getBarcodeSVG($reporting_data, 'QRCODE', 3, 3) !!}
                        </div>
                    </div>

                    @if(request()->has('start_date'))
                        <p class="text-center font-extrabold mb-4">
                            Report from {{ \Carbon\Carbon::parse(request('start_date'))->format('d-M-Y') }} to
                            {{ \Carbon\Carbon::parse(request('end_date'))->format('d-M-Y') }} - Department Wise Report
                            <br>
                            <span>Software Developed By SeeChange Innovative - Contact No: 0300-8169924</span>
                        </p>
                    @else
                        <p class="text-center font-extrabold mb-4">
                            Report as of {{ now()->format('d-M-Y h:m:s') }} - Department Wise Report
                            <br>
                            <span>Software Developed By SeeChange Innovative - Contact No: 0300-8169924</span>
                        </p>
                    @endif
                    <table class="table-auto w-full border-collapse border border-black">
                        <thead>
                            <tr class="border-black">
                                <th class="border-black border px-4 py-2">No</th>
                                <th class="border-black border px-4 py-2">Category</th>
                                {{-- <th class="border-black border px-4 py-2">Type</th>--}}
                                <th class="border-black border px-4 py-2">Name</th>
                                <th class="border-black border px-4 py-2">Non Entitled</th>
                                <th class="border-black border px-4 py-2">Entitled</th>
                                @if(request()->input('status') == "Normal")
                                    <th class="border-black border px-4 py-2">Returned</th>
                                    <th class="border-black border px-4 py-2">Return Amount</th>
                                @endif
                                <th class="border-black border px-4 py-2">HIF</th>
                                <th class="border-black border px-4 py-2">Govt</th>
                                <th class="border-black border px-4 py-2">Total</th>
                            </tr>
                        </thead>
                        <tbody>

                            @php $count = 1;
                                $non_entitled = 0;
                                $entitled = 0;
                                $total = 0;
                                $hif = 0;
                                $return_count = 0;
                                $return_amount = 0;
                            $return_amount_temp = 0; @endphp
                            @foreach($categories as $categoryName => $feeTypes)
                                @foreach($feeTypes as $feeTypeName => $feeTypeDetails)
                                    <tr class="border-black">
                                        <td class="border-black border px-4 py-2">{{ $count }} @php $count++; @endphp</td>
                                        <!-- Category Name -->
                                        @if ($loop->first)
                                            <td rowspan="{{ count($feeTypes) }}" class="border-black text-center border px-4 py-2">
                                                {{ \App\Models\FeeCategory::find($categoryName)?->name ?? 'N/A' }}</td>
                                        @endif

                                        <!-- Fee Type Name -->
                                        <td class="border-black border px-4 py-2">
                                            {{ \App\Models\FeeType::find($feeTypeName)?->type ?? 'N/A' }}</td>
                                        <!-- Fee Type Details -->
                                        <td class="border-black border text-center px-4 py-2">

                                            @if(
                                                    $categoryName == "Emergency" && $feeTypeName == "Chit Fee" ||
                                                    $categoryName == "Cardiology" && $feeTypeName == "OPD Chit Fee" ||
                                                    $categoryName == "OPD (Out Door Patient)" && $feeTypeName == "Chit Fee" ||
                                                    $categoryName == "OPD (Out Door Patient)" && $feeTypeName == "Chit Fee (Family OPD)"
                                                )
                                                @if(request()->has('start_date'))
                                                    <a href="{{ route('chits.issued', ['start_date' => request()->input('start_date'), 'end_date' => request()->input('end_date'), 'filter[government_non_gov]' => 0, 'filter[fee_type_id]' => $feeTypeDetails['fee_type_id']]) }}"
                                                        class="text-blue-500 hover:underline">{{ number_format($feeTypeDetails['Non Entitled'], 0) }}</a>
                                                @else
                                                    <a href="{{ route('chits.issued', ['start_date' => \Carbon\Carbon::now()->format('Y-m-d'), 'end_date' => \Carbon\Carbon::now()->format('Y-m-d'), 'filter[government_non_gov]' => 0, 'filter[fee_type_id]' => $feeTypeDetails['fee_type']]) }}"
                                                        class="text-blue-500 hover:underline">{{ number_format($feeTypeDetails['Non Entitled'], 0) }}</a>
                                                @endif

                                            @else

                                                @if(request()->has('start_date'))
                                                    <a href="{{ route('invoice.issued', ['start_date' => request()->input('start_date'), 'end_date' => request()->input('end_date'), 'filter[government_non_government]' => 0, 'filter[patient_test.fee_type_id]' => $feeTypeDetails['fee_type_id']]) }}"
                                                        class="text-blue-500 hover:underline">{{ number_format($feeTypeDetails['Non Entitled'], 0) }}</a>
                                                @else
                                                    <a href="{{ route('invoice.issued', ['start_date' => \Carbon\Carbon::now()->format('Y-m-d'), 'end_date' => \Carbon\Carbon::now()->format('Y-m-d'), 'filter[government_non_government]' => 0, 'filter[patient_test.fee_type_id]' => $feeTypeDetails['fee_type_id']]) }}"
                                                        class="text-blue-500 hover:underline">{{ number_format($feeTypeDetails['Non Entitled'], 0) }}</a>
                                                @endif
                                            @endif


                                            @php $non_entitled += $feeTypeDetails['Non Entitled']; @endphp
                                        </td>
                                        <td class="border-black border text-center px-4 py-2">
                                            @if(
                                                    $categoryName == "Emergency" && $feeTypeName == "Chit Fee" ||
                                                    $categoryName == "Cardiology" && $feeTypeName == "OPD Chit Fee" ||
                                                    $categoryName == "OPD (Out Door Patient)" && $feeTypeName == "Chit Fee" ||
                                                    $categoryName == "OPD (Out Door Patient)" && $feeTypeName == "Chit Fee (Family OPD)"
                                                )
                                                @if(request()->has('start_date'))
                                                    <a href="{{ route('chits.issued', ['start_date' => request()->input('start_date'), 'end_date' => request()->input('end_date'), 'filter[government_non_gov]' => 1, 'filter[fee_type_id]' => $feeTypeDetails['fee_type_id']]) }}"
                                                        class="text-blue-500 hover:underline">{{ number_format($feeTypeDetails['Entitled'], 0) }}</a>
                                                @else
                                                    <a href="{{ route('chits.issued', ['start_date' => \Carbon\Carbon::now()->format('Y-m-d'), 'end_date' => \Carbon\Carbon::now()->format('Y-m-d'), 'filter[government_non_gov]' => 1, 'filter[fee_type_id]' => $feeTypeDetails['fee_type_id']]) }}"
                                                        class="text-blue-500 hover:underline">{{ number_format($feeTypeDetails['Entitled'], 0) }}</a>
                                                @endif

                                            @else

                                                @if(request()->has('start_date'))
                                                    <a href="{{ route('invoice.issued', ['start_date' => request()->input('start_date'), 'end_date' => request()->input('end_date'), 'filter[government_non_government]' => 1, 'filter[patient_test.fee_type_id]' => $feeTypeDetails['fee_type_id']]) }}"
                                                        class="text-blue-500 hover:underline">{{ number_format($feeTypeDetails['Entitled'], 0) }}</a>
                                                @else
                                                    <a href="{{ route('invoice.issued', ['start_date' => \Carbon\Carbon::now()->format('Y-m-d'), 'end_date' => \Carbon\Carbon::now()->format('Y-m-d'), 'filter[government_non_government]' => 1, 'filter[patient_test.fee_type_id]' => $feeTypeDetails['fee_type_id']]) }}"
                                                        class="text-blue-500 hover:underline">{{ number_format($feeTypeDetails['Entitled'], 0) }}</a>
                                                @endif
                                            @endif


                                            @php $entitled += $feeTypeDetails['Entitled'] @endphp
                                        </td>


                                        @if(request()->input('status') == "Normal")
                                            <td class="border-black border text-center px-4 py-2">
                                                @if(!empty($feeTypeDetails['Returned']))
                                                    @php
                                                        $return_count = $return_count + \App\Models\PatientTest::where('fee_type_id', $feeTypeDetails['Returned']->id)->whereBetween('created_at', [$feeTypeDetails['Returned Start Date'], $feeTypeDetails['Returned End Date']])->where('total_amount', '<', 0)->count()
                                                    @endphp
                                                    {{ \App\Models\PatientTest::where('fee_type_id', $feeTypeDetails['Returned']->id)->whereBetween('created_at', [$feeTypeDetails['Returned Start Date'], $feeTypeDetails['Returned End Date']])->where('total_amount', '<', 0)->count() }}
                                                @endif
                                            </td>

                                            <td class="border-black border text-center px-4 py-2">
                                                @if(!empty($feeTypeDetails['Returned']))
                                                    @php
                                                        $return_amount = $return_amount + \App\Models\PatientTest::where('fee_type_id', $feeTypeDetails['Returned']->id)->whereBetween('created_at', [$feeTypeDetails['Returned Start Date'], $feeTypeDetails['Returned End Date']])->where('total_amount', '<', 0)->sum('total_amount');
                                                        $return_amount_temp = \App\Models\PatientTest::where('fee_type_id', $feeTypeDetails['Returned']->id)->whereBetween('created_at', [$feeTypeDetails['Returned Start Date'], $feeTypeDetails['Returned End Date']])->where('total_amount', '<', 0)->sum('total_amount');

                                                    @endphp
                                                    {{ \App\Models\PatientTest::where('fee_type_id', $feeTypeDetails['Returned']->id)->whereBetween('created_at', [$feeTypeDetails['Returned Start Date'], $feeTypeDetails['Returned End Date']])->where('total_amount', '<', 0)->sum('total_amount') }}
                                                @endif

                                            </td>
                                        @endif

                                        <td class="border-black border text-right px-4 py-2">
                                            {{ number_format($feeTypeDetails['HIF'], 2) }}
                                            @php $hif += $feeTypeDetails['HIF'] @endphp</td>
                                        <td class="border-black border text-right px-4 py-2">
                                            {{ number_format($feeTypeDetails['Revenue'] - $feeTypeDetails['HIF'], 2) }}</td>
                                        <td class="border-black border text-right px-4 py-2">
                                            {{ number_format($feeTypeDetails['Revenue'] - abs($return_amount_temp), 2) }}
                                            @php $total += $feeTypeDetails['Revenue'] @endphp</td>
                                    </tr>
                                @endforeach
                            @endforeach

                        <tfoot class="border-black">


                            <tr>
                                <th colspan="3" class="text-right px-4">Total</th>
                                <th class="border-black border text-center px-4 py-2">
                                    {{ number_format($non_entitled, 0) }}</th>
                                <th class="border-black border text-center px-4 py-2">{{ number_format($entitled, 0) }}
                                </th>
                                @if(request()->input('status') == "Normal")
                                    <th class="border-black border text-center px-4 py-2">{{ $return_count }}</th>
                                    <th class="border-black border text-center px-4 py-2">
                                        {{ number_format($return_amount, 2) }}</th>
                                @endif
                                <th class="border-black border text-center px-4 py-2 text-right">
                                    {{ number_format($hif, 2) }}</th>
                                <th class="border-black border text-center px-4 py-2 text-right">
                                    {{ number_format($total - $hif, 2) }}</th>
                                <th class="border-black border text-center px-4 py-2 text-right">
                                    {{ number_format($total - abs($return_amount), 2) }}</th>
                            </tr>
                        </tfoot>

                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
    @section('custom_script')



        <script>


            $(document).ready(function () {
                // Initialize Select2 on your elements
                $('.js-example-basic-multiple').select2();
                $('.select2').select2();

                // This function updates the action URL of the form or modifies/creates a hidden input to include the selected values as a single query parameter
                function updateFilterParameter() {
                    // Assuming your select element has the class 'select2', adjust if necessary
                    var selectedCategories = $('#category_name').val().join(',');

                    // Check if a hidden input for 'filter[name]' already exists, if not create one
                    var hiddenInput = $('input[type=hidden][name="filter[name]"]');
                    if (hiddenInput.length === 0) {
                        $('<input>').attr({
                            type: 'hidden',
                            name: 'filter[name]',
                            value: selectedCategories
                        }).appendTo('form');
                    } else {
                        // If it exists, just update its value
                        hiddenInput.val(selectedCategories);
                    }
                }

                // Listen for form submission
                $('form').on('submit', function (e) {
                    // Prevent the default form submission
                    e.preventDefault();

                    // Update the filter parameter before submitting the form
                    updateFilterParameter();

                    // Submit the form
                    this.submit();
                });
            });

            $(document).on('select2:open', () => {
                // Auto-focus on the search field when select2 opens
                document.querySelector('.select2-search__field').focus();
            });


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