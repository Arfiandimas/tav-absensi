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
        $start = $this->start;
        $end = $this->end;
        $dateConditionClockIn = function ($query) use ($start, $end) {
            $query->whereBetween(DB::raw('DATE(a1.waktu)'), [$start, $end]);
        };
        $dateConditionClockOut = function ($query) use ($start, $end) {
            $query->whereBetween(DB::raw('DATE(b1.waktu)'), [$start, $end]);
        };
        $user_id = $this->user_id;
        $clockIns = DB::table('absensi as a1')
                ->select('a1.user_id', DB::raw('DATE(a1.waktu) as tanggal'), 'a1.waktu as clock_in_time', 'a1.mlat as clock_in_mlat', 'a1.mlong as clock_in_mlong', 'a1.foto as clock_in_foto')
                ->where('a1.type', 'Clock In')
                ->when($user_id, function ($query) use ($user_id) {
                    $query->where('a1.user_id', $user_id);
                })
                ->where($dateConditionClockIn)
                ->whereRaw('a1.waktu = (
                    SELECT MIN(a2.waktu) FROM absensi a2
                    WHERE a2.user_id = a1.user_id AND a2.type = "Clock In" AND DATE(a2.waktu) = DATE(a1.waktu)
                )')
                ->orderBy('tanggal', 'asc');

        $clockOuts = DB::table('absensi as b1')
                ->select('b1.user_id', DB::raw('DATE(b1.waktu) as tanggal'), 'b1.waktu as clock_out_time', 'b1.foto as clock_out_foto', 'b1.mlat as clock_out_mlat', 'b1.mlong as clock_out_mlong')
                ->where('b1.type', 'Clock Out')
                ->when($user_id, function ($query) use ($user_id) {
                    $query->where('b1.user_id', $user_id);
                })
                ->where($dateConditionClockOut)
                ->whereRaw('b1.waktu = (
                    SELECT MIN(b2.waktu) FROM absensi b2
                    WHERE b2.user_id = b1.user_id AND b2.type = "Clock Out" AND DATE(b2.waktu) = DATE(b1.waktu)
                )')
                ->orderBy('tanggal', 'asc');

        $results = DB::table('users')
                ->leftJoinSub($clockIns, 'clock_in', function ($join) {
                    $join->on('users.id', '=', 'clock_in.user_id');
                })
                ->leftJoinSub($clockOuts, 'clock_out', function ($join) {
                    $join->on('users.id', '=', 'clock_out.user_id')
                        ->on('clock_in.tanggal', '=', 'clock_out.tanggal');
                })
                ->leftJoin('departemen', 'users.departemen_id', '=', 'departemen.id')
                ->when($user_id, function ($query) use ($user_id) {
                    $query->where('users.id', $user_id);
                })
                ->select(
                    DB::raw("CONCAT(users.first_name, ' ', users.last_name) as full_name"),
                    'departemen.nama',
                    'clock_in.tanggal',
                    DB::raw("DATE_FORMAT(clock_in.clock_in_time, '%H:%i') as clock_in_time"),
                    DB::raw("DATE_FORMAT(clock_out.clock_out_time, '%H:%i') as clock_out_time")
                )
                ->orderBy('users.id', 'asc')
                ->orderBy('clock_in.tanggal', 'asc')
                ->get();
        return $results;
    }

    public function headings(): array
    {
        return ['Nama', 'Departemen', 'Tanggal', 'Clock In', 'Clock Out'];
    }
}
