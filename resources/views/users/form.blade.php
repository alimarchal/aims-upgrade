@php
    $isCreate = ! $user->exists;
    $isSelf = ! $isCreate && $user->is(auth()->user());
    $statusValue = (string) old('status', $isCreate ? '1' : (int) $user->status);

    $rolePermissions = $roles->mapWithKeys(fn ($role) => [$role->id => $role->permissions->pluck('id')->values()]);
    $roleNames = $roles->mapWithKeys(fn ($role) => [$role->id => $role->name]);
    $roleHelp = [
        'Super-Admin' => 'Full access to everything',
        'Administrator' => 'Dashboards, reports and patient edit',
        'Front Desk/Receptionist' => 'Register patients, chits, invoices, admissions',
    ];

    $labels = [];
    $modules = [];
    foreach ($catalog as $module) {
        foreach ($module['groups'] as $group) {
            foreach ($group['permissions'] as $permission) {
                $labels[$permission['id']] = $group['label'].' › '.ucfirst($permission['action']);
                $modules[$module['label']][] = $permission['id'];
            }
        }
    }

    $original = [
        'roles' => $isCreate ? [] : array_map('intval', $user->roles->pluck('id')->all()),
        'permissions' => $isCreate ? [] : array_map('intval', $user->permissions->pluck('id')->all()),
        'status' => $isCreate ? '1' : (string) (int) $user->status,
    ];
    $initials = collect(explode(' ', trim((string) $user->name)))->filter()->take(2)->map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)))->implode('');
@endphp
<x-app-layout>
    <x-slot name="header">
        <div class="ak-head">
            <div>
                <nav class="ak-crumbs" aria-label="Breadcrumb">
                    <a href="{{ route('users.index') }}">Users</a><span aria-hidden="true">›</span><span>{{ $isCreate ? 'Add' : 'Edit' }}</span>
                </nav>
                <h1 class="ak-title">{{ $isCreate ? 'Add New User' : 'Edit User: '.$user->name }}</h1>
                <p class="ak-sub">{{ $isCreate ? 'Create the login, pick roles, then add any extra permissions.' : $user->email }}</p>
            </div>
            <div class="ak-actions">
                @unless ($isCreate)
                    <a href="{{ route('users.show', $user) }}" class="ak-btn">View profile</a>
                @endunless
                <a href="{{ route('users.index') }}" class="ak-btn"><span aria-hidden="true">←</span> Back to Users</a>
            </div>
        </div>
    </x-slot>

    @include('partials.access-style')

    <div class="ak-page">
        @if (session('error'))
            <div class="ak-alert ak-alert-err" role="alert">{{ session('error') }}</div>
        @endif
        @if ($errors->any())
            <div class="ak-alert ak-alert-err" role="alert">
                <b>Please fix the following before saving:</b>
                <ul style="margin:4px 0 0 18px;list-style:disc">
                    @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </div>
        @endif

        <form method="POST" id="user-form" novalidate autocomplete="off"
            action="{{ $isCreate ? route('users.store') : route('users.update', $user) }}"
            x-data="userForm({
                direct: {{ \Illuminate\Support\Js::from(array_map('intval', old('permissions', $original['permissions']))) }},
                roles: {{ \Illuminate\Support\Js::from(array_map('intval', old('roles', $original['roles']))) }},
                rolePermissions: {{ \Illuminate\Support\Js::from($rolePermissions) }},
                locked: {{ \Illuminate\Support\Js::from($locked) }},
                roleMap: {{ \Illuminate\Support\Js::from($roleNames) }},
                labels: {{ \Illuminate\Support\Js::from($labels) }},
                modules: {{ \Illuminate\Support\Js::from($modules) }},
                original: {{ \Illuminate\Support\Js::from($original) }},
                status: {{ \Illuminate\Support\Js::from($statusValue) }},
                isCreate: {{ $isCreate ? 'true' : 'false' }},
                name: {{ \Illuminate\Support\Js::from(old('name', $user->name ?? '')) }},
                email: {{ \Illuminate\Support\Js::from(old('email', $user->email ?? '')) }},
            })"
            @submit.prevent="openReview()">
            @csrf
            @unless ($isCreate) @method('PUT') @endunless

            <div class="uf-grid">
                <section class="uf-card" aria-labelledby="uf-account">
                    <header class="uf-card-head">
                        <div>
                            <h2 class="uf-card-title" id="uf-account"><span class="uf-step">1</span> Account</h2>
                            <p class="uf-card-sub">Login details and whether the user can sign in.</p>
                        </div>
                    </header>
                    <div class="uf-body">
                        <div class="uf-fields">
                            <div class="uf-field">
                                <label for="name">Full name <span class="uf-req">*</span></label>
                                <input id="name" type="text" name="name" x-model="name" required maxlength="255" autocomplete="off" @if ($isCreate) autofocus @endif>
                                @error('name') <p class="uf-error">{{ $message }}</p> @enderror
                            </div>
                            <div class="uf-field">
                                <label for="email">Email (login) <span class="uf-req">*</span></label>
                                <input id="email" type="email" name="email" x-model="email" required autocomplete="off">
                                @error('email') <p class="uf-error">{{ $message }}</p> @enderror
                            </div>
                            <div class="uf-field" x-data="{ show: false }">
                                <label for="password">{{ $isCreate ? 'Password' : 'New password' }} @if ($isCreate)<span class="uf-req">*</span>@endif</label>
                                <div class="uf-pass">
                                    <input id="password" :type="show ? 'text' : 'password'" name="password" minlength="8" autocomplete="new-password" @required($isCreate)
                                        x-model="password" placeholder="{{ $isCreate ? 'At least 8 characters' : 'Leave blank to keep current' }}">
                                    <button type="button" @click="show = !show" x-text="show ? 'Hide' : 'Show'"></button>
                                </div>
                                <p class="uf-help">Minimum 8 characters.{{ $isCreate ? '' : ' Leave blank to keep the current password.' }}</p>
                                @error('password') <p class="uf-error">{{ $message }}</p> @enderror
                            </div>
                            <div class="uf-field">
                                <label for="password_confirmation">Confirm password</label>
                                <input id="password_confirmation" type="password" name="password_confirmation" autocomplete="new-password">
                            </div>
                            <div class="uf-field">
                                <label for="department_id">Department</label>
                                <select id="department_id" name="department_id">
                                    <option value="">No department</option>
                                    @foreach ($departments as $id => $name)
                                        <option value="{{ $id }}" @selected((string) old('department_id', $user->department_id) === (string) $id)>{{ $name }}</option>
                                    @endforeach
                                </select>
                                @error('department_id') <p class="uf-error">{{ $message }}</p> @enderror
                            </div>
                            <div class="uf-field">
                                <label for="status">Status <span class="uf-req">*</span></label>
                                <select id="status" name="status" x-model="status" @if ($isSelf) title="You cannot deactivate your own account" @endif>
                                    <option value="1">Active</option>
                                    <option value="0" @disabled($isSelf)>Inactive</option>
                                </select>
                                @error('status') <p class="uf-error">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>
                </section>

                <aside class="uf-card uf-summary" aria-labelledby="uf-summary">
                    <header class="uf-card-head">
                        <div>
                            <h2 class="uf-card-title" id="uf-summary">
                                <span class="ak-avatar" aria-hidden="true" x-text="initials()">{{ $initials ?: '+' }}</span>
                                Access summary
                            </h2>
                            <p class="uf-card-sub">Updates as you change roles and permissions.</p>
                        </div>
                    </header>
                    <div class="uf-body" style="display:flex;flex-direction:column;gap:16px">
                        <dl>
                            <div><dt>Status</dt><dd :style="status === '1' ? 'color:#15803d' : 'color:#b91c1c'" x-text="status === '1' ? 'Active' : 'Inactive'"></dd></div>
                            <div><dt>Roles</dt><dd x-text="roleNames().join(', ') || 'None'"></dd></div>
                            <div><dt>From roles</dt><dd x-text="inheritedSet().size"></dd></div>
                            <div><dt>Extra (individual)</dt><dd x-text="direct.length"></dd></div>
                            <div class="uf-total"><dt><b>Total access</b></dt><dd x-text="totalCount() + ' permissions'"></dd></div>
                        </dl>
                        <div class="uf-mod">
                            <template x-for="(ids, label) in modules" :key="label">
                                <div>
                                    <div><span x-text="label"></span><b x-text="grantedIn(ids) + ' / ' + ids.length"></b></div>
                                    <div class="uf-bar"><span :style="'width:' + (ids.length ? Math.round(grantedIn(ids) / ids.length * 100) : 0) + '%'"></span></div>
                                </div>
                            </template>
                        </div>
                        <p class="uf-note uf-note-warn" x-show="hasRole('Super-Admin')" x-cloak>Super-Admin skips every permission check; the permissions below do not limit this user.</p>
                        <p class="uf-note uf-note-warn" x-show="status === '0'" x-cloak>Inactive users cannot sign in.</p>
                    </div>
                </aside>
            </div>

            <section class="uf-card" style="margin-top:20px" aria-labelledby="uf-roles">
                <header class="uf-card-head">
                    <div>
                        <h2 class="uf-card-title" id="uf-roles"><span class="uf-step">2</span> Roles</h2>
                        <p class="uf-card-sub">Start with a role: it gives a ready set of permissions. Use step 3 only for what the role does not cover.</p>
                    </div>
                    @can('view roles')
                        <a href="{{ route('roles.index') }}" class="ak-btn" target="_blank" rel="noopener">Manage roles ↗</a>
                    @endcan
                </header>
                <div class="uf-body">
                    <div class="uf-roles">
                        @foreach ($roles as $role)
                            <label class="uf-role" :class="roles.includes({{ $role->id }}) && 'is-on'">
                                <input type="checkbox" name="roles[]" value="{{ $role->id }}"
                                    :checked="roles.includes({{ $role->id }})" @change="toggleRole({{ $role->id }}, $event.target.checked)">
                                <span>
                                    <b>{{ $role->name }}</b>
                                    <small>{{ $roleHelp[$role->name] ?? 'Custom role' }} &middot; {{ $role->permissions->count() }} permissions</small>
                                </span>
                            </label>
                        @endforeach
                    </div>
                    @error('roles') <p class="uf-error">{{ $message }}</p> @enderror
                    <p class="uf-note uf-note-info" style="margin-top:14px" x-show="roles.length === 0" x-cloak>Pick at least one role. Without a role the user cannot be saved.</p>
                </div>
            </section>

            <section class="uf-card" style="margin-top:20px" aria-labelledby="uf-perms">
                <header class="uf-card-head">
                    <div>
                        <h2 class="uf-card-title" id="uf-perms"><span class="uf-step">3</span> Extra permissions</h2>
                        <p class="uf-card-sub">Grouped like the menu. Green "(role)" items already come from a role; tick others to give more.</p>
                    </div>
                </header>
                @include('partials.permission-matrix', ['catalog' => $catalog])
                @error('permissions') <p class="uf-error" style="padding:0 20px 16px">{{ $message }}</p> @enderror
            </section>

            <div class="uf-actions" style="margin-top:20px">
                <p>
                    Fields marked <span class="uf-req">*</span> are required. You will review the changes before they are saved.
                    <span class="uf-changed" x-show="!isCreate && changeCount() > 0" x-cloak x-text="changeCount() + ' unsaved ' + (changeCount() === 1 ? 'change' : 'changes')"></span>
                </p>
                <div>
                    <a href="{{ route('users.index') }}" class="ak-btn">Cancel</a>
                    <button type="submit" class="ak-btn ak-btn-primary">Review &amp; save</button>
                </div>
            </div>

            <div class="uf-modal" x-show="review" x-cloak style="display:none" @keydown.escape.window="review = false" role="dialog" aria-modal="true" aria-labelledby="uf-review-title">
                <div class="uf-modal-bg" x-show="review" x-transition.opacity @click="review = false"></div>
                <div class="uf-modal-box" x-show="review" x-transition>
                    <div class="uf-modal-body">
                        <span class="uf-modal-icon" :class="warnings().length ? 'is-amber' : 'is-green'" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                        </span>
                        <div style="flex:1;min-width:0">
                            <h3 id="uf-review-title">{{ $isCreate ? 'Create this user?' : 'Save changes to '.$user->name.'?' }}</h3>
                            <div class="uf-review">
                                <dl>
                                    <div><dt>Name</dt><dd x-text="name.trim() || '—'"></dd></div>
                                    <div><dt>Email</dt><dd x-text="email.trim() || '—'" style="word-break:break-all"></dd></div>
                                    <div><dt>Status</dt><dd x-text="status === '1' ? 'Active' : 'Inactive'"></dd></div>
                                    <div><dt>Total access</dt><dd x-text="totalCount()"></dd></div>
                                    <div class="uf-wide"><dt>Roles</dt><dd x-text="roleNames().join(', ') || 'None'"></dd></div>
                                    <div><dt>Extra permissions</dt><dd x-text="direct.length"></dd></div>
                                    <div><dt>Password</dt><dd x-text="password ? '{{ $isCreate ? 'Set' : 'Will be changed' }}' : '{{ $isCreate ? 'Not set' : 'Unchanged' }}'"></dd></div>
                                </dl>
                                <ul class="uf-diff" x-show="!isCreate && diff().length">
                                    <template x-for="line in diff().slice(0, 12)" :key="line.text">
                                        <li :class="line.type" x-text="(line.type === 'add' ? '+ ' : '− ') + line.text"></li>
                                    </template>
                                    <li x-show="diff().length > 12" x-text="'… and ' + (diff().length - 12) + ' more'"></li>
                                </ul>
                            </div>
                            <ul class="uf-warn" x-show="warnings().length">
                                <template x-for="w in warnings()" :key="w"><li>⚠ <span x-text="w"></span></li></template>
                            </ul>
                        </div>
                    </div>
                    <div class="uf-modal-foot">
                        <button type="button" class="ak-btn" @click="review = false">Back to form</button>
                        <button type="button" class="ak-btn ak-btn-success" @click="confirmSave()" :disabled="saving || roles.length === 0">
                            <span x-show="!saving">{{ $isCreate ? 'Create user' : 'Save changes' }}</span><span x-show="saving" x-cloak>Saving…</span>
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <script>
        function userForm(c) {
            return Object.assign(permissionForm(c), {
                roleMap: c.roleMap, labels: c.labels, modules: c.modules, original: c.original,
                status: c.status, isCreate: c.isCreate, name: c.name || '', email: c.email || '',
                password: '', review: false, saving: false,
                toggleRole(id, on) {
                    if (on) { if (!this.roles.includes(id)) this.roles.push(id); }
                    else { this.roles = this.roles.filter((x) => x !== id); }
                },
                initials() {
                    const parts = this.name.trim().split(/\s+/).filter(Boolean).slice(0, 2);
                    return parts.map((p) => p[0].toUpperCase()).join('') || '+';
                },
                hasRole(name) { return this.roles.some((id) => this.roleMap[id] === name); },
                roleNames() { return this.roles.map((id) => this.roleMap[id]).filter(Boolean); },
                grantedIn(ids) { return ids.filter((id) => this.isOn(id)).length; },
                totalCount() {
                    const all = new Set(this.inheritedSet());
                    this.direct.forEach((id) => all.add(id));
                    return all.size;
                },
                diff() {
                    const lines = [];
                    const label = (id) => this.labels[id] || ('#' + id);
                    this.roles.filter((id) => !this.original.roles.includes(id)).forEach((id) => lines.push({ type: 'add', text: 'Role ' + this.roleMap[id] }));
                    this.original.roles.filter((id) => !this.roles.includes(id)).forEach((id) => lines.push({ type: 'rem', text: 'Role ' + (this.roleMap[id] || '#' + id) }));
                    this.direct.filter((id) => !this.original.permissions.includes(id)).forEach((id) => lines.push({ type: 'add', text: label(id) }));
                    this.original.permissions.filter((id) => !this.direct.includes(id)).forEach((id) => lines.push({ type: 'rem', text: label(id) }));
                    return lines;
                },
                changeCount() {
                    return this.diff().length + (this.status !== this.original.status ? 1 : 0) + (this.password ? 1 : 0);
                },
                warnings() {
                    const list = [];
                    if (this.status === '0' && this.original.status !== '0') list.push('The user will be made inactive and can no longer sign in.');
                    if (this.roles.length === 0) list.push('No role selected: pick at least one role before saving.');
                    if (this.hasRole('Super-Admin')) list.push('Super-Admin gives access to everything.');
                    if (this.original.roles.some((id) => !this.roles.includes(id))) list.push('A role is being removed; the user loses permissions that came only from it.');
                    return list;
                },
                openReview() {
                    const form = document.getElementById('user-form');
                    if (!form.reportValidity()) return;
                    this.saving = false;
                    this.review = true;
                },
                confirmSave() {
                    if (this.saving) return;
                    this.saving = true;
                    document.getElementById('user-form').submit();
                },
            });
        }
    </script>
</x-app-layout>
