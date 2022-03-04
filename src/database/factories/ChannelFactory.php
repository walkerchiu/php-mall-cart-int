<?php

/** @var \Illuminate\Database\Eloquent\Factory  $factory */

use Faker\Generator as Faker;
use WalkerChiu\MallCart\Models\Entities\Channel;
use WalkerChiu\MallCart\Models\Entities\ChannelLang;

$factory->define(Channel::class, function (Faker $faker) {
    return [
        'serial'     => $faker->isbn10,
        'identifier' => $faker->slug,
        'order'      => $faker->randomNumber
    ];
});

$factory->define(ChannelLang::class, function (Faker $faker) {
    return [
        'code'  => $faker->locale,
        'key'   => $faker->randomElement(['name', 'description']),
        'value' => $faker->sentence
    ];
});
