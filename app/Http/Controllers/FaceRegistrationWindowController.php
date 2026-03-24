<?php

namespace App\Http\Controllers;

use App\Models\FaceRegistrationWindow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FaceRegistrationWindowController extends Controller
{
    public function current()
    {
        $window = FaceRegistrationWindow::activeNow()->first();

        return response()->json([
            'success' => true,
            'data' => $window,
            'is_open' => (bool) $window,
            'message' => $window ? 'Đang có đợt đổi ảnh hoạt động.' : 'Hiện chưa có đợt đổi ảnh hoạt động.',
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'nullable|string|max:120',
            'starts_at' => 'required|date',
            'ends_at' => 'required|date|after:starts_at',
            'note' => 'nullable|string|max:1000',
        ]);

        // Close all currently active windows to avoid overlap.
        FaceRegistrationWindow::query()->where('is_active', true)->update(['is_active' => false]);

        $window = FaceRegistrationWindow::create([
            'name' => $validated['name'] ?? null,
            'starts_at' => $validated['starts_at'],
            'ends_at' => $validated['ends_at'],
            'is_active' => true,
            'opened_by_user_id' => Auth::id(),
            'note' => $validated['note'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'data' => $window,
            'message' => 'Đã mở thời gian cho phép sinh viên đổi ảnh cá nhân.',
        ], 201);
    }

    public function closeCurrent()
    {
        $updated = FaceRegistrationWindow::query()
            ->where('is_active', true)
            ->update(['is_active' => false]);

        return response()->json([
            'success' => true,
            'closed_count' => $updated,
            'message' => $updated > 0
                ? 'Đã đóng thời gian đổi ảnh cá nhân hiện tại.'
                : 'Không có thời gian đổi ảnh nào đang hoạt động.',
        ]);
    }
}
