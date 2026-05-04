<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\StockAdjustment;
use App\Models\Product;

class StockAdjustmentController extends Controller
{
    public function index(Request $request)
    {
        $query = StockAdjustment::with('product');

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('date', [$request->start_date, $request->end_date]);
        }

        $adjustments = $query->orderBy('date', 'desc')->orderBy('id', 'desc')->paginate(20);
        return view('stock_adjustments.index', compact('adjustments'));
    }

    public function create()
    {
        $products = Product::where('is_active', true)->orderBy('name')->get();
        return view('stock_adjustments.form', compact('products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'product_id' => 'required|exists:products,id',
            'type' => 'required|in:increase,decrease',
            'qty' => 'required|numeric|min:0.01',
            'unit_cost' => 'nullable|numeric|min:0',
            'note' => 'nullable|string|max:255',
        ]);

        try {
            DB::beginTransaction();

            $product = Product::lockForUpdate()->find($request->product_id);
            $qty = (float) $request->qty;
            $unit_cost = (float) ($request->unit_cost ?? $product->purchase_price);
            $amount = $qty * $unit_cost;

            if ($request->type === 'decrease' && $product->stock_qty < $qty) {
                throw new \Exception("Cannot decrease stock by $qty. Only {$product->stock_qty} available.");
            }

            StockAdjustment::create([
                'date' => $request->date,
                'product_id' => $request->product_id,
                'type' => $request->type,
                'qty' => $qty,
                'unit_cost' => $unit_cost,
                'amount' => $amount,
                'note' => $request->note,
            ]);

            if ($request->type === 'increase') {
                $product->stock_qty += $qty;
            } else {
                $product->stock_qty -= $qty;
            }
            $product->save();

            DB::commit();
            return redirect()->route('stock_adjustments.index')->with('success', 'Stock adjustment recorded successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function edit(StockAdjustment $stockAdjustment)
    {
        $products = Product::where('is_active', true)->orderBy('name')->get();
        return view('stock_adjustments.form', compact('stockAdjustment', 'products'));
    }

    public function update(Request $request, StockAdjustment $stockAdjustment)
    {
        $request->validate([
            'date' => 'required|date',
            'product_id' => 'required|exists:products,id',
            'type' => 'required|in:increase,decrease',
            'qty' => 'required|numeric|min:0.01',
            'unit_cost' => 'nullable|numeric|min:0',
            'note' => 'nullable|string|max:255',
        ]);

        try {
            DB::beginTransaction();

            // 1. Reverse old adjustment
            $oldProduct = Product::lockForUpdate()->find($stockAdjustment->product_id);
            if ($stockAdjustment->type === 'increase') {
                $oldProduct->stock_qty -= $stockAdjustment->qty;
            } else {
                $oldProduct->stock_qty += $stockAdjustment->qty;
            }
            $oldProduct->save();

            // 2. Apply new adjustment
            $product = Product::lockForUpdate()->find($request->product_id);
            $qty = (float) $request->qty;
            $unit_cost = (float) ($request->unit_cost ?? $product->purchase_price);
            $amount = $qty * $unit_cost;

            if ($request->type === 'decrease' && $product->stock_qty < $qty) {
                throw new \Exception("Cannot decrease stock by $qty. Only {$product->stock_qty} available after reversing previous adjustment.");
            }

            $stockAdjustment->update([
                'date' => $request->date,
                'product_id' => $request->product_id,
                'type' => $request->type,
                'qty' => $qty,
                'unit_cost' => $unit_cost,
                'amount' => $amount,
                'note' => $request->note,
            ]);

            if ($request->type === 'increase') {
                $product->stock_qty += $qty;
            } else {
                $product->stock_qty -= $qty;
            }
            $product->save();

            DB::commit();
            return redirect()->route('stock_adjustments.index')->with('success', 'Stock adjustment updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function destroy(StockAdjustment $stockAdjustment)
    {
        try {
            DB::beginTransaction();

            $product = Product::lockForUpdate()->find($stockAdjustment->product_id);
            
            // Reverse the adjustment effect
            if ($stockAdjustment->type === 'increase') {
                $product->stock_qty -= $stockAdjustment->qty;
            } else {
                $product->stock_qty += $stockAdjustment->qty;
            }
            $product->save();

            $stockAdjustment->delete();

            DB::commit();
            return redirect()->route('stock_adjustments.index')->with('success', 'Stock adjustment deleted and stock reversed.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }
}
