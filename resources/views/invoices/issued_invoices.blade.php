<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $patient->title . $patient->first_name . ' ' . $patient->last_name }} - Issued Chits
            <div class="flex justify-center items-center float-right">
                <a href="{{ route('patient.actions', $patient->id) }}" class="float-right inline-flex items-center px-4 py-2 bg-red-800 border border-transparent
                        rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-red-900
                        focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150" \>
                    Back
                </a>
                <button onclick="window.print()" class="flex items-center px-4 py-2 text-gray-600 bg-white border rounded-lg focus:outline-none hover:bg-gray-100 transition-colors duration-200 transform dark:text-gray-200 dark:border-gray-200  dark:hover:bg-gray-700 ml-2" title="Members List">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                    </svg>
                </button>
            </div>

        </h2>



    </x-slot>

    <div class="py-12">


        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-validation-errors class="mb-4"/>
            <x-success-message class="mb-4"/>
            <div class="bg-white overflow-hidden sm:rounded-lg p-4">
                <div class="overflow-x-auto">
                    <table class="table-auto w-full border-collapse border border-black">
                        <thead>
                        <tr class="border-black">
                            <th class="border-black border px-4 py-2">No</th>
                            <th class="border-black border px-4 py-2">MR Number</th>
                            <th class="border-black border px-4 py-2">Entitled/Non-Entitled</th>
                            <th class="border-black border px-4 py-2">Age/Sex</th>
                            <th class="border-black border px-4 py-2">Issue Date</th>
                            <th class="border-black border px-4 py-2">Amount</th>
                            <th class="border-black border px-4 py-2">Print</th>
                        </tr>
                        </thead>
                        <tbody>

                        @foreach($patient->invoices->sortByDesc('issued_date') as $chit)
                            <tr class="border-black">
                                <td class="border-black border px-4 py-2">{{$loop->iteration}}</td>
                                <td class="border-black border px-4 py-2 text-center">{{date('y')}}-{{ $chit->id .'-' . $chit->patient_id}}</td>
                                <td class="border-black border px-4 py-2 text-center">
                                    @if($chit->government_non_government == 1)
                                        Entitled
                                    @else
                                        Non-Entitled
                                    @endif
                                </td>
                                <td class="border-black border px-4 py-2 text-center">
                                    {{ $patient->age . ' ' . $patient->years_months }}/{{ ($patient->sex == 1?'Male':'Female') }}
                                </td>
                                <td class="border-black border px-4 py-2 text-center">
                                    {{ \Carbon\Carbon::parse($chit->created_at)->format('d-M-y h:i:s') }}
                                </td>
                                <td class="border-black border px-4 py-2 text-center">
                                    {{$chit->total_amount}}
                                </td>
                                <td class="border-black border px-4 py-2 text-center">
                                <a href="{{route('patient.patient_invoice',[$chit->patient_id, $chit->id])}}" class="text-center inline-block">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 m-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                                    </svg>
                                </a>


{{--                                    <a href="{{route('patient.patient_invoice',[$chit->patient_id, $chit->id ,'thermal=Yes'])}}" class="text-center inline-block">--}}
{{--                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 m-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">--}}
{{--                                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>--}}
{{--                                        </svg>--}}
{{--                                    </a>--}}


                                    <a href="javascript:;" onclick="openPopup('{{route('patient.patient_invoice_thermal_print',[$chit->patient_id, $chit->id])}}')" class="text-center inline-block">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 m-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                                        </svg>
                                    </a>

                                    <!-- JavaScript to Handle Popup -->
                                    <script>
                                        function openPopup(url) {
                                            // Window size and features
                                            const width = 800;
                                            const height = 600;

                                            // Calculate the position for the window to be centered
                                            const left = (window.screen.width / 2) - (width / 2);
                                            const top = (window.screen.height / 2) - (height / 2);

                                            // Define the size and properties of the popup window
                                            const popupFeatures = `width=${width},height=${height},left=${left},top=${top},resizable=yes,scrollbars=yes,status=yes`;

                                            // Open the new window
                                            const win = window.open(url, "_blank", popupFeatures);

                                            // Focus on the popup window if it opens successfully
                                            if (win) {
                                                win.focus();
                                            } else {
                                                alert('Popup blocked by browser.');
                                            }
                                        }
                                    </script>

                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
