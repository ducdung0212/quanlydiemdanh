<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\PersonalAccessToken;

class AuthTokenController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'device_name' => ['nullable', 'string', 'max:100'],
        ]);

        $user = \App\Models\User::query()->where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Email hoặc mật khẩu không đúng.'],
            ]);
        }

        $tokenName = $credentials['device_name'] ?? ('react-client-' . now()->format('YmdHis'));
        $token = $user->createToken($tokenName);

        return response()->json([
            'success' => true,
            'token_type' => 'Bearer',
            'access_token' => $token->plainTextToken,
            'user' => $this->transformUser($user),
            'message' => 'Đăng nhập thành công.',
        ]);
    }

    public function me(Request $request)
    {
        return response()->json([
            'success' => true,
            'user' => $this->transformUser($request->user()),
        ]);
    }
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'], 
            'password' => ['required', 'string', 'min:8', 'confirmed'], 
        ], [
            'current_password.current_password' => 'Mật khẩu hiện tại không chính xác.',
            'password.confirmed' => 'Mật khẩu xác nhận không khớp.',
            'password.min' => 'Mật khẩu mới phải có ít nhất 8 ký tự.',
        ]);

        $user = $request->user();
        $user->update([
            'password' => Hash::make($request->password)
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Đổi mật khẩu thành công!'
        ]);
    }


    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json([
            'success' => true,
            'message' => 'Đăng xuất thành công.',
        ]);
    }

    public function logoutAll(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Đã thu hồi toàn bộ phiên đăng nhập.',
        ]);
    }

    public function revokeToken(Request $request, int $tokenId)
    {
        $token = PersonalAccessToken::query()->find($tokenId);

        if (!$token || (int) $token->tokenable_id !== (int) $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Token không tồn tại hoặc không thuộc tài khoản hiện tại.',
            ], 404);
        }

        $token->delete();

        return response()->json([
            'success' => true,
            'message' => 'Thu hồi token thành công.',
        ]);
    }

    protected function transformUser($user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'student_code' => $user->student_code,
        ];
    }
}
