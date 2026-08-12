<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Departments
        </h2>
    </x-slot>

    <div class="py-12">

        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-validation-errors class="mb-4"/>
            <x-success-message class="mb-4"/>
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-4">
                <form action="{{ route('feeType.update', $feeType->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-4">
                        <label class="block text-gray-700 font-bold mb-2" for="test_name">
                            Fee Name
                        </label>
                        <input name="type" id="name" type="text"  value="{{ $feeType->type }}" placeholder="Fee Type" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        @error('type')
                        <p class="text-red-500 text-xs italic">{{ $message }}</p>
                        @enderror
                    </div>


                    <div class="mb-4">
                        <label class="block text-gray-700 font-bold mb-2" for="name">
                            Total Fee
                        </label>
                        <input name="amount" id="name" type="number"  step="0.00" value="{{ $feeType->amount }}" placeholder="Total Fee" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        @error('amount')
                        <p class="text-red-500 text-xs italic">{{ $message }}</p>
                        @enderror
                    </div>



                    <div class="mb-4">
                        <label class="block text-gray-700 font-bold mb-2" for="hif">
                            HIF Fee
                        </label>
                        <input name="hif" id="hif" type="number"  step="0.00" value="{{ $feeType->hif }}" placeholder="HIF Fee" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        @error('hif')
                        <p class="text-red-500 text-xs italic">{{ $message }}</p>
                        @enderror
                    </div>




                    <div class="flex items-center justify-between">
                        <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline" type="submit">
                            Update
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
