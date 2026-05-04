{{-- Shared sidebar links - used by both desktop sidebar and mobile offcanvas --}}
@php
  $currentRoute = Route::currentRouteName() ?? '';
@endphp

<span class="sidebar-label">Main</span>
<a href="{{ route('dashboard') }}" class="sidebar-link {{ $currentRoute == 'dashboard' ? 'active' : '' }}">
  <i class="bi bi-speedometer2"></i> Dashboard
</a>

<span class="sidebar-label">Sales & Collections</span>
<a href="{{ route('sales.index') }}" class="sidebar-link {{ str_starts_with($currentRoute, 'sales.') ? 'active' : '' }}">
  <i class="bi bi-receipt"></i> Sales
</a>
<a href="{{ route('sales.bulk') }}" class="sidebar-link {{ $currentRoute == 'sales.bulk' ? 'active' : '' }}">
  <i class="bi bi-stack"></i> Bulk Customer Sale
</a>
<a href="{{ route('receipts.index') }}" class="sidebar-link {{ str_starts_with($currentRoute, 'receipts.') && $currentRoute != 'receipts.bulk' ? 'active' : '' }}">
  <i class="bi bi-cash-stack"></i> Receipts
</a>
<a href="{{ route('receipts.bulk') }}" class="sidebar-link {{ $currentRoute == 'receipts.bulk' ? 'active' : '' }}">
  <i class="bi bi-collection"></i> Bulk Receipts
</a>
<a href="{{ route('customers.index') }}" class="sidebar-link {{ str_starts_with($currentRoute, 'customers.') ? 'active' : '' }}">
  <i class="bi bi-people"></i> Customers
</a>
<a href="{{ route('reports.daily_customer') }}" class="sidebar-link {{ $currentRoute == 'reports.daily_customer' ? 'active' : '' }}">
  <i class="bi bi-person-lines-fill"></i> Daily Customer Report
</a>

<span class="sidebar-label">Purchases & Payments</span>
<a href="{{ route('purchases.index') }}" class="sidebar-link {{ str_starts_with($currentRoute, 'purchases.') && $currentRoute != 'purchases.bulk' ? 'active' : '' }}">
  <i class="bi bi-bag"></i> Purchases
</a>
<a href="{{ route('purchases.bulk') }}" class="sidebar-link {{ $currentRoute == 'purchases.bulk' ? 'active' : '' }}">
  <i class="bi bi-stack"></i> Bulk Purchases
</a>
<a href="{{ route('payments.index') }}" class="sidebar-link {{ str_starts_with($currentRoute, 'payments.') && $currentRoute != 'payments.bulk' ? 'active' : '' }}">
  <i class="bi bi-credit-card"></i> Payments
</a>
<a href="{{ route('payments.bulk') }}" class="sidebar-link {{ $currentRoute == 'payments.bulk' ? 'active' : '' }}">
  <i class="bi bi-collection"></i> Bulk Payments
</a>
<a href="{{ route('suppliers.index') }}" class="sidebar-link {{ str_starts_with($currentRoute, 'suppliers.') ? 'active' : '' }}">
  <i class="bi bi-truck"></i> Suppliers
</a>

<span class="sidebar-label">Inventory</span>
<a href="{{ route('products.index') }}" class="sidebar-link {{ str_starts_with($currentRoute, 'products.') ? 'active' : '' }}">
  <i class="bi bi-box-seam"></i> Products
</a>
<a href="{{ route('stock_adjustments.index') }}" class="sidebar-link {{ str_starts_with($currentRoute, 'stock_adjustments.') ? 'active' : '' }}">
  <i class="bi bi-arrow-left-right"></i> Stock Adjustments
</a>

<span class="sidebar-label">Accounting</span>
<a href="{{ route('accounts.index') }}" class="sidebar-link {{ str_starts_with($currentRoute, 'accounts.') ? 'active' : '' }}">
  <i class="bi bi-bank"></i> Accounts
</a>
<a href="{{ route('equity.index') }}" class="sidebar-link {{ str_starts_with($currentRoute, 'equity.') ? 'active' : '' }}">
  <i class="bi bi-wallet2"></i> Owner's Equity
</a>
<a href="{{ route('expenses.index') }}" class="sidebar-link {{ str_starts_with($currentRoute, 'expenses.') ? 'active' : '' }}">
  <i class="bi bi-cart-dash"></i> Expenses
</a>
<a href="{{ route('expense_categories.index') }}" class="sidebar-link {{ str_starts_with($currentRoute, 'expense_categories.') ? 'active' : '' }}">
  <i class="bi bi-tags"></i> Expense Categories
</a>

<div class="sidebar-divider"></div>

<span class="sidebar-label">Reports</span>
<a href="{{ route('reports.daily') }}" class="sidebar-link {{ $currentRoute == 'reports.daily' ? 'active' : '' }}">
  <i class="bi bi-calendar-day"></i> Daily Report
</a>
<a href="{{ route('reports.trial_balance') }}" class="sidebar-link {{ $currentRoute == 'reports.trial_balance' ? 'active' : '' }}">
  <i class="bi bi-journal-text"></i> Trial Balance
</a>
<a href="{{ route('reports.profit_loss') }}" class="sidebar-link {{ $currentRoute == 'reports.profit_loss' ? 'active' : '' }}">
  <i class="bi bi-graph-up-arrow"></i> Profit & Loss
</a>
<a href="{{ route('reports.balance_sheet') }}" class="sidebar-link {{ $currentRoute == 'reports.balance_sheet' ? 'active' : '' }}">
  <i class="bi bi-clipboard-data"></i> Balance Sheet
</a>

<div class="sidebar-divider"></div>

<span class="sidebar-label">System</span>
<a href="{{ route('settings.company') }}" class="sidebar-link {{ $currentRoute == 'settings.company' ? 'active' : '' }}">
  <i class="bi bi-gear"></i> Company Settings
</a>
<a href="{{ route('backup.index') }}" class="sidebar-link {{ str_starts_with($currentRoute, 'backup.') ? 'active' : '' }}">
  <i class="bi bi-cloud-download"></i> Backup & Restore
</a>
