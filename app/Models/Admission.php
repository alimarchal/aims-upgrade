<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Admission extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'invoice_id',
        'patient_id',
        'government_department_id',
        'actual_total_amount',
        'unit_ward',
        'disease',
        'category',
        'nok_name',
        'relation_with_patient',
        'address',
        'cell_no',
        'cnic_no',
        'village',
        'tehsil',
        'district',
        'status',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
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
