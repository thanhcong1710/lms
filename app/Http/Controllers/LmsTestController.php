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

        $limit = $request->query('per_page', 20);
        if (!in_array($limit, [20, 50, 100])) {
            $limit = 20;
        }

        $tests = $query->orderBy('id', 'desc')->paginate($limit);

        return response()->json([
            'status' => 'success',
            'data' => $tests->items(),
            'pagination' => [
                'total' => $tests->total(),
                'per_page' => $tests->perPage(),
                'current_page' => $tests->currentPage(),
                'last_page' => $tests->lastPage(),
                'from' => $tests->firstItem(),
                'to' => $tests->lastItem()
            ]
        ]);
    }
}
