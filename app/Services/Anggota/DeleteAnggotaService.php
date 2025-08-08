<?php

namespace App\Services\Anggota;

use App\Base\ServiceBase;
use App\Models\Anggota;
use App\Responses\ServiceResponse;
use Illuminate\Support\Facades\Log;

class DeleteAnggotaService extends ServiceBase
{
    public function __construct(protected int $id)
    {
        
    }

    public function call(): ServiceResponse
    {
        try {
            $data = Anggota::where('id', $this->id)->first();
            if ($data->stock > 0) {
                return self::error(null, 'Gagal menghapus data, anggota masih memiliki pinjaman buku');
            }
            if ($data) {
                $data->delete();
                return self::success(null, 'Berhasil menghapus data');
            }
            return self::error(null, 'Gagal menghapus data');
        } catch (\Throwable $th) {
            Log::error(self::class, [
                'Message ' => $th->getMessage(),
                'On file ' => $th->getFile(),
                'On line ' => $th->getLine()
            ]);
            return self::error(null, $th->getMessage());
        }
    }
}