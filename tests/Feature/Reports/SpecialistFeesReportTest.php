<?php

namespace Tests\Feature\Reports;

use App\Models\Chit;
use App\Models\Department;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SpecialistFeesReportTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('Super-Admin', 'sanctum');

        $this->user = User::factory()->withPersonalTeam()->create();

        $this->user->assignRole('Super-Admin');

        $this->actingAs($this->user);
    }

    #[Test]
    public function it_shows_all_specialist_departments_even_with_no_data()
    {
        Department::create(['name' => 'ENT Specialist']);
        Department::create(['name' => 'Eye Specialist']);
        Department::create(['name' => 'General OPD']);

        $response = $this->get(route('reports.opd.specialist-fees'));

        $response->assertStatus(200);
        $response->assertSee('ENT Specialist');
        $response->assertSee('Eye Specialist');
        $response->assertDontSee('General OPD');

        $stats = $response->viewData('departmentStats');
        $this->assertCount(2, $stats);
    }

    #[Test]
    public function it_correctly_calculates_govt_amount_for_specialist_fees()
    {
        $spec = Department::create(['name' => 'Skin Specialist']);
        $patient = Patient::create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'phone' => '1234567890',
        ]);

        Chit::create([
            'department_id' => $spec->id,
            'patient_id' => $patient->id,
            'user_id' => $this->user->id,
            'amount' => 100.00,
            'govt_amount' => 70.00,
            'issued_date' => now(),
            'government_non_gov' => 0,
            'ipd_opd' => false,
        ]);

        $response = $this->get(route('reports.opd.specialist-fees'));

        $stats = $response->viewData('departmentStats');
        $specStats = $stats->first(fn ($s) => $s['department']->id === $spec->id);

        $this->assertEquals(70.00, $specStats['total_fees']);
    }

    #[Test]
    public function it_filters_by_specific_specialist_department()
    {
        $spec1 = Department::create(['name' => 'ENT Specialist']);
        $spec2 = Department::create(['name' => 'Eye Specialist']);

        $response = $this->get(route('reports.opd.specialist-fees', ['department_id' => $spec1->id]));

        $response->assertStatus(200);
        $response->assertSee('ENT Specialist');
        // The dropdown will still have all departments, so we check the view data
        $stats = $response->viewData('departmentStats');
        $this->assertCount(1, $stats);
        $this->assertEquals($spec1->id, $stats->first()['department']->id);
        $this->assertFalse($stats->contains(fn ($s) => $s['department']->id === $spec2->id));
    }
}
