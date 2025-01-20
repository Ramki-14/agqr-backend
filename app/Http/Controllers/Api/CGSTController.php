<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CGST;
use Illuminate\Http\Request;

class CGSTController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'rate' => 'required|numeric|min:0',
        ]);

        $cgst = CGST::create(['rate' => $request->rate]);

        return response()->json(['success' => true, 'data' => $cgst], 201);
    }

    public function getAll()
    {
        $cgsts = CGST::all();

        return response()->json(['success' => true, 'data' => $cgsts], 200);
    }

}
