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

class AddTransaksiBukuService extends ServiceBase
{
    protected Request $request;
    
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function call(): ServiceResponse
    {
        DB::beginTransaction();
        try {
            $this->request->request->remove('_token');
            $this->request->request->remove('_method');
            
            // update stock peminjaman anggota
            $anggota = Anggota::whereId($this->request->anggota_id)->first();
            $anggota->stock = $anggota->stock + 1;
            $anggota->update();

            // update stock buku
            $buku = Buku::whereId($this->request->buku_id)->first();
            $buku->stock = $buku->stock - 1;
            if ($buku->stock < 0) {
                return self::error(null, 'stok buku habis');
            }
            $buku->update();

            $data = TransaksiPeminjaman::create($this->request->all());

            DB::commit();
            return self::success($data, 'berhasil melakukan peminjaman');
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