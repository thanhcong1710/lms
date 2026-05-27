<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LmsTestController extends Controller
{
    public function index(Request $request)
    {
        $type = $request->query('type'); // UCREA or IG.BH
        $search = $request->query('search');

        $query = DB::table('lms_tests');

        if ($type) {
            $query->where('test_type', $type);
        }

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('level_cd', 'like', "%{$search}%")
                  ->orWhere('test_cd', 'like', "%{$search}%");
            });
        }

        $tests = $query->orderBy('id', 'desc')->get();

        return response()->json([
            'status' => 'success',
            'data' => $tests
        ]);
    }
}
