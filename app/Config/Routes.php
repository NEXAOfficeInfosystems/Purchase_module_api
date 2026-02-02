<?php

use App\Controllers\Api\StripeController;
use CodeIgniter\Router\RouteCollection;
use App\Controllers\Home;
use App\Controllers\api\CartController;
use App\Controllers\api\WishlistsController;
use App\Controllers\api\OrdersController;
use App\Controllers\api\ProductCategorieController;
use App\Controllers\api\AuthController;
use App\Controllers\api\CustomerController;
use App\Controllers\api\ProductController;
use App\Controllers\api\SaleorderController;
use App\Controllers\api\GeneralSettingController;
use App\Controllers\api\BestSellingController;
use App\Controllers\api\StripeWebhook;




/**
 * @var RouteCollection $routes
 */
$routes->post('api/validateToken', [AuthController::class, 'validateToken']);

$routes->get('api/generateCaptcha', [AuthController::class, 'generateCaptcha']);
$routes->post('api/validateCaptcha', [AuthController::class, 'validateCaptcha']);


$routes->post('api/login', [AuthController::class, 'login']);
$routes->post('api/sendNotification',[CustomerController::class,'sendEmailAndStoreData']);
$routes->get('api/getNotifications',[OrdersController::class,'getNotifications']);

$routes->post('api/register', [AuthController::class, 'register']);
$routes->post('api/sendOtp', [AuthController::class, 'sendOtptomail']);
$routes->post('api/verifyOtp', [AuthController::class, 'verifyOtp']);
$routes->post('api/updatedEmailPassword', [AuthController::class, 'isUpdatedEmailPassword']);
$routes->get('api/emailValidaion', [AuthController::class, 'isEmailvalidation']);
$routes->post('api/forgetpassword', [AuthController::class, 'forgetPassword']);

$routes->post('api/changepassword', [AuthController::class, 'changePassword']);
// general settings
$routes->get('api/getsetting', [GeneralSettingController::class, 'getSettings']);


$routes->get('api/getCategoriesdetails', [ProductCategorieController::class, 'getCategories']);
$routes->get('api/getProductsdetails', [ProductCategorieController::class, 'getProducts']);


$routes->get('api/bestsellingproducts', [BestSellingController::class, 'bestsellingproduct']);


$routes->get('api/getcategoriesproduct', [ProductCategorieController::class, 'getCategorieslistProducts']);
$routes->get('api/getcategorielist', [ProductCategorieController::class, 'list']);
$routes->get('api/catprodlist', [ProductCategorieController::class, 'cat_product']);
$routes->get('api/wishlist', [ProductCategorieController::class, 'getwishlistProducts']);
$routes->get('api/searchproduct', [ProductCategorieController::class, 'getsearchProducts']);


// request_bulk_order
$routes->post('api/req_bulk_order', [ProductCategorieController::class, 'bulk_order_request']);


$routes->get('api/autogenbulk', [ProductCategorieController::class, 'AutoGenBulkOrderNumber']);
$routes->post('api/updateprofile', [AuthController::class, 'updateUserName']);



$routes->get('api/getcitiesdetails', [CustomerController::class, 'getcities']);
$routes->get('api/getcountriesdetails', [CustomerController::class, 'getcountries']);
$routes->get('api/getCustomerdetails', [CustomerController::class, 'getCustomer']);
$routes->get('api/getCustomerData', [CustomerController::class, 'index']);
$routes->post('api/BankDetails', [CustomerController::class, 'saveBankDetails']);
$routes->post('api/contactus', [CustomerController::class, 'createContactus']);
$routes->post('api/addeditaddress', [CustomerController::class, 'AddEditAddress']);
$routes->post('api/CopyAddress', [CustomerController::class, 'CopyAddress']);

$routes->post('api/user/address/delete', [CustomerController::class, 'useraddressDelete']);
$routes->get('api/getstatesdetails', [CustomerController::class, 'getStates']);


$routes->get('api/getWishlistdetails', [WishlistsController::class, 'getWishlist']);
$routes->get('api/getproduct', [ProductController::class, 'index']);
$routes->get('api/getSaleOrderData', [SaleorderController::class, 'index']);
$routes->get('api/getCartData', [CartController::class, 'index']);
$routes->post('api/removecart', [CartController::class, 'removecart']);
$routes->post('api/deletecartitem', [CartController::class, 'deletecartitem']);

$routes->get('verify-email', [AuthController::class, 'verifyEmail']);
$routes->post('logincheck',[AuthController::class, 'logincheck'] );

$routes->get('/', [Home::class, 'index']);
$routes->post('/check', [Home::class, 'check']);
$routes->post('api/addcart', [CartController::class, 'addcart']);
$routes->post('api/addcartlocal', [CartController::class, 'addcartlocal']);


$routes->post('api/delcart', [CartController::class, 'Cartdelete']);
$routes->get('api/getcart', [CartController::class, 'index']);
$routes->post('api/adddelwish', [WishlistsController::class, 'adddeletewish']);
$routes->post('api/placeorder', [OrdersController::class, 'placeorder']);
$routes->get('api/orderlist', [OrdersController::class, 'index']);
$routes->get('api/orderlistReturns', [OrdersController::class, 'getCancelledOrders']);

$routes->get('api/ordertracklist', [OrdersController::class, 'trackOrderlist']);
$routes->post('api/orderTrackReturn', [OrdersController::class, 'trackOrderReturn']);
$routes->get('api/generateToken', [AuthController::class, 'generateGuestToken']);
$routes->post('api/getPaymentUser',[AuthController::class, 'sendPaymentOtptomail'] );



// ----------------------Stripe------------------------
$routes->post('stripe/create-checkout', [StripeController::class,'createCheckoutSession']);
$routes->get('stripe/return', [StripeController::class,'handleReturn']);


// In app/Config/Routes.php
$routes->post('checkout/process', [StripeController::class,'cartCheckout']);
$routes->post('stripe/webhook', [StripeWebhook::class,'index']);