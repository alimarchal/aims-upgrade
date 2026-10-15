<?php

namespace App\Services;

use App\Models\Chit;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Numbers for the two dashboards. "Govt" is the government share of a payment
 * (amount - HIF), the same meaning it has in the reports. Date filters use
 * ranges (>= start, < end) so the indexes on the date columns stay usable.
 */
class DashboardData
{
    private const CHIT_GOVT = 'amount - COALESCE(amount_hif, 0)';

    private const INVOICE_GOVT = 'total_amount - COALESCE(hif_amount, 0)';

    /**
     * Management dashboard for a date range (default: today). Cached for a minute because it scans
     * months of data; only scalars and arrays are cached.
     */
    public static function admin(?Carbon $from = null, ?Carbon $to = null): array
    {
        $to = ($to ?? Carbon::today())->copy()->startOfDay();
        $from = ($from ?? $to)->copy()->startOfDay();
        if ($from->gt($to)) {
            [$from, $to] = [$to, $from];
        }
        if ($to->gt(Carbon::today())) {
            $to = Carbon::today();
        }
        if ($from->diffInDays($to) > 800) {
            $from = $to->copy()->subDays(800);
        }

        return Cache::remember('dashboard.admin.'.$from->toDateString().'.'.$to->toDateString().'.'.now()->format('YmdHi').'.'.intdiv((int) now()->format('i'), 2), 120, fn () => self::buildAdmin($from, $to));
    }

    /** @return array<string, mixed> */
    private static function buildAdmin(Carbon $from, Carbon $to): array
    {
        $today = Carbon::today();
        $end = $to->copy()->addDay(); // exclusive upper bound
        $length = (int) $from->diffInDays($to) + 1;
        $prevEnd = $from->copy(); // exclusive end of the previous period of the same length
        $prevFrom = $from->copy()->subDays($length);
        // A period that includes right now is only partly over, so compare it with the previous period
        // up to the same moment (today so far against yesterday at this time), not with a whole day.
        $isLive = $to->isSameDay($today);
        $prevCompareEnd = $isLive ? $prevFrom->copy()->addSeconds(max(60, (int) $from->diffInSeconds(now()))) : $prevEnd;
        $monthStart = $today->copy()->startOfMonth();
        $tomorrow = $today->copy()->addDay();

        $chit = fn (Carbon $a, Carbon $b) => DB::table('chits')->whereNull('chits.deleted_at')->where('chits.issued_date', '>=', $a)->where('chits.issued_date', '<', $b);
        $inv = fn (Carbon $a, Carbon $b) => DB::table('invoices')->whereNull('invoices.deleted_at')->where('invoices.created_at', '>=', $a)->where('invoices.created_at', '<', $b);
        $money = function (Carbon $a, Carbon $b) use ($chit, $inv) {
            $c = $chit($a, $b)->selectRaw('COUNT(*) n, COALESCE(SUM(amount),0) amt, COALESCE(SUM(amount_hif),0) hif, COALESCE(SUM(CASE WHEN government_non_gov THEN 1 ELSE 0 END),0) ent')->first();
            $i = $inv($a, $b)->selectRaw('COUNT(*) n, COALESCE(SUM(total_amount),0) amt, COALESCE(SUM(hif_amount),0) hif')->first();

            return [
                'chits' => (int) $c->n, 'invoices' => (int) $i->n, 'entitled' => (int) $c->ent,
                'non_entitled' => (int) $c->n - (int) $c->ent,
                'collected' => (float) $c->amt + (float) $i->amt, 'hif' => (float) $c->hif + (float) $i->hif,
                'govt' => ((float) $c->amt - (float) $c->hif) + ((float) $i->amt - (float) $i->hif),
                'chit_gross' => (float) $c->amt, 'invoice_gross' => (float) $i->amt,
                'chit_govt' => (float) $c->amt - (float) $c->hif, 'invoice_govt' => (float) $i->amt - (float) $i->hif,
            ];
        };
        $count = fn (string $table, string $col, Carbon $a, Carbon $b) => DB::table($table)
            ->when(Schema::hasColumn($table, 'deleted_at'), fn ($q) => $q->whereNull('deleted_at'))->where($col, '>=', $a)->where($col, '<', $b)->count();

        $period = $money($from, $end);
        $previous = $money($prevFrom, $prevCompareEnd);
        $period['patients'] = $count('patients', 'created_at', $from, $end);
        $previous['patients'] = $count('patients', 'created_at', $prevFrom, $prevCompareEnd);
        $period['admissions'] = $count('admissions', 'created_at', $from, $end);
        $previous['admissions'] = $count('admissions', 'created_at', $prevFrom, $prevCompareEnd);

        // ---- trend: daily up to ~3 months, monthly beyond. Short periods (today, yesterday ...) are
        // drawn with at least 14 days of context so the charts are never a single dot. ----
        $chartFrom = $length < 14 ? $to->copy()->subDays(13) : $from->copy();
        $chartLength = (int) $chartFrom->diffInDays($to) + 1;
        $monthly = $chartLength > 92;
        $bucket = $monthly ? "to_char(%s, 'YYYY-MM')" : 'CAST(%s AS DATE)';
        $keys = [];
        $labels = [];
        if ($monthly) {
            for ($d = $chartFrom->copy()->startOfMonth(); $d->lte($to); $d->addMonth()) {
                $keys[] = $d->format('Y-m');
                $labels[] = $d->format('M y');
            }
        } else {
            for ($d = $chartFrom->copy(); $d->lte($to); $d->addDay()) {
                $keys[] = $d->toDateString();
                $labels[] = $d->format('d M');
            }
        }
        $cRows = $chit($chartFrom, $end)->selectRaw(sprintf($bucket, 'chits.issued_date').' k, COUNT(*) n, SUM(chits.amount - COALESCE(chits.amount_hif,0)) g, SUM(COALESCE(chits.amount_hif,0)) h, SUM(CASE WHEN chits.government_non_gov THEN 1 ELSE 0 END) e')->groupBy('k')->get()->keyBy('k');
        $iRows = $inv($chartFrom, $end)->selectRaw(sprintf($bucket, 'invoices.created_at').' k, COUNT(*) n, SUM(invoices.total_amount - COALESCE(invoices.hif_amount,0)) g, SUM(COALESCE(invoices.hif_amount,0)) h')->groupBy('k')->get()->keyBy('k');
        $pRows = DB::table('patients')->whereNull('deleted_at')->where('created_at', '>=', $chartFrom)->where('created_at', '<', $end)->selectRaw(sprintf($bucket, 'created_at').' k, COUNT(*) n')->groupBy('k')->pluck('n', 'k');
        $aRows = DB::table('admissions')->where('created_at', '>=', $chartFrom)->where('created_at', '<', $end)->selectRaw(sprintf($bucket, 'created_at').' k, COUNT(*) n')->groupBy('k')->pluck('n', 'k');

        $series = ['chits' => [], 'invoices' => [], 'govt' => [], 'hif' => [], 'patients' => [], 'admissions' => [], 'entitled' => [], 'paid' => []];
        foreach ($keys as $k) {
            $series['chits'][] = (int) ($cRows[$k]->n ?? 0);
            $series['invoices'][] = (int) ($iRows[$k]->n ?? 0);
            $series['govt'][] = round((float) ($cRows[$k]->g ?? 0) + (float) ($iRows[$k]->g ?? 0));
            $series['hif'][] = round((float) ($cRows[$k]->h ?? 0) + (float) ($iRows[$k]->h ?? 0));
            $series['patients'][] = (int) ($pRows[$k] ?? 0);
            $series['admissions'][] = (int) ($aRows[$k] ?? 0);
            $series['entitled'][] = (int) ($cRows[$k]->e ?? 0);
            $series['paid'][] = (int) ($cRows[$k]->n ?? 0) - (int) ($cRows[$k]->e ?? 0);
        }
        $movingAverage = [];
        $window = $monthly ? 3 : 7;
        foreach ($series['chits'] as $i => $v) {
            $slice = array_slice($series['chits'], max(0, $i - $window + 1), min($window, $i + 1));
            $movingAverage[] = round(array_sum($slice) / max(1, count($slice)), 1);
        }

        // ---- distributions inside the period ----
        $departments = $chit($from, $end)->join('departments', 'departments.id', '=', 'chits.department_id')
            ->selectRaw('departments.name, COUNT(*) n, SUM(chits.amount - COALESCE(chits.amount_hif,0)) g, SUM(COALESCE(chits.amount_hif,0)) h')
            ->groupBy('departments.name')->orderByDesc('n')->limit(12)->get()->map(fn ($r) => (array) $r)->all();
        $govtDepartments = $chit($from, $end)->join('government_departments', 'government_departments.id', '=', 'chits.government_department_id')
            ->selectRaw('government_departments.name, COUNT(*) n')->groupBy('government_departments.name')->orderByDesc('n')->limit(8)->get()->map(fn ($r) => (array) $r)->all();

        $testBase = fn () => DB::table('patient_tests')->whereNull('patient_tests.deleted_at')
            ->where('patient_tests.created_at', '>=', $from)->where('patient_tests.created_at', '<', $end)
            ->join('fee_types', 'fee_types.id', '=', 'patient_tests.fee_type_id')
            ->join('fee_categories', 'fee_categories.id', '=', 'fee_types.fee_category_id');
        $govtExpr = 'SUM(patient_tests.total_amount - COALESCE(patient_tests.hif_amount,0))';
        $hifExpr = 'SUM(COALESCE(patient_tests.hif_amount,0))';
        $categories = $testBase()->selectRaw("fee_categories.name, COUNT(*) n, {$govtExpr} g, {$hifExpr} h")->groupBy('fee_categories.name')->orderByRaw('(SUM(patient_tests.total_amount)) DESC')->limit(10)->get()->map(fn ($r) => (array) $r)->all();
        $services = $testBase()->selectRaw("fee_types.type name, COUNT(*) n, {$govtExpr} g, {$hifExpr} h")->groupBy('fee_types.type')->orderByRaw('(SUM(patient_tests.total_amount)) DESC')->limit(10)->get()->map(fn ($r) => (array) $r)->all();
        $serviceCounts = fn (array $feeTypeIds) => (function () use ($testBase, $feeTypeIds) {
            $done = $testBase()->whereIn('fee_types.id', $feeTypeIds)->selectRaw('fee_types.id, COUNT(*) n')->groupBy('fee_types.id')->pluck('n', 'id');

            return DB::table('fee_types')->whereIn('id', $feeTypeIds)->orderBy('id')->get(['id', 'type'])
                ->map(fn ($t) => ['name' => $t->type, 'n' => (int) ($done[$t->id] ?? 0)])->all();
        })();
        $operationTheatre = $serviceCounts([9, 10]);
        $radiology = $serviceCounts([6, 7, 8]);

        $hours = array_fill(0, 24, 0);
        foreach ($chit($from, $end)->selectRaw('EXTRACT(HOUR FROM chits.issued_date)::int h, COUNT(*) n')->groupBy('h')->get() as $row) {
            $hours[(int) $row->h] = (int) $row->n;
        }
        $hourSlice = array_slice($hours, 7, 16, true);
        $peakHour = max($hourSlice) > 0 ? array_keys($hourSlice, max($hourSlice))[0] : 10;

        $weekdayTotals = array_fill(1, 7, 0);
        $weekdayDays = array_fill(1, 7, []);
        foreach ($chit($from, $end)->selectRaw('EXTRACT(ISODOW FROM chits.issued_date)::int w, CAST(chits.issued_date AS DATE) d, COUNT(*) n')->groupBy('w', 'd')->get() as $row) {
            $weekdayTotals[(int) $row->w] += (int) $row->n;
            $weekdayDays[(int) $row->w][$row->d] = true;
        }
        $weekday = [];
        foreach (['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'] as $i => $label) {
            $days = count($weekdayDays[$i + 1]);
            $weekday[$label] = $days ? (int) round($weekdayTotals[$i + 1] / $days) : 0;
        }

        $gender = ['Male' => 0, 'Female' => 0];
        $age = array_fill_keys(['0-12', '13-20', '21-30', '31-50', '50+'], 0);
        $pt = DB::table('patients')->whereNull('deleted_at')->where('created_at', '>=', $from)->where('created_at', '<', $end);
        $gender['Male'] = (clone $pt)->where('sex', 1)->count();
        $gender['Female'] = (clone $pt)->where('sex', 0)->count();
        $cut = fn (int $y) => $to->copy()->subYears($y)->toDateString();
        $ar = (clone $pt)->selectRaw('
            COALESCE(SUM(CASE WHEN dob > ? THEN 1 ELSE 0 END),0) a1,
            COALESCE(SUM(CASE WHEN dob <= ? AND dob > ? THEN 1 ELSE 0 END),0) a2,
            COALESCE(SUM(CASE WHEN dob <= ? AND dob > ? THEN 1 ELSE 0 END),0) a3,
            COALESCE(SUM(CASE WHEN dob <= ? AND dob > ? THEN 1 ELSE 0 END),0) a4,
            COALESCE(SUM(CASE WHEN dob <= ? THEN 1 ELSE 0 END),0) a5', [$cut(13), $cut(13), $cut(21), $cut(21), $cut(31), $cut(31), $cut(51), $cut(51)])->first();
        $age = ['0-12' => (int) $ar->a1, '13-20' => (int) $ar->a2, '21-30' => (int) $ar->a3, '31-50' => (int) $ar->a4, '50+' => (int) $ar->a5];

        $wards = DB::table('admissions')->where('created_at', '>=', $from)->where('created_at', '<', $end)->whereNotNull('unit_ward')
            ->selectRaw('unit_ward name, COUNT(*) n')->groupBy('unit_ward')->orderByDesc('n')->limit(8)->get()->map(fn ($r) => (array) $r)->all();

        // ---- analytics: race against the previous period, entitled vs paid, year on year, spread of busy days ----
        $race = self::race($chit, $inv, $from, $end, $prevFrom, $prevEnd, $length);

        $yoyYear = (int) $to->format('Y');
        $yoyFrom = Carbon::create($yoyYear - 1, 1, 1)->startOfDay();
        $yoyRows = $chit($yoyFrom, $tomorrow)->selectRaw('EXTRACT(YEAR FROM chits.issued_date)::int y, EXTRACT(MONTH FROM chits.issued_date)::int m, COUNT(*) n, SUM(chits.amount - COALESCE(chits.amount_hif,0)) g, SUM(COALESCE(chits.amount_hif,0)) h')->groupBy('y', 'm')->get();
        $yoyInvRows = $inv($yoyFrom, $tomorrow)->selectRaw('EXTRACT(YEAR FROM invoices.created_at)::int y, EXTRACT(MONTH FROM invoices.created_at)::int m, SUM(invoices.total_amount - COALESCE(invoices.hif_amount,0)) g, SUM(COALESCE(invoices.hif_amount,0)) h')->groupBy('y', 'm')->get();
        $yoy = ['year' => $yoyYear, 'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            'chits' => [$yoyYear => array_fill(1, 12, 0), $yoyYear - 1 => array_fill(1, 12, 0)], 'govt' => [$yoyYear => array_fill(1, 12, 0), $yoyYear - 1 => array_fill(1, 12, 0)], 'hif' => [$yoyYear => array_fill(1, 12, 0), $yoyYear - 1 => array_fill(1, 12, 0)]];
        foreach ($yoyRows as $r) {
            $yoy['chits'][(int) $r->y][(int) $r->m] = (int) $r->n;
            $yoy['govt'][(int) $r->y][(int) $r->m] = (float) $r->g;
            $yoy['hif'][(int) $r->y][(int) $r->m] = (float) $r->h;
        }
        foreach ($yoyInvRows as $r) {
            $yoy['govt'][(int) $r->y][(int) $r->m] += (float) $r->g;
            $yoy['hif'][(int) $r->y][(int) $r->m] += (float) $r->h;
        }
        foreach ($yoy['chits'] as $y => $vals) { // months that have not happened yet stay empty, not zero
            foreach ($vals as $m => $n) {
                if (Carbon::create($y, $m, 1)->gt($today)) {
                    $yoy['chits'][$y][$m] = null;
                    $yoy['govt'][$y][$m] = null;
                    $yoy['hif'][$y][$m] = null;
                }
            }
            $yoy['chits'][$y] = array_values($yoy['chits'][$y]);
            $yoy['govt'][$y] = array_map(fn ($v) => $v === null ? null : round($v), array_values($yoy['govt'][$y]));
            $yoy['hif'][$y] = array_map(fn ($v) => $v === null ? null : round($v), array_values($yoy['hif'][$y]));
            $yoy['total'][$y] = array_map(fn ($g, $h) => $g === null ? null : $g + $h, $yoy['govt'][$y], $yoy['hif'][$y]);
        }

        // which part of the month is busy (average chits per calendar day over the last 12 months)
        $domRows = $chit($firstMonthForDom = $monthStart->copy()->subMonths(11), $tomorrow)->selectRaw('EXTRACT(DAY FROM chits.issued_date)::int d, CAST(chits.issued_date AS DATE) dt, COUNT(*) n')->groupBy('d', 'dt')->get();
        $domTotals = array_fill(1, 31, 0);
        $domDays = array_fill(1, 31, 0);
        foreach ($domRows as $r) {
            $domTotals[(int) $r->d] += (int) $r->n;
            $domDays[(int) $r->d]++;
        }
        $dayOfMonth = array_map(fn ($d) => $domDays[$d] ? (int) round($domTotals[$d] / $domDays[$d]) : 0, range(1, 31));

        // age pyramid of the new patients in the period (one aggregate query, no rows loaded into PHP)
        $groups = ['0-12', '13-20', '21-30', '31-50', '50+'];
        $pyramid = ['groups' => $groups, 'male' => array_fill(0, 5, 0), 'female' => array_fill(0, 5, 0)];
        $c13 = $to->copy()->subYears(13)->toDateString();
        $c21 = $to->copy()->subYears(21)->toDateString();
        $c31 = $to->copy()->subYears(31)->toDateString();
        $c51 = $to->copy()->subYears(51)->toDateString();
        $pyRows = DB::table('patients')->whereNull('deleted_at')->where('created_at', '>=', $from)->where('created_at', '<', $end)->whereNotNull('dob')
            ->selectRaw('sex, CASE WHEN dob > ? THEN 0 WHEN dob > ? THEN 1 WHEN dob > ? THEN 2 WHEN dob > ? THEN 3 ELSE 4 END g, COUNT(*) n', [$c13, $c21, $c31, $c51])
            ->groupBy('sex', 'g')->get();
        foreach ($pyRows as $row) {
            $pyramid[$row->sex ? 'male' : 'female'][(int) $row->g] = (int) $row->n;
        }

        // a usual day: the average of the same weekday over the 8 weeks before the end of the period
        $sameWeekday = [];
        for ($w = 1; $w <= 8; $w++) {
            $d = $to->copy()->subWeeks($w);
            $sameWeekday[] = $chit($d, $d->copy()->addDay())->count();
        }
        $usual = (int) round(array_sum($sameWeekday) / max(1, count(array_filter($sameWeekday, fn ($n) => $n > 0))));

        // ---- extra insights for the period ----
        $freeCare = (float) $chit($from, $end)->where('chits.government_non_gov', true)->join('fee_types', 'fee_types.id', '=', 'chits.fee_type_id')->sum('fee_types.amount');
        $specialist = $chit($from, $end)->join('departments', 'departments.id', '=', 'chits.department_id')->where('departments.name', 'ilike', '%specialist%')
            ->selectRaw('COUNT(*) n, COALESCE(SUM(chits.amount - COALESCE(chits.amount_hif,0)),0) g')->first();
        $deletedChits = DB::table('chits')->whereNotNull('deleted_at')->where('deleted_at', '>=', $from)->where('deleted_at', '<', $end)->selectRaw('COUNT(*) n, COALESCE(SUM(amount),0) a')->first();
        $deletedInvoices = DB::table('invoices')->whereNotNull('deleted_at')->where('deleted_at', '>=', $from)->where('deleted_at', '<', $end)->selectRaw('COUNT(*) n, COALESCE(SUM(total_amount),0) a')->first();
        $returns = DB::table('patient_tests')->whereNull('deleted_at')->where('status', 'Return')->where('created_at', '>=', $from)->where('created_at', '<', $end)->selectRaw('COUNT(*) n, COALESCE(SUM(total_amount),0) a')->first();
        $ssp = config('modules.ssp') ? $chit($from, $end)->where('chits.government_department_id', 95)->selectRaw('COUNT(*) n, COALESCE(SUM(chits.actual_amount),0) a')->first() : null;

        $periodDays = [];
        foreach ($chit($from, $end)->selectRaw('CAST(chits.issued_date AS DATE) d, COUNT(*) n')->groupBy('d')->pluck('n', 'd') as $d => $n) {
            $periodDays[$d] = (int) $n;
        }
        $busiestDay = $periodDays ? array_keys($periodDays, max($periodDays))[0] : null;
        $quietDay = $periodDays && count($periodDays) > 1 ? array_keys($periodDays, min($periodDays))[0] : null;
        $activeDays = max(1, count($periodDays));

        // ---- always-on context: last 12 months, 90-day heat map, anomalies, month projection ----
        $months = [];
        for ($m = 11; $m >= 0; $m--) {
            $d = $monthStart->copy()->subMonths($m);
            $months[$d->format('Y-m')] = $d;
        }
        $firstMonth = Carbon::parse(array_key_first($months).'-01');
        $chitMonthly = $chit($firstMonth, $tomorrow)->selectRaw("to_char(chits.issued_date, 'YYYY-MM') m, COUNT(*) n, SUM(chits.amount - COALESCE(chits.amount_hif,0)) g, SUM(COALESCE(chits.amount_hif,0)) h")->groupBy('m')->get()->keyBy('m');
        $invMonthly = $inv($firstMonth, $tomorrow)->selectRaw("to_char(invoices.created_at, 'YYYY-MM') m, SUM(invoices.total_amount - COALESCE(invoices.hif_amount,0)) g, SUM(COALESCE(invoices.hif_amount,0)) h")->groupBy('m')->get()->keyBy('m');

        $heatStart = $to->copy()->subDays(89);
        $heat = array_fill(1, 7, array_fill(8, 14, 0)); // ISO weekday x 08..21
        foreach ($chit($heatStart, $end)->selectRaw('EXTRACT(ISODOW FROM chits.issued_date)::int w, EXTRACT(HOUR FROM chits.issued_date)::int h, COUNT(*) n')->groupBy('w', 'h')->get() as $row) {
            if (isset($heat[(int) $row->w][(int) $row->h])) {
                $heat[(int) $row->w][(int) $row->h] = (int) $row->n;
            }
        }
        $heatSeries = [];
        foreach (['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'] as $i => $label) {
            $heatSeries[] = ['name' => $label, 'data' => array_map(fn ($h, $n) => ['x' => sprintf('%02d', $h), 'y' => $n], array_keys($heat[$i + 1]), array_values($heat[$i + 1]))];
        }

        // days that were unusually busy or quiet (|z| > 2) in the 90 days up to the end of the period
        // today is unfinished, so it is left out of the normal-range statistics
        $dailyEnd = $to->isSameDay($today) ? $today : $end;
        $dailyLastDay = $dailyEnd->copy()->subDay();
        $daily = $chit($heatStart, $dailyEnd)->selectRaw('CAST(chits.issued_date AS DATE) d, COUNT(*) n')->groupBy('d')->pluck('n', 'd')->map(fn ($n) => (int) $n)->all();
        $anomalies = [];
        if (count($daily) >= 14) {
            $mean = array_sum($daily) / count($daily);
            $std = sqrt(array_sum(array_map(fn ($v) => ($v - $mean) ** 2, $daily)) / count($daily));
            if ($std > 0) {
                foreach ($daily as $d => $n) {
                    $z = ($n - $mean) / $std;
                    if (abs($z) >= 2) {
                        $anomalies[] = ['date' => Carbon::parse($d)->format('D d M'), 'chits' => $n, 'z' => round($z, 1), 'kind' => $z > 0 ? 'busy' : 'quiet'];
                    }
                }
                usort($anomalies, fn ($a, $b) => abs($b['z']) <=> abs($a['z']));
                $anomalies = array_slice($anomalies, 0, 6);
            }
        }
        $dailyMean = $daily ? (int) round(array_sum($daily) / count($daily)) : 0;

        // spread of a normal day for each weekday (box plot) and the control chart limits
        $byWeekday = array_fill(1, 7, []);
        foreach ($daily as $d => $n) {
            $byWeekday[(int) Carbon::parse($d)->isoWeekday()][] = $n;
        }
        $quantile = function (array $v, float $q) {
            sort($v);
            $pos = (count($v) - 1) * $q;
            $lo = (int) floor($pos);
            $hi = (int) ceil($pos);

            return $v[$lo] + ($v[$hi] - $v[$lo]) * ($pos - $lo);
        };
        $box = [];
        foreach (['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'] as $i => $label) {
            $v = $byWeekday[$i + 1];
            $box[] = ['x' => $label, 'y' => $v ? [(int) min($v), (int) round($quantile($v, .25)), (int) round($quantile($v, .5)), (int) round($quantile($v, .75)), (int) max($v)] : [0, 0, 0, 0, 0]];
        }
        $controlMean = $daily ? array_sum($daily) / count($daily) : 0;
        $controlStd = $daily ? sqrt(array_sum(array_map(fn ($v) => ($v - $controlMean) ** 2, $daily)) / count($daily)) : 0;
        $control = ['labels' => [], 'values' => [], 'mean' => round($controlMean), 'upper' => round($controlMean + 2 * $controlStd), 'lower' => max(0, round($controlMean - 2 * $controlStd))];
        foreach (self::lastDaysBetween($heatStart, $dailyLastDay) as $d) {
            $control['labels'][] = Carbon::parse($d)->format('d M');
            $control['values'][] = $daily[$d] ?? 0;
        }

        $mtd = $money($monthStart, $tomorrow);
        $elapsed = max(1, $today->day);
        $daysInMonth = $today->daysInMonth;
        $projection = [
            'elapsed' => $elapsed, 'days' => $daysInMonth,
            'chits' => (int) round($mtd['chits'] / $elapsed * $daysInMonth),
            'govt' => round($mtd['govt'] / $elapsed * $daysInMonth),
            'mtd_chits' => $mtd['chits'], 'mtd_govt' => $mtd['govt'],
        ];

        return [
            'from' => $from->toDateString(), 'to' => $to->toDateString(), 'length' => $length, 'monthly' => $monthly,
            'live' => $isLive, 'compare_until' => $length === 1 ? $prevCompareEnd->format('H:i') : $prevCompareEnd->format('d M H:i'), 'chart_from' => $chartFrom->toDateString(), 'chart_length' => $chartLength, 'widened' => $length < 14,
            'prev_from' => $prevFrom->toDateString(), 'prev_to' => $prevEnd->copy()->subDay()->toDateString(),
            'period' => $period, 'previous' => $previous, 'usual' => $usual,
            'labels' => $labels, 'series' => $series, 'moving_average' => $movingAverage, 'window' => $window,
            'departments' => $departments, 'govt_departments' => $govtDepartments,
            'categories' => $categories, 'services' => $services, 'operation_theatre' => $operationTheatre, 'radiology' => $radiology,
            'hours' => array_values($hourSlice), 'hour_labels' => array_map(fn ($h) => sprintf('%02d:00', $h), array_keys($hourSlice)), 'peak_hour' => $peakHour,
            'extras' => [
                'free_care' => $freeCare,
                'specialist_chits' => (int) $specialist->n, 'specialist_govt' => (float) $specialist->g,
                'deleted_chits' => (int) $deletedChits->n, 'deleted_chits_amount' => (float) $deletedChits->a,
                'deleted_invoices' => (int) $deletedInvoices->n, 'deleted_invoices_amount' => (float) $deletedInvoices->a,
                'returns' => (int) $returns->n, 'returns_amount' => (float) $returns->a,
                'ssp' => $ssp ? ['n' => (int) $ssp->n, 'claims' => (float) $ssp->a] : null,
                'busiest_day' => $busiestDay ? ['date' => Carbon::parse($busiestDay)->format('D d M Y'), 'n' => $periodDays[$busiestDay]] : null,
                'quietest_day' => $quietDay ? ['date' => Carbon::parse($quietDay)->format('D d M Y'), 'n' => $periodDays[$quietDay]] : null,
                'active_days' => $activeDays,
                'peak_per_hour' => $hours[$peakHour] / $activeDays,
            ],
            'race' => $race, 'yoy' => $yoy, 'day_of_month' => $dayOfMonth, 'pyramid' => $pyramid, 'box' => $box, 'control' => $control,
            'weekday' => $weekday, 'gender' => $gender, 'age' => $age, 'wards' => $wards,
            'months' => array_values(array_map(fn (Carbon $d) => $d->format('M y'), $months)),
            'chits_monthly' => array_values(array_map(fn ($k) => (int) ($chitMonthly[$k]->n ?? 0), array_keys($months))),
            'govt_monthly' => array_values(array_map(fn ($k) => round((float) ($chitMonthly[$k]->g ?? 0) + (float) ($invMonthly[$k]->g ?? 0)), array_keys($months))),
            'hif_monthly' => array_values(array_map(fn ($k) => round((float) ($chitMonthly[$k]->h ?? 0) + (float) ($invMonthly[$k]->h ?? 0)), array_keys($months))),
            'heat' => $heatSeries, 'heat_from' => $heatStart->format('d M Y'),
            'anomalies' => $anomalies, 'daily_mean' => $dailyMean, 'projection' => $projection,
            'team' => self::team($from, $end),
            'last_chit_at' => ($last = DB::table('chits')->whereNull('deleted_at')->max('issued_date')) ? Carbon::parse($last)->toIso8601String() : null,
        ];
    }

    /** Collection per user for the chosen period. */
    private static function team(Carbon $from, Carbon $end): array
    {
        $rows = [];
        $add = function ($table, $dateCol, $govtExpr, $key) use (&$rows, $from, $end) {
            foreach (DB::table($table)->whereNull('deleted_at')->where($dateCol, '>=', $from)->where($dateCol, '<', $end)
                ->selectRaw("user_id, COUNT(*) n, SUM({$govtExpr}) g")->groupBy('user_id')->get() as $r) {
                $rows[$r->user_id][$key] = (int) $r->n;
                $rows[$r->user_id]['govt'] = ($rows[$r->user_id]['govt'] ?? 0) + (float) $r->g;
            }
        };
        $add('chits', 'issued_date', self::CHIT_GOVT, 'chits');
        $add('invoices', 'created_at', self::INVOICE_GOVT, 'invoices');

        $names = User::whereIn('id', array_keys($rows))->pluck('name', 'id');
        $total = max(1.0, (float) collect($rows)->sum('govt'));

        return collect($rows)->map(fn ($v, $id) => [
            'name' => $names[$id] ?? 'User #'.$id,
            'chits' => $v['chits'] ?? 0,
            'invoices' => $v['invoices'] ?? 0,
            'govt' => round($v['govt'] ?? 0),
            'share' => round(($v['govt'] ?? 0) / $total * 100, 1),
        ])->sortByDesc('govt')->values()->all();
    }

    /** @return array<string, mixed> */
    public static function frontDesk(User $user): array
    {
        $today = Carbon::today();
        $tomorrow = $today->copy()->addDay();
        $yesterday = $today->copy()->subDay();
        $monthStart = $today->copy()->startOfMonth();

        $chit = fn (Carbon $from, Carbon $to) => DB::table('chits')->whereNull('chits.deleted_at')->where('chits.user_id', $user->id)->where('chits.issued_date', '>=', $from)->where('chits.issued_date', '<', $to);
        $inv = fn (Carbon $from, Carbon $to) => DB::table('invoices')->whereNull('deleted_at')->where('user_id', $user->id)->where('created_at', '>=', $from)->where('created_at', '<', $to);
        $sum = function (Carbon $from, Carbon $to) use ($chit, $inv) {
            $c = $chit($from, $to)->selectRaw('COUNT(*) n, COALESCE(SUM(amount),0) amt, COALESCE(SUM(amount_hif),0) hif, COALESCE(SUM(CASE WHEN government_non_gov THEN 1 ELSE 0 END),0) ent')->first();
            $i = $inv($from, $to)->selectRaw('COUNT(*) n, COALESCE(SUM(total_amount),0) amt, COALESCE(SUM(hif_amount),0) hif')->first();

            return [
                'chits' => (int) $c->n, 'invoices' => (int) $i->n, 'entitled' => (int) $c->ent, 'non_entitled' => (int) $c->n - (int) $c->ent,
                'chit_gross' => (float) $c->amt, 'invoice_gross' => (float) $i->amt,
                'collected' => (float) $c->amt + (float) $i->amt, 'hif' => (float) $c->hif + (float) $i->hif,
                'govt' => ((float) $c->amt - (float) $c->hif) + ((float) $i->amt - (float) $i->hif),
            ];
        };

        $now = $sum($today, $tomorrow);
        $prev = $sum($yesterday, $today);
        $month = $sum($monthStart, $tomorrow);
        $workedDays = DB::table('chits')->whereNull('deleted_at')->where('user_id', $user->id)->where('issued_date', '>=', $monthStart)->where('issued_date', '<', $tomorrow)
            ->selectRaw('COUNT(DISTINCT CAST(issued_date AS DATE)) d')->value('d');

        $days = self::lastDays(7);
        $start = Carbon::parse(array_key_first($days));
        $chitDaily = $chit($start, $tomorrow)->selectRaw('CAST(issued_date AS DATE) d, COUNT(*) n')->groupBy('d')->pluck('n', 'd');
        $invDaily = $inv($start, $tomorrow)->selectRaw('CAST(created_at AS DATE) d, COUNT(*) n')->groupBy('d')->pluck('n', 'd');

        $departments = $chit($today, $tomorrow)
            ->join('departments', 'departments.id', '=', 'chits.department_id')
            ->selectRaw('departments.name, COUNT(*) n')->groupBy('departments.name')->orderByDesc('n')->limit(6)->get();

        $hours = array_fill(8, 15, 0); // 08:00 - 22:00
        foreach ($chit($today, $tomorrow)->selectRaw('EXTRACT(HOUR FROM issued_date)::int h, COUNT(*) n')->groupBy('h')->get() as $row) {
            if (isset($hours[(int) $row->h])) {
                $hours[(int) $row->h] = (int) $row->n;
            }
        }

        $registered = DB::table('patients')->whereNull('deleted_at')->where('user_id', $user->id)->where('created_at', '>=', $today)->where('created_at', '<', $tomorrow)->count();
        $last = $chit($today, $tomorrow)->max('issued_date');

        return [
            'now' => $now, 'prev' => $prev, 'month' => $month,
            'month_daily_avg' => $workedDays ? (int) round($month['chits'] / $workedDays) : 0,
            'worked_days' => (int) $workedDays,
            'registered' => $registered,
            'last_chit_at' => $last ? Carbon::parse($last) : null,
            'week_labels' => array_values(array_map(fn (Carbon $d) => $d->format('D d'), $days)),
            'week_chits' => array_values(array_map(fn ($k) => (int) ($chitDaily[$k] ?? 0), array_keys($days))),
            'week_invoices' => array_values(array_map(fn ($k) => (int) ($invDaily[$k] ?? 0), array_keys($days))),
            'departments' => $departments,
            'hours' => array_values($hours),
            'hour_labels' => array_map(fn ($h) => sprintf('%02d', $h), array_keys($hours)),
            'recent_chits' => Chit::where('user_id', $user->id)->with(['patient:id,title,first_name,last_name', 'department:id,name'])
                ->where('issued_date', '>=', $today)->where('issued_date', '<', $tomorrow)->latest('issued_date')->limit(8)->get(),
        ];
    }

    /** Every date (Y-m-d) from $a to $b inclusive. */
    private static function lastDaysBetween(Carbon $a, Carbon $b): array
    {
        $out = [];
        for ($d = $a->copy(); $d->lte($b); $d->addDay()) {
            $out[] = $d->toDateString();
        }

        return $out;
    }

    /**
     * Cumulative total revenue (government + HIF) and chits of the period against the previous period, bucket by bucket:
     * hour by hour for a single day, day by day otherwise.
     *
     * @return array{labels: array, current: array, previous: array, unit: string, chits_current: array, chits_previous: array}
     */
    private static function race(callable $chit, callable $inv, Carbon $from, Carbon $end, Carbon $prevFrom, Carbon $prevEnd, int $length): array
    {
        $hourly = $length === 1;
        $bucketChit = $hourly ? 'EXTRACT(HOUR FROM chits.issued_date)::int' : 'CAST(chits.issued_date AS DATE)';
        $bucketInv = $hourly ? 'EXTRACT(HOUR FROM invoices.created_at)::int' : 'CAST(invoices.created_at AS DATE)';
        $buckets = $hourly ? range(7, 22) : null;

        $series = function (Carbon $a, Carbon $b) use ($chit, $inv, $bucketChit, $bucketInv) {
            $c = $chit($a, $b)->selectRaw("{$bucketChit} k, COUNT(*) n, SUM(chits.amount) g")->groupBy('k')->get()->keyBy('k');
            $i = $inv($a, $b)->selectRaw("{$bucketInv} k, SUM(invoices.total_amount) g")->groupBy('k')->get()->keyBy('k');

            return [$c, $i];
        };
        [$cNow, $iNow] = $series($from, $end);
        [$cPrev, $iPrev] = $series($prevFrom, $prevEnd);

        $labels = [];
        $current = [];
        $previous = [];
        $chitsNow = [];
        $chitsPrev = [];
        $sumNow = $sumPrev = $nNow = $nPrev = 0;
        $count = $hourly ? count($buckets) : $length;
        for ($n = 0; $n < $count; $n++) {
            $kNow = $hourly ? $buckets[$n] : $from->copy()->addDays($n)->toDateString();
            $kPrev = $hourly ? $buckets[$n] : $prevFrom->copy()->addDays($n)->toDateString();
            $sumNow += (float) ($cNow[$kNow]->g ?? 0) + (float) ($iNow[$kNow]->g ?? 0);
            $sumPrev += (float) ($cPrev[$kPrev]->g ?? 0) + (float) ($iPrev[$kPrev]->g ?? 0);
            $nNow += (int) ($cNow[$kNow]->n ?? 0);
            $nPrev += (int) ($cPrev[$kPrev]->n ?? 0);
            $labels[] = $hourly ? sprintf('%02d:00', $kNow) : 'Day '.($n + 1);
            $current[] = round($sumNow);
            $previous[] = round($sumPrev);
            $chitsNow[] = $nNow;
            $chitsPrev[] = $nPrev;
        }

        return ['labels' => $labels, 'current' => $current, 'previous' => $previous, 'chits_current' => $chitsNow, 'chits_previous' => $chitsPrev, 'unit' => $hourly ? 'hour' : 'day'];
    }

    /** @return array<string, Carbon> keyed by Y-m-d, oldest first */
    private static function lastDays(int $count): array
    {
        $days = [];
        for ($i = $count - 1; $i >= 0; $i--) {
            $d = Carbon::today()->subDays($i);
            $days[$d->toDateString()] = $d;
        }

        return $days;
    }
}
