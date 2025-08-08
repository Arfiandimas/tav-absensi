<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

abstract class ReqValidator extends FormRequest
{
    protected function addRequired($validateRules, $except = []) {
        foreach($validateRules as $key=>$n){
            if(in_array($key,$except) && !empty($except)){
                continue;
            }
            else{
                $validateRules[$key] = $n.'|required';
            }
        }
        return $validateRules;
    }
}
