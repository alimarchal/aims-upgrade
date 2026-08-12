<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight inline">
            {{ __('Patient / Actions') }}
        </h2>


        <div class="flex justify-center items-center float-right">
            <a href="{{ route('patient.index') }}"
                class="float-right inline-flex items-center px-4 py-2 bg-red-800 border border-transparent
                        rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-red-900
                        focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150" \>
                Back
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-12 gap-6 ">
                <a href="{{ route('patient.issued-chits', $patient->id) }}"
                    class="transform  hover:scale-105 transition duration-300 shadow-xl rounded-lg col-span-12 sm:col-span-6 xl:col-span-3 intro-y bg-white">
                    <div class="p-5">
                        <div class="grid grid-cols-3 gap-1">
                            <div class="col-span-2">
                                <div class="text-3xl font-bold leading-8">{{$patient->chits->count()}}</div>
                                <div class="mt-1 text-base  font-bold text-gray-600">
                                    Issued Chits
                                </div>
                            </div>
                            <div class="col-span-1 flex items-center justify-end">
                                <img src="{{Storage::url('chits.png')}}" alt="employees on leave" class="h-12 w-12">
                            </div>
                        </div>
                    </div>
                </a>
                <a href="{{ route('patient.issued-invoices', $patient->id) }}"
                    class="transform  hover:scale-105 transition duration-300 shadow-xl rounded-lg col-span-12 sm:col-span-6 xl:col-span-3 intro-y bg-white">
                    <div class="p-5">
                        <div class="grid grid-cols-3 gap-1">
                            <div class="col-span-2">
                                <div class="text-3xl font-bold leading-8">
                                    {{ \App\Models\Invoice::where('patient_id', $patient->id)->count() }}
                                </div>
                                <div class="mt-1 text-base  font-bold text-gray-600">
                                    Issued Invoices
                                </div>
                            </div>
                            <div class="col-span-1 flex items-center justify-end">

                                <img src="{{ Storage::url('issue_new_chit.png') }}" alt="legal case" class="h-12 w-12">
                            </div>
                        </div>
                    </div>
                </a>

                @if(Auth::user()->hasRole(['Administrator', 'Front Desk/Receptionist', 'Super-Admin']))
                    <a href="{{ route('patient.proceed', $patient->id) }}"
                        class="transform  hover:scale-105 transition duration-300 shadow-xl rounded-lg col-span-12 sm:col-span-6 xl:col-span-3 intro-y bg-white">
                        <div class="p-5">
                            <div class="grid grid-cols-3 gap-1">
                                <div class="col-span-2">
                                    <div class="text-3xl font-bold leading-8">
                                        Bill
                                    </div>
                                    <div class="mt-1 text-base  font-bold text-gray-600">
                                        Make Invoice
                                    </div>
                                </div>
                                <div class="col-span-1 flex items-center justify-end">
                                    <img src="https://cdn-icons-png.flaticon.com/512/3127/3127109.png" alt="legal case"
                                        class="h-12 w-12">
                                </div>
                            </div>
                        </div>
                    </a>
                    <a href="{{ route('patient.issue-new-chit', $patient->id) }}"
                        class="transform  hover:scale-105 transition duration-300 shadow-xl rounded-lg col-span-12 sm:col-span-6 xl:col-span-3 intro-y bg-white">

                        <div class="p-5">
                            <div class="grid grid-cols-3 gap-1">
                                <div class="col-span-2">
                                    <div class="text-3xl font-bold leading-8">
                                        Issue
                                    </div>
                                    <div class="mt-1 text-base font-bold text-gray-600">
                                        New Chit
                                    </div>
                                </div>
                                <div class="col-span-1 flex items-center justify-end">
                                    <img src="{{ Storage::url('issue_new_chit.png') }}" alt="legal case" class="h-12 w-12">
                                    {{-- <img src="https://cdn-icons-png.flaticon.com/512/2906/2906361.png" alt="legal case"
                                        class="h-12 w-12">--}}
                                </div>
                            </div>
                        </div>
                    </a>

                    <a href="{{ route('patient.emergency_treatment', $patient->id) }}"
                        class="transform  hover:scale-105 transition duration-300 shadow-xl rounded-lg col-span-12 sm:col-span-6 xl:col-span-3 intro-y bg-white">

                        <div class="p-5">
                            <div class="grid grid-cols-3 gap-1">
                                <div class="col-span-2">
                                    <div class="text-3xl font-bold leading-8">
                                        Emergency
                                    </div>
                                    <div class="mt-1 text-base font-bold text-gray-600">
                                        Treatment
                                    </div>
                                </div>
                                <div class="col-span-1 flex items-center justify-end">
                                    <img src="{{ Storage::url('pulse.png') }}" alt="legal case" class="h-12 w-12">
                                    {{-- <img src="https://cdn-icons-png.flaticon.com/512/2906/2906361.png" alt="legal case"
                                        class="h-12 w-12">--}}
                                </div>
                            </div>
                        </div>
                    </a>
                @endif
            </div>

        </div>
    </div>
    @section('custom_script')
        <script>


        </script>
    @endsection
</x-app-layout>