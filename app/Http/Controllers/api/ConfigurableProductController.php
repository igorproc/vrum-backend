<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;

use App\Models\ProductOptionGroup;
use App\Models\ProductOptionItem;
use App\Models\ProductVariantGroup;
use App\Models\ProductVariantItem;

class ConfigurableProductController extends Controller
{
    private function getOptionsByGroupIdForVariant(int $groupId, int $itemId)
    {
        $optionGroup = ProductOptionGroup::query()->find($groupId);
        $optionItem = ProductOptionItem::query()->find($itemId);

        return [
            'code' => $optionGroup['label'],
            'valueId' => $optionItem['id']
        ];
    }

    protected function getOptionsByProductId(int $productId)
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
                'optionId' => $optionGroup->id,
                'optionName' => $optionGroup->label,
                'values' => array_map(function ($item) {
                    return [
                        'optionId' => $item['id'],
                        'name' => $item['label'],
                        'value' => $item['value'],
                    ];
                }, $optionItem),
            ];
        }

        return $optionItems;
    }

    protected function getVariantsByProductId(int $productId)
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

            $variantItems[] = [
                'product' => [
                    'id' => $variantGroup->id,
                    'sku' => $variantGroup->sku,
                    'imageUrl' => $variantGroup->image_url
                ],
                'attributes' => array_map(function ($item) {
                    return $this->getOptionsByGroupIdForVariant(
                        $item['option_group_id'],
                        $item['option_item_id'],
                    );
                }, $variantItem)
            ];
        }

        return $variantItems;
    }

    public function getData(int $productId)
    {
        return [
            'options' => $this->getOptionsByProductId($productId),
            'variants' => $this->getVariantsByProductId($productId),
        ];
    }
}
