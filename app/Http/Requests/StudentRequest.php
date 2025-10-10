<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

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
        $student_code=$this->route()->student;
        $emailRule ='required|email|unique:students,email';
        $phoneRule = 'nullable|string|max:15|unique:students,phone';
        $student_codeRule = 'required|min:10|max:11|unique:students,student_code';
        if($student_code){
            $emailRule .=",{$student_code},student_code";
            $phoneRule .= ",{$student_code},student_code";
            $student_codeRule .= ",{$student_code},student_code";
            $full_name=$this->full_name;
            $class_code=$this->class_code;
            $email=$this->email;
            $phone=$this->phone;
            $rules = [];
            if ($student_code) {
                $rules['student_code'] = $student_codeRule;
            }
            if ($class_code) {
                $rules['class_code'] = ['required','exists:classes,class_code'];
            }
            if ($full_name) {
                $rules['full_name'] = ['required','string','max:100'];
            }
            if ($email) {
                $rules['email'] = $emailRule;
            }
            if ($phone) {
                $rules['phone'] = $phoneRule;
            }
            return $rules;
        }
        return [
            'student_code' => $student_codeRule,
            'class_code' => ['required','exists:classes,class_code'],
            'full_name' => ['required','string','max:100'],
            'email'=>$emailRule,
            'phone'=>$phoneRule,
        ];
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
