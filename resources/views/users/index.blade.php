<x-app-layout>
    <x-slot name="header">
        <div class="ak-head">
            <div>
                <h2 class="ak-title">Users</h2>
                <p class="ak-sub">Who can sign in, their roles and what they can open.</p>
            </div>
            <div class="ak-actions">
                @can('view roles')
                    <a href="{{ route('roles.index') }}" class="ak-btn">Roles &amp; permissions</a>
                @endcan
                @can('create users')
                    <a href="{{ route('users.create') }}" class="ak-btn ak-btn-primary">+ Add user</a>
                @endcan
            </div>
        </div>
    </x-slot>

    @include('partials.access-style')

    @php
        $tabUrl = fn (string $key) => route('users.index', ['filter' => array_merge($filters, ['status' => $key])]);
    @endphp

    <div class="ak-page" x-data="confirmDialog()">
        @if (session('status'))
            <div class="ak-alert ak-alert-ok">{{ session('status') }}</div>
        @endif
        @if (session('error'))
            <div class="ak-alert ak-alert-err">{{ session('error') }}</div>
        @endif

        <div class="ak-kpis">
            <a href="{{ $tabUrl('') }}" class="ak-kpi {{ $tab === '' ? 'is-active' : '' }}"><b>{{ $stats['total'] }}</b><span>All users</span></a>
            <a href="{{ $tabUrl('1') }}" class="ak-kpi {{ $tab === '1' ? 'is-active' : '' }}"><b>{{ $stats['active'] }}</b><span>Active</span></a>
            <a href="{{ $tabUrl('0') }}" class="ak-kpi {{ $tab === '0' ? 'is-active' : '' }}"><b>{{ $stats['inactive'] }}</b><span>Inactive</span></a>
            <a href="{{ $tabUrl('no_role') }}" class="ak-kpi {{ $tab === 'no_role' ? 'is-active' : '' }}"><b>{{ $stats['no_role'] }}</b><span>No role</span></a>
        </div>

        <form method="GET" action="{{ route('users.index') }}" class="ak-filter">
            <input type="hidden" name="filter[status]" value="{{ $tab }}">
            <input type="search" name="filter[search]" value="{{ $filters['search'] ?? '' }}" class="ak-input" style="min-width:14rem" placeholder="Search name or email">
            <select name="filter[role]" class="ak-select">
                <option value="">All roles</option>
                @foreach ($roles as $role)
                    <option value="{{ $role }}" @selected(($filters['role'] ?? '') === $role)>{{ $role }}</option>
                @endforeach
            </select>
            <select name="filter[department_id]" class="ak-select">
                <option value="">All departments</option>
                @foreach ($departments as $id => $name)
                    <option value="{{ $id }}" @selected((string) ($filters['department_id'] ?? '') === (string) $id)>{{ $name }}</option>
                @endforeach
            </select>
            <button class="ak-btn ak-btn-primary" type="submit">Search</button>
            <a href="{{ route('users.index') }}" class="ak-btn">Reset</a>
        </form>

        <div class="ak-card" style="overflow-x:auto">
            <table class="ak-table">
                <thead>
                    <tr>
                        <th>#</th><th>Name</th><th>Email</th><th>Roles</th><th>Extra permissions</th><th>Status</th><th>Department</th><th style="text-align:center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr>
                            <td>{{ $user->id }}</td>
                            <td><b>{{ $user->name }}</b></td>
                            <td>{{ $user->email }}</td>
                            <td>
                                @forelse ($user->roles as $role)
                                    <span class="ak-badge">{{ $role->name }}</span>
                                @empty
                                    <span class="ak-badge ak-badge-warn">No role</span>
                                @endforelse
                            </td>
                            <td>
                                @if ($user->permissions->isNotEmpty())
                                    <span class="ak-badge ak-badge-direct" title="{{ $user->permissions->pluck('name')->implode(', ') }}">{{ $user->permissions->count() }} direct</span>
                                @else
                                    <span class="ak-muted">-</span>
                                @endif
                            </td>
                            <td><span class="ak-badge {{ $user->status ? 'ak-badge-ok' : 'ak-badge-off' }}">{{ $user->status ? 'Active' : 'Inactive' }}</span></td>
                            <td>{{ $user->department?->name ?? '-' }}</td>
                            <td>
                                <div class="ak-rowact">
                                    @can('view users')
                                        <a class="ak-link" href="{{ route('users.show', $user) }}">View</a>
                                    @endcan
                                    @can('edit users')
                                        <a class="ak-link" href="{{ route('users.edit', $user) }}">Edit</a>
                                    @endcan
                                    @if (auth()->user()->hasRole('Super-Admin') && $user->id !== auth()->id() && $user->id !== 1)
                                        <form action="{{ route('users.destroy', $user) }}" method="POST"
                                            @submit.prevent="ask($el, @js('Delete '.$user->name.'?'), 'The user will no longer be able to sign in. This cannot be undone.', 'Delete user')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="ak-link ak-link-danger">Delete</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="ak-muted" style="text-align:center;padding:2rem">No users found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="ak-pager">{{ $users->links() }}</div>

        @include('partials.confirm-modal')
    </div>
</x-app-layout>
