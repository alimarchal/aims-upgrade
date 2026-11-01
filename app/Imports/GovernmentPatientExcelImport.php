<?php

namespace App\Imports;

use App\Services\GovernmentPatientInvoiceImport as GovernmentPatientInvoiceImportService;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class GovernmentPatientExcelImport implements ToCollection, WithHeadingRow
{
    /**
     * @var array<string, mixed>
     */
    private array $result = [];

    public function __construct(private readonly GovernmentPatientInvoiceImportService $service) {}

    public function collection(Collection $rows): void
    {
        $this->result = $this->service->process($rows);
    }

    /**
     * @return array<string, mixed>
     */
    public function result(): array
    {
        return $this->result;
    }
}
