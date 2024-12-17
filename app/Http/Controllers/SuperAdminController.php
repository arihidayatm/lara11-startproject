<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SuperAdminController extends Controller
{
    public function SuperAdminDashboard(Request $request)
    {
        return view ('sadmin.index');
    }

    public function SuperAdminLogout(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/sadmin/login');
    }

    public function SuperAdminLogin(Request $request)
    {
        return view ('sadmin.sadmin-login');
    }
}
