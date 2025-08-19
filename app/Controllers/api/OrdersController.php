<?php

namespace App\Controllers\Api;

use CodeIgniter\API\ResponseTrait;
use App\Models\NotificationModel;
use CodeIgniter\Model;
use Config\App;
use DateTime;
use TCPDF;
use App\Models\api\CartsModel;
use App\Models\api\SaleorderModel;

use App\Models\api\OrderReturnModel;
use App\Models\api\OrderReturnItemModel;
use CodeIgniter\RESTful\ResourceController;
use App\Models\Customer\ProductsModel;
use App\Models\Customer\OrdersModel;
use App\Models\Customer\Order_Groups_Model;
use App\Models\Customer\Order_Items_Model;
use App\Models\Customer\UsersModel;
use App\Models\api\ProductVariationsModel;
use App\Models\api\ProductVariationStocksModel;
use App\Models\Customer\Media_managersModel;
use App\Models\Customer\PaymentgatwayModel;
use App\Models\Customer\TaxesModel;
use App\Models\Customer\ShopsModel;
class OrdersController extends ResourceController
{
    use ResponseTrait;


public function placeorder()
{
    $json = $this->request->getJSON();
    $OrdersModels = new OrdersModel();
    $OrdersGrpModels = new Order_Groups_Model();
    $OrdersItemModels = new Order_Items_Model();
    $cartModel = new CartsModel();
    $PaymentgatwayModels = new PaymentgatwayModel();
    $usermodel = new UsersModel();
    $productModel22 = new ProductsModel();
    $productVariationStockModel = new ProductVariationStocksModel();
    $productVariationModel = new ProductVariationsModel();
    $SaleOrderModels = new SaleorderModel();

    // 1. Check stock for all cart items
    $cartModel->where('user_id', $json->user_id);
    $cartlist = $cartModel->findAll();
    foreach ($cartlist as $cartItem) {
        $variationId = $cartItem['product_variation_id'];
        $qtyNeeded = $cartItem['qty'];
        $stockRow = $productVariationStockModel->where('product_variation_id', $variationId)->first();
        $variation = $productVariationModel->where('id', $variationId)->first();
        $variationProd = $productModel22->where('id', $variation['product_id'])->first();
        if (!$stockRow || $stockRow['stock_qty'] < $qtyNeeded) {
            $productName = isset($variationProd['name']) ? $variationProd['name'] : 'Unknown Product';
            $message = [
                'message' => "{$productName} is out of stock",
                'product_variation_id' => $variationId,
                'status' => 400
            ];
            return $this->response->setStatusCode(400)->setJSON(['messageobject' => $message]);
        }
    }

    // 2. Get user data
    $usermodel->where('id', $json->user_id);
    $userData = $usermodel->first();

// Fetch all settings and format as requested
$db = \Config\Database::connect();
$builder = $db->table('system_settings');
$settingsData = $builder->get()->getResultArray();

$MediaModel = new Media_managersModel();
$mockedData = [];
foreach ($settingsData as $setting) {
    $image_details = $MediaModel->where('id', $setting['image_id'])->first();
    $mockedData[$setting["entity"]] = [
        "key" => $setting['entity'],
        "value" => $setting['value'],
        "image_data" => $image_details
    ];
}

// Now you can access, for example:
$orderPrefix = isset($mockedData['order_code_prefix']['value']) ? $mockedData['order_code_prefix']['value'] : 'ORD';
$orderStartCode = isset($mockedData['order_code_start']['value']) ? (int)$mockedData['order_code_start']['value'] : 1;
$orderSuffix = isset($mockedData['order_code_start']['value']) ? (int)$mockedData['order_code_start']['value'] : 1;


// ...rest of your code...
        $lastOrder = $OrdersModels->orderBy('order_number', 'DESC')->first();
        if ($lastOrder && isset($lastOrder['order_number'])) {
            $lastNumber = (int)preg_replace('/\D/', '', $lastOrder['order_number']);
            $nextOrderNumber = $lastNumber + 1;
        } else {
            $nextOrderNumber = $orderStartCode;
        }
        $orderNumber = $orderPrefix .'-'. str_pad($nextOrderNumber, 5, '0', STR_PAD_LEFT) .'-'. $orderSuffix;
   
    $orderGroupData = [
        "user_id" => $json->user_id,
        "guest_user_id" => null,
        "order_code" => 1, 
        "shipping_address_id" => isset($json->shipping_address_id) ? $json->shipping_address_id : null,
        "billing_address_id" => isset($json->billing_address_id) ? $json->billing_address_id : null,
        "phone_no" => isset($userData['phone']) ? $userData['phone'] : null,
        "alternative_phone_no" => isset($userData['phone']) ? $userData['phone'] : null,
        "sub_total_amount" => $json->sub_total_amount,
        "total_tax_amount" => $json->totalTax,
        "total_coupon_discount_amount" => $json->calculatedDiscount,
        "total_discount_amount" => $json->calculatedDiscount,
        "total_shipping_cost" => isset($json->shipping_cost) ? $json->shipping_cost : 0,
        "grand_total_amount" => $json->totalAmount,
        "created_at" => $json->created_date,
        "updated_at" => date('Y-m-d H:i:s'),
        "payment_method" => isset($json->payment_method) ? $json->payment_method : null,
        "payment_status" => isset($json->payment_status) ? $json->payment_status : null,
    ];
    $order_grp_id = $OrdersGrpModels->insert($orderGroupData);
    $OrdersGrpModels->set(['order_code' => $order_grp_id])->where('id', $order_grp_id)->update();

   

$sorderPrefix = isset($mockedData['sale_order_code_prefix']['value']) ? $mockedData['sale_order_code_prefix']['value'] : 'SO#';
$sorderStartCode = isset($mockedData['sale_order_code_start']['value']) ? (int)$mockedData['sale_order_code_start']['value'] : 0001;
$sordersuffix=isset($mockedData['so_suffix']['value']) ? $mockedData['so_suffix']['value'] : '2027';


  
    $salesData123 = $SaleOrderModels->orderBy('OrderNumber', 'DESC')->first();

  
    if (isset($salesData123) && isset($salesData123['OrderNumber'])) {
        
        preg_match('/\d+$/', $salesData123['OrderNumber'], $matches);
        $salesautonumber = isset($matches[0]) ? intval($matches[0]) : 0;
    $salesautonumber += 1;

    }else{
        $salesautonumber = $sorderStartCode;
    }

    
    $formatted_number = str_pad($salesautonumber, 5, '0', STR_PAD_LEFT);
    $salesorderNumber = $sorderPrefix .'-'. $formatted_number.'-'.$sordersuffix;

    // 5. Insert into orders table
    $ordInst = [
        'order_group_id' => $order_grp_id,
        'shop_id' => isset($userData['shop_id']) ? $userData['shop_id'] : 1,
        'user_id' => $json->user_id,
        "order_number" => $orderNumber,
        "is_approved"=>1,
        "saleorder_id" => $salesorderNumber,
        "updated_at" => date('Y-m-d H:i:s'),
        "payment_status" => isset($json->payment_status) ? $json->payment_status : null,
        "shipping_cost" => isset($json->shipping_cost) ? $json->shipping_cost : 0,
        "shipping_delivery_type" => isset($json->scheduled_description) ? $json->scheduled_description : null,
        "scheduled_delivery_info" => isset($json->scheduled_time) ? $json->scheduled_time : null
    ];
    $ord_id = $OrdersModels->insert($ordInst);

    // 6. Insert into sale_order table (alternative)
    $saleOrderData = [
        "OrderNumber" => $salesorderNumber,
        "Note" => "customer order",
        "order_id" => $ord_id,
        "SaleReturnNote" => '',
        "TermAndCondition" => '',
        "IsSalesOrderRequest" => 0,
        "SOCreatedDate" => $json->created_date,
        "Status" => 'customer order',
        "DeliveryStatus" => 'Placed',
        "CustomerId" => $json->user_id,
        "TotalAmount" => $json->totalAmount,
        "TotalTax" => $json->totalTax,
        "TotalDiscount" => $json->calculatedDiscount,
        "TotalPaidAmount" => $json->totalAmount,
        "PaymentStatus" => $json->payment_status,
        "PurchaseReturnNote" => "None",
        "CreatedDate" => $json->created_date,
        "DeliveryDate" => $json->created_date,
        "is_bulkorder" => 0,
        "bulkorder_id" => 0
    ];
    $insertedId = $SaleOrderModels->insert($saleOrderData);
    if (!$insertedId) {
        $message = [
            'message' => 'Failed to create sales order',
            'status' => 400
        ];
        return $this->response->setStatusCode(400)->setJSON(['messageobject' => $message]);
    }

    // 7. Insert order items
    foreach ($cartlist as $list) {
        $productVariation = $productVariationModel->where("id", $list['product_variation_id'])->first();
        $ordItem = [
            "order_id" => $ord_id,
            "product_variation_id" => $list['product_variation_id'],
            "qty" => $list['qty'],
            "location_id" => $list['location_id'],
            "unit_price" => $productVariation['price'],
            "tax_id" => $productVariation['tax_id'],
            "discount" => $productVariation['discount_value'],
            "total_price" => $productVariation['price'] * $list['qty'],
        ];
        $OrdersItemModels->insert($ordItem);
    }

    // 8. Update stock and clear cart
    foreach ($cartlist as $list) {
        $productVariation = $productVariationModel->where("id", $list['product_variation_id'])->first();
        $qty = $productVariation['stock_qty'] - $list['qty'];
        $productVariationModel->set(['stock_qty' => $qty])->where('id', $list['product_variation_id'])->update();

        $productvarstockModel = new ProductVariationStocksModel();
        $stockRow = $productvarstockModel->where('product_variation_id', $list['product_variation_id'])->first();
        if ($stockRow) {
            $qtyStock = $stockRow['stock_qty'] - $list['qty'];
            $productvarstockModel->set(['stock_qty' => $qtyStock])->where('product_variation_id', $list['product_variation_id'])->update();
        }

        $cartModel->delete($list['id']);
    }

    // 9. Insert payment details if present
    if (isset($json->paymentdetails) && $json->paymentdetails != '') {
        $keys = [
            'order_code' => $ord_id,
            'customer_name' => isset($json->paymentdetails->name) ? $json->paymentdetails->name : null,
            'customer_card_number' => isset($json->customer_card_number) ? $json->customer_card_number : null,
            'card_exp_date' => null,
            'amount' => isset($json->amount) ? $json->amount : null,
            'balance_amount' => 0,
            "address_city" => isset($json->paymentdetails->address_city) ? $json->paymentdetails->address_city : null,
            "address_country" => isset($json->paymentdetails->address_country) ? $json->paymentdetails->address_country : null,
            "address_line1" => isset($json->paymentdetails->address_line1) ? $json->paymentdetails->address_line1 : null,
            "address_line1_check" => isset($json->paymentdetails->address_line1_check) ? $json->paymentdetails->address_line1_check : null,
            "address_line2" => isset($json->paymentdetails->address_line2) ? $json->paymentdetails->address_line2 : null,
            "address_state" => isset($json->paymentdetails->address_state) ? $json->paymentdetails->address_state : null,
            "address_zip" => isset($json->paymentdetails->address_zip) ? $json->paymentdetails->address_zip : null,
            "address_zip_check" => isset($json->paymentdetails->address_zip_check) ? $json->paymentdetails->address_zip_check : null,
            "brand" => isset($json->paymentdetails->brand) ? $json->paymentdetails->brand : null,
            "country" => isset($json->paymentdetails->country) ? $json->paymentdetails->country : null,
            "cvc_check" => isset($json->paymentdetails->cvc_check) ? $json->paymentdetails->cvc_check : null,
            "dynamic_last4" => isset($json->paymentdetails->dynamic_last4) ? $json->paymentdetails->dynamic_last4 : null,
            "exp_month" => isset($json->paymentdetails->exp_month) ? $json->paymentdetails->exp_month : null,
            "exp_year" => isset($json->paymentdetails->exp_year) ? $json->paymentdetails->exp_year : null,
            "funding" => isset($json->paymentdetails->funding) ? $json->paymentdetails->funding : null,
            "card_id" => isset($json->paymentdetails->card_id) ? $json->paymentdetails->card_id : null,
            "last4" => isset($json->paymentdetails->last4) ? $json->paymentdetails->last4 : null,
            "name" => isset($json->paymentdetails->name) ? $json->paymentdetails->name : null,
            "transaction_id" => isset($json->paymentdetails->transaction_id) ? $json->paymentdetails->transaction_id : null,
            "transaction_created" => isset($json->paymentdetails->transaction_created) ? $json->paymentdetails->transaction_created : null,
            "transaction_client_ip" => isset($json->paymentdetails->transaction_client_ip) ? $json->paymentdetails->transaction_client_ip : null,
            "transaction_response" => isset($json->transaction_response) ? $json->transaction_response : null,
        ];
        $PaymentgatwayModels->insert($keys);
    }

    $message = [
        'message' => 'Your Order has been Placed Successfully',
        'order_id' => $ord_id,
        'status' => 200
    ];
    return $this->response->setStatusCode(200)->setJSON(['messageobject' => $message, 'data' => "Your Order has been Placed Successfully"]);
}
// ...existing code...

    public function getNotifications()
    {
        $notifyModel = new NotificationModel();

        $user_id = $this->request->getGet('user_id');
        $reponse = $notifyModel->where('userId', $user_id)->findAll();
        if ($reponse) {
          
            $message = [
                'message' => 'Fetched Successfully ',
                'status' => 200
            ];
            return $this->response->setStatusCode(200)->setJSON(['messageobject' => $message, 'data' => $reponse]);
        } else {
            $message = [
                'message' => 'No data found',
                'status' => 200
            ];
            return $this->response->setStatusCode(200)->setJSON(['messageobject' => $message, 'data' => $reponse]);
        }


    }

    public function deleteNotifications(){

        $notifyModel = new NotificationModel();
        $notification_id = $this->request->getGet('id');
      $isDeleted=  $notifyModel->where('id', $notification_id)->delete();

      if ($isDeleted) {
        $message = [
            'message' => 'Deleted Successfully ',
            'status' => 200
        ];
        return $this->response->setStatusCode(200)->setJSON(['messageobject' => $message,]);
      } else {
        $message = [
            'message' => 'No data found',
            'status' => 200
        ];
      }
      


    }

    public function transformProducts($cardModelsResponse, $productModel)
    {
        $total_cost = 0;
        // var_dump($cardModelsResponse); 

       
        $transformedProducts = [];
        foreach ($cardModelsResponse as $crtlist) {
         
            $product = $productModel->find($crtlist['product_id']);
            $taxModel = new TaxesModel();
            $taxes = $taxModel->where('id', $product['tax_id'])->first();
            $amt = $crtlist['qty'] * ($product['price']+((int)$taxes['value'] - (int)$product['discount_value']));
            
            $total_cost += $amt;
        }
        $transformedProducts['total'] = $total_cost;
       
        return $transformedProducts;
    }

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
                'message' => 'successfully updated',
                'status' => 200
            ];
            return $this->response->setStatusCode(200)->setJSON(['messageobject' => $message, 'data' => "successfully updated"]);

        } else {
            $message = [
                'message' => 'internal server error',
                'status' => 400
            ];
            return $this->response->setStatusCode(400)->setJSON(['messageobject' => $message, 'data' => "internal server error"]);
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
        $orderGrpModel = new Order_Groups_Model();
        $OrdersModels = new OrdersModel();
        $CustomerId = $this->request->getGet('user_id');
        $AllOrderList = [];
        if (isset($CustomerId)) {
            $OrdersModels->where('user_id', $CustomerId);
            $cardModelsResponse = $OrdersModels->findAll();
            $AllOrderList = [];
            foreach ($cardModelsResponse as $ordergrp) {
                $orderGrpModel->where('id', $ordergrp['order_group_id']);
                $orderGrp = $orderGrpModel->first();
                $ordergrp['order_group'] = $orderGrp;
                $AllOrderList[] = $ordergrp;
            }
            $message = [
                'message' => 'Success fetch',
                'status' => 200
            ];

            return $this->response->setStatusCode(200)->setJSON(['messageobject' => $message, 'data' => $AllOrderList]);
        } else {
            $message = [
                'message' => 'Invalid user id',
                'status' => 400
            ];
            return $this->response->setStatusCode(400)->setJSON(['message' => $message]);
        }
    }
    public function getCancelledOrders()
    {
        $orderGrpModel = new Order_Groups_Model();
        $OrdersModels = new OrdersModel();
        $CustomerId = $this->request->getGet('user_id');
        $AllOrderList = [];
        if (isset($CustomerId)) {
            $OrdersModels->where('user_id', $CustomerId);
            $cardModelsResponse = $OrdersModels->where('delivery_status', 'Refunded')->findAll();
            $AllOrderList = [];
            foreach ($cardModelsResponse as $ordergrp) {
                $orderGrpModel->where('id', $ordergrp['order_group_id']);
                $orderGrp = $orderGrpModel->first();
                $ordergrp['order_group'] = $orderGrp;
                $AllOrderList[] = $ordergrp;
            }
            $message = [
                'message' => 'Success fetch',
                'status' => 200
            ];

            return $this->response->setStatusCode(200)->setJSON(['messageobject' => $message, 'data' => $AllOrderList]);
        } else {
            $message = [
                'message' => 'Invalid user id',
                'status' => 400
            ];
            return $this->response->setStatusCode(400)->setJSON(['message' => $message]);
        }
    }
    // public function trackOrderlist()
    // {
    //     $order_id = $this->request->getGet('order_id');
    //     $OrdersModels = new OrdersModel();
    //     $OrdersItemsModels = new Order_Items_Model();
    //     $ProductVariationsModels = new ProductVariationsModel();
    //     $ProductsModels = new ProductsModel();
    //     $OrdersModels->where('id', $order_id);
    //     $orderlist = $OrdersModels->first();

    //     $OrdersItemsModels->where('order_id', $order_id);
    //     $orderItemlist = $OrdersItemsModels->findAll();

    //     $products = [];

    //     foreach ($orderItemlist as $list) {
    //         $ProductVariationsModels->where('id', $list['product_variation_id']);
    //         $provarlist = $ProductVariationsModels->first();
    //         $ProductsModels->where('id', $provarlist['product_id']);
    //         $products[] = $ProductsModels->first();
    //     }

    //     $orderlist['productslist'] = $products;

    //     $message = [
    //         'message' => 'Success fetch',
    //         'status' => 200
    //     ];
    //     return $this->response->setStatusCode(200)->setJSON(['messageobject' => $message, 'data' => $orderlist]);
    // }



// public function trackOrderlist()
// {
//     $order_id = $this->request->getGet('order_id');
//     $OrdersModels = new OrdersModel();
//     $OrdersItemsModels = new Order_Items_Model();
//     $ProductVariationsModels = new ProductVariationsModel();
//     $ProductsModels = new ProductsModel();
//     $MediaManagersModel = new Media_managersModel();
//     $TaxesModels = new TaxesModel();
//     $ShopsModel = new ShopsModel();
//     $ordergroupModel = new Order_Groups_Model();
//     $OrdersModels->where('id', $order_id);
//     $orderlist = $OrdersModels->first();

//     $OrdersItemsModels->where('order_id', $order_id);
//     $orderItemlist = $OrdersItemsModels->findAll();

//     $products = [];

//     $total_cost = 0;
//     $totalDiscount = 0;
//     $totalTax = 0;

//     foreach ($orderItemlist as $list) {
//         $ProductVariationsModels->where('id', $list['product_variation_id']);
//         $provarlist = $ProductVariationsModels->first();
//         $ProductsModels->where('id', $provarlist['product_id']);
//         $product = $ProductsModels->first();
        
       
//         $MediaManagersModel->where('id', $provarlist['thumbnail_image']);
//         $thumbnail = $MediaManagersModel->first();
//         $provarlist['thumbnail_image'] = $thumbnail['media_file'];
        
//         if ($provarlist['tax_id']) {
//             $taxData = $TaxesModels->where('id', $provarlist['tax_id'])->first();
//             $taxValue = $taxData['value'];
//         } else {
//             $taxValue = 0; 
//         }


//         $discountAmount = (($list['qty'] * $provarlist['price']) * $provarlist['discount_value']) / 100;
//         $totalDiscount += $discountAmount;

    
//         $taxAmount = ($list['qty'] * $provarlist['price'] * $taxValue) / 100;
//         $totalTax += $taxAmount;


//         $amt = $list['qty'] * $provarlist['price'];
//         $total_cost += $amt;

    
//         // $ShopsModel->where('id', $provarlist['shop_id']);
//         // $shops = $ShopsModel->findAll();
//         $ordergroupModel->where('id', $orderlist['order_group_id']);
//         $ordergroup = $ordergroupModel->first();
//         $provarlist['qty'] = $list['qty'];
//         $provarlist['name'] = $product['name']; 
  
        
//         $orderlist['discount_value'] = (float) $ordergroup['total_discount_amount'];
//         $provarlist['tax_amount'] = $taxAmount;
//         $provarlist['total_cost'] = $amt; 
//         $products[] = $product;
//     }
//     $ordergroupModel->where('id', $orderlist['order_group_id']);
//     $ordergroup = $ordergroupModel->first();

//     $orderlist['productslist'] = [$provarlist];

//     $orderlist['total_cost'] = $total_cost;
//     $orderlist['total_tax'] = $totalTax;
    
// $orderlist['discount_value'] = (float) $ordergroup['total_discount_amount'];


//     $TotalAmount = ($totalTax - $totalDiscount) + $total_cost;

//     $orderlist['TotalAmount'] = $TotalAmount;
    


//     $message = [
//         'message' => 'Success fetch',
//         'status' => 200
//     ];
//     return $this->response->setStatusCode(200)->setJSON(['messageobject' => $message, 'data' => $orderlist]);
// }



// public function trackOrderlist()
// {
//     $order_id = $this->request->getGet('order_id');
//     $OrdersModels = new OrdersModel();
//     $OrdersItemsModels = new Order_Items_Model();
//     $ProductVariationsModels = new ProductVariationsModel();
//     $ProductsModels = new ProductsModel();
//     $MediaManagersModel = new Media_managersModel();
//     $TaxesModels = new TaxesModel();
//     $ShopsModel = new ShopsModel();
//     $ordergroupModel = new Order_Groups_Model();
//    $orderlist =  $OrdersModels->where('id', $order_id)->first();
//     // $OrdersModels->first();

//     $OrdersItemsModels->where('order_id', $order_id);
//     $orderItemlist = $OrdersItemsModels->findAll();

//     $products = [];
//     $productslist = []; // Initialize an array to store all product details

//     $total_cost = 0;
//     $totalDiscount = 0;
//     $totalTax = 0;

//     foreach ($orderItemlist as $list) {
//         $ProductVariationsModels->where('id', $list['product_variation_id']);
//         $provarlist = $ProductVariationsModels->first();
//         $ProductsModels->where('id', $provarlist['product_id']);
//         $product = $ProductsModels->first();
        
//         $MediaManagersModel->where('id', $provarlist['thumbnail_image']);
//         $thumbnail = $MediaManagersModel->first();
//         $provarlist['thumbnail_image'] = $thumbnail['media_file'];
        
//         if ($provarlist['tax_id']) {
//             $taxData = $TaxesModels->where('id', $provarlist['tax_id'])->first();
//             $taxValue = $taxData['value'];
//         } else {
//             $taxValue = 0; 
//         }

//         $discountAmount = (($list['qty'] * $provarlist['price']) * $provarlist['discount_value']) / 100;
//         $totalDiscount += $discountAmount;

//         $taxAmount = ($list['qty'] * $provarlist['price'] * $taxValue) / 100;
//         $totalTax += $taxAmount;

//         $amt = $list['qty'] * $provarlist['price'];
//         $total_cost += $amt;

//         $ordergroupModel->where('id', $orderlist['order_group_id']);
//         $ordergroup = $ordergroupModel->first();
//         $provarlist['qty'] = $list['qty'];
//         $provarlist['name'] = $product['name']; 
  
//         $orderlist['discount_value'] = (float) $ordergroup['total_discount_amount'];
//         $provarlist['tax_amount'] = $taxAmount;
//         $provarlist['total_cost'] = $amt; 

//         $productslist[] = $provarlist; // Append the product variation details to the list
//     }

//     $ordergroupModel->where('id', $orderlist['order_group_id']);
//     $ordergroup = $ordergroupModel->first();

//     $orderlist['productslist'] = $productslist; // Assign the full list of products

//     $orderlist['total_cost'] = $total_cost;
//     $orderlist['total_tax'] = $totalTax;
//     $orderlist['discount_value'] = (float) $ordergroup['total_discount_amount'];

//     $TotalAmount = ($totalTax - $totalDiscount) + $total_cost;
//     $orderlist['TotalAmount'] = $TotalAmount;

//     $message = [
//         'message' => 'Success fetch',
//         'status' => 200
//     ];
//     return $this->response->setStatusCode(200)->setJSON(['messageobject' => $message, 'data' => $orderlist]);
// }

// public function trackOrderReturn()
// {
//     $json = $this->request->getJSON();

//     $order_id = $json->order_id;
//     $product_variation_ids = $json->product_variation_ids;

//     if (!is_array($product_variation_ids) || empty($product_variation_ids)) {
//         return $this->response->setStatusCode(400)->setJSON([
//             'messageobject' => [
//                 'message' => 'Invalid or missing product_variation_ids',
//                 'status' => 400
//             ]
//         ]);
//     }

//     $OrdersModels = new OrdersModel();
//     $OrdersItemsModels = new Order_Items_Model();
//     $OrderReturnModel = new OrderReturnModel();
//     $OrderReturnItemModel = new OrderReturnItemModel();
//     $ProductVariationsModels = new ProductVariationsModel();

    
//     $OrdersModels->where('id', $order_id);
//     $order = $OrdersModels->first();

//     if (!$order) {
//         return $this->response->setStatusCode(404)->setJSON([
//             'messageobject' => [
//                 'message' => 'Order not found',
//                 'status' => 404
//             ]
//         ]);
//     }

   
//     $ProductVariationsModels->whereIn('id', $product_variation_ids);
//     $productVariations = $ProductVariationsModels->findAll();

//     if (empty($productVariations)) {
//         return $this->response->setStatusCode(404)->setJSON([
//             'messageobject' => [
//                 'message' => 'No product variations found for the provided product_ids',
//                 'status' => 404
//             ]
//         ]);
//     }

   
//     $OrdersItemsModels->where('order_id', $order_id);
//     $orderItems = $OrdersItemsModels->findAll();

//     if (empty($orderItems)) {
//         return $this->response->setStatusCode(404)->setJSON([
//             'messageobject' => [
//                 'message' => 'No items found for the given order',
//                 'status' => 404
//             ]
//         ]);
//     }

//     // print_r($orderItems);
//     // die;
//     $productVariationIds = array_column($productVariations, 'id');

   
//     $returnedItems = array_filter($orderItems, function ($item) use ($productVariationIds) {
//         return in_array($item['product_variation_id'], $productVariationIds);
//     });

//     if (empty($returnedItems)) {
//         return $this->response->setStatusCode(400)->setJSON([
//             'messageobject' => [
//                 'message' => 'No matching items found for the provided product_ids',
//                 'status' => 400
//             ]
//         ]);
//     }
// $OrdersModels->set([
//     'is_return'=>'1',
//     'delivery_status'=>'Cancelled',
// ])->where('id', $order_id)->update();
    
//     $returnData = [
//         'order_id' => $order_id,
//         'return_date' => date('Y-m-d H:i:s'),
//         'return_status' => 'pending', 
//         'return_type'=>'customer',
//         'created_at' => date('Y-m-d H:i:s'),
//         'updated_at' => date('Y-m-d H:i:s')
//     ];
//     $return_id = $OrderReturnModel->insert($returnData);

    
//     foreach ($returnedItems as $item) {
//         $returnItemData = [
//             'order_returns_id' => $return_id,
//             'product_variation_id' => $item['product_variation_id'],
//             'order_item_id' => $item['id'],
//             'returned_quantity' => $item['qty'],
//             'returned_amount' => $item['total_price'],
//             'discount' => $item['discount'],
//             'tax_id' => $item['tax_id'],
//             'unit_price' => $item['unit_price'],
//             'total_price' => $item['total_price'],
//             'created_at' => date('Y-m-d H:i:s'),
//             'updated_at' => date('Y-m-d H:i:s')
//         ];
//         $OrderReturnItemModel->insert($returnItemData);
//     }

//     return $this->response->setStatusCode(200)->setJSON([
//         'messageobject' => [
//             'message' => 'Return processed successfully',
//             'status' => 200
//         ],
//         'data' => [
//             'return_id' => $return_id,
//             'returned_items' => $returnedItems
//         ]
//     ]);
// }



public function trackOrderReturn()
{
    $json = $this->request->getJSON();

    $order_id = $json->order_id;
    $product_variation_ids = $json->product_variation_ids;

    if (!is_array($product_variation_ids) || empty($product_variation_ids)) {
        return $this->response->setStatusCode(400)->setJSON([
            'messageobject' => [
                'message' => 'Invalid or missing product_variation_ids',
                'status' => 400
            ]
        ]);
    }
    

    $OrdersModels = new OrdersModel();
    $OrdersItemsModels = new Order_Items_Model();
    $productModel = new ProductsModel();
    $OrderReturnModel = new OrderReturnModel();
    $OrderReturnItemModel = new OrderReturnItemModel();
    $SaleOrderModels = new SaleOrderModel();
    $ProductVariationsModels = new ProductVariationsModel();
     $OrdersModels->where('id', $order_id);
    $order = $OrdersModels->first();
$currentDate = new DateTime();
    $orderDate = new DateTime(isset($order['created_at']) ? $order['created_at'] : $currentDate);

foreach ($product_variation_ids as $product_variation_id) {

        $productData = $ProductVariationsModels->where('id', $product_variation_id)->first();

        if ($productData['is_return']=='1') {
            $p = $productModel->find($productData['product_id']);
            return $this->response->setStatusCode(400)->setJSON([
                'messageobject' => [
                    'message' => 'Product ' . $p['name'] . ' this product is not returnable',
                    'status' => 400
                ]
            ]);
        }
        $interval = $orderDate->diff($currentDate)->days;
        if($productData['return_policy_days']>= $interval){
 $p = $productModel->find($productData['product_id']);
            return $this->response->setStatusCode(400)->setJSON([
                'messageobject' => [
                    'message' => 'Product ' . $p['name'] . 'Returnable period expired',
                    'status' => 400
                ]
            ]);

        }
    }
   
   

    if (!$order) {
        return $this->response->setStatusCode(404)->setJSON([
            'messageobject' => [
                'message' => 'Order not found',
                'status' => 404
            ]
        ]);
    }

    // --- Return allowed only within 5 days of order date ---
    // $orderDate = 
    // if ($orderDate) {
    //     $orderDateTime = new DateTime($orderDate);
    //     $now = new DateTime();
    //     $interval = $orderDateTime->diff($now)->days;
    //     if ($interval > 5) {
    //         return $this->response->setStatusCode(400)->setJSON([
    //             'messageobject' => [
    //                 'message' => 'Return period expired. Returns are only allowed within 5 days of order date.',
    //                 'status' => 400
    //             ]
    //         ]);
    //     }
    // }

    $ProductVariationsModels->whereIn('id', $product_variation_ids);
    $productVariations = $ProductVariationsModels->findAll();

    if (empty($productVariations)) {
        return $this->response->setStatusCode(404)->setJSON([
            'messageobject' => [
                'message' => 'No product variations found for the provided product_ids',
                'status' => 404
            ]
        ]);
    }

    $OrdersItemsModels->where('order_id', $order_id);
    $orderItems = $OrdersItemsModels->findAll();
$SaleOrderModels->set([
            'order_id' => $order_id,
            
        ])->where('id', $order_id)->update();
    if (empty($orderItems)) {
        return $this->response->setStatusCode(404)->setJSON([
            'messageobject' => [
                'message' => 'No items found for the given order',
                'status' => 404
            ]
        ]);
    }

    $productVariationIds = array_column($productVariations, 'id');

    $returnedItems = array_filter($orderItems, function ($item) use ($productVariationIds) {
        return in_array($item['product_variation_id'], $productVariationIds);
    });

    if (empty($returnedItems)) {
        return $this->response->setStatusCode(400)->setJSON([
            'messageobject' => [
                'message' => 'No matching items found for the provided product_ids',
                'status' => 400
            ]
        ]);
    }

    $returnData = [
        'order_id' => $order_id,
        'sr_number' => $order['saleorder_id'] ?? null, 
        'return_date' => date('Y-m-d H:i:s'),
        'return_status' => 'pending', 
        'return_type'=>'customer',
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    ];
    $return_id = $OrderReturnModel->insert($returnData);

    foreach ($returnedItems as $item) {
        $returnItemData = [
            'order_returns_id' => $return_id,
            'product_variation_id' => $item['product_variation_id'],
            'order_item_id' => $item['id'],
            'returned_quantity' => $item['qty'],
            'returned_amount' => $item['total_price'],
            'discount' => $item['discount'],
            'tax_id' => $item['tax_id'],
            'unit_price' => $item['unit_price'],
            'total_price' => $item['total_price'],
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        $OrderReturnItemModel->insert($returnItemData);
    }

    // Check if all items have been returned
    $allOrderItemIds = array_column($orderItems, 'product_variation_id');
    $OrderReturnItemModel->whereIn('order_returns_id', function($builder) use ($order_id) {
        $builder->select('id')->from('order_returns')->where('order_id', $order_id);
    });
    $allReturnedItemIds = $OrderReturnItemModel->select('product_variation_id')->findAll();
    $allReturnedItemIds = array_column($allReturnedItemIds, 'product_variation_id');
    $remainingItems = array_diff($allOrderItemIds, $allReturnedItemIds);
  $OrdersModels->set([
            'is_return' => '1',
           
        ])->where('id', $order_id)->update();
    if (empty($remainingItems)) {
        $OrdersModels->set([
            'is_return' => '1',
            'delivery_status' => 'Cancelled',
        ])->where('id', $order_id)->update();
    }

    return $this->response->setStatusCode(200)->setJSON([
        'messageobject' => [
            'message' => 'Return processed successfully',
            'status' => 200
        ],
        'data' => [
            'return_id' => $return_id,
            'returned_items' => $returnedItems
        ]
    ]);
}

public function trackOrderlist()
{
    $order_id = $this->request->getGet('order_id');
    $OrdersModels = new OrdersModel();
    $OrdersItemsModels = new Order_Items_Model();
    $ProductVariationsModels = new ProductVariationsModel();
    $ProductsModels = new ProductsModel();
    $MediaManagersModel = new Media_managersModel();
    $TaxesModels = new TaxesModel();
    $ShopsModel = new ShopsModel();
    $ordergroupModel = new Order_Groups_Model();
    $OrderReturnItemModel = new OrderReturnItemModel(); // Add this line

    $orderlist =  $OrdersModels->where('id', $order_id)->first();

    $OrdersItemsModels->where('order_id', $order_id);
    $orderItemlist = $OrdersItemsModels->findAll();

    $productslist = [];

    $total_cost = 0;
    $totalDiscount = 0;
    $totalTax = 0;

    foreach ($orderItemlist as $list) {
        $ProductVariationsModels->where('id', $list['product_variation_id']);
        $provarlist = $ProductVariationsModels->first();
        $ProductsModels->where('id', $provarlist['product_id']);
        $product = $ProductsModels->first();

        $MediaManagersModel->where('id', $provarlist['thumbnail_image']);
        $thumbnail = $MediaManagersModel->first();
        $provarlist['thumbnail_image'] = $thumbnail['media_file'];

        if ($provarlist['tax_id']) {
            $taxData = $TaxesModels->where('id', $provarlist['tax_id'])->first();
            $taxValue = $taxData['value'];
        } else {
            $taxValue = 0;
        }

        $discountAmount = (($list['qty'] * $provarlist['price']) * $provarlist['discount_value']) / 100;
        $totalDiscount += $discountAmount;

        $taxAmount = ($list['qty'] * $provarlist['price'] * $taxValue) / 100;
        $totalTax += $taxAmount;

        $amt = $list['qty'] * $provarlist['price'];
        $total_cost += $amt;

        $ordergroupModel->where('id', $orderlist['order_group_id']);
        $ordergroup = $ordergroupModel->first();
        $provarlist['qty'] = $list['qty'];
        $provarlist['name'] = $product['name'];

        $orderlist['discount_value'] = (float) $ordergroup['total_discount_amount'];
        $provarlist['tax_amount'] = $taxAmount;
        $provarlist['total_cost'] = $amt;

        // Check if this product is returned
        $isReturned = $OrderReturnItemModel
            ->where('order_item_id', $list['id'])
            ->where('product_variation_id', $list['product_variation_id'])
            ->first() ? true : false;
        $provarlist['is_returned'] = $isReturned;

        $productslist[] = $provarlist;
    }

    $ordergroupModel->where('id', $orderlist['order_group_id']);
    $ordergroup = $ordergroupModel->first();

    $orderlist['productslist'] = $productslist;
    $orderlist['total_cost'] = $total_cost;
    $orderlist['total_tax'] = $totalTax;
    $orderlist['discount_value'] = (float) $ordergroup['total_discount_amount'];

    $TotalAmount = ($totalTax - $totalDiscount) + $total_cost;
    $orderlist['TotalAmount'] = $TotalAmount;

    $message = [
        'message' => 'Success fetch',
        'status' => 200
    ];
    return $this->response->setStatusCode(200)->setJSON(['messageobject' => $message, 'data' => $orderlist]);
}
}


