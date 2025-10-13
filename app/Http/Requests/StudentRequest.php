<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StudentRequest extends FormRequest
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
        $studentCode = $this->route('student');

        if ($this->isMethod('post')) {
            return [
                'student_code' => ['required', 'min:10', 'max:11', Rule::unique('students', 'student_code')],
                'class_code' => ['required', 'exists:classes,class_code'],
                'full_name' => ['required', 'string', 'max:100'],
                'email' => ['nullable', 'email', Rule::unique('students', 'email')],
                'phone' => ['nullable', 'string', 'max:15', Rule::unique('students', 'phone')],
            ];
        }

        if ($this->isMethod('put') || $this->isMethod('patch')) {
            return [
                'student_code' => ['sometimes', 'required', 'min:10', 'max:11', Rule::unique('students', 'student_code')->ignore($studentCode, 'student_code')],
                'class_code' => ['sometimes', 'required', 'exists:classes,class_code'],
                'full_name' => ['sometimes', 'required', 'string', 'max:100'],
                'email' => ['sometimes', 'nullable', 'email', Rule::unique('students', 'email')->ignore($studentCode, 'student_code')],
                'phone' => ['sometimes', 'nullable', 'string', 'max:15', Rule::unique('students', 'phone')->ignore($studentCode, 'student_code')],
            ];
        }

        return [];
    }

    public function messages(): array
    {
        return [
            'required' => ':attribute bắt buộc phải nhập.',
            'min' => ':attribute phải có độ dài ít nhất :min ký tự.',
            'unique' => ':attribute đã tồn tại trong hệ thống.',
            'exists' => ':attribute không tồn tại trong hệ thống.',
            'string' => ':attribute phải là chuỗi ký tự.',
            'max' => ':attribute không được vượt quá :max ký tự.',
            'email' => ':attribute phải đúng định dạng email.',
        ];
    }
    public function attributes(): array
    {
        return [
            'student_code' => 'Mã sinh viên',
            'class_code' => 'Mã lớp',
            'full_name' => 'Họ và tên',
            'email' => 'Email',
            'phone' => 'Số điện thoại',
        ];
    }
}
