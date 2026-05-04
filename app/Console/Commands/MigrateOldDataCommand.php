<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MigrateOldDataCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:old-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate data from old SQLite db to new MySQL db';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Schema::disableForeignKeyConstraints();

        $tables = [
            'company_info' => 'company_infos',
            'account' => 'accounts',
            'expense_category' => 'expense_categories',
            'expense' => 'expenses',
            'equity_txn' => 'equity_txns',
            'product' => 'products',
            'customer' => 'customers',
            'supplier' => 'suppliers',
            'customer_price' => 'customer_prices',
            'stock_adjustment' => 'stock_adjustments',
            'purchase' => 'purchases',
            'purchase_item' => 'purchase_items',
            'sale' => 'sales',
            'sale_item' => 'sale_items',
            'receipt' => 'receipts',
            'payment' => 'payments',
        ];

        foreach ($tables as $oldTable => $newTable) {
            $this->info("Migrating {$oldTable} to {$newTable}...");
            try {
                DB::table($newTable)->truncate();
                $records = DB::connection('sqlite_old')->table($oldTable)->get();
                $data = json_decode(json_encode($records), true);
                
                if (count($data) > 0) {
                    foreach(array_chunk($data, 100) as $chunk) {
                        DB::table($newTable)->insert($chunk);
                    }
                }
                $this->info("  -> Migrated " . count($data) . " rows.");
            } catch (\Exception $e) {
                $this->error("  -> Error: " . $e->getMessage());
            }
        }

        Schema::enableForeignKeyConstraints();
        $this->info("Data migration completed successfully.");
    }
}
