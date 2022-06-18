<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;

class StoreShopRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $regex = '/^[\+]?[(]?[0-9]{3}[)]?[-\s\.]?[0-9]{3}[-\s\.]?[0-9]{4,6}$/';
        return [
            'name' => 'required|max:255|min:3',
            'description' => 'nullable|min:10',
            'address' => 'nullable',
            'phone_number' => [
                'nullable',
                'regex:' . $regex
            ],
            'email' => [
                'required',
                'email:rfc',
                Rule::unique('shops'),
            ],
            'manager_name' => 'required|min:3|max:255'
        ];
    }
}
