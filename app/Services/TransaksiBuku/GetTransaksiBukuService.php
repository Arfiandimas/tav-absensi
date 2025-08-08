<?php

namespace App\Services\TransaksiBuku;

use App\Base\ServiceBase;
use App\Models\Anggota;
use App\Models\TransaksiPeminjaman;
use App\Responses\ServiceResponse;
use Illuminate\Support\Facades\Log;

class GetTransaksiBukuService extends ServiceBase
{
    protected ?int $id;
    protected bool $pengembalian;
    
    public function __construct()
    {
        $this->id = null;
        $this->pengembalian = false;
    }

    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    public function setPengembalian()
    {
        $this->pengembalian = true;
        return $this;
    }

    public function call(): ServiceResponse
    {
        try {
            if ($this->id) {
                $data = TransaksiPeminjaman::with('anggota', 'buku')->whereId($this->id)->first();
            } else {
                $data = TransaksiPeminjaman::with('anggota', 'buku')
                    ->when(!$this->pengembalian, function ($query){
                        $query->where('tanggal_kembali', null);
                    })
                    ->when($this->pengembalian, function ($query){
                        $query->where('tanggal_kembali', '!=', null);
                    })
                    ->orderBy("created_at", "desc")
                    ->paginate(10);
            }
            return self::success($data);
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