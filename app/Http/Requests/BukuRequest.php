<?php

namespace App\Http\Requests;

class BukuRequest extends ReqValidator
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'judul_buku' => 'string|max:100',
            'penerbit' => 'string|max:100',
            'lebar' => 'numeric',
            'tinggi' => 'numeric',
            'stock' => 'integer|max:99999'
        ];
        
        if($this->getMethod() == 'POST'){
            $rules = $this->addRequired($rules);
        }

        return $rules;
    }
}
