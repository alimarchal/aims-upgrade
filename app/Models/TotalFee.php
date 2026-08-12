<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TotalFee extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_test_id',
        'user_id',
        'department_id',
        'patient_id',
        'lab_test_id',
        'hif_amount',
        'government_amount',
        'total_amount',
    ];
}
