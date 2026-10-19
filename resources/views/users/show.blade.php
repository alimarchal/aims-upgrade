<x-app-layout>
    <x-slot name="header">
        <div class="ak-head">
            <div>
                <h2 class="ak-title">{{ $user->name }}</h2>
                <p class="ak-sub">{{ $user->email }}</p>
            </div>
            <div class="ak-actions">
                <a href="{{ route('users.index') }}" class="ak-btn">&larr; Back to users</a>
                @can('edit users')
                    <a href="{{ route('users.edit', $user) }}" class="ak-btn ak-btn-primary">Edit user</a>
                @endcan
            </div>
        </div>
    </x-slot>

    @include('partials.access-style')

    <div class="ak-page">
        <div class="ak-card">
            <div class="ak-card-head">Account</div>
            <div class="ak-card-body ak-grid">
                <div><div class="ak-muted">Status</div><span class="ak-badge {{ $user->status ? 'ak-badge-ok' : 'ak-badge-off' }}">{{ $user->status ? 'Active' : 'Inactive' }}</span></div>
                <div><div class="ak-muted">Department</div>{{ $user->department?->name ?? 'N/A' }}</div>
                <div><div class="ak-muted">Created</div>{{ $user->created_at?->format('d M Y H:i') ?? 'N/A' }}</div>
                <div>
                    <div class="ak-muted">Roles</div>
                    @forelse ($user->roles as $role)<span class="ak-badge">{{ $role->name }}</span>@empty<span class="ak-muted">No roles</span>@endforelse
                </div>
            </div>
        </div>

        <div class="ak-card">
            <div class="ak-card-head">Access <span class="ak-muted">Green = from role, purple = given directly</span></div>
            <div class="ak-card-body">
                @php $anyShown = false; @endphp
                @foreach ($catalog as $module)
                    @php
                        $rows = collect($module['groups'])->map(function ($group) use ($direct, $inherited) {
                            $group['permissions'] = array_values(array_filter($group['permissions'], fn ($p) => in_array($p['id'], $direct, true) || in_array($p['id'], $inherited, true)));
                            return $group;
                        })->filter(fn ($g) => $g['permissions'] !== []);
                    @endphp
                    @if ($rows->isNotEmpty())
                        @php $anyShown = true; @endphp
                        <div class="ak-mod">
                            <div class="ak-mod-head">{{ $module['label'] }}</div>
                            @foreach ($rows as $group)
                                <div class="ak-perm-row">
                                    <span class="ak-perm-label">{{ $group['label'] }}</span>
                                    @foreach ($group['permissions'] as $permission)
                                        <span class="ak-badge {{ in_array($permission['id'], $direct, true) ? 'ak-badge-direct' : 'ak-badge-ok' }}" title="{{ $permission['name'] }}">{{ ucfirst($permission['action']) }}</span>
                                    @endforeach
                                </div>
                            @endforeach
                        </div>
                    @endif
                @endforeach
                @unless ($anyShown)
                    <p class="ak-muted">This user has no permissions yet.</p>
                @endunless
            </div>
        </div>
    </div>
</x-app-layout>
