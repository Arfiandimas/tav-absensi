<?php

namespace App\Services\TransaksiBuku;

use App\Base\ServiceBase;
use App\Models\Anggota;
use App\Models\Buku;
use App\Models\TransaksiPeminjaman;
use App\Responses\ServiceResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class PengembalianBukuService extends ServiceBase
{
    protected Request $request;
    
    public function __construct(Request $request, protected int $id)
    {
        $this->request = $request;
    }

    public function call(): ServiceResponse
    {
        DB::beginTransaction();
        try {
            $data = TransaksiPeminjaman::whereId($this->id)->first();
            $data->tanggal_kembali = $this->request->tanggal_kembali;
            $data->update();

            // update stock buku
            $buku = Buku::whereId($data->buku_id)->first();
            $buku->stock = $buku->stock + 1;
            $buku->update();

            // update stock anggota
            Anggota::whereId($data->anggota_id)->update(["stock" =>TransaksiPeminjaman::where(['anggota_id' => $data->anggota_id, 'tanggal_kembali' => null])->count()]);
            
            DB::commit();
            return self::success($data, "berhasil melakukan pengembalian");
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error(self::class, [
                'Message ' => $th->getMessage(),
                'On file ' => $th->getFile(),
                'On line ' => $th->getLine()
            ]);
            return self::error(null, $th->getMessage());
        }
    }
}