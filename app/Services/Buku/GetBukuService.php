<?php

namespace App\Services\Buku;

use App\Base\ServiceBase;
use App\Models\Buku;
use App\Responses\ServiceResponse;
use Illuminate\Support\Facades\Log;

class GetBukuService extends ServiceBase
{
    protected ?int $id;
    protected bool $isDropdown;
    
    public function __construct()
    {
        $this->id = null;
        $this->isDropdown = false;
    }

    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    public function isDropdown()
    {
        $this->isDropdown = true;
        return $this;
    }

    public function call(): ServiceResponse
    {
        try {
            if ($this->id) {
                $data = Buku::whereId($this->id)->first();
            } else {
                if (!$this->isDropdown) {
                    $data = Buku::orderBy("updated_at", "desc")->paginate(10);
                } else {
                    $data = Buku::
                    when($this->isDropdown, function ($query){
                        $query->where('stock', '>', 0);
                    })
                    ->orderBy("updated_at", "desc")->get();
                }
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