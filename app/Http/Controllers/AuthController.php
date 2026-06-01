<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->username)->orWhere('name', $request->username)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        if ($user->status != 1) {
            return response()->json(['message' => 'Account is disabled'], 403);
        }

        return response()->json([
            'token' => 'mock-jwt-token-' . $user->id,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'branch_id' => $user->branch_id,
                'teacher_id' => $user->teacher_id,
            ]
        ]);
    }

    public function me(Request $request)
    {
        $user = $this->resolveUser($request);
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'branch_id' => $user->branch_id,
                'teacher_id' => $user->teacher_id,
            ]
        ]);
    }

    /**
     * Resolve user from mock token (token format: mock-jwt-token-{userId})
     */
    public static function resolveUser(Request $request): ?User
    {
        $token = $request->bearerToken();
        if (!$token) return null;

        if (preg_match('/^mock-jwt-token-(\d+)$/', $token, $matches)) {
            return User::find($matches[1]);
        }

        return null;
    }
}
