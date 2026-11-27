{{--
    Dashboard styles (management and front desk), in an Apple-like visual language: a light grey canvas, white
    cards with large radii and soft shadows instead of borders, the system font with size-specific tracking,
    one blue accent, Apple system colours for data, a translucent sticky header and restrained motion.
--}}
<style>
    :root {
        --db-canvas: #f5f5f7; --db-surface: #ffffff; --db-fill: rgba(120, 120, 128, .12); --db-fill-strong: rgba(120, 120, 128, .2);
        --db-text: #1d1d1f; --db-muted: #6e6e73; --db-faint: #86868b; --db-separator: rgba(60, 60, 67, .12);
        --db-accent: #0071e3; --db-accent-hover: #0077ed; --db-accent-soft: rgba(0, 113, 227, .1);
        --db-green: #248a3d; --db-green-soft: rgba(52, 199, 89, .14); --db-red: #d70015; --db-red-soft: rgba(255, 59, 48, .12);
        --db-radius-lg: 22px; --db-radius: 18px; --db-radius-sm: 12px;
        --db-shadow: 0 1px 1px rgba(0, 0, 0, .02), 0 4px 18px rgba(0, 0, 0, .05);
        --db-shadow-hover: 0 1px 2px rgba(0, 0, 0, .03), 0 10px 30px rgba(0, 0, 0, .08);
        --db-ease: cubic-bezier(.25, .1, .25, 1); --db-spring: cubic-bezier(.2, .8, .2, 1);
        /* legacy names other partials still read */
        --ak-border: var(--db-separator); --ak-line: var(--db-separator); --ak-text: var(--db-text); --ak-muted: var(--db-muted); --ak-radius: var(--db-radius);
    }

    /* ---- canvas & type ---------------------------------------------------------------------------- */
    body:has(.ak-page) { background: var(--db-canvas); }
    .min-h-screen:has(.ak-page) { background: transparent; }
    /* a soft, still wash of colour behind everything, so white cards and the frosted header have something to sit on */
    .ak-page::before {
        content: ""; position: fixed; inset: 0; z-index: -1; pointer-events: none;
        background:
            radial-gradient(62rem 40rem at -5% -10%, rgba(0, 113, 227, .14), transparent 60%),
            radial-gradient(54rem 38rem at 105% -5%, rgba(175, 82, 222, .13), transparent 60%),
            radial-gradient(58rem 42rem at 85% 105%, rgba(255, 149, 0, .11), transparent 60%),
            radial-gradient(50rem 38rem at -5% 105%, rgba(48, 161, 78, .10), transparent 60%);
    }
    .ak-page {
        max-width: 80rem; margin: 0 auto; padding: 20px 16px 72px; display: flex; flex-direction: column; gap: 0;
        font-family: -apple-system, BlinkMacSystemFont, "SF Pro Text", "SF Pro Display", "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        color: var(--db-text); font-size: 15px; line-height: 1.47; letter-spacing: -.01em;
        -webkit-font-smoothing: antialiased; font-optical-sizing: auto;
    }
    @media (min-width: 640px) { .ak-page { padding-left: 24px; padding-right: 24px; } }
    @media (min-width: 1024px) { .ak-page { padding-left: 32px; padding-right: 32px; } }
    .ak-page :is(h1, h2, h3) { letter-spacing: -.02em; }
    .ak-muted { color: var(--db-muted); font-size: 12px; margin-top: 2px; letter-spacing: 0; }
    .ak-page :focus-visible { outline: 3px solid rgba(0, 113, 227, .45); outline-offset: 2px; }

    /* ---- sticky translucent header (management) -------------------------------------------------------- */
    .db-top {
        position: sticky; top: 10px; z-index: 30; padding: 18px 22px; border-radius: var(--db-radius-lg);
        background: rgba(255, 255, 255, .72); -webkit-backdrop-filter: saturate(180%) blur(20px); backdrop-filter: saturate(180%) blur(20px);
        box-shadow: 0 0 0 .5px rgba(0, 0, 0, .06), 0 8px 30px rgba(0, 0, 0, .06);
        transition: padding .3s var(--db-ease), box-shadow .3s var(--db-ease);
    }
    .db-top-row { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 12px; }
    .db-eyebrow { margin: 0; font-size: 13px; font-weight: 600; color: var(--db-muted); letter-spacing: 0; transition: opacity .2s var(--db-ease), height .3s var(--db-ease); }
    .db-top-title h1 { margin: 2px 0 0; font-size: 34px; line-height: 1.15; font-weight: 700; letter-spacing: -.025em; color: var(--db-text); transition: font-size .3s var(--db-ease); }
    .db-top-title h1, .db-hero h1 { background: linear-gradient(100deg, #1d1d1f 0%, #1d1d1f 38%, #0071e3 72%, #af52de 100%); -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent; padding-bottom: 2px; }
    .db-top-title p.db-sub { margin: 6px 0 0; display: flex; flex-wrap: wrap; gap: 12px; align-items: center; font-size: 13px; color: var(--db-muted); letter-spacing: 0; }
    .db-top.is-compact { padding-top: 10px; padding-bottom: 10px; }
    .db-top.is-compact .db-top-title h1 { font-size: 20px; }
    .db-top.is-compact .db-eyebrow, .db-top.is-compact .db-sub { display: none !important; }
    .db-period { display: inline-flex; align-items: center; gap: 6px; }
    .db-period svg { width: 14px; height: 14px; }
    .db-live { display: inline-flex; align-items: center; gap: 6px; }
    .db-live i { width: 7px; height: 7px; border-radius: 50%; background: #34c759; box-shadow: 0 0 0 0 rgba(52, 199, 89, .5); animation: db-pulse 2.4s var(--db-ease) infinite; }
    @keyframes db-pulse { 0% { box-shadow: 0 0 0 0 rgba(52, 199, 89, .5); } 70%, 100% { box-shadow: 0 0 0 7px rgba(52, 199, 89, 0); } }
    .db-top-actions { display: flex; gap: 8px; }
    .db-filter-panel { margin-top: 16px; padding-top: 16px; border-top: .5px solid var(--db-separator); display: grid; gap: 10px; }
    .db-filter-label { font-size: 12px; font-weight: 600; color: var(--db-muted); letter-spacing: 0; }
    .db-chips { display: flex; flex-wrap: wrap; gap: 6px; }
    .db-chip { padding: 6px 14px; border-radius: 999px; background: var(--db-fill); color: var(--db-text); font-size: 13px; font-weight: 500; text-decoration: none; letter-spacing: 0; transition: background .15s var(--db-ease), transform .1s var(--db-ease); }
    .db-chip:hover { background: var(--db-fill-strong); }
    .db-chip:active { transform: scale(.96); }
    .db-chip.is-on { background: var(--db-text); color: #fff; }
    .db-range { display: flex; flex-wrap: wrap; align-items: center; gap: 8px; font-size: 13px; color: var(--db-muted); }
    .db-range input[type=date] { height: 34px; padding: 0 12px; border-radius: 10px; border: 0; background: var(--db-fill); color: var(--db-text); font: inherit; font-size: 13px; color-scheme: light; }
    .db-range input[type=date]:focus { box-shadow: 0 0 0 3px rgba(0, 113, 227, .35); outline: none; }

    /* ---- buttons ---------------------------------------------------------------------------------- */
    .db-btn { display: inline-flex; align-items: center; gap: 6px; height: 34px; padding: 0 16px; border: 0; border-radius: 999px; background: var(--db-fill); color: var(--db-text); font: inherit; font-size: 13px; font-weight: 500; letter-spacing: 0; text-decoration: none; cursor: pointer; transition: background .15s var(--db-ease), transform .1s var(--db-ease); }
    .db-btn:hover { background: var(--db-fill-strong); }
    .db-btn:active { transform: scale(.96); }
    .db-btn svg { width: 15px; height: 15px; }
    .db-btn-primary { background: var(--db-accent); color: #fff; }
    .db-btn-primary:hover { background: var(--db-accent-hover); }
    .db-dot { width: 7px; height: 7px; border-radius: 50%; background: #ff9500; }

    /* ---- front desk hero -------------------------------------------------------------------------- */
    .db-hero { position: relative; padding: 26px 28px; border-radius: var(--db-radius-lg); background: radial-gradient(40rem 16rem at 100% 0%, rgba(0, 113, 227, .09), transparent 70%), radial-gradient(30rem 14rem at 0% 100%, rgba(175, 82, 222, .07), transparent 70%), var(--db-surface); color: var(--db-text); box-shadow: var(--db-shadow); }
    .db-hero h1 { margin: 0; font-size: 34px; line-height: 1.1; font-weight: 700; letter-spacing: -.025em; }
    .db-hero p { margin: 6px 0 0; font-size: 15px; color: var(--db-muted); letter-spacing: -.005em; }
    .db-hero-row { display: flex; flex-wrap: wrap; align-items: flex-end; justify-content: space-between; gap: 16px; }
    .db-date { text-align: right; font-size: 13px; color: var(--db-muted); }
    .db-date b { display: block; font-size: 22px; font-weight: 600; color: var(--db-text); letter-spacing: -.02em; }
    .db-actions { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 20px; }
    .db-action { display: inline-flex; align-items: center; gap: 7px; height: 36px; padding: 0 16px; border-radius: 999px; background: var(--db-fill); color: var(--db-text); font-size: 14px; font-weight: 500; letter-spacing: -.01em; text-decoration: none; transition: background .15s var(--db-ease), transform .1s var(--db-ease); }
    .db-action:hover { background: var(--db-fill-strong); }
    .db-action:active { transform: scale(.96); }
    .db-action svg { width: 16px; height: 16px; }
    .db-action.is-primary { background: var(--db-accent); color: #fff; }
    .db-action.is-primary:hover { background: var(--db-accent-hover); }

    /* ---- grids ------------------------------------------------------------------------------------ */
    .db-grid { display: grid; gap: 16px; margin-top: 16px; }
    .db-kpis { grid-template-columns: repeat(1, minmax(0, 1fr)); }
    @media (min-width: 560px) { .db-kpis { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    @media (min-width: 1100px) { .db-kpis { grid-template-columns: repeat(4, minmax(0, 1fr)); } }
    .db-two, .db-three { grid-template-columns: 1fr; }
    @media (min-width: 1024px) {
        .db-two { grid-template-columns: minmax(0, 2fr) minmax(0, 1fr); }
        .db-two.is-even { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .db-three { grid-template-columns: repeat(3, minmax(0, 1fr)); }
    }
    /* a card whose neighbours are hidden by permissions takes the whole row */
    .db-two > :only-child, .db-three > :only-child { grid-column: 1 / -1; }

    /* ---- section titles --------------------------------------------------------------------------- */
    .db-section::before { content: ""; align-self: center; width: 5px; height: 22px; border-radius: 3px; background: linear-gradient(180deg, #0071e3, #af52de); }
    .db-section { display: flex; flex-wrap: wrap; align-items: baseline; gap: 4px 12px; margin: 44px 4px 0; font-size: 24px; line-height: 1.15; font-weight: 700; letter-spacing: -.022em; color: var(--db-text); }
    .db-section i { display: none; }
    .db-section span { font-size: 14px; font-weight: 400; letter-spacing: -.005em; color: var(--db-muted); }
    .db-section + .db-grid, .db-section + .db-links, .db-section + .db-group { margin-top: 14px; }
    .db-group { display: flex; align-items: center; gap: 8px; margin: 22px 4px 0; font-size: 15px; font-weight: 600; letter-spacing: -.01em; color: var(--db-text); }
    .db-group::before { content: ""; width: 8px; height: 8px; border-radius: 50%; background: var(--dot, var(--db-accent)); }
    .db-group span { font-weight: 400; color: var(--db-muted); }
    .db-group + .db-grid { margin-top: 10px; }

    /* ---- KPI tiles -------------------------------------------------------------------------------- */
    .db-kpi { position: relative; display: block; padding: 18px 20px 14px; border-radius: var(--db-radius); background: linear-gradient(165deg, var(--db-surface) 52%, var(--soft, transparent) 150%), var(--db-surface); box-shadow: var(--db-shadow); overflow: hidden; color: inherit; text-decoration: none; transition: box-shadow .35s var(--db-spring), transform .35s var(--db-spring); }
    .db-kpi:hover { transform: translateY(-3px); box-shadow: 0 1px 2px rgba(0, 0, 0, .03), 0 16px 34px -10px var(--glow, rgba(0, 0, 0, .18)); }
    a.db-kpi:active, a > .db-kpi:active { transform: scale(.985); }
    .db-kpi-top { display: flex; align-items: center; justify-content: space-between; gap: 10px; }
    .db-kpi-label { font-size: 13px; font-weight: 500; color: var(--db-muted); letter-spacing: -.005em; }
    .db-kpi-icon { display: inline-flex; flex: none; width: 32px; height: 32px; align-items: center; justify-content: center; border-radius: 9px; background: linear-gradient(150deg, color-mix(in srgb, var(--tone, #0071e3) 72%, #fff), var(--tone, #0071e3)); color: #fff; box-shadow: 0 4px 10px -3px var(--glow, rgba(0, 113, 227, .4)), inset 0 1px 0 rgba(255, 255, 255, .35); transition: transform .35s var(--db-spring); }
    .db-kpi:hover .db-kpi-icon { transform: scale(1.08) rotate(-4deg); }
    .db-kpi-icon svg { width: 17px; height: 17px; }
    .db-kpi-value { margin-top: 8px; font-size: 30px; line-height: 1.1; font-weight: 600; letter-spacing: -.03em; color: var(--db-text); font-variant-numeric: tabular-nums; }
    .db-kpi-value small { font-size: 15px; font-weight: 500; color: var(--db-muted); margin-right: 3px; letter-spacing: -.01em; }
    .db-kpi-hint { margin-top: 6px; display: flex; flex-wrap: wrap; gap: 4px 8px; align-items: center; font-size: 12px; line-height: 1.35; color: var(--db-muted); letter-spacing: 0; }
    .db-delta { display: inline-flex; align-items: center; gap: 3px; padding: 1px 8px; border-radius: 999px; font-size: 12px; font-weight: 600; letter-spacing: 0; font-variant-numeric: tabular-nums; }
    .db-delta.up { background: var(--db-green-soft); color: var(--db-green); }
    .db-delta.down { background: var(--db-red-soft); color: var(--db-red); }
    .db-delta.flat { background: var(--db-fill); color: var(--db-muted); }
    .db-spark { height: 34px; margin: 10px -20px -14px; }

    /* ---- cards ------------------------------------------------------------------------------------ */
    .db-card { position: relative; border-radius: var(--db-radius-lg); background: var(--db-surface); box-shadow: var(--db-shadow); transition: box-shadow .35s var(--db-spring), transform .35s var(--db-spring); }
    .db-card:hover { transform: translateY(-2px); box-shadow: var(--db-shadow-hover); }
    .db-card-head { display: flex; align-items: baseline; justify-content: space-between; gap: 12px; padding: 20px 22px 0; }
    .db-card-head h2 { margin: 0; font-size: 17px; line-height: 1.25; font-weight: 600; color: var(--db-text); letter-spacing: -.02em; }
    .db-card-head span { font-size: 13px; color: var(--db-muted); text-align: right; letter-spacing: -.005em; }
    .db-card-body { padding: 8px 14px 16px; }
    .db-chart { min-height: 280px; }
    .db-note { margin: 4px 8px 4px; font-size: 13px; line-height: 1.5; color: var(--db-muted); letter-spacing: -.005em; }
    .db-empty { padding: 36px 12px; text-align: center; color: var(--db-faint); font-size: 14px; }

    /* ---- highlights ------------------------------------------------------------------------------- */
    .db-highlights { margin-top: 16px; }
    .db-gauges { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 4px; padding: 8px 12px 4px; }
    @media (min-width: 900px) { .db-gauges { grid-template-columns: repeat(5, minmax(0, 1fr)); } }
    .db-gauges figure { margin: 0; padding: 4px; text-align: center; }
    .db-gauges figcaption { font-size: 12px; line-height: 1.4; color: var(--db-muted); letter-spacing: 0; }
    .db-gauges figcaption b { display: block; margin-bottom: 2px; font-size: 13px; font-weight: 600; color: var(--db-text); letter-spacing: -.01em; }
    .db-words { margin: 6px 22px 18px; border-top: .5px solid var(--db-separator); }
    .db-words summary, .db-help summary { cursor: pointer; list-style: none; }
    .db-words summary::-webkit-details-marker, .db-help summary::-webkit-details-marker { display: none; }
    .db-words summary { padding: 12px 0 2px; font-size: 14px; font-weight: 500; color: var(--db-accent); }
    .db-words summary::after, .db-help summary::after { content: "›"; display: inline-block; margin-left: 6px; transition: transform .2s var(--db-ease); }
    .db-words[open] summary::after, .db-help[open] summary::after { transform: rotate(90deg); }
    .db-words ul { margin: 8px 0 0; padding-left: 18px; font-size: 14px; line-height: 1.6; color: var(--db-text); list-style: disc; }
    .db-help { margin-top: 16px; padding: 0 22px; border-radius: var(--db-radius); background: var(--db-surface); box-shadow: var(--db-shadow); }
    .db-help summary { padding: 14px 0; font-size: 15px; font-weight: 500; color: var(--db-text); }
    .db-help ul { margin: 0 0 16px; padding-left: 18px; font-size: 14px; line-height: 1.6; color: var(--db-muted); list-style: disc; }
    .db-help ul b { color: var(--db-text); font-weight: 600; }

    /* ---- insights --------------------------------------------------------------------------------- */
    .db-insights { grid-template-columns: 1fr; }
    @media (min-width: 700px) { .db-insights { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    @media (min-width: 1100px) { .db-insights { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
    .db-insight { position: relative; padding: 20px 22px; border-radius: var(--db-radius); background: linear-gradient(165deg, var(--db-surface) 55%, var(--soft, transparent) 170%), var(--db-surface); box-shadow: var(--db-shadow); transition: box-shadow .35s var(--db-spring), transform .35s var(--db-spring); }
    .db-insight:hover { transform: translateY(-3px); box-shadow: 0 1px 2px rgba(0, 0, 0, .03), 0 16px 34px -12px var(--glow, rgba(0, 0, 0, .18)); }
    .db-insight b { display: flex; align-items: center; gap: 8px; font-size: 28px; line-height: 1.1; font-weight: 600; letter-spacing: -.03em; color: var(--db-text); font-variant-numeric: tabular-nums; }
    .db-insight b::before { content: ""; flex: none; width: 10px; height: 10px; border-radius: 50%; background: var(--tone, var(--db-accent)); box-shadow: 0 0 0 4px var(--soft, var(--db-accent-soft)); }
    .db-insight h3 { margin: 8px 0 4px; font-size: 15px; font-weight: 600; color: var(--db-text); }
    .db-insight p { margin: 0; font-size: 13px; line-height: 1.5; color: var(--db-muted); letter-spacing: -.005em; }

    /* ---- tables, bars, lists ---------------------------------------------------------------------- */
    .db-table { width: 100%; border-collapse: collapse; font-size: 14px; letter-spacing: -.01em; }
    .db-table th { text-align: left; padding: 8px 12px; font-size: 12px; font-weight: 500; color: var(--db-muted); letter-spacing: 0; border-bottom: .5px solid var(--db-separator); }
    .db-table td { padding: 11px 12px; border-bottom: .5px solid var(--db-separator); }
    .db-table td b { font-weight: 600; }
    .db-table tr:last-child td { border-bottom: 0; }
    .db-table .r { text-align: right; font-variant-numeric: tabular-nums; }
    .db-table tbody tr { transition: background .15s var(--db-ease); }
    .db-table tbody tr:hover { background: rgba(0, 0, 0, .025); }
    .db-bar { height: 6px; border-radius: 999px; background: var(--db-fill); overflow: hidden; margin-top: 6px; }
    .db-bar > span { display: block; height: 100%; border-radius: 999px; background: var(--db-accent); }
    .db-split { display: flex; gap: 2px; height: 10px; border-radius: 999px; overflow: hidden; background: var(--db-fill); margin-top: 12px; }
    .db-split > span:first-child { background: #0a9bb5; }
    .db-split > span:last-child { background: #5856d6; }
    .db-legend { display: flex; justify-content: space-between; margin-top: 8px; font-size: 13px; color: var(--db-muted); }
    .db-legend b { color: var(--db-text); font-weight: 600; }
    .db-pill { display: inline-block; padding: 2px 9px; border-radius: 999px; font-size: 12px; font-weight: 600; }
    .db-pill.busy { background: var(--db-green-soft); color: var(--db-green); }
    .db-pill.quiet { background: var(--db-red-soft); color: var(--db-red); }
    .db-proj { display: grid; gap: 16px; grid-template-columns: repeat(2, minmax(0, 1fr)); padding: 16px 22px 18px; }
    .db-proj b { display: block; font-size: 24px; font-weight: 600; letter-spacing: -.03em; color: var(--db-text); font-variant-numeric: tabular-nums; }
    .db-proj small { color: var(--db-muted); font-size: 12px; }

    /* ---- history & reports: grouped list cells ---------------------------------------------------- */
    .db-links { display: grid; gap: 12px; grid-template-columns: 1fr; }
    @media (min-width: 560px) { .db-links { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    @media (min-width: 1024px) { .db-links { grid-template-columns: repeat(4, minmax(0, 1fr)); } }
    .db-link { display: flex; align-items: center; gap: 12px; padding: 14px 16px; border-radius: var(--db-radius-sm); background: var(--db-surface); box-shadow: var(--db-shadow); color: var(--db-text); font-size: 15px; font-weight: 500; letter-spacing: -.01em; text-decoration: none; transition: box-shadow .25s var(--db-ease), transform .1s var(--db-ease); }
    .db-link::after { content: "›"; margin-left: auto; font-size: 20px; line-height: 1; color: var(--db-faint); }
    .db-link:hover { box-shadow: var(--db-shadow-hover); }
    .db-link:active { transform: scale(.98); }
    .db-link svg { flex: none; width: 30px; height: 30px; padding: 6px; border-radius: 8px; background: var(--db-accent-soft); color: var(--db-accent); }

    /* ---- charts ----------------------------------------------------------------------------------- */
    .apexcharts-tooltip.apexcharts-theme-light { border: 0 !important; border-radius: 12px !important; background: rgba(255, 255, 255, .92) !important; -webkit-backdrop-filter: blur(20px); backdrop-filter: blur(20px); box-shadow: 0 0 0 .5px rgba(0, 0, 0, .08), 0 10px 30px rgba(0, 0, 0, .12) !important; }
    .apexcharts-tooltip-title { background: transparent !important; border-bottom: .5px solid var(--db-separator) !important; font-weight: 600; }
    .apexcharts-legend-text { color: var(--db-muted) !important; font-weight: 500 !important; }

    /* ---- phones ----------------------------------------------------------------------------------- */
    @media (max-width: 639px) {
        .db-top { top: 6px; padding: 14px 16px; border-radius: var(--db-radius); }
        .db-top-row { flex-wrap: nowrap; align-items: flex-start; }
        .db-top-title { min-width: 0; }
        .db-top-title h1, .db-hero h1 { font-size: 28px; }
        .db-top.is-compact .db-top-row { align-items: center; }
        .db-top.is-compact .db-top-title h1 { font-size: 17px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .db-hero { padding: 20px; }
        .db-date { text-align: left; }
        .db-section { margin-top: 36px; font-size: 22px; }
        .db-card-head { flex-direction: column; align-items: flex-start; gap: 2px; padding: 16px 16px 0; }
        .db-card-head span { text-align: left; }
        .db-card-body { padding: 6px 6px 12px; }
        .db-kpi { padding: 16px 16px 12px; }
        .db-spark { margin-left: -16px; margin-right: -16px; margin-bottom: -12px; }
        .db-table th, .db-table td { padding: 10px 8px; white-space: nowrap; }
        .db-words { margin-left: 16px; margin-right: 16px; }
        .db-help { padding: 0 16px; }
        .db-proj { padding: 14px 16px; }
    }

    /* ---- motion & accessibility ------------------------------------------------------------------- */
    /* cards glide in as they scroll into view (classes are added by the script below, so without JS nothing is hidden) */
    .db-reveal { opacity: 0; transform: translateY(22px) scale(.985); }
    .db-reveal.is-in { opacity: 1; transform: none; transition: opacity .7s var(--db-ease) var(--d, 0ms), transform .9s var(--db-spring) var(--d, 0ms); }
    @media (prefers-reduced-motion: reduce) {
        .db-reveal { opacity: 1; transform: none; }
        .db-live i { animation: none; }
        .db-kpi:hover, .db-card:hover, .db-insight:hover, .db-kpi:hover .db-kpi-icon { transform: none; }
        .ak-page * { transition-duration: 0s !important; }
    }
    @media (prefers-reduced-transparency: reduce) {
        .db-top, .apexcharts-tooltip.apexcharts-theme-light { background: #fff !important; -webkit-backdrop-filter: none; backdrop-filter: none; }
    }
    @media (prefers-contrast: more) {
        .db-card, .db-kpi, .db-insight, .db-link, .db-help, .db-top, .db-hero { box-shadow: 0 0 0 1px #8e8e93; }
        :root { --db-muted: #3a3a3c; }
    }

    /* ---- print ------------------------------------------------------------------------------------ */
    @media print {
        @page { size: A4 landscape; margin: 8mm; }
        nav, .db-top-actions, .db-filter-panel, .db-help, .db-actions, .db-links { display: none !important; }
        .min-h-screen:has(.ak-page) { background: #fff; }
        .db-top { position: static; -webkit-backdrop-filter: none; backdrop-filter: none; }
        .db-kpi, .db-card, .db-insight { break-inside: avoid; box-shadow: 0 0 0 1px #d2d2d7 !important; animation: none !important; }
    }
</style>
<script>
    // Dashboard motion: cards glide in as they scroll into view (staggered within a row) and headline numbers
    // count up once. Skipped entirely when the system asks for reduced motion.
    document.addEventListener('DOMContentLoaded', () => {
        const page = document.querySelector('.ak-page');
        if (!page || window.matchMedia('(prefers-reduced-motion: reduce)').matches || !('IntersectionObserver' in window)) return;

        const reveal = new IntersectionObserver((entries) => entries.forEach(({ isIntersecting, target }) => {
            if (!isIntersecting) return;
            reveal.unobserve(target);
            target.classList.add('is-in');
            // hand the element back to its normal hover transitions once it has arrived
            setTimeout(() => target.classList.remove('db-reveal', 'is-in'), 1000 + parseInt(target.style.getPropertyValue('--d') || '0', 10));
        }), { rootMargin: '0px 0px -6% 0px', threshold: 0.06 });

        page.querySelectorAll('.db-kpi, .db-card, .db-insight, .db-section, .db-group, .db-help, .db-hero').forEach((el) => {
            const cell = el.parentElement.tagName === 'A' ? el.parentElement : el;
            const index = Array.prototype.indexOf.call(cell.parentElement.children, cell);
            el.style.setProperty('--d', Math.min(Math.max(index, 0), 5) * 70 + 'ms');
            el.classList.add('db-reveal');
            reveal.observe(el);
        });

        const easeOut = (t) => 1 - Math.pow(1 - t, 3);
        const countUp = (node, prefix, target, decimals, suffix) => {
            const format = (v) => v.toLocaleString('en-US', { minimumFractionDigits: decimals, maximumFractionDigits: decimals });
            const start = performance.now(), duration = 1100;
            const step = (now) => {
                const t = Math.min(1, (now - start) / duration);
                node.nodeValue = prefix + format(target * easeOut(t)) + suffix;
                if (t < 1) requestAnimationFrame(step);
            };
            requestAnimationFrame(step);
        };
        const counters = new IntersectionObserver((entries) => entries.forEach(({ isIntersecting, target }) => {
            if (!isIntersecting) return;
            counters.unobserve(target);
            const walker = document.createTreeWalker(target, NodeFilter.SHOW_TEXT);
            let node;
            while ((node = walker.nextNode()) && !/\d/.test(node.nodeValue)) { /* find the text that holds the number */ }
            const match = node && node.nodeValue.match(/^(\D*?)(\d[\d,]*(?:\.\d+)?)([\s\S]*)$/);
            if (!match) return;
            const value = parseFloat(match[2].replace(/,/g, ''));
            if (value > 0) countUp(node, match[1], value, (match[2].split('.')[1] || '').length, match[3]);
        }), { threshold: 0.4 });
        page.querySelectorAll('.db-kpi-value, .db-insight b, .db-proj b').forEach((el) => counters.observe(el));
    });
</script>
