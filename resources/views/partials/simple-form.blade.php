{{--
    Single-card create / edit page (Moon Traders style) with a confirm-before-save modal.
    Pass: title, section (breadcrumb label), indexRoute, action, isCreate, fields, subtitle (optional), method (optional).
    fields: [ ['name','label','type'(text|number|select),'value','required','placeholder','help','options','step','id'] ]
--}}
@php
    $isCreate = $isCreate ?? true;
    $fields = collect($fields)->map(fn ($f) => $f + ['type' => 'text', 'value' => '', 'required' => false, 'placeholder' => '', 'help' => '', 'options' => [], 'step' => 'any'])->all();
@endphp
<x-app-layout>
    <x-slot name="header">
        <div class="ak-head">
            <div>
                <nav class="ak-crumbs" aria-label="Breadcrumb">
                    <a href="{{ route($indexRoute) }}">{{ $section }}</a><span aria-hidden="true">›</span><span>{{ $isCreate ? 'Add' : 'Edit' }}</span>
                </nav>
                <h1 class="ak-title">{{ $title }}</h1>
                @if (! empty($subtitle))<p class="ak-sub">{{ $subtitle }}</p>@endif
            </div>
            <div class="ak-actions">
                <a href="{{ route($indexRoute) }}" class="ak-btn"><span aria-hidden="true">←</span> Back to {{ $section }}</a>
            </div>
        </div>
    </x-slot>

    @include('partials.access-style')

    <div class="ak-page">
        @if ($errors->any())
            <div class="ak-alert ak-alert-err" role="alert">
                <b>Please fix the following before saving:</b>
                <ul style="margin:4px 0 0 18px;list-style:disc">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif

        <form method="POST" id="simple-form" novalidate action="{{ $action }}"
            x-data="{ review: false, saving: false, rows: [],
                openReview() {
                    const form = this.$el;
                    if (!form.reportValidity()) return;
                    this.rows = Array.from(form.querySelectorAll('[data-review]')).map((el) => ({
                        label: el.dataset.review,
                        value: el.tagName === 'SELECT' ? (el.selectedOptions[0]?.text || '—') : (el.value.trim() || '—'),
                    }));
                    this.saving = false;
                    this.review = true;
                },
                confirmSave() { if (this.saving) return; this.saving = true; this.$root.submit(); } }"
            @submit.prevent="openReview()">
            @csrf
            @unless ($isCreate) @method('PUT') @endunless

            <section class="uf-card" aria-labelledby="sf-card">
                <header class="uf-card-head">
                    <div>
                        <h2 class="uf-card-title" id="sf-card"><span class="uf-step">1</span> Details</h2>
                        <p class="uf-card-sub">Fields marked <span class="uf-req">*</span> are required.</p>
                    </div>
                </header>
                <div class="uf-body">
                    <div class="uf-fields">
                        @foreach ($fields as $field)
                            <div class="uf-field">
                                <label for="f_{{ $field['name'] }}">{{ $field['label'] }} @if ($field['required'])<span class="uf-req">*</span>@endif</label>
                                @if ($field['type'] === 'select')
                                    <select id="f_{{ $field['name'] }}" name="{{ $field['name'] }}" data-review="{{ $field['label'] }}" @required($field['required'])>
                                        <option value="">None</option>
                                        @foreach ($field['options'] as $value => $label)
                                            <option value="{{ $value }}" @selected((string) old($field['name'], $field['value']) === (string) $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                @else
                                    <input id="f_{{ $field['name'] }}" type="{{ $field['type'] }}" name="{{ $field['name'] }}" data-review="{{ $field['label'] }}"
                                        value="{{ old($field['name'], $field['value']) }}" placeholder="{{ $field['placeholder'] }}" @required($field['required'])
                                        @if ($field['type'] === 'number') step="{{ $field['step'] }}" @endif
                                        @if ($loop->first && $isCreate) autofocus @endif maxlength="255">
                                @endif
                                @if ($field['help'])<p class="uf-help">{{ $field['help'] }}</p>@endif
                                @error($field['name']) <p class="uf-error">{{ $message }}</p> @enderror
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>

            <div class="uf-actions" style="margin-top:20px">
                <p>You will confirm before anything is saved.</p>
                <div>
                    <a href="{{ route($indexRoute) }}" class="ak-btn">Cancel</a>
                    <button type="submit" class="ak-btn ak-btn-primary">Review &amp; save</button>
                </div>
            </div>

            <div class="uf-modal" x-show="review" x-cloak style="display:none" @keydown.escape.window="review = false" role="dialog" aria-modal="true">
                <div class="uf-modal-bg" x-show="review" x-transition.opacity @click="review = false"></div>
                <div class="uf-modal-box" x-show="review" x-transition>
                    <div class="uf-modal-body">
                        <span class="uf-modal-icon is-green" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                        </span>
                        <div style="flex:1;min-width:0">
                            <h3>{{ $isCreate ? 'Create this record?' : 'Save these changes?' }}</h3>
                            <div class="uf-review">
                                <dl>
                                    <template x-for="row in rows" :key="row.label">
                                        <div class="uf-wide"><dt x-text="row.label"></dt><dd x-text="row.value" style="word-break:break-word"></dd></div>
                                    </template>
                                </dl>
                            </div>
                        </div>
                    </div>
                    <div class="uf-modal-foot">
                        <button type="button" class="ak-btn" @click="review = false">Back to form</button>
                        <button type="button" class="ak-btn ak-btn-success" @click="confirmSave()" :disabled="saving">
                            <span x-show="!saving">{{ $isCreate ? 'Create' : 'Save changes' }}</span><span x-show="saving" x-cloak>Saving…</span>
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</x-app-layout>
