<!doctype html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Order Confirmation</title>
        <style>
            body {
                font-family: Arial, sans-serif;
            }
            .product-list {
                width: 100%;
                border-collapse: collapse;
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
        <h2>Order Confirmation</h2>
        <p>Dear Customer,</p>
        <p>Thank you for your order. Here are the details of your order:</p>
        <table class="product-list">
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
        <p>Thank you for shopping with us!</p>
    </body>
</html>
