<?php

namespace App\Http\Controllers;

use App\Models\Admission;
use App\Models\AdmissionWard;
use App\Models\Chit;
use App\Models\Department;
use App\Models\FeeCategory;
use App\Models\FeeType;
use App\Models\Invoice;
use App\Models\PatientTest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class ReportsController extends Controller
{
    public function reportDaily(Request $request)
    {
        $user = \Auth::user();
        $issued_invoices = null;

        $start_date = Carbon::parse($request->start_date)->format('Y-m-d');
        $end_date = Carbon::parse($request->end_date)->format('Y-m-d');

        $date_start_at = $start_date.' 00:00:00';
        $date_end_at = $end_date.' 23:59:59';

        $data = null;
        foreach (Department::where('name', '!=', 'Emergency')->get() as $dpt) {
            $data[$dpt->name] = ['Non_Entitiled' => 0, 'Entitiled' => 0, 'Revenue' => 0, 'Revenue_HIF' => 0, 'department_id' => $dpt->id];
        }

        $non_entitled = DB::table('chits')
            ->join('departments', 'chits.department_id', '=', 'departments.id')
            ->select('departments.name', DB::raw('COUNT(chits.government_non_gov) AS "Non_Entitiled"'), DB::raw('SUM(chits.amount) as "Revenue", SUM(chits.amount_hif) as "Revenue_HIF"'))
            ->whereBetween('chits.issued_date', [$date_start_at, $date_end_at])
            ->where('chits.government_non_gov', 0)
            ->whereIn('ipd_opd', [1, 0])
            ->groupBy('chits.department_id', 'departments.name')
            ->get();

        $entitled = DB::table('chits')
            ->join('departments', 'chits.department_id', '=', 'departments.id')
            ->select('departments.name', DB::raw('COUNT(chits.government_non_gov) AS "Entitiled"'), DB::raw('SUM(chits.amount) as "Revenue", SUM(chits.amount_hif) as "Revenue_HIF"'))
            ->whereBetween('chits.issued_date', [$date_start_at, $date_end_at])
            ->where('chits.government_non_gov', 1)
            ->whereIn('ipd_opd', [1, 0])
            ->groupBy('chits.department_id', 'departments.name')
            ->get();

        // Update the $data array with figures from $non_entitled and $entitled queries
        foreach ($non_entitled as $row) {
            $data[$row->name]['Non_Entitiled'] = $row->Non_Entitiled;
            $data[$row->name]['Revenue'] = $row->Revenue;
            $data[$row->name]['Revenue_HIF'] = $row->Revenue_HIF;
        }

        foreach ($entitled as $row) {
            $data[$row->name]['Entitiled'] = $row->Entitiled;
        }

        if ($user->hasRole('Auditor')) {
            return view('reports.auditor.reports-daily', compact('data'));
        } else {
            return view('reports.reports-daily', compact('data'));
        }

    }

    public function reportDailyIPD(Request $request)
    {

        $user = \Auth::user();
        // this is invoices and chits report by user wise
        // Only For IPD
        $start_date = Carbon::parse($request->start_date)->format('Y-m-d');
        $end_date = Carbon::parse($request->end_date)->format('Y-m-d');

        $date_start_at = $start_date.' 00:00:00';
        $date_end_at = $end_date.' 23:59:59';

        $data = null;
        $user_id = null;
        $users = null;
        $roleName = 'Front Desk/Receptionist';

        if ($request->input('user_id')) {
            $user_id = $request->user_id;
            $roleName = 'Front Desk/Receptionist';
            $users = \App\Models\User::role($roleName)->where('id', $user_id)->get();
        } else {
            $users = \App\Models\User::all();
        }

        foreach ($users as $user) {
            $data[$user->id] = ['Name' => $user->name, 'Invoices' => 0, 'Invoices HIF' => 0, 'Chits' => 0, 'Chits HIF' => 0, 'Invoices Entitled' => 0, 'Invoices Non Entitled' => 0, 'Chit Entitled' => 0, 'Chit Non Entitled' => 0];
        }

        foreach ($users as $user) {
            $data[$user->id] = [
                'Name' => $user->name,
                'Invoices Entitled' => Invoice::whereBetween('created_at', [$date_start_at, $date_end_at])->where('user_id', $user->id)->where('government_non_government', 1)->count(),
                'Invoices Non Entitled' => Invoice::whereBetween('created_at', [$date_start_at, $date_end_at])->where('user_id', $user->id)->where('government_non_government', 0)->count(),
                'Invoices' => Invoice::whereBetween('created_at', [$date_start_at, $date_end_at])->where('user_id', $user->id)->sum('total_amount'),
                'Invoices HIF' => Invoice::whereBetween('created_at', [$date_start_at, $date_end_at])->where('user_id', $user->id)->sum('hif_amount'),
                'Chits' => Chit::whereBetween('created_at', [$date_start_at, $date_end_at])->where('user_id', $user->id)->sum('amount'),
                'Chits HIF' => Chit::whereBetween('created_at', [$date_start_at, $date_end_at])->where('user_id', $user->id)->sum('amount_hif'),
                'Chit Entitled' => Chit::whereBetween('created_at', [$date_start_at, $date_end_at])->where('user_id', $user->id)->where('government_non_gov', 1)->count(),
                'Chit Non Entitled' => Chit::whereBetween('created_at', [$date_start_at, $date_end_at])->where('user_id', $user->id)->where('government_non_gov', 0)->count(),
            ];
        }

        if (\Auth::user()->hasRole('Auditor')) {
            return view('reports.auditor.reports-daily-ipd', compact('data'));
        }

        return view('reports.reports-daily-ipd', compact('data'));
    }

    public function index()
    {
        return view('reports.index');
    }

    public function opd()
    {
        return view('reports.opd.index');
    }

    public function reportOpdUserWise(Request $request)
    {
        $start_date = $request->start_date ? Carbon::parse($request->start_date)->format('Y-m-d') : now()->format('Y-m-d');
        $end_date = $request->end_date ? Carbon::parse($request->end_date)->format('Y-m-d') : now()->format('Y-m-d');

        $date_start_at = $start_date.' 00:00:00';
        $date_end_at = $end_date.' 23:59:59';

        $query = Chit::with(['user', 'patient', 'department'])
            ->whereBetween('issued_date', [$date_start_at, $date_end_at]);

        if ($request->department_id) {
            $query->where('department_id', $request->department_id);
        }

        if ($request->user_id) {
            $query->where('user_id', $request->user_id);
        }

        $chits = $query->get()->filter(function ($chit) use ($request) {
            if ($request->specialists_only == 'on') {
                return str_contains(strtolower($chit->department->name), 'specialist');
            }

            return true;
        })->sortBy(function ($chit) {
            $isSpecialist = str_contains(strtolower($chit->department->name), 'specialist') ? 1 : 0;

            return [
                $chit->user_id,
                $isSpecialist,
                $chit->department->name,
                $chit->issued_date,
            ];
        });

        $departments = Department::all();
        $users = User::all();

        return view('reports.opd.user-wise', compact('chits', 'start_date', 'end_date', 'departments', 'users'));
    }

    public function reportOpdSpecialistFees(Request $request)
    {
        $start_date = $request->start_date ? Carbon::parse($request->start_date)->format('Y-m-d') : now()->format('Y-m-d');
        $end_date = $request->end_date ? Carbon::parse($request->end_date)->format('Y-m-d') : now()->format('Y-m-d');

        $date_start_at = $start_date.' 00:00:00';
        $date_end_at = $end_date.' 23:59:59';

        // Get all chits for the date range with relationships
        $query = Chit::with(['user', 'patient', 'department'])
            ->whereBetween('issued_date', [$date_start_at, $date_end_at]);

        // Get all specialist departments
        $specialistDepartments = Department::where('name', 'like', '%specialist%');

        if ($request->department_id) {
            $specialistDepartments->where('id', $request->department_id);
        }

        $specialistDepartments = $specialistDepartments->orderBy('name')->get();

        $chits = $query->get();

        // Group by department and calculate statistics
        $departmentStats = $specialistDepartments->map(function ($department) use ($chits) {
            $departmentChits = $chits->where('department_id', $department->id);

            $totalPatients = $departmentChits->count();
            $entitledPatients = $departmentChits->where('government_non_gov', 1)->count();
            $nonEntitledPatients = $departmentChits->where('government_non_gov', 0)->count();
            $totalFees = $departmentChits->sum('govt_amount');

            return [
                'department' => $department,
                'total_patients' => $totalPatients,
                'entitled_patients' => $entitledPatients,
                'non_entitled_patients' => $nonEntitledPatients,
                'total_fees' => $totalFees,
            ];
        });

        // Calculate grand totals
        $grandTotals = [
            'total_patients' => $departmentStats->sum('total_patients'),
            'entitled_patients' => $departmentStats->sum('entitled_patients'),
            'non_entitled_patients' => $departmentStats->sum('non_entitled_patients'),
            'total_fees' => $departmentStats->sum('total_fees'),
        ];

        $departments = Department::all();

        return view('reports.opd.specialist-fees', compact('departmentStats', 'grandTotals', 'start_date', 'end_date', 'departments'));
    }

    public function ipd()
    {
        return view('reports.ipd.index');
    }

    public function monthlyIncomeStatement(Request $request)
    {
        $allowedFiscalYears = [2025];
        $supportedFiscalYears = [2025, 2026];
        $currentDate = now();
        $defaultFiscalYearStart = $currentDate->month >= 7 ? $currentDate->year : $currentDate->year - 1;

        if (! in_array($defaultFiscalYearStart, $supportedFiscalYears, true)) {
            $defaultFiscalYearStart = $allowedFiscalYears[0];
        }

        $fiscalYearStart = (int) $request->input('year', $defaultFiscalYearStart);

        if (! in_array($fiscalYearStart, $supportedFiscalYears, true)) {
            $fiscalYearStart = $defaultFiscalYearStart;
        }

        $fiscalStartDate = Carbon::create($fiscalYearStart, 7, 1)->startOfDay();
        $fiscalEndDate = $fiscalStartDate->copy()->addYear()->subDay()->endOfDay();

        $templateMonthlyIncomeDataByYear = [
            2025 => [
                'Jul-25' => 1819524,
                'Aug-25' => 1843648,
                'Sep-25' => 1889056,
                'Oct-25' => 1743710,
                'Nov-25' => 1701382,
                'Dec-25' => 1695430,
                'Jan-26' => 1290550,
                'Feb-26' => 1196432,
                'Mar-26' => 1092510,
                'Apr-26' => 1124330,
                'May-26' => 1185590,
                'Jun-26' => 1282130,
            ],
        ];

        if (array_key_exists($fiscalYearStart, $templateMonthlyIncomeDataByYear)) {
            $monthlyIncomeData = [];

            foreach ($templateMonthlyIncomeDataByYear[$fiscalYearStart] as $month => $income) {
                $monthlyIncomeData[] = [
                    'sn' => count($monthlyIncomeData) + 1,
                    'month' => $month,
                    'income' => $income,
                ];
            }

            $totalIncome = collect($monthlyIncomeData)->sum('income');
            $yearLabel = $fiscalYearStart.'-'.substr((string) ($fiscalYearStart + 1), -2);

            return view('reports.ipd.monthly-income-statement', compact('monthlyIncomeData', 'totalIncome', 'fiscalYearStart', 'yearLabel', 'allowedFiscalYears'));
        }

        $invoiceTotals = Invoice::query()
            ->selectRaw("DATE_TRUNC('month', created_at) as report_month, SUM(total_amount) as total_income")
            ->whereBetween('created_at', [$fiscalStartDate, $fiscalEndDate])
            ->groupBy('report_month')
            ->get()
            ->keyBy(fn ($item) => Carbon::parse($item->report_month)->format('Y-m'));

        $chitTotals = Chit::query()
            ->selectRaw("DATE_TRUNC('month', issued_date) as report_month, SUM(amount) as total_income")
            ->whereBetween('issued_date', [$fiscalStartDate, $fiscalEndDate])
            ->groupBy('report_month')
            ->get()
            ->keyBy(fn ($item) => Carbon::parse($item->report_month)->format('Y-m'));

        $monthlyIncomeData = collect(range(0, 11))->map(function (int $offset) use ($fiscalStartDate, $invoiceTotals, $chitTotals) {
            $monthDate = $fiscalStartDate->copy()->addMonths($offset);
            $monthKey = $monthDate->format('Y-m');

            $invoiceIncome = (float) ($invoiceTotals[$monthKey]->total_income ?? 0);
            $chitIncome = (float) ($chitTotals[$monthKey]->total_income ?? 0);

            return [
                'sn' => $offset + 1,
                'month' => $monthDate->format('M-y'),
                'income' => $invoiceIncome + $chitIncome,
            ];
        })->all();

        $totalIncome = collect($monthlyIncomeData)->sum('income');
        $yearLabel = $fiscalYearStart.'-'.substr((string) ($fiscalYearStart + 1), -2);

        return view('reports.ipd.monthly-income-statement', compact('monthlyIncomeData', 'totalIncome', 'fiscalYearStart', 'yearLabel', 'allowedFiscalYears'));
    }

    public function reportMisc(Request $request)
    {
        return view('reports.category-wise.misc');
    }

    public function categoryWise(Request $request)
    {
        $start_date = Carbon::parse($request->start_date)->format('Y-m-d');
        $end_date = Carbon::parse($request->end_date)->format('Y-m-d');

        $date_start_at = $start_date.' 00:00:00';
        $date_end_at = $end_date.' 23:59:59';

        $fee_categories = QueryBuilder::for(FeeCategory::class)->with('feeTypes')
            ->allowedFilters('name')
            ->allowedIncludes('feeTypes')
            ->get();

        $categories = [];

        foreach ($fee_categories as $fee_cat) {
            $fee_types_relation = null;
            if ($request->input('status')) {
                // The filter exists, retrieve its value
                $status = $request->input('status');

                $fee_types_relation = $fee_cat->feeTypes->where('status', $status);
            } else {
                // The filter does not exist, handle the case accordingly
                $fee_types_relation = $fee_cat->feeTypes;
            }

            foreach ($fee_types_relation as $fee_type) {
                // Fee category 13 = Chit-based fees (includes specialists), also IDs 1, 19 are special chit fees
                if ($fee_type->fee_category_id == 13 || $fee_type->id == 1 || $fee_type->id == 19) {
                    $categories[$fee_cat->id][$fee_type->id] = [
                        'Non Entitled' => Chit::whereBetween('issued_date', [$date_start_at, $date_end_at])->where('fee_type_id', $fee_type->id)->where('government_non_gov', 0)->count(),
                        'Entitled' => Chit::whereBetween('issued_date', [$date_start_at, $date_end_at])->where('fee_type_id', $fee_type->id)->where('government_non_gov', 1)->count(),
                        'Revenue' => Chit::whereBetween('issued_date', [$date_start_at, $date_end_at])->where('fee_type_id', $fee_type->id)->where('government_non_gov', 0)->sum('amount'),
                        'HIF' => Chit::whereBetween('issued_date', [$date_start_at, $date_end_at])->where('fee_type_id', $fee_type->id)->where('government_non_gov', 0)->sum('amount_hif'),
                        'fee_category_id' => $fee_cat->id,
                        'fee_type_id' => $fee_type->id,
                        'Returned Start Date' => $date_start_at,
                        'Returned End Date' => $date_end_at,
                        'Returned' => FeeType::where('type', 'Return '.FeeType::find($fee_type->id)->type)->first(),
                    ];
                } else {
                    $categories[$fee_cat->id][$fee_type->id] = [
                        'Non Entitled' => PatientTest::whereBetween('created_at', [$date_start_at, $date_end_at])->where('fee_type_id', $fee_type->id)->where('government_non_gov', 0)->count(),
                        'Entitled' => PatientTest::whereBetween('created_at', [$date_start_at, $date_end_at])->where('fee_type_id', $fee_type->id)->where('government_non_gov', 1)->count(),
                        'Revenue' => PatientTest::whereBetween('created_at', [$date_start_at, $date_end_at])->where('fee_type_id', $fee_type->id)->where('government_non_gov', 0)->sum('total_amount'),
                        'HIF' => PatientTest::whereBetween('created_at', [$date_start_at, $date_end_at])->where('fee_type_id', $fee_type->id)->where('government_non_gov', 0)->sum('hif_amount'),
                        'fee_category_id' => $fee_cat->id,
                        'fee_type_id' => $fee_type->id,
                        'Returned Start Date' => $date_start_at,
                        'Returned End Date' => $date_end_at,
                        'Returned' => FeeType::where('type', 'Return '.FeeType::find($fee_type->id)->type)->first(),
                    ];
                }
            }
        }

        return view('reports.category-wise.index', compact('categories'));
    }

    public function department_wise(Request $request)
    {
        $start_date = Carbon::parse($request->start_date)->format('Y-m-d');
        $end_date = Carbon::parse($request->end_date)->format('Y-m-d');
        $user = \Auth::user();

        $date_start_at = $start_date.' 00:00:00';
        $date_end_at = $end_date.' 23:59:59';

        $fee_types = null;
        $status = ['Normal', 'Return Fee'];
        $fee_category_ids = $request->input('filter.fee_category_id');
        $get_status_values = $request->input('status');

        if ($get_status_values !== null) {
            $status = explode(',', $get_status_values);
        }

        if ($fee_category_ids !== null) {
            // Split the string into an array of individual IDs
            $fee_category_ids = explode(',', $fee_category_ids);

            $fee_types = QueryBuilder::for(FeeType::class)
                ->orderBy('fee_category_id')
                ->orderByRaw("CASE WHEN status = 'Normal' THEN 1 ELSE 2 END")
                ->whereIn('fee_category_id', $fee_category_ids)
                ->whereIn('status', $status)
                ->get();
        } else {
            $fee_types = QueryBuilder::for(FeeType::class)
                ->orderBy('fee_category_id')
                ->orderByRaw("CASE WHEN status = 'Normal' THEN 1 ELSE 2 END")
                ->whereIn('status', $status)
                ->get();
        }

        $categories = [];

        if ($request->input('status') == 'Normal') {
            foreach ($fee_types as $ft) {
                // Fee category 13 = Chit-based fees (includes specialists), also IDs 1, 19 are special chit fees
                if ($ft->fee_category_id == 13 || $ft->id == 1 || $ft->id == 19) {
                    $categories[$ft->fee_category_id][$ft->id] = [
                        'Non Entitled' => Chit::whereBetween('issued_date', [$date_start_at, $date_end_at])->where('fee_type_id', $ft->id)->where('government_non_gov', 0)->count(),
                        'Entitled' => Chit::whereBetween('issued_date', [$date_start_at, $date_end_at])->where('fee_type_id', $ft->id)->where('government_non_gov', 1)->count(),
                        'Revenue' => Chit::whereBetween('issued_date', [$date_start_at, $date_end_at])->where('fee_type_id', $ft->id)->where('government_non_gov', 0)->sum('amount'),
                        'HIF' => Chit::whereBetween('issued_date', [$date_start_at, $date_end_at])->where('fee_type_id', $ft->id)->where('government_non_gov', 0)->sum('amount_hif'),
                        'fee_category_id' => $ft->fee_category_id,
                        'fee_type_id' => $ft->id,
                        'Returned Start Date' => $date_start_at,
                        'Returned End Date' => $date_end_at,
                        'Returned' => FeeType::where('type', 'Return '.FeeType::find($ft->id)->type)->first(),
                        'Status' => $ft->status,
                    ];
                } else {

                    $return_fee_id = 0;
                    $return_fee = FeeType::where('type', 'Return '.FeeType::find($ft->id)->type)->first();
                    if (! empty($return_fee)) {
                        $return_fee_id = $return_fee->id;
                    }

                    $categories[$ft->fee_category_id][$ft->id] = [
                        'Non Entitled' => PatientTest::whereBetween('created_at', [$date_start_at, $date_end_at])->where('fee_type_id', $ft->id)->where('government_non_gov', 0)->count(),
                        'Entitled' => PatientTest::whereBetween('created_at', [$date_start_at, $date_end_at])->where('fee_type_id', $ft->id)->where('government_non_gov', 1)->count(),
                        'Revenue' => PatientTest::whereBetween('created_at', [$date_start_at, $date_end_at])->whereIn('fee_type_id', [$ft->id, $return_fee_id])->where('government_non_gov', 0)->sum('total_amount'),
                        'HIF' => PatientTest::whereBetween('created_at', [$date_start_at, $date_end_at])->whereIn('fee_type_id', [$ft->id, $return_fee_id])->where('government_non_gov', 0)
                            //                            ->whereHas('fee_type', function($query) {
//                                $query->whereIn('status', ['Return Fee','Normal']);
//                            })
                            ->sum('hif_amount'),
                        'fee_category_id' => $ft->fee_category_id,
                        'fee_type_id' => $ft->id,
                        'Returned Start Date' => $date_start_at,
                        'Returned End Date' => $date_end_at,
                        'Returned' => FeeType::where('type', 'Return '.FeeType::find($ft->id)->type)->first(),
                        'Status' => $ft->status,
                    ];
                }
            }
        } else {
            foreach ($fee_types as $ft) {
                // Fee category 13 = Chit-based fees (includes specialists), also IDs 1, 19 are special chit fees
                if ($ft->fee_category_id == 13 || $ft->id == 1 || $ft->id == 19) {
                    $categories[$ft->fee_category_id][$ft->id] = [
                        'Non Entitled' => Chit::whereBetween('issued_date', [$date_start_at, $date_end_at])->where('fee_type_id', $ft->id)->where('government_non_gov', 0)->count(),
                        'Entitled' => Chit::whereBetween('issued_date', [$date_start_at, $date_end_at])->where('fee_type_id', $ft->id)->where('government_non_gov', 1)->count(),
                        'Revenue' => Chit::whereBetween('issued_date', [$date_start_at, $date_end_at])->where('fee_type_id', $ft->id)->where('government_non_gov', 0)->sum('amount'),
                        'HIF' => Chit::whereBetween('issued_date', [$date_start_at, $date_end_at])->where('fee_type_id', $ft->id)->where('government_non_gov', 0)->sum('amount_hif'),
                        'fee_category_id' => $ft->fee_category_id,
                        'fee_type_id' => $ft->id,
                        'Returned Start Date' => $date_start_at,
                        'Returned End Date' => $date_end_at,
                        'Returned' => FeeType::where('type', 'Return '.FeeType::find($ft->id)->type)->first(),
                        'Status' => $ft->status,
                    ];
                } else {
                    $categories[$ft->fee_category_id][$ft->id] = [
                        'Non Entitled' => PatientTest::whereBetween('created_at', [$date_start_at, $date_end_at])->where('fee_type_id', $ft->id)->where('government_non_gov', 0)->count(),
                        'Entitled' => PatientTest::whereBetween('created_at', [$date_start_at, $date_end_at])->where('fee_type_id', $ft->id)->where('government_non_gov', 1)->count(),
                        'Revenue' => PatientTest::whereBetween('created_at', [$date_start_at, $date_end_at])->where('fee_type_id', $ft->id)->where('government_non_gov', 0)->sum('total_amount'),
                        'HIF' => PatientTest::whereBetween('created_at', [$date_start_at, $date_end_at])->where('fee_type_id', $ft->id)->where('government_non_gov', 0)
                            ->whereHas('fee_type', function ($query) {
                                $query->whereIn('status', ['Return Fee', 'Normal']);
                            })
                            ->sum('hif_amount'),
                        'fee_category_id' => $ft->fee_category_id,
                        'fee_type_id' => $ft->id,
                        'Returned Start Date' => $date_start_at,
                        'Returned End Date' => $date_end_at,
                        'Returned' => FeeType::where('type', 'Return '.FeeType::find($ft->id)->type)->first(),
                        'Status' => $ft->status,
                    ];
                }
            }
        }

        if ($user->hasRole('Auditor')) {
            return view('reports.auditor.department-wise', compact('categories', 'fee_types'));
        } else {
            return view('reports.category-wise.department-wise', compact('categories', 'fee_types'));
        }

    }

    public function department_wise_two(Request $request)
    {

        ini_set('max_execution_time', 300);

        $start_date = Carbon::parse($request->start_date)->format('Y-m-d');
        $end_date = Carbon::parse($request->end_date)->format('Y-m-d');
        $user = \Auth::user();

        $date_start_at = $start_date.' 00:00:00';
        $date_end_at = $end_date.' 23:59:59';

        $fee_types = null;
        //        $status = ['Normal', 'Return Fee'];
        $status = ['Normal'];
        $fee_category_ids = $request->input('filter.fee_category_id');
        $get_status_values = $request->input('status');

        if ($get_status_values !== null) {
            $status = explode(',', $get_status_values);
        }

        if ($fee_category_ids !== null) {
            // Split the string into an array of individual IDs
            $fee_category_ids = explode(',', $fee_category_ids);

            $fee_types = QueryBuilder::for(FeeType::class)
                ->orderBy('fee_category_id')
                //                ->orderByRaw('CASE WHEN status = "Normal" THEN 1 ELSE 2 END')
                ->whereIn('fee_category_id', $fee_category_ids)
                ->whereIn('status', $status)
                ->get();
        } else {
            $fee_types = QueryBuilder::for(FeeType::class)
                ->orderBy('fee_category_id')
                //                ->orderByRaw('CASE WHEN status = "Normal" THEN 1 ELSE 2 END')
                ->whereIn('status', $status)
                ->get();
        }

        $categories = [];

        foreach ($fee_types as $ft) {
            // Fee category 13 = Chit-based fees (includes specialists), also IDs 1, 19 are special chit fees
            if ($ft->fee_category_id == 13 || $ft->id == 1 || $ft->id == 19) {
                $categories[$ft->fee_category_id][$ft->id] = [
                    'Non Entitled' => Chit::whereBetween('issued_date', [$date_start_at, $date_end_at])->where('fee_type_id', $ft->id)->where('government_non_gov', 0)->count(),
                    'Entitled' => Chit::whereBetween('issued_date', [$date_start_at, $date_end_at])->where('fee_type_id', $ft->id)->where('government_non_gov', 1)->count(),
                    'Return Non Entitled' => 0,
                    'Return Entitled' => 0,
                    'HIF' => Chit::whereBetween('issued_date', [$date_start_at, $date_end_at])->where('fee_type_id', $ft->id)->where('government_non_gov', 0)->sum('amount_hif'),
                    'GOVT' => Chit::whereBetween('issued_date', [$date_start_at, $date_end_at])->where('fee_type_id', $ft->id)->where('government_non_gov', 0)->sum('govt_amount'),
                    'TOTAL' => Chit::whereBetween('issued_date', [$date_start_at, $date_end_at])->where('fee_type_id', $ft->id)->where('government_non_gov', 0)->sum('amount'),
                    'fee_category_id' => $ft->fee_category_id,
                    'fee_type_id' => $ft->id,
                    'Status' => $ft->status,
                ];
            } else {

                $return_fee_id = 0;
                $return_fee = FeeType::where('type', 'Return '.FeeType::find($ft->id)->type)->first();
                if (! empty($return_fee)) {
                    $return_fee_id = $return_fee->id;
                }

                $categories[$ft->fee_category_id][$ft->id] = [
                    'Non Entitled' => PatientTest::whereBetween('created_at', [$date_start_at, $date_end_at])->where('fee_type_id', $ft->id)->where('government_non_gov', 0)->where('status', 'Normal')->count(),
                    'Entitled' => PatientTest::whereBetween('created_at', [$date_start_at, $date_end_at])->where('fee_type_id', $ft->id)->where('government_non_gov', 1)->where('status', 'Normal')->count(),
                    'Return Non Entitled' => PatientTest::whereBetween('created_at', [$date_start_at, $date_end_at])->where('fee_type_id', $ft->id)->where('government_non_gov', 0)->where('status', 'Return')->count(),
                    'Return Entitled' => PatientTest::whereBetween('created_at', [$date_start_at, $date_end_at])->where('fee_type_id', $ft->id)->where('government_non_gov', 1)->where('status', 'Return')->count(),
                    'HIF' => PatientTest::whereBetween('created_at', [$date_start_at, $date_end_at])->whereIn('fee_type_id', [$ft->id, $return_fee_id])->where('government_non_gov', 0)->sum('hif_amount'),
                    'GOVT' => PatientTest::whereBetween('created_at', [$date_start_at, $date_end_at])->whereIn('fee_type_id', [$ft->id, $return_fee_id])->where('government_non_gov', 0)->sum('govt_amount'),
                    'TOTAL' => PatientTest::whereBetween('created_at', [$date_start_at, $date_end_at])->whereIn('fee_type_id', [$ft->id, $return_fee_id])->where('government_non_gov', 0)->sum('total_amount'),
                    'fee_category_id' => $ft->fee_category_id,
                    'fee_type_id' => $ft->id,
                    'Status' => $ft->status,
                ];
            }
        }

        return view('reports.category-wise.department-wise-two', compact('categories', 'fee_types'));
    }

    public function admission(Request $request)
    {
        $start_date = Carbon::parse($request->start_date)->format('Y-m-d');
        $end_date = Carbon::parse($request->end_date)->format('Y-m-d');

        $date_start_at = $start_date.' 00:00:00';
        $date_end_at = $end_date.' 23:59:59';

        $admissions = QueryBuilder::for(Admission::class)->with('invoice', 'patient')
            ->allowedFilters([
                'patient.sex',
                'disease',
                'category',
                'nok_name',
                'relation_with_patient',
                'address',
                'cell_no',
                'cnic_no',
                'village',
                AllowedFilter::exact('invoice.government_non_government'),
                AllowedFilter::exact('unit_ward'),
                AllowedFilter::exact('tehsil'),
                AllowedFilter::exact('district'),
                AllowedFilter::exact('patient_id'),
                AllowedFilter::exact('invoice_id'),
            ], )
            ->whereBetween('created_at', [$date_start_at, $date_end_at])
            ->get();

        return view('reports.general-information.index', compact('admissions'));
    }

    public function emergency_treatments(Request $request)
    {
        $start_date = $request->has('start_date') ? Carbon::parse($request->start_date)->format('Y-m-d') : Carbon::today()->format('Y-m-d');
        $end_date = $request->has('end_date') ? Carbon::parse($request->end_date)->format('Y-m-d') : Carbon::today()->format('Y-m-d');

        $date_start_at = $start_date.' 00:00:00';
        $date_end_at = $end_date.' 23:59:59';

        $treatments = QueryBuilder::for(\App\Models\PatientEmergencyTreatment::class)
            ->with('patient', 'disease', 'user')
            ->allowedFilters([
                'patient.first_name',
                'patient.last_name',
                'patient.sex',
                'patient.address',
                'disease.name',
                'user.name',
                AllowedFilter::exact('patient_id'),
                AllowedFilter::exact('disease_id'),
                AllowedFilter::exact('user_id'),
            ])
            ->whereBetween('created_at', [$date_start_at, $date_end_at])
            ->orderBy('created_at', 'asc')
            ->get();

        return view('reports.emergency-treatments.index', compact('treatments', 'start_date', 'end_date'));
    }

    /**
     * SSP (Sehat Sahulat Program) Insurance Claims Report.
     *
     * Shows OPD chits and IPD invoices/tests/admissions for SSP patients
     * with actual fee amounts stored for insurance claim submissions.
     */
    public function sspClaims(Request $request): \Illuminate\View\View
    {
        $sspDepartmentId = 95;

        $start_date = $request->has('start_date') ? Carbon::parse($request->start_date)->format('Y-m-d') : Carbon::today()->format('Y-m-d');
        $end_date = $request->has('end_date') ? Carbon::parse($request->end_date)->format('Y-m-d') : Carbon::today()->format('Y-m-d');

        $date_start_at = $start_date.' 00:00:00';
        $date_end_at = $end_date.' 23:59:59';

        $reportType = $request->input('report_type', 'all'); // all, opd, ipd
        $departmentId = $request->input('department_id');
        $feeCategoryId = $request->input('fee_category_id');
        $patientId = $request->input('patient_id');
        $patientName = $request->input('patient_name');
        $sex = $request->input('sex');
        $userId = $request->input('user_id');
        $unitWard = $request->input('unit_ward');
        $feeStatus = $request->input('fee_status'); // Normal, Return Fee

        // --- OPD Section: Chits ---
        $opdChits = collect();
        if ($reportType === 'all' || $reportType === 'opd') {
            $chitQuery = Chit::with(['patient', 'department', 'fee_type', 'user'])
                ->where('government_department_id', $sspDepartmentId)
                ->whereBetween('issued_date', [$date_start_at, $date_end_at]);

            if ($departmentId) {
                $chitQuery->where('department_id', $departmentId);
            }

            if ($patientId) {
                $chitQuery->where('patient_id', $patientId);
            }

            if ($patientName) {
                $chitQuery->whereHas('patient', function ($q) use ($patientName) {
                    $q->where('first_name', 'like', '%'.$patientName.'%')
                        ->orWhere('last_name', 'like', '%'.$patientName.'%')
                        ->orWhere('father_husband_name', 'like', '%'.$patientName.'%');
                });
            }

            if ($sex !== null && $sex !== '') {
                $chitQuery->whereHas('patient', function ($q) use ($sex) {
                    $q->where('sex', $sex);
                });
            }

            if ($userId) {
                $chitQuery->where('user_id', $userId);
            }

            if ($feeCategoryId) {
                $chitQuery->whereHas('fee_type', function ($q) use ($feeCategoryId) {
                    $q->where('fee_category_id', $feeCategoryId);
                });
            }

            $opdChits = $chitQuery->orderBy('issued_date', 'asc')->get();
        }

        // --- IPD Section: Invoices with PatientTests and Admissions ---
        $ipdInvoices = collect();
        if ($reportType === 'all' || $reportType === 'ipd') {
            $invoiceQuery = Invoice::with(['patient', 'patient_test.fee_type', 'admission', 'user'])
                ->where('government_department_id', $sspDepartmentId)
                ->whereBetween('created_at', [$date_start_at, $date_end_at]);

            if ($patientId) {
                $invoiceQuery->where('patient_id', $patientId);
            }

            if ($patientName) {
                $invoiceQuery->whereHas('patient', function ($q) use ($patientName) {
                    $q->where('first_name', 'like', '%'.$patientName.'%')
                        ->orWhere('last_name', 'like', '%'.$patientName.'%')
                        ->orWhere('father_husband_name', 'like', '%'.$patientName.'%');
                });
            }

            if ($sex !== null && $sex !== '') {
                $invoiceQuery->whereHas('patient', function ($q) use ($sex) {
                    $q->where('sex', $sex);
                });
            }

            if ($userId) {
                $invoiceQuery->where('user_id', $userId);
            }

            if ($unitWard) {
                $invoiceQuery->whereHas('admission', function ($q) use ($unitWard) {
                    $q->where('unit_ward', $unitWard);
                });
            }

            if ($feeCategoryId) {
                $invoiceQuery->whereHas('patient_test', function ($q) use ($feeCategoryId) {
                    $q->whereHas('fee_type', function ($q2) use ($feeCategoryId) {
                        $q2->where('fee_category_id', $feeCategoryId);
                    });
                });
            }

            if ($feeStatus) {
                $invoiceQuery->whereHas('patient_test', function ($q) use ($feeStatus) {
                    $q->where('status', $feeStatus);
                });
            }

            $ipdInvoices = $invoiceQuery->orderBy('created_at', 'asc')->get();
        }

        // --- Summary Statistics ---
        $summary = [
            'opd_total_patients' => $opdChits->unique('patient_id')->count(),
            'opd_total_chits' => $opdChits->count(),
            'opd_actual_amount' => $opdChits->sum('actual_amount'),
            'opd_charged_amount' => $opdChits->sum('amount'),
            'ipd_total_patients' => $ipdInvoices->unique('patient_id')->count(),
            'ipd_total_invoices' => $ipdInvoices->count(),
            'ipd_actual_amount' => $ipdInvoices->sum('actual_total_amount'),
            'ipd_charged_amount' => $ipdInvoices->sum('total_amount'),
            'ipd_total_tests' => $ipdInvoices->sum(fn ($inv) => $inv->patient_test->count()),
        ];

        $summary['grand_actual_amount'] = $summary['opd_actual_amount'] + $summary['ipd_actual_amount'];
        $summary['grand_charged_amount'] = $summary['opd_charged_amount'] + $summary['ipd_charged_amount'];
        $summary['grand_claimable_amount'] = $summary['grand_actual_amount'] - $summary['grand_charged_amount'];

        // --- Dropdown Data for Filters ---
        $departments = Department::orderBy('name')->get();
        $feeCategories = FeeCategory::orderBy('name')->get();
        $users = User::orderBy('name')->get();
        $admissionWards = AdmissionWard::orderBy('name')->get();

        return view('reports.ssp.claims', compact(
            'opdChits',
            'ipdInvoices',
            'summary',
            'start_date',
            'end_date',
            'departments',
            'feeCategories',
            'users',
            'admissionWards',
        ));
    }
}
