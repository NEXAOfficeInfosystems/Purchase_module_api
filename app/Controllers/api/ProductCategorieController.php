<?php
namespace App\Controllers\Api;
use App\Models\api\UnitConversationsModel;
use App\Models\api\BrandsModel;
use App\Models\Customer\VariationMasterModel;
use CodeIgniter\RESTful\ResourceController;
use App\Models\Customer\CategoriesModel;
use App\Models\Customer\ProductsModel;
use App\Models\Customer\ProductcategoriesModel;
use App\Models\api\ProductVariationsModel;
use App\Models\Customer\Media_managersModel;
use App\Models\Customer\WishlistsModel;
use App\Models\api\Order_Items_Model;
use App\Models\api\ProductSelectedVariationsModel;
use App\Models\api\VariationTypeModel;
use App\Models\api\BulkOrderItemModel;
use App\Models\api\ProductVariationStocksModel;

// 
use App\Models\api\Bulk_Order_RequestModel;
use App\Models\Customer\UserAddressesModel;







class ProductCategorieController extends ResourceController
{

    public function index()
    {
        $ProductModel = new ProductsModel();
        $UnitConversationsModels = new UnitConversationsModel();
        $ProductCategoriesModels = new ProductCategoriesModel();
        $BrandsModels = new BrandsModel();
        $data = $ProductModel->findAll();
        $transformedProducts = $this->transformProducts($data, $UnitConversationsModels, $ProductCategoriesModels, $BrandsModels);
        return $this->respond($transformedProducts);
    }

    protected function transformProducts($products, $UnitConversationsModels, $ProductCategoriesModels, $brandsModels)
    {
        $transformedProducts = [];
        foreach ($products as $product) {
            $UnitConversationsModel = $UnitConversationsModels->find($product['UnitId']);
            $ProductCategoriesModel = $ProductCategoriesModels->find($product['CategoryId']);
            $brandsModel = $brandsModels->find($product['BrandId']);
            $transformedProducts[] = [
                'Id' => $product['Id'],
                'Name' => $product['Name'],
                'Code' => $product['Code'],
                'Barcode' => $product['Barcode'],
                'SkuCode' => $product['SkuCode'],
                'SkuName' => $product['SkuName'],
                'Description' => $product['Description'],
                'ProductUrl' => $product['ProductUrl'],
                'QRCodeUrl' => $product['QRCodeUrl'],
                'UnitId' => $product['UnitId'],
                'Unit_name' => $UnitConversationsModel ? $UnitConversationsModel['Name'] : null,
                'PurchasePrice' => $product['PurchasePrice'],
                'SalesPrice' => $product['SalesPrice'],
                'Mrp' => $product['Mrp'],
                'CategoryId' => $product['CategoryId'],
                'CategoryName' => $ProductCategoriesModel ? $ProductCategoriesModel['Name'] : null,
                'BrandName' => $brandsModel ? $brandsModel['Name'] : null,
                'WarehouseId' => $product['WarehouseId'],
                'CreatedDate' => $product['CreatedDate'],
                'CreatedBy' => $product['CreatedBy'],
                'ModifiedDate' => $product['ModifiedDate'],
                'ModifiedBy' => $product['ModifiedBy'],
                'DeletedDate' => $product['DeletedDate'],
                'DeletedBy' => $product['DeletedBy'],
                'IsDeleted' => $product['IsDeleted']
            ];
        }

        return $transformedProducts;
    }

    public function list()
    {
        $ProductCategoriesModels = new ProductCategoriesModel();
        $ProductCategoriesModels->where('IsDeleted', 0);
        $data = $ProductCategoriesModels->find();
        $index = 1;
        foreach ($data as $dd) {
           
            $arr = array("a" => "furnishing", "b" => "fashions", "c" => "groceries", "d" => "farm-fresh-produce");
            $dataset['data'][] = [
                "id" => $index,
                "Ids" => $dd['Id'],
                "index" => $index,// Use the loop counter $i as the ID
                "name" => $dd['Name'],
                "slug" => $dd['Description'],
                "description" => "Furniture encompasses a wide range of functional and decorative items designed to enhance living and working spaces. It includes various pieces that serve practical purposes while contributing to the overall aesthetics and ambiance of a room or environment. Furniture is an essential aspect of interior design and plays a crucial role in creating comfortable and functional living, working, and recreational spaces.",
                "category_image_id" => null,
                "category_icon_id" => null,
                "status" => 1,
                "type" => "product",
                "commission_rate" => null,
                "parent_id" => null,
                "created_by_id" => "1",
                "created_at" => "2023-08-31T06:32:22.000000Z",
                "updated_at" => "2023-08-31T06:32:22.000000Z",
                "deleted_at" => null,
                "blogs_count" => 6,
                "products_count" => 0,
                "category_image" => $dd['category_image'],
                "category_icon" => null,
                "subcategories" => [],
                "parent" => null
            ];

            $index += 1;
        }
        return $this->respond($dataset);
    }

    public function cat_product()
    {
        $cat_id = $this->request->getGet('id');
        $ProductCategoriesModels = new ProductCategoriesModel();
        $joinCondition = 'ProductCategories.Id = Products.CategoryId';

        $results = $ProductCategoriesModels->select('Products.Id as product_id , Products.Name as product_name ,  ProductCategories.Id as cat_id, ProductCategories.Name as cat_name')
            ->join('Products', $joinCondition)
            ->where('Products.CategoryId', $cat_id)
            ->findAll();

        return $this->respond($results);
    }


    //=======================
    public function getCategories()
    {
        try {
            $CategoriesModels = new CategoriesModel();
            $mediaModel = new Media_managersModel();
            $data = $CategoriesModels->findAll();
            $resposneData = [];

            foreach ($data as $resposne) {

                $img = $mediaModel->find($resposne['thumbnail_image']);

                $resposneData[] = [

                    'id' => $resposne['id'],
                    'name' => $resposne['name'],
                    'slug' => $resposne['slug'],
                    'description' => $resposne['description'],
                    'category_image_id' => null,
                    'category_icon_id' => null,
                    'status' => 1,
                    'type' => "post",
                    'commission_rate' => null,
                    'parent_id' => $resposne['parent_id'],
                    'created_by_id' => "1",
                    'created_at' => $resposne['created_at'],
                    'updated_at' => $resposne['updated_at'],
                    'deleted_at' => null,
                    'blogs_count' => 6,
                    'products_count' => 0,
                    'thumbnail_image' => $resposne['thumbnail_image'],
                    'thumbnail_image_url' => isset($img['media_file']) ? $img['media_file'] : null,
                    'category_icon' => null,
                    'subcategories' => [],
                    'parent' => null,
                ];
            }
            $data = $resposneData;

            $message = [
                'message' => 'Success!',
                'status' => 200
            ];

            return $this->response->setStatusCode(200)->setJSON(['messageobject' => $message, 'data' => $data]);
        } catch (\Exception $e) {
            $message = [
                'message' => $e->getMessage(),
                'status' => 500
            ];
            return $this->response->setStatusCode(500)->setJSON(['messageobject' => $message]);
        }
    }

    public function getProducts()
    {
        try {
            $productsModel = new ProductsModel();
            $variationModel = new VariationMasterModel();
            $productVariationsModel = new ProductVariationsModel();
            $productSelectedVariationsModel = new ProductSelectedVariationsModel();
            $variationMasterModel = new VariationTypeModel();
            $mediaModel = new Media_managersModel();
            $productvarstockModel = new ProductVariationStocksModel();
    
            $productsModel
            // ->where('stock_qty >', 0)
                          ->where('is_published', 1)
                          ->where('is_deleted', 0);
            $products = $productsModel->findAll();
    
            $responseData = [];
    
            foreach ($products as $product) {
                $mediaModel->where('id', $product['thumbnail_image']);
                $imageUrl = $mediaModel->findAll();
                $thumbnailImageUrl = isset($imageUrl[0]['media_file']) ? $imageUrl[0]['media_file'] : null;
    
                $productVariations = $productVariationsModel
                    // ->select('id,barcodes, sku, price, thumbnail_image, stock_qty')
                    ->where('product_id', $product['id'])
                    ->findAll();
    
                $variationThumbnails = [];
                $variationsDetails = [];
                foreach ($productVariations as $variation) {


                    $stock = $productvarstockModel
                    ->where('product_variation_id', $variation['id'])
                    ->first();
        
                $dataaa = $stock ? $stock['stock_qty'] : 0;
        
                $totalStockQty = $dataaa;


                    if (!empty($variation['thumbnail_image'])) {
                        $mediaModel->where('id', $variation['thumbnail_image']);
                        $variationImage = $mediaModel->findAll();
                        $variation['thumbnail_image_url'] = isset($variationImage[0]['media_file']) ? $variationImage[0]['media_file'] : null;
    
                        if ($variation['thumbnail_image_url']) {
                            $variationThumbnails[] = $variation['thumbnail_image_url'];
                        }
                    } else {
                        $variation['thumbnail_image_url'] = null;
                    }
    
                    $selectedVariations = $productSelectedVariationsModel
                        ->select('variation_type_id, selected_value')
                        ->where('product_variation_id', $variation['id'])
                        ->where('product_id', $product['id'])
                        ->findAll();
                    log_message('debug', 'Selected Variations for Variation ID ' . $variation['id'] . ': ' . json_encode($selectedVariations));
                    $stockData = $productvarstockModel->where('product_variation_id', $variation['id'])->first();
              
              
              
                    $variationDetails = [];
                    foreach ($selectedVariations as $selected) {
                       
                        $variationType = $variationModel
                            ->select('id, variation_type, display_name')
                            ->where('variation_type_id', $selected['variation_type_id'])
                            ->first();
                           
    
                        if ($variationType) {
                            $variationDetails[] = [
                                'variation_id' => (string)$selected['variation_type_id'],
                                'variation_type' => $variationType['variation_type'],
                                'display_name' => $variationType['display_name'],
                                'selected_value' => $selected['selected_value'],
                            ];
                        }
                        
                    }
                    log_message('debug', 'Variation Details for Variation ID ' . $variation['id'] . ': ' . json_encode($variationDetails));
    
                    // $variationsDetails[] = [
                    //     'product_variation_id' => $variation['id'],
                    //     'sku' => $variation['sku'],
                    //     'barcode'=>$variation['barcodes'],
                    //     'price' => $variation['price'],
                    //     'thumbnail_image' => $variation['thumbnail_image_url'] ?? $thumbnailImageUrl,
                    //     'stock_qty' => $variation['stock_qty'],
                    //     'selected_variations' => $variationDetails
                    // ];

                    $variationsDetails[] = [
                        'product_variation_id' => $variation['id'],
                   
                        'selected_variations' => $variationDetails,
                        'sku' => $variation['sku'],
                        'barcodes' => $variation['barcodes'],
                        'code' => $variation['code'] ?? NULL,
                        'price' => $variation['price'],
                        'stock_qty' => $totalStockQty,
                    
                        'thumbnail_image' => $variation['thumbnail_image_url'] ?? $thumbnailImageUrl,
                        'created_at' => $variation['created_at'] ?? NULL,
                        'updated_at' => $variation['updated_at'] ?? NULL,
                        'deleted_at' => $variation['deleted_at'] ?? NULL,
                        'is_active' => $variation['is_active'] ?? 1,
                        'capacity' => $variation['capacity'] ?? NULL,
                        'short_description' => $variation['short_description'] ?? NULL,
                        'discount_value' => $variation['discount_value'] ?? 0,
                        'discount_type' => $variation['discount_type'] ?? NULL,
                        'discount_start_date' => $variation['discount_start_date'] ?? NULL,
                        'discount_end_date' => $variation['discount_end_date'] ?? NULL,
                        'sell_target' => $variation['sell_target'] ?? NULL,
                        'is_published' => $variation['is_published'] ?? 0,
                        'is_featured' => $variation['is_featured'] ?? 0,
                        'min_purchase_qty' => $variation['min_purchase_qty'] ?? 1,
                        'max_purchase_qty' => $variation['max_purchase_qty'] ?? 1,
                        'has_warranty' => $variation['has_warranty'] ?? 1,
                        'total_sale_count' => $variation['total_sale_count'] ?? 0,
                        'standard_delivery_hours' => $variation['standard_delivery_hours'] ?? 24,
                        'express_delivery_hours' => $variation['express_delivery_hours'] ?? 24,
                        'reward_points' => $variation['reward_points'] ?? 0,
                        'meta_title' => $variation['meta_title'] ?? NULL,
                        'meta_description' => $variation['meta_description'] ?? NULL,
                        'meta_img' => $variation['meta_img'] ?? NULL,
                        'brand_id' => $variation['brand_id'] ?? NULL,
                        'tax_id' => $variation['tax_id'] ?? NULL,
                        'unit_id' => $variation['unit_id'] ?? NULL,
                        'min_quantity_wholesale' => $variation['min_quantity_wholesale'] ?? NULL,
                        'max_quantity_wholesale' => $variation['max_quantity_wholesale'] ?? NULL,
                        'wholesale_discount' => $variation['wholesale_discount'] ?? NULL,
                        'wholesale_notes' => $variation['wholesale_notes'] ?? NULL,
                        'min_price' => $variation['min_price'] ?? 0,
                        'is_return'=>$variation['is_return'] ?? 0,
                        'return_days'=>$variation['return_policy_days'] ?? 0,
                          'name_arabic'=>$variation['name_arabic'],
        'short_description_arabic'=>$variation['short_description_arabic'],
        'wholesale_notes_arabic'=>$variation['wholesale_notes_arabic']
                    ];
                    // print_r( $variation);
                    // die;
                }
                $product['stock_qty'] = $totalStockQty; 
                $variationOptions = $productSelectedVariationsModel
                    ->select('variation_type_id, selected_value')
                    ->where('product_id', $product['id'])
                    ->findAll();
                log_message('debug', 'Variation Options for Product ID ' . $product['id'] . ': ' . json_encode($variationOptions));
    
                $variationTypes = [];
                foreach ($variationOptions as $option) {
                    $variationType = $variationModel
                        ->select('id, variation_type, display_name')
                        ->where('id', $option['variation_type_id'])
                        ->first();
                    log_message('debug', 'Variation Type for ID ' . $option['variation_type_id'] . ': ' . json_encode($variationType));
    
                    if ($variationType) {
                        $typeId = $variationType['id'];
                        if (!isset($variationTypes[$typeId])) {
                            $variationTypes[$typeId] = [
                                'variation_id' => (string)$typeId,
                                'variation_type' => $variationType['variation_type'],
                                'display_name' => $variationType['display_name'],
                                'values' => []
                            ];
                        }
                        if (!in_array($option['selected_value'], $variationTypes[$typeId]['values'])) {
                            $variationTypes[$typeId]['values'][] = $option['selected_value'];
                        }
                    }
                }
                log_message('debug', 'Variation Types for Product ID ' . $product['id'] . ': ' . json_encode($variationTypes));
    
                $stock_status = (intval($product['stock_qty']) > 0) ? 'in_stock' : 'out_stock';
    
                $responseData[] = [
                    'id' => $product['id'],
                    'name' => $product['name'],
                    'slug' => $product['slug'],
                    'description' => $product['description'],
                    'category_image_id' => null,
                    'category_icon_id' => null,
                    'status' => 1,
                    'type' => "product",
                    'commission_rate' => null,
                    'parent_id' => null,
                    'created_by_id' => "1",
                    'created_at' => $product['created_at'],
                    'updated_at' => $product['updated_at'],
                    'deleted_at' => null,
                    'blogs_count' => 6,
                    'products_count' => 0,
                    'thumbnail_image' => $product['thumbnail_image'],
                    'thumbnail_image_url' => $thumbnailImageUrl,
                    'category_icon' => null,
                    'subcategories' => [],
                    'parent' => null,
                    'short_description' => [],
                    'unit' => [],
                    'weight' => [],
                    'quantity' => $product['stock_qty'],
                    'sale_price' => $product['price'],
                    'price' => $product['price'],
                    'product_barcode'=>$product['barcode_image'],
                    'product_thumbnail' => $variationThumbnails,
                    'discount' => $product['discount_value'],
                    'is_featured' => $product['is_featured'],
                    'shipping_days' => [],
                    'is_cod' => [],
                    'is_free_shipping' => [],
                    'is_sale_enable' => [],
                    'is_return' => [],
                    'is_trending' => [],
                    'is_approved' => [],
                    'sale_starts_at' => [],
                    'sale_expired_at' => [],
                    'sku' => $product['sku'],
                    'is_random_related_products' => [],
                    'stock_status' => $stock_status,
                    'meta_title' => [],
                    'meta_description' => [],
                    'product_thumbnail_id' => [],
                    'product_meta_image_id' => [],
                    'size_chart_image_id' => [],
                    'estimated_delivery_text' => [],
                    'return_policy_text' => [],
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
                    'variation_options' => array_values($variationTypes),
                    'variations' => $variationsDetails,
                     'name_arabic'=>$product['name_arabic'],
        'short_description_arabic'=>$product['short_description_arabic'],
        'wholesale_notes_arabic'=>$product['wholesale_notes_arabic']
                ];
            }
    
            $message = [
                'message' => 'Success!',
                'status' => 200
            ];
    
            return $this->response->setStatusCode(200)->setJSON(['messageobject' => $message, 'data' => $responseData]);
        } catch (\Exception $e) {
            $message = [
                'message' => $e->getMessage(),
                'status' => 500
            ];
            return $this->response->setStatusCode(500)->setJSON(['messageobject' => $message]);
        }
    }
     /// ================ GETPRODUCTS COMPLETE ================

    public function getCategoriesWiseProductdetails()
    {
        try {
            $categoryId = $this->request->getGet('categoryId');
            $idsArray = array_map('intval', explode(',', $categoryId));
            if (isset($categoryId)) {
                $productCategoryModel = new ProductcategoriesModel();
                $products = $productCategoryModel->getProductsWithCategory($idsArray);
                if (count($products) > 0) {
                    $data = [];
                    foreach ($products as $response) {
                        $data[] = [
                            'id' => $response['id'],
                            'shop_id ' => $response['shop_id'],
                            'added_by ' => $response['added_by'],
                            'name ' => $response['name'],
                            'slug' => $response['slug'],
                            'brand_id' => $response['brand_id'],
                            'unit_id' => $response['unit_id'],
                            'thumbnail_image' => $response['thumbnail_image'],
                            'gallery_images' => $response['gallery_images'],
                            'product_tags' => $response['product_tags'],
                            'short_description' => $response['short_description'],
                            'description' => $response['description'],
                            'price' => $response['price'],
                            'min_price' => $response['min_price'],
                            'max_price' => $response['max_price'],
                            'discount_value' => $response['discount_value'],
                            'discount_type' => $response['discount_type'],
                            'discount_start_date' => $response['discount_start_date'],
                            'discount_end_date' => $response['discount_end_date'],
                            'sell_target' => $response['sell_target'],
                            'stock_qty' => $response['stock_qty'],
                            'is_published' => $response['is_published'],
                            'is_featured' => $response['is_featured'],
                            'min_purchase_qty' => $response['min_purchase_qty'],
                            'max_purchase_qty' => $response['max_purchase_qty'],
                            'min_stock_qty' => $response['min_stock_qty'],
                            'has_variation' => $response['has_variation'],
                            'has_warranty' => $response['has_warranty'],
                            'total_sale_count' => $response['total_sale_count'],
                            'standard_delivery_hours' => $response['standard_delivery_hours'],
                            'express_delivery_hours' => $response['express_delivery_hours'],
                            'size_guide' => $response['size_guide'],
                            'meta_description' => $response['meta_description'],
                            'meta_img' => $response['meta_img'],
                            'reward_points' => $response['reward_points'],
                            'created_at' => $response['created_at'],
                            'updated_at' => $response['updated_at'],
                            'deleted_at' => $response['deleted_at'],
                            'vedio_link' => $response['vedio_link'],
                            'created_by' => $response['created_by'],
                            'updated_by' => $response['updated_by'],
                            'is_import' => $response['is_import'],
                        ];
                    }

                    $message = [
                        'message' => 'successfully get data',
                        'status' => 200
                    ];
                    return $this->response->setStatusCode(200)->setJSON(['messageobject' => $message, 'data' => $data]);
                } else {
                    $message = [
                        'message' => 'productaId not found ',
                        'status' => 400
                    ];

                    return $this->response->setStatusCode(400)->setJSON(['messageobject' => $message]);
                }
            } else {
                $message = [
                    'message' => 'data not Found',
                    'status' => 400
                ];

                return $this->response->setStatusCode(400)->setJSON(['messageobject' => $message]);
            }
        } catch (\Exception $e) {
            $message = [
                'message' => $e->getMessage(),
                'status' => 500
            ];
            return $this->response->setStatusCode(500)->setJSON(['messageobject' => $message]);
        }

    }

    public function getwishlistProducts()
    {
        try {
            $user_id = $this->request->getGet('user_id');

            $wishlistModels = new WishlistsModel();

            $wishlistModels->where('user_id', $user_id);

            $wishlist = $wishlistModels->findAll();
            $product_id = [];
            if ($wishlist) {
                foreach ($wishlist as $list) {
                    $product_id[] = $list['product_variation_id'];
                }
                $ProductVariationsModel = new ProductVariationsModel();

                $ProductsModels = new ProductVariationsModel();
                $Products = new ProductsModel();
                $ProductsModels->whereIn('id', $product_id);
                $data = $ProductsModels->findAll();
                $resposneData = [];
                $mediaModel = new Media_managersModel();

                foreach ($data as $resposne) {
                    $mediaModel->where('id', $resposne['thumbnail_image']);
                    $imageUrl = $mediaModel->findAll();
                    $ProductsModelsdata = $ProductVariationsModel->find($resposne['id']);
                    $stock_status = (intval($resposne['stock_qty']) > 0) ? 'in_stock' : 'out_stock';
                $ProductData = $Products->find($resposne['product_id']); 

                    $resposneData[] = [
                        'id' => $resposne['id'],
                        'name' => $ProductData ? $ProductData['name'] : null,
                        'slug' =>$ProductData ? $ProductData['slug'] : null,
                        // 'description' => $resposne['description'],
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
                        'thumbnail_image_url' => isset($imageUrl) ? $imageUrl[0]['media_file'] : null,
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
                        'return_policy_text' => [],
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
                        'variations' => $ProductsModelsdata,
                    ];
                }
                $data = $resposneData;

                $message = [
                    'message' => 'Success!',
                    'status' => 200
                ];

                return $this->response->setStatusCode(200)->setJSON(['messageobject' => $message, 'data' => $data]);
            } else {
                $message = [
                    'message' => "No data found",
                    'status' => 200
                ];
                return $this->response->setStatusCode(200)->setJSON(['messageobject' => $message,"data"=>[]]);
            }
        } catch (\Exception $e) {
            $message = [
                'message' => $e->getMessage(),
                'status' => 500
            ];
            return $this->response->setStatusCode(500)->setJSON(['messageobject' => $message]);
        }
    }  /// ================ GETPRODUCTS COMPLETE ================

public function getCategorieslistProducts()
{
    try {
        $categoryId = $this->request->getGet('categoryId');
        $idsArray = array_map('intval', explode(',', $categoryId));
        $ProductcategoriesModels = new ProductcategoriesModel();
        $ProductcategoriesModels->whereIn('category_id', $idsArray);
        $wishlist = $ProductcategoriesModels->findAll();
        $product_id = [];
        if ($wishlist) {
            foreach ($wishlist as $list) {
                $product_id[] = $list['product_id'];
            }
            $ProductsModels = new ProductsModel();
            $ProductsModels->whereIn('id', $product_id);
            $ProductsModels->where('is_published', 1);
            $ProductsModels->where('is_deleted', 0); 
            $products = $ProductsModels->findAll();

            $responseData = [];
            $productVariationsModel = new ProductVariationsModel();
            $productSelectedVariationsModel = new ProductSelectedVariationsModel();
            $variationModel = new VariationMasterModel();
            $mediaModel = new Media_managersModel();
            $productvarstockModel = new ProductVariationStocksModel();

            foreach ($products as $product) {
                // Get product thumbnail image
                $mediaModel->where('id', $product['thumbnail_image']);
                $imageUrl = $mediaModel->findAll();
                $thumbnailImageUrl = isset($imageUrl[0]['media_file']) ? $imageUrl[0]['media_file'] : null;

                // Get product variations
                $productVariations = $productVariationsModel
                    ->where('product_id', $product['id'])
                    ->findAll();

                $variationThumbnails = [];
                $variationsDetails = [];
                $totalStockQty = 0;

                foreach ($productVariations as $variation) {
                    // Get stock for this variation
                    $stock = $productvarstockModel
                        ->where('product_variation_id', $variation['id'])
                        ->first();
                    $stockQty = $stock ? $stock['stock_qty'] : 0;
                    $totalStockQty += $stockQty;

                    // Get variation thumbnail image
                    if (!empty($variation['thumbnail_image'])) {
                        $mediaModel->where('id', $variation['thumbnail_image']);
                        $variationImage = $mediaModel->findAll();
                        $variation['thumbnail_image_url'] = isset($variationImage[0]['media_file']) ? $variationImage[0]['media_file'] : null;
                        if ($variation['thumbnail_image_url']) {
                            $variationThumbnails[] = $variation['thumbnail_image_url'];
                        }
                    } else {
                        $variation['thumbnail_image_url'] = null;
                    }

                    // Get selected variations
                    $selectedVariations = $productSelectedVariationsModel
                        ->select('variation_type_id, selected_value')
                        ->where('product_variation_id', $variation['id'])
                        ->where('product_id', $product['id'])
                        ->findAll();

                    $variationDetails = [];
                    foreach ($selectedVariations as $selected) {
                        $variationType = $variationModel
                            ->select('id, variation_type, display_name')
                            ->where('variation_type_id', $selected['variation_type_id'])
                            ->first();
                        if ($variationType) {
                            $variationDetails[] = [
                                'variation_id' => (string)$selected['variation_type_id'],
                                'variation_type' => $variationType['variation_type'],
                                'display_name' => $variationType['display_name'],
                                'selected_value' => $selected['selected_value'],
                            ];
                        }
                    }

                    $variationsDetails[] = [
                        'product_variation_id' => $variation['id'],
                        'selected_variations' => $variationDetails,
                        'sku' => $variation['sku'],
                        'barcodes' => $variation['barcodes'],
                        'code' => $variation['code'] ?? NULL,
                        'price' => $variation['price'],
                        'stock_qty' => $stockQty,
                        'thumbnail_image' => $variation['thumbnail_image_url'] ?? $thumbnailImageUrl,
                        'created_at' => $variation['created_at'] ?? NULL,
                        'updated_at' => $variation['updated_at'] ?? NULL,
                        'deleted_at' => $variation['deleted_at'] ?? NULL,
                        'is_active' => $variation['is_active'] ?? 1,
                        'capacity' => $variation['capacity'] ?? NULL,
                        'short_description' => $variation['short_description'] ?? NULL,
                        'discount_value' => $variation['discount_value'] ?? 0,
                        'discount_type' => $variation['discount_type'] ?? NULL,
                        'discount_start_date' => $variation['discount_start_date'] ?? NULL,
                        'discount_end_date' => $variation['discount_end_date'] ?? NULL,
                        'sell_target' => $variation['sell_target'] ?? NULL,
                        'is_published' => $variation['is_published'] ?? 0,
                        'is_featured' => $variation['is_featured'] ?? 0,
                        'min_purchase_qty' => $variation['min_purchase_qty'] ?? 1,
                        'max_purchase_qty' => $variation['max_purchase_qty'] ?? 1,
                        'has_warranty' => $variation['has_warranty'] ?? 1,
                        'total_sale_count' => $variation['total_sale_count'] ?? 0,
                        'standard_delivery_hours' => $variation['standard_delivery_hours'] ?? 24,
                        'express_delivery_hours' => $variation['express_delivery_hours'] ?? 24,
                        'reward_points' => $variation['reward_points'] ?? 0,
                        'meta_title' => $variation['meta_title'] ?? NULL,
                        'meta_description' => $variation['meta_description'] ?? NULL,
                        'meta_img' => $variation['meta_img'] ?? NULL,
                        'brand_id' => $variation['brand_id'] ?? NULL,
                        'tax_id' => $variation['tax_id'] ?? NULL,
                        'unit_id' => $variation['unit_id'] ?? NULL,
                        'min_quantity_wholesale' => $variation['min_quantity_wholesale'] ?? NULL,
                        'max_quantity_wholesale' => $variation['max_quantity_wholesale'] ?? NULL,
                        'wholesale_discount' => $variation['wholesale_discount'] ?? NULL,
                        'wholesale_notes' => $variation['wholesale_notes'] ?? NULL,
                        'min_price' => $variation['min_price'] ?? 0,
                    ];
                }

                // Get variation options for this product
                $variationOptions = $productSelectedVariationsModel
                    ->select('variation_type_id, selected_value')
                    ->where('product_id', $product['id'])
                    ->findAll();

                $variationTypes = [];
                foreach ($variationOptions as $option) {
                    $variationType = $variationModel
                        ->select('id, variation_type, display_name')
                        ->where('id', $option['variation_type_id'])
                        ->first();
                    if ($variationType) {
                        $typeId = $variationType['id'];
                        if (!isset($variationTypes[$typeId])) {
                            $variationTypes[$typeId] = [
                                'variation_id' => (string)$typeId,
                                'variation_type' => $variationType['variation_type'],
                                'display_name' => $variationType['display_name'],
                                'values' => []
                            ];
                        }
                        if (!in_array($option['selected_value'], $variationTypes[$typeId]['values'])) {
                            $variationTypes[$typeId]['values'][] = $option['selected_value'];
                        }
                    }
                }

                $stock_status = ($totalStockQty > 0) ? 'in_stock' : 'out_stock';

                $responseData[] = [
                    'id' => $product['id'],
                    'name' => $product['name'],
                    'slug' => $product['slug'],
                    'description' => $product['description'],
                    'category_image_id' => null,
                    'category_icon_id' => null,
                    'status' => 1,
                    'type' => "product",
                    'commission_rate' => null,
                    'parent_id' => null,
                    'created_by_id' => "1",
                    'created_at' => $product['created_at'],
                    'updated_at' => $product['updated_at'],
                    'deleted_at' => null,
                    'blogs_count' => 6,
                    'products_count' => 0,
                    'thumbnail_image' => $product['thumbnail_image'],
                    'thumbnail_image_url' => $thumbnailImageUrl,
                    'category_icon' => null,
                    'subcategories' => [],
                    'parent' => null,
                    'short_description' => [],
                    'unit' => [],
                    'weight' => [],
                    'quantity' => $totalStockQty,
                    'sale_price' => $product['price'],
                    'price' => $product['price'],
                    'product_barcode' => $product['barcode_image'] ?? null,
                    'product_thumbnail' => $variationThumbnails,
                    'discount' => $product['discount_value'],
                    'is_featured' => $product['is_featured'],
                    'shipping_days' => [],
                    'is_cod' => [],
                    'is_free_shipping' => [],
                    'is_sale_enable' => [],
                    'is_return' => [],
                    'is_trending' => [],
                    'is_approved' => [],
                    'sale_starts_at' => [],
                    'sale_expired_at' => [],
                    'sku' => $product['sku'],
                    'is_random_related_products' => [],
                    'stock_status' => $stock_status,
                    'meta_title' => [],
                    'meta_description' => [],
                    'product_thumbnail_id' => [],
                    'product_meta_image_id' => [],
                    'size_chart_image_id' => [],
                    'estimated_delivery_text' => [],
                    'return_policy_text' => [],
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
                    'variation_options' => array_values($variationTypes),
                    'variations' => $variationsDetails
                ];
            }

            $message = [
                'message' => 'Success!',
                'status' => 200
            ];

            return $this->response->setStatusCode(200)->setJSON(['messageobject' => $message, 'data' => $responseData]);
        } else {
            $message = [
                'message' => "No data found",
                'status' => 200
            ];
            return $this->response->setStatusCode(200)->setJSON(['messageobject' => $message, "data" => []]);
        }
    } catch (\Exception $e) {
        $message = [
            'message' => $e->getMessage(),
            'status' => 500
        ];
        return $this->response->setStatusCode(500)->setJSON(['messageobject' => $message]);
    }
}
 
     /// ================ GETPRODUCTS COMPLETE ================

 
public function getsearchProducts()
{
    try {
        $ProductsModels = new ProductsModel();
        $search = $this->request->getGet('search');
        $products = $ProductsModels->searchproduct($search);

        $responseData = [];
        $productVariationsModel = new ProductVariationsModel();
        $productSelectedVariationsModel = new ProductSelectedVariationsModel();
        $variationModel = new VariationMasterModel();
        $mediaModel = new Media_managersModel();
        $productvarstockModel = new ProductVariationStocksModel();

        foreach ($products as $product) {
            // Get product thumbnail image
            $mediaModel->where('id', $product['thumbnail_image']);
            $imageUrl = $mediaModel->findAll();
            $thumbnailImageUrl = isset($imageUrl[0]['media_file']) ? $imageUrl[0]['media_file'] : null;

            // Get product variations
            $productVariations = $productVariationsModel
                ->where('product_id', $product['id'])
                ->findAll();

            $variationThumbnails = [];
            $variationsDetails = [];
            $totalStockQty = 0;

            foreach ($productVariations as $variation) {
                // Get stock for this variation
                $stock = $productvarstockModel
                    ->where('product_variation_id', $variation['id'])
                    ->first();
                $stockQty = $stock ? $stock['stock_qty'] : 0;
                $totalStockQty += $stockQty;

                // Get variation thumbnail image
                if (!empty($variation['thumbnail_image'])) {
                    $mediaModel->where('id', $variation['thumbnail_image']);
                    $variationImage = $mediaModel->findAll();
                    $variation['thumbnail_image_url'] = isset($variationImage[0]['media_file']) ? $variationImage[0]['media_file'] : null;
                    if ($variation['thumbnail_image_url']) {
                        $variationThumbnails[] = $variation['thumbnail_image_url'];
                    }
                } else {
                    $variation['thumbnail_image_url'] = null;
                }

                // Get selected variations
                $selectedVariations = $productSelectedVariationsModel
                    ->select('variation_type_id, selected_value')
                    ->where('product_variation_id', $variation['id'])
                    ->where('product_id', $product['id'])
                    ->findAll();

                $variationDetails = [];
                foreach ($selectedVariations as $selected) {
                    $variationType = $variationModel
                        ->select('id, variation_type, display_name')
                        ->where('variation_type_id', $selected['variation_type_id'])
                        ->first();
                    if ($variationType) {
                        $variationDetails[] = [
                            'variation_id' => (string)$selected['variation_type_id'],
                            'variation_type' => $variationType['variation_type'],
                            'display_name' => $variationType['display_name'],
                            'selected_value' => $selected['selected_value'],
                        ];
                    }
                }

                $variationsDetails[] = [
                    'product_variation_id' => $variation['id'],
                    'selected_variations' => $variationDetails,
                    'sku' => $variation['sku'],
                    'barcodes' => $variation['barcodes'],
                    'code' => $variation['code'] ?? NULL,
                    'price' => $variation['price'],
                    'stock_qty' => $stockQty,
                    'thumbnail_image' => $variation['thumbnail_image_url'] ?? $thumbnailImageUrl,
                    'created_at' => $variation['created_at'] ?? NULL,
                    'updated_at' => $variation['updated_at'] ?? NULL,
                    'deleted_at' => $variation['deleted_at'] ?? NULL,
                    'is_active' => $variation['is_active'] ?? 1,
                    'capacity' => $variation['capacity'] ?? NULL,
                    'short_description' => $variation['short_description'] ?? NULL,
                    'discount_value' => $variation['discount_value'] ?? 0,
                    'discount_type' => $variation['discount_type'] ?? NULL,
                    'discount_start_date' => $variation['discount_start_date'] ?? NULL,
                    'discount_end_date' => $variation['discount_end_date'] ?? NULL,
                    'sell_target' => $variation['sell_target'] ?? NULL,
                    'is_published' => $variation['is_published'] ?? 0,
                    'is_featured' => $variation['is_featured'] ?? 0,
                    'min_purchase_qty' => $variation['min_purchase_qty'] ?? 1,
                    'max_purchase_qty' => $variation['max_purchase_qty'] ?? 1,
                    'has_warranty' => $variation['has_warranty'] ?? 1,
                    'total_sale_count' => $variation['total_sale_count'] ?? 0,
                    'standard_delivery_hours' => $variation['standard_delivery_hours'] ?? 24,
                    'express_delivery_hours' => $variation['express_delivery_hours'] ?? 24,
                    'reward_points' => $variation['reward_points'] ?? 0,
                    'meta_title' => $variation['meta_title'] ?? NULL,
                    'meta_description' => $variation['meta_description'] ?? NULL,
                    'meta_img' => $variation['meta_img'] ?? NULL,
                    'brand_id' => $variation['brand_id'] ?? NULL,
                    'tax_id' => $variation['tax_id'] ?? NULL,
                    'unit_id' => $variation['unit_id'] ?? NULL,
                    'min_quantity_wholesale' => $variation['min_quantity_wholesale'] ?? NULL,
                    'max_quantity_wholesale' => $variation['max_quantity_wholesale'] ?? NULL,
                    'wholesale_discount' => $variation['wholesale_discount'] ?? NULL,
                    'wholesale_notes' => $variation['wholesale_notes'] ?? NULL,
                    'min_price' => $variation['min_price'] ?? 0,
                ];
            }

            // Get variation options for this product
            $variationOptions = $productSelectedVariationsModel
                ->select('variation_type_id, selected_value')
                ->where('product_id', $product['id'])
                ->findAll();

            $variationTypes = [];
            foreach ($variationOptions as $option) {
                $variationType = $variationModel
                    ->select('id, variation_type, display_name')
                    ->where('id', $option['variation_type_id'])
                    ->first();
                if ($variationType) {
                    $typeId = $variationType['id'];
                    if (!isset($variationTypes[$typeId])) {
                        $variationTypes[$typeId] = [
                            'variation_id' => (string)$typeId,
                            'variation_type' => $variationType['variation_type'],
                            'display_name' => $variationType['display_name'],
                            'values' => []
                        ];
                    }
                    if (!in_array($option['selected_value'], $variationTypes[$typeId]['values'])) {
                        $variationTypes[$typeId]['values'][] = $option['selected_value'];
                    }
                }
            }

            $stock_status = ($totalStockQty > 0) ? 'in_stock' : 'out_stock';

            $responseData[] = [
                'id' => $product['id'],
                'name' => $product['name'],
                'slug' => $product['slug'],
                'description' => $product['description'],
                'category_image_id' => null,
                'category_icon_id' => null,
                'status' => 1,
                'type' => "product",
                'commission_rate' => null,
                'parent_id' => null,
                'created_by_id' => "1",
                'created_at' => $product['created_at'],
                'updated_at' => $product['updated_at'],
                'deleted_at' => null,
                'blogs_count' => 6,
                'products_count' => 0,
                'thumbnail_image' => $product['thumbnail_image'],
                'thumbnail_image_url' => $thumbnailImageUrl,
                'category_icon' => null,
                'subcategories' => [],
                'parent' => null,
                'short_description' => [],
                'unit' => [],
                'weight' => [],
                'quantity' => $totalStockQty,
                'sale_price' => $product['price'],
                'price' => $product['price'],
                'product_barcode' => $product['barcode_image'] ?? null,
                'product_thumbnail' => $variationThumbnails,
                'discount' => $product['discount_value'],
                'is_featured' => $product['is_featured'],
                'shipping_days' => [],
                'is_cod' => [],
                'is_free_shipping' => [],
                'is_sale_enable' => [],
                'is_return' => [],
                'is_trending' => [],
                'is_approved' => [],
                'sale_starts_at' => [],
                'sale_expired_at' => [],
                'sku' => $product['sku'],
                'is_random_related_products' => [],
                'stock_status' => $stock_status,
                'meta_title' => [],
                'meta_description' => [],
                'product_thumbnail_id' => [],
                'product_meta_image_id' => [],
                'size_chart_image_id' => [],
                'estimated_delivery_text' => [],
                'return_policy_text' => [],
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
                'variation_options' => array_values($variationTypes),
                'variations' => $variationsDetails
            ];
        }

        $message = [
            'message' => 'Success!',
            'status' => 200
        ];

        return $this->response->setStatusCode(200)->setJSON(['messageobject' => $message, 'data' => $responseData]);
    } catch (\Exception $e) {
        $message = [
            'message' => $e->getMessage(),
            'status' => 500
        ];
        return $this->response->setStatusCode(500)->setJSON(['messageobject' => $message]);
    }
} /// ================ GETPRODUCTS COMPLETE ================

    // public function bulk_order_request()
    // {
    //     try {
          
    //         $bulk_order_req_model = new Bulk_Order_RequestModel();
    //         $ProductVariationsModel = new ProductVariationsModel();
    //         $bulkOrderItemModel = new BulkOrderItemModel();
    //         $user_address_model = new UserAddressesModel();
    
    //         $json_data = json_decode(file_get_contents('php://input'), true); 
    

    //         if (!isset($json_data['bulkorder_id'], $json_data['deliveryDate'], $json_data['variation'], $json_data['quantity'], $json_data['email'], $json_data['mobileNumber'], $json_data['shippingAddressSelect'], $json_data['productName'], $json_data['user_id'])) {
    //             return $this->response->setStatusCode(400)->setJSON(['messageobject' => ['message' => 'Missing required fields.', 'status' => 400]]);
    //         }
    
       
    //         $bulkOrderId = $json_data['bulkorder_id'];
    //         $description = isset($json_data['description']) ? $json_data['description'] : "";
    //         $deliveryDate = $json_data['deliveryDate'];
    //         $variation = $json_data['variation'];
    //         $quantity = $json_data['quantity'];
    //         $email = $json_data['email'];
    //         $mobileNumber = $json_data['mobileNumber'];
    //         $shippingAddressSelect = $json_data['shippingAddressSelect'];
    //         $shippingAddress = $json_data['shippingAddress'];
    //         $productName = $json_data['productName'];
    //         $user_id = $json_data['user_id'];
    
      
    //         $user_address = $user_address_model->where('id', $shippingAddressSelect)->first();
    
    //         if (!$user_address) {
    //             $message = [
    //                 'message' => 'Shipping address not found.',
    //                 'status' => 404
    //             ];
    //             return $this->response->setStatusCode(404)->setJSON(['messageobject' => $message]);
    //         }
    
   
    //         $created_date = date('Y-m-d H:i:s');
    

    //         $data = [
    //             'Customer_Id' =>  $user_id ,
    //             'bulkorder_id' => $bulkOrderId,
    //             'Product_variation_id' => $variation['product_variation_id'],  
    //             'Email' => $email,
    //             'Mob_number' => $mobileNumber,
    //             'Product_name' => $productName,
    //             'Bulk_qty' => $quantity,
    //             'description' => $description,
    //             'Shippingaddress_id' => $user_address['id'],
    //             'Status' => 'pending',
    //             'expected_delivery_date' => $deliveryDate,
    //             'CreatedDate' => $created_date,
    //             'CreatedBy' => $user_id 
    //         ];
    
       
    //         $insertedId = $bulk_order_req_model->insert($data);
    //         if (!$insertedId) {
    //             $message = [
    //                 'message' => 'Failed to create bulk order request.',
    //                 'status' => 500
    //             ];
    //             return $this->response->setStatusCode(500)->setJSON(['messageobject' => $message]);
    //         }
         
    //         if (!empty($variation)) {
    //             // foreach ($variation as $product) {
    //                 $productData = [
    //                 'bulk_order_id' => $insertedId, 
    //                     'product_variation_id' => $variation['product_variation_id'] ?? null, 
    //                     'created_at' => $created_date,
    //                     'qty' => $quantity,
    //                     'price' => $variation['price'] ?? 0,
    //                     'total_price' => $product['total_price'] ?? 0,
    //                     'tax_id' =>$variation['tax_id'] ?? null, 
    //                     'discount_value' => $product['discount_value'] ?? 0,
    //                     'unit_id' =>$variation['unit_id'] ?? 0, 
    //                 ];
    
                 
    //                 $bulkOrderItemModel->insert($productData);
    //             // }
    //         }
    
         
    //         $message = [
    //             'message' => 'Success!',
    //             'status' => 200
    //         ];
    
    //         return $this->response->setStatusCode(200)->setJSON(['messageobject' => $message, 'data' => $data]);
    
    //     } catch (\Exception $e) {
         
    //         $message = [
    //             'message' => $e->getMessage(),
    //             'status' => 500
    //         ];
    //         return $this->response->setStatusCode(500)->setJSON(['messageobject' => $message]);
    //     }
    // }

    




// public function bulk_order_request()
// {
//     try {
//         // Models for new tables
//         $OrderGroupsModel = new \App\Models\Customer\Order_Groups_Model();
//         $OrdersModel = new \App\Models\Customer\OrdersModel();
//         $OrderItemsModel = new Order_Items_Model();
//         $SaleOrderModel = new \App\Models\api\SaleOrderModel();
//         $user_address_model = new UserAddressesModel();

//         $json_data = json_decode(file_get_contents('php://input'), true);

//         $description = isset($json_data['description']) ? $json_data['description'] : "";
//         $deliveryDate = $json_data['deliveryDate'];
//         $variation = $json_data['variation'];
//         $quantity = $json_data['quantity'];
//         $email = $json_data['email'];
//         $mobileNumber = $json_data['mobileNumber'];
//         $shippingAddressSelect = $json_data['shippingAddressSelect'];
//         $productName = $json_data['productName'];
//         $user_id = $json_data['user_id'];

//         $user_address = $user_address_model->where('id', $shippingAddressSelect)->first();
//         if (!$user_address) {
//             return $this->response->setStatusCode(404)->setJSON(['messageobject' => ['message' => 'Shipping address not found.', 'status' => 404]]);
//         }

//         $created_date = date('Y-m-d H:i:s');

//         // --- Fetch order_prefix and order_start_code from system_settings ---
//         $db = \Config\Database::connect();
//         $builder = $db->table('system_settings');
//         $settings = $builder->select('order_code_prefix, order_code_start')->get()->getRowArray();
//         $orderPrefix = isset($settings['order_code_prefix']) ? $settings['order_code_prefix'] : 'ORD';
//         $orderStartCode = isset($settings['order_code_start']) ? (int)$settings['ordert_code_start'] : 1;

//         // --- Get last order_number and increment ---
//         $lastOrder = $OrdersModel->orderBy('order_number', 'DESC')->first();
//         if ($lastOrder && isset($lastOrder['order_number'])) {
//             $lastNumber = (int)preg_replace('/\D/', '', $lastOrder['order_number']);
//             $nextOrderNumber = $lastNumber + 1;
//         } else {
//             $nextOrderNumber = $orderStartCode;
//         }
//         $orderNumber = $orderPrefix . str_pad($nextOrderNumber, 5, '0', STR_PAD_LEFT);

//         // 1. Insert into order_group
//         $orderGroupData = [
//             "user_id" => $user_id,
//             "order_code" => 1, // will update after insert
//             "shipping_address_id" => $user_address['id'],
//             "phone_no" => $mobileNumber,
//             "sub_total_amount" => $variation['price'] * $quantity,
//             "grand_total_amount" => $variation['price'] * $quantity,
//             "created_at" => $created_date,
//             "updated_at" => $created_date,
//             "payment_method" => 'COD',   
//             "payment_status" => 'Unpaid',
//             "description" => $description,
//         ];
//         $order_grp_id = $OrderGroupsModel->insert($orderGroupData);
//         $OrderGroupsModel->set(['order_code' => $order_grp_id])->where('id', $order_grp_id)->update();

//         // 2. Generate sales order number
//         $salesautonumber = 0;
//         $salesData = $SaleOrderModel->orderBy('OrderNumber', 'DESC')->first();
//         if (isset($salesData) && isset($salesData['OrderNumber'])) {
//             preg_match('/\d+$/', $salesData['OrderNumber'], $matches);
//             $salesautonumber = isset($matches[0]) ? intval($matches[0]) : 0;
//         }
//         $salesautonumber += 1;
//         $formatted_number = str_pad($salesautonumber, 5, '0', STR_PAD_LEFT);
//         $salesorderNumber = "SO#" . $formatted_number;

//         // 3. Insert into orders table (add order_number)
//         $ordInst = [
//             'order_group_id' => $order_grp_id,
//             'user_id' => $user_id,
//             "saleorder_id" => $salesorderNumber,
//             "order_number" => $orderNumber, 
//             "updated_at" => $created_date,
//             "payment_status" => 'Unpaid',
//             "is_approved" => 0,
//             "delivery_status" => 'Pending',
//             "shipping_cost" => 0,
//             "shipping_delivery_type" => 'scheduled',
//             "scheduled_delivery_info" => $deliveryDate,
//             "order_from"=>'BULK'
//         ];
//         $ord_id = $OrdersModel->insert($ordInst);

//         // 4. Insert into sale_order table (alternative)
//         if ($ord_id) {
//             $saleOrderData = [
//                 "OrderNumber" => $salesorderNumber,
//                 "Note" => "bulk order",
//                 "order_id" => $ord_id,
//                 "SaleReturnNote" => '',
//                 "TermAndCondition" => '',
//                 "IsSalesOrderRequest" => 1,
//                 "SOCreatedDate" => $created_date,
//                 "Status" => 'bulk order',
//                 "DeliveryStatus" => 'Pending',
//                 "CustomerId" => $user_id,
//                 "TotalAmount" => $variation['price'] * $quantity,
//                 "TotalTax" => 0,
//                 "TotalDiscount" => 0,
//                 "orderStatus" => 'pending',
//                 "TotalPaidAmount" => 0,
//                 "PaymentStatus" => 'Unpaid',
//                 "PurchaseReturnNote" => "None",
//                 "CreatedDate" => $created_date,
//                 "DeliveryDate" => $deliveryDate,
//                 "is_bulkorder" => 1,
//                 // "bulkorder_id" => $bulkOrderId
//             ];
//             $insertedId = $SaleOrderModel->insert($saleOrderData);
//             if (!$insertedId) {
//                 return $this->response->setStatusCode(500)->setJSON(['messageobject' => ['message' => 'Failed to create sales order.', 'status' => 500]]);
//             }
//         } elseif (!$ord_id) {
            
//             return $this->response->setStatusCode(500)->setJSON(['messageobject' => ['message' => 'Failed to create order.', 'status' => 500]]);
//         }

//         // 5. Insert into order_items table
//         $variations = is_array($variation) && isset($variation[0]) ? $variation : [$variation];
//         foreach ($variations as $var) {
//             $itemData = [
//                 "order_id" => $ord_id,
//                 "product_variation_id" => $var['product_variation_id'],
//                 "qty" => $quantity,
//                 "unit_price" => $var['price'],
//                 "tax_id" => $var['tax_id'] ?? null,
//                 "discount" => $var['discount_value'] ?? 0,
//                 "total_price" => $var['price'] * $quantity,
//                 "created_at" => $created_date,
//             ];
//             $OrderItemsModel->insert($itemData);
//         }

//         $message = [
//             'message' => 'Bulk Order Placed Successfully!',
//             'status' => 200
//         ];
//         return $this->response->setStatusCode(200)->setJSON(['messageobject' => $message, 'data' => $saleOrderData]);
//     } catch (\Exception $e) {
//         $message = [
//             'message' => $e->getMessage(),
//             'status' => 500
//         ];
//         return $this->response->setStatusCode(500)->setJSON(['messageobject' => $message]);
//     }
// }



public function bulk_order_request()
{
    try {
        $OrderGroupsModel = new \App\Models\Customer\Order_Groups_Model();
        $OrdersModel = new \App\Models\Customer\OrdersModel();
        $OrderItemsModel = new Order_Items_Model();
        $SaleOrderModel = new \App\Models\api\SaleOrderModel();
        $user_address_model = new UserAddressesModel();

        $json_data = json_decode(file_get_contents('php://input'), true);

        $description = isset($json_data['description']) ? $json_data['description'] : "";
        $deliveryDate = $json_data['deliveryDate'];
        $variation = $json_data['variation'];
        $quantity = $json_data['quantity'];
        $email = $json_data['email'];
        $mobileNumber = $json_data['mobileNumber'];
        $shippingAddressSelect = $json_data['shippingAddressSelect'];
        $productName = $json_data['productName'];
        $user_id = $json_data['user_id'];

        $user_address = $user_address_model->where('id', $shippingAddressSelect)->first();
        if (!$user_address) {
            return $this->response->setStatusCode(404)->setJSON(['messageobject' => ['message' => 'Shipping address not found.', 'status' => 404]]);
        }

        $created_date = date('Y-m-d H:i:s');

        // --- Check for recent bulk order group for this user ---
        $recentOrderGroup = $OrderGroupsModel
            ->where('user_id', $user_id)
            ->where('payment_status', 'Unpaid')
            ->orderBy('id', 'DESC')
            ->first();

        if ($recentOrderGroup) {
            $recentOrder = $OrdersModel
                ->where('order_group_id', $recentOrderGroup['id'])
                ->orderBy('id', 'DESC')
                ->first();

            if ($recentOrder && $recentOrder['order_from'] == 'BULK') {
                // Update order group
                $OrderGroupsModel->update($recentOrderGroup['id'], [
                    "sub_total_amount" => $variation['price'] * $quantity,
                    "grand_total_amount" => $variation['price'] * $quantity,
                    "updated_at" => $created_date,
                    "description" => $description,
                ]);

                // Add new items for this group
                $variations = is_array($variation) && isset($variation[0]) ? $variation : [$variation];
                foreach ($variations as $var) {
                    $itemData = [
                        "order_id" => $recentOrder['id'],
                        "product_variation_id" => $var['product_variation_id'],
                        "qty" => $quantity,
                        "unit_price" => $var['price'],
                        "tax_id" => $var['tax_id'] ?? null,
                        "discount" => $var['discount_value'] ?? 0,
                        "total_price" => $var['price'] * $quantity,
                        "created_at" => $created_date,
                    ];
                    $OrderItemsModel->insert($itemData);
                }

                $message = [
                    'message' => 'Bulk order updated with new items.',
                    'status' => 200
                ];
                return $this->response->setStatusCode(200)->setJSON(['messageobject' => $message]);
            }
            // If not BULK, fall through to create new order below
        }

        // --- No recent bulk order, or last order is not BULK: create new order group and order ---
 
// ...existing code...

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
$orderStartCode = isset($mockedData['order_code_start']['value']) ? (int)$mockedData['order_code_start']['value'] : 0001;
$ordersuffix = isset($mockedData['order_suffix']['value']) ? (int)$mockedData['order_suffix']['value'] : 0001;


// ...rest of your code...
        $lastOrder = $OrdersModel->orderBy('order_number', 'DESC')->first();
        if ($lastOrder && isset($lastOrder['order_number'])) {
            $lastNumber = (int)preg_replace('/\D/', '', $lastOrder['order_number']);
            $nextOrderNumber = $lastNumber + 1;
        } else {
            $nextOrderNumber = $orderStartCode;
        }
        $orderNumber = $orderPrefix .'-'. str_pad($nextOrderNumber, 5, '0', STR_PAD_LEFT).'-'.$ordersuffix;

        $orderGroupData = [
            "user_id" => $user_id,
            "order_code" => 1, // will update after insert
            "shipping_address_id" => $user_address['id'],
            "phone_no" => $mobileNumber,
            "sub_total_amount" => $variation['price'] * $quantity,
            "grand_total_amount" => $variation['price'] * $quantity,
            "created_at" => $created_date,
            "updated_at" => $created_date,
            "payment_method" => 'COD',
            "payment_status" => 'Unpaid',
            "description" => $description,
        ];
        $order_grp_id = $OrderGroupsModel->insert($orderGroupData);
        $OrderGroupsModel->set(['order_code' => $order_grp_id])->where('id', $order_grp_id)->update();

        
$sorderPrefix = isset($mockedData['sale_order_code_prefix']['value']) ? $mockedData['sale_order_code_prefix']['value'] : 'SO#';
$sorderStartCode = isset($mockedData['sale_order_code_start']['value']) ? (int)$mockedData['sale_order_code_start']['value'] : 0001;
$sordersuffix=isset($mockedData['so_suffix']['value']) ? $mockedData['so_suffix']['value'] : '2027';


  
    $salesData123 = $SaleOrderModel->orderBy('OrderNumber', 'DESC')->first();

  
    if (isset($salesData123) && isset($salesData123['OrderNumber'])) {
        
        preg_match('/\d+$/', $salesData123['OrderNumber'], $matches);
        $salesautonumber = isset($matches[0]) ? intval($matches[0]) : 0;
    $salesautonumber += 1;

    }else{
        $salesautonumber = $sorderStartCode;
    }

    
    $formatted_number = str_pad($salesautonumber, 5, '0', STR_PAD_LEFT);
    $salesorderNumber = $sorderPrefix .'-'. $formatted_number.'-'.$sordersuffix;

        $ordInst = [
            'order_group_id' => $order_grp_id,
            'user_id' => $user_id,
            "saleorder_id" => $salesorderNumber,
            "order_number" => $orderNumber,
            "updated_at" => $created_date,
            "payment_status" => 'Unpaid',
            "is_approved" => 0,
            "delivery_status" => 'Pending',
            "shipping_cost" => 0,
            "shipping_delivery_type" => 'scheduled',
            "scheduled_delivery_info" => $deliveryDate,
            "order_from" => 'BULK'
        ];
        $ord_id = $OrdersModel->insert($ordInst);

        if ($ord_id) {
            $saleOrderData = [
                "OrderNumber" => $salesorderNumber,
                "Note" => "bulk order",
                "order_id" => $ord_id,
                "SaleReturnNote" => '',
                "TermAndCondition" => '',
                "IsSalesOrderRequest" => 1,
                "SOCreatedDate" => $created_date,
                "Status" => 'bulk order',
                "DeliveryStatus" => 'Pending',
                "CustomerId" => $user_id,
                "TotalAmount" => $variation['price'] * $quantity,
                "TotalTax" => 0,
                "TotalDiscount" => 0,
                "orderStatus" => 'pending',
                "TotalPaidAmount" => 0,
                "PaymentStatus" => 'Unpaid',
                "PurchaseReturnNote" => "None",
                "CreatedDate" => $created_date,
                "DeliveryDate" => $deliveryDate,
                "is_bulkorder" => 1,
            ];
            $insertedId = $SaleOrderModel->insert($saleOrderData);
            if (!$insertedId) {
                return $this->response->setStatusCode(500)->setJSON(['messageobject' => ['message' => 'Failed to create sales order.', 'status' => 500]]);
            }
        } else {
            return $this->response->setStatusCode(500)->setJSON(['messageobject' => ['message' => 'Failed to create order.', 'status' => 500]]);
        }

        $variations = is_array($variation) && isset($variation[0]) ? $variation : [$variation];
        foreach ($variations as $var) {
            $itemData = [
                "order_id" => $ord_id,
                "product_variation_id" => $var['product_variation_id'],
                "qty" => $quantity,
                "unit_price" => $var['price'],
                "tax_id" => $var['tax_id'] ?? null,
                "discount" => $var['discount_value'] ?? 0,
                "total_price" => $var['price'] * $quantity,
                "created_at" => $created_date,
            ];
            $OrderItemsModel->insert($itemData);
        }

        $message = [
            'message' => 'Bulk Order Placed Successfully!',
            'status' => 200
        ];
        return $this->response->setStatusCode(200)->setJSON(['messageobject' => $message, 'data' => $saleOrderData]);
    } catch (\Exception $e) {
        $message = [
            'message' => $e->getMessage(),
            'status' => 500
        ];
        return $this->response->setStatusCode(500)->setJSON(['messageobject' => $message]);
    }
}
// ...existing code...

    public function AutoGenBulkOrderNumber()
{
    $salesautonumber = 0;
   
    $BulkOrderModels = new Bulk_Order_RequestModel();
 

    $salesData = $BulkOrderModels->orderBy('bulkorder_id', 'DESC')->first();

    if (isset($salesData) && isset($salesData['bulkorder_id'])) {
      
        preg_match('/\d+$/', $salesData['bulkorder_id'], $matches);
        $salesautonumber = isset($matches[0]) ? intval($matches[0]) : 0;
    }

 
    $salesautonumber += 1;

    $formatted_number = str_pad($salesautonumber, 5, '0', STR_PAD_LEFT);
    $BulkorderNumber = "BO#" . $formatted_number;

    $message = [
        'message' => 'Successfully Generated Bulk Order Number!',
        'status' => 200
    ];

    return $this->response->setStatusCode(200)->setJSON(['messageobject' => $message, 'BulkorderNumber' => $BulkorderNumber]);
}
    
}
