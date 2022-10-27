<?php

namespace WalkerChiu\MallCart\Models\Forms;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use WalkerChiu\Core\Models\Forms\FormRequest;

class ChannelFormRequest extends FormRequest
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
            $data['id'] = (int) $request->id;
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
            'host_type'   => trans('php-mall-cart::channel.host_type'),
            'host_id'     => trans('php-mall-cart::channel.host_id'),
            'serial'      => trans('php-mall-cart::channel.serial'),
            'identifier'  => trans('php-mall-cart::channel.identifier'),
            'order'       => trans('php-mall-cart::channel.order'),
            'is_enabled'  => trans('php-mall-cart::channel.is_enabled'),
            'name'        => trans('php-mall-cart::channel.name'),
            'description' => trans('php-mall-cart::channel.description')
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
            'host_type'   => 'required_with:host_id|string',
            'host_id'     => 'required_with:host_type|integer|min:1',
            'serial'      => '',
            'identifier'  => 'required|string|max:255',
            'order'       => 'nullable|numeric|min:0',
            'is_enabled'  => 'boolean',

            'name'        => 'required|string|max:255',
            'description' => ''
        ];

        $request = Request::instance();
        if (
            $request->isMethod('put')
            && isset($request->id)
        ) {
            $rules = array_merge($rules, ['id' => ['required','integer','min:1','exists:'.config('wk-core.table.mall-cart.channels').',id']]);
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
            'id.required'             => trans('php-core::validation.required'),
            'id.integer'              => trans('php-core::validation.integer'),
            'id.min'                  => trans('php-core::validation.min'),
            'id.exists'               => trans('php-core::validation.exists'),
            'host_type.required_with' => trans('php-core::validation.required_with'),
            'host_type.string'        => trans('php-core::validation.string'),
            'host_id.required_with'   => trans('php-core::validation.required_with'),
            'host_id.integer'         => trans('php-core::validation.integer'),
            'host_id.min'             => trans('php-core::validation.min'),
            'identifier.required'     => trans('php-core::validation.required'),
            'identifier.max'          => trans('php-core::validation.max'),
            'order.numeric'           => trans('php-core::validation.numeric'),
            'order.min'               => trans('php-core::validation.min'),
            'is_enabled.boolean'      => trans('php-core::validation.boolean'),

            'name.required'           => trans('php-core::validation.required'),
            'name.max'                => trans('php-core::validation.max')
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
        if ( !empty(config('wk-core.class.site-mall.site')) ) {
            $validator->after( function ($validator) {
                $data = $validator->getData();
                if (
                    isset($data['host_type'])
                    && isset($data['host_id'])
                ) {
                    if (
                        config('wk-mall-cart.onoff.site-mall')
                        && !empty(config('wk-core.class.site-mall.site'))
                        && $data['host_type'] == config('wk-core.class.site-mall.site')
                    ) {
                        $result = DB::table(config('wk-core.table.site-mall.sites'))
                                    ->where('id', $data['host_id'])
                                    ->exists();
                        if (!$result)
                            $validator->errors()->add('host_id', trans('php-core::validation.exists'));

                    }
                }
                if (isset($data['identifier'])) {
                    $result = config('wk-core.class.mall-cart.channel')::where('identifier', $data['identifier'])
                                    ->when(isset($data['host_type']), function ($query) use ($data) {
                                        return $query->where('host_type', $data['host_type']);
                                      })
                                    ->when(isset($data['host_id']), function ($query) use ($data) {
                                        return $query->where('host_id', $data['host_id']);
                                      })
                                    ->when(isset($data['id']), function ($query) use ($data) {
                                        return $query->where('id', '<>', $data['id']);
                                      })
                                    ->exists();
                    if ($result)
                        $validator->errors()->add('identifier', trans('php-core::validation.unique', ['attribute' => trans('php-mall-cart::channel.identifier')]));
                }
            });
        }
    }
}
