<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight ">
            Patient Cart Invoice
            <a href="{{route('labTest.create')}}" class="float-right inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent
                        rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900
                        focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150" \>
                Create Lab Tests
            </a>


        </h2>



    </x-slot>

    <div class="py-12">


        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-validation-errors class="mb-4"/>
            <x-success-message class="mb-4"/>
            <div class="bg-white overflow-hidden  sm:rounded-lg p-4 ">




                <img src="{{\Illuminate\Support\Facades\Storage::url('Aimsa8.png')}}" alt="Logo" class="w-16 h-16 m-auto">
                <h1 class="text-center text-2xl font-bold">Abbas Institute of Medical Sciences (AIMS)</h1>
                <h2 class="text-1xl text-center font-bold ">Muzaffarabad, Azad Jammu & Kashmir</h2>
                <h2 class="text-1xl text-center font-bold mb-2">Patient Test Invoice Generating</h2>
                <table class="table-auto w-full">
                    <tr class="border-none">
                        <td class="font-extrabold">Patient Name:</td>
                        <td class="">{{ $patient->first_name . ' ' . $patient->last_name }}</td>
                        <td class="font-extrabold">Age/Sex</td>
                        <td class="">{{ $patient->age . ' ' . $patient->years_months }}/{{ ($patient->sex == 1?'Male':'Female') }}
                        </td>
                        {{--                                        <td class="border-black border">{{ \Carbon\Carbon::parse($patient->created_at)->format('d-M-y h:i:s') }}</td>--}}
                    </tr>
                    <tr>
                        <td class=" font-extrabold">Father/Husband Name:</td>
                        <td class="">{{ $patient->father_husband_name }}</td>
                        <td class=" font-extrabold">Registration Date:</td>
                        <td class="">{{ \Carbon\Carbon::parse($patient->registration_date)->format('d-M-y h:i:s') }}</td>
                    </tr>
                    <tr>
                        <td class=" font-extrabold">Mobile:</td>
                        <td class="">{{$patient->mobile}}</td>
                        <td class=" font-extrabold">
{{--                            @if($chit->ipd_opd == 1)--}}
{{--                                OPD--}}
{{--                            @else--}}
{{--                                IPD--}}
{{--                            @endif--}}
                        </td>
                        <td class="">
{{--                            @if(!empty($chit->department))--}}
{{--                                {{$chit->department->name}}--}}
{{--                            @else--}}
{{--                                Emergency--}}
{{--                            @endif--}}
                        </td>
                    </tr>

                    <tr>
                        <td class=" font-extrabold">Reference No:</td>
                        <td class="">
                            {{'P' . $patient->id}}-{{date('ymd')}}
                        </td>
                        <td class=" font-extrabold">Entitlement:</td>
                        <td class="">
{{--                            @if($chit->amount == 0)--}}
{{--                                Government Servant--}}
{{--                            @else--}}
{{--                                Private--}}
{{--                            @endif--}}
                        </td>
                    </tr>

                    <tr>
                        <td class=" font-extrabold">Amount Payable:</td>
                        <td class="font-extrabold">
{{--                            Rs. {{$chit->amount}}--}}
                        </td>
                        <td class="">
{{--                            @if($patient->government_non_gov == 1)--}}
{{--                                Department--}}
{{--                            @endif--}}

                        </td>
                        <td class="font-extrabold">
{{--                            @if($patient->government_non_gov == 1)--}}
{{--                                {{ $patient->government_department->name }}--}}
{{--                            @endif--}}


                        </td>
                    </tr>
                </table>
                <hr class="border-black h-2">

                <br>
                @if($patient->government_non_gov == 1)
                    <p class="text-center text-4xl text-red-600 mb-2 font-bold mb-0">
                        Entitled - Government Employee
                    </p>
                @endif
                <form action="{{route('patient.patient_test_invoice_generate')}}" method="post" id="payment-form">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-1 gap-4 mb-4 ">
                        <div>
                            <input type="hidden" name="patient_id" value="{{$patient->id}}">
                            <label for="name" class="block text-gray-700 font-bold mb-2">Test Name</label>
                            <select name="patient_test[]" multiple="multiple" class="js-example-basic-multiple w-full px-3 py-2 border rounded-md text-gray-700 focus:outline-none focus:border-blue-500">
                                @foreach(\App\Models\LabTest::orderBy('name','ASC')->get() as $labTest)
                                    <option value="{{$labTest->id}}">{{$labTest->name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="flex items-center justify-between">
                        <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline" type="submit">
                            Create
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @section('custom_script')
        <script>
            $(document).ready(function () {
                $('.js-example-basic-multiple').select2();
            });
        </script>
    @endsection
</x-app-layout>
