<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class AbsensiExport implements FromCollection, WithHeadings
{
    protected $start, $end, $user_id;

    public function __construct($start, $end, $user_id = null)
    {
        $this->start = $start;
        $this->end = $end;
        $this->user_id = $user_id;
    }

    public function collection()
    {
        $clockIns = DB::table('absensi as a1')
            ->select('a1.user_id', DB::raw('DATE(a1.waktu) as tanggal'), 'a1.waktu as clock_in_time')
            ->where('a1.type', 'Clock In')
            ->whereBetween(DB::raw('DATE(a1.waktu)'), [$this->start, $this->end])
            ->when($this->user_id, function ($q) {
                $q->where('a1.user_id', $this->user_id);
            })
            ->whereRaw('a1.waktu = (
                SELECT MIN(a2.waktu) FROM absensi a2
                WHERE a2.user_id = a1.user_id AND a2.type = "Clock In" AND DATE(a2.waktu) = DATE(a1.waktu)
            )');

        $clockOuts = DB::table('absensi as b1')
            ->select('b1.user_id', DB::raw('DATE(b1.waktu) as tanggal'), 'b1.waktu as clock_out_time')
            ->where('b1.type', 'Clock Out')
            ->whereBetween(DB::raw('DATE(b1.waktu)'), [$this->start, $this->end])
            ->when($this->user_id, function ($q) {
                $q->where('b1.user_id', $this->user_id);
            })
            ->whereRaw('b1.waktu = (
                SELECT MIN(b2.waktu) FROM absensi b2
                WHERE b2.user_id = b1.user_id AND b2.type = "Clock Out" AND DATE(b2.waktu) = DATE(b1.waktu)
            )');

        return DB::table('users')
            ->leftJoinSub($clockIns, 'clock_in', function ($join) {
                $join->on('users.id', '=', 'clock_in.user_id');
            })
            ->leftJoinSub($clockOuts, 'clock_out', function ($join) {
                $join->on('users.id', '=', 'clock_out.user_id')
                     ->on('clock_in.tanggal', '=', 'clock_out.tanggal');
            })
            ->leftJoin('departemen', 'users.departemen_id', '=', 'departemen.id')
            ->select(
                DB::raw("CONCAT(users.first_name, ' ', users.last_name) as Nama"),
                'departemen.nama',
                'clock_in.tanggal as Tanggal',
                'clock_in.clock_in_time as Clock In',
                'clock_out.clock_out_time as Clock Out'
            )
            ->get();
    }

    public function headings(): array
    {
        return ['Nama', 'Departemen', 'Tanggal', 'Clock In', 'Clock Out'];
    }
}
