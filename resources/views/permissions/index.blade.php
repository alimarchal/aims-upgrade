<x-app-layout>
    <x-slot name="header">
        <div class="ak-head">
            <div>
                <h2 class="ak-title">Permissions</h2>
                <p class="ak-sub">{{ $total }} permissions, grouped like the menu. Give them to people from Roles or Users.</p>
            </div>
            <div class="ak-actions">
                <a href="{{ route('permissions.create') }}" class="ak-btn ak-btn-primary">+ Create permission</a>
            </div>
        </div>
    </x-slot>

    @include('partials.access-style')

    <div class="ak-page" x-data="{ q: '', ...confirmDialog() }">
        @if (session('success'))<div class="ak-alert ak-alert-ok">{{ session('success') }}</div>@endif
        @if (session('error'))<div class="ak-alert ak-alert-err">{{ session('error') }}</div>@endif

        <input type="search" x-model="q" class="ak-input" style="width:100%;max-width:24rem;margin-bottom:1rem" placeholder="Filter permissions...">

        @foreach ($catalog as $module)
            <div class="ak-card">
                <div class="ak-card-head">{{ $module['label'] }}</div>
                <div style="overflow-x:auto">
                    <table class="ak-table">
                        <thead><tr><th>Permission</th><th>Roles</th><th>Users (direct)</th><th style="text-align:center">Actions</th></tr></thead>
                        <tbody>
                            @foreach ($module['groups'] as $group)
                                @foreach ($group['permissions'] as $permission)
                                    <tr x-show="q === '' || @js($permission['name']).includes(q.toLowerCase())">
                                        <td>{{ $permission['name'] }}</td>
                                        <td>{{ $roleCounts[$permission['id']] ?? 0 }}</td>
                                        <td>{{ $userCounts[$permission['id']] ?? 0 }}</td>
                                        <td>
                                            <div class="ak-rowact">
                                                <a class="ak-link" href="{{ route('permissions.edit', $permission['id']) }}">Edit</a>
                                                @if (auth()->user()->hasRole('Super-Admin'))
                                                    <form action="{{ route('permissions.destroy', $permission['id']) }}" method="POST"
                                                        @submit.prevent="ask($el, @js('Delete '.$permission['name'].'?'), 'Screens that check this permission will stop working for everyone. This cannot be undone.', 'Delete permission')">
                                                        @csrf @method('DELETE')
                                                        <button type="submit" class="ak-link ak-link-danger">Delete</button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endforeach

        @include('partials.confirm-modal')
    </div>
</x-app-layout>
