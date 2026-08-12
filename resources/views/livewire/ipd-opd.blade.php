<div class="grid grid-cols-1 md:grid-cols-4 gap-3 mb-4">
    <div>
        <label for="ipd_opd" class="block text-gray-700 font-bold mb-2">IPD/OPD</label>
        <select name="ipd_opd" wire:model.live="ipd_opd" id="ipd_opd"
            class=" w-full px-3 py-2 border rounded-md text-gray-700 focus:outline-none focus:border-blue-500">
            <option value="">Please select...</option>
            <option value="1">OPD</option>
            <option value="0">IPD</option>
        </select>
    </div>

    <div>
        <label for="department_id" class="block text-gray-700 font-bold mb-2"> IPD / OPD</label>
        <select name="department_id"
            class="w-full px-3 py-2 border rounded-md text-gray-700 focus:outline-none focus:border-blue-500">
            <option value="" selected>None</option>
            @if($ipd_opd != 'NONE')
                @if($ipd_opd == 1)
                    @foreach(\App\Models\Department::orderBy('name', 'ASC')->where('category', 'OPD')->get() as $dept)
                        <option value="{{$dept->id}}" {{ old('department_id') === $dept->id ? 'selected' : '' }}>{{ $dept->name }}
                        </option>
                    @endforeach
                @elseif($ipd_opd == 0)
                    {{-- @foreach(\App\Models\Department::orderBy('name', 'ASC')->where('category','Emergency')->get() as
                    $dept)--}}
                    {{-- <option value="{{$dept->id}}" {{ old('department_id')===$dept->id ? 'selected' : '' }}>{{ $dept->name
                        }}</option>--}}
                    {{-- @endforeach--}}
                @endif
            @endif
        </select>
    </div>

</div>