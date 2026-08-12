<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'patient_id',
        'government_non_government',
        'government_card_no',
        'government_department_id',
        'total_amount',
        'hif_amount',
        'govt_amount',
        'actual_total_amount',
    ];

    public function patient_test(): HasMany
    {
        return $this->hasMany(PatientTest::class);
    }

    public function patient_test_sum()
    {
        return $this->hasMany(PatientTest::class)->sum('total_amount');
    }

    public function patient_test_latest(): HasOne
    {
        return $this->hasOne(PatientTest::class)->latestOfMany();
    }

    public function admission(): HasOne
    {
        return $this->hasOne(Admission::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
