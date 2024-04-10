<?php

function formatted_product_price(array $product): array
{
    $formatter = NumberFormatter::create('en_US', NumberFormatter::CURRENCY);
    $baseProductPrice = [
        'price' => $product['product']->price * $product['qty'],
        'currency' => $formatter->formatCurrency($product['product']->price  * $product['qty'], 'USD'),
    ];

    if (!$product['selectedVariant']) {
        return $baseProductPrice;
    }

    $variantCandidate = [];
    foreach ($product['product']->configurable->variants as $variant)
    {
        if (isset($variant->product->id) && $variant->product->id === $product['selectedVariant'])
        {
            $variantCandidate = $variant;
            break;
        }
    }

    if (!$variantCandidate) {
        return $baseProductPrice;
    }

    return [
        'price' => $variantCandidate->product->price * $product['qty'],
        'currency' => $formatter->formatCurrency($variantCandidate->product->price * $product['qty'], 'USD')
    ];
}

function total_order_amount(array $products)
{
    $formatter = NumberFormatter::create('en_US', NumberFormatter::CURRENCY);
    $total = 0;

    foreach ($products as $product)
    {
        $productPrice = formatted_product_price($product);
        $total += $productPrice['price'];
    }
    $total = $formatter->formatCurrency($total, 'USD');

    return $total;
}
