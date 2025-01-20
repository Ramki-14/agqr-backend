<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SGST;
use Illuminate\Http\Request;

class SGSTController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'rate' => 'required|numeric|min:0',
        ]);

        $sgst = SGST::create(['rate' => $request->rate]);

        return response()->json(['success' => true, 'data' => $sgst], 201);
    }

    public function getAll()
    {
        $sgsts = SGST::all();

        return response()->json(['success' => true, 'data' => $sgsts], 200);
    }
}
