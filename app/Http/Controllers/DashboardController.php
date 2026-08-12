<?php

namespace App\Http\Controllers;

use App\Models\Admission;
use App\Models\Chit;
use App\Models\Department;
use App\Models\Invoice;
use App\Models\Patient;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    //
    public function index()
    {

        $hif_amount_today = 0;
        $government_amount_today = 0;
        $issued_chits = 0;
        $issued_invoices_revenue = 0;
        $issued_invoices = 0;
        $today_revenue = 0;
        $non_entitled = 0;
        $entitled = 0;
        $hif_today = 0;
        $government_today = 0;
        $user = \Auth::user();
        // reports variables
        $gender_wise = ['Male' => 0, 'Female' => 0];
        $age_group_wise_data = ['0-12' => 0, '13-20' => 0, '20-30' => 0, '30-50' => 0, '50-90+' => 0];
        $opd_department_wise = array_fill_keys(Department::pluck('name')->toArray(), 0);
        $admission_weekly_report = [];
        $patient_test_daily_report = [];
        $patient_test_daily_report_op = [];
        $patient_test_daily_report_rd = [];

        for ($i = 12; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('d/m');
            $admission_weekly_report[$date] = 0;
        }

        foreach (\App\Models\FeeCategory::whereIn('id', [8, 9, 10, 11, 12])->pluck('name') as $name) {
            $patient_test_daily_report[$name] = 0;
        }

        foreach (\App\Models\FeeType::whereIn('id', [9, 10])->pluck('type') as $type) {
            $patient_test_daily_report_op[$type] = 0;
        }

        foreach (\App\Models\FeeType::whereIn('id', [6, 7, 8])->pluck('type') as $type) {
            $patient_test_daily_report_rd[$type] = 0;
        }

        // OPD Front Desk
        if ($user->hasRole('Front Desk/Receptionist')) {
            $issued_chits = Chit::where('user_id', $user->id)->whereDate('issued_date', Carbon::today())->count();
            $today_revenue = Chit::where('user_id', $user->id)->whereDate('issued_date', Carbon::today())->sum('amount');
            $non_entitled = Chit::where('user_id', $user->id)->whereDate('issued_date', Carbon::today())->where('government_non_gov', 0)->count();
            $entitled = Chit::where('user_id', $user->id)->whereDate('issued_date', Carbon::today())->where('government_non_gov', 1)->count();
            $issued_invoices = Invoice::where('user_id', $user->id)->whereDate('created_at', Carbon::today())->count();

            $issued_invoices_revenue = Invoice::where('user_id', $user->id)->whereDate('created_at', Carbon::today())->sum('total_amount');

        } elseif ($user->hasRole(['Administrator', 'Super-Admin'])) {

            $issued_chits = Chit::whereDate('issued_date', Carbon::today())->count();
            $today_revenue = Chit::whereDate('issued_date', Carbon::today())->sum('amount');
            $non_entitled = Chit::whereDate('issued_date', Carbon::today())->where('government_non_gov', 0)->count();
            $entitled = Chit::whereDate('issued_date', Carbon::today())->where('government_non_gov', 1)->count();
            $issued_invoices = Invoice::whereDate('created_at', Carbon::today())->count();
            $issued_invoices_revenue = Invoice::whereDate('created_at', Carbon::today())->sum('total_amount');

            $hif_amount_today = Invoice::whereDate('created_at', Carbon::today())->sum('hif_amount') + Chit::whereDate('created_at', Carbon::today())->sum('amount_hif');
            $government_amount_today = (Invoice::whereDate('created_at', Carbon::today())->sum('total_amount') + Chit::whereDate('created_at', Carbon::today())->sum('amount')) - $hif_amount_today;

            // charts reports
            $gender_wise = ['Male' => Patient::where('sex', 1)->whereDate('created_at', Carbon::today())->count(), 'Female' => Patient::where('sex', 0)->whereDate('created_at', Carbon::today())->count()];

            $now = now();
            $d13 = $now->copy()->subYears(13)->toDateString();
            $d21 = $now->copy()->subYears(21)->toDateString();
            $d31 = $now->copy()->subYears(31)->toDateString();
            $d51 = $now->copy()->subYears(51)->toDateString();

            $age_group_raw = Patient::whereDate('created_at', today())
                ->selectRaw('
                    SUM(CASE WHEN dob > ? THEN 1 ELSE 0 END) as age_0_12,
                    SUM(CASE WHEN dob <= ? AND dob > ? THEN 1 ELSE 0 END) as age_13_20,
                    SUM(CASE WHEN dob <= ? AND dob > ? THEN 1 ELSE 0 END) as age_21_30,
                    SUM(CASE WHEN dob <= ? AND dob > ? THEN 1 ELSE 0 END) as age_31_50,
                    SUM(CASE WHEN dob <= ? THEN 1 ELSE 0 END) as age_50_plus
                ', [$d13, $d13, $d21, $d21, $d31, $d31, $d51, $d51])
                ->first();

            $age_group_wise_data = [
                '0-12' => (int) ($age_group_raw->age_0_12 ?? 0),
                '13-20' => (int) ($age_group_raw->age_13_20 ?? 0),
                '20-30' => (int) ($age_group_raw->age_21_30 ?? 0),
                '30-50' => (int) ($age_group_raw->age_31_50 ?? 0),
                '50-90+' => (int) ($age_group_raw->age_50_plus ?? 0),
            ];

            foreach ($age_group_wise_data as $key => $value) {
                $age_group_wise[$key] = (int) $value;
            }

            foreach ($opd_department_wise as $key => $value) {
                $id = Department::where('name', $key)->first()->id;
                $opd_department_wise[$key] = Chit::where('department_id', $id)->whereDate('issued_date', Carbon::today())->count();
            }

            $admissions_data = Admission::select(DB::raw('CAST(created_at AS DATE) as admission_date'), DB::raw('COUNT(*) AS count'))
                ->where('created_at', '>=', now()->subDays(12)->startOfDay())
                ->where('status', '=', 'No')
                ->groupBy(DB::raw('CAST(created_at AS DATE)'))
                ->orderBy('admission_date', 'DESC')
                ->get();

            foreach ($admissions_data as $item) {
                $admission_weekly_report[Carbon::parse($item->admission_date)->format('d/m')] = $item->count;
            }

            $pt = DB::table('patient_tests')
                ->join('fee_types', 'patient_tests.fee_type_id', '=', 'fee_types.id')
                ->join('fee_categories', 'fee_types.fee_category_id', '=', 'fee_categories.id')
                ->select('fee_categories.name', DB::raw('count(patient_tests.fee_type_id) as total'))
                ->whereNull('patient_tests.deleted_at')
                ->whereDate('patient_tests.created_at', Carbon::today())
                ->whereIn('fee_types.fee_category_id', [8, 9, 10, 11, 12])
                ->groupBy('fee_categories.name')
                ->get();
            foreach ($pt as $item) {
                $patient_test_daily_report[$item->name] = $item->total;
            }

            $op = DB::table('patient_tests')
                ->join('fee_types', 'patient_tests.fee_type_id', '=', 'fee_types.id')
                ->select('fee_types.type', DB::raw('COUNT(*) AS total'))
                ->whereNull('patient_tests.deleted_at')
                ->whereDate('patient_tests.created_at', Carbon::today())
                ->whereIn('fee_type_id', [9, 10])
                ->groupBy('fee_types.type')
                ->get();

            $rd = DB::table('patient_tests')
                ->join('fee_types', 'patient_tests.fee_type_id', '=', 'fee_types.id')
                ->select('fee_types.type', DB::raw('COUNT(*) AS total'))
                ->whereNull('patient_tests.deleted_at')
                ->whereDate('patient_tests.created_at', Carbon::today())
                ->whereIn('fee_type_id', [6, 7, 8])
                ->groupBy('fee_types.type')
                ->get();

            foreach ($op as $item) {
                $patient_test_daily_report_op[$item->type] = $item->total;
            }

            foreach ($rd as $item) {
                $patient_test_daily_report_rd[$item->type] = $item->total;
            }

        }

        return view('dashboard', compact('issued_chits', 'today_revenue', 'non_entitled', 'entitled', 'issued_invoices', 'issued_invoices_revenue',
            'gender_wise',
            'age_group_wise_data',
            'opd_department_wise',
            'admission_weekly_report',
            'patient_test_daily_report',
            'government_amount_today',
            'hif_amount_today',
            'patient_test_daily_report_op',
            'patient_test_daily_report_rd',
        ));

    }
}
