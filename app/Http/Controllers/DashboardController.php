<?php

namespace App\Http\Controllers;

use App\Models\Admission;
use App\Models\Chit;
use App\Models\Department;
use App\Models\FeeCategory;
use App\Models\FeeType;
use App\Models\Invoice;
use App\Models\Patient;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    //
    public function index(): View
    {
        $todayStart = Carbon::today()->startOfDay();
        $todayEnd = Carbon::today()->endOfDay();
        $user = \Auth::user();

        $hif_amount_today = 0;
        $government_amount_today = 0;
        $issued_chits = 0;
        $issued_invoices_revenue = 0;
        $issued_invoices = 0;
        $today_revenue = 0;
        $non_entitled = 0;
        $entitled = 0;

        $gender_wise = ['Male' => 0, 'Female' => 0];
        $age_group_wise_data = ['0-12' => 0, '13-20' => 0, '20-30' => 0, '30-50' => 0, '50-90+' => 0];
        $opd_department_wise = [];
        $admission_weekly_report = [];
        $patient_test_daily_report = [];
        $patient_test_daily_report_op = [];
        $patient_test_daily_report_rd = [];

        // OPD Front Desk
        if ($user->hasRole('Front Desk/Receptionist')) {
            $chitStats = Chit::query()
                ->where('user_id', $user->id)
                ->whereBetween('issued_date', [$todayStart, $todayEnd])
                ->selectRaw('COUNT(*) AS issued_chits')
                ->selectRaw('COALESCE(SUM(amount), 0) AS today_revenue')
                ->selectRaw('SUM(CASE WHEN government_non_gov IS FALSE THEN 1 ELSE 0 END) AS non_entitled')
                ->selectRaw('SUM(CASE WHEN government_non_gov IS TRUE THEN 1 ELSE 0 END) AS entitled')
                ->first();

            $invoiceStats = Invoice::query()
                ->where('user_id', $user->id)
                ->whereBetween('created_at', [$todayStart, $todayEnd])
                ->selectRaw('COUNT(*) AS issued_invoices')
                ->selectRaw('COALESCE(SUM(total_amount), 0) AS issued_invoices_revenue')
                ->first();

            $issued_chits = (int) ($chitStats->issued_chits ?? 0);
            $today_revenue = (float) ($chitStats->today_revenue ?? 0);
            $non_entitled = (int) ($chitStats->non_entitled ?? 0);
            $entitled = (int) ($chitStats->entitled ?? 0);
            $issued_invoices = (int) ($invoiceStats->issued_invoices ?? 0);
            $issued_invoices_revenue = (float) ($invoiceStats->issued_invoices_revenue ?? 0);

        } elseif ($user->hasRole(['Administrator', 'Super-Admin'])) {

            $departmentNames = Department::query()->pluck('name', 'id');
            $opd_department_wise = array_fill_keys($departmentNames->values()->toArray(), 0);

            for ($i = 12; $i >= 0; $i--) {
                $date = now()->subDays($i)->format('d/m');
                $admission_weekly_report[$date] = 0;
            }

            foreach (FeeCategory::query()->whereIn('id', [8, 9, 10, 11, 12])->pluck('name') as $name) {
                $patient_test_daily_report[$name] = 0;
            }

            foreach (FeeType::query()->whereIn('id', [9, 10])->pluck('type') as $type) {
                $patient_test_daily_report_op[$type] = 0;
            }

            foreach (FeeType::query()->whereIn('id', [6, 7, 8])->pluck('type') as $type) {
                $patient_test_daily_report_rd[$type] = 0;
            }

            $chitStats = Chit::query()
                ->whereBetween('issued_date', [$todayStart, $todayEnd])
                ->selectRaw('COUNT(*) AS issued_chits')
                ->selectRaw('COALESCE(SUM(amount), 0) AS today_revenue')
                ->selectRaw('SUM(CASE WHEN government_non_gov IS FALSE THEN 1 ELSE 0 END) AS non_entitled')
                ->selectRaw('SUM(CASE WHEN government_non_gov IS TRUE THEN 1 ELSE 0 END) AS entitled')
                ->selectRaw('COALESCE(SUM(amount_hif), 0) AS chits_hif_amount')
                ->first();

            $invoiceStats = Invoice::query()
                ->whereBetween('created_at', [$todayStart, $todayEnd])
                ->selectRaw('COUNT(*) AS issued_invoices')
                ->selectRaw('COALESCE(SUM(total_amount), 0) AS total_amount')
                ->selectRaw('COALESCE(SUM(hif_amount), 0) AS total_hif_amount')
                ->first();

            $issued_chits = (int) ($chitStats->issued_chits ?? 0);
            $today_revenue = (float) ($chitStats->today_revenue ?? 0);
            $non_entitled = (int) ($chitStats->non_entitled ?? 0);
            $entitled = (int) ($chitStats->entitled ?? 0);
            $issued_invoices = (int) ($invoiceStats->issued_invoices ?? 0);
            $issued_invoices_revenue = (float) ($invoiceStats->total_amount ?? 0);

            $hif_amount_today = (float) ($invoiceStats->total_hif_amount ?? 0) + (float) ($chitStats->chits_hif_amount ?? 0);
            $government_amount_today = ((float) ($invoiceStats->total_amount ?? 0) + (float) ($chitStats->today_revenue ?? 0)) - $hif_amount_today;

            $genderStats = Patient::query()
                ->whereBetween('created_at', [$todayStart, $todayEnd])
                ->selectRaw('SUM(CASE WHEN sex IS TRUE THEN 1 ELSE 0 END) AS male_count')
                ->selectRaw('SUM(CASE WHEN sex IS FALSE THEN 1 ELSE 0 END) AS female_count')
                ->first();

            $gender_wise = [
                'Male' => (int) ($genderStats->male_count ?? 0),
                'Female' => (int) ($genderStats->female_count ?? 0),
            ];

            $now = now();
            $d13 = $now->copy()->subYears(13)->toDateString();
            $d21 = $now->copy()->subYears(21)->toDateString();
            $d31 = $now->copy()->subYears(31)->toDateString();
            $d51 = $now->copy()->subYears(51)->toDateString();

            $age_group_raw = Patient::query()
                ->whereBetween('created_at', [$todayStart, $todayEnd])
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

            $opdDepartmentCounts = Chit::query()
                ->select('department_id', DB::raw('COUNT(*) AS total'))
                ->whereBetween('issued_date', [$todayStart, $todayEnd])
                ->whereNotNull('department_id')
                ->groupBy('department_id')
                ->pluck('total', 'department_id');

            foreach ($departmentNames as $departmentId => $departmentName) {
                $opd_department_wise[$departmentName] = (int) ($opdDepartmentCounts[$departmentId] ?? 0);
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
                ->whereBetween('patient_tests.created_at', [$todayStart, $todayEnd])
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
                ->whereBetween('patient_tests.created_at', [$todayStart, $todayEnd])
                ->whereIn('fee_type_id', [9, 10])
                ->groupBy('fee_types.type')
                ->get();

            $rd = DB::table('patient_tests')
                ->join('fee_types', 'patient_tests.fee_type_id', '=', 'fee_types.id')
                ->select('fee_types.type', DB::raw('COUNT(*) AS total'))
                ->whereNull('patient_tests.deleted_at')
                ->whereBetween('patient_tests.created_at', [$todayStart, $todayEnd])
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
