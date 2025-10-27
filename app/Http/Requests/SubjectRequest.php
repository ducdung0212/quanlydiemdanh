<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SubjectRequest extends FormRequest
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
        $subjectCode = $this->route('subject');
        if($this->isMethod('post')){
            return[
                'subject_code'=>['required','min:5','max:10',Rule::unique('subjects','subject_code')],
                'name'=>['required','string','max:100'],
                'credit'=>['nullable','integer','min:1','max:10'],
            ];
        }
        if($this->isMethod('put') || $this->isMethod('patch')){
            return[
                'subject_code'=>['sometimes','required','min:5','max:10',Rule::unique('subjects','subject_code')->ignore($subjectCode,'subject_code')],
                'name'=>['sometimes','required','string','max:100'],
                'credit'=>['sometimes','nullable','integer','min:1','max:10'],
            ];
        }
        return [];
    }
    public function messages(): array
    {
        return [
            'required' => ':attribute bắt buộc phải nhập.',
            'min' => ':attribute phải có độ dài ít nhất :min ký tự.',
            'max' => ':attribute không được vượt quá :max ký tự.',
            'unique' => ':attribute đã tồn tại trong hệ thống.',
            'string' => ':attribute phải là chuỗi ký tự.',
            'integer' => ':attribute phải là số nguyên.',
        ];
    }
    public function attributes(): array{
        return[
            'subject_code'=>'Mã môn học',
            'name'=>'Tên môn học',
            'credit'=>'Số tín chỉ',
        ];
    }
}