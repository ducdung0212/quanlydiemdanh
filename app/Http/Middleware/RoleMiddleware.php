<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     * Usage: ->middleware('role:admin') or ->middleware('role:admin,lecturer')
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = $request->user();
        if (!$user) {
            return redirect()->route('login');
        }

        // If no roles provided, allow
        if (empty($roles)) {
            return $next($request);
        }

        $userRole = $user->role ?? null;
        if (!in_array($userRole, $roles)) {
            // If AJAX or expects JSON, return 403
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['message' => 'Forbidden'], 403);
            }

            // Otherwise redirect to attendance page (lecturers' only area) with a flash message
            return redirect()->to('/attendance')->with('error', 'Bạn không có quyền truy cập trang này.');
        }

        return $next($request);
    }
}
