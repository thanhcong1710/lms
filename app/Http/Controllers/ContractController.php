<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Contract;

class ContractController extends Controller
{
    public function index()
    {
        return response()->json(Contract::with(['student', 'lmsClass', 'branch'])->get());
    }

    public function store(Request $request)
    {
        $contract = Contract::create($request->all());
        return response()->json($contract, 201);
    }

    public function show($id)
    {
        return response()->json(Contract::with(['student', 'lmsClass', 'branch'])->findOrFail($id));
    }

    public function update(Request $request, $id)
    {
        $contract = Contract::findOrFail($id);
        $contract->update($request->all());
        return response()->json($contract);
    }

    public function destroy($id)
    {
        Contract::destroy($id);
        return response()->json(['message' => 'Deleted successfully']);
    }
}
