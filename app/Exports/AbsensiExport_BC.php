<?php

namespace App\Exports;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class AbsensiExport_BC implements FromCollection, WithHeadings
{
    protected $start, $end, $user_id, $office_id, $departemen_id;

    public function __construct($start, $end, $user_id = null, $office_id = null, $departemen_id = null)
    {
        $this->start = $start;
        $this->end = $end;
        $this->user_id = $user_id;
        $this->office_id = $office_id;
        $this->departemen_id = $departemen_id;
    }

    public function collection()
    {
        $start = $this->start;
        $end = $this->end;
        $dateConditionClockIn = function ($query) use ($start, $end) {
            $query->whereBetween(DB::raw('DATE(waktu)'), [$start, $end]);
        };
        $dateConditionClockOut = function ($query) use ($start, $end) {
            $query->whereBetween(DB::raw('DATE(waktu)'), [$start, $end]);
        };
        $user_id = $this->user_id;
        $departemen_id = $this->departemen_id;
        $office_id = $this->office_id;

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

        $clockInsSiang = DB::table('absensi as a2')
                ->select('a2.user_id', DB::raw('DATE(a2.waktu) as tanggal'), 'a2.waktu as clock_in_siang_time', 'a2.mlat as clock_in_siang_mlat', 'a2.mlong as clock_in_siang_mlong', 'a2.foto as clock_in_siang_foto')
                ->where('a2.type', 'Clock In')
                ->when($user_id, function ($query) use ($user_id) {
                    $query->where('a2.user_id', $user_id);
                })
                ->where($dateConditionClockIn)
                ->whereRaw('a2.waktu = (
                    SELECT MIN(a3.waktu) FROM absensi a3
                    WHERE a3.user_id = a2.user_id AND a3.type = "Clock In" AND DATE(a3.waktu) = DATE(a2.waktu) AND TIME(a3.waktu) >= "12:00:00" AND TIME(a3.waktu) <= "15:00:00"
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

        $results = User::
                leftJoinSub($clockIns, 'clock_in', function ($join) {
                    $join->on('users.id', '=', 'clock_in.user_id');
                })
                ->leftJoinSub($clockInsSiang, 'clock_in_siang', function ($join) {
                    $join->on('users.id', '=', 'clock_in_siang.user_id')
                        ->on('clock_in.tanggal', '=', 'clock_in_siang.tanggal');
                })
                ->leftJoinSub($clockOuts, 'clock_out', function ($join) {
                    $join->on('users.id', '=', 'clock_out.user_id')
                        ->on('clock_in.tanggal', '=', 'clock_out.tanggal');
                })
                ->leftJoin('departemen', 'users.departemen_id', '=', 'departemen.id')
                ->when($user_id, function ($query) use ($user_id) {
                    $query->where('users.id', $user_id);
                })
                ->when($departemen_id, function ($query, $departemen_id){
                    $query->where('users.departemen_id', $departemen_id);
                })
                ->when($office_id, function ($query, $office_id){
                    $query->where('users.office_id', $office_id);
                })
                ->select(
                    DB::raw("CONCAT(users.first_name, ' ', users.last_name) as full_name"),
                    'departemen.nama',
                    'clock_in.tanggal',
                    DB::raw("DATE_FORMAT(clock_in.clock_in_time, '%H:%i') as clock_in_time"),
                    DB::raw("DATE_FORMAT(clock_in_siang.clock_in_siang_time, '%H:%i') as clock_in_siang_time"),
                    DB::raw("DATE_FORMAT(clock_out.clock_out_time, '%H:%i') as clock_out_time")
                )
                ->orderBy('users.id', 'asc')
                ->orderBy('clock_in.tanggal', 'asc')
                ->get();
        return $results;
    }

    public function headings(): array
    {
        return ['Nama', 'Departemen', 'Tanggal', 'Clock In', 'Clock In Siang', 'Clock Out'];
    }
}
