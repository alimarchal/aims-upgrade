<?php

use App\Models\Chit;
use App\Models\Department;
use App\Models\Patient;
use App\Models\User;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::findOrCreate('Super-Admin', 'sanctum');
    $this->user = User::factory()->withPersonalTeam()->create();
    $this->user->assignRole('Super-Admin');
    $this->actingAs($this->user);
});

it('displays opd user wise report page', function () {
    $response = $this->get(route('reports.opd.user-wise'));

    $response->assertStatus(200);
    $response->assertViewIs('reports.opd.user-wise');
});

it('shows all chits when specialists_only filter is not set', function () {
    $generalDept = Department::factory()->create(['name' => 'General']);
    $specialistDept = Department::factory()->create(['name' => 'Specialist Cardiology']);
    $patient = Patient::factory()->create();
    $user = User::factory()->create();

    Chit::factory()->create([
        'department_id' => $generalDept->id,
        'patient_id' => $patient->id,
        'user_id' => $user->id,
        'issued_date' => now(),
    ]);

    Chit::factory()->create([
        'department_id' => $specialistDept->id,
        'patient_id' => $patient->id,
        'user_id' => $user->id,
        'issued_date' => now(),
    ]);

    $response = $this->get(route('reports.opd.user-wise'));

    $chits = $response->viewData('chits');
    expect($chits)->toHaveCount(2);
});

it('filters only specialist department chits when specialists_only filter is enabled', function () {
    $generalDept = Department::factory()->create(['name' => 'General']);
    $specialistDept = Department::factory()->create(['name' => 'Specialist Cardiology']);
    $patient = Patient::factory()->create();
    $user = User::factory()->create();

    Chit::factory()->create([
        'department_id' => $generalDept->id,
        'patient_id' => $patient->id,
        'user_id' => $user->id,
        'issued_date' => now(),
    ]);

    Chit::factory()->create([
        'department_id' => $specialistDept->id,
        'patient_id' => $patient->id,
        'user_id' => $user->id,
        'issued_date' => now(),
    ]);

    $response = $this->get(route('reports.opd.user-wise', ['specialists_only' => 'on']));

    $chits = $response->viewData('chits');
    expect($chits)->toHaveCount(1);
    expect($chits->first()->department->name)->toContain('Specialist');
});

it('returns empty results for specialists_only filter when no specialist chits exist', function () {
    $generalDept = Department::factory()->create(['name' => 'General']);
    $patient = Patient::factory()->create();
    $user = User::factory()->create();

    Chit::factory()->create([
        'department_id' => $generalDept->id,
        'patient_id' => $patient->id,
        'user_id' => $user->id,
        'issued_date' => now(),
    ]);

    $response = $this->get(route('reports.opd.user-wise', ['specialists_only' => 'on']));

    $chits = $response->viewData('chits');
    expect($chits)->toHaveCount(0);
});

it('filters by date range along with specialists_only filter', function () {
    $specialistDept = Department::factory()->create(['name' => 'Specialist Orthopedics']);
    $patient = Patient::factory()->create();
    $user = User::factory()->create();

    $today = now();
    $yesterday = now()->subDay();

    Chit::factory()->create([
        'department_id' => $specialistDept->id,
        'patient_id' => $patient->id,
        'user_id' => $user->id,
        'issued_date' => $today,
    ]);

    Chit::factory()->create([
        'department_id' => $specialistDept->id,
        'patient_id' => $patient->id,
        'user_id' => $user->id,
        'issued_date' => $yesterday,
    ]);

    $response = $this->get(route('reports.opd.user-wise', [
        'specialists_only' => 'on',
        'start_date' => $today->format('Y-m-d'),
        'end_date' => $today->format('Y-m-d'),
    ]));

    $chits = $response->viewData('chits');
    expect($chits)->toHaveCount(1);
});
