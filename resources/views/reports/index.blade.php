<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('All Reports') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-12 gap-6 ">
                @can('view opd reports')
                    <a href="{{ route('reports.opd') }}"
                        class="transform  hover:scale-105 transition duration-300 shadow-xl rounded-lg col-span-12 sm:col-span-6 xl:col-span-3 intro-y bg-white">
                        <div class="p-5">
                            <div class="grid grid-cols-3 gap-1">
                                <div class="col-span-2">
                                    <div class="text-3xl font-bold leading-8">
                                        OPD
                                    </div>

                                    <div class="mt-1 text-base  font-bold text-gray-600">
                                        Reports
                                    </div>
                                </div>
                                <div class="col-span-1 flex items-center justify-end">
                                    <img src="{{ url('images/opd.jpeg') }}" alt="employees on leave" class="w-18">

                                </div>
                            </div>
                        </div>
                    </a>
                @endcan

                @can('view daily reports')
                    <a href="{{ route('reports.opd.reportDailyIPD') }}"
                        class="transform  hover:scale-105 transition duration-300 shadow-xl rounded-lg col-span-12 sm:col-span-6 xl:col-span-3 intro-y bg-white">
                        <div class="p-5">
                            <div class="grid grid-cols-3 gap-1">
                                <div class="col-span-2">
                                    <div class="text-3xl font-bold leading-8">
                                        Daily
                                    </div>
                                    <div class="mt-1 text-base  font-bold text-gray-600">
                                        User Wise
                                    </div>
                                </div>
                                <div class="col-span-1 flex items-center justify-end">
                                    <img src="{{ url('images/reports.png') }}" alt="employees on leave" class="h-12 w-12">
                                </div>
                            </div>
                        </div>
                    </a>
                @endcan

                @can('view department reports')
                    <a href="{{ route('reports.misc.category-wise-two') }}"
                        class="transform  hover:scale-105 transition duration-300 shadow-xl rounded-lg col-span-12 sm:col-span-6 xl:col-span-3 intro-y bg-white">
                        <div class="p-5">
                            <div class="grid grid-cols-3 gap-1">
                                <div class="col-span-2">
                                    <div class="text-3xl font-bold leading-8">
                                        Department
                                    </div>
                                    <div class="mt-1 text-base  font-bold text-gray-600">
                                        Wise Two
                                    </div>
                                </div>
                                <div class="col-span-1 flex items-center justify-end">
                                    <img src="{{ url('images/reports.png') }}" alt="employees on leave" class="h-12 w-12">
                                </div>
                            </div>
                        </div>
                    </a>
                @endcan

                @can('view admission reports')
                    <a href="{{ route('reports.misc.admission') }}"
                        class="transform  hover:scale-105 transition duration-300 shadow-xl rounded-lg col-span-12 sm:col-span-6 xl:col-span-3 intro-y bg-white">
                        <div class="p-5">
                            <div class="grid grid-cols-3 gap-1">
                                <div class="col-span-2">
                                    <div class="text-3xl font-bold leading-8">
                                        General
                                    </div>
                                    <div class="mt-1 text-base  font-bold text-gray-600">
                                        Information
                                    </div>
                                </div>
                                <div class="col-span-1 flex items-center justify-end">
                                    <img src="{{ url('images/reports.png') }}" alt="employees on leave" class="h-12 w-12">
                                </div>
                            </div>
                        </div>
                    </a>
                @endcan

                @can('view emergency reports')
                    <a href="{{ route('reports.emergency_treatments') }}"
                        class="transform  hover:scale-105 transition duration-300 shadow-xl rounded-lg col-span-12 sm:col-span-6 xl:col-span-3 intro-y bg-white">
                        <div class="p-5">
                            <div class="grid grid-cols-3 gap-1">
                                <div class="col-span-2">
                                    <div class="text-3xl font-bold leading-8">
                                        Emergency
                                    </div>
                                    <div class="mt-1 text-base  font-bold text-gray-600">
                                        Treatments
                                    </div>
                                </div>
                                <div class="col-span-1 flex items-center justify-end">
                                    <img src="{{ url('images/emergency.png') }}" alt="emergency treatments"
                                        class="h-16 w-16">
                                </div>
                            </div>
                        </div>
                    </a>
                @endcan

                @can('view ssp reports')
                    <a href="{{ route('reports.ssp.claims') }}"
                        class="transform hover:scale-105 transition duration-300 shadow-xl rounded-lg col-span-12 sm:col-span-6 xl:col-span-3 intro-y bg-emerald-700">
                        <div class="p-5">
                            <div class="grid grid-cols-3 gap-1">
                                <div class="col-span-2">
                                    <div class="text-3xl font-bold leading-8 text-white">
                                        SSP
                                    </div>
                                    <div class="mt-1 text-base font-bold text-emerald-200">
                                        Insurance Claims
                                    </div>
                                </div>
                                <div class="col-span-1 flex items-center justify-end">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-emerald-200" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </a>
                @endcan



                {{-- <a href="#"
                    class="transform  hover:scale-105 transition duration-300 shadow-xl rounded-lg col-span-12 sm:col-span-6 xl:col-span-3 intro-y bg-white">--}}
                    {{-- <div class="p-5">--}}
                        {{-- <div class="grid grid-cols-3 gap-1">--}}
                            {{-- <div class="col-span-2">--}}
                                {{-- <div class="text-3xl font-bold leading-8">--}}
                                    {{-- 0--}}
                                    {{-- </div>--}}
                                {{-- <div class="mt-1 text-base  font-bold text-gray-600">--}}
                                    {{-- Departmental Reports--}}
                                    {{-- </div>--}}
                                {{-- </div>--}}
                            {{-- <div class="col-span-1 flex items-center justify-end">--}}
                                {{-- <img src="{{ Storage::url('images/3127109.png') }}" alt="legal case"
                                    class="h-12 w-12">--}}
                                {{-- </div>--}}
                            {{-- </div>--}}
                        {{-- </div>--}}
                    {{-- </a>--}}
                {{-- <a href="#"
                    class="transform  hover:scale-105 transition duration-300 shadow-xl rounded-lg col-span-12 sm:col-span-6 xl:col-span-3 intro-y bg-white">--}}

                    {{-- <div class="p-5">--}}
                        {{-- <div class="grid grid-cols-3 gap-1">--}}
                            {{-- <div class="col-span-2">--}}
                                {{-- <div class="text-3xl font-bold leading-8">--}}
                                    {{-- &nbsp;--}}
                                    {{-- </div>--}}
                                {{-- <div class="mt-1 text-base font-bold text-gray-600">--}}
                                    {{-- Misc Reports--}}
                                    {{-- </div>--}}
                                {{-- </div>--}}
                            {{-- <div class="col-span-1 flex items-center justify-end">--}}

                                {{-- <img src="{{ Storage::url('images/2906361.png') }}" alt="legal case"
                                    class="h-12 w-12">--}}
                                {{-- </div>--}}
                            {{-- </div>--}}
                        {{-- </div>--}}
                    {{-- </a>--}}
            </div>
        </div>
    </div>
    @section('custom_script')
    @endsection
</x-app-layout>