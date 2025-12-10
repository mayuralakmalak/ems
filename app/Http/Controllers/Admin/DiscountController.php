<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Discount;
use Illuminate\Http\Request;

class DiscountController extends Controller
{
    public function index()
    {
        $discounts = Discount::latest()->paginate(20);
        return view('admin.discounts.index', compact('discounts'));
    }

    public function create()
    {
        return view('admin.discounts.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'code' => 'required|string|max:255|unique:discounts,code',
            'type' => 'required|in:fixed,percentage',
            'amount' => 'required|numeric|min:0',
            'status' => 'required|in:active,inactive',
        ]);

        Discount::create($validated);
        return redirect()->route('admin.discounts.index')->with('success', 'Discount created successfully.');
    }

    public function show($id)
    {
        $discount = Discount::findOrFail($id);
        return view('admin.discounts.show', compact('discount'));
    }

    public function edit($id)
    {
        $discount = Discount::findOrFail($id);
        return view('admin.discounts.edit', compact('discount'));
    }

    public function update(Request $request, $id)
    {
        $discount = Discount::findOrFail($id);
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'code' => 'required|string|max:255|unique:discounts,code,' . $id,
            'type' => 'required|in:fixed,percentage',
            'amount' => 'required|numeric|min:0',
            'status' => 'required|in:active,inactive',
        ]);

        $discount->update($validated);
        return redirect()->route('admin.discounts.index')->with('success', 'Discount updated successfully.');
    }

    public function destroy($id)
    {
        $discount = Discount::findOrFail($id);
        $discount->delete();
        return redirect()->route('admin.discounts.index')->with('success', 'Discount deleted successfully.');
    }
}
