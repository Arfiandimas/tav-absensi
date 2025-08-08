<?php

namespace App\Services\Dashboard;

use App\Base\ServiceBase;
use App\Models\Buku;
use App\Responses\ServiceResponse;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class BukuTopFiveService extends ServiceBase
{
    public function __construct()
    {
        
    }
    
    public function call(): ServiceResponse
    {
        try {
            $data = Buku::withCount(['transaksiPeminjaman' => function ($query) {
                $query->whereBetween('tanggal_pinjam', [
                    Carbon::now()->startOfWeek(),
                    Carbon::now()->endOfWeek()
                ]);
            }])
            ->having('transaksi_peminjaman_count', '>', 0)
            ->orderByDesc('transaksi_peminjaman_count')
            ->take(5)
            ->get();
            return self::success($data, 'Berhasil');
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