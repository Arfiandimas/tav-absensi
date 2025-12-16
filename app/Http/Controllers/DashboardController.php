<?php

namespace App\Http\Controllers;

use App\Models\Departemen;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AbsensiExport;
use App\Models\Absensi;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        if (!session("is_logged_in")) {
            return view("dashboard");
        }

        $departements = Departemen::when(Auth::Id() == 1, function ($query) {
            $query->where("type", "bursa_mobil");
        })
        ->when(Auth::Id() == 2, function ($query) {
            $query->where("type", "perumahan");
        })
        ->get();

        $users = User::select("id", "nama_lengkap")
            ->when($request->departemen_id, function ($query, $departemen_id){
                $query->where("departemen_id", $departemen_id);
            })
            ->where('departemen_id', '!=', null)
            ->get();

        $start= $request->start_date;
        $end = $request->end_date;

        if (!$start || !$end) {
            $start = Carbon::now()->startOfMonth()->toDateString();
            $end = Carbon::now()->endOfMonth()->toDateString();
        }

        $dateConditionClockIn = function ($query) use ($start, $end) {
            $query->whereBetween(DB::raw("DATE(waktu)"), [$start, $end]);
        };

        $dateConditionClockOut = function ($query) use ($start, $end) {
            $query->whereBetween(DB::raw("DATE(waktu)"), [$start, $end]);
        };

        $clockIns = DB::table("absensi as a1")
            ->select("a1.user_id", DB::raw("DATE(a1.waktu) as tanggal"), "a1.waktu as clock_in_time", "a1.mlat as clock_in_mlat", "a1.mlong as clock_in_mlong", "a1.foto as clock_in_foto")
            ->where("a1.type", "Clock In")
            ->when($request->user_id, function ($query) use ($request) {
                $query->where("a1.user_id", $request->user_id);
            })
            ->where($dateConditionClockIn)
            ->whereRaw("a1.waktu = (
                SELECT MIN(a2.waktu) FROM absensi a2
                WHERE a2.user_id = a1.user_id AND a2.type = 'Clock In' AND DATE(a2.waktu) = DATE(a1.waktu)
            )")
            ->orderBy("tanggal", "asc");

        $clockInsSiang = DB::table("absensi as a2")
            ->select("a2.user_id", DB::raw("DATE(a2.waktu) as tanggal"), "a2.waktu as clock_in_siang_time", "a2.mlat as clock_in_siang_mlat", "a2.mlong as clock_in_siang_mlong", "a2.foto as clock_in_siang_foto")
            ->where("a2.type", "Clock In")
            ->when($request->user_id, function ($query) use ($request) {
                $query->where("a2.user_id", $request->user_id);
            })
            ->where($dateConditionClockIn)
            ->whereRaw("
                a2.waktu = (
                    SELECT MIN(a3.waktu)
                    FROM absensi a3
                    WHERE a3.user_id = a2.user_id
                      AND a3.type = 'Clock In'
                      AND DATE(a3.waktu) = DATE(a2.waktu)
                      AND TO_CHAR(a3.waktu, 'HH24:MI:SS') >= '12:00:00'
                      AND TO_CHAR(a3.waktu, 'HH24:MI:SS') <= '15:00:00'
                )
            ")
            ->orderBy("tanggal", "asc");

        $clockOuts = DB::table("absensi as b1")
            ->select("b1.user_id", DB::raw("DATE(b1.waktu) as tanggal"), "b1.waktu as clock_out_time", "b1.foto as clock_out_foto", "b1.mlat as clock_out_mlat", "b1.mlong as clock_out_mlong")
            ->where("b1.type", "Clock Out")
            ->when($request->user_id, function ($query) use ($request) {
                $query->where("b1.user_id", $request->user_id);
            })
            ->where($dateConditionClockOut)
            ->whereRaw("
                b1.waktu = (
                    SELECT MIN(b2.waktu)
                    FROM absensi b2
                    WHERE b2.user_id = b1.user_id
                      AND b2.type = 'Clock Out'
                      AND DATE(b2.waktu) = DATE(b1.waktu)
                )
            ")
            ->orderBy("tanggal", "asc");
            
        $results = User::
            leftJoinSub($clockIns, "clock_in", function ($join) {
                $join->on("user.id", "=", "clock_in.user_id");
            })
            ->leftJoinSub($clockInsSiang, "clock_in_siang", function ($join) {
                $join->on("user.id", "=", "clock_in_siang.user_id")
                    ->on("clock_in.tanggal", "=", "clock_in_siang.tanggal");
            })
            ->leftJoinSub($clockOuts, "clock_out", function ($join) {
                $join->on("user.id", "=", "clock_out.user_id")
                    ->on("clock_in.tanggal", "=", "clock_out.tanggal");
            })
            ->when($request->user_id, function ($query) use ($request) {
                $query->where("user.id", $request->user_id);
            })
            ->when($request->departemen_id, function ($query, $departemen_id){
                $query->where("user.departemen_id", $departemen_id);
            })
            ->select(
                "user.id as user_id",
                "nama_lengkap as full_name",
                "clock_in.tanggal",
                "clock_in.clock_in_time",
                "clock_in.clock_in_mlat",
                "clock_in.clock_in_mlong",
                "clock_in.clock_in_foto",
                "clock_in_siang.clock_in_siang_time",
                "clock_in_siang.clock_in_siang_mlat",
                "clock_in_siang.clock_in_siang_mlong",
                "clock_in_siang.clock_in_siang_foto",
                "clock_out.clock_out_time",
                "clock_out.clock_out_foto",
                "clock_out.clock_out_mlat",
                "clock_out.clock_out_mlong"
            )
            ->where("user.departemen_id", "!=", null)
            ->orderBy("user.id", "asc")
            ->orderBy("clock_in.tanggal", "asc")
            ->paginate(10)
            ->appends($request->all());

        return view("dashboard", compact("results", "start", "end", "departements", "users"));
    }

    public function getByDepartemen(Request $request)
    {
        $departemenId = $request->input("departemen_id");

        $users = User::select("id", "nama_lengkap")
            ->when($departemenId, fn($q) => $q->where("departemen_id", $departemenId))
            ->orderBy("nama_lengkap")
            ->get();

        return response()->json($users);
    }

    public function exportExcel(Request $request) {
        $start = $request->start_date ?? Carbon::now()->startOfMonth()->toDateString();
        $end = $request->end_date ?? Carbon::now()->endOfMonth()->toDateString();
        $user_id = $request->user_id;
        $departemen_id = $request->departemen_id;
        return Excel::download(new AbsensiExport($start, $end, $user_id, $departemen_id), "absensi".$start." sampai ".$end.".xlsx");
    }

    public function absensiStore(Request $request) {
        $validated = $request->validate([
            'user_id'       => ['required', 'exists:user,id'],
            'type'       => ['required', 'in:Clock In,Clock Out'],
            'tanggal'       => ['required', 'date'],
        ]);
        
        // Optional: normalisasi timezone (kalau perlu)
        $tanggal = Carbon::parse($validated['tanggal']);

        $data = new Absensi();
        $data->user_id = $request->user_id;
        $data->type = $request->type;
        $data->waktu = $tanggal;
        $data->mlat = "-6.9971806";
        $data->mlong = "110.4338661";
        $data->foto = "default.png";
        $data->save();

        return redirect()
            ->back()
            ->with('success', 'Absensi berhasil ditambahkan');
    }
}
