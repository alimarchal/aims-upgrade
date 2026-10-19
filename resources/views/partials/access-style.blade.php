{{-- Shared UI for Users / Roles / Permissions screens. Plain CSS (same look as Moon Traders settings) -- no Tailwind rebuild needed. --}}
@once
<style>
    :root {
        --ak-navy: #172554; --ak-navy-hover: #1e3a8a; --ak-navy-tint: #e0e7ff;
        --ak-green: #15803d; --ak-green-bg: #dcfce7; --ak-amber: #b45309; --ak-amber-bg: #fef3c7;
        --ak-red: #b91c1c; --ak-red-bg: #fee2e2;
        --ak-slate-bg: #f1f5f9; --ak-border: #cbd5e1; --ak-border-strong: #94a3b8; --ak-line: #e2e8f0;
        --ak-text: #0f172a; --ak-muted: #475569; --ak-soft: #f8fafc; --ak-radius: 12px;
        --ak-shadow: 0 1px 3px rgba(15,23,42,.08), 0 6px 18px -6px rgba(15,23,42,.14);
        --ak-shadow-hover: 0 4px 8px rgba(15,23,42,.08), 0 14px 28px -8px rgba(15,23,42,.18);
    }
    [x-cloak] { display: none !important; }
    .ak-page { max-width: 80rem; margin: 0 auto; padding: 1.5rem 1rem 3rem; }
    .ak-head { display: flex; flex-wrap: wrap; align-items: flex-end; justify-content: space-between; gap: 16px; }
    .ak-title { margin: 2px 0 0; font-size: 24px; line-height: 1.2; font-weight: 700; color: var(--ak-text); }
    .ak-sub { margin: 4px 0 0; font-size: 13px; color: var(--ak-muted); }
    .ak-actions { display: flex; flex-wrap: wrap; align-items: center; gap: 8px; }
    .ak-btn { display: inline-flex; align-items: center; gap: 6px; height: 38px; padding: 0 16px; border-radius: 8px; font-size: 13.5px; font-weight: 600;
        border: 1px solid var(--ak-border); background: #fff; color: var(--ak-text); cursor: pointer; text-decoration: none; box-shadow: 0 1px 2px rgba(15,23,42,.06); }
    .ak-btn:hover { background: var(--ak-soft); border-color: var(--ak-border-strong); }
    .ak-btn-primary { background: var(--ak-navy); border-color: var(--ak-navy); color: #fff; }
    .ak-btn-primary:hover { background: var(--ak-navy-hover); border-color: var(--ak-navy-hover); }
    .ak-btn-danger { border-color: #fca5a5; color: var(--ak-red); }
    .ak-btn-danger:hover { background: var(--ak-red-bg); }
    .ak-btn:disabled { opacity: .6; cursor: not-allowed; }
    .ak-alert { padding: 12px 16px; border-radius: 10px; margin-bottom: 16px; font-size: 14px; box-shadow: var(--ak-shadow); }
    .ak-alert-ok { background: var(--ak-green-bg); color: #14532d; border: 1px solid #86efac; }
    .ak-alert-err { background: var(--ak-red-bg); color: #7f1d1d; border: 1px solid #fca5a5; }

    .ak-kpis { display: grid; grid-template-columns: repeat(auto-fit, minmax(10rem, 1fr)); gap: 14px; margin: 18px 0; }
    .ak-kpi { display: block; background: #fff; border: 1px solid var(--ak-border); border-radius: var(--ak-radius); padding: 14px 16px; text-decoration: none; color: var(--ak-text);
        box-shadow: var(--ak-shadow); transition: box-shadow .15s, border-color .15s, transform .15s; }
    a.ak-kpi:hover { border-color: var(--ak-border-strong); box-shadow: var(--ak-shadow-hover); transform: translateY(-1px); }
    .ak-kpi.is-active { border-color: var(--ak-navy); box-shadow: 0 0 0 1px var(--ak-navy), var(--ak-shadow); }
    .ak-kpi b { display: block; font-size: 26px; line-height: 1.1; }
    .ak-kpi span { font-size: 12px; color: var(--ak-muted); text-transform: uppercase; letter-spacing: .04em; font-weight: 600; }

    .ak-card { background: #fff; border: 1px solid var(--ak-border); border-radius: var(--ak-radius); box-shadow: var(--ak-shadow); margin-bottom: 18px; overflow: hidden; }
    .ak-card-head { padding: 14px 18px; border-bottom: 1px solid var(--ak-line); font-weight: 700; font-size: 15px; color: var(--ak-text);
        display: flex; justify-content: space-between; align-items: center; gap: 8px; flex-wrap: wrap; }
    .ak-card-head .ak-muted { font-weight: 400; }
    .ak-card-body { padding: 18px; }
    .ak-filter { display: flex; flex-wrap: wrap; gap: 10px; align-items: center; padding: 14px 18px; background: #fff; border: 1px solid var(--ak-border); border-radius: var(--ak-radius); box-shadow: var(--ak-shadow); margin-bottom: 18px; }
    .ak-input, .ak-select { height: 38px; border: 1px solid var(--ak-border); border-radius: 8px; padding: 0 12px; font-size: 14px; background: #fff; min-width: 9rem; color: var(--ak-text); }
    .ak-input:focus, .ak-select:focus { outline: none; border-color: #4f46e5; box-shadow: 0 0 0 3px rgba(79,70,229,.2); }
    .ak-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(16rem, 1fr)); gap: 16px; }
    .ak-field label { display: block; font-size: 12.5px; font-weight: 600; color: var(--ak-text); margin-bottom: 5px; }
    .ak-field .ak-input, .ak-field .ak-select { width: 100%; }

    .ak-table { width: 100%; border-collapse: collapse; font-size: 14px; }
    .ak-table th { background: var(--ak-soft); text-align: left; padding: 11px 14px; font-size: 11.5px; text-transform: uppercase; letter-spacing: .05em; color: var(--ak-muted); border-bottom: 1px solid var(--ak-line); white-space: nowrap; }
    .ak-table td { padding: 12px 14px; border-bottom: 1px solid var(--ak-line); vertical-align: middle; color: var(--ak-text); }
    .ak-table tbody tr:hover td { background: var(--ak-soft); }
    .ak-table tbody tr:last-child td { border-bottom: 0; }
    .ak-badge { display: inline-block; padding: 2px 10px; border-radius: 999px; font-size: 12px; font-weight: 600; background: var(--ak-navy-tint); color: var(--ak-navy); margin: 2px 4px 2px 0; }
    .ak-badge-ok { background: var(--ak-green-bg); color: #14532d; }
    .ak-badge-off { background: var(--ak-red-bg); color: #7f1d1d; }
    .ak-badge-warn { background: var(--ak-amber-bg); color: #92400e; }
    .ak-badge-direct { background: #ede9fe; color: #5b21b6; }
    .ak-rowact { display: flex; gap: 14px; align-items: center; justify-content: center; }
    .ak-rowact form { margin: 0; }
    .ak-link { color: var(--ak-navy-hover); font-size: 13px; font-weight: 600; background: none; border: 0; cursor: pointer; text-decoration: none; padding: 0; }
    .ak-link:hover { text-decoration: underline; }
    .ak-link-danger { color: var(--ak-red); }
    .ak-muted { color: var(--ak-muted); font-size: 13px; }

    .ak-roles { display: grid; grid-template-columns: repeat(auto-fill, minmax(15rem, 1fr)); gap: 10px; }
    .ak-role { display: flex; gap: 10px; align-items: flex-start; border: 1px solid var(--ak-border); border-radius: 10px; padding: 10px 12px; cursor: pointer; font-size: 14px; background: #fff; }
    .ak-role.is-on { border-color: var(--ak-green); background: #f0fdf4; box-shadow: 0 0 0 1px var(--ak-green); }
    .ak-mod { border: 1px solid var(--ak-border); border-radius: 10px; margin-bottom: 12px; overflow: hidden; box-shadow: 0 1px 2px rgba(15,23,42,.05); }
    .ak-mod-head { display: flex; justify-content: space-between; align-items: center; background: var(--ak-slate-bg); padding: 9px 14px; font-weight: 700; font-size: 13.5px; }
    .ak-perm-row { display: flex; gap: 18px; flex-wrap: wrap; align-items: center; padding: 9px 14px; border-top: 1px solid var(--ak-line); font-size: 13.5px; }
    .ak-perm-label { width: 15rem; font-weight: 600; color: var(--ak-text); }
    .ak-perm { display: inline-flex; gap: 6px; align-items: center; color: var(--ak-muted); }
    .ak-perm.is-inherited { color: var(--ak-green); }
    .ak-perm.is-locked { opacity: .55; }
    .ak-sticky { position: sticky; bottom: 0; display: flex; justify-content: space-between; gap: 8px; padding: 12px 18px; z-index: 5;
        background: rgba(255,255,255,.96); border: 1px solid var(--ak-border); border-radius: var(--ak-radius); box-shadow: 0 -4px 16px rgba(15,23,42,.08); backdrop-filter: blur(4px); }
    .ak-pager { margin-top: 16px; }

    /* Confirm / review modal (same as Moon Traders) */
    .uf-modal { position: fixed; inset: 0; z-index: 60; display: flex; align-items: center; justify-content: center; padding: 16px; }
    .uf-modal-bg { position: absolute; inset: 0; background: rgba(15,23,42,.45); backdrop-filter: blur(2px); }
    .uf-modal-box { position: relative; width: 100%; max-width: 560px; max-height: calc(100vh - 32px); overflow: auto; background: #fff; border-radius: 14px; box-shadow: 0 25px 50px rgba(0,0,0,.25); }
    .uf-modal-body { display: flex; gap: 16px; padding: 22px 22px 16px; }
    .uf-modal-icon { flex: none; display: inline-flex; align-items: center; justify-content: center; width: 42px; height: 42px; border-radius: 50%; }
    .uf-modal-icon svg { width: 22px; height: 22px; }
    .uf-modal-icon.is-red { background: var(--ak-red-bg); color: var(--ak-red); }
    .uf-modal-icon.is-amber { background: var(--ak-amber-bg); color: var(--ak-amber); }
    .uf-modal-icon.is-green { background: var(--ak-green-bg); color: var(--ak-green); }
    .uf-modal h3 { margin: 0; font-size: 17px; font-weight: 700; color: var(--ak-text); }
    .uf-modal p { margin: 8px 0 0; font-size: 14px; color: #334155; }
    .uf-review { margin-top: 12px; padding: 12px; border: 1px solid var(--ak-border); border-radius: 8px; background: var(--ak-soft); }
    .uf-review dl { margin: 0; display: grid; grid-template-columns: 1fr 1fr; gap: 8px 14px; font-size: 13px; }
    .uf-review dt { color: var(--ak-muted); }
    .uf-review dd { margin: 0; font-weight: 700; color: var(--ak-text); }
    .uf-review .uf-wide { grid-column: 1 / -1; }
    .uf-modal-foot { display: flex; justify-content: flex-end; gap: 10px; padding: 14px 22px; background: var(--ak-slate-bg); border-radius: 0 0 14px 14px; }

    /* ---------------- Forms (user add / edit) ---------------- */
    .ak-crumbs { display: flex; gap: 6px; font-size: 12px; color: var(--ak-muted); }
    .ak-crumbs a { color: var(--ak-muted); text-decoration: none; }
    .ak-crumbs a:hover { color: var(--ak-text); text-decoration: underline; }
    .ak-avatar { flex: none; display: inline-flex; align-items: center; justify-content: center; width: 34px; height: 34px; border-radius: 50%; background: var(--ak-navy-tint); color: var(--ak-navy); font-size: 12px; font-weight: 700; }
    .uf-grid { display: grid; gap: 20px; grid-template-columns: 1fr; }
    @media (min-width: 1024px) { .uf-grid { grid-template-columns: minmax(0, 2fr) minmax(0, 1fr); } }
    .uf-card { background: #fff; border: 1px solid var(--ak-border); border-radius: var(--ak-radius); box-shadow: var(--ak-shadow); }
    .uf-card-head { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 12px; padding: 14px 20px; border-bottom: 1px solid var(--ak-line); background: var(--ak-soft); border-radius: var(--ak-radius) var(--ak-radius) 0 0; }
    .uf-card-title { margin: 0; display: flex; align-items: center; gap: 10px; font-size: 15px; font-weight: 700; color: var(--ak-text); }
    .uf-step { display: inline-flex; align-items: center; justify-content: center; width: 24px; height: 24px; border-radius: 50%; background: var(--ak-navy); color: #fff; font-size: 12px; font-weight: 700; }
    .uf-card-sub { margin: 2px 0 0 34px; font-size: 12.5px; color: var(--ak-muted); }
    .uf-body { padding: 20px; }
    .uf-fields { display: grid; gap: 16px; grid-template-columns: 1fr; }
    @media (min-width: 768px) { .uf-fields { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    .uf-field label { display: block; margin-bottom: 6px; font-size: 13px; font-weight: 600; color: var(--ak-text); }
    .uf-field input, .uf-field select { width: 100%; height: 40px; padding: 0 12px; border: 1px solid var(--ak-border-strong); border-radius: 8px; background: #fff; color: var(--ak-text); font-size: 14px; }
    .uf-field input:focus, .uf-field select:focus { outline: none; border-color: #4f46e5; box-shadow: 0 0 0 3px rgba(79,70,229,.2); }
    .uf-req { color: #dc2626; }
    .uf-error { margin: 5px 0 0; font-size: 12.5px; color: #b91c1c; font-weight: 600; }
    .uf-help { margin: 5px 0 0; font-size: 12px; color: var(--ak-muted); }
    .uf-pass { position: relative; }
    .uf-pass input { padding-right: 64px; }
    .uf-pass button { position: absolute; right: 4px; top: 4px; height: 32px; padding: 0 10px; border: 0; border-radius: 6px; background: transparent; font-size: 12px; font-weight: 600; color: var(--ak-muted); cursor: pointer; }
    .uf-pass button:hover { background: var(--ak-slate-bg); color: var(--ak-text); }
    .uf-summary dl { margin: 0; display: flex; flex-direction: column; gap: 10px; font-size: 13.5px; }
    .uf-summary dl > div { display: flex; justify-content: space-between; gap: 12px; }
    .uf-summary dt { color: var(--ak-muted); }
    .uf-summary dd { margin: 0; font-weight: 700; color: var(--ak-text); text-align: right; }
    .uf-summary .uf-total { padding-top: 10px; border-top: 1px dashed var(--ak-line); }
    .uf-bar { height: 6px; border-radius: 999px; background: var(--ak-line); overflow: hidden; margin-bottom: 8px; }
    .uf-bar span { display: block; height: 100%; background: var(--ak-navy); border-radius: 999px; transition: width .2s; }
    .uf-mod { font-size: 12.5px; }
    .uf-mod > div > div:first-child { display: flex; justify-content: space-between; margin-bottom: 4px; }
    .uf-note { padding: 10px 14px; border-radius: 8px; font-size: 13px; }
    .uf-note-info { background: #eff6ff; border: 1px solid #bfdbfe; color: #1e3a8a; }
    .uf-note-warn { background: var(--ak-amber-bg); border: 1px solid #fcd34d; color: #78350f; }
    .uf-roles { display: grid; gap: 12px; grid-template-columns: 1fr; }
    @media (min-width: 640px) { .uf-roles { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    @media (min-width: 1024px) { .uf-roles { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
    .uf-role { display: flex; align-items: flex-start; gap: 10px; padding: 12px 14px; border: 1px solid var(--ak-border); border-radius: 10px; background: #fff; cursor: pointer; transition: border-color .15s, background .15s; }
    .uf-role:hover { border-color: var(--ak-border-strong); }
    .uf-role.is-on { border-color: var(--ak-green); background: #f0fdf4; box-shadow: 0 0 0 1px var(--ak-green); }
    .uf-role input { margin-top: 2px; width: 16px; height: 16px; border-radius: 4px; }
    .uf-role b { display: block; font-size: 14px; color: var(--ak-text); }
    .uf-role small { display: block; font-size: 12px; color: var(--ak-muted); }
    .uf-actions { position: sticky; bottom: 0; z-index: 20; display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 12px; padding: 12px 20px;
        background: rgba(255,255,255,.96); border: 1px solid var(--ak-border); border-radius: var(--ak-radius); box-shadow: 0 -4px 16px rgba(15,23,42,.08); backdrop-filter: blur(4px); }
    .uf-actions p { margin: 0; font-size: 12.5px; color: var(--ak-muted); }
    .uf-actions div { display: flex; gap: 10px; }
    .uf-changed { display: inline-block; margin-left: 6px; padding: 1px 8px; border-radius: 999px; background: var(--ak-amber-bg); color: #92400e; font-weight: 700; }
    .uf-diff { margin: 10px 0 0; padding: 0; list-style: none; font-size: 12.5px; }
    .uf-diff li { padding: 2px 0; }
    .uf-diff .add { color: #166534; }
    .uf-diff .rem { color: #b91c1c; }
    .uf-warn { margin: 10px 0 0; padding: 0; list-style: none; font-size: 13px; color: #92400e; }
    .ak-btn-success { background: var(--ak-green); border-color: var(--ak-green); color: #fff; }
    .ak-btn-success:hover { background: #166534; border-color: #166534; }
    @media (max-width: 640px) { .ak-perm-label { width: 100%; } }
</style>
@endonce
