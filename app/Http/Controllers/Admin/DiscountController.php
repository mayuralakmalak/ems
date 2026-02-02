<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Discount;
use App\Models\Exhibition;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class DiscountController extends Controller
{
    public function index()
    {
        $discounts = Discount::with('exhibition')->latest()->paginate(20);
        return view('admin.discounts.index', compact('discounts'));
    }

    public function create()
    {
        $exhibitions = Exhibition::orderBy('name')->get();
        return view('admin.discounts.create', compact('exhibitions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'code' => 'required|string|max:255|unique:discounts,code',
            'type' => 'required|in:fixed,percentage',
            'amount' => 'required|numeric|min:0',
            'status' => 'required|in:active,inactive',
            'email' => 'required|email|max:255',
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
        $exhibitions = Exhibition::orderBy('name')->get();
        return view('admin.discounts.edit', compact('discount', 'exhibitions'));
    }

    public function update(Request $request, $id)
    {
        $discount = Discount::findOrFail($id);
        $validated = $request->validate([
            'exhibition_id' => 'nullable|exists:exhibitions,id',
            'title' => 'required|string|max:255',
            'code' => 'required|string|max:255|unique:discounts,code,' . $id,
            'type' => 'required|in:fixed,percentage',
            'amount' => 'required|numeric|min:0',
            'status' => 'required|in:active,inactive',
            'email' => 'required|email|max:255',
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

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'discount_ids' => 'required|string',
        ]);

        $ids = json_decode($request->discount_ids);
        if (!is_array($ids) || empty($ids)) {
            return back()->with('error', 'No discounts selected for deletion.');
        }

        Discount::whereIn('id', $ids)->delete();
        return back()->with('success', count($ids) . ' discount(s) deleted successfully.');
    }

    public function import()
    {
        $exhibitions = Exhibition::orderBy('name')->get();
        return view('admin.discounts.import', compact('exhibitions'));
    }

    public function processImport(Request $request)
    {
        $validated = $request->validate([
            'exhibition_id' => 'nullable|exists:exhibitions,id',
            'csv_file' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        $file = $request->file('csv_file');
        $exhibitionId = $validated['exhibition_id'] ?? null;
        
        $csvData = array_map('str_getcsv', file($file->getRealPath()));
        $headers = array_shift($csvData);
        
        // Normalize headers (trim and lowercase)
        $headers = array_map(function($header) {
            return strtolower(trim($header));
        }, $headers);
        
        // Expected headers: title, code, type, amount, status, email
        $expectedHeaders = ['title', 'code', 'type', 'amount', 'status', 'email'];
        $headerMap = [];
        
        foreach ($expectedHeaders as $expected) {
            $index = array_search($expected, $headers);
            if ($index === false) {
                return back()->withInput()->with('error', "CSV file must contain '{$expected}' column.");
            }
            $headerMap[$expected] = $index;
        }
        
        $errors = [];
        $successCount = 0;
        $rowNumber = 1;
        $importedCodes = []; // Track codes in current import to prevent duplicates
        
        DB::beginTransaction();
        try {
            foreach ($csvData as $row) {
                $rowNumber++;
                
                if (count($row) < count($expectedHeaders)) {
                    $errors[] = "Row {$rowNumber}: Insufficient columns";
                    continue;
                }
                
                $title = trim($row[$headerMap['title']] ?? '');
                $code = trim($row[$headerMap['code']] ?? '');
                $type = strtolower(trim($row[$headerMap['type']] ?? ''));
                $amount = trim($row[$headerMap['amount']] ?? '');
                $status = strtolower(trim($row[$headerMap['status']] ?? 'active'));
                $email = trim($row[$headerMap['email']] ?? '');
                
                // Check for duplicate codes in CSV
                if (in_array($code, $importedCodes)) {
                    $errors[] = "Row {$rowNumber}: Duplicate code '{$code}' found in CSV file";
                    continue;
                }
                
                // Check if code already exists in database
                if (Discount::where('code', $code)->exists()) {
                    $errors[] = "Row {$rowNumber}: Code '{$code}' already exists in database";
                    continue;
                }
                
                // Validate row data
                $validator = Validator::make([
                    'title' => $title,
                    'code' => $code,
                    'type' => $type,
                    'amount' => $amount,
                    'status' => $status,
                    'email' => $email,
                ], [
                    'title' => 'required|string|max:255',
                    'code' => 'required|string|max:255',
                    'type' => 'required|in:fixed,percentage',
                    'amount' => 'required|numeric|min:0',
                    'status' => 'required|in:active,inactive',
                    'email' => 'required|email|max:255',
                ]);
                
                if ($validator->fails()) {
                    $errors[] = "Row {$rowNumber}: " . implode(', ', $validator->errors()->all());
                    continue;
                }
                
                // Create discount
                Discount::create([
                    'exhibition_id' => $exhibitionId,
                    'title' => $title,
                    'code' => $code,
                    'type' => $type,
                    'amount' => $amount,
                    'status' => $status,
                    'email' => $email,
                ]);
                
                $importedCodes[] = $code; // Track this code
                $successCount++;
            }
            
            if (!empty($errors)) {
                DB::rollBack();
                return back()->withInput()->with('error', 'Import completed with errors. ' . implode(' | ', array_slice($errors, 0, 10)) . (count($errors) > 10 ? ' ... and more' : ''));
            }
            
            DB::commit();
            
            return redirect()->route('admin.discounts.index')
                ->with('success', "Successfully imported {$successCount} discount(s).");
                
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Import failed: ' . $e->getMessage());
        }
    }
}
