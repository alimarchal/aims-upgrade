<x-app-layout>
    @section('custom_header')
        <style>
            @media print {
                @page {
                    margin-top: 20px;
                    size: portrait;
                }

                #filters,
                #actions {
                    display: none !important;
                }

                table {
                    font-size: 14px !important;
                    width: 100%;
                }
            }
        </style>
    @endsection

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight inline-block">
            {{ __('Monthly Income Statement') }}
        </h2>

        <div id="actions" class="flex justify-center items-center float-right">
            <button onclick="window.print()"
                class="flex items-center px-4 py-2 text-gray-600 bg-white border rounded-lg focus:outline-none hover:bg-gray-100 transition-colors duration-200 transform dark:text-gray-200 dark:border-gray-200 dark:hover:bg-gray-700 ml-2"
                title="Print Report">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
            </button>
        </div>
    </x-slot>

    <div id="filters" class="max-w-7xl mx-auto mt-8 px-4 sm:px-6 lg:px-8">
        <div class="rounded-xl p-4 bg-white shadow-lg">
            <form action="" method="GET">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                    <div>
                        <label for="year" class="block text-gray-700 font-bold mb-2">Fiscal Year Start</label>
                        <select name="year" id="year"
                            class="w-full px-3 py-2 border rounded-md text-gray-700 focus:outline-none focus:border-blue-500">
                            @foreach (range(now()->year - 5, now()->year + 1) as $year)
                                <option value="{{ $year }}" @selected($fiscalYearStart === $year)>
                                    {{ $year }}-{{ substr((string) ($year + 1), -2) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
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

    <div class="py-10">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden p-4">
                <div class="overflow-x-auto">
                    <h3 class="text-center text-3xl font-bold mb-2">Abbas Institute of Medical Sciences Muzaffarabad
                    </h3>
                    <p class="text-center text-4xl font-extrabold mb-8">Income Statement for Year {{ $yearLabel }}</p>

                    <table class="table-auto w-full border-collapse border border-black" style="font-size: 20px;">
                        <thead>
                            <tr class="border-black bg-gray-100">
                                <th class="border-black border px-4 py-3 text-center">S.N</th>
                                <th class="border-black border px-4 py-3 text-center">Month</th>
                                <th class="border-black border px-4 py-3 text-center">Income</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($monthlyIncomeData as $item)
                                <tr class="border-black">
                                    <td class="border-black border px-4 py-3 text-center">{{ $item['sn'] }}</td>
                                    <td class="border-black border px-4 py-3 text-center">{{ $item['month'] }}</td>
                                    <td class="border-black border px-4 py-3 text-center">
                                        {{ number_format($item['income'], 0, '.', '') }}</td>
                                </tr>
                            @endforeach
                            <tr class="border-black font-extrabold">
                                <td class="border-black border px-4 py-3 text-center"></td>
                                <td class="border-black border px-4 py-3 text-center">Total</td>
                                <td class="border-black border px-4 py-3 text-center">
                                    {{ number_format($totalIncome, 0, '.', '') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>