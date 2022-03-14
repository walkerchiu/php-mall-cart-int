<?php

namespace WalkerChiu\MallCart\Models\Entities;

use WalkerChiu\Core\Models\Entities\Lang;

class ChannelLang extends Lang
{
    /**
     * Create a new instance.
     *
     * @param Array  $attributes
     * @return void
     */
    public function __construct(array $attributes = [])
    {
        $this->table = config('wk-core.table.mall-cart.channels_lang');

        parent::__construct($attributes);
    }
}
