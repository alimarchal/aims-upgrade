<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">

            <div class="flex justify-center items-center float-right">
                <a href="{{ route('dashboard') }}" class="float-right inline-flex items-center px-4 py-2 bg-red-800 border border-transparent
                        rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-red-900
                        focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150" \>
                    Back
                </a>
            </div>
            <br>

        </h2>


    </x-slot>

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
                            <th class="border-black border px-4 py-2">Entitled/Non-Entitled</th>
                            <th class="border-black border px-4 py-2">Issue Date</th>
                            <th class="border-black border px-4 py-2">Amount</th>
                            <th class="border-black border px-4 py-2">Print</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($issued_invoices ?? [] as $invoice)
                            <tr class="border-black">
                                <td class="border-black border px-4 py-2">{{$loop->iteration}}</td>
                                <td class="border-black border px-4 py-2 text-center">
                                    <a href="{{ route('patient.actions', $invoice->patient_id) }}" class="hover:underline text-blue-500">
                                        {{date('y')}}-{{ $invoice->id .'-' . $invoice->patient_id}}
                                    </a>
                                </td>

                                <td class="border-black border px-4 py-2 text-center">
                                    @if($invoice->government_non_government == 1)
                                        Entitled
                                    @else
                                        Non-Entitled
                                    @endif
                                </td>
                                <td class="border-black border px-4 py-2 text-center">
                                    {{ \Carbon\Carbon::parse($invoice->created_at)->format('d-M-y h:i:s a') }}
                                </td>
                                <td class="border-black border px-4 py-2 text-center">
                                    {{$invoice->total_amount}}
                                </td>
                                <td class="border-black border px-4 py-2 text-center">
                                    <a href="{{route('patient.patient_invoice',[$invoice->patient_id, $invoice->id])}}" class="text-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 m-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                                        </svg>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                        @if(!empty($issued_invoices))
                            <tr class="border-black bg-black">
                                <td class="border-black text-white border px-4 py-2 text-right font-extrabold" colspan="4">Total Amount</td>
                                <td class="border-black text-white border px-4 py-2 text-center font-extrabold">
                                    Rs. {{ number_format($issued_invoices->sum('total_amount'),2) }}</td>
                                <td class="border-black text-white border px-4 py-2 text-center font-extrabold"></td>
                            </tr>
                        @endif
                        </tbody>
                    </table>

                    <div class="mt-4">
                        {{ $issued_invoices?->links() }}
                    </div>
                </div>

            </div>
        </div>
    </div>

</x-app-layout>
