<?php

namespace App\Http\Requests\Attendance;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
use Carbon\Carbon;

class StoreCorrectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'work_start' => ['required'],
            'work_end' => ['required'],
            'remark' => ['required'],
            'breaks.*.start' => ['nullable', 'date_format:H:i'],
            'breaks.*.end' => ['nullable', 'date_format:H:i'],
        ];
    }

    public function messages(): array
    {
        return [
            'work_start.required' => '出勤時間を入力してください',
            'work_end.required' => '退勤時間を入力してください',
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
                    'work_start',
                    '出勤時間もしくは退勤時間が不適切な値です'
                );
                $validator->errors()->add(
                    'work_end',
                    '出勤時間もしくは退勤時間が不適切な値です'
                );
                return;
            }

            // 出勤・退勤の前後関係
            if ($start >= $end) {
                $validator->errors()->add(
                    'work_end',
                    '出勤時間もしくは退勤時間が不適切な値です'
                );
                return;
            }

            $breakTimes = [];
            // 休憩時間チェック
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

                // 休憩開始 > 終了
                if ($breakStart >= $breakEnd) {
                    $validator->errors()->add(
                        "breaks.$index.end",
                        '休憩時間が不適切な値です'
                    );
                }

                if ($breakStart < $start || $breakStart > $end) {
                    $validator->errors()->add(
                        "breaks.$index.start",
                        '休憩時間が不適切な値です'
                    );
                }

                // 休憩終了が退勤より後
                if ($breakEnd > $end) {
                    $validator->errors()->add(
                        "breaks.$index.end",
                        '休憩時間もしくは退勤時間が不適切な値です'
                    );
                }

                //重なりチェック用に保存
                $breakTimes[] = [
                    'start' => $breakStart,
                    'end' => $breakEnd,
                    'index' => $index,
                ];
            }
            // 開始時刻でソートしてから重なり判定（順不同入力でもOK）
            usort($breakTimes, fn($a, $b) => $a['start'] <=> $b['start']);

            //休憩の重なりチェック
            for ($i = 0; $i < count($breakTimes) - 1; $i++) {
                $current = $breakTimes[$i];
                $next = $breakTimes[$i + 1];

                if ($current['end'] > $next['start']) {
                    $validator->errors()->add(
                        "breaks.{$next['index']}.start",
                        '休憩時間が重複しています'
                    );
                }
            }
        });
    }
}
