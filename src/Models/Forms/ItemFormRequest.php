<?php

namespace WalkerChiu\MallCart\Models\Forms;

use Illuminate\Support\Facades\Request;
use WalkerChiu\Core\Models\Forms\FormRequest;

class ItemFormRequest extends FormRequest
{
    /**
     * @Override Illuminate\Foundation\Http\FormRequest::getValidatorInstance
     */
    protected function getValidatorInstance()
    {
        $request = Request::instance();
        $data = $this->all();
        if (
            $request->isMethod('put')
            && empty($data['id'])
            && isset($request->id)
        ) {
            $data['id'] = (string) $request->id;
            $this->getInputSource()->replace($data);
        }

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
            'user_id'    => trans('php-mall-cart::item.user_id'),
            'stock_id'   => trans('php-mall-cart::item.stock_id'),
            'nums'       => trans('php-mall-cart::item.nums'),
            'binding'    => trans('php-mall-cart::item.binding'),
            'options'    => trans('php-mall-cart::item.options')
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return Array
     */
    public function rules()
    {
        $rules = [
            'channel_id' => ['required','integer','min:1','exists:'.config('wk-core.table.mall-cart.channels').',id'],
            'user_id'    => ['required','integer','min:1','exists:'.config('wk-core.table.user').',id'],
            'stock_id'   => ['required','integer','min:1','exists:'.config('wk-core.table.mall-shelf.stocks').',id'],
            'nums'       => 'required|integer',
            'binding'    => 'nullable|json',
            'options'    => 'nullable|json'
        ];

        $request = Request::instance();
        if (
            $request->isMethod('put')
            && isset($request->id)
        ) {
            $rules = array_merge($rules, ['id' => ['required','string','exists:'.config('wk-core.table.mall-cart.items').',id']]);
        }

        return $rules;
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return Array
     */
    public function messages()
    {
        return [
            'id.required'         => trans('php-core::validation.required'),
            'id.string'           => trans('php-core::validation.string'),
            'id.exists'           => trans('php-core::validation.exists'),
            'channel_id.required' => trans('php-core::validation.required'),
            'channel_id.integer'  => trans('php-core::validation.integer'),
            'channel_id.min'      => trans('php-core::validation.min'),
            'channel_id.exists'   => trans('php-core::validation.exists'),
            'user_id.required'    => trans('php-core::validation.required'),
            'user_id.integer'     => trans('php-core::validation.integer'),
            'user_id.min'         => trans('php-core::validation.min'),
            'user_id.exists'      => trans('php-core::validation.exists'),
            'stock_id.required'   => trans('php-core::validation.required'),
            'stock_id.integer'    => trans('php-core::validation.integer'),
            'stock_id.min'        => trans('php-core::validation.min'),
            'stock_id.exists'     => trans('php-core::validation.exists'),
            'nums.required'       => trans('php-core::validation.required'),
            'nums.integer'        => trans('php-core::validation.integer'),
            'binding.json'        => trans('php-core::validation.json'),
            'options.json'        => trans('php-core::validation.json')
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
            $request = Request::instance();

            if (
                config('wk-mall-cart.onoff.mall-shelf')
                && !empty(config('wk-core.class.mall-shelf.stock'))
            ) {
                $map = [];

                if (
                    isset($data['channel_id'])
                    && isset($data['user_id'])
                ) {
                    $records = config('wk-core.class.mall-cart.item')
                                    ::where('channel_id', $data['channel_id'])
                                    ->where('user_id', $data['user_id'])
                                    ->select('stock_id', 'nums', 'binding')
                                    ->get();
                    foreach ($records as $record) {
                        if (!isset($map[$record->stock_id])) {
                            $map[$record->stock_id] = $record->nums;
                        } else {
                            $map[$record->stock_id] += $record->nums;
                        }

                        if (empty($record->binding))
                            continue;
                        foreach ($record->binding as $binding) {
                            if (!isset($map[$binding['stock_id']])) {
                                $map[$binding['stock_id']] = $binding['nums'];
                            } else {
                                $map[$binding['stock_id']] += $binding['nums'];
                            }
                        }
                    }
                }

                if (
                    isset($data['stock_id'])
                    && isset($data['nums'])
                ) {
                    $stock = config('wk-core.class.mall-shelf.stock')
                                ::where('is_sellable', 1)
                                ->where('is_enabled', 1)
                                ->where('id', $data['stock_id'])
                                ->first();
                    if ($stock) {
                        if (!is_null($stock->quantity)) {
                            $item = config('wk-core.class.mall-cart.item')::find($request->id);
                            if (
                                $request->isMethod('put')
                                && isset($request->id)
                            ) {
                                if ($item) {
                                    if ($stock->quantity < ($item->nums + $data['nums']))
                                        $validator->errors()->add('nums', trans('php-mall-cart::validation.insufficient_available_inventory'));
                                    elseif ($stock->quantity - ($item->nums + $data['nums']) < 1)
                                        $validator->errors()->add('nums', trans('php-mall-cart::validation.insufficient_available_inventory'));
                                } else {
                                    $validator->errors()->add('id', trans('php-core::validation.exists'));
                                }
                            } else {
                                if ($stock->quantity < $data['nums'])
                                    $validator->errors()->add('nums', trans('php-mall-cart::validation.insufficient_available_inventory'));
                                elseif ($stock->quantity - $data['nums'] < 0)
                                    $validator->errors()->add('nums', trans('php-mall-cart::validation.insufficient_available_inventory'));
                            }
                        }

                        if (!isset($map[$data['stock_id']])) {
                            $map[$data['stock_id']] = $data['nums'];
                        } else {
                            $map[$data['stock_id']] += $data['nums'];
                        }
                    } else {
                        $validator->errors()->add('stock_id', trans('php-core::validation.exists'));
                    }

                    if (
                        isset($data['binding'])
                        && is_iterable($data['binding'])
                    ) {
                        foreach ($data['binding'] as $binding) {
                            $binding = is_string($binding) ? json_decode($binding) : $binding;
                            $stock2 = config('wk-core.class.mall-shelf.stock')
                                        ::where('is_sellable', 1)
                                        ->where('is_enabled', 1)
                                        ->where('id', $binding->stock_id)
                                        ->first();
                            if ($stock2->id == $stock->id)
                                $validator->errors()->add('binding', trans('php-mall-cart::validation.insufficient_available_inventory'));
                            if ($stock2) {
                                if (!is_null($stock2->quantity)) {
                                    if (!property_exists($binding, 'stock_id'))
                                        $validator->errors()->add('binding', trans('php-mall-cart::validation.insufficient_available_inventory'));
                                    if (!property_exists($binding, 'nums'))
                                        $validator->errors()->add('binding', trans('php-mall-cart::validation.insufficient_available_inventory'));

                                    if ($stock2->quantity < $binding->nums)
                                        $validator->errors()->add('binding', trans('validation.max'));
                                    elseif ($stock2->quantity - $binding->nums < 0)
                                        $validator->errors()->add('binding', trans('validation.min'));

                                    if (!isset($map[$binding->stock_id])) {
                                        $map[$binding->stock_id] = $binding->nums;
                                    } else {
                                        $map[$binding->stock_id] += $binding->nums;
                                    }
                                }
                            } else {
                                $validator->errors()->add('binding', trans('validation.min'));
                            }
                        }
                    }

                    foreach ($map as $key => $value) {
                        $result = config('wk-core.class.mall-shelf.stock')
                                    ::where('is_sellable', 1)
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
