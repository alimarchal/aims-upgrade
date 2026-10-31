<?php

use App\Console\Commands\ApplyOneTimeFeeUpdate;
use App\Models\FeeCategory;
use App\Models\FeeType;
use App\Models\User;
use Illuminate\Console\Scheduling\Schedule;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

test('the one-time command sets the revised totals and HIF', function () {
    $category = FeeCategory::factory()->create();
    foreach (array_keys(ApplyOneTimeFeeUpdate::REVISED_FEES) as $id) {
        FeeType::unguarded(fn () => FeeType::query()->updateOrCreate(['id' => $id], ['fee_category_id' => $category->id, 'type' => "Fee {$id}", 'status' => 'Normal', 'amount' => 10, 'hif' => 1]));
    }

    $this->artisan('fees:apply-one-time-update')->assertSuccessful();

    $expected = [1 => [50, 25], 107 => [50, 25], 215 => [-50, -25], 216 => [-50, -25], 108 => [50, 25], 270 => [50, 25]];

    foreach ($expected as $id => [$amount, $hif]) {
        $feeType = FeeType::find($id);

        expect((float) $feeType->amount)->toBe((float) $amount)
            ->and((float) $feeType->hif)->toBe((float) $hif);
    }
});

test('the one-time command skips missing fee types without failing', function () {
    $this->artisan('fees:apply-one-time-update')->assertSuccessful();
});

test('the one-time update is scheduled for 23:55 on 31 October only', function () {
    $event = collect(app(Schedule::class)->events())->first(fn ($e) => str_contains($e->command, 'fees:apply-one-time-update'));

    expect($event->expression)->toBe('55 23 * * *');

    $this->travelTo(now()->setDate(2026, 10, 31)->setTime(23, 55));
    expect($event->filtersPass(app()))->toBeTrue();

    $this->travelTo(now()->setDate(2026, 11, 1)->setTime(23, 55));
    expect($event->filtersPass(app()))->toBeFalse();
});

test('the fee list shows HIF, Govt and Total where Govt is total minus HIF', function () {
    Permission::findOrCreate('view fee types', 'sanctum');
    Role::findOrCreate('Viewer', 'sanctum')->syncPermissions(['view fee types']);
    $user = User::factory()->create();
    $user->assignRole('Viewer');
    $category = FeeCategory::factory()->create();
    FeeType::factory()->create(['fee_category_id' => $category->id, 'type' => 'Emergency Test', 'status' => 'Normal', 'amount' => 50, 'hif' => 20]);

    $this->actingAs($user)->get(route('feeType.index'))
        ->assertOk()
        ->assertSeeInOrder(['HIF', 'Govt', 'Total'])
        ->assertSeeInOrder(['Emergency Test', '20.00', '30.00', '50.00']);
});
