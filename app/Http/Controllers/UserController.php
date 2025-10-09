<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Requests\UserRequest;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $q=request()->query('q');
        $limit=request()->query('limit',10);
        $users=User::latest();
        if($q){
            $users->where(function($query) use ($q){
                $query->where('name','like',"%$q%");
                $query->orWhere('email','like',"%$q%");
            });
        }
        return response()->json([
            'success' => true,
            'data' => $users->paginate($limit),
            'message'=>'List Users'
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
        $user =new User;
        $user->fill($request->all());
        $user->password=bcrypt($request->password);
        $user->save();
        return response()->json([
            'success' => true,
            'data' => $user,
            'message'=>'Create User Successfully'
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {

        $user=User::find($id);
        if(!$user){
        return response()->json([
            'success' => false,
            'message'=>'User Not Found'
        ],
        404);
        }
         return response()->json([
            'success' => true,
            'data' => $user,
            'message'=>'Detail User'
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UserRequest $request, string $id)
    {
        $user=User::find($id);
        if(!$user){
        return response()->json([
            'success' => false,
            'message'=>'User Not Found'
        ],
        404);
        }
        $user->fill($request->all());
        if($request->password){
            $user->password=bcrypt($request->password);
        }
        $user->save();
         return response()->json([
            'success' => true,
            'data' => $user,
            'message'=>'Update User Successfully'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user=User::find($id);
        if(!$user){
        return response()->json([
            'success' => false,
            'message'=>'User Not Found'
        ],
        404);
        }
        $user->delete();
         return response()->json([
            'success' => true,
            'message'=>'Delete User Successfully'
        ]);
    }
}
