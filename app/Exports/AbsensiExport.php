<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

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
            AfterSheet::class => function (AfterSheet $event) {

                $sheet = $event->sheet->getDelegate();

                // kolom terakhir (huruf & index)
                $highestColumnLetter = $sheet->getHighestColumn();
                $highestColumnIndex  = Coordinate::columnIndexFromString($highestColumnLetter);

                /* ===============================
                *  MERGE PERIODE (ROW 1)
                * =============================== */
                $sheet->mergeCells("A1:{$highestColumnLetter}1");
                $sheet->getStyle("A1")->getFont()->setBold(true)->setSize(14);

                /* ===============================
                *  MERGE TANGGAL (ROW 2)
                * =============================== */
                $colIndex = 3; // mulai dari kolom C
                foreach ($this->dates as $tanggal) {
                    $start = Coordinate::stringFromColumnIndex($colIndex);
                    $end   = Coordinate::stringFromColumnIndex($colIndex + 1);
                    $sheet->mergeCells("{$start}2:{$end}2");
                    $colIndex += 2;
                }

                /* ===============================
                *  BOLD HEADING
                * =============================== */
                $sheet->getStyle("A2:{$highestColumnLetter}3")
                    ->getFont()
                    ->setBold(true);

                /* ===============================
                *  AUTO WIDTH (AMAN > Z)
                * =============================== */
                for ($col = 1; $col <= $highestColumnIndex; $col++) {
                    $columnLetter = Coordinate::stringFromColumnIndex($col);
                    $sheet->getColumnDimension($columnLetter)->setAutoSize(true);
                }

                /* ===============================
                *  CENTER ALIGNMENT
                * =============================== */
                $sheet->getStyle(
                    'A2:' . $highestColumnLetter . $sheet->getHighestRow()
                )->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                ->setVertical(Alignment::VERTICAL_CENTER);
            }
        ];
    }
}
