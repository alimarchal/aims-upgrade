<div>
    <div>
        <label for="department_name" class="block text-gray-700 font-bold mb-2">Department Name</label>
        <select name="department_name" id="department_name" class="w-full px-3 py-2 border rounded-md text-gray-700 focus:outline-none focus:border-blue-500">
            <option value="">Select Department</option>
            @foreach(\App\Models\GovernmentDepartment::orderBy('id', 'DESC')->get() as $dep)
                <option value="{{$dep->id}}">{{$dep->name}}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label for="government_card_no" class="block text-gray-700 font-bold mb-2">Card No</label>
        <input type="text" name="government_card_no" id="government_card_no" class="w-full px-3 py-2 border rounded-md text-gray-700 focus:outline-none focus:border-blue-500" placeholder="Government card number">
    </div>
</div>
