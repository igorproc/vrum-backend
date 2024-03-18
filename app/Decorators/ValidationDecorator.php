<?php

namespace App\Decorators;

use Illuminate\Support\Facades\Validator;

class ValidationDecorator
{
    public function validate(array $rules, array $data = [])
    {
        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return $validator->errors();
        }

        return $data;
    }
}
