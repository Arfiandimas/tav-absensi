<?php

namespace App\Services\Buku;

use App\Base\ServiceBase;
use App\Models\Buku;
use App\Responses\ServiceResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AddUpdateBukuService extends ServiceBase
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
            $this->request->request->add(['dimensi' => $this->request->lebar.' x '.$this->request->tinggi]);
            $this->request->request->remove('lebar');
            $this->request->request->remove('tinggi');
            if ($this->id) {
                $data = Buku::findOrFail($this->id);
                $data->fill($this->request->all());
                $data->save();
            } else {
                $data = Buku::create($this->request->all());
            }
            return self::success($data, 'berhasil');
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