<?php

use App\Models\User;
use Spatie\Permission\Models\Permission;

it('shows the opd daily report card to users with the government daily reports permission', function () {
    $user = User::factory()->create();
    $user->givePermissionTo([
        Permission::findOrCreate('view reports', 'sanctum'),
        Permission::findOrCreate('view government daily reports', 'sanctum'),
    ]);

    $this->actingAs($user)
        ->get(route('reports.index'))
        ->assertSuccessful()
        ->assertSee(route('reports.opd.reportDailyOpd'))
        ->assertSeeInOrder(['OPD', 'Daily Report']);
});

it('hides the opd daily report card from users without the government daily reports permission', function () {
    $user = User::factory()->create();
    $user->givePermissionTo(Permission::findOrCreate('view reports', 'sanctum'));

    $this->actingAs($user)
        ->get(route('reports.index'))
        ->assertSuccessful()
        ->assertDontSee(route('reports.opd.reportDailyOpd'));
});
