<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $account = Auth::user()->account;
        $account->load('ftpAccounts', 'mysqlDatabases.mysqlUsers');

        return view('portal.dashboard', compact('account'));
    }
}
