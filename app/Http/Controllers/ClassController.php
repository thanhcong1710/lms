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
        return response()->json(LmsClass::paginate($limit));
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
