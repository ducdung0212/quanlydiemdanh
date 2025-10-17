<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LecturerRequest extends FormRequest
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
        $lecturerCode=$this->route('lecturer');
        if ($this->isMethod('post')){
            return[
                'lecturer_code' => ['required','min:10','max:11', Rule::unique('lecturers')],
                'user_id' =>['nullable','exists:users,id'],
                'full_name' => ['required','string','max:100'],
                'email' => ['nullable','email',Rule::unique('lecturers')],
                'phone' => ['nullable','string','max:15',Rule::unique('lecturers')],
                'faculty_code' => ['required','exists:faculties,faculty_code'],
            ];
        }
        if( $this->isMethod('put') || $this->isMethod('patch')){
            return[
                'lecturer_code' => ['sometimes','required','min:10','max:11', Rule::unique('lecturers')->ignore($lecturerCode,'lecturer_code')],
                'user_id' =>['sometimes','nullable','exists:users,id'],
                'full_name' => ['sometimes','required','string','max:100'],
                'email' => ['sometimes','nullable','email',Rule::unique('lecturers')->ignore($lecturerCode,'lecturer_code')],
                'phone' => ['sometimes','nullable','string','max:15',Rule::unique('lecturers')->ignore($lecturerCode,'lecturer_code')],
                'faculty_code' => ['sometimes','required','exists:faculties,faculty_code'],
            ];
        }
        return [];
    }
    public function messages(): array{
        return[
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
            'lecturer_code' => 'Mã giảng viên',
            'user_id' => 'Người dùng',
            'full_name' => 'Họ và tên',
            'email' => 'Email',
            'phone' => 'Số điện thoại',
            'faculty_code' => 'Khoa',
        ];
    }
}
