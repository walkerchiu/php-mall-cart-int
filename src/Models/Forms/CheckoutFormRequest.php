<?php

namespace WalkerChiu\MallCart\Models\Forms;

use WalkerChiu\Core\Models\Forms\FormRequest;

class CheckoutFormRequest extends FormRequest
{
    /**
     * @Override Illuminate\Foundation\Http\FormRequest::getValidatorInstance
     */
    protected function getValidatorInstance()
    {
        return parent::getValidatorInstance();
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return Array
     */
    public function attributes()
    {
        return [
            'channel_id' => trans('php-mall-cart::item.channel_id'),
            'stock_id[]' => trans('php-mall-cart::item.stock_id'),
            'nums[]'     => trans('php-mall-cart::item.nums'),
            'binding[]'  => trans('php-mall-cart::item.binding'),
            'options[]'  => trans('php-mall-cart::item.options'),

            'coupon' => trans('php-mall-cart::channel.coupon.name'),
            'point'  => trans('php-mall-cart::channel.point.name')
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return Array
     */
    public function rules()
    {
        return [
            'channel_id' => ['required','integer','min:1','exists:'.config('wk-core.table.mall-cart.channels').',id'],
            'stock_id'   => 'required|array',
            'stock_id.*' => ['required','integer','min:1','exists:'.config('wk-core.table.mall-shelf.stocks').',id'],
            'nums'       => 'required|array',
            'nums.*'     => 'required|numeric|min:1',
            'binding'    => 'nullable|array',
            'options'    => 'nullable|array',

            'coupon' => 'nullable|string',
            'point'  => 'nullable|numeric|min:0|not_in:0'
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return Array
     */
    public function messages()
    {
        return [
            'channel_id.required' => trans('php-core::validation.required'),
            'channel_id.integer'  => trans('php-core::validation.integer'),
            'channel_id.min'      => trans('php-core::validation.min'),
            'channel_id.exists'   => trans('php-core::validation.exists'),
            'stock_id.required'   => trans('php-core::validation.required'),
            'stock_id.array'      => trans('php-core::validation.array'),
            'stock_id.*.required' => trans('php-core::validation.required'),
            'stock_id.*.integer'  => trans('php-core::validation.integer'),
            'stock_id.*.min'      => trans('php-core::validation.min'),
            'stock_id.*.exists'   => trans('php-core::validation.exists'),
            'nums.required'       => trans('php-core::validation.required'),
            'nums.array'          => trans('php-core::validation.array'),
            'nums.*.required'     => trans('php-core::validation.required'),
            'nums.*.numeric'      => trans('php-core::validation.numeric'),
            'nums.*.min'          => trans('php-core::validation.min'),
            'binding.array'       => trans('php-core::validation.array'),
            'options.array'       => trans('php-core::validation.array'),

            'coupon.string' => trans('php-core::validation.string'),
            'point.numeric' => trans('php-core::validation.numeric'),
            'point.min'     => trans('php-core::validation.min'),
            'point.not_in'  => trans('php-core::validation.not_in')
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after( function ($validator) {
            $data = $validator->getData();

            if (
                config('wk-mall-cart.onoff.mall-shelf')
                && !empty(config('wk-core.class.mall-shelf.stock'))
            ) {
                if (
                    isset($data['stock_id'])
                    && isset($data['nums'])
                    && is_array($data['stock_id'])
                    && is_array($data['nums'])
                ) {
                    $map = [];

                    if (count($data['stock_id']) != count($data['nums'])) {
                        $validator->errors()->add('stock_id[]', trans('php-core::validation.not_same_length', [
                            'attribute' => trans('php-mall-cart::item.stock_id'),
                            'nums'      => trans('php-mall-cart::item.nums')
                        ]));
                    } else {
                        foreach ($data['stock_id'] as $key => $item) {
                            $stock = config('wk-core.class.mall-shelf.stock')
                                        ::where('is_sellable', 1)
                                        ->where('is_enabled', 1)
                                        ->where('id', $data['stock_id'][$key])
                                        ->first();
                            if (!$stock)
                                $validator->errors()->add('stock_id[]', trans('php-core::validation.exists'));

                            if (!is_null($stock->quantity)) {
                                if ($stock->quantity < $data['nums'][$key])
                                    $validator->errors()->add('nums[]', trans('php-core::validation.max'));
                            }

                            if (!isset($map[$key])) {
                                $map = array_merge($map, [$key => $data['nums'][$key]]);
                            } else {
                                $map[$key] += $data['nums'][$key];
                            }
                        }
                    }

                    if (
                        isset($data['binding'])
                        && is_array($data['binding'])
                    ) {
                        foreach ($data['binding'] as $binding_array) {
                            if (
                                empty($binding_array)
                                || !is_iterable($binding_array)
                            )
                                continue;
                            foreach ($binding_array as $binding) {
                                $binding = is_string($binding) ? json_decode($binding) : $binding;

                                if (!property_exists($binding, 'stock_id'))
                                    $validator->errors()->add('binding[]', trans('php-mall-cart::validation.insufficient_available_inventory'));
                                if (!property_exists($binding, 'nums'))
                                    $validator->errors()->add('binding[]', trans('php-mall-cart::validation.insufficient_available_inventory'));

                                if (!isset($map[$binding->stock_id])) {
                                    $map = array_merge($map, [$binding->stock_id => $binding->nums]);
                                } else {
                                    $map[$binding->stock_id] += $binding->nums;
                                }
                            }
                        }
                    }

                    foreach ($map as $key => $value) {
                        $result = config('wk-core.class.mall-shelf.stock')
                                    ->where('is_sellable', 1)
                                    ->where('is_enabled', 1)
                                    ->where('id', $key)
                                    ->where( function ($query) use ($value) {
                                        return $query->whereNull('qty_per_order')
                                                     ->orWhere('qty_per_order', '>=', $value);
                                    })
                                    ->exists();
                        if (!$result)
                            $validator->errors()->add('stock_id[]', trans('php-mall-cart::validation.insufficient_available_inventory'));
                    }
                }
            }
        });
    }
}
