<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Http\Requests\UserRequest;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $q = request()->query('q');
        $limit = (int) request()->query('limit', 10);
        $limit = max(1, min(100, $limit));

        $users = User::select(['id', 'name', 'email', 'role', 'created_at'])
            ->latest();

        if ($q) {
            $users->where(function ($query) use ($q) {
                $query->where('name', 'like', "%$q%");
                $query->orWhere('email', 'like', "%$q%");
            });
        }
        return response()->json([
            'success' => true,
            'data' => $users->paginate($limit),
            'message' => 'List Users'
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(UserRequest $request)
    {
        $validated = $request->validated();

        $user = new User;
        $user->fill(collect($validated)->only(['name', 'email', 'role'])->toArray());
        $user->password = Hash::make($validated['password']);
        $user->save();
        return response()->json([
            'success' => true,
            'data' => $user,
            'message' => 'Create User Successfully'
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {

        $user = User::find($id);
        if (!$user) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'User Not Found'
                ],
                404
            );
        }
        return response()->json([
            'success' => true,
            'data' => $user,
            'message' => 'Detail User'
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id) {}
    public function update(UserRequest $request, string $id)
    {
        $validated = $request->validated();

        $user = User::find($id);
        if (!$user) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'User not found'
                ],
                404
            );
        }

        if (array_key_exists('name', $validated)) {
            $user->name = $validated['name'];
        }
        if (array_key_exists('email', $validated)) {
            $user->email = $validated['email'];
        }
        if (array_key_exists('role', $validated)) {
            $user->role = $validated['role'];
        }
        if (array_key_exists('password', $validated) && $validated['password']) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return response()->json(
            [
                'success' => true,
                'data' => $user,
                'message' => 'User updated successfully'
            ]
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'User Not Found'
                ],
                404
            );
        }
        $user->delete();
        return response()->json([
            'success' => true,
            'message' => 'Delete User Successfully'
        ]);
    }
}
