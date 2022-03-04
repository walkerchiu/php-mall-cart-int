<?php

namespace WalkerChiu\MallCart\Models\Repositories;

use Illuminate\Support\Facades\App;
use WalkerChiu\Core\Models\Exceptions\NotExpectedmodelException;
use WalkerChiu\Core\Models\Exceptions\NotFoundModelException;
use WalkerChiu\Core\Models\Forms\FormTrait;
use WalkerChiu\Core\Models\Repositories\Repository;
use WalkerChiu\Core\Models\Repositories\RepositoryTrait;
use WalkerChiu\Core\Models\Services\PackagingFactory;
use WalkerChiu\MorphImage\Models\Repositories\ImageRepositoryTrait;
use WalkerChiu\MallShelf\Models\Services\StockService;

class ItemRepository extends Repository
{
    use FormTrait;
    use RepositoryTrait;
    use ImageRepositoryTrait;

    protected $instance;



    /**
     * Create a new repository instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->instance = App::make(config('wk-core.class.mall-cart.item'));
    }

    /**
     * @param String  $code
     * @param Array   $data
     * @param Bool    $auto_packing
     * @return Array|Collection|Eloquent
     */
    public function list(string $code, array $data, $auto_packing = false)
    {
        $instance = $this->instance;

        $data = array_map('trim', $data);
        $repository = $instance->when($data, function ($query, $data) {
                                    return $query->unless(empty($data['id']), function ($query) use ($data) {
                                                return $query->where('id', $data['id']);
                                            })
                                            ->unless(empty($data['channel_id']), function ($query) use ($data) {
                                                return $query->where('channel_id', $data['channel_id']);
                                            })
                                            ->unless(empty($data['user_id']), function ($query) use ($data) {
                                                return $query->where('user_id', $data['user_id']);
                                            })
                                            ->unless(empty($data['stock_id']), function ($query) use ($data) {
                                                return $query->where('stock_id', $data['stock_id']);
                                            })
                                            ->unless(empty($data['nums']), function ($query) use ($data) {
                                                return $query->where('nums', $data['nums']);
                                            });
                                })
                                ->orderBy('updated_at', 'DESC');

        if ($auto_packing) {
            $factory = new PackagingFactory(config('wk-mall-cart.output_format'), config('wk-mall-cart.pagination.pageName'), config('wk-mall-cart.pagination.perPage'));

            if (in_array(config('wk-mall-cart.output_format'), ['array', 'array_pagination'])) {
                switch (config('wk-mall-cart.output_format')) {
                    case "array":
                        $entities = $factory->toCollection($repository);
                        // no break
                    case "array_pagination":
                        $entities = $factory->toCollectionWithPagination($repository);
                        // no break
                    default:
                        $output = [];
                        foreach ($entities as $instance) {
                            array_push($output, $this->show($instance, $code));
                        }
                }
                return $output;
            } else {
                return $factory->output($repository);
            }
        }

        return $repository;
    }

    /**
     * @param Int           $id
     * @param Array|String  $code
     * @param Int           $user_id
     * @return Array
     */
    public function showByChannelId(int $id, $code, $user_id = null): array
    {
        $list = [];
        if (is_array($code)) {
            foreach ($code as $language) {
                $list = array_merge($list, [$language => []]);
            }
        }

        $items = $this->instance->with('stock', 'stock.product', 'stock.catalog')
                                ->where('channel_id', $id)
                                ->when($user_id, function ($query, $user_id) {
                                        return $query->where('user_id', $user_id);
                                    })
                                ->get();
        foreach ($items as $item) {
            if (
                config('wk-mall-cart.onoff.mall-shelf')
                && !empty(config('wk-core.class.mall-shelf.stock'))
            ) {
                $stock = $item->stock;
                $product = $stock->product;
                $catalog = $stock->catalog;

                if (is_string($code)) {
                    array_push($list, [
                        'id'         => $item->id,
                        'nums'       => $item->nums,
                        'binding'    => $item->binding,
                        'options'    => $item->options,
                        'created_at' => $item->created_at,
                        'updated_at' => $item->updated_at,
                        'stock' => [
                            'id'             => $stock->id,
                            'name'           => $stock->findLang($code, 'name'),
                            'abstract'       => $stock->findLang($code, 'abstract'),
                            'description'    => $stock->findLang($code, 'description'),
                            'keywords'       => $stock->findLang($code, 'keywords'),
                            'sku'            => $stock->sku,
                            'identifier'     => $stock->identifier,
                            'price_original' => $stock->price_original,
                            'price_discount' => $stock->price_discount,
                            'options'        => $stock->options,
                            'inventory'      => $stock->inventory,
                            'quantity'       => $stock->quantity,
                            'fee'            => $stock->fee,
                            'tax'            => $stock->tax,
                            'tip'            => $stock->tip,
                            'is_new'         => $stock->is_new,
                            'is_featured'    => $stock->is_featured,
                            'is_highlighted' => $stock->is_highlighted,
                            'updated_at'     => $stock->updated_at,
                            'covers'         => $this->getEnabledCover($code, $stock)
                        ],
                        'product' => [
                            'id'          => empty($product) ? '' : $product->id,
                            'serial'      => empty($product) ? '' : $product->serial,
                            'name'        => empty($product) ? '' : $product->findLang($code, 'name'),
                            'abstract'    => empty($product) ? '' : $product->findLang($code, 'abstract'),
                            'description' => empty($product) ? '' : $product->findLang($code, 'description'),
                            'covers'      => empty($product) ? '' : $this->getEnabledCover($code, $product)
                        ],
                        'catalog' => [
                            'id'          => empty($catalog) ? '' : $catalog->id,
                            'serial'      => empty($catalog) ? '' : $catalog->serial,
                            'name'        => empty($catalog) ? '' : $catalog->findLang($code, 'name'),
                            'description' => empty($catalog) ? '' : $catalog->findLang($code, 'description'),
                            'color'       => empty($catalog) ? '' : $catalog->color,
                            'size'        => empty($catalog) ? '' : $catalog->size,
                            'material'    => empty($catalog) ? '' : $catalog->material,
                            'taste'       => empty($catalog) ? '' : $catalog->taste,
                            'weight'      => empty($catalog) ? '' : $catalog->weight,
                            'length'      => empty($catalog) ? '' : $catalog->length,
                            'width'       => empty($catalog) ? '' : $catalog->width,
                            'height'      => empty($catalog) ? '' : $catalog->height,
                            'covers'      => empty($catalog) ? [] : $this->getEnabledCover($code, $catalog)
                        ]
                        ]);
                } else {
                    foreach ($code as $language) {
                        array_push($list[$language], [
                           'id'         => $item->id,
                           'nums'       => $item->nums,
                           'binding'    => $item->binding,
                           'options'    => $item->options,
                           'created_at' => $item->created_at,
                           'updated_at' => $item->updated_at,
                           'stock' => [
                                'id'             => $stock->id,
                                'name'           => $stock->findLang($language, 'name'),
                                'abstract'       => $stock->findLang($language, 'abstract'),
                                'description'    => $stock->findLang($language, 'description'),
                                'keywords'       => $stock->findLang($language, 'keywords'),
                                'sku'            => $stock->sku,
                                'identifier'     => $stock->identifier,
                                'price_original' => $stock->price_original,
                                'price_discount' => $stock->price_discount,
                                'options'        => $stock->options,
                                'inventory'      => $stock->inventory,
                                'quantity'       => $stock->quantity,
                                'fee'            => $stock->fee,
                                'tax'            => $stock->tax,
                                'tip'            => $stock->tip,
                                'is_new'         => $stock->is_new,
                                'is_featured'    => $stock->is_featured,
                                'is_highlighted' => $stock->is_highlighted,
                                'updated_at'     => $stock->updated_at,
                                'covers'         => $this->getEnabledCover($language, $stock)
                            ],
                            'product' => [
                                'id'          => empty($product) ? '' : $product->id,
                                'serial'      => empty($product) ? '' : $product->serial,
                                'name'        => empty($product) ? '' : $product->findLang($language, 'name'),
                                'abstract'    => empty($product) ? '' : $product->findLang($language, 'abstract'),
                                'description' => empty($product) ? '' : $product->findLang($language, 'description'),
                                'covers'      => empty($product) ? '' : $this->getEnabledCover($language, $product)
                            ],
                            'catalog' => [
                                'id'          => empty($catalog) ? '' : $catalog->id,
                                'serial'      => empty($catalog) ? '' : $catalog->serial,
                                'name'        => empty($catalog) ? '' : $catalog->findLang($language, 'name'),
                                'description' => empty($catalog) ? '' : $catalog->findLang($language, 'description'),
                                'color'       => empty($catalog) ? '' : $catalog->color,
                                'size'        => empty($catalog) ? '' : $catalog->size,
                                'material'    => empty($catalog) ? '' : $catalog->material,
                                'taste'       => empty($catalog) ? '' : $catalog->taste,
                                'weight'      => empty($catalog) ? '' : $catalog->weight,
                                'length'      => empty($catalog) ? '' : $catalog->length,
                                'width'       => empty($catalog) ? '' : $catalog->width,
                                'height'      => empty($catalog) ? '' : $catalog->height,
                                'covers'      => empty($catalog) ? [] : $this->getEnabledCover($language, $catalog)
                            ]
                        ]);
                    }
                }
            } else {
                array_push($list, [
                    'id'       => $item->id,
                    'nums'     => $item->nums,
                    'binding'  => $item->binding,
                    'options'  => $item->options,
                    'stock_id' => $item->stock_id
                ]);
            }
        }

        return $list;
    }

    /**
     * @param Item    $instance
     * @param String  $code
     * @return Array
     */
    public function show($instance, string $code): array
    {
        $data = [
            'id'         => $instance->id,
            'channel_id' => $instance->channel_id,
            'user_id'    => $instance->user_id,
            'binding'    => $instance->binding,
            'options'    => $instance->options,
            'created_at' => $instance->created_at,
            'updated_at' => $instance->updated_at,
            'stock'      => []
        ];

        if (
            config('wk-mall-cart.onoff.mall-shelf')
            && !empty(config('wk-core.class.mall-shelf.stock'))
        ) {
            $service = new StockService();
            $data['stock'] = $service->showForItem($instance->stock, $code);
        }

        return $data;
    }

    /**
     * @param Int  $channel_id
     * @param Int  $user_id
     * @param Int  $stock_id
     * @return Int
     */
    public function countNums(int $channel_id, int $user_id, int $stock_id): int
    {
        $record = $this->where('channel_id', '=', $channel_id)
                       ->where('user_id', '=', $user_id)
                       ->where('stock_id', '=', $stock_id)
                       ->first();

        return empty($record) ? 0 : $record->nums;
    }

    /**
     * @param String  $code
     * @param Int     $channel_id
     * @param Int     $user_id
     * @param Int     $stock_id
     * @param Int     $nums
     * @param Array   $binding
     * @param Array   $options
     * @return Array
     */
    public function push(string $code, int $channel_id, int $user_id, int $stock_id, $nums = 1, $binding = null, $options = null): array
    {
        $record = $this->where('channel_id', '=', $channel_id)
                       ->where('user_id', '=', $user_id)
                       ->where('stock_id', '=', $stock_id)
                       ->first();

        if (empty($record)) {
            $record = $this->save([
                'channel_id' => $channel_id,
                'user_id'    => $user_id,
                'stock_id'   => $stock_id,
                'nums'       => $nums,
                'binding'    => $binding,
                'options'    => $options
            ]);
        } else {
            $record->nums   += $nums;
            $record->binding = $binding;
            $record->options = $options;
            $record->save();
        }

        return $this->show($record, $code);
    }

    /**
     * @param String  $code
     * @param Int     $channel_id
     * @param Int     $user_id
     * @param Int     $stock_id
     * @param Int     $nums
     * @return Array
     */
    public function pop(string $code, int $channel_id, int $user_id, int $stock_id, $nums = -1): array
    {
        $record = $this->where('channel_id', '=', $channel_id)
                       ->where('user_id', '=', $user_id)
                       ->where('stock_id', '=', $stock_id)
                       ->first();

        if (
            !empty($record)
            && ($record->nums+$nums) > 0
        ) {
            $record->nums += $nums;
            $record->save();

            return $this->show($record, $code);
        }

        return [];
    }

    /**
     * @param String  $code
     * @param Int     $channel_id
     * @param Int     $user_id
     * @param Int     $stock_id
     * @param Int     $nums
     * @return Model
     */
    public function update(string $code, int $channel_id, int $user_id, int $stock_id, $nums = 1)
    {
        $record = $this->where('channel_id', '=', $channel_id)
                       ->where('user_id', '=', $user_id)
                       ->where('stock_id', '=', $stock_id)
                       ->first();

        if (empty($record)) {
            $record = $this->save([
                'channel_id' => $channel_id,
                'user_id'    => $user_id,
                'stock_id'   => $stock_id,
                'nums'       => $nums
            ]);
        } else {
            $record->nums = $nums;
            $record->save();
        }

        return $this->show($record, $code);
    }

    /**
     * @param String  $code
     * @param Int     $channel_id
     * @param Int     $user_id
     * @param Array   $stocks
     * @param Array   $nums
     * @return Bool
     *
     * @throws NotExpectedmodelException
     * @throws NotFoundModelException
     */
    public function updateAll(string $code, int $channel_id, int $user_id, array $stocks, array $nums): bool
    {
        if (count($stocks) != count($nums))
            throw new NotExpectedmodelException($stocks);

        foreach ($stocks as $key => $stock_id) {
            if (
                config('wk-mall-cart.onoff.mall-shelf')
                && !empty(config('wk-core.class.mall-shelf.stock'))
            ) {
                $service = new StockService();
                $result = $service->checkOverflowWithMember('update', $channel_id, $user_id, $stock_id, $nums[$key]);
                if ($result)
                    return false;
            }

            $record = $this->where('channel_id', '=', $channel_id)
                           ->where('user_id', '=', $user_id)
                           ->where('stock_id', '=', $stock_id)
                           ->first();

            if (empty($record)) {
                throw new NotFoundModelException($record);
            } else {
                $record->nums = $nums[$key];
                $record->save();
            }
        }

        return true;
    }

    /**
     * @param Int    $channel_id
     * @param Int    $user_id
     * @param Array  $stock_id
     * @return Bool
     */
    public function remove(int $channel_id, int $user_id, array $stock_id): bool
    {
        return $this->where('channel_id', '=', $channel_id)
                    ->where('user_id', '=', $user_id)
                    ->whereIn('stock_id', $stock_id)
                    ->delete();
    }
}
