<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePatientRequest;
use App\Http\Requests\UpdatePatientRequest;
use App\Models\Admission;
use App\Models\Chit;
use App\Models\Department;
use App\Models\FeeCategory;
use App\Models\FeeType;
use App\Models\Invoice;
use App\Models\Patient;
use App\Models\PatientTest;
use App\Models\PatientTestCart;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class PatientController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $patients = QueryBuilder::for(Patient::class)
            ->allowedFilters([
                'first_name',
                'last_name',
                'father_husband_name',
                AllowedFilter::exact('sex'),
                'cnic',
                'mobile',
                AllowedFilter::exact('government_non_gov'),
                AllowedFilter::exact('government_card_no'),
                AllowedFilter::callback('id', function ($query, $value): void {
                    if (is_numeric($value)) {
                        $query->where('id', $value);
                    }
                }),
            ])
            ->orderByDesc('created_at') // Corrected 'DSEC' to 'DESC'
            ->paginate(3)
            ->withQueryString();

        return view('patient.index', compact('patients'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('patient.create');
    }

    public function createOPD()
    {
        return view('patient.create-opd');
    }

    public function createIPD()
    {
        return view('patient.create-ipd');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePatientRequest $request)
    {
        // login user id capture
        $request->merge(['user_id' => auth()->user()->id]);
        $patient = null;
        DB::beginTransaction();
        try {
            $request->merge(['sex' => $this->getAutoGender($request->input('title'))])->all();
            $age = $request->age;
            $yearsMonths = $request->years_months;
            $dateOfBirth = $request->dob; // Get the provided date of birth from the request

            // Check if the user has already provided a date of birth
            if (! $dateOfBirth) {
                if ($yearsMonths === 'Year(s)') {
                    $dateOfBirth = now()->subYears($age)->format('Y-m-d');
                } elseif ($yearsMonths === 'Month(s)') {
                    $dateOfBirth = now()->subMonths($age)->format('Y-m-d');
                } elseif ($yearsMonths === 'Day(s)') {
                    $dateOfBirth = now()->subDays($age)->format('Y-m-d');
                } else {
                    // Handle an invalid selection or provide a default value
                    $dateOfBirth = null;
                }
            }
            // Merge the calculated date of birth into the request data
            $request->merge(['dob' => $dateOfBirth])->all();
            $patient = Patient::create($request->all());
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            \Illuminate\Support\Facades\Log::error('Patient Creation Error: '.$e->getMessage());
        }

        if (! empty($patient)) {
            return to_route('patient.actions', [$patient->id]);
        } else {
            return to_route('patient.index')->with('message', 'There is an error occurred for creating patient');
        }
    }

    public function storeOPD(Request $request)
    {
        $request->validate([
            'first_name' => 'required',
            'department_id' => 'required',
            'mobile' => 'required|regex:/^03\d{2}-\d{7}$/',
            'phone' => 'nullable|string|max:15',
            'age' => 'required|integer|min:0',
            'years_months' => 'required_if:age,!=,null|in:Year(s),Month(s),Day(s)',
            'government_non_gov' => 'required',
            'government_department_id' => 'required_with:government_card_no,designation,sehat_sahulat_visit_no,sehat_sahulat_patient_id',
            'government_card_no' => [
                'nullable',
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->government_department_id && $request->government_department_id != 95 && empty($value)) {
                        $fail('The government card no field is required.');
                    }
                },
            ],
            'designation' => [
                'nullable',
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->government_department_id && $request->government_department_id != 95 && empty($value)) {
                        $fail('The designation field is required.');
                    }
                },
            ],
            'sehat_sahulat_visit_no' => [
                'nullable',
                'string',
                'max:255',
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->government_department_id == 95 && empty($value)) {
                        $fail('The Visit ID is required for Sehat Sahulat Program.');
                    }
                },
            ],
            'sehat_sahulat_patient_id' => [
                'nullable',
                'string',
                'max:255',
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->government_department_id == 95 && empty($value)) {
                        $fail('The Patient ID is required for Sehat Sahulat Program.');
                    }
                },
            ],
        ]);
        // login user id capture
        $request->merge(['user_id' => auth()->user()->id]);
        $patient = null;
        $chit = null;
        $fee_type_id = null;
        // 1 for opd 0 for ipd
        $ipd_opd = null;

        $count_chit_of_today = Chit::where('department_id', $request->department_id)->where('issued_date', '>=', Carbon::today())->count();
        $count_chit_of_today_limit = Department::where('id', $request->department_id)->first()->daily_patient_limit;
        $count_chit_of_today++;

        if ($count_chit_of_today_limit <= $count_chit_of_today) {
            return to_route('patient.create-opd')->with('error', 'Today\'s limit has been reached to '.$count_chit_of_today_limit);
        }

        DB::beginTransaction();

        try {
            $request->merge(['sex' => $this->getAutoGender($request->input('title'))])->all();

            $age = $request->age;
            $yearsMonths = $request->years_months;
            $dateOfBirth = $request->dob; // Get the provided date of birth from the request

            // Check if the user has already provided a date of birth
            if (! $dateOfBirth) {
                if ($yearsMonths === 'Year(s)') {
                    $dateOfBirth = now()->subYears($age)->format('Y-m-d');
                } elseif ($yearsMonths === 'Day(s)') {
                    $dateOfBirth = now()->subDays($age)->format('Y-m-d');
                } elseif ($yearsMonths === 'Month(s)') {
                    $dateOfBirth = now()->subMonths($age)->format('Y-m-d');
                } else {
                    // Handle an invalid selection or provide a default value
                    $dateOfBirth = null;
                }
            }
            // Merge the calculated date of birth into the request data
            $request->merge(['dob' => $dateOfBirth])->all();

            $patient = Patient::create($request->all());

            $amount = null;
            $amount_hif = null;
            $govt_amount = null;
            $fee_type_id = null;
            $actual_amount = 0;

            // Get department name for dynamic fee lookup
            $department = Department::find($request->department_id);
            $emergencyOpdFeeType = FeeType::where('fee_category_id', 13)
                ->where('type', 'Emergency Chit')
                ->first();

            if ($request->input('government_department_id')) {
                $amount = 0.00;
                $amount_hif = 0.00;
                $govt_amount = 0.00;
                if ($department && $department->name === 'OPD Emergency' && $emergencyOpdFeeType) {
                    $fee_type_id = $emergencyOpdFeeType->id;
                } elseif ($request->department_id == 7) {
                    $fee_type_id = 108;
                } elseif ($request->department_id == 23) {
                    $fee_type_id = 270;
                } elseif ($request->department_id == 1) {
                    // For emergency
                    $fee_type_id = 1;
                } elseif ($request->department_id == 16) {
                    // For Cardiology
                    $fee_type_id = 1;
                } else {
                    // Dynamic lookup for specialist departments by name
                    $feeType = FeeType::where('type', $department->name)->first();
                    if ($feeType) {
                        $fee_type_id = $feeType->id;
                    } else {
                        $fee_type_id = 107;
                    }
                }

                // For SSP (Sehat Sahulat Program), store the actual fee for insurance claim tracking
                if ($request->government_department_id == 95) {
                    $sspFeeType = FeeType::find($fee_type_id);
                    if ($sspFeeType) {
                        $actual_amount = $sspFeeType->amount;
                    }
                }
            } else {
                if ($department && $department->name === 'OPD Emergency' && $emergencyOpdFeeType) {
                    $amount = $emergencyOpdFeeType->amount;
                    $amount_hif = $emergencyOpdFeeType->hif;
                    $fee_type_id = $emergencyOpdFeeType->id;
                    $govt_amount = $amount - $amount_hif;
                } elseif ($request->department_id == 7) {
                    $amount = FeeType::find(108)->amount;
                    $amount_hif = FeeType::find(108)->hif;
                    $fee_type_id = 108;
                    $govt_amount = $amount - $amount_hif;
                } elseif ($request->department_id == 23) {
                    $amount = FeeType::find(270)->amount;
                    $amount_hif = FeeType::find(270)->hif;
                    $fee_type_id = 270;
                    $govt_amount = $amount - $amount_hif;
                } elseif ($request->department_id == 1) {
                    // For emergency
                    $amount = FeeType::find(1)->amount;
                    $amount_hif = FeeType::find(1)->hif;
                    $govt_amount = $amount - $amount_hif;
                    $fee_type_id = 1;
                } elseif ($request->department_id == 16) {
                    // For Cardiology
                    $amount = FeeType::find(19)->amount;
                    $amount_hif = FeeType::find(19)->hif;
                    $govt_amount = $amount - $amount_hif;
                    $fee_type_id = 1;
                } else {
                    // Dynamic lookup for specialist departments by name
                    $feeType = FeeType::where('type', $department->name)->first();
                    if ($feeType) {
                        $fee_type_id = $feeType->id;
                        $amount = $feeType->amount;
                        $amount_hif = $feeType->hif;
                        $govt_amount = $amount - $amount_hif;
                    } else {
                        $fee_type_id = 107;
                        $amount = FeeType::find(107)->amount;
                        $amount_hif = FeeType::find(107)->hif;
                        $govt_amount = $amount - $amount_hif;
                    }
                }
            }

            if ($request->has('ipd_opd')) {
                $ipd_opd = 0;
            } else {
                $ipd_opd = 1;
            }

            // this is for opd
            $chit = Chit::create([
                'user_id' => auth()->user()->id,
                'department_id' => $request->department_id,
                'patient_id' => $patient->id,
                'government_non_gov' => $patient->government_non_gov,
                'government_department_id' => $patient->government_department_id,
                'government_card_no' => $patient->government_card_no,
                'designation' => $patient->designation,
                'fee_type_id' => $fee_type_id,
                'issued_date' => now(),
                'address' => $patient->address,
                'amount' => $amount,
                'amount_hif' => $amount_hif,
                'govt_amount' => $govt_amount,
                'ipd_opd' => $ipd_opd,
                'payment_status' => 1,
                'sehat_sahulat_visit_no' => $request->sehat_sahulat_visit_no,
                'actual_amount' => $actual_amount,
            ]);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            // something went wrong
            \Illuminate\Support\Facades\Log::error($e->getMessage());
        }

        if (! empty($chit) && ! empty($patient)) {
            return to_route('chit.print', [$patient->id, $chit->id]);
        } else {
            return to_route('patient.index')->with('message', 'There is an error occurred for creating patient and chit');
        }
    }

    public function storeIPD(Request $request)
    {
        $request->validate([
            'first_name' => 'required',
            'department_id' => 'required',
            'mobile' => 'required|regex:/^03\d{2}-\d{7}$/',

            'age' => 'required|integer|min:0',
            'years_months' => 'required_if:age,!=,null|in:Year(s),Month(s),Day(s)',

            'government_non_gov' => 'required',
            'government_department_id' => 'required_with:government_card_no,designation',
            'government_card_no' => 'required_with:government_department_id',
            'designation' => 'required_with:government_department_id',
        ]);
        // login user id capture
        $request->merge(['user_id' => auth()->user()->id]);
        $patient = null;
        $chit = null;
        $fee_type_id = null;
        // 1 for opd 0 for ipd
        $ipd_opd = null;

        $count_chit_of_today = Chit::where('department_id', $request->department_id)->where('issued_date', '>=', Carbon::today())->count();
        $count_chit_of_today_limit = Department::where('id', $request->department_id)->first()->daily_patient_limit;
        $count_chit_of_today++;

        if ($count_chit_of_today_limit <= $count_chit_of_today) {
            return to_route('patient.create-ipd')->with('error', 'Today\'s limit has been reached to '.$count_chit_of_today_limit);
        }

        DB::beginTransaction();

        try {
            $request->merge(['sex' => $this->getAutoGender($request->input('title'))])->all();

            $age = $request->age;
            $yearsMonths = $request->years_months;
            $dateOfBirth = $request->dob; // Get the provided date of birth from the request

            // Check if the user has already provided a date of birth
            if (! $dateOfBirth) {
                if ($yearsMonths === 'Year(s)') {
                    $dateOfBirth = now()->subYears($age)->format('Y-m-d');
                } elseif ($yearsMonths === 'Month(s)') {
                    $dateOfBirth = now()->subMonths($age)->format('Y-m-d');
                } elseif ($yearsMonths === 'Day(s)') {
                    $dateOfBirth = now()->subDays($age)->format('Y-m-d');
                } else {
                    // Handle an invalid selection or provide a default value
                    $dateOfBirth = null;
                }
            }
            // Merge the calculated date of birth into the request data
            $request->merge(['dob' => $dateOfBirth])->all();

            $patient = Patient::create($request->all());

            $amount = null;
            $actual_amount = 0;
            if ($request->input('government_department_id')) {
                $amount = 0.00;
                if ($request->department_id == 7) {
                    $fee_type_id = 108;
                } elseif ($request->department_id == 23) {
                    $fee_type_id = 270;
                } elseif ($request->department_id == 1) {
                    $fee_type_id = 1;
                } elseif ($request->department_id == 16) {
                    $fee_type_id = 1;
                } else {
                    $fee_type_id = 107;
                }

                // For SSP (Sehat Sahulat Program), store the actual fee for insurance claim tracking
                if ($request->government_department_id == 95) {
                    $sspFeeType = FeeType::find($fee_type_id);
                    if ($sspFeeType) {
                        $actual_amount = $sspFeeType->amount;
                    }
                }
            } else {
                if ($request->department_id == 7) {
                    $amount = FeeType::find(108)->amount;
                    $fee_type_id = 108;
                } else {

                    if ($request->department_id == 23) {
                        $amount = FeeType::find(270)->amount;
                        $amount_hif = FeeType::find(270)->hif;
                        $fee_type_id = 270;
                        // all amount goes to government
                        $govt_amount = $amount;
                    }

                    if ($request->department_id == 1) {
                        // For emergency
                        $amount = FeeType::find(1)->amount;
                        $fee_type_id = 1;
                    } else {
                        if ($request->department_id == 16) {
                            // For Cardiology
                            $amount = FeeType::find(19)->amount;
                            $fee_type_id = 1;
                        } else {
                            $fee_type_id = 107;
                            $amount = FeeType::find(107)->amount;
                        }
                    }
                }
            }

            if ($request->has('ipd_opd')) {
                $ipd_opd = 0;
            } else {
                $ipd_opd = 1;
            }
            // this is for opd
            $chit = Chit::create([
                'user_id' => auth()->user()->id,
                'department_id' => $request->department_id,
                'patient_id' => $patient->id,
                'address' => $patient->address,
                'government_non_gov' => $patient->government_non_gov,
                'government_department_id' => $patient->government_department_id,
                'government_card_no' => $patient->government_card_no,
                'designation' => $patient->designation,
                'fee_type_id' => $fee_type_id,
                'issued_date' => now(),
                'amount' => $amount,
                'ipd_opd' => $ipd_opd,
                'payment_status' => 1,
                'actual_amount' => $actual_amount,
            ]);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            // something went wrong
        }

        if (! empty($chit) && ! empty($patient)) {
            return to_route('chit.print', [$patient->id, $chit->id]);
        } else {
            return to_route('patient.index')->with('message', 'There is an error occurred for creating patient and chit');
        }
    }

    public function proceed(Patient $patient)
    {
        return view('patient.proceed', compact('patient'));
    }

    public function add_to_cart(\Illuminate\Http\Request $request, Patient $patient)
    {

        $status = $request->status;
        if (empty($request->status)) {
            $status = 'Normal';
        } else {
            $status = $request->status;
        }

        if ($status === 'Return' && ! auth()->user()->getAllPermissions()->contains('name', 'return invoices')) {
            abort(403, 'You are not authorized to create return invoices.');
        }

        $add_to_cart = PatientTestCart::create([
            'patient_id' => $request->patient_id,
            'fee_type_id' => $request->fee_type_id,
            'government_non_gov' => $patient->government_non_gov,
            'government_department_id' => $patient->government_department_id,
            'government_card_no' => $patient->government_card_no,
            'status' => $status,
        ]);

        return to_route('patient.proceed', $patient->id);
    }

    public function proceed_cart_destroy(\Illuminate\Http\Request $request, PatientTestCart $patientTestCart)
    {
        $patient_id = $patientTestCart->patient_id;
        $patientTestCart->delete();

        return to_route('patient.proceed', $patient_id)->with('message', 'Lab test deleted successfully!');
    }

    public function proceed_to_invoice(\Illuminate\Http\Request $request, Patient $patient)
    {
        // Validate that the user has agreed to the terms.
        $request->validate([
            'terms' => 'required',
            'admission_no' => 'nullable|required_if:admission_form_return,1|integer|exists:admissions,id',
        ]);

        // Fetch the patient's test cart items.
        $patientTestCartItems = PatientTestCart::where('patient_id', $patient->id)->get();

        // Ensure the patient has tests or admissions in their cart.
        if ($patientTestCartItems->isEmpty()) {
            return redirect()->route('patient.proceed', $patient->id)
                ->with('error', 'You must add at least one test or admission to the cart before proceeding to the invoice.');
        }

        // Initialize a flag for later use (purpose to be determined by subsequent logic).
        $flag = false;

        // Initialize an admission variable (purpose to be determined by subsequent logic).
        $admission = null;

        DB::beginTransaction();

        try {
            // User and patient data
            $userId = auth()->user()->id;
            $patientId = $patient->id;

            // Initialize total amounts
            $totalAllAmount = 0;
            $totalAllAmountHif = 0;
            $totalAllGovernmentAmount = 0;
            $actualTotalAllAmount = 0;

            // Create initial invoice
            $invoice = Invoice::create([
                'user_id' => $userId,
                'patient_id' => $patientId,
                'government_non_government' => $patient->government_non_gov,
                'government_department_id' => $patient->government_department_id,
                'government_card_no' => $patient->government_card_no,
            ]);

            //            foreach ($patientTestCartItems as $ptc) {
            //                $total_amount = 0;
            //                $total_amount_hif = 0;
            //                if ($patient->government_non_gov == 1) {
            //                    $total_amount = 0;
            //                    $total_all_amount = $total_all_amount + $total_amount;
            //                    $total_all_amount_hif = $total_all_amount_hif + $total_amount_hif;
            //                }
            //                else {
            //                    $total_amount = FeeType::find($ptc->fee_type_id)->amount;
            //                    $total_all_amount = $total_all_amount + $total_amount;
            //                    $total_all_amount_hif = $total_all_amount_hif + FeeType::find($ptc->fee_type_id)->hif;
            //                }
            //                PatientTest::create([
            //                    'patient_id' => $ptc->patient_id,
            //                    'fee_type_id' => $ptc->fee_type_id,
            //                    'invoice_id' => $invoice->id,
            //                    'government_non_gov' => $patient->government_non_gov,
            //                    'government_department_id' => $patient->government_department_id,
            //                    'government_card_no' => $patient->government_card_no,
            //                    'total_amount' => $total_amount,
            //                    'hif_amount' =>  FeeType::find($ptc->fee_type_id)->hif,
            //                ]);
            //            }

            // Loop through test cart items
            foreach ($patientTestCartItems as $ptc) {
                $totalAmount = 0;
                $totalHifAmount = 0;
                $totalGovtAmount = 0;
                $actualTotalAmount = 0;

                // Calculate total amount (based on government status)
                if ($patient->government_non_gov == 1) {
                    $totalAmount = 0;
                    $totalHifAmount = 0;
                    $totalGovtAmount = 0;

                    // For SSP (Sehat Sahulat Program), store the actual fee for insurance claim tracking
                    if ($patient->government_department_id == 95) {
                        $sspFeeType = FeeType::find($ptc->fee_type_id);
                        if ($sspFeeType) {
                            $actualTotalAmount = ($ptc->status == 'Return') ? $sspFeeType->amount * -1 : $sspFeeType->amount;
                        }
                    }
                } else {
                    $feeType = FeeType::find($ptc->fee_type_id);
                    if ($ptc->status == 'Normal') {
                        $totalAmount = $feeType->amount;
                        $totalHifAmount = $feeType->hif;
                        $totalGovtAmount = $totalAmount - $totalHifAmount;
                    } elseif ($ptc->status == 'Return') {
                        // amount convert to negative...
                        $totalAmount = $feeType->amount * -1;
                        $totalHifAmount = $feeType->hif * -1;
                        $totalGovtAmount = $totalAmount - $totalHifAmount;
                    }
                }

                // Update totals
                $totalAllAmount += $totalAmount;
                $totalAllAmountHif += $totalHifAmount;
                $totalAllGovernmentAmount += $totalGovtAmount;
                $actualTotalAllAmount += $actualTotalAmount;

                // Create PatientTest record
                PatientTest::create([
                    'patient_id' => $ptc->patient_id,
                    'fee_type_id' => $ptc->fee_type_id,
                    'invoice_id' => $invoice->id,
                    'government_non_gov' => $patient->government_non_gov,
                    'government_department_id' => $patient->government_department_id,
                    'government_card_no' => $patient->government_card_no,
                    'total_amount' => $totalAmount,
                    'hif_amount' => $totalHifAmount,
                    'govt_amount' => $totalGovtAmount,
                    'actual_total_amount' => $actualTotalAmount,
                    'status' => $ptc->status,
                ]);
            }

            // Update invoice with calculated totals
            $invoice->total_amount = $totalAllAmount;
            $invoice->hif_amount = $totalAllAmountHif;
            $invoice->govt_amount = $totalAllGovernmentAmount;
            $invoice->actual_total_amount = $actualTotalAllAmount;
            $invoice->save();

            // Clear patient's test cart
            $patientTestCartItems->each->delete();

            if ($request->has('admission_form') && $request->admission_form == 1) {
                $admission = Admission::create([
                    'user_id' => $userId,
                    'invoice_id' => $invoice->id,
                    'patient_id' => $patientId,
                    'government_department_id' => $patient->government_department_id,
                    'actual_total_amount' => $actualTotalAllAmount,
                    'unit_ward' => $request->unit_ward,
                    'disease' => $request->disease,
                    'category' => $request->category,
                    'nok_name' => $request->nok_name,
                    'relation_with_patient' => $request->relation_with_patient,
                    'village' => $request->village,
                    'tehsil' => $request->tehsil,
                    'district' => $request->district,
                    'address' => $request->address,
                    'cell_no' => $request->cell_no,
                    'cnic_no' => $request->cnic_no,
                ]);
            }

            if ($request->has('admission_form_return') && $request->admission_form_return == 1) {
                $admission = Admission::query()
                    ->where('id', $request->admission_no)
                    ->where('patient_id', $patientId)
                    ->first();

                if (! empty($admission)) {
                    $admission->status = 'Yes';
                    $admission->save();
                } else {
                    throw new \ErrorException('Admission record not found for this patient.');
                }
            }

            $flag = true;
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            // something went wrong
        }

        if ($flag) {
            return to_route('patient.patient_invoice', [$patient->id, $invoice->id]);
        } else {
            return to_route('patient.proceed', $patient->id)->with('message', 'Something went wrong, please try again.');
        }
    }

    public function patient_invoice(Patient $patient, Invoice $invoice)
    {
        $date_of_day = Carbon::parse($invoice->created_at)->format('Y-m-d');
        $fee_type_id = $invoice->patient_test_latest->fee_type_id;
        $patient_test_latest_id = $invoice->patient_test_latest->id;

        $result = DB::table('patient_tests')
            ->where('fee_type_id', $fee_type_id)
            ->whereDate('created_at', $date_of_day)
            ->orderByDesc('created_at')
            ->select('*', DB::raw('ROW_NUMBER() OVER (ORDER BY created_at) AS count_no'))
            ->get();

        $chitNumber = $result->where('id', $patient_test_latest_id)->first()->count_no;

        $total_amount = $invoice->patient_test->sum('total_amount');
        $department = null;
        $fee_category = null;
        $fee_category_main = null;
        if (! empty($invoice->patient_test_latest->fee_type->feeCategory)) {
            $department = $invoice->patient_test_latest->fee_type->feeCategory->name;
        }
        if (! empty($invoice->patient_test_latest->fee_type)) {
            $fee_category = $invoice->patient_test_latest->fee_type->type;
            $fee_cat_id = FeeCategory::find($invoice->patient_test_latest->fee_type->fee_category_id)->id;

            if ($fee_cat_id >= 8 && $fee_cat_id <= 12) {
                $fee_category_main = 'Pathology';
            } else {
                $fee_category_main = FeeCategory::find($invoice->patient_test_latest->fee_type->fee_category_id)->name;
            }
        }

        //        dd('ss')
        //        dd($invoice->patient_test->groupBy('fee_type_id'));
        return view('patient.invoice', compact('patient', 'patient', 'fee_category_main', 'invoice', 'total_amount', 'department', 'fee_category', 'chitNumber'));
    }

    public function patient_invoice_thermal_print(Patient $patient, Invoice $invoice)
    {
        $date_of_day = Carbon::parse($invoice->created_at)->format('Y-m-d');
        $fee_type_id = $invoice->patient_test_latest->fee_type_id;
        $patient_test_latest_id = $invoice->patient_test_latest->id;

        $result = DB::table('patient_tests')
            ->where('fee_type_id', $fee_type_id)
            ->whereDate('created_at', $date_of_day)
            ->orderByDesc('created_at')
            ->select('*', DB::raw('ROW_NUMBER() OVER (ORDER BY created_at) AS count_no'))
            ->get();

        $chitNumber = $result->where('id', $patient_test_latest_id)->first()->count_no;

        $total_amount = $invoice->patient_test->sum('total_amount');
        $department = null;
        $fee_category = null;
        $fee_category_main = null;
        if (! empty($invoice->patient_test_latest->fee_type->feeCategory)) {
            $department = $invoice->patient_test_latest->fee_type->feeCategory->name;
        }
        if (! empty($invoice->patient_test_latest->fee_type)) {
            $fee_category = $invoice->patient_test_latest->fee_type->type;
            $fee_cat_id = FeeCategory::find($invoice->patient_test_latest->fee_type->fee_category_id)->id;

            if ($fee_cat_id >= 8 && $fee_cat_id <= 12) {
                $fee_category_main = 'Pathology';
            } else {
                $fee_category_main = FeeCategory::find($invoice->patient_test_latest->fee_type->fee_category_id)->name;
            }
        }

        //        dd('ss')
        //        dd($invoice->patient_test->groupBy('fee_type_id'));
        return view('patient.thermal-print', compact('patient', 'patient', 'fee_category_main', 'invoice', 'total_amount', 'department', 'fee_category', 'chitNumber'));
    }

    public function patient_history(Patient $patient)
    {
        $patient_tests = PatientTest::where('patient_id', $patient->id)
            ->groupBy('patient_test_id')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('patient.history', compact('patient_tests'));
    }

    public function patient_test_invoice_generate(\Illuminate\Http\Request $request)
    {
        // login user id capture
        $request->merge(['user_id' => auth()->user()->id]);
        foreach ($request->patient_test as $pt) {
            PatientTestCart::create([
                'patient_id' => $request->patient_id,
                'lab_test_id' => $pt,
            ]);
        }

        return to_route('patient.proceed', $request->patient_id)->with('message', 'Patient test added successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Patient $patient)
    {
        return view('patient.show', compact('patient'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Patient $patient)
    {
        return view('patient.edit', compact('patient'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePatientRequest $request, Patient $patient)
    {
        if ($request->input('mobile_alert')) {
            $request->merge(['mobile_alert' => 1]);
        } else {
            $request->merge(['mobile_alert' => 0]);
        }

        if ($request->input('email_alert')) {
            $request->merge(['email_alert' => 1]);
        } else {
            $request->merge(['email_alert' => 0]);
        }

        $patient->update($request->all());

        return redirect()->route('patient.index')->with('message', 'Patient updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Patient $patient)
    {
        //
    }

    public function patient_actions(Patient $patient)
    {
        return view('patient.actions', compact('patient'));
    }

    private function getAutoGender($title)
    {
        // List of titles considered as male
        $maleTitles = ['Mr.', 'S/O', 'F/O'];

        // List of titles considered as female
        $femaleTitles = ['H/O', 'W/O', 'Miss', 'D/O', 'M/O'];

        if (in_array($title, $maleTitles)) {
            return '1';
        } elseif (in_array($title, $femaleTitles)) {
            return '0';
        }
    }

    public function emergency_treatment(Patient $patient)
    {
        // get all disease sort by name
        $diseases = DB::table('diseases')->orderBy('name', 'asc')->get();

        return view('patient.emergency-treatment', compact('patient', 'diseases'));
    }

    public function emergency_treatment_store(Request $request, Patient $patient)
    {
        $request->validate([
            'disease_id' => 'nullable|exists:diseases,id',
            'treatment_details' => 'required|string|max:5000',
            'medications' => 'required|string|max:5000',
        ]);

        try {
            DB::beginTransaction();

            \App\Models\PatientEmergencyTreatment::create([
                'user_id' => auth()->id(),
                'patient_id' => $patient->id,
                'disease_id' => $request->disease_id ?: null,
                'treatment_details' => $request->treatment_details,
                'medications' => $request->medications,
            ]);

            DB::commit();

            return redirect()->route('patient.emergency_treatment', $patient->id)
                ->with('success', 'Emergency treatment recorded successfully.')
                ->with('scroll_to', 'previous-treatments');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withInput()
                ->with('error', 'Failed to record emergency treatment: '.$e->getMessage());
        }
    }
}
