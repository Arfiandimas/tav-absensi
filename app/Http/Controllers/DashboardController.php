<?php

namespace App\Http\Controllers;

use App\Models\Departemen;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AbsensiExport;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        if (session('is_logged_in')) {
            $start= $request->start_date;
            $end = $request->end_date;

            if (!$start || !$end) {
                $start = Carbon::now()->startOfMonth()->toDateString();
                $end = Carbon::now()->endOfMonth()->toDateString();
            }

            $dateConditionClockIn = function ($query) use ($start, $end) {
                $query->whereBetween(DB::raw('DATE(a1.waktu)'), [$start, $end]);
            };

            $dateConditionClockOut = function ($query) use ($start, $end) {
                $query->whereBetween(DB::raw('DATE(b1.waktu)'), [$start, $end]);
            };

            $clockIns = DB::table('absensi as a1')
                ->select('a1.user_id', DB::raw('DATE(a1.waktu) as tanggal'), 'a1.waktu as clock_in_time', 'a1.mlat as clock_in_mlat', 'a1.mlong as clock_in_mlong', 'a1.foto as clock_in_foto')
                ->where('a1.type', 'Clock In')
                ->when($request->user_id, function ($query) use ($request) {
                    $query->where('a1.user_id', $request->user_id);
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
                ->when($request->user_id, function ($query) use ($request) {
                    $query->where('b1.user_id', $request->user_id);
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
                ->when($request->user_id, function ($query) use ($request) {
                    $query->where('users.id', $request->user_id);
                })
                ->select(
                    'users.id as user_id',
                    DB::raw("CONCAT(users.first_name, ' ', users.last_name) as full_name"),
                    'clock_in.tanggal',
                    'clock_in.clock_in_time',
                    'clock_in.clock_in_mlat',
                    'clock_in.clock_in_mlong',
                    'clock_in.clock_in_foto',
                    'clock_out.clock_out_time',
                    'clock_out.clock_out_foto',
                    'clock_out.clock_out_mlat',
                    'clock_out.clock_out_mlong'
                )
                ->orderBy('users.id', 'asc')
                ->orderBy('clock_in.tanggal', 'asc')
                ->paginate(10);

            $departements = Departemen::get();
            $users = User::select('id', 'first_name', 'last_name')->when($request->departemen_id, function ($query, $departemen_id){
                    $query->where('departemen_id', $departemen_id);
                })->get();
            return view('dashboard', compact('results', 'start', 'end', 'departements', 'users'));
        }

        return view('dashboard');
    }

    public function getByDepartemen(Request $request)
    {
        $departemenId = $request->input('departemen_id');

        $users = User::where(function ($q) use ($departemenId) {
                if ($departemenId) {
                    $q->where('departemen_id', $departemenId);
                }
            })
            ->select('id', 'first_name', 'last_name')
            ->orderBy('first_name')
            ->get();

        return response()->json($users);
    }

    public function exportExcel(Request $request) {
        $start = $request->start_date ?: Carbon::now()->startOfMonth()->toDateString();
        $end = $request->end_date ?: Carbon::now()->endOfMonth()->toDateString();
        $user_id = $request->user_id;
        $user = User::where('id', $user_id)->first();

        return Excel::download(new AbsensiExport($start, $end, $user_id), 'absensi'.$start.' sampai '.$end.'.xlsx');
    }
}
