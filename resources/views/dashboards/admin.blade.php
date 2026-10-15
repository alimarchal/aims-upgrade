{{--
    Management dashboard (Administrator and Super-Admin).
    Everything below the hero follows the chosen period (?from=&to=, default today) and is compared with the
    period of the same length just before it, so any day, month or year of back data can be reviewed.
--}}
@php
    use Carbon\Carbon;
    $user = auth()->user();
    $change = fn (float $now, float $before, string $suffix = 'vs previous period') => \App\Support\DashboardFormat::change($now, $before, $suffix);
    $rs = fn ($n) => number_format((float) $n);
    $p = $data['period'];
    $pv = $data['previous'];
    $from = Carbon::parse($data['from']);
    $to = Carbon::parse($data['to']);
    $today = Carbon::today();
    $isToday = $from->isSameDay($today) && $to->isSameDay($today);
    $vs = $data['live'] ? 'vs same time before' : 'vs previous period';
    $label = $from->isSameDay($to) ? ($isToday ? 'Today' : $from->format('l, d M Y')) : $from->format('d M Y').' – '.$to->format('d M Y');
    $prevLabel = Carbon::parse($data['prev_from'])->format('d M').($data['length'] > 1 ? ' – '.Carbon::parse($data['prev_to'])->format('d M Y') : ' '.Carbon::parse($data['prev_to'])->format('Y'));

    $presets = [
        'Today' => [$today, $today],
        'Yesterday' => [$today->copy()->subDay(), $today->copy()->subDay()],
        'Last 7 days' => [$today->copy()->subDays(6), $today],
        'Last 30 days' => [$today->copy()->subDays(29), $today],
        'This month' => [$today->copy()->startOfMonth(), $today],
        'Last month' => [$today->copy()->subMonthNoOverflow()->startOfMonth(), $today->copy()->subMonthNoOverflow()->endOfMonth()->startOfDay()],
        'This year' => [$today->copy()->startOfYear(), $today],
        'Last 12 months' => [$today->copy()->subMonthsNoOverflow(11)->startOfMonth(), $today],
    ];
    $activePreset = collect($presets)->search(fn ($r) => $r[0]->isSameDay($from) && $r[1]->isSameDay($to));

    $entShare = $p['chits'] ? round($p['entitled'] / $p['chits'] * 100) : 0;
    $prevEntShare = $pv['chits'] ? round($pv['entitled'] / $pv['chits'] * 100) : 0;
    $hifShare = $p['collected'] ? round($p['hif'] / $p['collected'] * 100) : 0;
    $perDay = $data['length'] > 0 ? $p['chits'] / $data['length'] : 0;
    $govtPerChit = $p['chits'] ? $p['govt'] / $p['chits'] : 0;
    $prevGovtPerChit = $pv['chits'] ? $pv['govt'] / $pv['chits'] : 0;
    $perChit = $p['chits'] ? $p['collected'] / $p['chits'] : 0;
    $prevPerChit = $pv['chits'] ? $pv['collected'] / $pv['chits'] : 0;

    $range = ['start_date' => $data['from'], 'end_date' => $data['to']];
    $chitsLink = fn (array $extra = []) => $isToday ? route('chits.issued-today', $extra) : route('chits.issued', $range + $extra);
    $x = $data['extras'];
    // [label, shown value, now, before, colour, icon, sparkline key, hint, link]
    $kpiGroups = [
        ['Key numbers', '#4f46e5', 'the four figures to watch first', [
            ['Chits issued', $rs($p['chits']), $p['chits'], $pv['chits'], '#4f46e5', 'chit', 'chits', $rs(round($perDay)).' per day', $chitsLink()],
            ['Total revenue', 'Rs '.$rs($p['collected']), $p['collected'], $pv['collected'], '#7c3aed', 'money', 'total', 'government + HIF together', null],
            ['Government revenue', 'Rs '.$rs($p['govt']), $p['govt'], $pv['govt'], '#16a34a', 'money', 'govt', 'what the hospital keeps', route('reports.opd.reportDailyIPD', $range + ['show_hif' => 'no'])],
            ['HIF revenue', 'Rs '.$rs($p['hif']), $p['hif'], $pv['hif'], '#f59e0b', 'gift', 'hif', $hifShare.'% of total revenue', route('reports.opd.reportDailyIPD', $range)],
        ]],
        ['Patient flow', '#0891b2', 'who came and how they were billed', [
            ['Entitled (free)', $rs($p['entitled']), $p['entitled'], $pv['entitled'], '#0d9488', 'shield', 'entitled', $entShare.'% of chits', $chitsLink(['filter' => ['government_non_gov' => 1]])],
            ['Non-entitled (paid)', $rs($p['non_entitled']), $p['non_entitled'], $pv['non_entitled'], '#2563eb', 'users', 'paid', (100 - $entShare).'% of chits', $chitsLink(['filter' => ['government_non_gov' => 0]])],
            ['Invoices', $rs($p['invoices']), $p['invoices'], $pv['invoices'], '#0891b2', 'invoice', 'invoices', 'lab & services', $isToday ? route('invoice.issued-today') : route('invoice.issued', $range)],
            ['New patients', $rs($p['patients']), $p['patients'], $pv['patients'], '#7c3aed', 'users', 'patients', 'registered in the period', null],
        ]],
        ['Care & efficiency', '#e11d48', 'wards, free care and earning per chit', [
            ['Admissions', $rs($p['admissions']), $p['admissions'], $pv['admissions'], '#e11d48', 'bed', 'admissions', 'IPD admissions', null],
            ['Free care given', 'Rs '.$rs($x['free_care']), $x['free_care'], null, '#0d9488', 'gift', null, 'value of treatment to entitled patients', null],
            ['Specialist clinics', $p['chits'] ? round($x['specialist_chits'] / $p['chits'] * 100).'%' : '0%', $x['specialist_chits'], null, '#4f46e5', 'chit', null, $rs($x['specialist_chits']).' chits · Rs '.$rs($x['specialist_govt']), null],
            ['Revenue per chit', 'Rs '.$rs(round($perChit)), $perChit, $prevPerChit, '#0d9488', 'money', null, 'government Rs '.$rs(round($govtPerChit)).' + HIF', null],
        ]],
    ];

    $departments = collect($data['departments']);
    $govtDepartments = collect($data['govt_departments']);
    $categories = collect($data['categories']);
    $services = collect($data['services']);
    $topDept = $departments->first();
    $deptTotal = max(1, (int) $departments->sum('n'));
    $busiestDay = collect($data['weekday'])->sortDesc()->keys()->first();
    $quietDay = collect($data['weekday'])->filter(fn ($v) => $v > 0)->sort()->keys()->first();
    $proj = $data['projection'];
    $hour = (int) now()->format('H');
    $greeting = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
    $firstName = \Illuminate\Support\Str::of($user->name)->replaceMatches('/^(mr|mrs|ms|miss|dr)\.?\s+/i', '')->trim()->before(' ');

    $insights = array_values(array_filter([
        $pv['govt'] > 0 ? "Government revenue is Rs ".$rs($p['govt'])." — ".($p['govt'] >= $pv['govt'] ? 'up' : 'down')." ".abs(round(($p['govt'] - $pv['govt']) / $pv['govt'] * 100))."% against the previous period".($data['live'] ? ' at the same time of day' : '')." (Rs ".$rs($pv['govt']).")." : null,
        $pv['chits'] > 0 ? $rs($p['chits'])." chits ".($p['chits'] >= $pv['chits'] ? 'up' : 'down')." ".abs(round(($p['chits'] - $pv['chits']) / $pv['chits'] * 100))."% (".$rs(round($perDay))." a day on average)." : null,
        $govtPerChit > 0 && $prevGovtPerChit > 0 ? "Each chit brings in Rs ".$rs(round($govtPerChit))." for the government share, ".($govtPerChit >= $prevGovtPerChit ? 'more' : 'less')." than before (Rs ".$rs(round($prevGovtPerChit)).")." : null,
        "Busiest hour is ".sprintf('%02d:00', $data['peak_hour']).", busiest weekday ".$busiestDay.", quietest ".$quietDay." — plan staffing around them.",
        $topDept ? $topDept['name']." has ".round($topDept['n'] / $deptTotal * 100)."% of the chits among the top departments." : null,
        $govtDepartments->first() ? "Most entitled patients come from ".$govtDepartments->first()['name']." (".$rs($govtDepartments->first()['n'])." chits)." : null,
    ]));
@endphp

<x-app-layout>
    @include('dashboards.style')

    <div class="ak-page" style="padding-top: 24px">
        <x-success-message />

        {{-- Slim header: title, period, and a Filters button that opens the period picker ----------------- --}}
        <header class="db-top" x-data="{ filters: {{ request()->hasAny(['from', 'to']) && ! $activePreset ? 'true' : 'false' }} }">
            <div class="db-top-row">
                <div class="db-top-title">
                    <h1>{{ $greeting }}, {{ $firstName }}</h1>
                    <p>
                        <span class="db-period"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" /></svg>{{ $label }}</span>
                        @if ($data['last_chit_at'])<span class="db-live"><i></i> last chit {{ Carbon::parse($data['last_chit_at'])->diffForHumans() }}</span>@endif
                    </p>
                </div>
                <div class="db-top-actions">
                    <button type="button" class="db-btn" @click="filters = ! filters" :aria-expanded="filters" aria-controls="db-filter-panel">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 0 1-.659 1.591l-5.432 5.432a2.25 2.25 0 0 0-.659 1.591v2.927a2.25 2.25 0 0 1-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 0 0-.659-1.591L3.659 7.409A2.25 2.25 0 0 1 3 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0 1 12 3Z" /></svg>
                        Filters<span class="db-dot" x-show="{{ $activePreset === 'Today' || $activePreset === false ? 'false' : 'true' }}" x-cloak></span>
                    </button>
                </div>
            </div>
            <form method="GET" action="{{ route('dashboard') }}" id="db-filter-panel" class="db-filter-panel" x-show="filters" x-cloak x-transition.opacity.duration.200ms aria-label="Choose the period">
                <div class="db-filter-label">Quick periods</div>
                <div class="db-chips">
                    @foreach ($presets as $name => [$a, $b])
                        <a class="db-chip {{ $activePreset === $name ? 'is-on' : '' }}" href="{{ route('dashboard', ['from' => $a->toDateString(), 'to' => $b->toDateString()]) }}">{{ $name }}</a>
                    @endforeach
                </div>
                <div class="db-filter-label">Custom dates</div>
                <div class="db-range">
                    <label for="from" class="sr-only">From</label><input id="from" type="date" name="from" value="{{ $data['from'] }}" max="{{ $today->toDateString() }}">
                    <span>to</span>
                    <label for="to" class="sr-only">To</label><input id="to" type="date" name="to" value="{{ $data['to'] }}" max="{{ $today->toDateString() }}">
                    <button type="submit" class="db-btn db-btn-primary">Show</button>
                    <a class="db-btn" href="{{ route('dashboard') }}">Reset to today</a>
                </div>
            </form>
        </header>

        {{-- Highlights: the story of the period as gauges ------------------------------------------------- --}}
        @php
            $gaugeChits = $data['length'] === 1 ? ($data['usual'] > 0 ? round($p['chits'] / $data['usual'] * 100) : 0) : ($data['daily_mean'] > 0 ? round($perDay / $data['daily_mean'] * 100) : 0);
            $specialistShare = $p['chits'] ? round($x['specialist_chits'] / $p['chits'] * 100) : 0;
        @endphp
        <section class="db-card db-highlights" aria-label="Highlights">
            <div class="db-card-head"><h2>Highlights</h2><span>each ring shows one share of the period — the fuller the ring, the bigger the share</span></div>
            <div class="db-gauges">
                <figure><div id="gauge-chits"></div><figcaption><b>{{ $data['length'] === 1 ? 'Chits vs a usual '.$from->format('l') : 'Chits per day vs the 90-day average' }}</b>{{ $rs($data['length'] === 1 ? $p['chits'] : round($perDay)) }} against {{ $rs($data['length'] === 1 ? $data['usual'] : $data['daily_mean']) }}</figcaption></figure>
                <figure><div id="gauge-govt"></div><figcaption><b>Government share of revenue</b>Rs {{ $rs($p['govt']) }} of Rs {{ $rs($p['collected']) }}</figcaption></figure>
                <figure><div id="gauge-hif"></div><figcaption><b>HIF share of revenue</b>Rs {{ $rs($p['hif']) }} of Rs {{ $rs($p['collected']) }}</figcaption></figure>
                <figure><div id="gauge-entitled"></div><figcaption><b>Entitled (free) patients</b>{{ $rs($p['entitled']) }} of {{ $rs($p['chits']) }} chits</figcaption></figure>
                <figure><div id="gauge-specialist"></div><figcaption><b>Specialist clinics</b>{{ $rs($x['specialist_chits']) }} of {{ $rs($p['chits']) }} chits</figcaption></figure>
            </div>
            <details class="db-words">
                <summary>Read the highlights in words</summary>
                <ul>@foreach ($insights as $line)<li>{{ $line }}</li>@endforeach</ul>
            </details>
        </section>

        <details class="db-help">
            <summary>How to read this dashboard</summary>
            <ul>
                <li><b>Period:</b> pick Today, a month, a year or any dates at the top — every number and chart below follows it. Percent badges compare with the previous period of the same length (for a day that is still going, with yesterday up to the same time).</li>
                <li><b>Government revenue</b> is what the hospital keeps (amount minus HIF). <b>HIF</b> is the share passed on to the Health Insurance Fund. <b>Total revenue</b> is both together.</li>
                <li><b>Entitled</b> patients are government employees treated free; <b>non-entitled</b> patients pay the fee.</li>
                <li>Small lines inside the cards show the trend over the period; hover any chart for exact values, and use the icons on the trend chart to zoom or download it.</li>
            </ul>
        </details>

        {{-- KPIs, in three groups ------------------------------------------------------------------- --}}
        <h2 class="db-section"><i>1</i> At a glance <span>{{ $label }} · each % compares with {{ $prevLabel }}@if ($data['live']) up to {{ $data['compare_until'] }} — the same stretch of time, not the whole earlier period @endif</span></h2>
        @foreach ($kpiGroups as [$groupTitle, $dot, $groupHint, $cards])
            <div class="db-group" style="--dot: {{ $dot }}">{{ $groupTitle }} <span>— {{ $groupHint }}</span></div>
            <section class="db-grid db-kpis" aria-label="{{ $groupTitle }}">
                @foreach ($cards as [$name, $value, $now, $before, $tone, $icon, $spark, $hint, $link])
                    @php [$tone2, $text] = $before === null ? [null, null] : $change((float) $now, (float) $before, ''); @endphp
                    <{{ $link ? 'a href='.chr(34).$link.chr(34) : 'div' }} class="db-kpi" style="{{ \App\Support\DashboardFormat::tone($tone) }}; display:block; text-decoration:none">
                        <div class="db-kpi-top"><span class="db-kpi-label">{{ $name }}</span>@include('dashboards.icon', ['icon' => $icon])</div>
                        <div class="db-kpi-value">{{ $value }}</div>
                        <div class="db-kpi-hint">@if ($tone2)<span class="db-delta {{ $tone2 }}" title="{{ trim($text) }} {{ $vs }}">{{ trim($text) }}</span> @endif{{ $hint }}</div>
                        @if ($spark)<div class="db-spark" id="spark-{{ $spark }}"></div>@endif
                    </{{ $link ? 'a' : 'div' }}>
                @endforeach
            </section>
        @endforeach

        @php
            $x = $data['extras'];
            $peakSeconds = $x['peak_per_hour'] > 0 ? 3600 / $x['peak_per_hour'] : null;
            $peakEvery = $peakSeconds === null ? null : ($peakSeconds < 90 ? round($peakSeconds).' seconds' : round($peakSeconds / 60, 1).' minutes');
        @endphp
        <h2 class="db-section"><i>2</i> What the numbers mean <span>plain-language insights for {{ $label }}</span></h2>
        <section class="db-grid db-insights" aria-label="Insights">
            <div class="db-insight" style="{{ \App\Support\DashboardFormat::tone('#0d9488') }}">
                <b>Rs {{ $rs($x['free_care']) }}</b>
                <h3>Free care given</h3>
                <p>{{ $rs($p['entitled']) }} entitled patients were treated free. At the normal fee this care is worth Rs {{ $rs($x['free_care']) }} — money the hospital did not collect.</p>
            </div>
            <div class="db-insight" style="{{ \App\Support\DashboardFormat::tone('#4f46e5') }}">
                <b>{{ $p['chits'] ? round($x['specialist_chits'] / $p['chits'] * 100) : 0 }}%</b>
                <h3>Specialist OPD share</h3>
                <p>{{ $rs($x['specialist_chits']) }} of {{ $rs($p['chits']) }} chits went to specialist clinics and brought Rs {{ $rs($x['specialist_govt']) }} government revenue. The rest is general OPD, mostly free or low-fee.</p>
            </div>
            <div class="db-insight" style="{{ \App\Support\DashboardFormat::tone('#e11d48') }}">
                <b>{{ $peakEvery ? 'every '.\Illuminate\Support\Str::of($peakEvery)->replace(' seconds', ' sec')->replace(' minutes', ' min') : '—' }}</b>
                <h3>Peak load</h3>
                <p>At {{ sprintf('%02d:00', $data['peak_hour']) }} the hospital issues about {{ $rs(round($x['peak_per_hour'])) }} chits an hour on a normal day{{ $peakEvery ? ' — one new chit every '.$peakEvery : '' }}. Keep enough counters open then.</p>
            </div>
            <div class="db-insight" style="{{ \App\Support\DashboardFormat::tone('#2563eb') }}">
                <b>{{ $x['busiest_day'] ? $rs($x['busiest_day']['n']) : '—' }}</b>
                <h3>Busiest day</h3>
                <p>@if ($x['busiest_day']){{ $x['busiest_day']['date'] }} was the busiest with {{ $rs($x['busiest_day']['n']) }} chits.@if ($x['quietest_day']) The quietest was {{ $x['quietest_day']['date'] }} with {{ $rs($x['quietest_day']['n']) }}.@endif @else No chits in this period.@endif</p>
            </div>
            <div class="db-insight" style="{{ \App\Support\DashboardFormat::tone(($x['deleted_chits'] + $x['deleted_invoices'] + $x['returns']) > 0 ? '#dc2626' : '#16a34a') }}">
                <b>{{ $rs($x['deleted_chits'] + $x['deleted_invoices'] + $x['returns']) }}</b>
                <h3>Deletions &amp; returns</h3>
                <p>{{ $rs($x['deleted_chits']) }} chits and {{ $rs($x['deleted_invoices']) }} invoices were deleted (Rs {{ $rs($x['deleted_chits_amount'] + $x['deleted_invoices_amount']) }}) and {{ $rs($x['returns']) }} tests returned (Rs {{ $rs($x['returns_amount']) }}). This should stay close to zero — a jump deserves a look.</p>
            </div>
            @if ($x['ssp'])
                <div class="db-insight" style="{{ \App\Support\DashboardFormat::tone('#7c3aed') }}">
                    <b>{{ $rs($x['ssp']['n']) }}</b>
                    <h3>Sehat Sahulat patients</h3>
                    <p>Patients treated under the Sehat Sahulat Program; the fees to claim from the insurer are Rs {{ $rs($x['ssp']['claims']) }}.</p>
                </div>
            @endif
        </section>

        {{-- 3. Trends ------------------------------------------------------------------------------- --}}
        <h2 class="db-section"><i>3</i> Trends <span>how patients and revenue move over time</span></h2>
        <section class="db-grid db-two is-even">
            <div class="db-card">
                <div class="db-card-head"><h2>{{ $data['monthly'] ? 'Monthly' : 'Daily' }} chits</h2><span>@if ($data['widened']) last {{ $data['chart_length'] }} days up to {{ $to->format('d M') }} · @endif bars and {{ $data['window'] }}-{{ $data['monthly'] ? 'month' : 'day' }} average</span></div>
                <div class="db-card-body"><div id="trend-chart" style="min-height: 320px"></div>
                    <p class="db-note">Bars: chits issued each {{ $data['monthly'] ? 'month' : 'day' }}. Dark line: the moving average — it smooths the ups and downs so the direction is clear.</p></div>
            </div>
            <div class="db-card">
                <div class="db-card-head"><h2>{{ $data['monthly'] ? 'Monthly' : 'Daily' }} revenue</h2><span>government + HIF, stacked</span></div>
                <div class="db-card-body"><div id="revenue-chart" style="min-height: 320px"></div>
                    <p class="db-note">Each bar is the day's total revenue: <b style="color:#16a34a">green</b> is the government share the hospital keeps, <b style="color:#d97706">amber</b> is HIF. Hover a bar for both amounts and the total.</p></div>
            </div>
        </section>
        <section class="db-grid db-two is-even">
            <div class="db-card">
                <div class="db-card-head"><h2>Race against the previous period</h2><span>cumulative total revenue, {{ $data['race']['unit'] === 'hour' ? 'hour by hour' : 'day by day' }}</span></div>
                <div class="db-card-body"><div id="race-chart" style="min-height: 320px"></div>
                    <p class="db-note">Both lines add up the revenue as time passes. If the green line stays above the grey one, this period is earning faster than the one before it; where they cross, the lead changed.</p></div>
            </div>
            <div class="db-card">
                <div class="db-card-head"><h2>Entitled vs paid patients</h2><span>chits per {{ $data['monthly'] ? 'month' : 'day' }}</span></div>
                <div class="db-card-body"><div id="mix-chart" style="min-height: 320px"></div>
                    <p class="db-note">Teal = entitled (free) chits, blue = paid chits, stacked to the day's total. A growing teal part means more free care and less income per chit.</p></div>
            </div>
        </section>
        <section class="db-grid db-two is-even">
            <div class="db-card">
                <div class="db-card-head"><h2>Control chart</h2><span>daily chits, 90 days up to {{ $to->format('d M') }}</span></div>
                <div class="db-card-body"><div id="control-chart" style="min-height: 300px"></div>
                    <p class="db-note">The dotted lines mark the normal range (average ± 2 standard deviations). A day outside it is unusual — red dots are unusually busy or quiet days worth asking about.</p></div>
            </div>
            <div class="db-card">
                <div class="db-card-head"><h2>Government vs HIF share</h2><span>how each rupee is divided</span></div>
                <div class="db-card-body"><div id="split-chart" style="min-height: 300px"></div>
                    <p class="db-note">Each bar is 100%: green is the government share, amber is HIF. A bigger green part means more of each rupee stays with the hospital.</p></div>
            </div>
        </section>

        {{-- 4. Revenue ---------------------------------------------------------------------------- --}}
        <h2 class="db-section"><i>4</i> Revenue <span>government + HIF, over the year and by source</span></h2>
        <section class="db-grid db-two is-even">
            <div class="db-card">
                <div class="db-card-head"><h2>Last 12 months</h2><span>revenue (stacked bars) and chits (line)</span></div>
                <div class="db-card-body"><div id="months-chart" style="min-height: 250px"></div><div id="months-chits" style="min-height: 190px"></div>
                    <p class="db-note">A year at a glance — green + amber bars are each month's government and HIF revenue; the blue line underneath is the number of chits. Use it to spot seasons and growth.</p></div>
            </div>
            <div class="db-card">
                <div class="db-card-head"><h2>{{ $data['yoy']['year'] }} vs {{ $data['yoy']['year'] - 1 }}</h2><span>total revenue by month (year on year)</span></div>
                <div class="db-card-body"><div id="yoy-chart" style="min-height: 300px"></div>
                    <p class="db-note">Same month, two years: the solid line is this year, the dashed grey line last year. It shows real growth without the seasonal ups and downs.</p></div>
            </div>
        </section>
        <section class="db-grid db-two is-even">
            <div class="db-card">
                <div class="db-card-head"><h2>Which services earn the money</h2><span>Pareto: top 10 services, government + HIF</span></div>
                <div class="db-card-body"><div id="services-chart" style="min-height: 250px"></div><div id="services-share" style="min-height: 240px"></div>
                    <p class="db-note">Bars: revenue of each service (green government, amber HIF). Red line below: the running share of the total. When it passes 80% after only a few bars, a small number of services carries most of the income.</p></div>
            </div>
            <div class="db-card">
                <div class="db-card-head"><h2>Department revenue</h2><span>government + HIF per department</span></div>
                <div class="db-card-body"><div id="dept-revenue" style="min-height: 320px"></div>
                    <p class="db-note">Revenue each department earned. General OPD departments are mostly paid by HIF (amber) with little or no government share (green).</p></div>
            </div>
        </section>

        {{-- 5. Busy times ------------------------------------------------------------------------- --}}
        <h2 class="db-section"><i>5</i> When it is busy <span>staffing guide</span></h2>
        <section class="db-grid db-two">
            <div class="db-card">
                <div class="db-card-head"><h2>Weekday × hour heat map</h2><span>chits, 90 days up to {{ $to->format('d M Y') }} · darker = busier</span></div>
                <div class="db-card-body"><div id="heat-chart" style="min-height: 330px"></div>
                    <p class="db-note">Darker squares are busier. Read across a row for one weekday and down a column for one hour — the darkest block is when the hospital is under most pressure.</p></div>
            </div>
            <div class="db-grid" style="margin-top:0">
                <div class="db-card">
                    <div class="db-card-head"><h2>Busiest hours</h2><span>period</span></div>
                    <div class="db-card-body"><div id="hours-chart" style="min-height: 150px"></div>
                        <p class="db-note">Chits issued in each hour; the dark bar is the busiest.</p></div>
                </div>
                <div class="db-card">
                    <div class="db-card-head"><h2>Busiest weekdays</h2><span>average chits per day</span></div>
                    <div class="db-card-body"><div id="weekday-chart" style="min-height: 150px"></div>
                        <p class="db-note">Plan leave and extra staff around the tallest bars.</p></div>
                </div>
            </div>
        </section>
        <section class="db-grid db-two is-even">
            <div class="db-card">
                <div class="db-card-head"><h2>How much a normal day varies</h2><span>box plot per weekday, 90 days</span></div>
                <div class="db-card-body"><div id="box-chart" style="min-height: 280px"></div>
                    <p class="db-note">Each box covers the middle half of the days for that weekday; the line inside is the median and the whiskers are the quietest and busiest day. Tall boxes mean an unpredictable weekday.</p></div>
            </div>
            <div class="db-card">
                <div class="db-card-head"><h2>Day of the month</h2><span>average chits per calendar day, last 12 months</span></div>
                <div class="db-card-body"><div id="dom-chart" style="min-height: 280px"></div>
                    <p class="db-note">Shows which part of the month is busy — for example the first days after salaries are paid. Day 31 is rarer, so its bar is lower.</p></div>
            </div>
        </section>

        {{-- 6. Departments & services --------------------------------------------------------------- --}}
        <h2 class="db-section"><i>6</i> Departments &amp; services <span>who uses what</span></h2>
        <section class="db-grid db-two is-even">
            <div class="db-card">
                <div class="db-card-head"><h2>OPD by department</h2><span>share of chits</span></div>
                <div class="db-card-body"><div id="dept-donut" style="min-height: 320px"></div>
                    <p class="db-note">Slices are departments; a bigger slice means more chits. The number in the middle is the total chits of the top departments.</p></div>
            </div>
            <div class="db-card">
                <div class="db-card-head"><h2>Specialist vs general OPD</h2><span>chits</span></div>
                <div class="db-card-body"><div id="specialist-chart" style="min-height: 320px"></div>
                    <p class="db-note">Specialist clinics charge a fee; general OPD is mostly free or HIF-funded, so it brings many chits but little government revenue.</p></div>
            </div>
        </section>
        <section class="db-card" style="margin-top:14px">
            <div class="db-card-head"><h2>Laboratory &amp; service categories</h2><span>revenue (government + HIF) and number of tests</span></div>
            <div class="db-card-body"><div id="categories-chart" style="min-height: 260px"></div><div id="categories-tests" style="min-height: 150px"></div>
                <p class="db-note">Top bars: revenue each category earned (green government, amber HIF). Red bars below: how many tests were done in that category.</p></div>
        </section>
        <section class="db-grid db-three">
            <div class="db-card">
                <div class="db-card-head"><h2>Operation theatre</h2><span>procedures</span></div>
                <div class="db-card-body"><div id="ot-chart" style="min-height: 200px"></div><p class="db-note">Theatre procedures done in the period, by type.@if (collect($data['operation_theatre'])->sum('n') === 0) <b>None were recorded in this period.</b>@endif</p></div>
            </div>
            <div class="db-card">
                <div class="db-card-head"><h2>Radiology</h2><span>investigations</span></div>
                <div class="db-card-body"><div id="radiology-chart" style="min-height: 200px"></div><p class="db-note">Radiology investigations done in the period, by type.@if (collect($data['radiology'])->sum('n') === 0) <b>None were recorded in this period.</b>@endif</p></div>
            </div>
            <div class="db-card">
                <div class="db-card-head"><h2>Entitled patients by employer</h2><span>government departments</span></div>
                <div class="db-card-body"><div id="govt-chart" style="min-height: 200px"></div><p class="db-note">Government employers whose staff used the hospital most — useful when talking to those departments.</p></div>
            </div>
        </section>

        {{-- 7. Patients & admissions ----------------------------------------------------------------- --}}
        <h2 class="db-section"><i>7</i> Patients &amp; admissions <span>who the patients are</span></h2>
        <section class="db-grid db-three">
            <div class="db-card">
                <div class="db-card-head"><h2>Gender</h2><span>{{ $rs($p['patients']) }} new patients</span></div>
                <div class="db-card-body">@if ($p['patients'])<div id="gender-chart" style="min-height: 250px"></div><p class="db-note">Share of new patients who are male or female.</p>@else<div class="db-empty">No new patients in this period.</div>@endif</div>
            </div>
            <div class="db-card">
                <div class="db-card-head"><h2>Age &amp; gender pyramid</h2><span>new patients</span></div>
                <div class="db-card-body">@if ($p['patients'])<div id="pyramid-chart" style="min-height: 250px"></div><p class="db-note">Men on the left, women on the right, by age group — shows who the hospital mostly serves.</p>@else<div class="db-empty">No new patients in this period.</div>@endif</div>
            </div>
            <div class="db-card">
                <div class="db-card-head"><h2>Admissions</h2><span>{{ $data['widened'] ? 'last '.$data['chart_length'].' days' : ($data['monthly'] ? 'per month' : 'per day') }} · {{ $rs($p['admissions']) }} in the period</span></div>
                <div class="db-card-body"><div id="admissions-chart" style="min-height: 250px"></div><p class="db-note">Patients admitted over time. Peaks show busy days for the wards.</p></div>
            </div>
        </section>
        <section class="db-grid db-two is-even">
            <div class="db-card">
                <div class="db-card-head"><h2>Admissions by ward</h2><span>where admitted patients went</span></div>
                <div class="db-card-body">
                    @if (empty($data['wards']))<div class="db-empty">No admissions in this period.</div>@else<div id="wards-chart" style="min-height: 240px"></div>
                    <p class="db-note">Each bar is the number of patients admitted to that ward or unit in the period.</p>@endif
                </div>
            </div>
            <div class="db-card">
                <div class="db-card-head"><h2>Month projection</h2><span>{{ now()->format('F Y') }} at the current pace</span></div>
                <div class="db-proj">
                    <div><small>Chits so far</small><b>{{ $rs($proj['mtd_chits']) }}</b></div>
                    <div><small>Projected for the month</small><b style="color:#4f46e5">{{ $rs($proj['chits']) }}</b></div>
                    <div><small>Government revenue so far</small><b>Rs {{ $rs($proj['mtd_govt']) }}</b></div>
                    <div><small>Projected for the month</small><b style="color:#16a34a">Rs {{ $rs($proj['govt']) }}</b></div>
                </div>
                <div class="db-card-body" style="padding-top:0"><div id="projection-chart" style="min-height: 110px"></div>
                    <p class="db-note">Day {{ $proj['elapsed'] }} of {{ $proj['days'] }}. A straight-line estimate — weekends and holidays move the real figure.</p></div>
            </div>
        </section>

        {{-- 8. People & watch list ------------------------------------------------------------------- --}}
        <h2 class="db-section"><i>8</i> People &amp; watch list <span>team results and days worth a look</span></h2>
        <section class="db-grid db-two">
            <div class="db-card">
                <div class="db-card-head"><h2>Team performance</h2><span>government revenue collected in the period</span></div>
                <div class="db-card-body">
                    @if (empty($data['team']))
                        <div class="db-empty">No activity in this period.</div>
                    @else
                        <div style="overflow-x:auto">
                            <table class="db-table">
                                <thead><tr><th>User</th><th class="r">Chits</th><th class="r">Invoices</th><th class="r">Revenue</th><th style="width:22%">Share</th></tr></thead>
                                <tbody>
                                    @foreach ($data['team'] as $row)
                                        <tr>
                                            <td><b>{{ $row['name'] }}</b></td>
                                            <td class="r">{{ $rs($row['chits']) }}</td><td class="r">{{ $rs($row['invoices']) }}</td>
                                            <td class="r"><b>Rs {{ $rs($row['govt']) }}</b></td>
                                            <td><div class="db-bar"><span style="width: {{ min(100, $row['share']) }}%"></span></div><small class="ak-muted">{{ $row['share'] }}%</small></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
            <div class="db-card">
                <div class="db-card-head"><h2>Unusual days</h2><span>beyond 2 standard deviations of the {{ $rs($data['daily_mean']) }}/day average</span></div>
                <div class="db-card-body">
                    @if (empty($data['anomalies']))
                        <div class="db-empty">Nothing unusual — every recent day was within the normal range.</div>
                    @else
                        <table class="db-table">
                            <thead><tr><th>Day</th><th class="r">Chits</th><th class="r">z-score</th><th></th></tr></thead>
                            <tbody>
                                @foreach ($data['anomalies'] as $a)
                                    <tr><td><b>{{ $a['date'] }}</b></td><td class="r">{{ $rs($a['chits']) }}</td><td class="r">{{ $a['z'] > 0 ? '+' : '' }}{{ $a['z'] }}</td>
                                        <td><span class="db-pill {{ $a['kind'] }}">{{ $a['kind'] === 'busy' ? 'unusually busy' : 'unusually quiet' }}</span></td></tr>
                                @endforeach
                            </tbody>
                        </table>
                        <p class="db-note">z-score = how many standard deviations a day is from the average; above +2 or below −2 is unusual.</p>
                    @endif
                </div>
            </div>
        </section>

        <h2 class="db-section"><i>9</i> History &amp; reports <span>open the full lists for {{ $label }}</span></h2>
        <nav class="db-links" aria-label="History and reports">
            @can('view chits') <a href="{{ route('chits.issued', $range) }}" class="db-link">@include('dashboards.icon', ['icon' => 'chit', 'plain' => true])Chits history</a> @endcan
            @can('view invoices') <a href="{{ route('invoice.issued', $range) }}" class="db-link">@include('dashboards.icon', ['icon' => 'invoice', 'plain' => true])Invoices history</a> @endcan
            @can('view daily reports') <a href="{{ route('reports.opd.reportDailyIPD', ['start_date' => $data['from'], 'end_date' => $data['to'], 'show_hif' => 'no']) }}" class="db-link">@include('dashboards.icon', ['icon' => 'invoice', 'plain' => true])Daily revenue</a> @endcan
            @can('view opd reports') <a href="{{ route('reports.opd.user-wise', ['start_date' => $data['from'], 'end_date' => $data['to']]) }}" class="db-link">@include('dashboards.icon', ['icon' => 'users', 'plain' => true])User-wise OPD</a> @endcan
            @can('view opd reports') <a href="{{ route('reports.opd.specialist-fees', ['start_date' => $data['from'], 'end_date' => $data['to']]) }}" class="db-link">@include('dashboards.icon', ['icon' => 'shield', 'plain' => true])Specialist fees</a> @endcan
            @can('view reports') <a href="{{ route('reports.index') }}" class="db-link">@include('dashboards.icon', ['icon' => 'chit', 'plain' => true])All reports</a> @endcan
        </nav>
    </div>

    @push('scripts')
        <script src="{{ url('js/apexcharts.js') }}"></script>
        <script>
            const shadow = { enabled: true, top: 3, left: 0, blur: 5, opacity: 0.16, color: '#0f172a' };
            const base = { toolbar: { show: false }, fontFamily: 'inherit', animations: { enabled: true, speed: 320, animateGradually: { enabled: false }, dynamicAnimation: { enabled: false } }, foreColor: '#475569' };
            const lineShadow = { dropShadow: shadow }; // a soft lift for the main lines only (shadows on every bar make the page heavy)
            const xlabels = { rotate: 0, hideOverlappingLabels: true, style: { fontSize: '11px', fontWeight: 500 } };
            const grid = { borderColor: '#e2e8f0', strokeDashArray: 4 };
            const int = (v) => Math.round(v).toLocaleString();
            const rupees = (v) => 'Rs ' + Math.round(v).toLocaleString();
            const k = (v) => (Math.abs(v) >= 1000 ? (v / 1000).toFixed(v % 1000 === 0 ? 0 : 1) + 'k' : Math.round(v));
            const palette = ['#4f46e5', '#0891b2', '#16a34a', '#f59e0b', '#e11d48', '#7c3aed', '#0d9488', '#2563eb', '#db2777', '#65a30d', '#ea580c', '#64748b'];
            const series = @json($data['series']);
            const labels = @json($data['labels']);
            const tick = Math.min(labels.length, 7);
            // Charts are drawn only when they are about to scroll into view, one per animation frame, so the page
            // opens quickly and scrolling stays smooth (43 charts at once cost about 1.7 s of main-thread time).
            const queue = []; let pumping = false;
            const pump = () => { const job = queue.shift(); if (!job) { pumping = false; return; } pumping = true; job(); requestAnimationFrame(() => setTimeout(pump, 0)); };
            const enqueue = (job) => { queue.push(job); if (!pumping) requestAnimationFrame(pump); };
            const pending = new Map();
            const io = new IntersectionObserver((entries) => entries.forEach((entry) => {
                if (!entry.isIntersecting) return;
                const render = pending.get(entry.target);
                if (render) { pending.delete(entry.target); io.unobserve(entry.target); enqueue(render); }
            }), { rootMargin: '600px 0px' });
            const draw = (selector, options) => {
                const el = document.querySelector(selector);
                if (!el) return;
                pending.set(el, () => new ApexCharts(el, options).render());
                io.observe(el);
            };

            // sparklines inside the KPI cards
            const total = series.govt.map((g, i) => g + series.hif[i]);
            const sparks = { chits: ['#4f46e5', series.chits], total: ['#7c3aed', total], entitled: ['#0d9488', series.entitled], paid: ['#2563eb', series.paid], invoices: ['#0891b2', series.invoices],
                govt: ['#16a34a', series.govt], hif: ['#f59e0b', series.hif], patients: ['#7c3aed', series.patients], admissions: ['#e11d48', series.admissions] };
            Object.entries(sparks).forEach(([name, [color, data]]) => draw('#spark-' + name, {
                chart: { type: 'area', height: 30, sparkline: { enabled: true }, animations: { enabled: true } }, series: [{ name, data }], colors: [color],
                stroke: { curve: 'smooth', width: 2 }, fill: { type: 'gradient', gradient: { opacityFrom: .35, opacityTo: 0 } }, tooltip: { fixed: { enabled: false }, x: { show: false }, marker: { show: false } },
            }));

            const gauge = (selector, value, color, caption) => draw(selector, {
                chart: { type: 'radialBar', height: 170, sparkline: { enabled: false }, fontFamily: 'inherit', dropShadow: { enabled: true, top: 3, blur: 5, opacity: 0.18, color } },
                series: [Math.min(100, Math.max(0, value))], colors: [color], labels: [caption],
                plotOptions: { radialBar: { hollow: { size: '58%' }, track: { background: '#e2e8f0', strokeWidth: '100%' }, dataLabels: { name: { show: false }, value: { fontSize: '24px', fontWeight: 800, offsetY: 8, color: '#0f172a', formatter: () => String(Math.round(value)) + '%' } } } },
                stroke: { lineCap: 'round' }, fill: { type: 'gradient', gradient: { shade: 'light', type: 'horizontal', gradientToColors: [color], opacityFrom: 1, opacityTo: .8 } },
            });
            gauge('#gauge-chits', {{ $gaugeChits }}, '#4f46e5', 'Chits');
            gauge('#gauge-govt', {{ $p['collected'] ? round($p['govt'] / $p['collected'] * 100) : 0 }}, '#16a34a', 'Government');
            gauge('#gauge-hif', {{ $hifShare }}, '#f59e0b', 'HIF');
            gauge('#gauge-entitled', {{ $entShare }}, '#0d9488', 'Entitled');
            gauge('#gauge-specialist', {{ $specialistShare }}, '#7c3aed', 'Specialist');

            // 3. trends -------------------------------------------------------------------------
            draw('#trend-chart', {
                chart: { ...base, ...lineShadow, type: 'line', height: 320, zoom: { enabled: true }, toolbar: { show: true, offsetY: -6, tools: { download: true, zoom: true, zoomin: true, zoomout: true, pan: false, reset: true } } },
                series: [{ name: 'Chits', type: 'column', data: series.chits }, { name: '{{ $data['window'] }}-{{ $data['monthly'] ? 'month' : 'day' }} average', type: 'line', data: @json($data['moving_average']) }],
                xaxis: { categories: labels, tickAmount: tick, labels: xlabels, axisBorder: { color: '#cbd5e1' } },
                colors: ['#a5b4fc', '#312e81'], stroke: { width: [0, 3.5], curve: 'smooth' }, markers: { size: [0, 3], strokeWidth: 2, strokeColors: '#fff', hover: { size: 7 } },
                plotOptions: { bar: { borderRadius: 3, columnWidth: '62%' } }, dataLabels: { enabled: false },
                grid: { borderColor: '#cbd5e1', strokeDashArray: 3 }, yaxis: { min: 0, title: { text: 'Chits' }, labels: { formatter: int } },
                legend: { position: 'bottom', horizontalAlign: 'center', fontWeight: 600 }, tooltip: { shared: true, intersect: false },
            });
            draw('#revenue-chart', {
                chart: { ...base, type: 'bar', height: 320, stacked: true, toolbar: { show: true, offsetY: -6, tools: { download: true, zoom: false, zoomin: false, zoomout: false, pan: false, reset: false } } },
                series: [{ name: 'Government revenue', data: series.govt }, { name: 'HIF revenue', data: series.hif }],
                xaxis: { categories: labels, tickAmount: tick, labels: xlabels }, colors: ['#16a34a', '#f59e0b'],
                plotOptions: { bar: { borderRadius: 3, columnWidth: '62%' } }, dataLabels: { enabled: false }, grid,
                yaxis: { min: 0, title: { text: 'Revenue (Rs)' }, labels: { formatter: k } }, legend: { position: 'bottom', horizontalAlign: 'center', fontWeight: 600 },
                tooltip: { shared: true, intersect: false, y: { formatter: rupees } },
            });

            const race = @json($data['race']);
            const raceLabels = race.unit === 'hour' ? race.labels.map((l) => l.slice(0, 2)) : race.labels.map((l) => l.replace('Day ', ''));
            draw('#race-chart', {
                chart: { ...base, ...lineShadow, type: 'area', height: 320 },
                series: [{ name: 'This period', data: race.current }, { name: 'Previous period', data: race.previous }],
                xaxis: { categories: raceLabels, tickAmount: Math.min(raceLabels.length, 8), labels: xlabels, title: { text: race.unit === 'hour' ? 'hour of the day' : 'day of the period' } },
                colors: ['#16a34a', '#94a3b8'], stroke: { width: [3.5, 2.5], curve: 'smooth', dashArray: [0, 5] },
                fill: { type: ['gradient', 'solid'], opacity: [1, 0], gradient: { opacityFrom: .35, opacityTo: .02 } }, dataLabels: { enabled: false },
                markers: { size: 0, hover: { size: 6 } }, grid, yaxis: { min: 0, title: { text: 'Cumulative total revenue (Rs)' }, labels: { formatter: k } },
                legend: { position: 'bottom', horizontalAlign: 'center', fontWeight: 600 }, tooltip: { shared: true, intersect: false, y: { formatter: rupees } },
            });

            draw('#mix-chart', {
                chart: { ...base, type: 'area', height: 280, stacked: true },
                series: [{ name: 'Entitled (free)', data: series.entitled }, { name: 'Paid', data: series.paid }],
                xaxis: { categories: labels, tickAmount: tick, labels: xlabels }, colors: ['#0d9488', '#2563eb'],
                stroke: { width: 2, curve: 'smooth' }, fill: { type: 'gradient', gradient: { opacityFrom: .6, opacityTo: .15 } }, dataLabels: { enabled: false }, grid,
                yaxis: { min: 0, labels: { formatter: int } }, legend: { position: 'top', horizontalAlign: 'right', fontWeight: 600 }, tooltip: { shared: true, intersect: false },
            });

            const control = @json($data['control']);
            const outside = control.values.map((v) => (v > control.upper || v < control.lower ? v : null));
            draw('#control-chart', {
                chart: { ...base, ...lineShadow, type: 'line', height: 280 },
                series: [{ name: 'Daily chits', data: control.values }, { name: 'Unusual day', data: outside }, { name: 'Average', data: control.values.map(() => control.mean) }, { name: 'Upper limit', data: control.values.map(() => control.upper) }, { name: 'Lower limit', data: control.values.map(() => control.lower) }],
                xaxis: { categories: control.labels, tickAmount: 8, labels: xlabels }, colors: ['#4f46e5', '#e11d48', '#16a34a', '#f59e0b', '#f59e0b'],
                stroke: { width: [2.5, 0, 2, 1.5, 1.5], dashArray: [0, 0, 0, 6, 6], curve: 'straight' }, markers: { size: [0, 6, 0, 0, 0], strokeColors: '#fff', strokeWidth: 2 }, dataLabels: { enabled: false }, grid,
                yaxis: { min: 0, labels: { formatter: int } }, legend: { position: 'top', horizontalAlign: 'right', fontWeight: 600 }, tooltip: { shared: false },
            });

            // 4. revenue -------------------------------------------------------------------------
            draw('#split-chart', {
                chart: { ...base, type: 'bar', height: 280, stacked: true, stackType: '100%' },
                series: [{ name: 'Government', data: series.govt }, { name: 'HIF', data: series.hif }], xaxis: { categories: labels, tickAmount: tick, labels: xlabels },
                colors: ['#16a34a', '#f59e0b'], dataLabels: { enabled: false }, grid, plotOptions: { bar: { columnWidth: '75%', borderRadius: 2 } },
                legend: { position: 'top', horizontalAlign: 'right', fontWeight: 600 }, tooltip: { y: { formatter: rupees } }, yaxis: { labels: { formatter: (v) => Math.round(v) + '%' } },
            });
            const monthLabels = @json($data['months']);
            draw('#months-chart', {
                chart: { ...base, type: 'bar', height: 250, stacked: true },
                series: [{ name: 'Government revenue', data: @json($data['govt_monthly']) }, { name: 'HIF revenue', data: @json($data['hif_monthly']) }],
                xaxis: { categories: monthLabels, labels: { show: false }, axisTicks: { show: false } }, colors: ['#16a34a', '#f59e0b'],
                plotOptions: { bar: { borderRadius: 3, columnWidth: '68%' } }, dataLabels: { enabled: false }, grid, yaxis: { min: 0, title: { text: 'Revenue (Rs)' }, labels: { formatter: k } },
                legend: { position: 'top', horizontalAlign: 'right', fontWeight: 600 }, tooltip: { shared: true, intersect: false, y: { formatter: rupees } },
            });
            draw('#months-chits', {
                chart: { ...base, ...lineShadow, type: 'line', height: 190 },
                series: [{ name: 'Chits', data: @json($data['chits_monthly']) }], xaxis: { categories: monthLabels, labels: { rotate: -45, rotateAlways: true, hideOverlappingLabels: false, style: { fontSize: '10px' } } },
                colors: ['#2563eb'], stroke: { width: 3, curve: 'smooth' }, markers: { size: 4, strokeColors: '#fff', strokeWidth: 2 }, dataLabels: { enabled: false }, grid,
                yaxis: { min: 0, title: { text: 'Chits' }, labels: { formatter: k } }, legend: { show: false }, tooltip: { y: { formatter: int } },
            });
            const yoy = @json($data['yoy']);
            draw('#yoy-chart', {
                chart: { ...base, ...lineShadow, type: 'line', height: 280 },
                series: [{ name: String(yoy.year), data: yoy.total[yoy.year] }, { name: String(yoy.year - 1), data: yoy.total[yoy.year - 1] }],
                xaxis: { categories: yoy.labels }, colors: ['#16a34a', '#94a3b8'], stroke: { width: [3.5, 2.5], curve: 'smooth', dashArray: [0, 5] }, markers: { size: [4, 0], strokeColors: '#fff', strokeWidth: 2, hover: { size: 7 } },
                dataLabels: { enabled: false }, grid, yaxis: { min: 0, title: { text: 'Total revenue (Rs)' }, labels: { formatter: k } }, legend: { position: 'bottom', horizontalAlign: 'center', fontWeight: 600 }, tooltip: { shared: true, intersect: false, y: { formatter: (v) => (v == null ? '—' : rupees(v)) } },
            });
            const services = @json(collect($data['services'])->values());
            const serviceNames = services.map((s) => s.name);
            const serviceTotal = services.reduce((a, s) => a + Number(s.g) + Number(s.h), 0) || 1; let running = 0;
            draw('#services-chart', {
                chart: { ...base, type: 'bar', height: 250, stacked: true },
                series: [{ name: 'Government revenue', data: services.map((s) => Math.round(Number(s.g))) }, { name: 'HIF revenue', data: services.map((s) => Math.round(Number(s.h))) }],
                xaxis: { categories: serviceNames, labels: { show: false }, axisTicks: { show: false } }, colors: ['#16a34a', '#f59e0b'], plotOptions: { bar: { borderRadius: 3, columnWidth: '68%' } }, dataLabels: { enabled: false }, grid,
                yaxis: { min: 0, title: { text: 'Revenue (Rs)' }, labels: { formatter: k } }, legend: { position: 'top', horizontalAlign: 'right', fontWeight: 600 }, tooltip: { shared: true, intersect: false, y: { formatter: rupees } },
            });
            draw('#services-share', {
                chart: { ...base, ...lineShadow, type: 'line', height: 240 },
                series: [{ name: 'Cumulative share of total', data: services.map((s) => { running += Number(s.g) + Number(s.h); return Math.round(running / serviceTotal * 100); }) }],
                xaxis: { categories: serviceNames, labels: { rotate: -40, rotateAlways: true, trim: true, maxHeight: 80, hideOverlappingLabels: false, style: { fontSize: '10px' } } }, colors: ['#e11d48'], stroke: { width: 3, curve: 'smooth' }, markers: { size: 4, strokeColors: '#fff', strokeWidth: 2 }, dataLabels: { enabled: false }, grid,
                yaxis: { min: 0, max: 100, tickAmount: 4, title: { text: 'Cumulative %' }, labels: { formatter: (v) => Math.round(v) + '%' } }, legend: { show: false }, tooltip: { y: { formatter: (v) => v + '% of the total' } },
            });

            // 5. busy times ----------------------------------------------------------------------
            draw('#heat-chart', {
                chart: { ...base, type: 'heatmap', height: 330 }, series: @json($data['heat']).reverse(), dataLabels: { enabled: false },
                plotOptions: { heatmap: { radius: 4, shadeIntensity: .6 } }, colors: ['#4f46e5'], stroke: { width: 2, colors: ['#fff'] },
                xaxis: { type: 'category', title: { text: 'hour of the day' } }, tooltip: { y: { formatter: (v) => int(v) + ' chits' } },
            });
            const peak = {{ $data['peak_hour'] }}; const hourLabels = @json($data['hour_labels']);
            draw('#hours-chart', {
                chart: { ...base, type: 'bar', height: 150 }, series: [{ name: 'Chits', data: @json($data['hours']) }],
                xaxis: { categories: hourLabels.map((l) => l.slice(0, 2)) }, plotOptions: { bar: { borderRadius: 3, columnWidth: '75%' } }, dataLabels: { enabled: false }, grid,
                colors: [({ dataPointIndex }) => (hourLabels[dataPointIndex] === String(peak).padStart(2, '0') + ':00' ? '#4f46e5' : '#c7d2fe')], yaxis: { labels: { formatter: int } },
            });
            draw('#weekday-chart', {
                chart: { ...base, type: 'bar', height: 150 }, series: [{ name: 'Avg chits', data: @json(array_values($data['weekday'])) }],
                xaxis: { categories: @json(array_keys($data['weekday'])) }, colors: palette, plotOptions: { bar: { borderRadius: 4, columnWidth: '60%', distributed: true } },
                dataLabels: { enabled: true, formatter: int, style: { fontSize: '10px' } }, grid, legend: { show: false }, yaxis: { show: false },
            });
            draw('#box-chart', {
                chart: { ...base, type: 'boxPlot', height: 280 }, series: [{ name: 'Chits per day', type: 'boxPlot', data: @json($data['box']) }],
                colors: ['#4f46e5'], plotOptions: { boxPlot: { colors: { upper: '#a5b4fc', lower: '#6366f1' } } }, grid, yaxis: { min: 0, labels: { formatter: int } }, stroke: { colors: ['#312e81'] },
            });
            draw('#dom-chart', {
                chart: { ...base, type: 'bar', height: 280 }, series: [{ name: 'Avg chits', data: @json($data['day_of_month']) }],
                xaxis: { categories: Array.from({ length: 31 }, (_, i) => i + 1), tickAmount: 16, labels: { style: { fontSize: '10px' } }, title: { text: 'day of the month' } }, colors: ['#0891b2'],
                plotOptions: { bar: { borderRadius: 3, columnWidth: '70%' } }, dataLabels: { enabled: false }, grid, yaxis: { min: 0, labels: { formatter: int } },
            });

            // 6. departments & services ----------------------------------------------------------
            draw('#dept-donut', {
                chart: { ...base, type: 'donut', height: 320 }, series: @json($departments->pluck('n')->values()), labels: @json($departments->pluck('name')->values()), colors: palette,
                legend: { position: 'right', fontSize: '12px' }, dataLabels: { enabled: false }, plotOptions: { pie: { donut: { size: '62%', labels: { show: true, total: { show: true, label: 'Chits', formatter: (w) => int(w.globals.seriesTotals.reduce((a, b) => a + b, 0)) } } } } },
            });
            draw('#dept-revenue', {
                chart: { ...base, type: 'bar', height: 320, stacked: true },
                series: [{ name: 'Government revenue', data: @json($departments->map(fn ($d) => round($d['g']))->values()) }, { name: 'HIF revenue', data: @json($departments->map(fn ($d) => round($d['h']))->values()) }],
                xaxis: { categories: @json($departments->pluck('name')->values()), labels: { formatter: k } }, plotOptions: { bar: { horizontal: true, borderRadius: 3, barHeight: '65%' } },
                colors: ['#16a34a', '#f59e0b'], dataLabels: { enabled: false }, grid, legend: { position: 'bottom', horizontalAlign: 'center', fontWeight: 600 }, tooltip: { shared: true, intersect: false, y: { formatter: rupees } },
                yaxis: { labels: { maxWidth: 140, style: { fontSize: '11px' } } },
            });
            const catNames = @json($categories->pluck('name')->values());
            draw('#categories-chart', {
                chart: { ...base, type: 'bar', height: 260, stacked: true },
                series: [{ name: 'Government revenue', data: @json($categories->map(fn ($c) => round($c['g']))->values()) }, { name: 'HIF revenue', data: @json($categories->map(fn ($c) => round($c['h']))->values()) }],
                xaxis: { categories: catNames, labels: { show: false }, axisTicks: { show: false } }, colors: ['#16a34a', '#f59e0b'], plotOptions: { bar: { borderRadius: 3, columnWidth: '55%' } }, dataLabels: { enabled: false }, grid,
                yaxis: { min: 0, title: { text: 'Revenue (Rs)' }, labels: { formatter: k } }, legend: { position: 'top', horizontalAlign: 'right', fontWeight: 600 }, tooltip: { shared: true, intersect: false, y: { formatter: rupees } },
            });
            draw('#categories-tests', {
                chart: { ...base, type: 'bar', height: 150 },
                series: [{ name: 'Tests done', data: @json($categories->pluck('n')->values()) }],
                xaxis: { categories: catNames, labels: { rotate: 0, trim: false, hideOverlappingLabels: false, style: { fontSize: '11px' } } }, colors: ['#e11d48'], plotOptions: { bar: { borderRadius: 3, columnWidth: '55%', dataLabels: { position: 'top' } } },
                dataLabels: { enabled: true, formatter: int, offsetY: -18, style: { fontSize: '11px', colors: ['#334155'] } }, grid, yaxis: { min: 0, title: { text: 'Tests' }, labels: { formatter: int } }, legend: { show: false }, tooltip: { y: { formatter: (v) => int(v) + ' tests' } },
            });
            draw('#specialist-chart', {
                chart: { ...base, type: 'donut', height: 300 }, series: [{{ $x['specialist_chits'] }}, {{ max(0, $p['chits'] - $x['specialist_chits']) }}], labels: ['Specialist clinics', 'General OPD'], colors: ['#4f46e5', '#94a3b8'],
                legend: { position: 'bottom' }, dataLabels: { enabled: true, formatter: (v) => Math.round(v) + '%' }, plotOptions: { pie: { donut: { size: '58%', labels: { show: true, total: { show: true, label: 'Chits', formatter: (w) => int(w.globals.seriesTotals.reduce((a, b) => a + b, 0)) } } } } },
            });
            const simpleBars = (selector, names, values, color) => draw(selector, {
                chart: { ...base, type: 'bar', height: 200 }, series: [{ name: 'Count', data: values }], xaxis: { categories: names, tickAmount: 4, labels: { formatter: (v) => Math.round(v), style: { fontSize: '10px' } } }, yaxis: { labels: { maxWidth: 110, style: { fontSize: '10px' } } },
                colors: [color], plotOptions: { bar: { horizontal: true, borderRadius: 4, barHeight: '60%', dataLabels: { position: 'top' } } }, dataLabels: { enabled: true, formatter: int, offsetX: 26, style: { colors: ['#334155'], fontSize: '11px' } }, grid,
                tooltip: { y: { formatter: int } },
            });
            simpleBars('#ot-chart', @json(collect($data['operation_theatre'])->pluck('name')->values()), @json(collect($data['operation_theatre'])->pluck('n')->values()), '#e11d48');
            simpleBars('#radiology-chart', @json(collect($data['radiology'])->pluck('name')->values()), @json(collect($data['radiology'])->pluck('n')->values()), '#0891b2');
            simpleBars('#govt-chart', @json($govtDepartments->pluck('name')->values()), @json($govtDepartments->pluck('n')->values()), '#4f46e5');

            // 7. patients & admissions ----------------------------------------------------------
            @if ($p['patients'])
                draw('#gender-chart', {
                    chart: { ...base, type: 'donut', height: 250 }, series: @json(array_values($data['gender'])), labels: @json(array_keys($data['gender'])), colors: ['#2563eb', '#db2777'],
                    legend: { position: 'bottom' }, dataLabels: { enabled: true, formatter: (v) => Math.round(v) + '%' }, plotOptions: { pie: { donut: { size: '58%' } } },
                });
                const pyramid = @json($data['pyramid']);
                draw('#pyramid-chart', {
                    chart: { ...base, type: 'bar', height: 250, stacked: true }, series: [{ name: 'Male', data: pyramid.male.map((n) => -n) }, { name: 'Female', data: pyramid.female }],
                    xaxis: { categories: pyramid.groups, labels: { formatter: (v) => int(Math.abs(v)) } }, colors: ['#2563eb', '#db2777'], plotOptions: { bar: { horizontal: true, barHeight: '70%', borderRadius: 3 } },
                    dataLabels: { enabled: false }, grid, yaxis: { reversed: false }, legend: { position: 'bottom' }, tooltip: { y: { formatter: (v) => int(Math.abs(v)) + ' patients' } },
                });
            @endif
            draw('#admissions-chart', {
                chart: { ...base, ...lineShadow, type: 'area', height: 250 }, series: [{ name: 'Admissions', data: series.admissions }], xaxis: { categories: labels, tickAmount: Math.min(labels.length, 6), labels: { rotate: 0, hideOverlappingLabels: true, style: { fontSize: '10px' } } },
                colors: ['#e11d48'], stroke: { curve: 'smooth', width: 3 }, markers: { size: 3, strokeColors: '#fff', strokeWidth: 2, hover: { size: 6 } }, fill: { type: 'gradient', gradient: { opacityFrom: .4, opacityTo: .02 } }, dataLabels: { enabled: false }, grid, yaxis: { min: 0, forceNiceScale: true, decimalsInFloat: 0, labels: { formatter: int } },
            });
            draw('#wards-chart', {
                chart: { ...base, type: 'bar', height: 240 }, series: [{ name: 'Admissions', data: @json(collect($data['wards'])->pluck('n')->values()) }],
                xaxis: { categories: @json(collect($data['wards'])->pluck('name')->values()) }, colors: palette, plotOptions: { bar: { horizontal: true, borderRadius: 4, barHeight: '60%', distributed: true, dataLabels: { position: 'top' } } },
                dataLabels: { enabled: true, formatter: int, offsetX: 26, style: { colors: ['#334155'], fontSize: '11px' } }, grid, legend: { show: false },
            });
            draw('#projection-chart', {
                chart: { ...base, type: 'bar', height: 110, stacked: true },
                series: [{ name: 'So far', data: [{{ $proj['mtd_chits'] }}] }, { name: 'Still to come (projected)', data: [{{ max(0, $proj['chits'] - $proj['mtd_chits']) }}] }],
                xaxis: { categories: ['Chits'], labels: { show: false } }, colors: ['#4f46e5', '#c7d2fe'], plotOptions: { bar: { horizontal: true, barHeight: '55%', borderRadius: 4 } },
                dataLabels: { enabled: true, formatter: int }, grid: { show: false }, legend: { position: 'bottom' }, yaxis: { labels: { show: false } },
            });
        </script>
    @endpush
</x-app-layout>
