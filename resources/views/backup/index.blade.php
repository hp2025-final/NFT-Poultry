@extends('layouts.app')
@section('title', 'Database Backup & Restore')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3"><i class="bi bi-database-gear me-2"></i>System Backup & Restore</h1>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle me-1"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle me-1"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row">

    {{-- ═══════════ Card 1: Restore from SQLite (Old App) ═══════════ --}}
    <div class="col-md-6 mb-4">
        <div class="card shadow-sm h-100 border-warning">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0"><i class="bi bi-database-up"></i> Restore from Old App (SQLite)</h5>
            </div>
            <div class="card-body d-flex flex-column">
                <p class="card-text text-muted">Import all business data from the old SQLite database (<code>nfdev.db</code>) into this MySQL system. Users will <strong>not</strong> be overwritten.</p>

                <div class="alert alert-warning py-2">
                    <small><strong>WARNING:</strong> This will erase all current business data in MySQL and replace it with data from the old SQLite database. Users and sessions are preserved.</small>
                </div>

                <form method="POST" action="{{ route('backup.restore_sqlite') }}" enctype="multipart/form-data" class="mt-auto" id="sqliteRestoreForm">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark">Select .db SQLite File</label>
                        <input type="file" name="sqlite_file" class="form-control" accept=".db,.sqlite,.sqlite3" required id="sqliteFileInput">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark">Type <strong>RESTORE</strong> to confirm</label>
                        <input type="text" class="form-control" id="sqliteConfirm" autocomplete="off" placeholder="Type RESTORE here...">
                    </div>
                    <button type="submit" class="btn btn-warning w-100 py-2" id="sqliteRestoreBtn" disabled>
                        <i class="bi bi-database-up"></i> Restore from SQLite
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- ═══════════ Card 2: Restore from MySQL Backup ═══════════ --}}
    <div class="col-md-6 mb-4">
        <div class="card shadow-sm h-100 border-danger">
            <div class="card-header bg-danger text-white">
                <h5 class="mb-0"><i class="bi bi-upload"></i> Restore from MySQL Backup</h5>
            </div>
            <div class="card-body d-flex flex-column">
                <p class="card-text text-muted">Upload a previously downloaded <code>.sql</code> backup file to completely overwrite and restore the application data.</p>

                <div class="alert alert-danger py-2 border-danger">
                    <small><strong>DANGER:</strong> Restoring a backup will erase all current data in the system. Make sure you know what you are doing.</small>
                </div>

                <form method="POST" action="{{ route('backup.restore') }}" enctype="multipart/form-data" class="mt-auto" id="mysqlRestoreForm">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-bold text-danger">Select .sql Backup File</label>
                        <input type="file" name="sql_file" class="form-control" accept=".sql" required id="mysqlFileInput">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold text-danger">Type <strong>RESTORE</strong> to confirm</label>
                        <input type="text" class="form-control" id="mysqlConfirm" autocomplete="off" placeholder="Type RESTORE here...">
                    </div>
                    <button type="submit" class="btn btn-danger w-100 py-2" id="mysqlRestoreBtn" disabled>
                        <i class="bi bi-exclamation-triangle"></i> Upload and Restore
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- ═══════════ Card 3: Backup (Download) ═══════════ --}}
    <div class="col-md-6 mb-4">
        <div class="card shadow-sm h-100 border-primary">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-download"></i> Backup Database</h5>
            </div>
            <div class="card-body d-flex flex-column">
                <p class="card-text text-muted">Generate and download a complete <code>.sql</code> backup of your entire MySQL database including customers, inventory, sales, and ledgers.</p>

                <div class="alert alert-info py-2">
                    <small><i class="bi bi-info-circle me-1"></i>It is recommended to download a backup <strong>every single day</strong> after business hours to prevent data loss.</small>
                </div>

                <a href="{{ route('backup.download') }}" class="btn btn-primary w-100 py-3 mt-auto">
                    <strong><i class="bi bi-cloud-download"></i> Download Full SQL Backup</strong>
                </a>
            </div>
        </div>
    </div>

    {{-- ═══════════ Card 4: Remove All Data (Admin Only) ═══════════ --}}
    @if(auth()->user()->is_admin)
    <div class="col-md-6 mb-4">
        <div class="card shadow-sm h-100 border-dark">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0"><i class="bi bi-trash3"></i> Remove All Data <span class="badge bg-danger ms-2">Admin Only</span></h5>
            </div>
            <div class="card-body d-flex flex-column">
                <p class="card-text text-muted">Permanently delete <strong>all business data</strong> from the system. This includes customers, suppliers, products, sales, purchases, receipts, payments, expenses, and all related records.</p>

                <div class="alert alert-danger py-2 border-danger">
                    <small><strong>EXTREME DANGER:</strong> This action is irreversible! All business data will be permanently deleted. Users and login sessions will be preserved. Make a backup first!</small>
                </div>

                <form method="POST" action="{{ route('backup.remove_all') }}" class="mt-auto" id="removeAllForm">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-bold text-danger">Type <strong>DELETE</strong> to confirm</label>
                        <input type="text" class="form-control" id="removeAllConfirm" autocomplete="off" placeholder="Type DELETE here...">
                    </div>
                    <button type="submit" class="btn btn-dark w-100 py-2" id="removeAllBtn" disabled>
                        <i class="bi bi-trash3"></i> Permanently Remove All Data
                    </button>
                </form>
            </div>
        </div>
    </div>
    @endif

</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {

    // --- SQLite Restore confirmation ---
    const sqliteConfirm = document.getElementById('sqliteConfirm');
    const sqliteBtn = document.getElementById('sqliteRestoreBtn');
    if (sqliteConfirm && sqliteBtn) {
        sqliteConfirm.addEventListener('input', function() {
            sqliteBtn.disabled = this.value.trim().toUpperCase() !== 'RESTORE';
        });
        document.getElementById('sqliteRestoreForm').addEventListener('submit', function(e) {
            if (sqliteConfirm.value.trim().toUpperCase() !== 'RESTORE') {
                e.preventDefault();
                return;
            }
            if (!confirm('Final confirmation: This will WIPE all current MySQL business data and replace it with SQLite data. Continue?')) {
                e.preventDefault();
            }
        });
    }

    // --- MySQL Restore confirmation ---
    const mysqlConfirm = document.getElementById('mysqlConfirm');
    const mysqlBtn = document.getElementById('mysqlRestoreBtn');
    if (mysqlConfirm && mysqlBtn) {
        mysqlConfirm.addEventListener('input', function() {
            const fileSelected = document.getElementById('mysqlFileInput').files.length > 0;
            mysqlBtn.disabled = !(this.value.trim().toUpperCase() === 'RESTORE' && fileSelected);
        });
        document.getElementById('mysqlFileInput').addEventListener('change', function() {
            const typed = mysqlConfirm.value.trim().toUpperCase() === 'RESTORE';
            mysqlBtn.disabled = !(typed && this.files.length > 0);
        });
        document.getElementById('mysqlRestoreForm').addEventListener('submit', function(e) {
            if (mysqlConfirm.value.trim().toUpperCase() !== 'RESTORE') {
                e.preventDefault();
                return;
            }
            if (!confirm('Final confirmation: This will WIPE the current database and replace it with the uploaded backup. Continue?')) {
                e.preventDefault();
            }
        });
    }

    // --- Remove All Data confirmation (admin only) ---
    const removeConfirm = document.getElementById('removeAllConfirm');
    const removeBtn = document.getElementById('removeAllBtn');
    if (removeConfirm && removeBtn) {
        removeConfirm.addEventListener('input', function() {
            removeBtn.disabled = this.value.trim().toUpperCase() !== 'DELETE';
        });
        document.getElementById('removeAllForm').addEventListener('submit', function(e) {
            if (removeConfirm.value.trim().toUpperCase() !== 'DELETE') {
                e.preventDefault();
                return;
            }
            if (!confirm('LAST CHANCE: All business data will be PERMANENTLY DELETED. This cannot be undone. Continue?')) {
                e.preventDefault();
            }
        });
    }

});
</script>
@endsection
