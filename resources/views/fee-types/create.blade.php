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
                <form action="{{ route('feeType.store') }}" method="POST">
                    @csrf


                    <div class="mb-4">
                        <label for="name" class="block text-gray-700 font-bold mb-2">Fee Category</label>
                        <select name="fee_category_id" style="width: 100%" class="js-example-basic-multiple w-full px-3 py-2 border rounded-md text-gray-700 focus:outline-none focus:border-blue-500">
                            <option value="">None</option>
                            @foreach(\App\Models\FeeCategory::orderBy('name','ASC')->get() as $labTest)
                                <option value="{{$labTest->id}}">{{$labTest->name}}</option>
                            @endforeach
                        </select>
                    </div>


                    <div class="mb-4">
                        <label class="block text-gray-700 font-bold mb-2" for="test_name">
                            Fee Name
                        </label>
                        <input name="type" id="name" type="text"  placeholder="Fee Name" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        @error('type')
                        <p class="text-red-500 text-xs italic">{{ $message }}</p>
                        @enderror
                    </div>


                    <div class="mb-4">
                        <label class="block text-gray-700 font-bold mb-2" for="name">
                            Total Fee
                        </label>
                        <input name="amount" id="name" type="number" step="0.00"  placeholder="Total Fee" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        @error('amount')
                        <p class="text-red-500 text-xs italic">{{ $message }}</p>
                        @enderror
                    </div>


                    <div class="mb-4">
                        <label class="block text-gray-700 font-bold mb-2" for="hif">
                            HIF Fee
                        </label>
                        <input name="hif" id="hif" type="number" step="0.00"  placeholder="HIF Fee" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        @error('hif')
                        <p class="text-red-500 text-xs italic">{{ $message }}</p>
                        @enderror
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
