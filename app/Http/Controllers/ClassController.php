<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LmsClass;

class ClassController extends Controller
{
    public function index(Request $request)
    {
        $limit = $request->query('per_page', 20);
        if (!in_array($limit, [20, 50, 100])) {
            $limit = 20;
        }

        $query = LmsClass::query();

        // Role-based filtering
        $user = AuthController::resolveUser($request);
        if ($user) {
            $user->scopeClasses($query);
        }

        if ($clsTypeGroup = $request->query('cls_type_group')) {
            if ($clsTypeGroup === 'ucrea') {
                $query->where(function ($q) {
                    $q->whereIn('cls_type', ['CT001', 'CT003'])
                      ->orWhere('cls_name', 'LIKE', '%.U.%');
                });
            } elseif ($clsTypeGroup === 'igaten') {
                $query->where(function ($q) {
                    $q->where('cls_name', 'LIKE', '%.IG.%')
                      ->orWhere('cls_type', 'CT003');
                });
            } elseif (in_array($clsTypeGroup, ['black_hold', 'bh', 'bright_heading'])) {
                $query->where(function ($q) {
                    $q->whereIn('cls_type', ['CT004', 'CT002'])
                      ->orWhere('cls_name', 'LIKE', '%.BH.%');
                });
            }
        }

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('cls_name', 'LIKE', "%{$search}%")
                  ->orWhere('class_seq', 'LIKE', "%{$search}%");
            });
        }

        return response()->json($query->paginate($limit));
    }

    public function store(Request $request)
    {
        $class = LmsClass::create($request->all());
        return response()->json($class, 201);
    }

    public function show($id)
    {
        return response()->json(LmsClass::findOrFail($id));
    }

    public function update(Request $request, $id)
    {
        $class = LmsClass::findOrFail($id);
        $class->update($request->all());
        return response()->json($class);
    }

    public function destroy($id)
    {
        LmsClass::destroy($id);
        return response()->json(['message' => 'Deleted successfully']);
    }
}
