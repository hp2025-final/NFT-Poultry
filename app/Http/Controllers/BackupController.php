<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BackupController extends Controller
{
    public function index()
    {
        return view('backup.index');
    }

    // ─── Helper: find mysql binaries ──────────────────────────────────
    private function findMysqlBinary(string $binary): ?string
    {
        $paths = [
            $binary,
            '/usr/bin/' . $binary,
            '/usr/local/bin/' . $binary,
            'c:\\xampp\\mysql\\bin\\' . $binary . '.exe',
            'C:\\xampp\\mysql\\bin\\' . $binary . '.exe',
        ];

        foreach ($paths as $path) {
            if (PHP_OS_FAMILY === 'Windows') {
                if (file_exists($path)) {
                    return '"' . $path . '"';
                }
            } else {
                $result = trim(shell_exec("which {$binary} 2>/dev/null") ?? '');
                if ($result) {
                    return $result;
                }
            }
        }

        return null;
    }

    private function buildConnectionFlags(): string
    {
        $user = config('database.connections.mysql.username');
        $password = config('database.connections.mysql.password');
        $host = config('database.connections.mysql.host');
        $port = config('database.connections.mysql.port', 3306);

        $flags = "--user={$user} --host={$host} --port={$port}";
        if ($password) {
            $flags .= " --password=" . escapeshellarg($password);
        }
        return $flags;
    }

    // ─── Option 3: Backup (Download .sql) ─────────────────────────────
    public function download()
    {
        $database = config('database.connections.mysql.database');
        $mysqldump = $this->findMysqlBinary('mysqldump');

        if (!$mysqldump) {
            return back()->with('error', 'mysqldump not found. Please ensure MySQL tools are accessible on your server.');
        }

        $filename = "database-backup-" . date('Y-m-d-H-i-s') . ".sql";
        $path = storage_path('app/' . $filename);

        $flags = $this->buildConnectionFlags();
        $command = "{$mysqldump} {$flags} {$database} > \"{$path}\" 2>&1";

        exec($command, $output, $returnCode);

        if (file_exists($path) && filesize($path) > 0) {
            return response()->download($path)->deleteFileAfterSend(true);
        }

        Log::error('Backup failed: ' . implode("\n", $output));
        return back()->with('error', 'Backup generation failed. Check server logs for details.');
    }

    // ─── Option 2: Restore from MySQL .sql file ───────────────────────
    public function restore(Request $request)
    {
        $request->validate([
            'sql_file' => 'required|file|max:50000',
        ]);

        $file = $request->file('sql_file');
        $path = $file->path();

        $database = config('database.connections.mysql.database');
        $mysql = $this->findMysqlBinary('mysql');

        if (!$mysql) {
            return back()->with('error', 'mysql client not found. Please ensure MySQL tools are accessible on your server.');
        }

        $flags = $this->buildConnectionFlags();
        $command = "{$mysql} {$flags} {$database} < \"{$path}\" 2>&1";

        exec($command, $output, $returnCode);

        if ($returnCode === 0) {
            return back()->with('success', 'Database restored successfully from MySQL backup! Please log out and log back in to verify.');
        }

        Log::error('Restore failed: ' . implode("\n", $output));
        return back()->with('error', 'Database restore failed. Check server logs for details.');
    }

    // ─── Option 1: Restore from SQLite (nfdev.db) ─────────────────────
    public function restoreSqlite(Request $request)
    {
        $request->validate([
            'sqlite_file' => 'required|file|max:50000',
        ]);

        $file = $request->file('sqlite_file');
        $sqlitePath = $file->path();

        try {
            $sqlite = new \PDO('sqlite:' . $sqlitePath);
            $sqlite->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        } catch (\Exception $e) {
            Log::error('SQLite open failed: ' . $e->getMessage());
            return back()->with('error', 'Could not open SQLite database: ' . $e->getMessage());
        }

        // Tables to import, in dependency order.
        // Format: 'sqlite_table' => ['mysql_table' => '...', 'columns' => [...mapping...]]
        $tableMap = $this->getSqliteToMysqlMap();

        $summary = [];

        try {
            DB::statement('SET FOREIGN_KEY_CHECKS = 0');

            DB::beginTransaction();

            // Truncate all target MySQL tables first
            foreach ($tableMap as $config) {
                DB::table($config['mysql_table'])->truncate();
            }

            // Import each table
            foreach ($tableMap as $sqliteTable => $config) {
                $rows = $sqlite->query("SELECT * FROM [{$sqliteTable}]")->fetchAll(\PDO::FETCH_ASSOC);
                $imported = 0;

                foreach ($rows as $row) {
                    $mapped = [];
                    foreach ($config['columns'] as $sqliteCol => $mysqlCol) {
                        if (array_key_exists($sqliteCol, $row)) {
                            $mapped[$mysqlCol] = $row[$sqliteCol];
                        }
                    }

                    if (!empty($mapped)) {
                        DB::table($config['mysql_table'])->insert($mapped);
                        $imported++;
                    }
                }

                $summary[] = "{$config['mysql_table']}: {$imported} rows";
            }

            DB::commit();
            DB::statement('SET FOREIGN_KEY_CHECKS = 1');

            $summaryText = implode(' | ', $summary);
            return back()->with('success', "SQLite data restored successfully! Imported: {$summaryText}. Please log out and log back in to verify.");

        } catch (\Exception $e) {
            DB::rollBack();
            DB::statement('SET FOREIGN_KEY_CHECKS = 1');
            Log::error('SQLite restore failed: ' . $e->getMessage());
            return back()->with('error', 'SQLite restore failed: ' . $e->getMessage());
        }
    }

    // ─── Option 4: Remove All Data (Admin only) ───────────────────────
    public function removeAllData()
    {
        $tables = [
            'sale_items',
            'purchase_items',
            'customer_prices',
            'receipts',
            'payments',
            'sales',
            'purchases',
            'expenses',
            'equity_txns',
            'stock_adjustments',
            'products',
            'customers',
            'suppliers',
            'accounts',
            'expense_categories',
            'company_infos',
        ];

        try {
            DB::statement('SET FOREIGN_KEY_CHECKS = 0');

            foreach ($tables as $table) {
                DB::table($table)->truncate();
            }

            DB::statement('SET FOREIGN_KEY_CHECKS = 1');

            return back()->with('success', 'All business data has been permanently removed. The system is now clean. Users and sessions are preserved.');

        } catch (\Exception $e) {
            DB::statement('SET FOREIGN_KEY_CHECKS = 1');
            Log::error('Remove all data failed: ' . $e->getMessage());
            return back()->with('error', 'Failed to remove data: ' . $e->getMessage());
        }
    }

    // ─── SQLite → MySQL column mapping ────────────────────────────────
    private function getSqliteToMysqlMap(): array
    {
        return [
            // 1. Independent tables first
            'company_info' => [
                'mysql_table' => 'company_infos',
                'columns' => [
                    'id' => 'id',
                    'name' => 'name',
                    'phone' => 'phone',
                    'email' => 'email',
                    'address' => 'address',
                    'updated_at' => 'updated_at',
                ],
            ],
            'account' => [
                'mysql_table' => 'accounts',
                'columns' => [
                    'id' => 'id',
                    'name' => 'name',
                    'type' => 'type',
                    'opening_balance' => 'opening_balance',
                    'is_active' => 'is_active',
                    'created_at' => 'created_at',
                    'updated_at' => 'updated_at',
                ],
            ],
            'expense_category' => [
                'mysql_table' => 'expense_categories',
                'columns' => [
                    'id' => 'id',
                    'name' => 'name',
                    'is_active' => 'is_active',
                    'created_at' => 'created_at',
                    'updated_at' => 'updated_at',
                ],
            ],
            'customer' => [
                'mysql_table' => 'customers',
                'columns' => [
                    'id' => 'id',
                    'name' => 'name',
                    'phone' => 'phone',
                    'opening_balance' => 'opening_balance',
                    'is_active' => 'is_active',
                    'created_at' => 'created_at',
                    'updated_at' => 'updated_at',
                ],
            ],
            'supplier' => [
                'mysql_table' => 'suppliers',
                'columns' => [
                    'id' => 'id',
                    'name' => 'name',
                    'phone' => 'phone',
                    'opening_balance' => 'opening_balance',
                    'is_active' => 'is_active',
                    'created_at' => 'created_at',
                    'updated_at' => 'updated_at',
                ],
            ],
            'product' => [
                'mysql_table' => 'products',
                'columns' => [
                    'id' => 'id',
                    'sku' => 'sku',
                    'name' => 'name',
                    'unit' => 'unit',
                    'purchase_price' => 'purchase_price',
                    'sale_price' => 'sale_price',
                    'opening_qty' => 'opening_qty',
                    'stock_qty' => 'stock_qty',
                    'is_active' => 'is_active',
                    'created_at' => 'created_at',
                    'updated_at' => 'updated_at',
                ],
            ],

            // 2. Dependent tables
            'customer_price' => [
                'mysql_table' => 'customer_prices',
                'columns' => [
                    'id' => 'id',
                    'customer_id' => 'customer_id',
                    'product_id' => 'product_id',
                    'effective_date' => 'effective_date',
                    'price' => 'price',
                    'created_at' => 'created_at',
                    'updated_at' => 'updated_at',
                ],
            ],
            'expense' => [
                'mysql_table' => 'expenses',
                'columns' => [
                    'id' => 'id',
                    'date' => 'date',
                    'category_id' => 'expense_category_id',  // ← column name mapping
                    'account_id' => 'account_id',
                    'amount' => 'amount',
                    'note' => 'note',
                    'created_at' => 'created_at',
                    'updated_at' => 'updated_at',
                ],
            ],
            'sale' => [
                'mysql_table' => 'sales',
                'columns' => [
                    'id' => 'id',
                    'customer_id' => 'customer_id',
                    'date' => 'date',
                    'total_amount' => 'total_amount',
                    'created_at' => 'created_at',
                    'updated_at' => 'updated_at',
                ],
            ],
            'sale_item' => [
                'mysql_table' => 'sale_items',
                'columns' => [
                    'id' => 'id',
                    'sale_id' => 'sale_id',
                    'product_id' => 'product_id',
                    'qty' => 'qty',
                    'price' => 'price',
                    'amount' => 'amount',
                    'created_at' => 'created_at',
                    'updated_at' => 'updated_at',
                ],
            ],
            'purchase' => [
                'mysql_table' => 'purchases',
                'columns' => [
                    'id' => 'id',
                    'supplier_id' => 'supplier_id',
                    'date' => 'date',
                    'total_amount' => 'total_amount',
                    'created_at' => 'created_at',
                    'updated_at' => 'updated_at',
                ],
            ],
            'purchase_item' => [
                'mysql_table' => 'purchase_items',
                'columns' => [
                    'id' => 'id',
                    'purchase_id' => 'purchase_id',
                    'product_id' => 'product_id',
                    'qty' => 'qty',
                    'price' => 'price',
                    'amount' => 'amount',
                    'created_at' => 'created_at',
                    'updated_at' => 'updated_at',
                ],
            ],
            'receipt' => [
                'mysql_table' => 'receipts',
                'columns' => [
                    'id' => 'id',
                    'customer_id' => 'customer_id',
                    'account_id' => 'account_id',
                    'date' => 'date',
                    'amount' => 'amount',
                    'note' => 'note',
                    'created_at' => 'created_at',
                    'updated_at' => 'updated_at',
                ],
            ],
            'payment' => [
                'mysql_table' => 'payments',
                'columns' => [
                    'id' => 'id',
                    'supplier_id' => 'supplier_id',
                    'account_id' => 'account_id',
                    'date' => 'date',
                    'amount' => 'amount',
                    'note' => 'note',
                    'created_at' => 'created_at',
                    'updated_at' => 'updated_at',
                ],
            ],
            'equity_txn' => [
                'mysql_table' => 'equity_txns',
                'columns' => [
                    'id' => 'id',
                    'date' => 'date',
                    'account_id' => 'account_id',
                    'type' => 'type',
                    'amount' => 'amount',
                    'note' => 'note',
                    'created_at' => 'created_at',
                    'updated_at' => 'updated_at',
                ],
            ],
            'stock_adjustment' => [
                'mysql_table' => 'stock_adjustments',
                'columns' => [
                    'id' => 'id',
                    'date' => 'date',
                    'product_id' => 'product_id',
                    'type' => 'type',
                    'qty' => 'qty',
                    'unit_cost' => 'unit_cost',
                    'amount' => 'amount',
                    'note' => 'note',
                    'created_at' => 'created_at',
                    'updated_at' => 'updated_at',
                ],
            ],
        ];
    }
}
