@php $isCreate = ! $role->exists; $isSuper = $role->name === 'Super-Admin'; @endphp
<x-app-layout>
    <x-slot name="header">
        <div class="ak-head">
            <div>
                <nav class="ak-crumbs" aria-label="Breadcrumb">
                    <a href="{{ route('roles.index') }}">Roles</a><span aria-hidden="true">›</span><span>{{ $isCreate ? 'Add' : 'Edit' }}</span>
                </nav>
                <h1 class="ak-title">{{ $isCreate ? 'Create Role' : 'Edit Role: '.$role->name }}</h1>
                <p class="ak-sub">Tick what users with this role may do by default.</p>
            </div>
            <div class="ak-actions"><a href="{{ route('roles.index') }}" class="ak-btn"><span aria-hidden="true">←</span> Back to Roles</a></div>
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

        <form method="POST" id="role-form" novalidate action="{{ $isCreate ? route('roles.store') : route('roles.update', $role) }}"
            x-data="{ ...permissionForm({ direct: {{ \Illuminate\Support\Js::from($checked) }} }), review: false, saving: false, name: {{ \Illuminate\Support\Js::from(old('name', $role->name ?? '')) }},
                openReview() { if (!this.$el.reportValidity()) return; this.saving = false; this.review = true; },
                confirmSave() { if (this.saving) return; this.saving = true; this.$root.submit(); } }"
            @submit.prevent="openReview()">
            @csrf
            @unless ($isCreate) @method('PUT') @endunless

            <section class="uf-card" aria-labelledby="rf-name">
                <header class="uf-card-head">
                    <div>
                        <h2 class="uf-card-title" id="rf-name"><span class="uf-step">1</span> Role name</h2>
                        <p class="uf-card-sub">Shown on the users list and in the user form.</p>
                    </div>
                </header>
                <div class="uf-body">
                    <div class="uf-field" style="max-width:26rem">
                        <label for="name">Name <span class="uf-req">*</span></label>
                        <input id="name" type="text" name="name" x-model="name" required maxlength="255" @readonly($isSuper) @if ($isCreate) autofocus @endif>
                        @error('name') <p class="uf-error">{{ $message }}</p> @enderror
                    </div>
                </div>
            </section>

            <section class="uf-card" style="margin-top:20px" aria-labelledby="rf-perms">
                <header class="uf-card-head">
                    <div>
                        <h2 class="uf-card-title" id="rf-perms"><span class="uf-step">2</span> Permissions</h2>
                        <p class="uf-card-sub"><span x-text="direct.length"></span> selected. Grouped like the menu.</p>
                    </div>
                </header>
                @if ($isSuper)
                    <div class="uf-body"><p class="uf-note uf-note-info">Super-Admin always has every permission and cannot be edited.</p></div>
                @else
                    @include('partials.permission-matrix', ['catalog' => $catalog])
                @endif
                @error('permissions') <p class="uf-error" style="padding:0 20px 16px">{{ $message }}</p> @enderror
            </section>

            <div class="uf-actions" style="margin-top:20px">
                <p>Fields marked <span class="uf-req">*</span> are required. You will confirm before saving.</p>
                <div>
                    <a href="{{ route('roles.index') }}" class="ak-btn">Cancel</a>
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
                            <h3>{{ $isCreate ? 'Create this role?' : 'Save changes to '.$role->name.'?' }}</h3>
                            <div class="uf-review">
                                <dl>
                                    <div><dt>Name</dt><dd x-text="name.trim() || '—'"></dd></div>
                                    <div><dt>Permissions</dt><dd x-text="{{ $isSuper ? "'All'" : 'direct.length' }}"></dd></div>
                                </dl>
                            </div>
                            @unless ($isCreate)
                                <p>Everyone with this role gets the new permission set the next time they open a page.</p>
                            @endunless
                        </div>
                    </div>
                    <div class="uf-modal-foot">
                        <button type="button" class="ak-btn" @click="review = false">Back to form</button>
                        <button type="button" class="ak-btn ak-btn-success" @click="confirmSave()" :disabled="saving">
                            <span x-show="!saving">{{ $isCreate ? 'Create role' : 'Save changes' }}</span><span x-show="saving" x-cloak>Saving…</span>
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</x-app-layout>
