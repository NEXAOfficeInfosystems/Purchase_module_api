<?php

namespace App\Controllers\api;

use CodeIgniter\Controller;
use Config\Services;
use Stripe\Stripe;
use Stripe\Webhook;
use Stripe\Checkout\Session as StripeCheckoutSession;

class StripeWebhook extends Controller
{
    public function index()
    {
        $payload    = @file_get_contents('php://input');
        $sigHeader  = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';
        $endpointSecret = env('stripe.webhook_secret');

        // 1) Verify Stripe signature
        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $endpointSecret);
        } catch (\UnexpectedValueException $e) {
            return $this->response->setStatusCode(400, 'Invalid payload');
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            return $this->response->setStatusCode(400, 'Invalid signature');
        }

        // 2) Process only the events you need
        if ($event->type === 'checkout.session.completed') {
            $session = $event->data->object;

            // Prevent double-send if Stripe retries:
            if ($this->alreadyProcessed($event->id)) {
                return $this->response->setStatusCode(200);
            }

            // Init Stripe for any follow-up API calls
            Stripe::setApiKey(env('stripe.secret'));

            // Get richer info: line items
            $sessionId = $session->id;
            $lineItems = StripeCheckoutSession::allLineItems($sessionId, ['limit' => 100]);

            $customerEmail = $session->customer_details->email ?? null;
            $orderId = $session->metadata->order_id ?? ('ORD_' . $sessionId);
            $amountTotal = ($session->amount_total ?? 0) / 100; // INR

            // Build invoice HTML (or use a CI view)
            $html = view('emails/invoice_template', [
                'orderId'      => $orderId,
                'amount'       => $amountTotal,
                'paymentStatus'=> $session->payment_status,
                'currency'     => strtoupper($session->currency ?? 'INR'),
                'lineItems'    => $lineItems->data ?? [],
                'customerEmail'=> $customerEmail,
                'created'      => date('Y-m-d H:i:s', $session->created ?? time()),
                'sessionId'    => $sessionId,
            ]);

            // (Optional) Generate PDF
            $pdfPath = null;
            try {
                $pdfPath = $this->generatePdf($orderId, $html); // comment out if not using mPDF
            } catch (\Throwable $e) {
                log_message('error', 'PDF generation failed: ' . $e->getMessage());
            }

            // Send email via SMTP
            $this->sendInvoiceEmail(
                to: $customerEmail,
                orderId: $orderId,
                htmlBody: $html,
                attachmentPath: $pdfPath
            );

            // Mark event as processed (DB/file/redis)
            $this->markProcessed($event->id);
        }

        return $this->response->setStatusCode(200);
    }

    private function sendInvoiceEmail(string $to, string $orderId, string $htmlBody, ?string $attachmentPath = null): void
    {
        $email = Services::email();

        // Pull defaults from .env (optional overrides)
        $email->setFrom(env('email.fromEmail'), env('email.fromName'));
        $email->setTo($to);
        $email->setSubject('Invoice for Order #' . $orderId);
        $email->setMessage($htmlBody);

        if ($attachmentPath && is_file($attachmentPath)) {
            $email->attach($attachmentPath);
        }

        if (!$email->send()) {
            log_message('error', 'Email send failed: ' . print_r($email->printDebugger(['headers', 'subject']), true));
        } else {
            log_message('info', 'Invoice email sent to ' . $to);
        }
    }

    private function generatePdf(string $orderId, string $html): ?string
    {
        // Requires composer require mpdf/mpdf
        $invoicesDir = WRITEPATH . 'invoices/';
        if (!is_dir($invoicesDir)) {
            @mkdir($invoicesDir, 0775, true);
        }
        $file = $invoicesDir . $orderId . '.pdf';

        $mpdf = new \Mpdf\Mpdf();
        $mpdf->WriteHTML($html);
        $mpdf->Output($file, \Mpdf\Output\Destination::FILE);
        return $file;
    }

    // ---- Idempotency helpers (implement with DB in real app) ----
    private function alreadyProcessed(string $eventId): bool
    {
        $flagFile = WRITEPATH . 'cache/stripe_' . md5($eventId) . '.done';
        return is_file($flagFile);
    }

    private function markProcessed(string $eventId): void
    {
        $flagFile = WRITEPATH . 'cache/stripe_' . md5($eventId) . '.done';
        @file_put_contents($flagFile, '1');
    }
}
