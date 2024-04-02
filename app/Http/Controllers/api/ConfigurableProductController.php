<?php

namespace App\Http\Controllers\api;
// Vendors
use App\Http\Controllers\Controller;
use Illuminate\Support\MessageBag;
use Illuminate\Http\JsonResponse;

// Utils
use Carbon\Carbon;
use App\Decorators\ValidationDecorator;
use App\Http\Requests\ConfigurableProductRequest;

// Controller Deps
use App\Models\ProductOptionGroup;
use App\Models\ProductOptionItem;
use App\Models\ProductVariantGroup;
use App\Models\ProductVariantItem;

class ConfigurableProductController extends Controller
{
    protected array $DeleteType = [
        'optionItem' => ProductOptionItem::class,
        'optionGroup' => ProductOptionGroup::class,
        'variantItem' => ProductVariantItem::class,
        'variantGroup' => ProductVariantGroup::class,
    ];
    protected ValidationDecorator $validationDecorator;

    public function __construct(ValidationDecorator $validationDecorator)
    {
        $this->validationDecorator = $validationDecorator;
    }

    private function getOptionsByGroupIdForVariant(int $groupId, int $itemId): array
    {
        $optionGroup = ProductOptionGroup::query()->find($groupId);
        $optionItem = ProductOptionItem::query()->find($itemId);

        return [
            'code' => $optionGroup['label'],
            'valueId' => $optionItem['id']
        ];
    }

    protected function getOptionsByProductId(int $productId): array
    {
        $optionGroups = ProductOptionGroup::query()
            ->where('product_id', '=', $productId)
            ->get();
        $optionItems = [];

        foreach ($optionGroups as $optionGroup) {
            $optionItem = ProductOptionItem::query()
                ->where('product_option_group_id', '=', $optionGroup->id)
                ->get()
                ->toArray();

            $optionItems[] = [
                'id' => $optionGroup->id,
                'name' => $optionGroup->label,
                'values' => array_map(function ($item) {
                    return [
                        'id' => $item['id'],
                        'name' => $item['label'],
                        'value' => $item['value'],
                    ];
                }, $optionItem),
            ];
        }

        return $optionItems;
    }

    protected function getVariantsByProductId(int $productId): array
    {
        $variantGroups = ProductVariantGroup::query()
            ->where('product_id', '=', $productId)
            ->get();
        $variantItems = [];

        foreach ($variantGroups as $variantGroup) {
            $variantItem = ProductVariantItem::query()
                ->where('product_variant_group_id', '=', $variantGroup->id)
                ->get()
                ->toArray();
            $variantImage = env('APP_URL') . $variantGroup->image_url;

            $variantItems[] = [
                'product' => [
                    'id' => $variantGroup->id,
                    'sku' => $variantGroup->sku,
                    'imageUrl' => $variantImage,
                    'price' => $variantGroup->price,
                ],
                'attributes' => array_map(function ($item) {
                    $variantAttributes = $this->getOptionsByGroupIdForVariant(
                        $item['option_group_id'],
                        $item['option_item_id'],
                    );
                    $variantAttributesArray = json_decode(json_encode($variantAttributes), true);

                    return [
                        'id' => $item['id'],
                        ...$variantAttributesArray,
                    ];
                }, $variantItem)
            ];
        }

        return $variantItems;
    }

    public function getData(int $productId): array
    {
        return [
            'options' => $this->getOptionsByProductId($productId),
            'variants' => $this->getVariantsByProductId($productId),
        ];
    }

    public function createOptionGroup(ConfigurableProductRequest $request): JsonResponse
    {
        $rules = [
            'productId' => 'required|numeric|min:1|max:100000',
            'label' => 'required|string|min:3|max:10',
        ];
        $data = $this->validationDecorator->validate($rules, $request->input('data'));

        if ($data instanceof MessageBag) {
            return response()->json([
                'error' => [
                    'code' => 401,
                    'message' => $data
                ]
            ], 401);
        }

        $optionGroup = new ProductOptionGroup([
            'product_id' => $data['productId'],
            'label' => $data['label'],
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
        $optionGroup->save();

        return response()->json([
            'group' => [
                'id' => $optionGroup['id'],
                'name' => $optionGroup['label'],
                'values' => [],
            ]
        ]);
    }

    public function createOptionItem(ConfigurableProductRequest $request): JsonResponse
    {
        $rules = [
            'groupId' => 'required|numeric|min:1|max:100000',
            'name' => 'required|string|min:1|max:32',
            'value' => 'required|string|min:1|max:32'
        ];
        $data = $this->validationDecorator->validate($rules, $request->input('data'));
        if ($data instanceof MessageBag) {
            return response()->json([
                'error' => [
                    'code' => 401,
                    'message' => $data
                ]
            ], 401);
        }

        $optionItem = new ProductOptionItem([
            'product_option_group_id' => $data['groupId'],
            'label' => $data['name'],
            'value' => $data['value'],
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
        $optionItem->save();

        return response()->json([
            'item' => [
                'id' => $optionItem['id'],
                'name' => $optionItem['label'],
                'value' => $optionItem['value']
            ]
        ]);
    }

    public function createVariantGroup(ConfigurableProductRequest $request): JsonResponse
    {
        $rules = [
            'id' => 'required|numeric|min:1|max:100000',
            'sku' => 'required|string|min:1|max:32',
            'imageUrl' => 'nullable|string|min:1|max:128',
            'price' => 'required|numeric|min:1|max:128',
        ];

        $data = $this->validationDecorator->validate($rules, $request->input('data'));
        if ($data instanceof MessageBag) {
            return response()->json([
                'error' => [
                    'code' => 401,
                    'message' => $data
                ]
            ], 401);
        }

        $variantGroup = new ProductVariantGroup([
            'product_id' => $data['id'],
            'sku' => $data['sku'],
            'image_url' => $data['imageUrl'] ?? null,
            'price' => $data['price'],
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
        $variantGroup->save();
        $variantImage = null;
        if ($variantGroup['image_url']) {
            $variantImage = env('APP_URL') . $variantGroup['image_url'];
        }

        return response()->json([
            'group' => [
                'attributes' => [],
                'product' => [
                    'id' => $variantGroup['id'],
                    'imageUrl' => $variantImage,
                    'price' => $variantGroup['price'],
                    'sku' => $variantGroup['sku']
                ],
            ]
        ]);
    }

    public function createVariantItem(ConfigurableProductRequest $request): JsonResponse
    {
        $rules = [
            'variantGroupId' => 'required|numeric|min:1|max:100000',
            'optionGroupId' => 'required|numeric|min:1|max:100000',
            'optionId' => 'required|numeric|min:1|max:100000',
        ];
        $data = $this->validationDecorator->validate($rules, $request->input('data'));
        if ($data instanceof MessageBag) {
            return response()->json([
                'error' => [
                    'code' => 401,
                    'message' => $data
                ]
            ], 401);
        }

        $variantItem = new ProductVariantItem([
            'product_variant_group_id' => $data['variantGroupId'],
            'option_group_id' => $data['optionGroupId'],
            'option_item_id' => $data['optionId'],
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
        $variantItem->save();
        $variantAttributes = $this->getOptionsByGroupIdForVariant($variantItem['option_group_id'], $variantItem['option_item_id']);
        $variantAttributesArray = json_decode(json_encode($variantAttributes), true);

        return response()->json([
            'item' => [
                'id' => $variantItem['id'],
                ...$variantAttributesArray
            ]
        ]);
    }

    public function deleteItem(ConfigurableProductRequest $request): JsonResponse
    {
        $rules = [
            'type' => 'required|string|min:5|max:100',
            'id' => 'required|numeric|min:1|max:100000',
        ];
        $data = $this->validationDecorator->validate($rules, $request->input('data'));
        if ($data instanceof MessageBag) {
            return response()->json([
                'error' => [
                    'code' => 401,
                    'message' => $data
                ]
            ], 401);
        }

        $deleteItemModel = $this->DeleteType[$data['type']];
        if (!$deleteItemModel) {
            return response()->json([], 500);
        }

        $deletedInstance = $deleteItemModel::query()->find($data['id']);
        $deletedInstance->delete();

        return response()->json(['successDelete' => boolval($deletedInstance)]);
    }
}
