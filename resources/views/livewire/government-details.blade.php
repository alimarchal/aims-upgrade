<div>
    <div class="mb-4">
        <label class="block text-gray-700 font-bold mb-2">Government Department</label>
        <div wire:ignore class="mt-2">
            <select class="w-full border rounded-md text-gray-700 focus:outline-none focus:border-blue-500" name="government_department_id" id="select2">
                <option value="">None</option>
                @if(request()->routeIs('patient.edit'))
                    @foreach(\App\Models\GovernmentDepartment::orderBy('name','ASC')->get() as $dept)
                        <option value="{{$dept->id}}" @if($patient->government_department_id == $dept->id) selected @endif>{{ $dept->name }}</option>
                    @endforeach
                @else
                    @foreach(\App\Models\GovernmentDepartment::orderBy('name','ASC')->get() as $dept)
                        <option value="{{$dept->id}}">{{ $dept->name }}</option>
                    @endforeach
                @endif

            </select>
        </div>
    </div>

    @if(request()->routeIs('patient.edit'))
        @if($patient->government_non_gov)
            @php $isGovernment = 1; @endphp
        @endif
    @endif

    @if($isGovernment == 95)
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
             <div>
                <label for="sehat_sahulat_visit_no" class="block text-red-500 font-bold mb-2">Visit ID # Sehat Sahulat Program</label>
                <input type="text" name="sehat_sahulat_visit_no" id="sehat_sahulat_visit_no" value="{{ old('sehat_sahulat_visit_no') }}" class="w-full px-3 py-2 border rounded-md text-gray-700 focus:outline-none focus:border-blue-500" placeholder="Enter Visit ID">
            </div>

            <div>
                <label for="sehat_sahulat_patient_id" class="block text-red-500 font-bold mb-2">Patient ID Sehat Sahulat Program</label>
                <input type="text" name="sehat_sahulat_patient_id" id="sehat_sahulat_patient_id" value="{{ old('sehat_sahulat_patient_id') }}" class="w-full px-3 py-2 border rounded-md text-gray-700 focus:outline-none focus:border-blue-500" placeholder="Enter Patient ID">
            </div>

             <div>
                <label class="block text-gray-700 font-bold mb-2">Patient Entitlement</label>
                <label class="inline-flex items-center">
                    <input type="radio" class="form-radio" name="government_non_gov" value="1" checked>
                    <span class="ml-2">Government / Entitled</span>
                </label>
            </div>
        </div>
    @elseif($isGovernment)
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3 ">
            <div class="mb-4">
                <label for="government_card_no" class="block text-red-500 font-bold mb-2">Card No (CAUTION: Card no wil be audited)</label>
                @if(request()->routeIs('patient.edit'))
                    <input type="text" name="government_card_no" value="{{$patient->government_card_no}}" id="government_card_no" class="w-full px-3 py-2 border rounded-md text-gray-700 focus:outline-none focus:border-blue-500" placeholder="Government card number">
                @else
                    <input type="text" name="government_card_no" id="government_card_no" class="w-full px-3 py-2 border rounded-md text-gray-700 focus:outline-none focus:border-blue-500" placeholder="Government card number">
                @endif
            </div>
            <div class="mb-4">
                <label for="designation" class="block text-red-500 font-bold mb-2">Designation</label>
                @if(request()->routeIs('patient.edit'))
                    <input type="text" value="{{ $patient->designation }}" name="designation" id="designation" class="w-full px-3 py-2 border rounded-md text-gray-700 focus:outline-none focus:border-blue-500" placeholder="Government designation">
                @else
                    <input type="text" name="designation" id="designation" class="w-full px-3 py-2 border rounded-md text-gray-700 focus:outline-none focus:border-blue-500" placeholder="Government designation">
                @endif
            </div>
            <div>
                <label class="block text-gray-700 font-bold mb-2">Patient Entitlement</label>
                <label class="inline-flex items-center">
                    <input type="radio" class="form-radio" name="government_non_gov" value="1" checked>
                    <span class="ml-2">Government / Entitled</span>
                </label>
            </div>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
            <div class="mb-4">
                <label class="block text-gray-700 font-bold mb-2">Patient Entitlement </label>
                <input type="radio" class="form-radio" name="government_non_gov" value="0" checked>
                <span class="ml-2">Private</span>
                </label>
            </div>
        </div>

    @endif

</div>

@section('custom_script')
    <script>
        $(document).ready(function () {
            $('.js-example-basic-multiple').select2();


            $('.select2').select2();

            $('#select2').select2();
            $('#select2').on('change', function (e) {
                var data = $('#select2').select2("val");
                @this.
                set('isGovernment', data);
            });

            $(document).on('select2:open', () => {
                document.querySelector('.select2-search__field').focus();
            });

        });

        const cnicInput = document.getElementById("cnic");
        cnicInput.addEventListener("input", (event) => {
            let cnic = event.target.value;
            cnic = cnic.replace(/\D/g, ""); // Remove all non-numeric characters
            cnic = cnic.slice(0, 13); // Trim to 13 digits
            cnic = cnic.replace(/(\d{5})(\d{7})(\d{1})/, "$1-$2-$3"); // Add hyphens
            event.target.value = cnic;
        });

        const mobileInput = document.getElementById("mobile");
        mobileInput.addEventListener("input", (event) => {
            let mobile = event.target.value;
            mobile = mobile.replace(/\D/g, ""); // Remove all non-numeric characters
            mobile = mobile.slice(0, 11); // Trim to 11 digits
            mobile = mobile.replace(/(\d{4})(\d{7})/, "$1-$2"); // Add hyphen
            event.target.value = mobile;
        });

        document.addEventListener("DOMContentLoaded", function() {
            // Set focus on the "first_name" input field
            document.getElementById("first_name").focus();
        });
    </script>
@endsection

