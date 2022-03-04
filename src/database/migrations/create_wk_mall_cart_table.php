<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateWkMallCartTable extends Migration
{
    public function up()
    {
        Schema::create(config('wk-core.table.mall-cart.channels'), function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->nullableUuidMorphs('host');
            $table->string('serial')->nullable();
            $table->string('identifier');
            $table->unsignedBigInteger('order')->nullable();
            $table->boolean('is_enabled')->default(0);

            $table->timestampsTz();
            $table->softDeletes();

            $table->index('serial');
            $table->index('identifier');
            $table->index(['host_type', 'host_id', 'is_enabled']);
        });
        if (!config('wk-mall-cart.onoff.core-lang_core')) {
            Schema::create(config('wk-core.table.mall-cart.channels_lang'), function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->morphs('morph');
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('code');
                $table->string('key');
                $table->text('value')->nullable();
                $table->boolean('is_current')->default(1);

                $table->timestampsTz();
                $table->softDeletes();

                $table->foreign('user_id')->references('id')
                    ->on(config('wk-core.table.user'))
                    ->onDelete('set null')
                    ->onUpdate('cascade');
            });
        }

        Schema::create(config('wk-core.table.mall-cart.items'), function (Blueprint $table) {
            $table->uuid('id');
            $table->unsignedBigInteger('channel_id')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('stock_id');
            $table->unsignedInteger('nums');
            $table->json('binding')->nullable();
            $table->json('options')->nullable();

            $table->timestampsTz();

            $table->foreign('user_id')->references('id')
                  ->on(config('wk-core.table.user'))
                  ->onDelete('cascade')
                  ->onUpdate('cascade');

            $table->foreign('channel_id')->references('id')
                  ->on(config('wk-core.table.mall-cart.channels'))
                  ->onDelete('cascade')
                  ->onUpdate('cascade');

            $table->primary('id');
        });
        if (
            config('wk-mall-cart.onoff.mall-shelf')
            && Schema::hasTable(config('wk-core.table.mall-shelf.stocks'))
        ) {
            Schema::table(config('wk-core.table.mall-cart.items'), function (Blueprint $table) {
                $table->foreign('stock_id')->references('id')
                      ->on(config('wk-core.table.mall-shelf.stocks'))
                      ->onDelete('cascade')
                      ->onUpdate('cascade');
            });
        }
    }

    public function down() {
        Schema::dropIfExists(config('wk-core.table.mall-cart.items'));
        Schema::dropIfExists(config('wk-core.table.mall-cart.channels_lang'));
        Schema::dropIfExists(config('wk-core.table.mall-cart.channels'));
    }
}
