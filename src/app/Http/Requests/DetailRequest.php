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
            'remarks' => 'required',
        ];
    }
    public function messages()
    {
        return [
            'remarks.required' => '備考を記入してください',
        ];
    }
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $in_time = strtotime($this->input('clock_in_time'));
            $out_time = strtotime($this->input('clock_out_time'));

            if ($this->filled('clock_in_time') && $this->filled('clock_out_time')) {
                if ($in_time > $out_time) {
                    $validator->errors()->add('clock_out_time', '出勤時間もしくは退勤時間が不適切な値です');
                }
            }

            if ($this->filled('break_start_time')) {
                $break_start = strtotime($this->input('break_start_time'));
                if ($this->filled('clock_in_time')) {
                    if ($break_start < $in_time) {
                        $validator->errors()->add('break_start_time', '休憩時間が不適切な値です');
                    }
                }
                if ($this->filled('clock_out_time')) {
                    if ($break_start > $out_time) {
                        $validator->errors()->add('break_start_time', '休憩時間が不適切な値です');
                    }
                }
            }

            if ($this->filled('break_end_time') && $this->filled('clock_out_time')) {
                $break_end = strtotime($this->input('break_end_time'));
                $clock_out = strtotime($this->input('clock_out_time'));
                if ($break_end > $clock_out) {
                    $validator->errors()->add('break_end_time', '休憩時間もしくは退勤時間が不適切な値です');
                }
            }
        });
    }
}
