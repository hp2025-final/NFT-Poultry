<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Product;
use App\Models\Customer;
use App\Models\CustomerPrice;
use App\Models\Receipt;
use App\Services\FinancialService;
use Carbon\Carbon;

class SaleController extends Controller
{
    protected $finService;

    public function __construct(FinancialService $finService)
    {
        $this->finService = $finService;
    }
    public function index(Request $request)
    {
        $startDate = $request->input('start_date', date('Y-m-d'));
        $endDate = $request->input('end_date', date('Y-m-d'));

        $sales = Sale::with(['customer', 'items'])
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(20);

        return view('sales.index', compact('sales'));
    }

    public function create()
    {
        $customers = Customer::where('is_active', true)->orderBy('name')->get();
        $products = Product::where('is_active', true)->orderBy('name')->get();
        return view('sales.form', compact('customers', 'products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.qty' => 'required|numeric|min:0.25',
            'items.*.price' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $total_amount = 0;
            $items_data = [];

            // Pre-validation for 0.25 KG step and Stock Availability
            foreach ($request->items as $item) {
                $qty = (float) $item['qty'];

                // 0.25 KG step validation
                if (fmod($qty * 100, 25) != 0) {
                    throw new \Exception("Quantity must be in 0.25 steps (e.g., 0.25, 0.5, 0.75, 1.0). Invalid: " . $qty);
                }

                $product = Product::lockForUpdate()->find($item['product_id']);
                
                // Stock Check
                if ($product->stock_qty < $qty) {
                    throw new \Exception("Insufficient stock for product " . $product->name . " (Only " . $product->stock_qty . " available)");
                }

                $price = (float) $item['price'];
                $amount = $qty * $price;
                $total_amount += $amount;

                $items_data[] = [
                    'product' => $product,
                    'qty' => $qty,
                    'price' => $price,
                    'amount' => $amount,
                ];
            }

            $sale = Sale::create([
                'customer_id' => $request->customer_id,
                'date' => $request->date,
                'total_amount' => $total_amount,
            ]);

            $product_quantities = [];

            foreach ($items_data as $data) {
                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $data['product']->id,
                    'qty' => $data['qty'],
                    'price' => $data['price'],
                    'amount' => $data['amount'],
                ]);

                // Aggregate quantities to avoid stale object overwrite
                $pid = $data['product']->id;
                $product_quantities[$pid] = ($product_quantities[$pid] ?? 0) + $data['qty'];
            }

            // Perform single update per product
            foreach ($product_quantities as $id => $total_qty) {
                $product = Product::lockForUpdate()->find($id);
                $product->stock_qty -= $total_qty;
                $product->save();
            }

            DB::commit();
            return redirect()->route('sales.index')->with('success', 'Sale recorded successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function edit(Sale $sale)
    {
        $sale->load('items.product');
        $customers = Customer::where('is_active', true)->orderBy('name')->get();
        $products = Product::where('is_active', true)->orderBy('name')->get();
        return view('sales.form', compact('sale', 'customers', 'products'));
    }

    public function update(Request $request, Sale $sale)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.qty' => 'required|numeric|min:0.25',
            'items.*.price' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $old_product_quantities = [];
            foreach ($sale->items as $item) {
                $pid = $item->product_id;
                $old_product_quantities[$pid] = ($old_product_quantities[$pid] ?? 0) + $item->qty;
            }

            // 1. Restore old stock
            foreach ($old_product_quantities as $id => $qty) {
                $product = Product::lockForUpdate()->find($id);
                $product->stock_qty += $qty;
                $product->save();
            }

            // 2. Remove old items
            $sale->items()->delete();

            // 3. Process new data (similar to store)
            $total_amount = 0;
            $items_data = [];
            $new_product_quantities = [];

            foreach ($request->items as $item) {
                $qty = (float) $item['qty'];
                if (fmod($qty * 100, 25) != 0) {
                    throw new \Exception("Quantity must be in 0.25 steps. Invalid: " . $qty);
                }

                $product = Product::lockForUpdate()->find($item['product_id']);
                
                // Note: Stock was restored above, so check against restored stock
                if ($product->stock_qty < $qty) {
                    throw new \Exception("Insufficient stock for product " . $product->name . " (Only " . $product->stock_qty . " available)");
                }

                $price = (float) $item['price'];
                $amount = $qty * $price;
                $total_amount += $amount;

                $items_data[] = [
                    'product_id' => $item['product_id'],
                    'qty' => $qty,
                    'price' => $price,
                    'amount' => $amount,
                ];

                $pid = $item['product_id'];
                $new_product_quantities[$pid] = ($new_product_quantities[$pid] ?? 0) + $qty;
            }

            $sale->update([
                'customer_id' => $request->customer_id,
                'date' => $request->date,
                'total_amount' => $total_amount,
            ]);

            foreach ($items_data as $data) {
                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $data['product_id'],
                    'qty' => $data['qty'],
                    'price' => $data['price'],
                    'amount' => $data['amount'],
                ]);
            }

            // Deduct new stock
            foreach ($new_product_quantities as $id => $qty) {
                $product = Product::lockForUpdate()->find($id);
                $product->stock_qty -= $qty;
                $product->save();
            }

            DB::commit();
            return redirect()->route('sales.index')->with('success', 'Sale updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function destroy(Sale $sale)
    {
        try {
            DB::beginTransaction();

            $product_quantities = [];
            foreach ($sale->items as $item) {
                $pid = $item->product_id;
                $product_quantities[$pid] = ($product_quantities[$pid] ?? 0) + $item->qty;
            }

            // Restore stock
            foreach ($product_quantities as $id => $qty) {
                $product = Product::lockForUpdate()->find($id);
                $product->stock_qty += $qty;
                $product->save();
            }

            $sale->items()->delete();
            $sale->delete();

            DB::commit();
            return redirect()->route('sales.index')->with('success', 'Sale deleted and stock restored.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error deleting sale: ' . $e->getMessage());
        }
    }

    public function show(Sale $sale)
    {
        $sale->load(['customer', 'items.product']);
        return view('sales.view', compact('sale'));
    }

    public function bulk(Request $request)
    {
        $date = $request->input('date', date('Y-m-d'));
        $customers = Customer::where('is_active', true)->orderBy('name')->get();
        
        foreach ($customers as $customer) {
            $customer->opening_balance_on_date = $this->finService->getCustomerBalances(
                $customer->id, 
                Carbon::parse($date), 
                Carbon::parse($date)
            )->opening;
        }

        // Given there's only 1 product, select the first active one as default
        $product = Product::where('is_active', true)->first();
        return view('sales.bulk', compact('customers', 'product', 'date'));
    }

    public function storeBulk(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'product_id' => 'required|exists:products,id',
            'customers' => 'required|array|min:1',
            'customers.*.id' => 'required|exists:customers,id',
            'customers.*.qty' => 'nullable|numeric|min:0',
            'customers.*.rate' => 'nullable|numeric|min:0',
        ]);

        $product = Product::findOrFail($request->product_id);

        try {
            DB::beginTransaction();

            $total_qty_needed = 0;
            $valid_customers = [];

            foreach ($request->customers as $custData) {
                // Determine quantity, handle blanks correctly
                $qty = isset($custData['qty']) && rtrim($custData['qty']) !== '' ? (float) $custData['qty'] : 0;
                if ($qty <= 0) continue; // Skip empty rows

                if (fmod($qty * 100, 25) != 0) {
                    throw new \Exception("Quantity must be in 0.25 steps. Invalid: " . $qty);
                }

                $total_qty_needed += $qty;
                // Use input rate or default to product's sale price
                $rate = isset($custData['rate']) && rtrim($custData['rate']) !== '' ? (float) $custData['rate'] : $product->sale_price;

                $valid_customers[] = [
                    'id' => $custData['id'],
                    'qty' => $qty,
                    'price' => $rate,
                ];
            }

            if (empty($valid_customers)) {
                throw new \Exception("Please enter a valid quantity for at least one customer.");
            }

            // Lock product once
            $lockedProduct = Product::lockForUpdate()->find($product->id);
            if ($lockedProduct->stock_qty < $total_qty_needed) {
                throw new \Exception("Insufficient stock for product " . $lockedProduct->name . ". Cannot fulfill bulk order. (Required: {$total_qty_needed}, Available: {$lockedProduct->stock_qty})");
            }

            foreach ($valid_customers as $custData) {
                $amount = $custData['qty'] * $custData['price'];

                $sale = Sale::create([
                    'customer_id' => $custData['id'],
                    'date' => $request->date,
                    'total_amount' => $amount,
                ]);

                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $lockedProduct->id,
                    'qty' => $custData['qty'],
                    'price' => $custData['price'],
                    'amount' => $amount,
                ]);
            }

            $lockedProduct->stock_qty -= $total_qty_needed;
            $lockedProduct->save();

            DB::commit();
            return redirect()->route('sales.index')->with('success', 'Bulk sales recorded successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage())->withInput();
        }
    }
}
