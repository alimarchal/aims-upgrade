<x-app-layout>
    <x-slot name="header">
        <div class="ak-head">
            <div>
                <h1 class="ak-title">Hospital Fees</h1>
                <p class="ak-sub">Fee list grouped by category.</p>
            </div>
            <div class="ak-actions">
                <button type="button" class="ak-btn" onclick="window.print()">Print</button>
                @can('create fee types')
                    <a href="{{ route('feeType.create') }}" class="ak-btn ak-btn-primary">+ Add hospital fee</a>
                @endcan
            </div>
        </div>
    </x-slot>

    @include('partials.access-style')

    <div class="ak-page">
        <x-validation-errors class="mb-4" />
        @if (session('message'))<div class="ak-alert ak-alert-ok">{{ session('message') }}</div>@endif
        @if (session('error'))<div class="ak-alert ak-alert-err">{{ session('error') }}</div>@endif

        @foreach ($categories as $category)
            <div class="ak-card" style="overflow-x:auto">
                <div class="ak-card-head"><span>{{ $category->name }} <span class="ak-muted">({{ $category->id }})</span></span><span class="ak-badge">{{ $category->feeTypes->count() }} fees</span></div>
                <table class="ak-table">
                    <thead><tr><th>Name</th><th style="text-align:right">HIF</th><th style="text-align:right">Govt</th><th style="text-align:right">Total</th><th style="text-align:center" class="print:hidden">Actions</th></tr></thead>
                    <tbody>
                        @forelse ($category->feeTypes as $feeType)
                            <tr>
                                <td>{{ $feeType->type }} <span class="ak-muted">({{ $feeType->id }})</span></td>
                                <td style="text-align:right">{{ number_format($feeType->hif, 2) }}</td>
                                <td style="text-align:right">{{ number_format($feeType->amount - $feeType->hif, 2) }}</td>
                                <td style="text-align:right">{{ number_format($feeType->amount, 2) }}</td>
                                <td class="print:hidden">
                                    <div class="ak-rowact">
                                        @can('edit fee types')
                                            <a class="ak-link" href="{{ route('feeType.edit', $feeType->id) }}">Edit</a>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="ak-muted" style="text-align:center">No fees in this category.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @endforeach
    </div>
</x-app-layout>
