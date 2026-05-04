@extends('layouts.app')

@section('title', 'Dashboard - NF Dev')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <h2 class="mb-0">Dashboard</h2>
        <p class="text-muted">Welcome back! Here is the overview of your business.</p>
    </div>
</div>

<div class="row g-4">
    <!-- Today Sales -->
    <div class="col-md-4 col-sm-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="bg-primary bg-opacity-10 text-primary rounded p-3 me-3">
                        <i class="bi bi-cart-check fs-4"></i>
                    </div>
                    <div>
                        <h6 class="card-title text-muted mb-0">Today's Sales</h6>
                        <h3 class="fw-bold mb-0">{{ number_format($todaySales, 2) }}</h3>
                    </div>
                </div>
                <small class="text-muted">Sales for {{ $today->format('d-m-y') }}</small>
            </div>
        </div>
    </div>

    <!-- Monthly Sales -->
    <div class="col-md-4 col-sm-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="bg-success bg-opacity-10 text-success rounded p-3 me-3">
                        <i class="bi bi-graph-up-arrow fs-4"></i>
                    </div>
                    <div>
                        <h6 class="card-title text-muted mb-0">Month's Sales</h6>
                        <h3 class="fw-bold mb-0">{{ number_format($monthSales, 2) }}</h3>
                    </div>
                </div>
                <small class="text-muted">Sales for {{ $today->format('F Y') }}</small>
            </div>
        </div>
    </div>

    <!-- Monthly Purchases -->
    <div class="col-md-4 col-sm-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="bg-warning bg-opacity-10 text-warning rounded p-3 me-3">
                        <i class="bi bi-box-seam fs-4"></i>
                    </div>
                    <div>
                        <h6 class="card-title text-muted mb-0">Month's Purchases</h6>
                        <h3 class="fw-bold mb-0">{{ number_format($monthPurchases, 2) }}</h3>
                    </div>
                </div>
                <small class="text-muted">Purchases for {{ $today->format('F Y') }}</small>
            </div>
        </div>
    </div>

    <!-- Receivables -->
    <div class="col-md-4 col-sm-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="bg-info bg-opacity-10 text-info rounded p-3 me-3">
                        <i class="bi bi-arrow-down-left-circle fs-4"></i>
                    </div>
                    <div>
                        <h6 class="card-title text-muted mb-0">Total Receivables</h6>
                        <h3 class="fw-bold mb-0">{{ number_format($receivables, 2) }}</h3>
                    </div>
                </div>
                <small class="text-muted">Amount you are owed</small>
            </div>
        </div>
    </div>

    <!-- Payables -->
    <div class="col-md-4 col-sm-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="bg-danger bg-opacity-10 text-danger rounded p-3 me-3">
                        <i class="bi bi-arrow-up-right-circle fs-4"></i>
                    </div>
                    <div>
                        <h6 class="card-title text-muted mb-0">Total Payables</h6>
                        <h3 class="fw-bold mb-0">{{ number_format($payables, 2) }}</h3>
                    </div>
                </div>
                <small class="text-muted">Amount you owe</small>
            </div>
        </div>
    </div>

    <!-- Products Count -->
    <div class="col-md-4 col-sm-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="bg-secondary bg-opacity-10 text-secondary rounded p-3 me-3">
                        <i class="bi bi-tags fs-4"></i>
                    </div>
                    <div>
                        <h6 class="card-title text-muted mb-0">Active Products</h6>
                        <h3 class="fw-bold mb-0">{{ number_format($productCount) }}</h3>
                    </div>
                </div>
                <small class="text-muted">Total active items in inventory</small>
            </div>
        </div>
    </div>
</div>
@endsection
