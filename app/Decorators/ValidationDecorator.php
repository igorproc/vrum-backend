<?php

namespace App\Decorators;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\MessageBag;

class ValidationDecorator
{
    public function validate(array $rules, array | null $data = []): MessageBag | array
    {
        if (!$data) {
            return new MessageBag(['all' => 'required fileds aren\'t sent']);
        }

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return $validator->errors();
        }

        return $data;
    }
}
