<?php
$userFullName = ($user['name']?? ''). ' '. ($user['surname']?? '');
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Created</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }

        .user-table {
            width: 100%;
            border-collapse: collapse;
            margin: 32px 0;
        }

        .user-table th, .user-table td {
            border: 1px solid #ddd;
            padding: 8px;
        }

        .user-table th {
            padding-top: 12px;
            padding-bottom: 12px;
            text-align: left;
            background-color: #EE204D;
            color: white;
        }

        .product-list {
            width: 100%;
            border-collapse: collapse;
            margin: 32px 0;
        }

        .product-list th, .product-list td {
            border: 1px solid #ddd;
            padding: 8px;
        }

        .product-list th {
            padding-top: 12px;
            padding-bottom: 12px;
            text-align: left;
            background-color: #4CAF50;
            color: white;
        }
    </style>
</head>

<body>
    <h2>Order Created</h2>
    <p>Customer {{ $userFullName }} placed order</p>

    <table class="user-table">
        <caption>User Data</caption>
        <thead>
        <tr>
            <th>Name</th>
            <th>Country</th>
            <th>City</th>
            <th>address</th>
            <th>email</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td>{{ $userFullName }}
            </td>
            <td>{{ $user['country'] }}</td>
            <td>{{ $user['city'] }}</td>
            <td>{{ $user['address'] }}</td>
            <td>{{ $user['email'] }}</td>
        </tr>
        </tbody>
    </table>
    <table class="product-list">
        <caption>User Products</caption>
        <thead>
        <tr>
            <th>Product Name</th>
            <th>Price</th>
            <th>Quantity</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($products as $product)
        <tr>
            <td>{{ $product['product']->name }}</td>
            <td>{{ formatted_product_price($product)['currency'] }}</td>
            <td>{{ $product['qty'] }}</td>
        </tr>
        @endforeach
        </tbody>
    </table>
    <p>Total Price: {{ total_order_amount($products) }}</p>
</body>
</html>
