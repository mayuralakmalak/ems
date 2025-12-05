<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Wallet;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    public function index()
    {
        $balance = auth()->user()->wallet_balance;
        $transactions = Wallet::where('user_id', auth()->id())
            ->latest()
            ->paginate(20);

        return view('frontend.wallet.index', compact('balance', 'transactions'));
    }
}
