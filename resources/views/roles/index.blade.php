<x-app-layout>
    <x-slot name="header">
        <div class="ak-head">
            <div>
                <h2 class="ak-title">Roles</h2>
                <p class="ak-sub">Default permission sets that are assigned to users.</p>
            </div>
            <div class="ak-actions">
                @can('create roles')
                    <a href="{{ route('roles.create') }}" class="ak-btn ak-btn-primary">+ Create role</a>
                @endcan
            </div>
        </div>
    </x-slot>

    @include('partials.access-style')

    <div class="ak-page" x-data="confirmDialog()">
        @if (session('success'))<div class="ak-alert ak-alert-ok">{{ session('success') }}</div>@endif
        @if (session('error'))<div class="ak-alert ak-alert-err">{{ session('error') }}</div>@endif

        <div class="ak-card" style="overflow-x:auto">
            <table class="ak-table">
                <thead><tr><th>Role</th><th>Permissions</th><th>Users</th><th style="text-align:center">Actions</th></tr></thead>
                <tbody>
                    @foreach ($roles as $role)
                        <tr>
                            <td><b>{{ $role->name }}</b></td>
                            <td>{{ $role->name === 'Super-Admin' ? 'All' : $role->permissions_count }}</td>
                            <td>{{ $userCounts[$role->id] ?? 0 }}</td>
                            <td>
                                <div class="ak-rowact">
                                    @can('edit roles')
                                        <a class="ak-link" href="{{ route('roles.edit', $role) }}">{{ $role->name === 'Super-Admin' ? 'View' : 'Edit' }}</a>
                                    @endcan
                                    @if (auth()->user()->hasRole('Super-Admin') && $role->name !== 'Super-Admin')
                                        <form action="{{ route('roles.destroy', $role) }}" method="POST"
                                            @submit.prevent="ask($el, @js('Delete role '.$role->name.'?'), 'Users must be moved off this role first. Its permissions are not deleted. This cannot be undone.', 'Delete role')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="ak-link ak-link-danger">Delete</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @include('partials.confirm-modal')
    </div>
</x-app-layout>
