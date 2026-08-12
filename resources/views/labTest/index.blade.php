<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Lab Tests
            <div class="flex justify-center items-center float-right">
                <button onclick="window.print()" class="flex items-center px-4 py-2 text-gray-600 bg-white border rounded-lg focus:outline-none hover:bg-gray-100 transition-colors duration-200 transform dark:text-gray-200 dark:border-gray-200  dark:hover:bg-gray-700 ml-2" title="Members List">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                    </svg>
                </button>
            </div>
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
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-4">
                <div class="overflow-x-auto">
                    <table class="table-auto w-full border-collapse border border-black">
                        <thead>
                        <tr class="border-black">
                            <th class="border-black border px-4 py-2">No</th>
                            <th class="border-black border px-4 py-2">Test Name</th>
                            <th class="border-black border px-4 py-2">Government</th>
                            <th class="border-black border px-4 py-2">HIF</th>
                            <th class="border-black border px-4 py-2">Total</th>
                            <th class="border-black border px-4 py-2 print:hidden">Edit</th>
                            <th class="border-black border px-4 py-2 print:hidden">Delete</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($labTests as $labTest)
                            <tr class="border-black">
                                <td class="border-black border px-4 py-2">{{$loop->iteration}}</td>
                                <td class="border-black border px-4 py-2">{{$labTest->name}}</td>
                                <td class="border-black border px-4 py-2 text-center">{{number_format($labTest->government_fee,2)}}</td>
                                <td class="border-black border px-4 py-2 text-center">{{number_format($labTest->hif_fee,2)}}</td>
                                <td class="border-black border px-4 py-2 text-center">{{number_format($labTest->total_fee,2)}}</td>
                                <td class="border-black border px-4 py-2 text-center print:hidden">
                                    <a href="{{ route('labTest.edit', $labTest->id) }}" class="text-indigo-600 hover:text-indigo-900">Edit</a>

                                </td>
                                <td class="border-black border px-4 py-2 text-center print:hidden">
                                    <form action="{{ route('labTest.destroy', $labTest->id) }}" method="POST" class="inline-block">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" onclick="return confirm('Are you sure you want to delete this lab test?')" class="text-red-600 hover:text-red-900">Delete</button>
                                    </form>
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
