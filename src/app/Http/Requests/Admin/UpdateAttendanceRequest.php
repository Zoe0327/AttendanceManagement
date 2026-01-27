<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
use Carbon\Carbon;

class UpdateAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'work_start' => ['required'],
            'work_end'   => ['required'],
            'breaks.*.start' => ['nullable', 'date_format:H:i'],
            'breaks.*.end'   => ['nullable', 'date_format:H:i'],
            'remark' => ['required'],
        ];
    }

    public function messages(): array
    {
        return [
            'remark.required' => '備考を記入してください',
        ];
    }

    public function withValidator(Validator $validator)
    {
        $validator->after(function ($validator) {

            if (!$this->work_start || !$this->work_end) {
                return;
            }

            try {
                $start = Carbon::createFromFormat('H:i', $this->work_start);
                $end   = Carbon::createFromFormat('H:i', $this->work_end);
            } catch (\Exception $e) {
                $validator->errors()->add(
                    'work_end',
                    '出勤時間もしくは退勤時間が不適切な値です'
                );
                return;
            }

            // 出勤 >= 退勤
            if ($start >= $end) {
                $validator->errors()->add(
                    'work_end',
                    '出勤時間もしくは退勤時間が不適切な値です'
                );
                return;
            }

            foreach ($this->breaks ?? [] as $index => $break) {

                if (empty($break['start']) || empty($break['end'])) {
                    continue;
                }

                try {
                    $breakStart = Carbon::createFromFormat('H:i', $break['start']);
                    $breakEnd   = Carbon::createFromFormat('H:i', $break['end']);
                } catch (\Exception $e) {
                    $validator->errors()->add(
                        "breaks.$index.start",
                        '休憩時間が不適切な値です'
                    );
                    $validator->errors()->add(
                        "breaks.$index.end",
                        '休憩時間が不適切な値です'
                    );
                    continue;
                }

                // 休憩開始 >= 終了
                if ($breakStart >= $breakEnd) {
                    $validator->errors()->add(
                        "breaks.$index.end",
                        '休憩時間が不適切な値です'
                    );
                }

                // 勤務時間外
                if ($breakStart < $start || $breakEnd > $end) {
                    $validator->errors()->add(
                        "breaks.$index.end",
                        '休憩時間もしくは退勤時間が不適切な値です'
                    );
                }
            }
        });
    }
}
