<?php

/** @var \Illuminate\Database\Eloquent\Factory  $factory */

use Faker\Generator as Faker;
use WalkerChiu\MallCart\Models\Entities\Item;

$factory->define(Item::class, function (Faker $faker) {
    return [
        'channel_id' => 1,
        'user_id'    => 1,
        'stock_id'   => 1,
        'nums'       => $faker->randomDigitNotNull
    ];
});
