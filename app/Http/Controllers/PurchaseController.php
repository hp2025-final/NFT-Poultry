<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\StockAdjustment;

class PurchaseController extends Controller
{
    public function index(Request $request)
    {
        $startDate = $request->input('start_date', date('Y-m-d'));
        $endDate = $request->input('end_date', date('Y-m-d'));

        $purchases = Purchase::with(['supplier', 'items'])
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(20);

        return view('purchases.index', compact('purchases'));
    }

    public function create()
    {
        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();
        $products = Product::where('is_active', true)->orderBy('name')->get();
        return view('purchases.form', compact('suppliers', 'products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.qty' => 'required|numeric|min:0.01',
            'items.*.finished_qty' => 'nullable|numeric|min:0',
            'items.*.price' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $total_amount = 0;
            $items_data = [];

            foreach ($request->items as $item) {
                $qty = (float) $item['qty'];
                $price = (float) $item['price'];
                $amount = $qty * $price;
                $total_amount += $amount;

                $items_data[] = [
                    'product_id' => $item['product_id'],
                    'qty' => $qty,
                    'finished_qty' => isset($item['finished_qty']) && $item['finished_qty'] !== '' ? (float) $item['finished_qty'] : $qty,
                    'price' => $price,
                    'amount' => $amount,
                ];
            }

            $purchase = Purchase::create([
                'supplier_id' => $request->supplier_id,
                'date' => $request->date,
                'total_amount' => $total_amount,
            ]);

            foreach ($items_data as $data) {
                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $data['product_id'],
                    'qty' => $data['qty'],
                    'price' => $data['price'],
                    'amount' => $data['amount'],
                ]);

                $product = Product::find($data['product_id']);
                
                // Add full purchase qty to stock initially
                $product->stock_qty += $data['qty'];
                $product->save();

                // Auto-Adjustment if finished_qty differs
                if ($data['finished_qty'] != $data['qty']) {
                    $diff = $data['qty'] - $data['finished_qty'];
                    $type = $diff > 0 ? 'decrease' : 'increase';
                    $adjustment_qty = abs($diff);
                    
                    StockAdjustment::create([
                        'date' => $request->date,
                        'product_id' => $product->id,
                        'type' => $type,
                        'qty' => $adjustment_qty,
                        'unit_cost' => $data['price'], // Approximate cost of variance
                        'amount' => $adjustment_qty * $data['price'],
                        'note' => 'Auto-adjustment from Purchase #' . $purchase->id . ' (Variance)',
                    ]);

                    // Apply adjustment to stock
                    if ($type === 'decrease') {
                        $product->stock_qty -= $adjustment_qty;
                    } else {
                        $product->stock_qty += $adjustment_qty;
                    }
                    $product->save();
                }
            }

            DB::commit();
            return redirect()->route('purchases.index')->with('success', 'Purchase recorded successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error recording purchase: ' . $e->getMessage())->withInput();
        }
    }

    public function edit(Purchase $purchase)
    {
        $purchase->load('items.product');
        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();
        $products = Product::where('is_active', true)->orderBy('name')->get();
        return view('purchases.form', compact('purchase', 'suppliers', 'products'));
    }

    public function update(Request $request, Purchase $purchase)
    {
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.qty' => 'required|numeric|min:0.01',
            'items.*.finished_qty' => 'nullable|numeric|min:0',
            'items.*.price' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            // 1. Reverse old stock changes
            foreach ($purchase->items as $oldItem) {
                $product = Product::lockForUpdate()->find($oldItem->product_id);
                $product->stock_qty -= $oldItem->qty;
                $product->save();
            }

            // Also reverse auto-adjustments if any
            StockAdjustment::where('note', 'like', 'Auto-adjustment from Purchase #' . $purchase->id . '%')->get()->each(function($adj) {
                $product = Product::lockForUpdate()->find($adj->product_id);
                if ($adj->type === 'increase') {
                    $product->stock_qty -= $adj->qty;
                } else {
                    $product->stock_qty += $adj->qty;
                }
                $product->save();
                $adj->delete();
            });

            // 2. Clear old items
            $purchase->items()->delete();

            // 3. Process new items (same as store logic)
            $total_amount = 0;
            foreach ($request->items as $item) {
                $qty = (float) $item['qty'];
                $price = (float) $item['price'];
                $amount = $qty * $price;
                $total_amount += $amount;

                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $item['product_id'],
                    'qty' => $qty,
                    'price' => $price,
                    'amount' => $amount,
                ]);

                $product = Product::lockForUpdate()->find($item['product_id']);
                $product->stock_qty += $qty;
                $product->save();

                $finished_qty = isset($item['finished_qty']) && $item['finished_qty'] !== '' ? (float) $item['finished_qty'] : $qty;
                if ($finished_qty != $qty) {
                    $diff = $qty - $finished_qty;
                    $type = $diff > 0 ? 'decrease' : 'increase';
                    $adjustment_qty = abs($diff);

                    StockAdjustment::create([
                        'date' => $request->date,
                        'product_id' => $product->id,
                        'type' => $type,
                        'qty' => $adjustment_qty,
                        'unit_cost' => $price,
                        'amount' => $adjustment_qty * $price,
                        'note' => 'Auto-adjustment from Purchase #' . $purchase->id . ' (Variance) - Edited',
                    ]);

                    if ($type === 'decrease') {
                        $product->stock_qty -= $adjustment_qty;
                    } else {
                        $product->stock_qty += $adjustment_qty;
                    }
                    $product->save();
                }
            }

            $purchase->update([
                'supplier_id' => $request->supplier_id,
                'date' => $request->date,
                'total_amount' => $total_amount,
            ]);

            DB::commit();
            return redirect()->route('purchases.index')->with('success', 'Purchase updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error updating purchase: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy(Purchase $purchase)
    {
        try {
            DB::beginTransaction();

            // 1. Reverse stock changes
            foreach ($purchase->items as $item) {
                $product = Product::lockForUpdate()->find($item->product_id);
                $product->stock_qty -= $item->qty;
                $product->save();
            }

            // Reverse auto-adjustments
            StockAdjustment::where('note', 'like', 'Auto-adjustment from Purchase #' . $purchase->id . '%')->get()->each(function($adj) {
                $product = Product::lockForUpdate()->find($adj->product_id);
                if ($adj->type === 'increase') {
                    $product->stock_qty -= $adj->qty;
                } else {
                    $product->stock_qty += $adj->qty;
                }
                $product->save();
                $adj->delete();
            });

            // 2. Delete purchase and items (cascading)
            $purchase->items()->delete();
            $purchase->delete();

            DB::commit();
            return redirect()->route('purchases.index')->with('success', 'Purchase deleted and stock adjusted.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error deleting purchase: ' . $e->getMessage());
        }
    }

    public function show(Purchase $purchase)
    {
        $purchase->load(['supplier', 'items.product']);
        return view('purchases.view', compact('purchase'));
    }

    public function bulk()
    {
        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();
        $product = Product::where('is_active', true)->first();
        return view('purchases.bulk', compact('suppliers', 'product'));
    }

    public function storeBulk(Request $request)
    {
        $request->validate([
            'date'                       => 'required|date',
            'product_id'                 => 'required|exists:products,id',
            'suppliers'                  => 'required|array|min:1',
            'suppliers.*.id'             => 'required|exists:suppliers,id',
            'suppliers.*.qty'            => 'nullable|numeric|min:0',
            'suppliers.*.finished_qty'   => 'nullable|numeric|min:0',
            'suppliers.*.rate'           => 'nullable|numeric|min:0',
        ]);

        $product = Product::findOrFail($request->product_id);

        try {
            DB::beginTransaction();

            $total_qty     = 0;
            $valid_suppliers = [];

            foreach ($request->suppliers as $supData) {
                $qty = isset($supData['qty']) && rtrim($supData['qty']) !== '' ? (float) $supData['qty'] : 0;
                if ($qty <= 0) continue;

                $rate         = isset($supData['rate']) && rtrim($supData['rate']) !== '' ? (float) $supData['rate'] : ($product->purchase_price ?? $product->sale_price);
                $finished_qty = isset($supData['finished_qty']) && rtrim($supData['finished_qty']) !== '' ? (float) $supData['finished_qty'] : $qty;

                $total_qty += $qty;

                $valid_suppliers[] = [
                    'id'           => $supData['id'],
                    'qty'          => $qty,
                    'finished_qty' => $finished_qty,
                    'price'        => $rate,
                ];
            }

            if (empty($valid_suppliers)) {
                throw new \Exception('Please enter a valid quantity for at least one supplier.');
            }

            $lockedProduct = Product::lockForUpdate()->find($product->id);

            foreach ($valid_suppliers as $supData) {
                $amount = $supData['qty'] * $supData['price'];

                $purchase = Purchase::create([
                    'supplier_id'  => $supData['id'],
                    'date'         => $request->date,
                    'total_amount' => $amount,
                ]);

                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'product_id'  => $lockedProduct->id,
                    'qty'         => $supData['qty'],
                    'price'       => $supData['price'],
                    'amount'      => $amount,
                ]);

                // Add full purchased qty to stock first
                $lockedProduct->stock_qty += $supData['qty'];
                $lockedProduct->save();

                // Auto-adjustment if finished_qty differs from purchased qty
                if ($supData['finished_qty'] != $supData['qty']) {
                    $diff          = $supData['qty'] - $supData['finished_qty'];
                    $type          = $diff > 0 ? 'decrease' : 'increase';
                    $adjustment_qty = abs($diff);

                    StockAdjustment::create([
                        'date'      => $request->date,
                        'product_id'=> $lockedProduct->id,
                        'type'      => $type,
                        'qty'       => $adjustment_qty,
                        'unit_cost' => $supData['price'],
                        'amount'    => $adjustment_qty * $supData['price'],
                        'note'      => 'Auto-adjustment from Bulk Purchase #' . $purchase->id . ' (Variance)',
                    ]);

                    if ($type === 'decrease') {
                        $lockedProduct->stock_qty -= $adjustment_qty;
                    } else {
                        $lockedProduct->stock_qty += $adjustment_qty;
                    }
                    $lockedProduct->save();
                }
            }

            DB::commit();
            return redirect()->route('purchases.index')->with('success', 'Bulk purchases recorded successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage())->withInput();
        }
    }
}
