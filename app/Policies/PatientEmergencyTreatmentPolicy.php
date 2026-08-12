<?php

namespace App\Policies;

use App\Models\PatientEmergencyTreatment;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PatientEmergencyTreatmentPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        //
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, PatientEmergencyTreatment $patientEmergencyTreatment): bool
    {
        //
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        //
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, PatientEmergencyTreatment $patientEmergencyTreatment): bool
    {
        //
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, PatientEmergencyTreatment $patientEmergencyTreatment): bool
    {
        //
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, PatientEmergencyTreatment $patientEmergencyTreatment): bool
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, PatientEmergencyTreatment $patientEmergencyTreatment): bool
    {
        //
    }
}
