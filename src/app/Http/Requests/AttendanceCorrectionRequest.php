<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AttendanceCorrectionRequest extends FormRequest
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
            'clock_in' => ['required', 'date_format:H:i'],
            'clock_out' => ['required', 'date_format:H:i'],
            'breaks' => ['nullable', 'array'],
            'breaks.*.break_start' => ['nullable', 'date_format:H:i'],
            'breaks.*.break_end' => ['nullable', 'date_format:H:i'],
            'new_break' => ['nullable', 'array'],
            'new_break.break_start' => ['nullable', 'date_format:H:i'],
            'new_break.break_end' => ['nullable', 'date_format:H:i'],
            'note' => ['required', 'string', 'max:1000'],
        ];
    }

    public function messages()
    {
        return [
            'note.required' => '備考を記入してください。',
        ];
    }
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $clockIn = $this->input('clock_in');
            $clockOut = $this->input('clock_out');
            if (
                !empty($clockIn) &&
                !empty($clockOut) &&
                $clockIn > $clockOut
            ) {
                $validator->errors()->add(
                    'clock_in',
                    '出勤時間もしくは退勤時間が不適切な値です'
                );
            }
            foreach ($this->input('breaks', []) as $breakData) {
                $breakStart = $breakData['break_start'] ?? null;
                $breakEnd = $breakData['break_end'] ?? null;
                if (
                    !empty($breakStart) &&
                    !empty($clockIn) &&
                    !empty($clockOut) &&
                    (
                        $breakStart < $clockIn ||
                        $breakStart > $clockOut
                    )
                ) {
                    $validator->errors()->add(
                        'breaks',
                        '休憩時間が不適切な値です'
                    );
                }
                if (
                    !empty($breakEnd) &&
                    !empty($clockOut) &&
                    $breakEnd > $clockOut
                ) {
                    $validator->errors()->add(
                        'breaks',
                        '休憩時間もしくは退勤時間が不適切な値です'
                    );
                }
            }
            $newBreakStart = $this->input('new_break.break_start');
            $newBreakEnd = $this->input('new_break.break_end');
            if (
                !empty($newBreakStart) &&
                !empty($clockIn) &&
                !empty($clockOut) &&
                (
                    $newBreakStart < $clockIn ||
                    $newBreakStart > $clockOut
                )
            ) {
                $validator->errors()->add(
                    'new_break',
                    '休憩時間が不適切な値です'
                );
            }
            if (
                !empty($newBreakEnd) &&
                !empty($clockOut) &&
                $newBreakEnd > $clockOut
            ) {
                $validator->errors()->add(
                    'new_break',
                    '休憩時間もしくは退勤時間が不適切な値です'
                );
            }
        });
    }
}
