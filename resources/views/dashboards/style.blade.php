<style>
    :root {
        --ak-border: #cbd5e1; --ak-border-strong: #94a3b8; --ak-line: #e2e8f0;
        --ak-text: #0f172a; --ak-muted: #475569; --ak-soft: #f8fafc; --ak-radius: 12px;
    }
    .ak-page { max-width: 80rem; margin: 0 auto; padding: 24px 16px 64px; display: flex; flex-direction: column; gap: 20px; }
    @media (min-width: 640px) { .ak-page { padding-left: 24px; padding-right: 24px; } }
    @media (min-width: 1024px) { .ak-page { padding-left: 32px; padding-right: 32px; } }
    .ak-muted { color: var(--ak-muted); font-size: 12px; margin-top: 2px; }
</style>
<style>
    .db-hero { position: relative; overflow: hidden; border-radius: 16px; padding: 24px 28px; color: #fff; background: linear-gradient(120deg, #0f2a5c 0%, #1e40af 55%, #0e7490 100%); box-shadow: 0 10px 30px rgba(15, 42, 92, .25); }
    .db-hero::after { content: ""; position: absolute; right: -60px; top: -80px; width: 280px; height: 280px; border-radius: 50%; background: radial-gradient(circle, rgba(255,255,255,.18), transparent 65%); }
    .db-hero h1 { margin: 0; font-size: 24px; font-weight: 800; letter-spacing: -.01em; }
    .db-hero p { margin: 4px 0 0; font-size: 14px; color: #bfdbfe; }
    .db-hero-row { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 16px; position: relative; z-index: 1; }
    .db-date { text-align: right; font-size: 13px; color: #bfdbfe; }
    .db-date b { display: block; font-size: 20px; color: #fff; font-weight: 800; }
    .db-actions { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 18px; position: relative; z-index: 1; }
    .db-action { display: inline-flex; align-items: center; gap: 8px; padding: 10px 16px; border-radius: 10px; background: rgba(255,255,255,.14); border: 1px solid rgba(255,255,255,.28); color: #fff; font-size: 13.5px; font-weight: 700; text-decoration: none; transition: background .15s, transform .15s; }
    .db-action:hover { background: rgba(255,255,255,.26); transform: translateY(-1px); }
    .db-action svg { width: 18px; height: 18px; }
    .db-action.is-primary { background: #fff; color: #1e3a8a; border-color: #fff; }
    .db-action.is-primary:hover { background: #eff6ff; }

    .db-grid { display: grid; gap: 16px; margin-top: 20px; }
    .db-kpis { grid-template-columns: repeat(1, minmax(0, 1fr)); }
    @media (min-width: 640px) { .db-kpis { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    @media (min-width: 1100px) { .db-kpis { grid-template-columns: repeat(4, minmax(0, 1fr)); } }
    .db-kpi { position: relative; background: #fff; border: 1px solid var(--ak-border); border-radius: 14px; padding: 18px; box-shadow: 0 1px 2px rgba(15,23,42,.04); overflow: hidden; }
    .db-kpi::before { content: ""; position: absolute; left: 0; top: 0; bottom: 0; width: 4px; background: var(--tone, #1e40af); }
    .db-kpi-top { display: flex; align-items: center; justify-content: space-between; gap: 10px; }
    .db-kpi-label { font-size: 12.5px; font-weight: 700; color: var(--ak-muted); text-transform: uppercase; letter-spacing: .04em; }
    .db-kpi-icon { display: inline-flex; width: 38px; height: 38px; align-items: center; justify-content: center; border-radius: 10px; background: var(--soft, rgba(30,64,175,.12)); color: var(--tone, #1e40af); }
    .db-kpi-icon svg { width: 20px; height: 20px; }
    .db-kpi-value { margin-top: 10px; font-size: 28px; font-weight: 800; color: var(--ak-text); letter-spacing: -.02em; font-variant-numeric: tabular-nums; }
    .db-kpi-value small { font-size: 14px; font-weight: 700; color: var(--ak-muted); margin-right: 3px; }
    .db-kpi-hint { margin-top: 4px; font-size: 12.5px; color: var(--ak-muted); }
    .db-delta { display: inline-flex; align-items: center; gap: 3px; padding: 1px 8px; border-radius: 999px; font-size: 12px; font-weight: 700; }
    .db-delta.up { background: #dcfce7; color: #166534; }
    .db-delta.down { background: #fee2e2; color: #991b1b; }
    .db-delta.flat { background: #f1f5f9; color: #475569; }

    .db-two { grid-template-columns: 1fr; }
    @media (min-width: 1024px) { .db-two { grid-template-columns: minmax(0, 2fr) minmax(0, 1fr); } .db-two.is-even { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    .db-three { grid-template-columns: 1fr; }
    @media (min-width: 1024px) { .db-three { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
    .db-card { background: #fff; border: 1px solid var(--ak-border); border-radius: 14px; box-shadow: 0 1px 2px rgba(15,23,42,.04); }
    .db-card-head { display: flex; align-items: center; justify-content: space-between; gap: 10px; padding: 16px 18px 0; }
    .db-card-head h2 { margin: 0; font-size: 15px; font-weight: 800; color: var(--ak-text); }
    .db-card-head span { font-size: 12.5px; color: var(--ak-muted); }
    .db-card-body { padding: 8px 14px 14px; }
    .db-chart { min-height: 280px; }
    .db-table { width: 100%; border-collapse: collapse; font-size: 13.5px; }
    .db-table th { text-align: left; padding: 8px 12px; font-size: 11.5px; text-transform: uppercase; letter-spacing: .04em; color: var(--ak-muted); border-bottom: 1px solid var(--ak-line); }
    .db-table td { padding: 10px 12px; border-bottom: 1px solid var(--ak-line); }
    .db-table tr:last-child td { border-bottom: 0; }
    .db-table .r { text-align: right; font-variant-numeric: tabular-nums; }
    .db-empty { padding: 32px 12px; text-align: center; color: var(--ak-muted); font-size: 13.5px; }
    .db-bar { height: 6px; border-radius: 999px; background: #e2e8f0; overflow: hidden; margin-top: 6px; }
    .db-bar > span { display: block; height: 100%; border-radius: 999px; background: linear-gradient(90deg, #1e40af, #0e7490); }
    .db-split { display: flex; height: 10px; border-radius: 999px; overflow: hidden; background: #e2e8f0; margin-top: 12px; }
    .db-split > span:first-child { background: #16a34a; }
    .db-split > span:last-child { background: #2563eb; }
    .db-legend { display: flex; justify-content: space-between; margin-top: 8px; font-size: 12.5px; color: var(--ak-muted); }
    .db-legend b { color: var(--ak-text); }
    .db-links { display: grid; gap: 10px; grid-template-columns: repeat(2, minmax(0, 1fr)); }
    @media (min-width: 640px) { .db-links { grid-template-columns: repeat(4, minmax(0, 1fr)); } }
    .db-link { display: flex; align-items: center; gap: 10px; padding: 12px 14px; border: 1px solid var(--ak-border); border-radius: 12px; background: #fff; color: var(--ak-text); font-size: 13.5px; font-weight: 700; text-decoration: none; transition: border-color .15s, box-shadow .15s; }
    .db-link:hover { border-color: #94a3b8; box-shadow: 0 6px 16px rgba(15,23,42,.08); }
    .db-link svg { width: 20px; height: 20px; color: #1e40af; flex: none; }
    @media print { .db-actions, .db-links { display: none; } .db-hero { color: #000; background: #fff; box-shadow: none; } .db-hero p, .db-date { color: #000; } }
</style>
<style>
    .db-section { display: flex; align-items: baseline; gap: 10px; margin: 26px 0 0; font-size: 15px; font-weight: 800; color: var(--ak-text); }
    .db-section span { font-size: 12.5px; font-weight: 500; color: var(--ak-muted); }
    .db-section + .db-grid, .db-section + .db-links { margin-top: 10px; }
</style>
<style>
    /* ---- motion: cards lift, zoom a little and cast a deeper shadow on hover ---- */
    @keyframes db-rise { from { opacity: 0; transform: translateY(14px); } to { opacity: 1; transform: none; } }
    @keyframes db-shift { 0% { background-position: 0% 50%; } 50% { background-position: 100% 50%; } 100% { background-position: 0% 50%; } }
    .db-hero { background-size: 200% 200%; animation: db-shift 18s ease infinite, db-rise .5s ease both; }
    .db-kpi, .db-card, .db-link { animation: db-rise .5s ease both; }
    .db-kpis > :nth-child(2) { animation-delay: .05s; } .db-kpis > :nth-child(3) { animation-delay: .1s; } .db-kpis > :nth-child(4) { animation-delay: .15s; }
    .db-kpis > :nth-child(5) { animation-delay: .2s; } .db-kpis > :nth-child(6) { animation-delay: .25s; } .db-kpis > :nth-child(7) { animation-delay: .3s; } .db-kpis > :nth-child(8) { animation-delay: .35s; }
    .db-kpi, .db-card, .db-link, .db-action { transition: transform .25s cubic-bezier(.2, .8, .2, 1), box-shadow .25s ease, border-color .25s ease; will-change: transform; }
    .db-kpi:hover { transform: translateY(-5px) scale(1.025); box-shadow: 0 18px 38px -12px var(--glow, rgba(30,64,175,.4)), 0 6px 14px rgba(15, 23, 42, .08); border-color: var(--edge, #cbd5e1); z-index: 2; }
    .db-card:hover { transform: translateY(-3px) scale(1.008); box-shadow: 0 18px 36px -14px rgba(15, 23, 42, .28), 0 4px 10px rgba(15, 23, 42, .06); border-color: #cbd5e1; z-index: 1; }
    .db-link:hover { transform: translateY(-3px) scale(1.03); }
    .db-kpi-icon { transition: transform .3s ease, background .3s ease; }
    .db-kpi:hover .db-kpi-icon { transform: rotate(-8deg) scale(1.12); }
    .db-bar > span { transition: width .6s ease; }
    .db-table tbody tr { transition: background .15s ease; } .db-table tbody tr:hover { background: #f8fafc; }
    @media (prefers-reduced-motion: reduce) { .db-hero, .db-kpi, .db-card, .db-link { animation: none; } .db-kpi:hover, .db-card:hover, .db-link:hover { transform: none; } }

    /* ---- period picker ---- */
    .db-picker { display: flex; flex-wrap: wrap; align-items: center; gap: 8px; margin-top: 18px; position: relative; z-index: 1; }
    .db-chip { padding: 6px 12px; border-radius: 999px; background: rgba(255,255,255,.14); border: 1px solid rgba(255,255,255,.28); color: #fff; font-size: 12.5px; font-weight: 700; text-decoration: none; transition: background .2s, transform .2s; }
    .db-chip:hover { background: rgba(255,255,255,.28); transform: translateY(-1px); }
    .db-chip.is-on { background: #fff; color: #1e3a8a; border-color: #fff; }
    .db-range { display: inline-flex; align-items: center; gap: 6px; margin-left: auto; font-size: 12.5px; color: #dbeafe; }
    .db-range input[type=date] { height: 32px; padding: 0 8px; border-radius: 8px; border: 1px solid rgba(255,255,255,.35); background: rgba(255,255,255,.12); color: #fff; color-scheme: dark; font-size: 12.5px; }
    .db-range button { height: 32px; padding: 0 14px; border-radius: 8px; border: 0; background: #fff; color: #1e3a8a; font-weight: 800; font-size: 12.5px; cursor: pointer; transition: transform .2s; }
    .db-range button:hover { transform: scale(1.05); }
    .db-spark { height: 46px; margin: 8px -6px -6px; }
    .db-pill { display: inline-block; padding: 2px 9px; border-radius: 999px; font-size: 11.5px; font-weight: 800; }
    .db-pill.busy { background: #dcfce7; color: #166534; } .db-pill.quiet { background: #fee2e2; color: #991b1b; }
    .db-proj { display: grid; gap: 14px; grid-template-columns: repeat(2, minmax(0, 1fr)); padding: 14px 18px 18px; }
    .db-proj b { display: block; font-size: 22px; font-weight: 800; color: var(--ak-text); }
    .db-proj small { color: var(--ak-muted); font-size: 12px; }
</style>
<style>
    .db-note { margin: 2px 12px 8px; font-size: 12px; line-height: 1.5; color: var(--ak-muted); }
    .db-help { margin-top: 16px; background: #fff; border: 1px solid var(--ak-border); border-radius: 12px; padding: 0 16px; }
    .db-help summary { cursor: pointer; padding: 12px 0; font-size: 13.5px; font-weight: 800; color: var(--ak-text); }
    .db-help ul { margin: 0 0 14px; padding-left: 18px; font-size: 13px; color: #334155; line-height: 1.6; list-style: disc; }
    .db-insights { grid-template-columns: 1fr; }
    @media (min-width: 700px) { .db-insights { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    @media (min-width: 1100px) { .db-insights { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
    .db-insight { position: relative; background: #fff; border: 1px solid var(--ak-border); border-left: 4px solid var(--tone, #4f46e5); border-radius: 14px; padding: 16px 18px; box-shadow: 0 1px 2px rgba(15,23,42,.04); animation: db-rise .5s ease both; transition: transform .25s cubic-bezier(.2,.8,.2,1), box-shadow .25s ease; }
    .db-insight:hover { transform: translateY(-4px) scale(1.02); box-shadow: 0 18px 36px -14px var(--glow, rgba(79,70,229,.4)), 0 6px 14px rgba(15,23,42,.08); }
    .db-insight b { display: block; font-size: 26px; font-weight: 800; letter-spacing: -.02em; color: var(--tone, #4f46e5); font-variant-numeric: tabular-nums; }
    .db-insight h3 { margin: 4px 0 6px; font-size: 14px; font-weight: 800; color: var(--ak-text); }
    .db-insight p { margin: 0; font-size: 12.8px; line-height: 1.55; color: #475569; }
</style>
<style>
    @media print {
        @page { size: A4 landscape; margin: 8mm; }
        nav, .db-picker, .db-help, .db-chip, .db-actions { display: none !important; }
        .db-kpi, .db-card, .db-insight { break-inside: avoid; box-shadow: none !important; animation: none !important; transform: none !important; }
        body { background: #fff !important; }
    }
</style>
<style>
    /* compact, tidy cards */
    .db-kpis { gap: 12px; margin-top: 10px; }
    .db-kpi { padding: 12px 14px 10px; border-radius: 12px; }
    .db-kpi::before { width: 3px; }
    .db-kpi-label { font-size: 11px; letter-spacing: .05em; }
    .db-kpi-icon { width: 30px; height: 30px; border-radius: 8px; }
    .db-kpi-icon svg { width: 17px; height: 17px; }
    .db-kpi-value { margin-top: 6px; font-size: 23px; line-height: 1.15; }
    .db-kpi-value small { font-size: 12px; }
    .db-kpi-hint { margin-top: 3px; font-size: 11.5px; line-height: 1.35; }
    .db-delta { padding: 0 7px; font-size: 11px; }
    .db-spark { height: 30px; margin: 4px -4px -4px; }
    .db-grid { gap: 14px; margin-top: 14px; }
    .db-card-head { padding: 12px 14px 0; }
    .db-card-head h2 { font-size: 14px; }
    .db-card-head span { font-size: 11.5px; text-align: right; }
    .db-card-body { padding: 4px 10px 10px; }
    .db-insight { padding: 12px 14px; border-radius: 12px; }
    .db-insight b { font-size: 21px; }
    .db-insight h3 { margin: 2px 0 4px; font-size: 13px; }
    .db-insight p { font-size: 12px; line-height: 1.5; }
    .db-hero { padding: 18px 22px; border-radius: 14px; }
    .db-hero h1 { font-size: 21px; }
    .db-group { display: flex; align-items: center; gap: 8px; margin: 14px 2px 0; font-size: 11.5px; font-weight: 800; letter-spacing: .06em; text-transform: uppercase; color: var(--ak-muted); }
    .db-group::before { content: ""; width: 8px; height: 8px; border-radius: 50%; background: var(--dot, #4f46e5); }
    .db-group span { font-weight: 500; letter-spacing: 0; text-transform: none; }
    .db-section { margin-top: 28px; padding-top: 4px; border-top: 1px solid var(--ak-line); font-size: 16px; }
    .db-section i { display: inline-flex; width: 24px; height: 24px; align-items: center; justify-content: center; border-radius: 8px; background: #1e3a8a; color: #fff; font-style: normal; font-size: 12px; font-weight: 800; }
    .db-grid.db-kpis { margin-top: 6px; }
    @media (min-width: 1100px) { .db-two.is-thirds { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
</style>
<style>
    /* slim header + filter panel */
    .db-top { background: #fff; border: 1px solid var(--ak-border); border-radius: 14px; padding: 14px 18px; box-shadow: 0 6px 18px -8px rgba(15,23,42,.18), 0 1px 3px rgba(15,23,42,.06); }
    .db-top-row { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 12px; }
    .db-top-title h1 { margin: 0; font-size: 20px; font-weight: 800; color: var(--ak-text); letter-spacing: -.01em; }
    .db-top-title p { margin: 4px 0 0; display: flex; flex-wrap: wrap; gap: 10px; align-items: center; font-size: 12.5px; color: var(--ak-muted); }
    .db-period { display: inline-flex; align-items: center; gap: 6px; padding: 3px 10px; border-radius: 999px; background: #eef2ff; color: #3730a3; font-weight: 700; }
    .db-period svg { width: 14px; height: 14px; }
    .db-live { display: inline-flex; align-items: center; gap: 6px; }
    .db-live i { width: 8px; height: 8px; border-radius: 50%; background: #22c55e; box-shadow: 0 0 0 0 rgba(34,197,94,.6); animation: db-pulse 2s infinite; }
    @keyframes db-pulse { 0% { box-shadow: 0 0 0 0 rgba(34,197,94,.55); } 70% { box-shadow: 0 0 0 8px rgba(34,197,94,0); } 100% { box-shadow: 0 0 0 0 rgba(34,197,94,0); } }
    .db-top-actions { display: flex; gap: 8px; }
    .db-btn { display: inline-flex; align-items: center; gap: 7px; height: 36px; padding: 0 14px; border-radius: 10px; border: 1px solid var(--ak-border-strong); background: #fff; color: var(--ak-text); font-size: 13px; font-weight: 700; text-decoration: none; cursor: pointer; transition: transform .2s, box-shadow .2s, background .2s; }
    .db-btn:hover { transform: translateY(-1px); box-shadow: 0 6px 14px -6px rgba(15,23,42,.3); background: #f8fafc; }
    .db-btn svg { width: 16px; height: 16px; }
    .db-btn-primary { background: #1e3a8a; border-color: #1e3a8a; color: #fff; }
    .db-btn-primary:hover { background: #1e40af; }
    .db-dot { width: 8px; height: 8px; border-radius: 50%; background: #f59e0b; }
    .db-filter-panel { margin-top: 14px; padding-top: 14px; border-top: 1px solid var(--ak-line); display: grid; gap: 8px; }
    .db-filter-label { font-size: 11px; font-weight: 800; letter-spacing: .06em; text-transform: uppercase; color: var(--ak-muted); }
    .db-chips { display: flex; flex-wrap: wrap; gap: 8px; }
    .db-top .db-chip { background: #f1f5f9; border: 1px solid var(--ak-border); color: #334155; }
    .db-top .db-chip:hover { background: #e2e8f0; }
    .db-top .db-chip.is-on { background: #1e3a8a; color: #fff; border-color: #1e3a8a; }
    .db-top .db-range { margin-left: 0; display: flex; flex-wrap: wrap; align-items: center; gap: 8px; color: var(--ak-muted); }
    .db-top .db-range input[type=date] { height: 36px; padding: 0 10px; border-radius: 10px; border: 1px solid var(--ak-border-strong); background: #fff; color: var(--ak-text); color-scheme: light; }
    /* highlights */
    .db-highlights { margin-top: 14px; }
    .db-gauges { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 6px; padding: 6px 12px 6px; }
    @media (min-width: 900px) { .db-gauges { grid-template-columns: repeat(5, minmax(0, 1fr)); } }
    .db-gauges figure { margin: 0; text-align: center; padding: 6px 4px; border-radius: 12px; transition: background .2s, transform .2s; }
    .db-gauges figure:hover { background: #f8fafc; transform: translateY(-2px); }
    .db-gauges figcaption { font-size: 12px; line-height: 1.4; color: var(--ak-muted); }
    .db-gauges figcaption b { display: block; margin-bottom: 2px; font-size: 12.5px; color: var(--ak-text); }
    .db-words { margin: 0 16px 14px; border-top: 1px solid var(--ak-line); }
    .db-words summary { cursor: pointer; padding: 10px 0 4px; font-size: 12.5px; font-weight: 700; color: #3730a3; }
    .db-words ul { margin: 6px 0 0; padding-left: 18px; font-size: 13px; line-height: 1.6; color: #334155; list-style: disc; }
    /* default lift: soft shadows on every card so the page feels raised */
    .db-kpi, .db-card, .db-insight, .db-link { box-shadow: 0 6px 18px -8px rgba(15, 23, 42, .20), 0 1px 3px rgba(15, 23, 42, .07); }
    .db-card { border-color: #e2e8f0; }
    .apexcharts-tooltip { box-shadow: 0 10px 24px -8px rgba(15, 23, 42, .35) !important; border-radius: 10px !important; }
    @media print { .db-top-actions, .db-filter-panel { display: none !important; } }
</style>
<style>
    .db-kpi-value { margin-bottom: 5px; padding-top: 2px; }
    .db-kpi-hint { margin-top: 4px; display: flex; flex-wrap: wrap; gap: 4px 6px; align-items: center; }
</style>
<style>
    /* lighter motion: no always-on animations, no GPU layers per card, heavy chart cards only change shadow on hover */
    .db-hero { animation: none; background-size: auto; }
    .db-card, .db-link, .db-kpi, .db-action { will-change: auto; }
    .db-card, .db-link { animation: none; }
    .db-card:hover { transform: none; box-shadow: 0 16px 32px -14px rgba(15, 23, 42, .26), 0 3px 8px rgba(15, 23, 42, .06); }
    .db-kpi, .db-insight { animation: db-rise .35s ease both; }
    .db-live i { animation-duration: 3s; }
</style>
