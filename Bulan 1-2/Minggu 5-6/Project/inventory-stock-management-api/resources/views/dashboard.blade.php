<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Inventory Dashboard</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>

body{
    background:#eef2f7;
    font-family:'Segoe UI',sans-serif;
}

.header{
    background:linear-gradient(135deg,#1f3b5b,#355c8c);
    color:#fff;
    padding:35px;
    border-radius:18px;
    margin-bottom:35px;
    position:relative;
    box-shadow:0 10px 25px rgba(0,0,0,.15);
}

.header::after{
    content:'';
    display:block;
    width:120px;
    height:3px;
    background:rgba(255,255,255,.7);
    border-radius:20px;
    margin-top:18px;
}

.header h1{
    font-weight:700;
    margin-bottom:10px;
}

.header p{
    color:#e4edf7;
}

.badge{
    background:rgba(255,255,255,.15)!important;
    color:white!important;
    border:1px solid rgba(255,255,255,.2);
    margin-right:8px;
    padding:8px 12px;
}

.card{
    border:none;
    border-radius:18px;
    background:#ffffff;
    box-shadow:0 6px 18px rgba(15,23,42,.08);
    transition:.25s ease;
}

.card:hover{
    transform:translateY(-6px);
    box-shadow:0 15px 30px rgba(15,23,42,.15);
}

.icon{
    width:60px;
    height:60px;
    border-radius:50%;
    display:flex;
    justify-content:center;
    align-items:center;
    background:#edf2f7;
    font-size:28px;
}

.number{
    font-size:36px;
    font-weight:700;
    margin-top:5px;
    color:#1f2937;
}

.text-primary{
    color:#2563eb!important;
}

.text-success{
    color:#059669!important;
}

.text-warning{
    color:#d97706!important;
}

.text-danger{
    color:#dc2626!important;
}

h6{
    color:#6b7280;
    font-weight:600;
}

.header small{
    color:#f8fafc;
    font-weight:500;
    opacity:.9;
}

h2{
    font-weight:700;
}

hr{
    border-top:1px solid #d1d5db;
}

footer{
    color:#6b7280;
    font-size:14px;
}

</style>

</head>

<body>

<div class="container py-5">

    <!-- HEADER -->

    <div class="header">

        <h1 class="fw-bold mb-2">
            <i class="bi bi-speedometer2"></i>
            Inventory Stock Management Dashboard
        </h1>

        <p class="mb-3">
            Monitoring data inventaris gudang secara realtime.
        </p>

        

        <small>
            <i class="bi bi-calendar-event"></i>
            {{ now()->format('d F Y') }}
        </small>

    </div>

    <!-- CARD -->

    <div class="row g-4">

        <!-- Product -->

        <div class="col-md-3">

            <div class="card p-4">

                <div class="d-flex justify-content-between align-items-center">

                    <div>

                        <small>Total Product</small>

                        <div class="number text-primary">

                            {{ $total_products }}

                        </div>

                    </div>

                    <div class="icon">

                        <i class="bi bi-box-seam text-primary"></i>

                    </div>

                </div>

            </div>

        </div>

        <!-- Category -->

        <div class="col-md-3">

            <div class="card p-4">

                <div class="d-flex justify-content-between align-items-center">

                    <div>

                        <small>Total Category</small>

                        <div class="number text-success">

                            {{ $total_categories }}

                        </div>

                    </div>

                    <div class="icon">

                        <i class="bi bi-tags text-success"></i>

                    </div>

                </div>

            </div>

        </div>

        <!-- Supplier -->

        <div class="col-md-3">

            <div class="card p-4">

                <div class="d-flex justify-content-between align-items-center">

                    <div>

                        <small>Total Supplier</small>

                        <div class="number text-warning">

                            {{ $total_suppliers }}

                        </div>

                    </div>

                    <div class="icon">

                        <i class="bi bi-truck text-warning"></i>

                    </div>

                </div>

            </div>

        </div>

        <!-- Warehouse -->

        <div class="col-md-3">

            <div class="card p-4">

                <div class="d-flex justify-content-between align-items-center">

                    <div>

                        <small>Total Warehouse</small>

                        <div class="number text-danger">

                            {{ $total_warehouses }}

                        </div>

                    </div>

                    <div class="icon">

                        <i class="bi bi-building text-danger"></i>

                    </div>

                </div>

            </div>

        </div>

        <!-- Total Stock -->

        <div class="col-md-3">

            <div class="card p-4">

                <h6>Total Stock</h6>

                <h2 class="text-primary">

                    {{ $total_stock }} Items

                </h2>

            </div>

        </div>

        <!-- Stock In -->

        <div class="col-md-3">

            <div class="card p-4">

                <h6>Stock In Today</h6>

                <h2 class="text-success">

                    {{ $stock_in_today }}

                </h2>

            </div>

        </div>

        <!-- Stock Out -->

        <div class="col-md-3">

            <div class="card p-4">

                <h6>Stock Out Today</h6>

                <h2 class="text-danger">

                    {{ $stock_out_today }}

                </h2>

            </div>

        </div>

        <!-- Adjustment -->

        <div class="col-md-3">

            <div class="card p-4">

                <h6>Stock Adjustment</h6>

                <h2 class="text-warning">

                    {{ $stock_adjustment_today }}

                </h2>

            </div>

        </div>

    </div>

    <hr class="mt-5">

    <footer class="text-center py-3">

        Inventory Stock Management API © {{ date('Y') }}

    </footer>

</div>

</body>

</html>