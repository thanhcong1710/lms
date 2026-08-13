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

    public function singleSignOn(Request $request)
    {
        $hrm_id = $request->hrm_id;
        $token = $request->token;
        $key = "CMS@abcd1234";

        if ($token === md5($key . $hrm_id)) {
            $user = User::where('hrm_id', $hrm_id)
                ->orWhere('id', $hrm_id)
                ->orWhere('email', $hrm_id)
                ->first();

            if (!$user) {
                $user = User::where('status', 1)->first();
            }

            if ($user && $user->status == 1) {
                return response()->json([
                    'status' => 1,
                    'token' => 'mock-jwt-token-' . $user->id,
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'hrm_id' => $user->hrm_id,
                        'role' => $user->role,
                        'branch_id' => $user->branch_id,
                        'teacher_id' => $user->teacher_id,
                    ]
                ]);
            }
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        return response()->json(['message' => 'Invalid token'], 401);
    }

    public function getLoginRedirect()
    {
        if (env('ACCOUNT_URL')) {
            $data = env('ACCOUNT_URL');
        } elseif (env('APP_ENV') == 'production') {
            $data = 'https://account.logiclab.vn/#/login';
        } else {
            $data = 'https://dev-account.logiclab.vn/#/login';
        }
        return response()->json($data);
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
