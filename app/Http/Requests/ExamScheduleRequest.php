<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ExamScheduleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $examScheduleId = $this->route('exam_schedule') ?? $this->route('id');

        return [
            'subject_code' => 'required|string|exists:subjects,subject_code',
            'exam_date' => 'required|date',
            'exam_time' => 'required',
            'duration' => 'required|integer|min:1|max:300',
            'room' => 'required|string|max:50',
            'note' => 'nullable|string|max:500',
        ];
    }

    /**
     * Custom validation để kiểm tra trùng lặp ca thi
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $examScheduleId = $this->route('exam_schedule') ?? $this->route('id');

            // Kiểm tra trùng phòng thi + ngày thi + giờ thi 
            $duplicateRoom = \App\Models\ExamSchedule::where('room', $this->room)
                ->where('exam_date', $this->exam_date)
                ->where('exam_time', $this->exam_time)
                ->when($examScheduleId, function ($query) use ($examScheduleId) {
                    $query->where('id', '!=', $examScheduleId);
                })
                ->exists();

            if ($duplicateRoom) {
                $validator->errors()->add(
                    'room',
                    'Phòng thi này đã được sử dụng cho ca thi khác vào cùng ngày và giờ này.'
                );
            }
        });
    }

    public function messages(): array
    {
        return [
            'subject_code.required' => 'Mã môn học là bắt buộc.',
            'subject_code.exists' => 'Mã môn học không tồn tại.',
            'exam_date.required' => 'Ngày thi là bắt buộc.',
            'exam_date.date' => 'Ngày thi không hợp lệ.',
            'exam_time.required' => 'Giờ thi là bắt buộc.',
            'exam_time.date_format' => 'Giờ thi phải có định dạng HH:mm:ss.',
            'duration.required' => 'Thời lượng là bắt buộc.',
            'duration.integer' => 'Thời lượng phải là số nguyên.',
            'duration.min' => 'Thời lượng tối thiểu là 1 phút.',
            'duration.max' => 'Thời lượng tối đa là 300 phút.',
            'room.required' => 'Phòng thi là bắt buộc.',
            'room.max' => 'Phòng thi không được vượt quá 50 ký tự.',
            'note.max' => 'Ghi chú không được vượt quá 500 ký tự.',
        ];
    }

   
    public function attributes(): array
    {
        return [
            'subject_code' => 'Mã môn học',
            'exam_date' => 'Ngày thi',
            'exam_time' => 'Giờ thi',
            'duration' => 'Thời lượng',
            'room' => 'Phòng thi',
            'note' => 'Ghi chú',
        ];
    }
}