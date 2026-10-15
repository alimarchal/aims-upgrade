{{-- Front Desk / Receptionist dashboard: the signed-in user's own work, today and this month. --}}
@php
    $change = fn (float $now, float $before, string $suffix = 'vs yesterday') => \App\Support\DashboardFormat::change($now, $before, $suffix);
    $rs = fn ($n) => number_format((float) $n);
@endphp
@php
    $user = auth()->user();
    $n = $data['now'];
    [$chitTone, $chitText] = $change($n['chits'], $data['prev']['chits']);
    [$invTone, $invText] = $change($n['invoices'], $data['prev']['invoices']);
    $entTotal = max(1, $n['entitled'] + $n['non_entitled']);
    $hour = (int) now()->format('H');
    $greeting = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
    $maxDept = max(1, (int) ($data['departments']->max('n') ?? 1));
    $peak = max($data['hours']) > 0 ? $data['hour_labels'][array_search(max($data['hours']), $data['hours'])].':00' : null;
@endphp

<x-app-layout>
    @include('dashboards.style')

    <div class="ak-page" style="padding-top: 24px">
        <x-success-message />

        <section class="db-hero" aria-label="Welcome">
            <div class="db-hero-row">
                <div>
                    <h1>{{ $greeting }}, {{ \Illuminate\Support\Str::of($user->name)->replaceMatches('/^(mr|mrs|ms|miss|dr)\.?\s+/i', '')->trim()->before(' ') }}</h1>
                    <p>
                        Front desk &middot; your own work today.
                        @if ($data['last_chit_at']) Last chit at <b>{{ $data['last_chit_at']->format('h:i A') }}</b>. @else No chit issued yet today. @endif
                    </p>
                </div>
                <div class="db-date">{{ now()->format('l') }}<b>{{ now()->format('d M Y') }}</b></div>
            </div>
            <div class="db-actions">
                @can('create patients')
                    <a href="{{ route('patient.create-opd') }}" class="db-action is-primary"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>New OPD patient</a>
                    <a href="{{ route('patient.create-ipd') }}" class="db-action"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>New IPD patient</a>
                @endcan
                @can('view patients')
                    <a href="{{ route('patient.index') }}" class="db-action"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35M17 10.5a6.5 6.5 0 1 1-13 0 6.5 6.5 0 0 1 13 0Z" /></svg>Find a patient</a>
                @endcan
                @can('view chits')
                    <a href="{{ route('chits.issued-today') }}" class="db-action"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5.586a1 1 0 0 1 .707.293l5.414 5.414a1 1 0 0 1 .293.707V19a2 2 0 0 1-2 2Z" /></svg>Today's chits</a>
                @endcan
                @can('view invoices')
                    <a href="{{ route('invoice.issued-today') }}" class="db-action"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 14.25l6-6m4.5-3.493V21.75l-3.75-1.5-3.75 1.5-3.75-1.5-3.75 1.5V4.757c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 0 1 11.186 0c1.1.128 1.907 1.077 1.907 2.185Z" /></svg>Today's invoices</a>
                @endcan
            </div>
        </section>

        <section class="db-grid db-kpis" aria-label="Today">
            <a href="{{ route('chits.issued-today') }}" style="text-decoration:none">@include('dashboards.kpi', ['label' => 'Issued chits today', 'value' => $rs($n['chits']), 'tone' => '#4f46e5', 'icon' => 'chit', 'badge' => $chitText, 'badgeTone' => $chitTone, 'hint' => ''])</a>
            <a href="{{ route('chits.issued-today', ['filter' => ['government_non_gov' => 1]]) }}" style="text-decoration:none">@include('dashboards.kpi', ['label' => 'Entitled (free)', 'value' => $rs($n['entitled']), 'tone' => '#0d9488', 'icon' => 'shield', 'hint' => round($n['entitled'] / $entTotal * 100).'% of today\'s chits'])</a>
            <a href="{{ route('chits.issued-today', ['filter' => ['government_non_gov' => 0]]) }}" style="text-decoration:none">@include('dashboards.kpi', ['label' => 'Non-entitled (paid)', 'value' => $rs($n['non_entitled']), 'tone' => '#2563eb', 'icon' => 'users', 'hint' => round($n['non_entitled'] / $entTotal * 100).'% of today\'s chits'])</a>
            <a href="{{ route('invoice.issued-today') }}" style="text-decoration:none">@include('dashboards.kpi', ['label' => 'Invoices today', 'value' => $rs($n['invoices']), 'tone' => '#0891b2', 'icon' => 'invoice', 'badge' => $invText, 'badgeTone' => $invTone, 'hint' => ''])</a>
            @include('dashboards.kpi', ['label' => 'Today revenue', 'prefix' => 'Rs ', 'value' => $rs($n['chit_gross']), 'tone' => '#4f46e5', 'icon' => 'money', 'hint' => 'from chits (with HIF)'])
            @include('dashboards.kpi', ['label' => 'Invoices revenue', 'prefix' => 'Rs ', 'value' => $rs($n['invoice_gross']), 'tone' => '#0891b2', 'icon' => 'money', 'hint' => 'from invoices (with HIF)'])
            @include('dashboards.kpi', ['label' => 'Government share', 'prefix' => 'Rs ', 'value' => $rs($n['govt']), 'tone' => '#16a34a', 'icon' => 'money', 'hint' => 'what the hospital keeps, after HIF'])
            @include('dashboards.kpi', ['label' => 'HIF share', 'prefix' => 'Rs ', 'value' => $rs($n['hif']), 'tone' => '#f59e0b', 'icon' => 'gift', 'hint' => 'total revenue Rs '.$rs($n['collected'])])
        </section>

        <section class="db-grid db-kpis" aria-label="This month">
            @include('dashboards.kpi', ['label' => 'Chits this month', 'value' => $rs($data['month']['chits']), 'tone' => '#4f46e5', 'icon' => 'chit', 'hint' => $data['worked_days'].' working days'])
            @include('dashboards.kpi', ['label' => 'Daily average', 'value' => $rs($data['month_daily_avg']), 'tone' => '#4f46e5', 'icon' => 'users', 'hint' => 'chits per working day'])
            @include('dashboards.kpi', ['label' => 'Government share (month)', 'prefix' => 'Rs ', 'value' => $rs($data['month']['govt']), 'tone' => '#16a34a', 'icon' => 'money', 'hint' => $rs($data['month']['invoices']).' invoices this month'])
            @include('dashboards.kpi', ['label' => 'Patients registered today', 'value' => $rs($data['registered']), 'tone' => '#0e7490', 'icon' => 'users', 'hint' => 'new patient files by you'])
        </section>

        <section class="db-grid db-two">
            <div class="db-card">
                <div class="db-card-head"><h2>My last 7 days</h2><span>chits and invoices issued</span></div>
                <div class="db-card-body"><div id="week-chart" class="db-chart"></div></div>
            </div>
            <div class="db-card">
                <div class="db-card-head"><h2>Today by entitlement</h2><span>{{ $rs($n['chits']) }} chits</span></div>
                <div class="db-card-body" style="padding: 14px 18px 18px">
                    <div class="db-split" role="img" aria-label="Entitled {{ $n['entitled'] }}, non entitled {{ $n['non_entitled'] }}">
                        <span style="width: {{ round($n['entitled'] / $entTotal * 100) }}%"></span><span style="width: {{ round($n['non_entitled'] / $entTotal * 100) }}%"></span>
                    </div>
                    <div class="db-legend"><span><b>{{ $rs($n['entitled']) }}</b> entitled (free)</span><span><b>{{ $rs($n['non_entitled']) }}</b> non-entitled (paid)</span></div>
                    <h3 style="margin: 22px 0 6px; font-size: 13px; font-weight: 800">Where my chits went</h3>
                    @forelse ($data['departments'] as $dept)
                        <div style="margin-top: 10px">
                            <div style="display:flex; justify-content:space-between; font-size:13px"><span>{{ $dept->name }}</span><b>{{ $rs($dept->n) }}</b></div>
                            <div class="db-bar"><span style="width: {{ round($dept->n / $maxDept * 100) }}%"></span></div>
                        </div>
                    @empty
                        <div class="db-empty">No chits yet today.</div>
                    @endforelse
                </div>
            </div>
        </section>

        <section class="db-grid db-two">
            <div class="db-card">
                <div class="db-card-head"><h2>My day by hour</h2><span>@if ($peak) busiest around {{ $peak }} @else chits issued per hour @endif</span></div>
                <div class="db-card-body"><div id="hour-chart" style="min-height: 220px"></div></div>
            </div>
            <div class="db-card">
                <div class="db-card-head"><h2>Latest chits by me</h2><span>today</span></div>
                <div class="db-card-body">
                    @if ($data['recent_chits']->isEmpty())
                        <div class="db-empty">No chits issued yet today. Use <b>New OPD patient</b> to get started.</div>
                    @else
                        <table class="db-table">
                            <thead><tr><th>Time</th><th>Patient</th><th class="r">Amount</th></tr></thead>
                            <tbody>
                                @foreach ($data['recent_chits'] as $chit)
                                    <tr>
                                        <td>{{ \Carbon\Carbon::parse($chit->issued_date)->format('h:i A') }}</td>
                                        <td><b>{{ trim(($chit->patient->first_name ?? '').' '.($chit->patient->last_name ?? '')) ?: '—' }}</b><div class="ak-muted" style="font-size:12px">{{ $chit->department->name ?? '' }}</div></td>
                                        <td class="r">{{ number_format($chit->amount, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>
        </section>
    </div>

    @push('scripts')
        <script src="{{ url('js/apexcharts.js') }}"></script>
        <script>
            const base = { toolbar: { show: false }, fontFamily: 'inherit', animations: { enabled: false } };
            new ApexCharts(document.querySelector('#week-chart'), {
                chart: { ...base, type: 'bar', height: 280 },
                series: [{ name: 'Chits', data: @json($data['week_chits']) }, { name: 'Invoices', data: @json($data['week_invoices']) }],
                xaxis: { categories: @json($data['week_labels']) },
                colors: ['#1e40af', '#0e7490'], plotOptions: { bar: { borderRadius: 5, columnWidth: '52%' } },
                dataLabels: { enabled: false }, grid: { borderColor: '#e2e8f0', strokeDashArray: 4 },
                legend: { position: 'top', horizontalAlign: 'right' }, yaxis: { min: 0, forceNiceScale: true, decimalsInFloat: 0, labels: { formatter: (v) => Math.round(v) } },
            }).render();
            new ApexCharts(document.querySelector('#hour-chart'), {
                chart: { ...base, type: 'area', height: 220 },
                series: [{ name: 'Chits', data: @json($data['hours']) }],
                xaxis: { categories: @json($data['hour_labels']), title: { text: 'hour of the day' } },
                colors: ['#4f46e5'], stroke: { curve: 'smooth', width: 3 }, fill: { type: 'gradient', gradient: { opacityFrom: .35, opacityTo: .02 } },
                dataLabels: { enabled: false }, grid: { borderColor: '#e2e8f0', strokeDashArray: 4 }, yaxis: { min: 0, forceNiceScale: true, decimalsInFloat: 0, labels: { formatter: (v) => Math.round(v) } },
            }).render();
        </script>
    @endpush
</x-app-layout>
