<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Patient / Edit
        </h2>
    </x-slot>

    <div class="py-12">

        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-validation-errors class="mb-4"/>
            <x-success-message class="mb-4"/>
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-4">
                <form action="{{ route('patient.update', $patient->id) }}" method="POST">
                    @csrf
                    @method('PUT')


                    <div class="grid grid-cols-1 md:grid-cols-4 gap-3 ">
                        <div>
                            <label for="title" class="block text-gray-700 font-bold mb-2">Title</label>
                            <select name="title" id="title" class="w-full px-3 py-2 border rounded-md text-gray-700 focus:outline-none focus:border-blue-500">
                                <option value="">Select Title</option>
                                <option value="Mr." {{ $patient->title === 'Mr.' ? 'selected' : '' }}>Mr.</option>
                                <option value="Mrs." {{ $patient->title === 'Mrs.' ? 'selected' : '' }}>Mrs.</option>
                                <option value="Miss" {{ $patient->title === 'Miss' ? 'selected' : '' }}>Miss</option>
                                <option value="Ms." {{ $patient->title === 'Ms.' ? 'selected' : '' }}>Ms.</option>
                                <option value="S/O"  {{ $patient->title === 'S/O' ? 'selected' : '' }}>S/O.</option>
                                <option value="D/O"  {{ $patient->title === 'D/O' ? 'selected' : '' }}>D/O.</option>
                                <option value="M/O"  {{ $patient->title === 'M/O' ? 'selected' : '' }}>M/O.</option>
                                <option value="F/O"  {{ $patient->title === 'F/O' ? 'selected' : '' }}>F/O.</option>
                            </select>
                        </div>
                        <div>
                            <label for="first_name" class="block text-gray-700 font-bold mb-2">First Name</label>
                            <input type="text" name="first_name" id="first_name" class="w-full px-3 py-2 border rounded-md text-gray-700 focus:outline-none focus:border-blue-500" placeholder="Enter first name" value="{{ $patient->first_name }}">
                        </div>
                        <div>
                            <label for="last_name" class="block text-gray-700 font-bold mb-2">Last Name</label>
                            <input type="text" name="last_name" id="last_name" class="w-full px-3 py-2 border rounded-md text-gray-700 focus:outline-none focus:border-blue-500" placeholder="Enter last name" value="{{ $patient->last_name }}">
                        </div>
                        <div>
                            <label for="father_husband_name" class="block text-gray-700 font-bold mb-2">Father/Husband Name</label>
                            <input type="text" name="father_husband_name" id="father_husband_name" class="w-full px-3 py-2 border rounded-md text-gray-700 focus:outline-none focus:border-blue-500" placeholder="Enter father/husband name" value="{{ $patient->father_husband_name }}">
                        </div>
                        <div>
                            <label for="age" class="block text-gray-700 font-bold mb-2">Age</label>
                            <input type="number" name="age" id="age" class="w-full px-3 py-2 border rounded-md text-gray-700 focus:outline-none focus:border-blue-500" placeholder="Enter age" value="{{ $patient->age }}">
                        </div>
                        <div>
                            <label for="years_months" class="block text-gray-700 font-bold mb-2">Years/Months</label>
                            <select name="years_months" id="years_months" class="w-full px-3 py-2 border rounded-md text-gray-700 focus:outline-none focus:border-blue-500">
                                <option value="">Select Year(s)</option>
                                <option value="Year(s)" {{ $patient->years_months === 'Year(s)' ? 'selected' : '' }}>Years</option>
                                <option value="Month(s)" {{ $patient->years_months === 'Month(s)' ? 'selected' : '' }}>Months</option>
                            </select>
                        </div>
                        <div>
                            <label for="dob" class="block text-gray-700 font-bold mb-2">Date of Birth</label>
                            <input type="date" name="dob" id="dob" class="w-full px-3 py-2 border rounded-md text-gray-700 focus:outline-none focus:border-blue-500" placeholder="Enter date of birth" value="{{ $patient->dob }}">
                        </div>
                        <div>
                            <label class="block text-gray-700 font-bold mb-2">Sex</label>
                            <div class="mt-2">
                                <label class="inline-flex items-center">
                                    <input type="radio" class="form-radio" name="sex" value="1" @if($patient->sex == 1) checked @endif>
                                    <span class="ml-2">Male</span>
                                </label>
                                <label class="inline-flex items-center ml-6">
                                    <input type="radio" class="form-radio" name="sex" value="0" @if($patient->sex == 0) checked @endif>
                                    <span class="ml-2">Female</span>
                                </label>
                            </div>
                        </div>
                        <div>
                            <label for="blood_group" class="block text-gray-700 font-bold mb-2">Blood Group</label>
                            <select name="blood_group" id="blood_group" class="w-full px-3 py-2 border rounded-md text-gray-700 focus:outline-none focus:border-blue-500">
                                <option value="Unknown" {{ $patient->blood_group === 'Unknown' ? 'selected' : '' }}>Unknown</option>
                                <option value="A+" {{ $patient->blood_group === 'A+' ? 'selected' : '' }}>A+</option>
                                <option value="A-" {{ $patient->blood_group === 'A-' ? 'selected' : '' }}>A-</option>
                                <option value="B+" {{ $patient->blood_group === 'B+' ? 'selected' : '' }}>B+</option>
                                <option value="B-" {{ $patient->blood_group === 'B-' ? 'selected' : '' }}>B-</option>
                                <option value="O+" {{ $patient->blood_group === 'O+' ? 'selected' : '' }}>O+</option>
                                <option value="O-" {{ $patient->blood_group === 'O-' ? 'selected' : '' }}>O-</option>
                                <option value="AB+" {{ $patient->blood_group === 'AB+' ? 'selected' : '' }}>AB+</option>
                                <option value="AB-" {{ $patient->blood_group === 'AB-' ? 'selected' : '' }}>AB-</option>
                            </select>
                        </div>
                        <div>
                            <label for="registration_date" class="block text-gray-700 font-bold mb-2">Registration Date</label>
                            <input type="date" name="registration_date" id="registration_date" class="w-full px-3 py-2 border rounded-md text-gray-700 focus:outline-none focus:border-blue-500" placeholder="Enter registration date" value="{{ $patient->registration_date }}">
                        </div>
                        <div>
                            <label for="phone" class="block text-gray-700 font-bold mb-2">Phone</label>
                            <input type="text" name="phone" value="{{ $patient->phone }}"  id="phone" class="w-full px-3 py-2 border rounded-md text-gray-700 focus:outline-none focus:border-blue-500" placeholder="Enter phone number">
                        </div>
                        <div>
                            <label for="email" class="block text-gray-700 font-bold mb-2">Email</label>
                            <input type="email" name="email" value="{{ $patient->email }}"  id="email" class="w-full px-3 py-2 border rounded-md text-gray-700 focus:outline-none focus:border-blue-500" placeholder="Enter email address">
                        </div>

                        <div>
                            <x-label for="address" value="Patient Address" :required="true"/>
                            <input type="text" name="address" id="address" class="w-full px-3 py-2 border rounded-md text-gray-700 focus:outline-none focus:border-blue-500" placeholder="Patient Address" value="{{ $patient->address }}">
                        </div>
                        <div>
                            <label for="mobile" class="block text-gray-700 font-bold mb-2">Mobile</label>
                            <input type="text" name="mobile" value="{{ $patient->mobile }}" id="mobile" class="w-full px-3 py-2 border rounded-md text-gray-700 focus:outline-none focus:border-blue-500" placeholder="Enter mobile number">
                        </div>
                        <div>
                            <label class="block text-gray-700 font-bold mb-2">Email Alert</label>
                            <div class="mt-2">
                                <label class="inline-flex items-center ml-6">
                                    <input type="checkbox" class="form-checkbox"  @if(old('email_alert', $patient->email_alert)) checked @endif name="email_alert">
                                    <span class="ml-2">Enable Email Alert</span>
                                </label>
                                <label class="inline-flex items-center ml-6">
                                    <input type="checkbox" class="form-checkbox"  @if(old('mobile_alert', $patient->mobile_alert)) checked @endif name="mobile_alert">
                                    <span class="ml-2">Enable Mobile Alert</span>
                                </label>
                            </div>

                        </div>
                        <div>
                            <label for="cnic" class="block text-gray-700 font-bold mb-2">CNIC</label>
                            <input type="text" name="cnic" id="cnic" value="{{ $patient->cnic }}"  class="w-full px-3 py-2 border rounded-md text-gray-700 focus:outline-none focus:border-blue-500" placeholder="Enter CNIC (00000-0000000-0)">
                        </div>


                    </div>
                    <livewire:government-details :patient="$patient"/>


                    <div class="flex items-center justify-between mt-4">

                        @if(Auth::user()->hasRole('Auditor'))
                        @else
                            <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline" type="submit">
                                Update
                            </button>
                        @endif

                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
