<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;

class GovernmentPatientImportTemplateExport implements FromArray, ShouldAutoSize, WithColumnFormatting, WithHeadings
{
    /**
     * @return list<string>
     */
    public function headings(): array
    {
        return [
            '#',
            'title',
            'first_name',
            'last_name',
            'relationship_title',
            'father_husband_name',
            'age',
            'address',
            'government_department_name',
            'designation',
            'government_card_no',
        ];
    }

    /**
     * @return list<array<int, mixed>>
     */
    public function array(): array
    {
        return [];
    }

    /**
     * @return array<string, string>
     */
    public function columnFormats(): array
    {
        return [
            'K' => '@',
        ];
    }
}
