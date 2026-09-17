<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class LaporanExport implements FromCollection, WithHeadings
{
    /**
     * @param  array<int, string>  $kolom
     * @param  Collection<int, array<int, mixed>>  $baris
     */
    public function __construct(
        private readonly array $kolom,
        private readonly Collection $baris,
    ) {}

    public function headings(): array
    {
        return $this->kolom;
    }

    public function collection(): Collection
    {
        return $this->baris;
    }
}
