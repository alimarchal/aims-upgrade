<x-app-layout>
    @section('custom_header')

        <style>
            @media print {
                @page {
                    size: 5.95in 8.5in;
                    margin-top: -1.0in;
                }
            }
        </style>
    @endsection
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Patient Chit
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
        </h2>


    </x-slot>

    <div class="py-0">
        {{-- <div class="max-w-7xl mx-auto  bg:whit bg-white sm:rounded-lg ">--}}
            <div class="grid grid-cols-3 gap-4">
                <div></div> <!-- Empty column for spacing -->
                <div class="flex items-center justify-center">
                    <img src="{{ \Illuminate\Support\Facades\Storage::url('Aimsa8 copy 2.png') }}" alt="Logo"
                        style="width: 200px;">
                </div>
                <div class="flex flex-col items-end">
                    @php
                        $patient_id = $patient->title . ' ' . $patient->first_name . ' ' . $patient->last_name . "\nMR: " . date('y') . '-' . $patient->id . '-' . $chit->id . "\nMobile: " . $patient->mobile . "\nC" . $chit->id;
                    @endphp
                    {{-- @php $patient_id = (string) "RS.". $chit->amount . "\nC$chit->id" .
                    "\n$chit->issued_date\nDeveloped By Ali Raza Marchal\nTel: 0300-8169924"; @endphp--}}
                    {!! DNS2D::getBarcodeSVG($patient_id, 'QRCODE', 3, 3) !!}
                </div>
            </div>

            <table class="table-auto w-full" style="font-size: 11px;">
                <tr class="border-none">
                    <td class="font-extrabold">Patient Name:</td>
                    <td class="">{{ $patient->title . ' ' . $patient->first_name . ' ' . $patient->last_name }}</td>


                    <td class="font-extrabold">
                        @if(!empty($patient->relationship_title))
                            {{ $patient->relationship_title }}
                        @else
                            Father / Husband
                        @endif

                    </td>
                    <td class="">
                        {{ $patient->father_husband_name }}
                    </td>
                </tr>
                <tr>
                    <td class=" font-extrabold" style="font-weight: bolder;font-size: 14px;">
                        @if($chit->ipd_opd == 1)
                            OPD:
                        @else
                            IPD:
                        @endif
                    </td>
                    <td style="font-weight: bolder; font-size: 14px;">
                        @if($chit->ipd_opd == 1)
                            @if(!empty($chit->department))
                                {{$chit->department->name}} - Token No ({{ $chitNumber }})
                            @endif
                        @else
                            @if(!empty($chit->department))
                                {{$chit->department->name}} - ({{ $chitNumber }})
                            @endif
                        @endif
                    </td>

                    {{-- <td class=" font-extrabold">Father/Husband Name::</td>--}}
                    {{-- <td class="">{{ $patient->father_husband_name }}</td>--}}

                    <td class=" font-extrabold">Issue Date:</td>
                    <td class="">{{ \Carbon\Carbon::parse($chit->issued_date)->format('d-M-y h:i:s') }}</td>
                </tr>

                <tr>
                    <td class=" font-extrabold">Mobile:</td>
                    <td class="">
                        {{-- @if($patient->mobile == "0300-1234567")--}}
                        {{-- N/A--}}
                        {{-- @endif--}}
                        {{ $patient->mobile }}
                    </td>
                    <td class="font-extrabold">MR Number:</td>
                    <td class="font-extrabold">{{ date('y') . '-' . $chit->patient_id . '-' . $chit->id }}</td>
                </tr>








                @if($chit->government_non_gov == 0)
                    <tr>
                        <td class="font-extrabold">Category:</td>
                        <td>Non-Entitled</td>
                        {{-- <td class=" font-extrabold">Amount Payable:</td>--}}
                        {{-- <td class="font-extrabold">Rs. {{$chit->amount}}</td>--}}


                        <td class="font-extrabold">Age/Sex</td>
                        <td class="">
                            {{ $patient->age . ' ' . $patient->years_months }}/{{ ($patient->sex == 1 ? 'Male' : 'Female') }}
                        </td>

                        {{-- <td class=" font-extrabold">Printed By:</td>--}}
                        {{-- <td>--}}
                            {{-- {{ auth()->user()->name }}--}}
                            {{-- </td>--}}
                    </tr>
                @endif

                @if($chit->government_non_gov == 1)

                    {{-- <tr>--}}
                        {{-- <td class="font-extrabold">Category:</td>--}}
                        {{-- <td>Non-Entitled</td>--}}
                        {{-- --}}{{-- <td class=" font-extrabold">Amount Payable:</td>--}}
                        {{-- --}}{{-- <td class="font-extrabold">Rs. {{$chit->amount}}</td>--}}


                        {{-- <td class="font-extrabold">Age/Sex</td>--}}
                        {{-- <td class="">--}}
                            {{-- {{ $patient->age . ' ' . $patient->years_months }}/{{ ($patient->sex == 1?'Male':'Female')
                            }}--}}
                            {{-- </td>--}}

                        {{-- --}}{{-- <td class=" font-extrabold">Printed By:</td>--}}
                        {{-- --}}{{-- <td>--}}
                            {{-- --}}{{-- {{ auth()->user()->name }}--}}
                            {{-- --}}{{-- </td>--}}
                        {{-- </tr>--}}

                    <tr>
                        <td class="font-extrabold">Category:</td>
                        <td class="">Entitled</td>

                        <td class="font-extrabold">Age/Sex</td>
                        <td class="">
                            {{ $patient->age . ' ' . $patient->years_months }}/{{ ($patient->sex == 1 ? 'Male' : 'Female') }}
                        </td>

                        {{-- <td class=" font-extrabold">Printed By:</td>--}}
                        {{-- <td class="font-extrabold">--}}
                            {{-- {{ auth()->user()->name }}--}}
                            {{-- </td>--}}


                        {{-- <td class=" font-extrabold">--}}
                            {{-- Card No:--}}
                            {{-- </td>--}}
                        {{-- <td class="">--}}
                            {{-- {{$chit->government_card_no}}--}}
                            {{-- </td>--}}
                    </tr>
                @endif

                @if($chit->government_non_gov)


                    {{-- <tr>--}}
                        {{-- <td class="font-extrabold">--}}
                            {{-- Department--}}
                            {{-- </td>--}}
                        {{-- <td colspan="3">--}}
                            {{-- @if(!empty($chit->government_department_id))--}}
                            {{-- {{ \App\Models\GovernmentDepartment::find($chit->government_department_id)->name }}--}}
                            {{-- @else--}}
                            {{-- @if($patient->government_non_gov == 1)--}}
                            {{-- {{ $patient->government_department->name }}--}}
                            {{-- @endif--}}
                            {{-- @endif--}}
                            {{-- </td>--}}
                        {{-- </tr>--}}

                    <tr>
                        {{-- <td class="font-extrabold">Designation:</td>--}}
                        {{-- <td class="">{{$chit->designation}}</td>--}}

                        {{-- <td class="font-extrabold"></td>--}}
                        {{-- <td class=""></td>--}}
                        {{-- <td class=" font-extrabold">Printed By:</td>--}}
                        {{-- <td class="font-extrabold">--}}
                            {{-- {{ auth()->user()->name }}--}}
                            {{-- </td>--}}
                        {{-- <td class=" font-extrabold">Amount Payable:</td>--}}
                        {{-- <td class=" font-extrabold"> {{ $chit->amount }}</td>--}}

                    </tr>
                @endif

                <tr>
                    <td class=" font-extrabold">Address:</td>
                    <td>
                        {{ $chit->address  }}
                    </td>

                    <td class=" font-extrabold">Printed By:</td>
                    <td>
                        {{ auth()->user()->name }}
                    </td>
                </tr>

                @if($chit->government_department_id == 95)
                    <tr>
                        <td class="font-extrabold">Visit ID (SS):</td>
                        <td>{{ $chit->sehat_sahulat_visit_no }}</td>

                        <td class="font-extrabold">Patient ID (SS):</td>
                        <td>{{ $patient->sehat_sahulat_patient_id }}</td>
                    </tr>
                    <tr>
                        <td class="font-extrabold">CNIC:</td>
                        <td>{{ $patient->cnic }}</td>
                    </tr>
                @endif


                {{-- <tr>--}}
                    {{-- <td colspan="4"
                        style="margin: 0px; padding: 0px; font-size: 10px; font-weight: bold; text-align: center">--}}
                        {{-- نوٹ : یہ کمپیوٹر سے تیار کردہ پرچی ہے اور ہم اس پرچی کی دوسری کاپی فراہم نہیں کریں گے۔--}}
                        {{-- فیس کی واپسی صرف ایک گھنٹے میں مکمن ہے۔--}}
                        {{-- </td>--}}
                    {{-- </tr>--}}
                <tr
                    style="border-bottom: 1px solid black; margin: 0px; padding: 0px; font-size: 7px; text-align: center">
                    <td colspan="4">Software Developed By SeeChange Innovative - UAN: 0335-999.1441</td>
                </tr>

                {{-- <tr>--}}
                    {{-- <td class="font-bold text-center" colspan="4" style="font-size: 16px;">--}}
                        {{-- @if($chit->ipd_opd == 1)--}}
                        {{-- OPD:--}}
                        {{-- @else--}}
                        {{-- IPD:--}}
                        {{-- @endif--}}

                        {{-- @if($chit->ipd_opd == 1)--}}
                        {{-- @if(!empty($chit->department))--}}
                        {{-- {{$chit->department->name}}--}}
                        {{-- @else--}}
                        {{-- Emergency--}}
                        {{-- @endif--}}
                        {{-- @endif--}}
                        {{-- </td>--}}
                    {{-- </tr>--}}


            </table>


            @if(\Carbon\Carbon::parse($chit->issued_date)->format('d-M-y') == \Carbon\Carbon::now()->format('d-M-y'))
            @else
                <h1 class="text-3xl text-center text-gray-500" style="color: gray!important;">You have printed old chit</h1>
            @endif


            <br>
            <br>
            <br>
            <br>
            <br>
            <br>
            <br>
            <br>
            <br>
            <br>
        </div>
    </div>


    @section('custom_script')
        <script>
            // Execute this code on page load
            document.addEventListener("DOMContentLoaded", function () {
                // Store the current window height before opening the print dialog
                const initialHeight = window.innerHeight;

                // Show the print dialog when the page loads
                window.print();

                // Wait for a short period (e.g., 1 second) and then check the window height again
                setTimeout(function () {
                    const currentHeight = window.innerHeight;

                    // If the window height decreased, it indicates that the print dialog is open
                    // If the window height remains the same, it means the user pressed "Cancel"
                    if (currentHeight === initialHeight) {
                        // Redirect to the specified route
                        var flag = false;

                        @if($chit->ipd_opd == 1)
                            flag = true
                        @else
                            flag = false;
                        @endif

                                    }
                }, 1000); // Adjust the delay time as needed
            });

            // Define the redirectToLink function
            function redirectToLink(url) {
                window.location.href = url;
            }
        </script>

    @endsection
</x-app-layout>