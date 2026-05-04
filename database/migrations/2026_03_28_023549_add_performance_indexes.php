<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add composite indexes for frequently queried columns.
     * These indexes dramatically speed up date-range reports, ledger calculations,
     * and dashboard queries that filter by customer_id/supplier_id + date.
     */
    public function up(): void
    {
        // Sales: customer lookups + date range filtering
        Schema::table('sales', function (Blueprint $table) {
            $table->index(['customer_id', 'date'], 'idx_sales_customer_date');
            $table->index('date', 'idx_sales_date');
        });

        // Purchases: supplier lookups + date range filtering
        Schema::table('purchases', function (Blueprint $table) {
            $table->index(['supplier_id', 'date'], 'idx_purchases_supplier_date');
            $table->index('date', 'idx_purchases_date');
        });

        // Receipts: customer + date for ledger and report queries
        Schema::table('receipts', function (Blueprint $table) {
            $table->index(['customer_id', 'date'], 'idx_receipts_customer_date');
            $table->index(['account_id', 'date'], 'idx_receipts_account_date');
        });

        // Payments: supplier + date for ledger and report queries
        Schema::table('payments', function (Blueprint $table) {
            $table->index(['supplier_id', 'date'], 'idx_payments_supplier_date');
            $table->index(['account_id', 'date'], 'idx_payments_account_date');
        });

        // Expenses: date range + account + category filtering
        Schema::table('expenses', function (Blueprint $table) {
            $table->index(['account_id', 'date'], 'idx_expenses_account_date');
            $table->index('date', 'idx_expenses_date');
        });

        // Equity transactions
        Schema::table('equity_txns', function (Blueprint $table) {
            $table->index(['account_id', 'date'], 'idx_equity_account_date');
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropIndex('idx_sales_customer_date');
            $table->dropIndex('idx_sales_date');
        });
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropIndex('idx_purchases_supplier_date');
            $table->dropIndex('idx_purchases_date');
        });
        Schema::table('receipts', function (Blueprint $table) {
            $table->dropIndex('idx_receipts_customer_date');
            $table->dropIndex('idx_receipts_account_date');
        });
        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex('idx_payments_supplier_date');
            $table->dropIndex('idx_payments_account_date');
        });
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropIndex('idx_expenses_account_date');
            $table->dropIndex('idx_expenses_date');
        });
        Schema::table('equity_txns', function (Blueprint $table) {
            $table->dropIndex('idx_equity_account_date');
        });
    }
};
