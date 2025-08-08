<?php

namespace App\Services\Anggota;

use App\Base\ServiceBase;
use App\Models\Anggota;
use App\Responses\ServiceResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AddUpdateAnggotaService extends ServiceBase
{
    protected Request $request;
    protected ?int $id;
    
    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->id = null;
    }

    public function setId($id){
        $this->id = $id;
        return $this;
    }

    public function call(): ServiceResponse
    {
        try {
            $this->request->request->remove('_token');
            $this->request->request->remove('_method');
            if ($this->id) {
                $data = Anggota::findOrFail($this->id);
                $data->fill($this->request->all());
                $data->save();
            } else {
                $data = Anggota::create($this->request->all());
            }
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