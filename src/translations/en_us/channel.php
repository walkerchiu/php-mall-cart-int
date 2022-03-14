<?php

return [

    /*
    |--------------------------------------------------------------------------
    | MallCart: Channel
    |--------------------------------------------------------------------------
    |
    */

    'host_type'   => 'Host Type',
    'host_id'     => 'Host ID',
    'serial'      => 'Serial',
    'identifier'  => 'Identifier',
    'order'       => 'Order',
    'is_enabled'  => 'Is Enabled',
    'name'        => 'Name',
    'description' => 'Description',

    'cart' => [
        'name'           => 'Shopping Cart',
        'checkout'       => 'Checkout',
        'clear'          => 'Clear Cart',
        'delete'         => 'Delete this item',
        'emptied'        => 'The cart has been emptied.',
        'update'         => 'Update Cart',
        'updated'        => 'The cart has been updated.',
        'view'           => 'View Cart',
        'added'          => 'Added to cart',
        'add_notEnough'  => 'Not enough quantity to add to cart',
        'add_fail'       => 'Can\'t add to cart',
        'add_fail_max'   => 'The maximum quantity that can be purchased has been reached.',
        'add_fail_login' => 'The shopping cart is for members only, please log in first.'
    ],



    'product' => [
        'stock'       => 'Product',
        'sku'         => 'SKU',
        'name'        => 'Name',
        'abstract'    => 'abstract',
        'description' => 'description',
        'price'       => 'Price',
        'cost'        => 'Cost',
        'nums'        => 'Nums',
        'total'       => 'Total'
    ],

    'coupon' => [
        'name'        => 'Coupon',
        'placeholder' => 'Coupon Code',
        'tip'         => 'If you have a coupon, please apply it.',
        'apply'       => 'Apply'
    ],
    'point' => [
        'name'     => 'Reward Point',
        'tip'      => 'You can use your points to offset the cost.',
        'nums_now' => 'You have',
        'nums_use' => 'Want to use',
        'nums_get' => 'You will get',
        'apply'    => 'Apply'
    ],

    'expense_list' => 'Expense List',
    'subtotal' => 'Subtotal',
    'fee'      => 'Fee',
    'tax'      => 'Tax',
    'tip'      => 'Tip',
    'discount' => [
        'name'     => 'Discount',
        'coupon'   => 'Coupon Discount',
        'point'    => 'Point Discount',
        'shipment' => 'Shipment Discount',
        'custom'   => 'Custom Discount',
        'total'    => 'Total'
    ],
    'grandtotal' => 'Grand Total',
    'checkout'  => [
        'header'       => 'Checkout',
        'basic'        => 'Order Detail',
        'additional'   => 'Additional Information',
        'confirmation' => 'Order Confirmation',
        'warning'      => 'Your personal data will be used to process the order, your experience throughout this linksite, and for other purposes described in our privacy policy.',
        'agree'        => 'I have read and agree to the store terms and conditions.',
        'order'        => 'Place Order'
    ],
    'payment' => [
        'name'             => 'Payment Method',
        'tip'              => 'Please choose your preferred method.',
        'bill_to_other'    => 'Billing to mobile phones and landlines',
        'cash_on_delivery' => 'Cash on Delivery',
        'credit_card'      => 'Credit Card',
        'debit_card'       => 'Debit Card',
        'e_money'          => 'Electronic Money',
        'free'             => 'Free',
        'gift_card'        => 'Gift Card',
        'point'            => 'Point'
    ],
    'shipment' => [
        'name'             => 'Shipment Method',
        'tip'              => 'Please choose your preferred method.',
        'direct_shipping'  => 'Direct Shipping',
        'drop_shipping'    => 'Drop Shipping',
        'in_site_pickup'  => 'In-Store Pickup',
        'pickup_in_person' => 'Pick up in Person',
        'ship_to'          => 'Ship To',
        'send_to'          => 'Send To',
        'bill_to'          => 'Bill To',
        'invoice_to'       => 'Invoice To'
    ],
    'free_return_within' => 'Free return within :num days',
    'can_return_within'  => 'Can be returned within :num days'
];
