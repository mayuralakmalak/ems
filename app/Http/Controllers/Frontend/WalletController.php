<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Wallet;
use App\Models\WalletRefundRequest;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    public function index()
    {
        $balance = auth()->user()->wallet_balance;
        $transactions = Wallet::where('user_id', auth()->id())
            ->latest()
            ->paginate(20);

        $existingRefundRequests = WalletRefundRequest::where('user_id', auth()->id())
            ->get()
            ->groupBy('wallet_id');

        return view('frontend.wallet.index', compact('balance', 'transactions', 'existingRefundRequests'));
    }

    public function showRefundRequest(Wallet $wallet)
    {
        abort_unless($wallet->user_id === auth()->id(), 403);

        // Only allow refund requests for special discount credits
        if ($wallet->transaction_type !== 'credit' || $wallet->reference_type !== 'booking_special_discount') {
            return redirect()->route('wallet.index')->with('error', 'This transaction is not eligible for refund request.');
        }

        $existingRequest = WalletRefundRequest::where('user_id', auth()->id())
            ->where('wallet_id', $wallet->id)
            ->whereIn('status', ['pending', 'approved'])
            ->first();

        if ($existingRequest) {
            return redirect()->route('wallet.index')->with('error', 'Refund has already been requested or processed for this discount.');
        }

        return view('frontend.wallet.request-refund', [
            'wallet' => $wallet,
        ]);
    }

    public function submitRefundRequest(Request $request, Wallet $wallet)
    {
        abort_unless($wallet->user_id === auth()->id(), 403);

        if ($wallet->transaction_type !== 'credit' || $wallet->reference_type !== 'booking_special_discount') {
            return redirect()->route('wallet.index')->with('error', 'This transaction is not eligible for refund request.');
        }

        $request->validate([
            'reason' => 'nullable|string|max:1000',
        ]);

        $existingRequest = WalletRefundRequest::where('user_id', auth()->id())
            ->where('wallet_id', $wallet->id)
            ->whereIn('status', ['pending', 'approved'])
            ->first();

        if ($existingRequest) {
            return redirect()->route('wallet.index')->with('error', 'Refund has already been requested or processed for this discount.');
        }

        WalletRefundRequest::create([
            'user_id' => auth()->id(),
            'wallet_id' => $wallet->id,
            'amount' => $wallet->amount,
            'status' => 'pending',
            'reason' => $request->input('reason'),
        ]);

        return redirect()->route('wallet.index')->with('success', 'Your refund request has been submitted. Admin will review it shortly.');
    }
}
