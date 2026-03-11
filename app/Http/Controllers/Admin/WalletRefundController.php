<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletRefundRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WalletRefundController extends Controller
{
    public function index()
    {
        abort_unless(auth()->user()->can('Wallet Management'), 403);

        $status = request('status', 'pending');

        $requests = WalletRefundRequest::with(['user', 'wallet'])
            ->when($status, function ($query, $status) {
                if (in_array($status, ['pending', 'approved', 'rejected'])) {
                    $query->where('status', $status);
                }
            })
            ->latest()
            ->paginate(20);

        return view('admin.wallet-refunds.index', compact('requests', 'status'));
    }

    public function show($id)
    {
        abort_unless(auth()->user()->can('Wallet Management'), 403);

        $request = WalletRefundRequest::with(['user', 'wallet'])->findOrFail($id);

        return view('admin.wallet-refunds.show', compact('request'));
    }

    public function approve(Request $request, $id)
    {
        abort_unless(auth()->user()->can('Wallet Management'), 403);

        $refundRequest = WalletRefundRequest::with(['user', 'wallet'])->findOrFail($id);

        if ($refundRequest->status !== 'pending') {
            return back()->with('error', 'This refund request has already been processed.');
        }

        $adminNote = $request->input('admin_note');

        DB::beginTransaction();
        try {
            $user = $refundRequest->user;

            if ($user->wallet_balance < $refundRequest->amount) {
                return back()->with('error', 'User does not have enough wallet balance to process this refund.');
            }

            // Create wallet debit (User wallet balance is a computed accessor)
            Wallet::create([
                'user_id' => $user->id,
                'balance' => $user->wallet_balance - $refundRequest->amount,
                'transaction_type' => 'debit',
                'amount' => $refundRequest->amount,
                'reference_type' => 'wallet_special_discount_refund',
                'reference_id' => $refundRequest->id,
                'description' => 'Refund of special discount credited to wallet',
            ]);

            $refundRequest->status = 'approved';
            $refundRequest->admin_note = $adminNote;
            $refundRequest->processed_by = auth()->id();
            $refundRequest->processed_at = now();
            $refundRequest->save();

            DB::commit();

            return redirect()->route('admin.wallet-refunds.index')->with('success', 'Refund request approved and wallet updated.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to approve refund request: ' . $e->getMessage());
        }
    }

    public function reject(Request $request, $id)
    {
        abort_unless(auth()->user()->can('Wallet Management'), 403);

        $refundRequest = WalletRefundRequest::findOrFail($id);

        if ($refundRequest->status !== 'pending') {
            return back()->with('error', 'This refund request has already been processed.');
        }

        $request->validate([
            'admin_note' => 'nullable|string|max:1000',
        ]);

        $refundRequest->status = 'rejected';
        $refundRequest->admin_note = $request->input('admin_note');
        $refundRequest->processed_by = auth()->id();
        $refundRequest->processed_at = now();
        $refundRequest->save();

        return redirect()->route('admin.wallet-refunds.index')->with('success', 'Refund request rejected.');
    }
}

