@php $isCreate = ! $permission->exists; @endphp
<x-app-layout>
    <x-slot name="header">
        <div class="ak-head">
            <div>
                <nav class="ak-crumbs" aria-label="Breadcrumb">
                    <a href="{{ route('permissions.index') }}">Permissions</a><span aria-hidden="true">›</span><span>{{ $isCreate ? 'Add' : 'Edit' }}</span>
                </nav>
                <h1 class="ak-title">{{ $isCreate ? 'Create Permission' : 'Edit Permission: '.$permission->name }}</h1>
                <p class="ak-sub">Use the "view patients" style: action word first (view, create, edit, delete), then what it applies to.</p>
            </div>
            <div class="ak-actions"><a href="{{ route('permissions.index') }}" class="ak-btn"><span aria-hidden="true">←</span> Back to Permissions</a></div>
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

        <form method="POST" id="perm-form" novalidate action="{{ $isCreate ? route('permissions.store') : route('permissions.update', $permission) }}"
            x-data="{ review: false, saving: false, name: {{ \Illuminate\Support\Js::from(old('name', $permission->name ?? '')) }},
                openReview() { if (!this.$el.reportValidity()) return; this.saving = false; this.review = true; },
                confirmSave() { if (this.saving) return; this.saving = true; this.$root.submit(); } }"
            @submit.prevent="openReview()">
            @csrf
            @unless ($isCreate) @method('PUT') @endunless

            <section class="uf-card" aria-labelledby="pf-name">
                <header class="uf-card-head">
                    <div>
                        <h2 class="uf-card-title" id="pf-name"><span class="uf-step">1</span> Permission name</h2>
                        <p class="uf-card-sub">Lower case, for example "edit patients".</p>
                    </div>
                </header>
                <div class="uf-body">
                    @unless ($isCreate)
                        <p class="uf-note uf-note-warn" style="margin-bottom:16px">Renaming a permission breaks every screen that checks the old name until the code is changed too.</p>
                    @endunless
                    <div class="uf-field" style="max-width:26rem">
                        <label for="name">Name <span class="uf-req">*</span></label>
                        <input id="name" type="text" name="name" x-model="name" required maxlength="255" @if ($isCreate) autofocus @endif>
                        @error('name') <p class="uf-error">{{ $message }}</p> @enderror
                    </div>
                </div>
            </section>

            <div class="uf-actions" style="margin-top:20px">
                <p>Fields marked <span class="uf-req">*</span> are required. You will confirm before saving.</p>
                <div>
                    <a href="{{ route('permissions.index') }}" class="ak-btn">Cancel</a>
                    <button type="submit" class="ak-btn ak-btn-primary">Review &amp; save</button>
                </div>
            </div>

            <div class="uf-modal" x-show="review" x-cloak style="display:none" @keydown.escape.window="review = false" role="dialog" aria-modal="true">
                <div class="uf-modal-bg" x-show="review" x-transition.opacity @click="review = false"></div>
                <div class="uf-modal-box" x-show="review" x-transition>
                    <div class="uf-modal-body">
                        <span class="uf-modal-icon {{ $isCreate ? 'is-green' : 'is-amber' }}" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                        </span>
                        <div style="flex:1;min-width:0">
                            <h3>{{ $isCreate ? 'Create this permission?' : 'Rename this permission?' }}</h3>
                            <div class="uf-review">
                                <dl>
                                    @unless ($isCreate)<div class="uf-wide"><dt>Current name</dt><dd>{{ $permission->name }}</dd></div>@endunless
                                    <div class="uf-wide"><dt>{{ $isCreate ? 'Name' : 'New name' }}</dt><dd x-text="name.trim() || '—'"></dd></div>
                                </dl>
                            </div>
                        </div>
                    </div>
                    <div class="uf-modal-foot">
                        <button type="button" class="ak-btn" @click="review = false">Back to form</button>
                        <button type="button" class="ak-btn ak-btn-success" @click="confirmSave()" :disabled="saving">
                            <span x-show="!saving">{{ $isCreate ? 'Create permission' : 'Save changes' }}</span><span x-show="saving" x-cloak>Saving…</span>
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</x-app-layout>
