<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\IGST;
use Illuminate\Http\Request;

class IGSTController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'rate' => 'required|numeric|min:0',
        ]);

        $igst = IGST::create(['rate' => $request->rate]);

        return response()->json(['success' => true, 'data' => $igst], 201);
    }

    public function getAll()
    {
        $igsts = IGST::all();

        return response()->json(['success' => true, 'data' => $igsts], 200);
    }

}
