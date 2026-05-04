<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::query();

        if ($request->filled('q')) {
            $query->where('name', 'like', '%' . $request->q . '%')
                  ->orWhere('sku', 'like', '%' . $request->q . '%');
        }

        $show = $request->get('show', 'active');
        if ($show === 'active') {
            $query->where('is_active', true);
        } elseif ($show === 'archived') {
            $query->where('is_active', false);
        }

        $products = $query->orderBy('name')->paginate(15);
        
        return view('products.index', compact('products', 'show'));
    }

    public function create()
    {
        return view('products.form');
    }

    public function store(Request $request)
    {
        $request->validate([
            'sku' => 'nullable|string|max:50|unique:products,sku',
            'name' => 'required|string|max:200',
            'unit' => 'nullable|string|max:50',
            'purchase_price' => 'nullable|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',
            'opening_qty' => 'nullable|numeric',
        ]);

        Product::create([
            'sku' => $request->sku,
            'name' => $request->name,
            'unit' => $request->unit,
            'purchase_price' => $request->purchase_price ?? 0,
            'sale_price' => $request->sale_price ?? 0,
            'opening_qty' => $request->opening_qty ?? 0,
            'stock_qty' => $request->opening_qty ?? 0, // Initial stock is the opening quantity
            'is_active' => true,
        ]);

        return redirect()->route('products.index')->with('success', 'Product created successfully.');
    }

    public function edit(Product $product)
    {
        return view('products.form', compact('product'));
    }

    public function update(Request $request, Product $product)
    {
        $request->validate([
            'sku' => 'nullable|string|max:50|unique:products,sku,' . $product->id,
            'name' => 'required|string|max:200',
            'unit' => 'nullable|string|max:50',
            'purchase_price' => 'nullable|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',
            'opening_qty' => 'nullable|numeric',
        ]);

        // Difference in opening qty should reflect in current stock qty
        $old_opening = $product->opening_qty;
        $new_opening = $request->opening_qty ?? 0;
        $diff = $new_opening - $old_opening;

        $product->update([
            'sku' => $request->sku,
            'name' => $request->name,
            'unit' => $request->unit,
            'purchase_price' => $request->purchase_price ?? 0,
            'sale_price' => $request->sale_price ?? 0,
            'opening_qty' => $new_opening,
            'stock_qty' => $product->stock_qty + $diff,
        ]);

        return redirect()->route('products.index')->with('success', 'Product updated successfully.');
    }

    public function toggleActive(Product $product)
    {
        $product->update(['is_active' => !$product->is_active]);
        $status = $product->is_active ? 'restored' : 'archived';
        return redirect()->back()->with('success', "Product $status successfully.");
    }

    public function show(Request $request, Product $product)
    {
        $openingQty = $product->opening_qty;
        
        if ($request->filled('start_date')) {
            $pastPurchases = \App\Models\PurchaseItem::where('product_id', $product->id)->whereHas('purchase', fn($q) => $q->where('date', '<', $request->start_date))->sum('qty');
            $pastSales = \App\Models\SaleItem::where('product_id', $product->id)->whereHas('sale', fn($q) => $q->where('date', '<', $request->start_date))->sum('qty');
            
            // Fix: StockAdjustment uses 'qty' and 'type' (increase/decrease)
            $pastAdjs = \App\Models\StockAdjustment::where('product_id', $product->id)
                ->where('date', '<', $request->start_date)
                ->get()
                ->reduce(function($carry, $adj) {
                    return $carry + ($adj->type === 'increase' ? $adj->qty : -$adj->qty);
                }, 0);
            
            $openingQty += ($pastPurchases - $pastSales + $pastAdjs);
        }

        $purchases = \App\Models\PurchaseItem::with('purchase.supplier')->where('product_id', $product->id)
            ->whereHas('purchase', function($q) use ($request) {
                $q->when($request->start_date, fn($q) => $q->where('date', '>=', $request->start_date))
                  ->when($request->end_date, fn($q) => $q->where('date', '<=', $request->end_date));
            })->get()->map(function($pi) {
                return [
                    'date' => $pi->purchase->date, 'type' => 'Purchase', 'ref' => '#INV-' . $pi->purchase_id,
                    'qty_in' => $pi->qty, 'qty_out' => 0, 'metadata' => $pi->purchase->supplier->name ?? 'Unknown',
                    'route' => route('purchases.show', $pi->purchase_id)
                ];
            });

        $sales = \App\Models\SaleItem::with('sale.customer')->where('product_id', $product->id)
            ->whereHas('sale', function($q) use ($request) {
                $q->when($request->start_date, fn($q) => $q->where('date', '>=', $request->start_date))
                  ->when($request->end_date, fn($q) => $q->where('date', '<=', $request->end_date));
            })->get()->map(function($si) {
                return [
                    'date' => $si->sale->date, 'type' => 'Sale', 'ref' => '#INV-' . $si->sale_id,
                    'qty_in' => 0, 'qty_out' => $si->qty, 'metadata' => $si->sale->customer->name ?? 'Unknown',
                    'route' => route('sales.show', $si->sale_id)
                ];
            });

        $adjustments = \App\Models\StockAdjustment::where('product_id', $product->id)
            ->when($request->start_date, fn($query) => $query->where('date', '>=', $request->start_date))
            ->when($request->end_date, fn($query) => $query->where('date', '<=', $request->end_date))
            ->get()->map(function($adj) {
                return [
                    'date' => $adj->date, 'type' => 'Adjustment', 'ref' => '#ADJ-' . $adj->id,
                    // Fix: Use 'qty' and 'type' from database
                    'qty_in' => $adj->type === 'increase' ? $adj->qty : 0, 
                    'qty_out' => $adj->type === 'decrease' ? $adj->qty : 0, 
                    // Fix: Use 'note' instead of 'reason'
                    'metadata' => $adj->note,
                    'route' => null
                ];
            });

        $transactions = $purchases->concat($sales)->concat($adjustments)->sortBy('date')->values()->toArray();
        
        $runningQty = $openingQty;
        foreach ($transactions as &$txn) {
            $runningQty += $txn['qty_in'];
            $runningQty -= $txn['qty_out'];
            $txn['balance'] = $runningQty;
        }

        $perPage = 30;
        $page = $request->get('page', 1);
        $offset = ($page - 1) * $perPage;
        
        $paginatedItems = array_slice($transactions, $offset, $perPage);
        $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
            $paginatedItems, count($transactions), $perPage, $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $broughtForward = $page == 1 ? $openingQty : ($offset > 0 && isset($transactions[$offset - 1]) ? $transactions[$offset - 1]['balance'] : $openingQty);

        return view('products.show', compact('product', 'paginator', 'openingQty', 'runningQty', 'broughtForward'));
    }
}
