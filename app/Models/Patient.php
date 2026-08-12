<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Patient extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'title',
        'first_name',
        'last_name',
        'relationship_title',
        'father_husband_name',
        'age',
        'years_months',
        'dob',
        'sex',
        'blood_group',
        'registration_date',
        'phone',
        'email',
        'address',
        'mobile',
        'email_alert',
        'mobile_alert',
        'cnic',
        'government_non_gov',
        'government_department_id',
        'designation',
        'government_card_no',
        'sehat_sahulat_patient_id',
        'sehat_sahulat_visit_no',
    ];

    public function patient_test_cart()
    {
        return $this->hasMany(PatientTestCart::class);
    }

    public function government_department(): BelongsTo
    {
        return $this->belongsTo(GovernmentDepartment::class);
    }

    public function chits(): HasMany
    {
        return $this->hasMany(Chit::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * Get the user that owns the Patient
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * Get the emergency treatments for the patient
     */
    public function emergencyTreatments(): HasMany
    {
        return $this->hasMany(PatientEmergencyTreatment::class);
    }
}
