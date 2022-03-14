<?php

return [

    /*
    |--------------------------------------------------------------------------
    | MallCart: Channel
    |--------------------------------------------------------------------------
    |
    */

    'host_type'   => '母體種類',
    'host_id'     => '母體 ID',
    'serial'      => '編號',
    'identifier'  => '識別符',
    'order'       => '排序',
    'is_enabled'  => '是否啟用',
    'name'        => '名稱',
    'description' => '描述',

    'cart' => [
        'name'           => '購物車',
        'checkout'       => '結帳',
        'clear'          => '清空購物車',
        'delete'         => '移出購物車',
        'emptied'        => '已清空購物車',
        'update'         => '更新購物車',
        'updated'        => '已更新購物車',
        'view'           => '查看購物車',
        'added'          => '已放入購物車',
        'add_notEnough'  => '數量不夠放入購物車',
        'add_fail'       => '無法放入購物車',
        'add_fail_max'   => '已達可被購買的最大數量',
        'add_fail_login' => '購物車僅限會員使用，請先登入'
    ],



    'product' => [
        'stock'       => '商品',
        'sku'         => 'SKU',
        'name'        => '商品名稱',
        'abstract'    => '摘要',
        'description' => '描述',
        'price'       => '價格',
        'cost'        => '花費',
        'nums'        => '數量',
        'total'       => '小計'
    ],

    'coupon' => [
        'name'        => '優惠券',
        'placeholder' => '優惠券代碼',
        'tip'         => '如果你有優惠券的話，可以在這裡輸入',
        'apply'       => '確認'
    ],
    'point' => [
        'name'     => '獎勵點數',
        'tip'      => '請輸入欲折抵的點數',
        'nums_now' => '擁有點數',
        'nums_use' => '欲使用點數',
        'nums_get' => '將獲得點數',
        'apply'    => '確認'
    ],

    'expense_list' => '購物車結算',
    'subtotal' => '小計',
    'fee'      => '手續費',
    'tax'      => '稅金',
    'tip'      => '小費',
    'discount' => [
        'name'     => '折扣資料',
        'coupon'   => '優惠券折抵',
        'point'    => '點數折抵',
        'shipment' => '運送優惠折抵',
        'custom'   => '其它優惠折抵',
        'total'    => '共折抵'
    ],
    'grandtotal' => '總計花費',
    'checkout'  => [
        'header'       => '結帳資訊',
        'basic'        => '訂單內容',
        'additional'   => '額外資訊',
        'confirmation' => '訂單確認',
        'warning'      => '您的資料將被用來處理這份訂單、改善您的使用體驗與其他規範在本站隱私政策裡的事項。',
        'agree'        => '我已閱讀並同意本站的隱私政策與使用規定。',
        'order'        => '送出訂單'
    ],
    'payment' => [
        'name'             => '付款方式',
        'tip'              => '請選擇您想要的付款方式。',
        'bill_to_other'    => '帳單付款',
        'cash_on_delivery' => '貨到付款',
        'credit_card'      => '信用卡',
        'debit_card'       => '金融卡',
        'e_money'          => '電子貨幣',
        'free'             => '免費',
        'gift_card'        => '禮物卡',
        'point'            => '點數支付'
    ],
    'shipment' => [
        'name'             => '運送方式',
        'tip'              => '請選擇您想要的運送方式。',
        'direct_shipping'  => '直接寄送',
        'drop_shipping'    => '第三方運送',
        'in_site_pickup'  => '到店取貨',
        'pickup_in_person' => '親自取貨',
        'ship_to'          => '運送至',
        'send_to'          => '寄送至',
        'bill_to'          => '帳單送至',
        'invoice_to'       => '發票送至'
    ],
    'free_return_within' => '鑑賞期 :num 天',
    'can_return_within'  => ':num 天內可退貨'
];
