<?php

namespace App\Exports;

use App\Models\Admission;
use Carbon\Carbon;
use Closure;
use Generator;
use Maatwebsite\Excel\Concerns\FromGenerator;
use Maatwebsite\Excel\Concerns\WithHeadings;

class AdmissionReportExport implements FromGenerator, WithHeadings
{
    /**
     * @param  Closure(): iterable<int, Admission>  $rows
     */
    public function __construct(protected Closure $rows) {}

    /**
     * @return array<int, string>
     */
    public function headings(): array
    {
        return ['#', 'MR Number', 'Patient Name', 'Age/Sex', 'Admission Date', 'Ward', 'Address', 'Category', 'Return', 'Amount'];
    }

    /**
     * @return Generator<int, array<int, int|float|string|null>>
     */
    public function generator(): Generator
    {
        $count = 0;

        foreach (($this->rows)() as $admission) {
            $patient = $admission->patient;

            yield [
                ++$count,
                $patient->id.'-'.$admission->invoice_id.'-'.$admission->id,
                trim($patient->title.' '.$patient->first_name.' '.$patient->last_name),
                $patient->age.' '.$patient->years_months.' / '.($patient->sex == 1 ? 'M' : 'F'),
                Carbon::parse($admission->created_at)->format('d-m-Y H:i:s'),
                $admission->unit_ward,
                $admission->address,
                $admission->category,
                $admission->status,
                $admission->status === 'Yes' ? -$admission->invoice->total_amount : $admission->invoice->total_amount,
            ];
        }
    }
}
