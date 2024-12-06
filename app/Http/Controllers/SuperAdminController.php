<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SuperAdminController extends Controller
{
    public function SuperAdminController(Request $request)
    {
        return view ('sadmin.dashboard');
    }
}
