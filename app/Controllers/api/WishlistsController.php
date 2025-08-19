<?php

namespace App\Controllers\Api;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\RESTful\ResourceController;
use App\Models\Customer\WishlistsModel;
use App\Models\Customer\ProductsModel;

class WishlistsController extends ResourceController
{
    use ResponseTrait;
    public function adddeletewish()
    {
        $json = $this->request->getJSON();
        $wishlists = new WishlistsModel();
        $ProductId = isset($json->product_id) ? $json->product_id : null;
        $user_id = isset($json->user_id) ? $json->user_id : null;
        $product_variation_id  = isset($json->product_variation_id ) ? $json->product_variation_id  : null;
        if ($ProductId && $user_id) {
            $existingUser = $wishlists->where([
                'user_id' => $user_id,
                'product_id' => $ProductId,
                'product_variation_id ' => $product_variation_id
            ])->findAll();
            if ($existingUser) {
                foreach ($existingUser as $list) {
                    $res = $wishlists->delete($list['id']);
                }
            } else {
                $wishlists->insert([
                    'user_id' => $user_id,
                    'product_id' => $ProductId,
                    'product_variation_id' => $product_variation_id
                ]);
            }
            $message = [
                'message' => 'successfully updated',
                'status' => 200
            ];

            return $this->response->setStatusCode(200)->setJSON(['data' => "successfully updated"]);

        } else {
            $message = [
                'message' => 'Product Id is a must',
                'status' => 400
            ];

            return $this->response->setStatusCode(400)->setJSON(['messageobject' => $message, 'data' => "Product Id is a must"]);

        }
    }
    public function getWishlist()
    {
        $userId = $this->request->getGet('user_id');
        if (isset($userId)) {
            $wishlistModels = new WishlistsModel();
            $wishlistedProducts = $wishlistModels->getUserWishlist($userId);
            if (count($wishlistedProducts) > 0) {
                foreach ($wishlistedProducts as $response) {
                    $ProductsModels = new ProductsModel();
                    if (isset($response['product_id'])) {
                        $ProductsModelsdata = $ProductsModels->find($response['product_id']);

                        $data[] = [
                            'id' => $ProductsModelsdata['id'],
                            'name' => $ProductsModelsdata['name'],
                            'shop_id' => $ProductsModelsdata['shop_id'],
                            'added_by' => $ProductsModelsdata['added_by'],
                            'slug' => $ProductsModelsdata['slug'],
                            'brand_id' => $ProductsModelsdata['brand_id'],
                            'unit_id ' => $ProductsModelsdata['unit_id'],
                            'thumbnail_image' => $ProductsModelsdata['thumbnail_image'],
                            'gallery_images' => $ProductsModelsdata['gallery_images'],
                            'product_tags' => $ProductsModelsdata['product_tags'],
                            'short_description' => $ProductsModelsdata['short_description'],
                            'description' => $ProductsModelsdata['description'],
                            'price' => $ProductsModelsdata['price'],
                            'min_price' => $ProductsModelsdata['min_price'],
                            'max_price' => $ProductsModelsdata['max_price'],
                            'discount_value' => $ProductsModelsdata['discount_value'],
                            'discount_type' => $ProductsModelsdata['discount_type'],
                            'discount_start_date' => $ProductsModelsdata['discount_start_date'],
                            'discount_end_date' => $ProductsModelsdata['discount_end_date'],
                            'sell_target' => $ProductsModelsdata['sell_target'],
                            'stock_qty' => $ProductsModelsdata['stock_qty'],
                            'is_published' => $ProductsModelsdata['is_published'],
                            'is_featured' => $ProductsModelsdata['is_featured'],
                            'min_purchase_qty' => $ProductsModelsdata['min_purchase_qty'],
                            'max_purchase_qty' => $ProductsModelsdata['max_purchase_qty'],
                            'min_stock_qty' => $ProductsModelsdata['min_stock_qty'],
                            'has_variation' => $ProductsModelsdata['has_variation'],
                            'has_warranty' => $ProductsModelsdata['has_warranty'],
                            'total_sale_count' => $ProductsModelsdata['total_sale_count'],
                            'standard_delivery_hours' => $ProductsModelsdata['standard_delivery_hours'],
                            'express_delivery_hours' => $ProductsModelsdata['express_delivery_hours'],
                            'size_guide' => $ProductsModelsdata['size_guide'],
                            'meta_title' => $ProductsModelsdata['meta_title'],
                            'meta_description' => $ProductsModelsdata['meta_description'],
                            'meta_img' => $ProductsModelsdata['meta_img'],
                            'reward_points' => $ProductsModelsdata['reward_points'],
                            'created_at' => $ProductsModelsdata['created_at'],
                            'updated_at' => $ProductsModelsdata['updated_at'],
                            'deleted_at' => $ProductsModelsdata['deleted_at'],
                            'vedio_link' => $ProductsModelsdata['vedio_link'],
                            'created_by' => $ProductsModelsdata['created_by'],
                            'updated_by' => $ProductsModelsdata['updated_by'],
                            'is_import' => $ProductsModelsdata['is_import'],
                        ];


                        $message = [
                            'message' => 'successfully fetch data',
                            'status' => 200
                        ];
                        return $this->response->setStatusCode(200)->setJSON(['messageobject' => $message, 'data' => $data]);
                    } else {
                        $message = [
                            'message' => 'productaId not found ',
                            'status' => 400
                        ];

                        return $this->response->setStatusCode(400)->setJSON(['messageobject' => $message]);
                        // error
                    }

                }
            } else {

                $message = [
                    'message' => 'data not Found',
                    'status' => 400
                ];

                return $this->response->setStatusCode(400)->setJSON(['messageobject' => $message]);

            }
        } else {

            $message = [
                'message' => 'userId not found ',
                'status' => 400
            ];

            return $this->response->setStatusCode(400)->setJSON(['messageobject' => $message]);

        }

    }


}




