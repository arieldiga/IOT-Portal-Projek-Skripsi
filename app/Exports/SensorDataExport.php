<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use App\Models\SensorColumnConfig;
use Illuminate\Support\Facades\Auth;

class SensorDataExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle, WithEvents
{
    protected $data;
    protected $username;
    protected $dateRange;
    protected $parameter;
    protected $availableColumns;
    protected $customLabels;

    public function __construct($data, $username, $dateRange, $parameter = null)
    {
        $this->data = $data;
        $this->username = $username;
        $this->dateRange = $dateRange;
        $this->parameter = $parameter;
        
        $this->loadCustomLabels();
        $this->detectAvailableColumns();
    }

    protected function loadCustomLabels()
    {
        $this->customLabels = [];
        
        if (Auth::check()) {
            $userId = Auth::id();
            
            $configs = SensorColumnConfig::where('user_id', $userId)
                ->where('is_visible', true)
                ->get()
                ->keyBy('column_name');
            
            foreach ($configs as $columnName => $config) {
                $this->customLabels[$columnName] = $config->custom_label;
            }
        }
    }

    protected function detectAvailableColumns()
    {
        $allColumns = [
            'ph' => 'pH',
            'suhu' => 'Suhu (°C)',
            'tds' => 'TDS (ppm)',
            'conductivity' => 'Conductivity (µS/cm)',
            'cod' => 'COD (mg/L)',
            'tss' => 'TSS (mg/L)',
            'nh3n' => 'NH3-N (mg/L)',
            'debit' => 'Debit (L/min)',
            'orp' => 'ORP (mV)',
            'corrosion_rate' => 'Corrosion Rate',
            'corrosion_inhibitor' => 'Corrosion Inhibitor',
            'scale_inhibitor' => 'Scale Inhibitor',
            'turbidity' => 'Turbidity (NTU)',
            'lvl_biocid_p' => 'Level Biocide P',
            'lvl_naoh_p' => 'Level NaOH P',
            'lvl_non_ox_bioa_p' => 'Level Non-Ox Bio A P',
            'lvl_non_ox_biob_p' => 'Level Non-Ox Bio B P',
            'suhu_1' => 'Suhu 1',
            'suhu_2' => 'Suhu 2',
            'suhu_3' => 'Suhu 3',
            'suhu_4' => 'Suhu 4',
            'suhu_5' => 'Suhu 5',
            'suhu_6' => 'Suhu 6',
            'suhu_7' => 'Suhu 7',
            'suhu_8' => 'Suhu 8'
        ];

        $this->availableColumns = [];

        if ($this->data->isEmpty()) {
            return;
        }

        $firstRow = $this->data->first();
        foreach ($allColumns as $column => $label) {
            if (isset($firstRow->$column) && $firstRow->$column !== null) {
                $this->availableColumns[$column] = $this->customLabels[$column] ?? $label;
            }
        }

        if (Auth::check()) {
            $userId = Auth::id();
            $visibilityConfigs = SensorColumnConfig::where('user_id', $userId)
                ->pluck('is_visible', 'column_name')
                ->toArray();

            $filteredColumns = [];
            foreach ($this->availableColumns as $column => $label) {
                if (isset($visibilityConfigs[$column]) && !$visibilityConfigs[$column]) {
                    continue;
                }
                $filteredColumns[$column] = $label;
            }
            $this->availableColumns = $filteredColumns;
        }
    }

    public function collection()
    {
        return $this->data;
    }

    public function headings(): array
    {
        $headings = ['No', 'Tanggal', 'Waktu'];
        
        foreach ($this->availableColumns as $column => $label) {
            $headings[] = $label;
        }
        
        return $headings;
    }

    public function map($row): array
    {
        static $counter = 0;
        $counter++;

        $datetime = \Carbon\Carbon::parse($row->datetime);
        
        $mappedRow = [
            $counter,
            $datetime->format('d/m/Y'),
            $datetime->format('H:i:s')
        ];

        foreach (array_keys($this->availableColumns) as $column) {
            $value = $row->$column ?? null;
            $mappedRow[] = $this->formatValue($value);
        }

        return $mappedRow;
    }

    protected function formatValue($value)
    {
        if ($value === null || $value === '') {
            return '-';
        }

        if (is_numeric($value)) {
            $numValue = (float)$value;
            
            if ($numValue == 0) {
                return 0;
            }

            $formatted = number_format($numValue, 2, '.', '');
            $formatted = rtrim($formatted, '0');
            $formatted = rtrim($formatted, '.');
            
            return $formatted;
        }

        return $value;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Tambahkan 6 baris kosong untuk header
                $sheet->insertNewRowBefore(1, 6);

                // Judul
                $sheet->setCellValue('A1', 'PT LAUTAN AIR INDONESIA');
                $sheet->mergeCells('A1:D1');
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 14],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                // Sub Judul
                $sheet->setCellValue('A2', 'LAPORAN DATA SENSOR');
                $sheet->mergeCells('A2:D2');
                $sheet->getStyle('A2')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 12],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                // Baris Customer & Periode
                $sheet->setCellValue('A4', 'Customer: ' . $this->username);
                $sheet->setCellValue('C4', 'Periode: ' . $this->dateRange);

                // Baris Parameter & Export Date
                $parameterLabel = $this->parameter ?
                    ($this->customLabels[$this->parameter] ?? $this->parameter) :
                    'Semua Parameter';

                $sheet->setCellValue('A5', 'Parameter: ' . $parameterLabel);
                $sheet->setCellValue('C5', 'Tanggal Export: ' . now()->format('d/m/Y H:i:s'));

                // Styling info header
                $sheet->getStyle('A4:C5')->applyFromArray([
                    'font' => ['size' => 10],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
                ]);

                // Border untuk info header
                $sheet->getStyle('A4:D5')->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => '000000']
                        ]
                    ]
                ]);

                // Header tabel data
                $headerRow = 7;
                $lastColumn = $sheet->getHighestColumn();
                $lastRow = $sheet->getHighestRow();

                $sheet->getStyle("A{$headerRow}:{$lastColumn}{$headerRow}")
                    ->applyFromArray([
                        'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => '4F81BD']
                        ],
                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_CENTER,
                            'vertical' => Alignment::VERTICAL_CENTER
                        ],
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                                'color' => ['rgb' => '000000']
                            ]
                        ]
                    ]);

                // Rapiin tabel data (semua rata tengah + border)
                $sheet->getStyle("A8:{$lastColumn}{$lastRow}")
                    ->applyFromArray([
                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_CENTER,
                            'vertical' => Alignment::VERTICAL_CENTER,
                            'wrapText' => true
                        ],
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                                'color' => ['rgb' => '000000']
                            ]
                        ]
                    ]);

                // Zebra striping
                for ($row = 8; $row <= $lastRow; $row++) {
                    if ($row % 2 == 0) {
                        $sheet->getStyle("A{$row}:{$lastColumn}{$row}")->applyFromArray([
                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'startColor' => ['rgb' => 'F0F8FF']
                            ]
                        ]);
                    }
                }
            }
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Auto-size columns
        foreach (range('A', $sheet->getHighestColumn()) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        return [];
    }

    public function title(): string
    {
        return 'Sensor Data';
    }
}
