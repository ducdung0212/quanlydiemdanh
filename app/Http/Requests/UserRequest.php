<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserRequest extends FormRequest
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
        $userId = $this->route('user');

        if ($this->isMethod('post')) {
            return [
                'name' => ['required', 'min:4'],
                'email' => ['required', 'email', Rule::unique('users', 'email')],
                'password' => ['required', 'min:6'],
                'role' => ['required', Rule::in(['admin', 'lecturer'])],
            ];
        }

        if ($this->isMethod('put') || $this->isMethod('patch')) {
            return [
                'name' => ['sometimes', 'required', 'min:4'],
                'email' => ['sometimes', 'required', 'email', Rule::unique('users', 'email')->ignore($userId)],
                'password' => ['sometimes', 'nullable', 'min:6'],
                'role' => ['sometimes', 'required', Rule::in(['admin', 'lecturer'])],
            ];
        }

        return [];
    }
    public function messages(){
        return[
            'required' => ':attribute bắt buộc phải nhập',
            'min' => ':attribute phải có độ dài ít nhất :min ký tự',
            'email' => ':attribute phải đúng định dạng email',
            'unique' => ':attribute đã tồn tại trong hệ thống',
        ];
    }
    public function attributes(){
        return[
            'name'=>'Tên người dùng',
            'email'=>'Email',
            'password'=>'Mật khẩu',
            'role' => 'Chức vụ',
        ];
    }
}
