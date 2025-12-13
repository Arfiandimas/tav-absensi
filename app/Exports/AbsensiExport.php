<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class AbsensiExport implements FromArray, WithHeadings, WithEvents
{
    protected $start, $end, $user_id, $departemen_id;
    protected $dates;

    public function __construct($start, $end, $user_id = null, $departemen_id = null)
    {
        $this->start = $start;
        $this->end = $end;
        $this->user_id = $user_id;
        $this->departemen_id = $departemen_id;

        // generate tanggal
        $this->dates = collect();
        $period = new \DatePeriod(
            new \DateTime($start),
            new \DateInterval('P1D'),
            (new \DateTime($end))->modify('+1 day')
        );

        foreach ($period as $date) {
            $this->dates->push($date->format('Y-m-d'));
        }
    }

    public function array(): array
    {
        DB::beginTransaction();

        DB::statement("
            SELECT fn_loadabsensi(?, ?, ?, ?)
        ", [
            $this->start,
            $this->end,
            $this->departemen_id,
            $this->user_id
        ]);

        $results = DB::select("FETCH ALL FROM pivot_cursor");

        DB::commit();

        $rows = [];

        foreach ($results as $row) {
            $rowData = [
                $row->nama_lengkap,
                $row->departemen,
            ];

            foreach ($this->dates as $tanggal) {
                $clockInCol  = "{$tanggal} Clock In";
                $clockOutCol = "{$tanggal} Clock Out";

                $rowData[] = $row->$clockInCol ?? '-';
                $rowData[] = $row->$clockOutCol ?? '-';
            }

            $rows[] = $rowData;
        }

        return $rows;
    }

    public function headings(): array
    {
        $periodeRow = [
            'Periode ' .
            \Carbon\Carbon::parse($this->start)->translatedFormat('d F Y') .
            ' - ' .
            \Carbon\Carbon::parse($this->end)->translatedFormat('d F Y')
        ];

        $row1 = ['Nama', 'Departemen'];
        foreach ($this->dates as $tanggal) {
            $row1[] = date('j', strtotime($tanggal));
            $row1[] = '';
        }

        $row2 = ['', ''];
        foreach ($this->dates as $tanggal) {
            $row2[] = 'Clock In';
            $row2[] = 'Clock Out';
        }

        return [$periodeRow, $row1, $row2];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastCol = $sheet->getHighestColumn();

                $sheet->mergeCells("A1:{$lastCol}1");
                $sheet->getStyle("A1")->getFont()->setBold(true)->setSize(14);

                $colIndex = 3;
                foreach ($this->dates as $tanggal) {
                    $start = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex);
                    $end   = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex + 1);
                    $sheet->mergeCells("{$start}2:{$end}2");
                    $colIndex += 2;
                }

                $sheet->getStyle("A2:{$sheet->getHighestColumn()}3")->getFont()->setBold(true);

                foreach(range('A', $sheet->getHighestColumn()) as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }

                $sheet->getStyle(
                    'A2:' . $sheet->getHighestColumn() . $sheet->getHighestRow()
                )->getAlignment()
                 ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
                 ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
            }
        ];
    }
}
