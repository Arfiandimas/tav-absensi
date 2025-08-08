<?php

namespace App\Http\Requests;

class PeminjamanRequest extends ReqValidator
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'anggota_id' => 'exists:anggotas,id',
            'buku_id' => 'exists:bukus,id',
            'tanggal_pinjam' => 'date_format:Y-m-d'
        ];
        
        if($this->getMethod() == 'POST'){
            $rules = $this->addRequired($rules);
        }

        return $rules;
    }
}
