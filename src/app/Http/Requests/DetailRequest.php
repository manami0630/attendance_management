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
            'reason' => 'required',
        ];
    }
    public function messages()
    {
        return [
            'reason.required' => '備考を記入してください',
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

            if ($this->filled('break_start_time') && is_array($this->input('break_start_time'))) {
                foreach ($this->input('break_start_time') as $index => $break_start_str) {
                    $break_start = strtotime($break_start_str);

                    if ($this->filled('clock_in_time')) {
                        $in_time = strtotime($this->input('clock_in_time'));
                        if ($break_start < $in_time || $break_start > $out_time) {
                            $validator->errors()->add("break_start_time.$index", '休憩時間が不適切な値です');
                        }
                    }
                }
            }

            if ($this->filled('break_end_time') && is_array($this->input('break_end_time')) && $this->filled('clock_out_time')) {
                foreach ($this->input('break_end_time') as $index => $break_end_str) {
                    $break_end = strtotime($break_end_str);
                    $clock_out = strtotime($this->input('clock_out_time'));

                    if ($break_end > $clock_out) {
                        $validator->errors()->add("break_end_time.$index", '休憩時間もしくは退勤時間が不適切な値です');
                    }
                }
            }
        });
    }
}
