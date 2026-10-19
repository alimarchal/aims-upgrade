{{--
    Delete / confirm dialog (Moon Traders style). Put x-data="confirmDialog()" on a wrapper,
    then on a form: @submit.prevent="ask($el, 'Delete X?', 'Message', 'Delete user')"
--}}
<div class="uf-modal" x-show="confirm.open" x-cloak style="display:none" @keydown.escape.window="confirm.open = false" role="dialog" aria-modal="true" aria-labelledby="ak-confirm-title">
    <div class="uf-modal-bg" x-show="confirm.open" x-transition.opacity @click="confirm.open = false"></div>
    <div class="uf-modal-box" x-show="confirm.open" x-transition>
        <div class="uf-modal-body">
            <span class="uf-modal-icon is-red" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.01" /></svg>
            </span>
            <div>
                <h3 id="ak-confirm-title" x-text="confirm.title"></h3>
                <p x-text="confirm.message"></p>
            </div>
        </div>
        <div class="uf-modal-foot">
            <button type="button" class="ak-btn" @click="confirm.open = false">Cancel</button>
            <button type="button" class="ak-btn ak-btn-danger" @click="run()" :disabled="confirm.busy" x-text="confirm.busy ? 'Working…' : confirm.button"></button>
        </div>
    </div>
</div>
@once
<script>
    function confirmDialog() {
        return {
            confirm: { open: false, title: '', message: '', button: 'Delete', busy: false, form: null },
            ask(form, title, message, button) {
                this.confirm = { open: true, title, message, button: button || 'Delete', busy: false, form };
            },
            run() {
                if (this.confirm.busy || !this.confirm.form) return;
                this.confirm.busy = true;
                this.confirm.form.submit();
            },
        };
    }
</script>
@endonce
