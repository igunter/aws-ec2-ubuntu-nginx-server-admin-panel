<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $ftpAccount = Auth::guard('ftp')->user();
        $account    = $ftpAccount->account;
        $account->load('ftpAccounts');

        return view('portal.dashboard', compact('account', 'ftpAccount'));
    }
}
