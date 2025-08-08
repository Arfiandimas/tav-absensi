<?php

namespace App\Services\Anggota;

use App\Base\ServiceBase;
use App\Models\Anggota;
use App\Responses\ServiceResponse;
use Illuminate\Support\Facades\Log;

class GetAnggotaService extends ServiceBase
{
    protected ?int $id;
    
    public function __construct()
    {
        $this->id = null;
    }

    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    public function call(): ServiceResponse
    {
        try {
            if ($this->id) {
                $data = Anggota::whereId($this->id)->first();
            } else {
                $data = Anggota::orderBy("updated_at", "desc")->paginate(10);
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