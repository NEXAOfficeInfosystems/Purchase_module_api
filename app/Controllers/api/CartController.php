<?php

namespace App\Controllers\Api;

use CodeIgniter\API\ResponseTrait;
use App\Models\api\CartsModel;
use CodeIgniter\RESTful\ResourceController;
use Ramsey\Uuid\Uuid;
use App\Models\Customer\ProductsModel;
use App\Models\Customer\TaxesModel;
use App\Models\api\ProductVariationsModel;
use App\Models\api\ProductVariationCombinationsModel;
use App\Models\Customer\WishlistsModel;
use App\Models\Customer\ShopsModel;
use App\Models\Customer\Media_managersModel;


class CartController extends ResourceController
{
    use ResponseTrait;
    public function addcart()
    {
        $json = $this->request->getJSON();
        $cartModel = new CartsModel();
        $ProductId = $json->product_id;
        $product_variation_id = $json->product_variation_id;
        $user_id = $json->user_id;
        $qty = isset($json->qty) ? $json->qty : 1;
        if ($ProductId && $product_variation_id && $user_id) {
            $existingUser = $cartModel->where([
                'user_id' => $user_id,
                'product_variation_id' => $product_variation_id,
                'product_id' => $ProductId
            ])->findAll();
            if ($existingUser) {
                $cartModel->set(
                    [
                        'product_variation_id' => $product_variation_id,
                        'product_id' => $ProductId,
                        'qty' => $qty
                    ]
                )->where([
                            'user_id' => $user_id,
                            'product_variation_id' => $product_variation_id,
                            'product_id' => $ProductId
                        ])->update();
            } else {
                $cartModel->insert([
                    'user_id' => $user_id,
                    'product_variation_id' => $product_variation_id,
                    'product_id' => $ProductId,
                    'qty' => $qty
                ]);
            }
            $message = [
                'message' => 'success updated',
                'status' => 200
            ];
            return $this->response->setStatusCode(200)->setJSON(['messageobject' => $message]);

        } else {
            $message = [
                'message' => 'Internal Server Error',
                'status' => 400
            ];
            return $this->response->setStatusCode(400)->setJSON(['messageobject' => $message]);
        }
    }

    public function Cartdelete()
    {
        $json = $this->request->getJSON();
        $cartModel = new CartsModel();
        $cartid = $json->id;
        if ($cartid) {
            $res = $cartModel->delete($cartid);
            $message = [
                'message' => 'Deleted successfully',
                'status' => 200
            ];
            return $this->response->setStatusCode(200)->setJSON(['messageobject' => $message, 'data' => "Deleted successfully"]);
        } else {
            $message = [
                'message' => 'Invalid user id',
                'status' => 400
            ];
            return $this->response->setStatusCode(400)->setJSON(['message' => $message]);

        }
    }

    public function index()
    {
        $productModel = new ProductsModel();
        $cardModels = new CartsModel();
        $CustomerId = $this->request->getGet('user_id');
        if (isset($CustomerId)) {
            $cardModels->where('user_id', $CustomerId);
            $cardModelsResponse = $cardModels->findAll();
            $transformedProducts = $this->transformProducts($cardModelsResponse, $productModel);
            $message = [
                'message' => 'Success fetch',
                'status' => 200
            ];

            return $this->response->setStatusCode(200)->setJSON(['messageobject' => $message, 'data' => $transformedProducts]);
        } else {
            $message = [
                'message' => 'Invalid user id',
                'status' => 400
            ];
            return $this->response->setStatusCode(400)->setJSON(['message' => $message]);
        }
    }

    public function transformProducts($cardModelsResponse, $productModel)
    {
        $total_cost = 0;
        $totalDiscount = 0;
        $transformedProducts = [];
        $shopModel = new ShopsModel();
        $mediaModel = new Media_managersModel();
        $product_variation_combination = new ProductVariationCombinationsModel();
        $TaxesModels = new TaxesModel();
        $productVariationsModel = new ProductVariationsModel();
        $taxValues=[];
        $discountValues=[];

        $totalSum=0;
       

        foreach ($cardModelsResponse as $crtlist) {

         
            $product = $productModel->find($crtlist['product_id']);
            $productVariation = $productVariationsModel->find($crtlist['product_variation_id']);
          
            if ($product) {
                $img = $mediaModel->find($product['thumbnail_image']);
                $product['thumbnail_image_url'] = $img['media_file'];
            } else {
                $product['thumbnail_image_url'] = null;
            }


            if ($productVariation) {
                $img = $mediaModel->find($productVariation['thumbnail_image']);
                // $productVariation['thumbnail_image'] = $img['media_file'];
                $productVariation['thumbnail_image_url'] = isset($img['media_file']) ? $img['media_file'] : null;
            } else {
                $productVariation['thumbnail_image_url'] = null;
            }


        

           if (isset($productVariation['tax_id']) && $productVariation['tax_id']) {
    $data = $TaxesModels->where('id', $productVariation['tax_id'])->first();
} else {
    $data['value'] = 0;
}



// if ($productVariation['discount_type']=="percent") {
    $discountAmount = ($crtlist['qty'] * $productVariation['price'] * $productVariation['discount_value']) / 100;
// }
// else {
//     $discountAmount= $productVariation['discount_value'];
// }
    
          array_push($discountValues,$discountAmount);
            if(isset($crtlist['qty']) && $crtlist['qty'] != null  && isset($productVariation['price']) && $productVariation['max_price'] !=null  && $data['value'] != null){
                $taxAmount = ($crtlist['qty'] * $productVariation['price'] * $data['value']) / 100;
            }else{
            $taxAmount = 0;
            }
            array_push($taxValues, $taxAmount);
         
            $amt = $crtlist['qty'] * $productVariation['price'];

            $total_cost += $amt;
            if (isset($product['shop_id'])) {
                $shopModel->where('id', $product['shop_id']);
            } 
            $shops = $shopModel->findAll();

            // $transformedProducts['items'][] = ['crtlist' => $crtlist, 'crtlistdd' => $product];
            $transformedProducts['items'][] = [
                "id" => $crtlist["id"],
                "user_id" => $crtlist["user_id"],
                "guest_user_id" => $crtlist["guest_user_id"],
                "location_id" => $crtlist["location_id"],
                "product_variation_id" => $crtlist["product_variation_id"],
                "qty" => $crtlist["qty"],
                "product_variation_combination" => $product_variation_combination->where('product_variation_id', $productVariation['id'])->findAll(),

                "product" => $product,
                "shop" => isset($shops[0]) ? $shops[0] : null,
                "product_variation" => $productVariation,
                "created_at" => $crtlist["created_at"],
                "updated_at" => $crtlist["updated_at"],
                "deleted_at" => $crtlist["deleted_at"],
                "product_id" => $crtlist["product_id"],
             "tax_details" => isset($product['tax_id']) ? $TaxesModels->where('id', $productVariation['tax_id'])->first() : null,

            ];



        }
     
        $transformedProducts['total'] = $total_cost;
        $transformedProducts['total_tax'] = number_format(array_sum($taxValues),2) ? number_format(array_sum($taxValues),2) : 0;
        $transformedProducts['discount_value'] =number_format(array_sum($discountValues),2);


        return $transformedProducts;
    }

    public function removecart()
    {
        // Get JSON data from the request
        $json = $this->request->getJSON();
        $cartModel = new CartsModel();

        // Generate a unique ID using UUID
        $guid = Uuid::uuid4()->toString();


        $GuestId = isset($json->GuestId) ? $json->GuestId : null;
        $CustomerId = isset($json->CustomerId) ? $json->CustomerId : null;

        $ProductId = $json->ProductId;

        if ($ProductId && ($GuestId || $CustomerId)) {
            if ($CustomerId && $GuestId) {
                $existingUser = $cartModel->where(['GuestId' => $json->GuestId, 'CustomerId' => null])->findAll();
                if ($existingUser) {
                    // Update the guest id for all records with the same user id
                    $cartModel->set(['CustomerId' => $CustomerId])->where('GuestId', $GuestId)->update();
                }
            }

            if ($CustomerId || $GuestId) {
                if ($CustomerId) {
                    $existingCart = $cartModel->where(['CustomerId' => $json->CustomerId, 'ProductId' => $json->ProductId])->first();
                } else if ($GuestId) {
                    $existingCart = $cartModel->where(['GuestId' => $json->GuestId, 'ProductId' => $json->ProductId])->first();
                } else {
                    return $this->failUnauthorized('Guest Id or User Id is a must');
                }
                if ($existingCart) {
                    $newQuantity = $existingCart['Quantity'] - 1;
                    $cartModel->set(['Quantity' => $newQuantity])->where('ProductId', $ProductId)->update();
                }

                return $this->respond(['message' => 'Item removed successfully']);


            } else {
                return $this->failUnauthorized('Guest Id or User Id is a must');
            }

        } else {
            return $this->failUnauthorized('Product Id  is a must');
        }


    }

    // =================== END REMOVE CART  =================



    // =================== DELETE CART  =================

    public function deletecartitem()
    {
        // Get JSON data from the request

        $json = $this->request->getJSON();
        $cartModel = new CartsModel();
        $GuestId = isset($json->GuestId) ? $json->GuestId : null;
        $CustomerId = isset($json->CustomerId) ? $json->CustomerId : null;
        $ProductId = $json->ProductId;

        if ($ProductId && ($GuestId || $CustomerId)) {

            if ($CustomerId && $GuestId) {
                $existingUser = $cartModel->where(['GuestId' => $json->GuestId, 'CustomerId' => null])->findAll();
                if ($existingUser) {
                    $cartModel->set(['CustomerId' => $CustomerId])->where('GuestId', $GuestId)->update();
                }
            }

            if ($CustomerId || $GuestId) {
                if ($CustomerId) {
                    $existingCart = $cartModel->where(['CustomerId' => $json->CustomerId, 'ProductId' => $json->ProductId])->first();
                } else if ($GuestId) {
                    $existingCart = $cartModel->where(['GuestId' => $json->GuestId, 'ProductId' => $json->ProductId])->first();
                } else {
                    return $this->failUnauthorized('Guest Id or User Id is a must');
                }
                if ($existingCart) {
                    $IsDeleted = 0;
                    $cartModel->set(['IsDeleted' => $IsDeleted])->where('ProductId', $ProductId)->update();
                }

                return $this->respond(['message' => 'Item deleted successfully']);


            } else {
                return $this->failUnauthorized('Guest Id or User Id is a must');
            }

        } else {
            return $this->failUnauthorized('Product Id  is a must');
        }


    }

    public function addcartlocal()
    {
        $datajson = $this->request->getJSON();
        $cartModel = new CartsModel();

        foreach ($datajson->data as $json) {
            $ProductId = $json->product_id;
            $product_variation_id = $json->product_variation_id;
            $user_id = $json->user_id;
            $qty = isset($json->qty) ? $json->qty : 1;
            if ($ProductId && $product_variation_id && $user_id) {
                $existingUser = $cartModel->where([
                    'user_id' => $user_id,
                    'product_variation_id' => $product_variation_id,
                    'product_id' => $ProductId
                ])->findAll();
                if ($existingUser) {
                    $cartModel->set(
                        [
                            'product_variation_id' => $product_variation_id,
                            'product_id' => $ProductId,
                            'qty' => $qty
                        ]
                    )->where([
                                'user_id' => $user_id,
                                'product_variation_id' => $product_variation_id,
                                'product_id' => $ProductId
                            ])->update();
                } else {
                    $cartModel->insert([
                        'user_id' => $user_id,
                        'product_variation_id' => $product_variation_id,
                        'product_id' => $ProductId,
                        'qty' => $qty
                    ]);
                }

            }
        }
        $message = [
            'message' => 'successfully updated',
            'status' => 200
        ];
        return $this->response->setStatusCode(200)->setJSON(['messageobject' => $message, 'data' => "successfully updated"]);

    }

    // =================== END DELETE CART  =================
}




