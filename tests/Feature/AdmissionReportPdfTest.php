<?php

use App\Models\Admission;
use App\Models\Invoice;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::findOrCreate('Super-Admin', 'sanctum');
    $this->user = User::factory()->withPersonalTeam()->create();
    $this->user->assignRole('Super-Admin');

    DB::table('government_departments')->insertOrIgnore(['id' => 1, 'name' => 'Army', 'created_at' => now(), 'updated_at' => now()]);

    $patient = Patient::create([
        'user_id' => $this->user->id,
        'title' => 'Mr',
        'first_name' => 'ALI',
        'last_name' => 'KHAN',
        'father_husband_name' => 'Father',
        'relationship_title' => 'S/O',
        'sex' => 1,
        'age' => 30,
        'years_months' => 'Years',
        'registration_date' => now(),
        'government_non_gov' => 1,
        'government_department_id' => 1,
    ]);

    $invoice = Invoice::create([
        'user_id' => $this->user->id,
        'patient_id' => $patient->id,
        'government_non_government' => 1,
        'government_department_id' => 1,
        'total_amount' => 0,
    ]);

    Admission::create([
        'user_id' => $this->user->id,
        'invoice_id' => $invoice->id,
        'patient_id' => $patient->id,
        'government_department_id' => 1,
        'unit_ward' => 'Medical Ward',
        'category' => 'General',
    ]);
});

it('downloads the admission report as a pdf when pdf output is selected for a long range', function () {
    $response = $this->actingAs($this->user)->get(route('reports.misc.admission', [
        'start_date' => now()->subYear()->format('Y-m-d'),
        'end_date' => now()->format('Y-m-d'),
        'output' => 'pdf',
    ]));

    $response->assertOk()->assertDownload();

    expect($response->headers->get('content-type'))->toContain('application/pdf')
        ->and(str_starts_with($response->streamedContent(), '%PDF'))->toBeTrue();
});

it('shows no data until a search is made', function () {
    $this->actingAs($this->user)->get(route('reports.misc.admission'))
        ->assertOk()
        ->assertSee('Select a date range and press Search')
        ->assertViewHas('admissions', fn ($admissions) => $admissions->isEmpty());
});

it('shows the data on screen for a range within three months and retains the filters', function () {
    $response = $this->actingAs($this->user)->get(route('reports.misc.admission', [
        'start_date' => now()->subMonth()->format('Y-m-d'),
        'end_date' => now()->format('Y-m-d'),
        'filter' => ['category' => 'General', 'unit_ward' => 'Medical Ward'],
    ]));

    $response->assertOk()
        ->assertViewHas('admissions', fn ($admissions) => $admissions->count() === 1)
        ->assertSee('value="General"', false)
        ->assertSee('value="'.now()->format('Y-m-d').'"', false);
});

it('downloads a pdf when the pdf output is selected', function () {
    $response = $this->actingAs($this->user)->get(route('reports.misc.admission', [
        'start_date' => now()->subMonth()->format('Y-m-d'),
        'end_date' => now()->format('Y-m-d'),
        'output' => 'pdf',
    ]));

    $response->assertOk()->assertDownload();
});

it('does not run extra queries per admission row', function () {
    $patient = Patient::first();
    $invoice = Invoice::first();

    foreach (range(1, 20) as $i) {
        Admission::create([
            'user_id' => $this->user->id,
            'invoice_id' => $invoice->id,
            'patient_id' => $patient->id,
            'government_department_id' => 1,
            'unit_ward' => 'Medical Ward',
        ]);
    }

    DB::enableQueryLog();

    $this->actingAs($this->user)->get(route('reports.misc.admission', [
        'start_date' => now()->subMonth()->format('Y-m-d'),
        'end_date' => now()->format('Y-m-d'),
    ]))->assertOk();

    $reportQueries = collect(DB::getQueryLog())->filter(fn ($q) => str_contains($q['query'], 'admission_wards'))->count();

    expect($reportQueries)->toBe(1);
});

it('downloads an excel file when excel output is selected', function () {
    $response = $this->actingAs($this->user)->get(route('reports.misc.admission', [
        'start_date' => now()->subYear()->format('Y-m-d'),
        'end_date' => now()->format('Y-m-d'),
        'output' => 'excel',
    ]));

    $response->assertOk()->assertDownload();

    expect($response->headers->get('content-disposition'))->toContain('.xlsx');
});

it('shows a long range on screen when screen output is selected', function () {
    $this->actingAs($this->user)->get(route('reports.misc.admission', [
        'start_date' => now()->subYear()->format('Y-m-d'),
        'end_date' => now()->format('Y-m-d'),
        'output' => 'screen',
    ]))->assertOk()->assertViewHas('admissions', fn ($a) => $a->count() === 1);
});

it('offers period presets and retains the selected one without loading data', function () {
    $this->actingAs($this->user)->get(route('reports.misc.admission', ['period' => 'last_tax_year']))
        ->assertOk()
        ->assertSee('Last tax year (Jul-Jun)')
        ->assertSee('value="last_tax_year" selected', false)
        ->assertViewHas('admissions', fn ($a) => $a->isEmpty());
});

it('sets a download cookie so the progress modal can close', function () {
    $response = $this->actingAs($this->user)->get(route('reports.misc.admission', [
        'start_date' => now()->subMonth()->format('Y-m-d'),
        'end_date' => now()->format('Y-m-d'),
        'output' => 'excel',
        'download_token' => 'abc123',
    ]));

    $cookie = collect($response->headers->getCookies())->first(fn ($c) => $c->getName() === 'report_download');

    expect($cookie)->not->toBeNull()
        ->and($cookie->getValue())->toBe('abc123');
});
