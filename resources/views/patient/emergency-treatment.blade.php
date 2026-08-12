<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight ">
            Emergency Treatment Record
            <a href="{{route('patient.index')}}"
                class="float-right inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent
                        rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900
                        focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 ml-2">
                Back to Patient List
            </a>
        </h2>
    </x-slot>

    <div class="py-12">


        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden  sm:rounded-lg p-4 shadow-lg ">
                <x-validation-errors class="mb-4" />
                <x-success-message class="mb-4" />
                <div class="grid grid-cols-3 gap-4">
                    <div></div> <!-- Empty column for spacing -->
                    <div class="flex items-center justify-center">
                        <img src="{{ \Illuminate\Support\Facades\Storage::url('Aimsa8.png') }}" alt="Logo"
                            class="w-16 h-16">
                    </div>
                    <div class="flex flex-col items-end">
                        {{-- @php $patient_id = (string) $patient->id; @endphp--}}
                        {{-- {!! DNS2D::getBarcodeSVG($patient_id, 'QRCODE',3,3) !!}--}}
                    </div>
                </div>
                <h1 class="text-center text-2xl font-bold">{{ config('app.name') }}</h1>
                <h2 class="text-1xl text-center font-bold">Muzaffarabad, Azad Jammu & Kashmir</h2>
                <h2 class="text-1xl text-center font-extrabold mb-2">Serving the Humanity</h2>
                <table class="table-auto w-full">
                    <tr class="border-none">
                        <td class="font-extrabold">Patient Name:</td>
                        <td class="">{{ $patient->title . '. ' . $patient->first_name . ' ' . $patient->last_name }}
                        </td>
                        <td class="font-extrabold">Age/Sex</td>
                        <td class="">{{ $patient->age . ' ' . $patient->years_months }}/{{ ($patient->sex ==
    1 ? 'Male' : 'Female') }}
                        </td>
                    </tr>
                    <tr>
                        <td class=" font-extrabold">Medical Record No:</td>
                        <td class="">{{ \Carbon\Carbon::now()->format('y') . '-' . $patient->id }}</td>
                        <td class=" font-extrabold">Address:</td>
                        <td class="">
                            {{ $patient->address }}
                        </td>
                    </tr>
                    <tr>
                        <td class="font-extrabold">Gender:</td>
                        <td class="">
                            @if($patient->sex == 1)
                                Male
                            @else
                                Female
                            @endif
                        </td>
                        <td class="font-extrabold">Blood Group:</td>
                        <td class="">{{$patient->blood_group}}</td>
                    </tr>
                    <tr>

                        <td class="font-extrabold">
                            Patient Register By:
                        </td>
                        <td class="">
                            {{ $patient->user->name }}
                        </td>
                        <td class=" font-extrabold">Mobile:</td>
                        <td class="">{{$patient->mobile}}</td>
                    </tr>

                    <tr>
                        <td class=" font-extrabold">Registration Date:</td>
                        <td class="">
                            {{ \Carbon\Carbon::parse($patient->registration_date)->format('d-M-Y h:i:s a') }}
                        </td>
                        <td class=" font-extrabold">Viewing By:</td>
                        <td class="">
                            {{ Auth::user()->name }}
                        </td>
                    </tr>
                </table>
                <hr style="border: 0.5px solid black; margin-top: 20px;">
                <br>

                <!-- Emergency Treatment Form -->
                <form method="POST" action="{{ route('patient.emergency_treatment_store', $patient->id) }}"
                    id="emergency-form">
                    @csrf

                    <h3 class="text-lg font-semibold mb-4">Emergency Treatment Record
                        <span class="text-sm font-normal text-gray-600">
                            (Press <kbd class="px-2 py-1 text-xs font-semibold text-white bg-red-600 rounded">Tab</kbd>
                            to navigate,
                            <kbd class="px-2 py-1 text-xs font-semibold text-white bg-red-600 rounded">Ctrl+S</kbd> to
                            save)
                        </span>
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                        <!-- Disease Selection -->
                        <div>
                            <x-label for="disease_id" value="Disease (Optional)" />
                            <select name="disease_id" id="disease_id"
                                class="w-full px-3 py-2 border rounded-md text-gray-700 focus:outline-none focus:border-blue-500">
                                <option value="">-- None --</option>
                                @foreach($diseases as $disease)
                                    <option value="{{ $disease->id }}" {{ old('disease_id') == $disease->id ? 'selected' : ''
                                        }}>
                                        {{ $disease->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('disease_id')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                            <p class="text-gray-500 text-xs mt-1">
                                Press <kbd
                                    class="px-1 py-0.5 text-xs font-semibold text-white bg-red-600 rounded">Tab</kbd> to
                                move to Treatment Details
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <!-- Treatment Details -->
                        <div>
                            <x-label for="treatment_details" value="Treatment Details" :required="true" />
                            <textarea name="treatment_details" id="treatment_details" rows="6" required
                                class="w-full px-3 py-2 border rounded-md text-gray-700 focus:outline-none focus:border-blue-500"
                                placeholder="Enter detailed information about the treatment provided...">{{ old('treatment_details') }}</textarea>
                            @error('treatment_details')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                            <p class="text-gray-500 text-xs mt-1">
                                Press <kbd
                                    class="px-1 py-0.5 text-xs font-semibold text-white bg-red-600 rounded">Tab</kbd> to
                                move to Medications
                            </p>
                        </div>

                        <!-- Medications -->
                        <div>
                            <x-label for="medications" value="Medications" :required="true" />
                            <textarea name="medications" id="medications" rows="6" required
                                class="w-full px-3 py-2 border rounded-md text-gray-700 focus:outline-none focus:border-blue-500"
                                placeholder="List all medications administered or prescribed...">{{ old('medications') }}</textarea>
                            @error('medications')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                            <p class="text-gray-500 text-xs mt-1">
                                Press <kbd
                                    class="px-1 py-0.5 text-xs font-semibold text-white bg-red-600 rounded">Ctrl+S</kbd>
                                to save quickly
                            </p>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex items-center justify-end space-x-4 pt-4 border-t">
                        <a href="{{ route('patient.index') }}"
                            class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            Back to Patient List
                        </a>
                        <a href="{{ route('patient.index') }}"
                            class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400 focus:bg-gray-400 active:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            Cancel
                        </a>
                        <button type="submit" id="save-button"
                            class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            <span class="button-text">Save Emergency Treatment</span>
                            <kbd
                                class="ml-2 px-2 py-1 text-xs font-semibold text-blue-600 bg-white rounded">Ctrl+S</kbd>
                        </button>
                    </div>
                </form>

                <!-- Previous Emergency Treatments -->
                @if($patient->emergencyTreatments()->exists())
                    <div class="mt-8 pt-8 border-t" id="previous-treatments">
                        <x-success-message class="mb-4" />
                        <h3 class="text-lg font-semibold mb-4">Previous Emergency Treatments</h3>
                        <div class="overflow-x-auto">
                            <table class="table-auto w-full border-collapse border border-black">
                                <thead>
                                    <tr class="bg-gray-100 border-black">
                                        <th class="border-black border px-4 py-2 text-left">S.No</th>
                                        <th class="border-black border px-4 py-2 text-left">Date & Time</th>
                                        <th class="border-black border px-4 py-2 text-left">Disease</th>
                                        <th class="border-black border px-4 py-2 text-left">Treatment Details</th>
                                        <th class="border-black border px-4 py-2 text-left">Medications</th>
                                        <th class="border-black border px-4 py-2 text-left">Recorded By</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($patient->emergencyTreatments()->oldest()->get() as $treatment)
                                                            <tr class="border-black hover:bg-gray-50">
                                                                <td class="border-black border px-4 py-2 text-center">{{ $loop->iteration }}</td>
                                                                <td class="border-black border px-4 py-2">
                                                                    {{ \Carbon\Carbon::parse($treatment->created_at)->format('d-M-Y h:i A') }}
                                                                </td>
                                                                <td class="border-black border px-4 py-2">
                                                                    @if($treatment->disease)
                                                                        <span class="bg-red-100 text-red-800 text-xs font-medium px-2.5 py-0.5 rounded">
                                                                            {{ $treatment->disease->name }}
                                                                        </span>
                                                                    @else
                                                                        <span class="text-gray-400 text-xs">-- None --</span>
                                                                    @endif
                                                                </td>
                                                                <td class="border-black border px-4 py-2">
                                                                    <p class="text-sm text-gray-600 whitespace-pre-line">{{
                                        Str::limit($treatment->treatment_details, 100) }}</p>
                                                                </td>
                                                                <td class="border-black border px-4 py-2">
                                                                    <p class="text-sm text-gray-600 whitespace-pre-line">{{
                                        Str::limit($treatment->medications, 100) }}</p>
                                                                </td>
                                                                <td class="border-black border px-4 py-2">
                                                                    <span class="text-sm">{{ $treatment->user->name }}</span>
                                                                </td>
                                                            </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

            </div>
        </div>
    </div>

    @section('custom_script')
        <script>
            $(document).ready(function () {
                // Initialize Select2 for disease dropdown
                $('#disease_id').select2({
                    placeholder: '-- None --',
                    allowClear: true
                });

                // Check if we need to scroll to previous treatments
                @if(session('scroll_to'))
                    setTimeout(function () {
                        const element = document.getElementById('{{ session('scroll_to') }}');
                        if (element) {
                            // Smooth scroll to element
                            element.scrollIntoView({ behavior: 'smooth', block: 'start' });

                            // Add highlight effect
                            element.style.transition = 'background-color 0.5s ease';
                            element.style.backgroundColor = '#fef3c7'; // Light yellow highlight

                            // Remove highlight after 2 seconds
                            setTimeout(function () {
                                element.style.backgroundColor = '';
                            }, 2000);
                        }
                    }, 100);
                @else
                    // Automatically focus on the disease dropdown when page loads (only if not scrolling)
                    setTimeout(function () {
                        $('#disease_id').select2('open');
                    }, 300);
                @endif

                // Also allow opening with spacebar when focused
                $('#disease_id').on('select2:open', function () {
                    setTimeout(function () {
                        document.querySelector('.select2-search__field').focus();
                    }, 100);
                });

                // Keyboard shortcut: Ctrl+S to save form
                $(document).on('keydown', function (e) {
                    // Check for Ctrl+S (Windows/Linux) or Cmd+S (Mac)
                    if ((e.ctrlKey || e.metaKey) && e.key === 's') {
                        e.preventDefault(); // Prevent browser's save dialog
                        $('#emergency-form').submit();
                        return false;
                    }
                });

                // Visual feedback when save button is clicked
                $('#save-button').on('click', function (e) {
                    // Don't prevent default - let form submit naturally
                    $(this).prop('disabled', true);
                    $(this).find('.button-text').html('Saving... ⏳');
                });

                // Re-enable button if form submission fails (e.g., validation error)
                $('#emergency-form').on('submit', function () {
                    $('#save-button').prop('disabled', true);
                    $('#save-button').find('.button-text').html('Saving... ⏳');
                });
            });
        </script>
    @endsection
</x-app-layout>