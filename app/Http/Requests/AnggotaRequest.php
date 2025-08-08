<?php

namespace App\Http\Requests;

class AnggotaRequest extends ReqValidator
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'nama' => 'string|max:100',
            'no_anggota' => 'integer|max:999999999999||unique:anggotas,no_anggota,'.$this->id,
            'tanggal_lahir' => 'date_format:Y-m-d'
        ];
        
        if($this->getMethod() == 'POST'){
            $rules = $this->addRequired($rules);
        }

        return $rules;
    }
}
