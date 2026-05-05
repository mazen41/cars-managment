<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Bus\Queueable;

abstract class BaseExport implements FromQuery, WithMapping, WithHeadings, WithCustomStartCell, WithStyles, WithChunkReading, WithEvents, ShouldQueue
{
    use Queueable;

    protected $ids;
    protected $filters;

    public function __construct($ids = null, $filters = [])
    {
        $this->ids = $ids;
        $this->filters = $filters;
    }

    /**
     * Build the base query for export
     * Must be implemented by child classes
     */
    abstract protected function buildQuery();

    /**
     * Define column headings
     * Must be implemented by child classes
     */
    abstract public function headings(): array;

    /**
     * Map model data to export row
     * Must be implemented by child classes
     */
    abstract public function map($model): array;

    /**
     * Calculate totals for the footer row
     * Can be overridden by child classes if totals are needed
     */
    protected function calculateTotals(): ?array
    {
        return null;
    }

    /**
     * Format the totals row
     * Can be overridden by child classes
     */
    protected function formatTotalsRow(array $totals): array
    {
        return $totals;
    }

    /**
     * Get the column range for styling (e.g., 'A1:K1')
     */
    protected function getColumnRange(): string
    {
        $lastColumn = $this->getLastColumn();
        return "A1:{$lastColumn}1";
    }

    /**
     * Get the last column letter based on headings count
     */
    protected function getLastColumn(): string
    {
        $count = count($this->headings());
        return chr(64 + $count); // A=65, so 64+1=A
    }

    public function query()
    {
        $query = $this->buildQuery();

        if ($this->ids) {
            $query->whereIn('id', $this->ids);
        }

        return $query;
    }

    public function startCell(): string
    {
        return 'A1';
    }

    public function styles(Worksheet $sheet)
    {
        $range = $this->getColumnRange();
        
        $sheet->getStyle($range)->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4B5563'],
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ]
        ]);

        return [];
    }

    public function chunkSize(): int
    {
        return 1000;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $totals = $this->calculateTotals();

                if ($totals) {
                    $sheet = $event->sheet;
                    $lastRow = $sheet->getHighestRow() + 1;
                    $lastColumn = $this->getLastColumn();

                    $sheet->append([$this->formatTotalsRow($totals)]);

                    $sheet->getStyle("A{$lastRow}:{$lastColumn}{$lastRow}")->applyFromArray([
                        'font' => [
                            'bold' => true,
                            'size' => 12,
                        ],
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => 'F3F4F6'],
                        ],
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                            ],
                            'top' => [
                                'borderStyle' => Border::BORDER_THICK,
                            ],
                            'bottom' => [
                                'borderStyle' => Border::BORDER_THICK,
                            ],
                        ],
                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_CENTER,
                            'vertical' => Alignment::VERTICAL_CENTER,
                        ]
                    ]);
                }

                // Auto-size all columns
                $lastColumn = $this->getLastColumn();
                foreach (range('A', $lastColumn) as $col) {
                    $event->sheet->getColumnDimension($col)->setAutoSize(true);
                }
            },
        ];
    }
}
