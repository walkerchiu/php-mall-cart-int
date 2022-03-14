<?php

namespace WalkerChiu\MallCart\Models\Observers;

class ItemObserver
{
    /**
     * Handle the model "retrieved" event.
     *
     * @param Model  $model
     * @return void
     */
    public function retrieved($model)
    {
        //
    }

    /**
     * Handle the model "creating" event.
     *
     * @param Model  $model
     * @return void
     */
    public function creating($model)
    {
        //
    }

    /**
     * Handle the model "created" event.
     *
     * @param Model  $model
     * @return void
     */
    public function created($model)
    {
        config('wk-core.class.mall-cart.item')
            ::where('channel_id', $model->channel_id)
            ->where('user_id', $model->user_id)
            ->where('stock_id', $model->stock_id)
            ->where('id', '<>', $model->id)
            ->delete();
    }

    /**
     * Handle the model "updating" event.
     *
     * @param Model  $model
     * @return void
     */
    public function updating($model)
    {
        //
    }

    /**
     * Handle the model "updated" event.
     *
     * @param Model  $model
     * @return void
     */
    public function updated($model)
    {
        //
    }

    /**
     * Handle the model "saving" event.
     *
     * @param Model  $model
     * @return void
     */
    public function saving($model)
    {
        if (
            config('wk-mall-cart.onoff.mall-shelf')
            && !empty(config('wk-core.class.mall-shelf.stock'))
        ) {
            $stock = config('wk-core.class.mall-shelf.stock')::find($model->stock_id);
            if (
                empty($stock)
                || !$stock->is_sellable
                || !$stock->is_enabled
            ) {
                return false;
            }
        }
    }

    /**
     * Handle the model "saved" event.
     *
     * @param Model  $model
     * @return void
     */
    public function saved($model)
    {
        //
    }

    /**
     * Handle the model "deleting" event.
     *
     * @param Model  $model
     * @return void
     */
    public function deleting($model)
    {
        //
    }

    /**
     * Handle the model "deleted" event.
     *
     * @param Model  $model
     * @return void
     */
    public function deleted($model)
    {
        //
    }

    /**
     * Handle the model "restoring" event.
     *
     * @param Model  $model
     * @return void
     */
    public function restoring($model)
    {
        //
    }

    /**
     * Handle the model "restored" event.
     *
     * @param Model  $model
     * @return void
     */
    public function restored($model)
    {
        //
    }
}
