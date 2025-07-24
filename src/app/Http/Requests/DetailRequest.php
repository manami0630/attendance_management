<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DetailRequest extends FormRequest
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
        return [
            'name' => 'required',
            'postal_code' => 'required|regex:/^\d{3}-\d{4}$/',
            'address' => 'required',
            'building_name' => 'nullable',
        ];
    }
    public function messages()
    {
        return [
            '' => '出勤時間もしくは退勤時間が不適切な値です',
            '' => '休憩時間が不適切な値です',
            '' => '休憩時間もしくは退勤時間が不適切な値です',
            '.required' => '備考を記入してください',
        ];
    }
}
