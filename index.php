<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RST Electronics ERP</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <header>
        <h1>RST ELECTRONICS ERP</h1>
        <nav>
            <button onclick="loadView('dashboard')">Dashboard</button>
            <button onclick="loadView('products')">Products</button>
            <button onclick="loadView('sales')">Sales</button>
            <button onclick="loadView('customers')">Customers</button>
            <button onclick="loadView('telecom')">Telecom</button>
        </nav>
    </header>
    <main id="app"></main>

    <script>
        const API = 'api.php?path=';

        async function fetchJSON(url) {
            const res = await fetch(url);
            return res.json();
        }

        async function loadView(view) {
            const app = document.getElementById('app');
            app.innerHTML = '<p>Loading...</p>';
            try {
                if (view === 'dashboard') {
                    const sales = await fetchJSON(API + 'sales');
                    const products = await fetchJSON(API + 'products');
                    const totalRevenue = sales.reduce((s, x) => s + parseFloat(x.grand_total), 0);
                    const lowStock = products.filter(p => p.stock <= p.low_stock_alert).length;
                    app.innerHTML = `
                        <div class="kpi-grid">
                            <div class="kpi-card"><h3>Total Revenue</h3><p>${totalRevenue.toFixed(3)} OMR</p></div>
                            <div class="kpi-card"><h3>Invoices</h3><p>${sales.length}</p></div>
                            <div class="kpi-card"><h3>Products</h3><p>${products.length}</p></div>
                            <div class="kpi-card"><h3>Low Stock Alerts</h3><p>${lowStock}</p></div>
                        </div>
                        <h2>Recent Sales</h2>
                        <table>
                            <thead><tr><th>Invoice</th><th>Customer</th><th>Total</th><th>Payment</th></tr></thead>
                            <tbody>${sales.slice(0,5).map(s => `<tr><td>${s.invoice_no}</td><td>${s.customer_name || 'Walk-in'}</td><td>${parseFloat(s.grand_total).toFixed(3)} OMR</td><td>${s.payment_method}</td></tr>`).join('')}</tbody>
                        </table>
                    `;
                } else if (view === 'products') {
                    const products = await fetchJSON(API + 'products');
                    app.innerHTML = `
                        <h2>Product Inventory</h2>
                        <button onclick="addProduct()">+ Add Product</button>
                        <table>
                            <thead><tr><th>Name</th><th>SKU</th><th>Price</th><th>Stock</th><th>Alert</th></tr></thead>
                            <tbody>${products.map(p => `<tr class="${p.stock <= p.low_stock_alert ? 'low-stock' : ''}"><td>${p.name}</td><td>${p.sku}</td><td>${parseFloat(p.price).toFixed(3)}</td><td>${p.stock}</td><td>${p.low_stock_alert}</td></tr>`).join('')}</tbody>
                        </table>
                    `;
                } else if (view === 'sales') {
                    const sales = await fetchJSON(API + 'sales');
                    app.innerHTML = `
                        <h2>Sales Invoices</h2>
                        <table>
                            <thead><tr><th>Invoice</th><th>Date</th><th>Customer</th><th>Subtotal</th><th>VAT</th><th>Total</th></tr></thead>
                            <tbody>${sales.map(s => `<tr><td>${s.invoice_no}</td><td>${s.sale_date}</td><td>${s.customer_name || 'Walk-in'}</td><td>${parseFloat(s.subtotal).toFixed(3)}</td><td>${parseFloat(s.vat).toFixed(3)}</td><td>${parseFloat(s.grand_total).toFixed(3)}</td></tr>`).join('')}</tbody>
                        </table>
                    `;
                } else if (view === 'customers') {
                    const customers = await fetchJSON(API + 'customers');
                    app.innerHTML = `
                        <h2>Customers</h2>
                        <table>
                            <thead><tr><th>Name</th><th>Phone</th><th>Email</th><th>Balance</th></tr></thead>
                            <tbody>${customers.map(c => `<tr><td>${c.name}</td><td>${c.phone}</td><td>${c.email}</td><td>${parseFloat(c.balance).toFixed(3)}</td></tr>`).join('')}</tbody>
                        </table>
                    `;
                } else if (view === 'telecom') {
                    const ops = await fetchJSON(API + 'telecom');
                    app.innerHTML = `
                        <h2>Telecom Operators</h2>
                        <table>
                            <thead><tr><th>Name</th><th>Code</th><th>Balance (OMR)</th><th>Commission %</th></tr></thead>
                            <tbody>${ops.map(o => `<tr><td>${o.name}</td><td>${o.code}</td><td>${parseFloat(o.balance).toFixed(3)}</td><td>${o.commission_percent}%</td></tr>`).join('')}</tbody>
                        </table>
                    `;
                }
            } catch (e) {
                app.innerHTML = `<p class="error">Error: ${e.message}</p>`;
            }
        }

        // Load dashboard by default
        loadView('dashboard');
    </script>
</body>
</html>