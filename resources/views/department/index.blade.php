<x-app-layout>
    <x-slot name="header">
        <div class="ak-head">
            <div>
                <h1 class="ak-title">Departments</h1>
                <p class="ak-sub">OPD departments and their daily patient limits.</p>
            </div>
            <div class="ak-actions">
                <button type="button" class="ak-btn" onclick="window.print()">Print</button>
                @can('create departments')
                    <a href="{{ route('department.create') }}" class="ak-btn ak-btn-primary">+ Add department</a>
                @endcan
            </div>
        </div>
    </x-slot>

    @include('partials.access-style')

    <div class="ak-page" x-data="confirmDialog()">
        <x-validation-errors class="mb-4" />
        @if (session('message'))<div class="ak-alert ak-alert-ok">{{ session('message') }}</div>@endif
        @if (session('error'))<div class="ak-alert ak-alert-err">{{ session('error') }}</div>@endif

        <div class="ak-card" style="overflow-x:auto">
            <table class="ak-table">
                <thead><tr><th>#</th><th>Name</th><th style="text-align:center">Daily limit</th><th style="text-align:center">Category</th><th style="text-align:center">Actions</th></tr></thead>
                <tbody>
                    @forelse ($departments as $department)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td><b>{{ $department->name }}</b></td>
                            <td style="text-align:center">{{ $department->daily_patient_limit }}</td>
                            <td style="text-align:center"><span class="ak-badge">{{ $department->category }}</span></td>
                            <td>
                                <div class="ak-rowact">
                                    @can('edit departments')
                                        <a class="ak-link" href="{{ route('department.edit', $department->id) }}">Edit</a>
                                    @endcan
                                    @if (auth()->user()->hasRole('Super-Admin'))
                                        <form action="{{ route('department.destroy', $department->id) }}" method="POST"
                                            @submit.prevent="ask($el, @js('Delete '.$department->name.'?'), 'Patients and chits already linked to this department may be affected. This cannot be undone.', 'Delete department')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="ak-link ak-link-danger">Delete</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="ak-muted" style="text-align:center;padding:2rem">No departments yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @include('partials.confirm-modal')
    </div>
</x-app-layout>
