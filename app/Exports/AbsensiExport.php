<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class AbsensiExport implements FromArray, WithHeadings, WithEvents
{
    protected $start, $end, $user_id, $office_id, $departemen_id;
    protected $dates;

    public function __construct($start, $end, $user_id = null, $office_id = null, $departemen_id = null)
    {
        $this->start = $start;
        $this->end = $end;
        $this->user_id = $user_id;
        $this->office_id = $office_id;
        $this->departemen_id = $departemen_id;

        // generate list tanggal
        $this->dates = collect();
        $period = new \DatePeriod(
            new \DateTime($this->start),
            new \DateInterval('P1D'),
            (new \DateTime($this->end))->modify('+1 day')
        );

        foreach ($period as $date) {
            $this->dates->push($date->format('Y-m-d'));
        }
    }

    public function array(): array
    {
        $users = DB::table('users')
            ->leftJoin('departemen', 'users.departemen_id', '=', 'departemen.id')
            ->select('users.id','users.first_name','users.last_name','departemen.nama as departemen')
            ->when($this->user_id, fn($q)=>$q->where('users.id',$this->user_id))
            ->when($this->departemen_id, fn($q)=>$q->where('users.departemen_id',$this->departemen_id))
            ->when($this->office_id, fn($q)=>$q->where('users.office_id',$this->office_id))
            ->get();

        $rows = [];

        foreach ($users as $user) {
            $row = [
                $user->first_name . ' ' . $user->last_name,
                $user->departemen,
            ];

            foreach ($this->dates as $tanggal) {
                $data = DB::table('absensi')
                    ->select(
                        DB::raw("MIN(CASE WHEN type='Clock In' THEN TIME(waktu) END) as clock_in"),
                        // DB::raw("MIN(CASE WHEN type='Clock In' AND TIME(waktu) BETWEEN '12:00:00' AND '15:00:00' THEN TIME(waktu) END) as clock_in_siang"),
                        DB::raw("MIN(CASE WHEN type='Clock Out' THEN TIME(waktu) END) as clock_out")
                    )
                    ->where('user_id',$user->id)
                    ->whereDate('waktu',$tanggal)
                    ->first();

                $row[] = $data->clock_in ? date('H:i', strtotime($data->clock_in)) : '-';
                // $row[] = $data->clock_in_siang ? date('H:i', strtotime($data->clock_in_siang)) : '-';
                $row[] = $data->clock_out ? date('H:i', strtotime($data->clock_out)) : '-';
            }

            $rows[] = $row;
        }

        return $rows;
    }

    public function headings(): array
    {
        // baris periode
        $periodeRow = ['Periode ' . \Carbon\Carbon::parse($this->start)->translatedFormat('d F Y') 
            . ' - ' . \Carbon\Carbon::parse($this->end)->translatedFormat('d F Y')];

        // baris pertama (tanggal aja)
        $row1 = ['Nama', 'Departemen'];
        foreach ($this->dates as $tanggal) {
            $day = date('j', strtotime($tanggal));
            $row1[] = $day;
            // $row1[] = '';
            $row1[] = '';
        }

        // baris kedua (sub kolom)
        $row2 = ['', ''];
        foreach ($this->dates as $tanggal) {
            $row2[] = 'Clock In';
            // $row2[] = 'Clock In Siang';
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

                // merge cell periode di baris 1
                $sheet->mergeCells("A1:{$lastCol}1");
                $sheet->getStyle("A1")->getFont()->setBold(true)->setSize(14);
                $sheet->getStyle("A1")->getAlignment()
                    ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT) // rata kiri
                    ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

                // merge cell tanggal di baris kedua
                $colIndex = 3; // kolom mulai dari C
                foreach ($this->dates as $tanggal) {
                    $colStart = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex);
                    $colEnd   = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex+1);
                    // kalau aktifkan clock_in_siang, ubah jadi +2
                    $sheet->mergeCells("{$colStart}2:{$colEnd}2");
                    $colIndex += 2; // kalau aktifkan clock_in_siang ubah jadi += 3
                }

                // bold heading
                $sheet->getStyle('A2:'.$sheet->getHighestColumn().'3')->getFont()->setBold(true);

                // auto width
                foreach(range('A',$sheet->getHighestColumn()) as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }

                // center alignment semua cell
                $sheet->getStyle('A2:'.$sheet->getHighestColumn().$sheet->getHighestRow())
                    ->getAlignment()
                    ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
                    ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
            },
        ];
    }
}
