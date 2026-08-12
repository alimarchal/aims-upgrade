<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Reports/IPD') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-12 gap-6 ">
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
                @can('view monthly income reports')
                    <a href="{{ route('reports.ipd.monthly-income-statement') }}"
                        class="transform hover:scale-105 transition duration-300 shadow-xl rounded-lg col-span-12 sm:col-span-6 xl:col-span-3 intro-y bg-white">
                        <div class="p-5">
                            <div class="grid grid-cols-3 gap-1">
                                <div class="col-span-2">
                                    <div class="text-3xl font-bold leading-8">
                                        Monthly
                                    </div>
                                    <div class="mt-1 text-base font-bold text-gray-600">
                                        Income Statement
                                    </div>
                                </div>
                                <div class="col-span-1 flex items-center justify-end">
                                    <img src="{{ url('images/reports.png') }}" alt="monthly income statement"
                                        class="h-12 w-12">
                                </div>
                            </div>
                        </div>
                    </a>
                @endcan
                <a href="{{ route('reports.misc.category-wise') }}"
                    class="transform  hover:scale-105 transition duration-300 shadow-xl rounded-lg col-span-12 sm:col-span-6 xl:col-span-3 intro-y bg-white">
                    <div class="p-5">
                        <div class="grid grid-cols-3 gap-1">
                            <div class="col-span-2">
                                <div class="text-3xl font-bold leading-8">
                                    Department
                                </div>
                                <div class="mt-1 text-base  font-bold text-gray-600">
                                    Wise
                                </div>
                            </div>
                            <div class="col-span-1 flex items-center justify-end">
                                <img src="{{ url('images/reports.png') }}" alt="employees on leave" class="h-12 w-12">
                            </div>
                        </div>
                    </div>
                </a>
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
            </div>
        </div>
    </div>
    @section('custom_script')
    @endsection
</x-app-layout>