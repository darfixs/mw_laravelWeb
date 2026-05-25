<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

abstract class AppRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function failedValidation(Validator $validator): void
    {
        $msgs = array_merge(...array_values($validator->errors()->toArray()));
        throw new HttpResponseException(
            response()->json(['ok' => false, 'mensaje' => implode(', ', $msgs)], 422)
        );
    }
}
