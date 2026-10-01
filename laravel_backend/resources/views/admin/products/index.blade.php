<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Products</title>
    <style>
        :root {
            --bg: #f4f6fb;
            --panel: #ffffff;
            --text: #172133;
            --muted: #5b6577;
            --accent: #1f6feb;
            --accent-soft: #e8f0ff;
            --border: #d8deea;
            --success: #0f9d58;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(130deg, #eef3ff 0%, #f7f8fc 50%, #edf8f4 100%);
            color: var(--text);
        }

        .layout {
            min-height: 100vh;
            display: grid;
            grid-template-columns: 260px 1fr;
        }

        .sidebar {
            background: #111a2b;
            color: #dfe7f7;
            padding: 28px 18px;
        }

        .brand {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 28px;
            letter-spacing: 0.4px;
        }

        .menu-title {
            text-transform: uppercase;
            font-size: 12px;
            color: #94a2be;
            margin-bottom: 10px;
        }

        .menu-item {
            display: block;
            width: 100%;
            text-align: left;
            text-decoration: none;
            border: 1px solid transparent;
            background: transparent;
            color: #dfe7f7;
            padding: 11px 12px;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 600;
        }

        .menu-item.active {
            background: rgba(31, 111, 235, 0.22);
            border-color: rgba(124, 169, 255, 0.45);
            color: #ffffff;
        }

        .content {
            padding: 30px;
        }

        .page-title {
            margin: 0;
            font-size: 28px;
            font-weight: 700;
        }

        .page-subtitle {
            color: var(--muted);
            margin-top: 6px;
            margin-bottom: 24px;
        }

        .flash {
            background: #e9f8ee;
            border: 1px solid #bfe8ce;
            color: var(--success);
            padding: 12px 14px;
            border-radius: 8px;
            margin-bottom: 18px;
            font-weight: 600;
        }

        .panel {
            background: var(--panel);
            border: 1px solid var(--border);
            border-radius: 14px;
            box-shadow: 0 10px 24px rgba(16, 31, 61, 0.06);
            padding: 20px;
            margin-bottom: 20px;
        }

        .panel h2 {
            margin: 0 0 14px;
            font-size: 20px;
        }

        .inline-form {
            display: grid;
            grid-template-columns: 1fr 160px 1fr 1fr 130px;
            gap: 10px;
            margin-bottom: 16px;
        }

        .inline-form input {
            width: 100%;
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 11px 12px;
            font-size: 14px;
        }

        .inline-form button {
            border: 0;
            border-radius: 10px;
            padding: 11px 12px;
            font-size: 14px;
            font-weight: 700;
            color: #ffffff;
            background: var(--accent);
            cursor: pointer;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            overflow: hidden;
            border-radius: 10px;
            border: 1px solid var(--border);
        }

        th, td {
            text-align: left;
            padding: 11px 12px;
            border-bottom: 1px solid var(--border);
        }

        th {
            background: var(--accent-soft);
            font-weight: 700;
        }

        tr:last-child td {
            border-bottom: none;
        }

        .amount {
            font-weight: 700;
            color: #143b8f;
        }

        .empty {
            text-align: center;
            color: var(--muted);
            font-style: italic;
        }

        @media (max-width: 900px) {
            .layout {
                grid-template-columns: 1fr;
            }

            .sidebar {
                padding-bottom: 8px;
            }

            .content {
                padding: 16px;
            }

            .inline-form {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
<div class="layout">
    <aside class="sidebar">
        <div class="brand">Admin Panel</div>
        <div class="menu-title">Sidebar</div>
        <a class="menu-item active" href="{{ route('admin.products.index') }}">Products</a>
    </aside>

    <main class="content">
        <h1 class="page-title">Products</h1>
        <p class="page-subtitle">First page showing wallet-wise product amounts</p>

        @if (session('success'))
            <div class="flash">{{ session('success') }}</div>
        @endif

        <section class="panel">
            <h2>Repurchase Wallet Amount</h2>
            <form method="POST" action="{{ route('admin.products.repurchase.store') }}" class="inline-form" enctype="multipart/form-data">
                @csrf
                <input type="text" name="product_name" placeholder="Product name" required>
                <input type="number" step="0.01" min="0" name="amount" placeholder="Amount" required>
                <input type="file" name="product_image" accept="image/*">
                <input type="url" name="product_image_url" placeholder="Or paste image URL">
                <button type="submit">Add Product</button>
            </form>

            <table>
                <thead>
                <tr>
                    <th style="width:80px;">#</th>
                    <th>Product</th>
                    <th style="width:180px;">Amount</th>
                    <th style="width:120px;">Photo</th>
                </tr>
                </thead>
                <tbody>
                @forelse($repurchaseProducts as $item)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $item->product_name }}</td>
                        <td class="amount">{{ number_format((float) $item->amount, 2) }}</td>
                        <td>
                            @if($item->image_path)
                                <img src="{{ str_starts_with($item->image_path, 'http') ? $item->image_path : asset('images/ecom/' . $item->image_path) }}" alt="{{ $item->product_name }}" style="width:56px;height:56px;object-fit:cover;border-radius:8px;">
                            @else
                                --
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="empty">No products found in repurchase wallet table.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </section>

        <section class="panel">
            <h2>Consistency Wallet Amount</h2>
            <form method="POST" action="{{ route('admin.products.consistency.store') }}" class="inline-form" enctype="multipart/form-data">
                @csrf
                <input type="text" name="product_name" placeholder="Product name" required>
                <input type="number" step="0.01" min="0" name="amount" placeholder="Amount" required>
                <input type="file" name="product_image" accept="image/*">
                <input type="url" name="product_image_url" placeholder="Or paste image URL">
                <button type="submit">Add Product</button>
            </form>

            <table>
                <thead>
                <tr>
                    <th style="width:80px;">#</th>
                    <th>Product</th>
                    <th style="width:180px;">Amount</th>
                    <th style="width:120px;">Photo</th>
                </tr>
                </thead>
                <tbody>
                @forelse($consistencyProducts as $item)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $item->product_name }}</td>
                        <td class="amount">{{ number_format((float) $item->amount, 2) }}</td>
                        <td>
                            @if($item->image_path)
                                <img src="{{ str_starts_with($item->image_path, 'http') ? $item->image_path : asset('images/ecom/' . $item->image_path) }}" alt="{{ $item->product_name }}" style="width:56px;height:56px;object-fit:cover;border-radius:8px;">
                            @else
                                --
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="empty">No products found in consistency wallet table.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </section>
    </main>
</div>
</body>
</html>
