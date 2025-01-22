<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class CommandController extends Controller
{
    public function runCertificateStatus()
    {
        Artisan::call('update:certificate-status');
        return response()->json(['message' => 'Certificate status updated successfully!']);
    }
}
