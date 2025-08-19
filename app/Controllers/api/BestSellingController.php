<?php

namespace App\Controllers\api;

use App\Models\api\Order_Items_Model;
use App\Models\api\ProductsModel;
use App\Models\api\Media_managersModel;
use App\Models\api\ProductVariationsModel;
use CodeIgniter\RESTful\ResourceController;

class BestSellingController extends ResourceController
{
    protected $format = 'json';


    public function bestsellingproduct()
{
    // try {
        $ProductsModels = new ProductVariationsModel();
        // $ProductsModels->where('stock_qty >', 0);
        $ProductsModels->where('is_published', 1);
        $ProductsModels->where('is_active', 1); 

        // $data = $ProductsModels->findAll();
        $data = $ProductsModels->where('is_active', '1')->orWhere('is_active', 1)->findAll();

        $resposneData = [];
        // $ProductVariationsModel = new ProductVariationsModel();
        $mediaModel = new Media_managersModel();

        $ProductVariationsModel = new ProductVariationsModel();

        $orderItemsModel = new Order_Items_Model();
        $bestSellingProducts = $orderItemsModel->bestselling();
        $productsModel = new ProductsModel();
        foreach ($bestSellingProducts as $resposne) {
            $mediaModel->where('id', $resposne['thumbnail_image']);
            $imageUrl = $mediaModel->findAll();
                $ProductsModelsdata = $ProductVariationsModel->find($resposne['id']);
                $productData = $productsModel->find($resposne['product_id']);

                $productName = isset($productData['name']) ? $productData['name'] : '';
            // $ProductsModelsdata = $ProductVariationsModel->find($resposne['id']);
            $stock_status = (intval($resposne['stock_qty']) > 0) ? 'in_stock' : 'out_stock';
            $resposneData[] = [
         
                'id' => $resposne['id'],
                'is_active' => $resposne['is_active'],
                'name' => $productName,
                'slug' => isset($productData['slug']) ? $productData['slug'] : null,
                'description' => $resposne['short_description'],
                'category_image_id' => null,
                'category_icon_id' => null,
                'status' => 1,
                'type' => "product",
                'commission_rate' => null,
                'parent_id' => null,
                'created_by_id' => "1",
                'created_at' => $resposne['created_at'],
                'updated_at' => $resposne['updated_at'],
                'deleted_at' => null,
                'blogs_count' => 6,
                'products_count' => 0,
                'thumbnail_image' => $resposne['thumbnail_image'],
                'thumbnail_image_url' => isset($imageUrl[0]['media_file']) ? $imageUrl[0]['media_file'] : null,
                'category_icon' => null,
                'subcategories' => [],
                'parent' => null,
                'short_description' => [],
                'unit' => [],
                'weight' => [],
                'quantity' => [],
                'sale_price' => $resposne['max_price'],
                'price' => $resposne['price'],
                'discount' => $resposne['discount_value'],
                'is_featured' => $resposne['is_featured'],
                'shipping_days' => [],
                'is_cod' => [],
                'is_free_shipping' => [],
                'is_sale_enable' => [],
                'is_return' => [],
                'is_trending' => [],
                'is_approved' => [],
                'sale_starts_at' => [],
                'sale_expired_at' => [],
                'sku' => [],
                'is_random_related_products' => [],
                'stock_status' => $stock_status,
                'meta_title' => [],
                'meta_description' => [],
                'product_thumbnail_id' => [],
                'product_meta_image_id' => [],
                'size_chart_image_id' => [],
                'estimated_delivery_text' => [],
                'return_policy_days' => $resposne['return_policy_days'],
                'safe_checkout' => [],
                'secure_checkout' => [],
                'social_share' => [],
                'encourage_order' => [],
                'encourage_view' => [],
                'store_id' => [],
                'tax_id' => [],
                'orders_count' => [],
                'reviews_count' => [],
                'can_review' => [],
                'rating_count' => [],
                'order_amount' => [],
                'review_ratings' => [],
                'related_product' => [],
                'cross_sell_products' => [],
                'product_thumbnail' => [],
                'product_galleries' => [],
                'product_meta_image' => [],
                'reviews' => [],
                'store' => [],
                'store_logo' => [],
                'store_cover' => [],
                'vendor' => [],
                'point' => [],
                'wallet' => [],
                'address' => [],
                'vendor_wallet' => [],
                'profile_image' => [],
                'payment_account' => [],
                'country' => [],
                'state' => [],
                'tax' => [],
                'categories' => [],
                'category_image' => [],
                'tags' => [],
                'attributes' => [],
                'variations' =>$ProductsModelsdata
            ];
        }

        $data = $resposneData;

     
        $message = [
            'message' => 'Success!',
            'status' => 200
        ];

        return $this->response->setStatusCode(200)->setJSON(['messageobject' => $message, 'data' => $data]);

}

}
