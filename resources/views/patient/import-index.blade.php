<x-app-layout>
    <x-slot name="header">
        <div class="ak-head">
            <div>
                <nav class="ak-crumbs" aria-label="Breadcrumb">
                    <a href="{{ route('patient.index') }}">Patients</a><span aria-hidden="true">›</span>
                    <span>Patient Import Archive</span>
                </nav>
                <h1 class="ak-title">Patient Import Archive</h1>
                <p class="ak-sub">Search archived snapshots, review their complete source records, or restore a selected patient version.</p>
            </div>
            <div class="ak-actions">
                @if (auth()->user()->hasRole('Super-Admin') && config('government_import.access'))
                    <a href="{{ route('patient.government-import.help') }}" class="ak-btn">Import lifecycle guide</a>
                @endif
                <a href="{{ route('patient.index') }}" class="ak-btn">Back to Patients</a>
            </div>
        </div>
    </x-slot>

    @include('partials.access-style')

    <div class="ak-page space-y-5">
        <x-validation-errors />

        @if (session('success'))
            <div role="status" class="ak-alert ak-alert-ok">{{ session('success') }}</div>
        @endif

        <div class="ak-card ak-card-body space-y-3">
            <p class="text-sm leading-6 text-gray-700 dark:text-gray-200">
                This page lists every archived version. Searching patient records uses the newest archived version for
                Front Desk/Receptionist; if no snapshot exists, that search uses the live patient. Super-Admin and
                Administrator continue to see live patient data. Restoring a version overwrites or recreates the
                patient's original rows and then removes only that patient's snapshot for the selected import batch
                from all three archive tables.
            </p>
            <form action="{{ route('patient-import.index') }}" method="GET" class="ak-filter">
                <label for="search" class="sr-only">Search archived patients</label>
                <input id="search" name="search" type="search" value="{{ $search }}"
                    placeholder="Search ID, name, father name, mobile, CNIC, card, or batch"
                    class="ak-input w-full md:max-w-xl">
                <button type="submit" class="ak-btn ak-btn-primary">Search archive</button>
                @if ($search !== '')
                    <a href="{{ route('patient-import.index') }}" class="ak-btn">Clear</a>
                @endif
            </form>
        </div>

        <section class="ak-card">
            <div class="ak-card-head">
                <span>Archived patient versions</span>
                <span class="ak-muted">{{ $patients->total() }} matching snapshot(s)</span>
            </div>
            <div class="overflow-x-auto">
                <table class="ak-table">
                    <thead>
                        <tr>
                            <th>Patient ID</th>
                            <th>Name</th>
                            <th>Father / Husband</th>
                            <th>Mobile</th>
                            <th>CNIC</th>
                            <th>Government card</th>
                            <th>Import batch</th>
                            <th>Archived at</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($patients as $patient)
                            <tr>
                                <td>{{ $patient->id }}</td>
                                <td>{{ trim(($patient->title ?? '').' '.($patient->first_name ?? '').' '.($patient->last_name ?? '')) }}</td>
                                <td>{{ $patient->father_husband_name }}</td>
                                <td>{{ $patient->mobile }}</td>
                                <td>{{ $patient->cnic }}</td>
                                <td>{{ $patient->government_card_no }}</td>
                                <td><code>{{ $patient->import_batch_id }}</code></td>
                                <td class="whitespace-nowrap">{{ $patient->archived_at }}</td>
                                <td>
                                    <form action="{{ route('patient-import.restore', [$patient->id, $patient->import_batch_id]) }}"
                                        method="POST"
                                        onsubmit="return confirm('Restore this patient, invoice, and patient-test snapshot exactly? Current rows will be overwritten, and this snapshot will be deleted from all three archive tables.');">
                                        @csrf
                                        <input type="hidden" name="confirm_restore" value="1">
                                        <button type="submit"
                                            class="inline-flex items-center rounded-md border border-amber-700 bg-amber-600 px-3 py-2 text-xs font-semibold uppercase tracking-widest text-white hover:bg-amber-700">
                                            Restore this version
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="py-8 text-center text-gray-500 dark:text-gray-400">
                                    {{ $search === '' ? 'No patient snapshots have been archived yet.' : 'No archived patient snapshots match this search.' }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="ak-card-body">
                {{ $patients->links() }}
            </div>
        </section>
    </div>
</x-app-layout>
