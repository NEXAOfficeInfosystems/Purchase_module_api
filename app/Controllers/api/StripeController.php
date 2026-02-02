<?php namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use Stripe\StripeClient;
use Stripe\Exception\ApiErrorException;
use CodeIgniter\HTTP\ResponseInterface;

class StripeController extends ResourceController
{
    private StripeClient $stripe;
    
    public function __construct()
    {
        $this->stripe = new StripeClient(config('Stripe')->secretKey);
    }

    /**
     * Create Checkout Session for Shopping Cart
     */
// public function cartCheckout(): ResponseInterface
// {
//     $jsonInput = $this->request->getJSON(true);
    
//     if (!$jsonInput || !isset($jsonInput['cart_items'])) {
//         return $this->errorResponse('Cart is empty or invalid', 400);
//     }

//     $cartItems = $jsonInput['cart_items'];
//     $customerEmail = $jsonInput['customer_email'] ?? null;

//     if (!is_array($cartItems) || empty($cartItems)) {
//         return $this->errorResponse('Cart is empty or invalid', 400);
//     }

//     $lineItems = [];
//     $cartTotal = 0;
//     $totalDiscount = 0;
//     $cartSummary = []; // ✅ response array with per-product info

//     foreach ($cartItems as $index => $item) {
//         if (!isset($item['amount']) || !is_numeric($item['amount']) || 
//             !isset($item['name']) || empty($item['name'])) {
//             return $this->errorResponse("Invalid item at position {$index}", 400);
//         }

//         $amount = (float) $item['amount'];
//         $quantity = isset($item['quantity']) ? (int) $item['quantity'] : 1;

//         if ($amount <= 0 || $quantity <= 0) {
//             return $this->errorResponse("Invalid amount or quantity at position {$index}", 400);
//         }

//         // Always define discount (default = 0)
//         $discount = isset($item['discount']) ? (float) $item['discount'] : 0;

//         // Calculate discounted unit price
//         $discountedAmount = max(0, $amount - $discount);

//         // Track totals
//         $cartTotal += ($discountedAmount * $quantity);
//         $totalDiscount += ($discount * $quantity);

//         // Stripe line item (quantity is included here)
//         $lineItems[] = [
//             'price_data' => [
//                 'currency' => 'inr',
//                 'product_data' => [
//                     'name' => $item['name'],
//                     'description' => $item['description'] ?? 'No',
//                     'metadata' => [
//                         'product_id'       => $item['product_id'] ?? '',
//                         'variant'          => $item['variant'] ?? 'None',
//                         'original_price'   => $amount,
//                         'discount_applied' => $discount,
//                         'quantity'         => $quantity   // ✅ store in metadata too
//                     ]
//                 ],
//                 'unit_amount' => $discountedAmount * 100,
//             ],
//             'quantity' => $quantity,
//             'tax_rates' => ['txr_1RzAamSELFbSCeVPcjG1ASSQ'] // replace with your Tax Rate ID
//         ];

//         // ✅ add to response cart summary (with per-product quantity)
//         $cartSummary[] = [
//             'name'             => $item['name'],
//             'quantity'         => $quantity,
//             'original_price'   => $amount,
//             'discount_applied' => $discount,
//             'final_price'      => $discountedAmount,
//             'total_for_item'   => $discountedAmount * $quantity,
//         ];
//     }

//     try {
//         $sessionData = [
//             'ui_mode' => 'embedded',
//             'line_items' => $lineItems,
//             'mode' => 'payment',
//             'return_url' => 'http://localhost:4200/#/cart-page' . '?session_id={CHECKOUT_SESSION_ID}',
//             'allow_promotion_codes' => false,
//         ];

//         // if ($customerEmail) {
//         //     $sessionData['customer_email'] = $customerEmail;
//         // }

//         $sessionData['metadata'] = [
//             'order_id'               => uniqid('ORD_'),
//             'subtotal_after_discount'=> number_format($cartTotal, 2),
//             'total_discount'         => number_format($totalDiscount, 2),
//             'item_count'             => count($cartItems)
//         ];

//         $session = $this->stripe->checkout->sessions->create($sessionData);

//         return $this->successResponse([
//             'clientSecret'            => $session->client_secret,
//             'order_id'                => $session->metadata->order_id,
//             'subtotal_after_discount' => $cartTotal,
//             'total_discount'          => $totalDiscount,
//             'items'                   => $cartSummary // ✅ includes per-product quantities
//         ]);

//     } catch (ApiErrorException $e) {
//         log_message('error', 'Stripe API Error: ' . $e->getMessage());
//         return $this->errorResponse('Checkout error: ' . $e->getMessage(), 500);
//     }
// }



public function cartCheckout(): ResponseInterface
{
    $jsonInput = $this->request->getJSON(true);

    if (!$jsonInput || !isset($jsonInput['cart_items'])) {
        return $this->errorResponse('Cart is empty or invalid', 400);
    }

    $cartItems = $jsonInput['cart_items'];
    $customerEmail = $jsonInput['customer_email'] ?? null;

    if (!is_array($cartItems) || empty($cartItems)) {
        return $this->errorResponse('Cart is empty or invalid', 400);
    }

    $lineItems = [];
    $cartTotal = 0;
    $totalDiscount = 0;
    $cartSummary = [];

    foreach ($cartItems as $index => $item) {
        if (!isset($item['amount']) || !is_numeric($item['amount']) ||
            !isset($item['name']) || empty($item['name'])) {
            return $this->errorResponse("Invalid item at position {$index}", 400);
        }

        $amount = (float) $item['amount'];
        $quantity = isset($item['quantity']) ? (int) $item['quantity'] : 1;

        if ($amount <= 0 || $quantity <= 0) {
            return $this->errorResponse("Invalid amount or quantity at position {$index}", 400);
        }

        $discount = isset($item['discount']) ? (float) $item['discount'] : 0;
        $discountedAmount = max(0, $amount - $discount);

        $cartTotal += ($discountedAmount * $quantity);
        $totalDiscount += ($discount * $quantity);

        $lineItems[] = [
            'price_data' => [
                'currency' => 'inr',
                'product_data' => [
                    'name' => $item['name'],
                    'description' => $item['description'] ?? 'No',
                    'metadata' => [
                        'product_id'       => $item['product_id'] ?? '',
                        'variant'          => $item['variant'] ?? 'None',
                        'original_price'   => $amount,
                        'discount_applied' => $discount,
                        'quantity'         => $quantity
                    ]
                ],
                'unit_amount' => $discountedAmount * 100,
            ],
            'quantity' => $quantity,
            'tax_rates' => ['txr_1RzAamSELFbSCeVPcjG1ASSQ']
        ];

        $cartSummary[] = [
            'name'             => $item['name'],
            'quantity'         => $quantity,
            'original_price'   => $amount,
            'discount_applied' => $discount,
            'final_price'      => $discountedAmount,
            'total_for_item'   => $discountedAmount * $quantity,
        ];
    }

    try {
        $sessionData = [
            'ui_mode' => 'embedded',
            'line_items' => $lineItems,
            'invoice_creation'=>['enabled'=>true],
            // 'invoice_url'=>'http://localhost:4200/#/cart-page?session_id={CHECKOUT_SESSION_ID}',
            'mode' => 'payment',
            'return_url' => 'http://localhost:4200/#/success-page?session_id={CHECKOUT_SESSION_ID}',
            'allow_promotion_codes' => false,
        ];

        // Do NOT set 'customer_email' so Stripe will not show email field

        $sessionData['metadata'] = [
            'order_id'               => uniqid('ORD_'),
            'subtotal_after_discount'=> number_format($cartTotal, 2),
            'total_discount'         => number_format($totalDiscount, 2),
            'item_count'             => count($cartItems)
        ];

        $session = $this->stripe->checkout->sessions->create($sessionData);

        // Save order/session info to DB if needed for invoice creation after payment

        return $this->successResponse([
            'clientSecret'            => $session->client_secret,
            'order_id'                => $session->metadata->order_id,
            'subtotal_after_discount' => $cartTotal,
            'total_discount'          => $totalDiscount,
            'items'                   => $cartSummary
        ]);

    } catch (ApiErrorException $e) {
        log_message('error', 'Stripe API Error: ' . $e->getMessage());
        return $this->errorResponse('Checkout error: ' . $e->getMessage(), 500);
    }
}

public function generateStripeInvoice(){
$json = $this->request->getJson(true);

}

/**
 * https://186aaf2d9d07.ngrok-free.app/api/Webhook


 * Stripe webhook endpoint to handle successful payment and create invoice.
 */
// public function stripeWebhook(): ResponseInterface
// {
//     $payload = file_get_contents('php://input');
//     $sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';
//     $endpoint_secret = 'your_webhook_secret'; // Set your Stripe webhook secret

//     try {
//         $event = \Stripe\Webhook::constructEvent(
//             $payload, $sig_header, $endpoint_secret
//         );
//     } catch(\UnexpectedValueException $e) {
//         return $this->response->setStatusCode(400)->setJSON(['error' => 'Invalid payload']);
//     } catch(\Stripe\Exception\SignatureVerificationException $e) {
//         return $this->response->setStatusCode(400)->setJSON(['error' => 'Invalid signature']);
//     }

//     // Handle the checkout.session.completed event
//     if ($event->type === 'checkout.session.completed') {
//         $session = $event->data->object;

//         // You can access $session->metadata['order_id'] and other info here
//         // Create invoice logic here (save to DB, generate PDF, send email, etc.)

//         // Example: Save invoice to DB (pseudo-code)
//         // $this->saveInvoice($session);

//         // Optionally, send invoice email to customer if you have their email
//     }

//     return $this->response->setStatusCode(200)->setJSON(['status' => 'success']);
// }


    private function successResponse(array $data): ResponseInterface
    {
        return $this->response->setJSON([
            'status' => 'success',
            'data' => $data
        ]);
    }

    private function errorResponse(string $message, int $code): ResponseInterface
    {
        return $this->response
            ->setStatusCode($code)
            ->setJSON([
                'status' => 'error',
                'message' => $message
            ]);
    }
}