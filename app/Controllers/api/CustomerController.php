<?php

namespace App\Controllers\Api;
use TCPDF;

use App\Models\api\CustomersModel;
use App\Models\api\CustomerBankDetailsModel;
use CodeIgniter\RESTful\ResourceController;
use App\Models\Customer\UsersModel;
use App\Models\Customer\UserAddressesModel;
use App\Models\Customer\StatesModel;
use App\Models\Customer\CitiesModel;
use App\Models\Customer\CountriesModel;
use App\Models\Customer\ContactusModel;
use App\Models\NotificationModel;
use CodeIgniter\API\ResponseTrait;


use App\Models\api\CartsModel;

use App\Models\Customer\ProductsModel;
use App\Models\Customer\OrdersModel;
use App\Models\Customer\Order_Groups_Model;
use App\Models\Customer\Order_Items_Model;

use App\Models\api\ProductVariationsModel;
use App\Models\api\ProductVariationStocksModel;
use App\Models\Customer\Media_managersModel;
use App\Models\Customer\PaymentgatwayModel;
use App\Models\Customer\TaxesModel;
use App\Models\Customer\ShopsModel;


class CustomerController extends ResourceController
{

    public function index()
    {
        $CustomersModels = new CustomersModel();
        $Email = $this->request->getGet('Email');
        if (isset($Email)) {
            $CustomersModels->where('Email', $Email);
        }
        $result = $CustomersModels->find();
        $transformedProducts = $this->transformProducts($result);
        return $this->respond($transformedProducts);
    }

    protected function transformProducts($products)
    {
        $transformedProducts = [];
        foreach ($products as $product) {

            $transformedProducts = [
                'id' => $product['Id'],
                'name' => $product['CustomerName'],
                'email' => $product['Email'],
                'phone' => $product['MobileNo'],
                'country_code' => 1,
                'profile_image' => null,
                'profile_image_id' => 1,
                'status' => 1,
                'email_verified_at' => $product['IsVarified'],
                'payment_account' => [],
                'role_id' => 1,
                'role_name' => 'consumer',
                'role' => [],
                'permission' => [],
                'address' => [],
                'point' => [],
                'wallet' => [],
                'orders_count' => [],
                'is_approved' => 1,
                'created_at' => $product['CreatedDate'],
                'updated_at' => $product['ModifiedDate'],
                'deleted_at' => $product['IsDeleted']
            ];
        }

        return $transformedProducts;
    }


    public function saveBankDetails()
    {
        // Get JSON data from the request
        $json = $this->request->getJSON();
        $CustomerBankDetailsModel = new CustomerBankDetailsModel();
        // Generate a unique ID using UUID
        $guid = Uuid::uuid4()->toString();
        $CustomerId = isset($json->CustomerId) ? $json->CustomerId : null;
        $AccountNo = isset($json->AccountNo) ? $json->AccountNo : null;
        $BankName = isset($json->BankName) ? $json->BankName : null;
        $HolderName = isset($json->HolderName) ? $json->HolderName : null;
        $Swift = isset($json->Swift) ? $json->Swift : null;
        $IFSC = isset($json->IFSC) ? $json->IFSC : null;

        if ($CustomerId) {
            $BankDetails = [
                'Id' => $guid,
                'CustomerId' => $CustomerId,
                'AccountNo' => $AccountNo,
                'BankName' => $BankName,
                'HolderName' => $HolderName,
                'Swift' => $Swift,
                'IFSC' => $IFSC,

            ];
            //  $cartModel->insert($cartData); 
            return $this->respond(['message' => 'Bank Details saved successfuly']);

        } else {
            return $this->failUnauthorized('Customer Id  is a must');
        }
    }
    
    public function sendEmailAndStoreData()
    {
        $notifyModel = new NotificationModel();
        $system = new \App\Models\api\System_settingsModel();
        $json = $this->request->getJSON();
        $order_number = $json->order_number;
        $email1 = $json->email;
        $name = $json->user_name;
    
        // Fetch order details
        $orderDetails = $this->trackOrderlist($order_number);

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
$image = 'https://'.$_SERVER['HTTP_HOST'].'/'.$mockedData['navbar_logo']['image_data']['media_file'];
    // print_r($orderDetails);
    // die();
        if (!$orderDetails) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Order not found']);
        }
    /////////////////////////////////////////////////////////////////////////
        // Generate PDF Invoice
        // $pdfFilePath = WRITEPATH . "invoices/invoice_$order_number.pdf";
       
        $pdfData = $this->generateInvoicePDF($orderDetails,$image);
        // Generate Email Template
        //////////////////////////////////////////////////////////////////////////////
        $emailBody = $this->generateEmailTemplate($orderDetails,$name,$image);
    
        try {
            $email = \Config\Services::email();
            $email->initialize([
                'protocol' => 'smtp',
                'SMTPHost' => 'smtp.gmail.com',
                'SMTPPort' => 587,
                'SMTPAuth' => true,
                'SMTPUser' => 'officeinfosystems2024@gmail.com',
                'SMTPPass' => 'msiz gltz miut vtcr',
                'mailType' => 'html',
                'SMTPCrypto' => 'tls',
                'newline' => "\r\n"
            ]);
    
            $email->setFrom('officeinfosystems2024@gmail.com', 'Planet Nursery');
            $email->setTo($email1);
            $email->setSubject('Your Order Invoice');
            $email->setMessage($emailBody);


            /////////////////////////////////////////////////////
            $file_content = file_get_contents($pdfData);
            $encoded_content = chunk_split(base64_encode($file_content));
            
            $email->attach($file_content, 'attachment', 'invoice.pdf', 'application/pdf',);
    ////////////////////////////////////////////////////////////////////////
            if (!$email->send()) {
                $errorMessage = $email->printDebugger(['headers']);
                return $this->response->setStatusCode(500)->setJSON(['error' => 'Failed to send email']);
            }
    
            return $this->response->setStatusCode(200)->setJSON(['message' => 'Email with invoice sent successfully']);
    
        } catch (\Exception $e) {
            return $this->response->setStatusCode(500)->setJSON(['error' => $e->getMessage()]);
        }
    }
    
    public function getCustomer()
    {
        try {
            $UsersModel = new UsersModel();
            $user_id = $this->request->getGet('user_id');
            if (isset($user_id)) {
                $UsersModel->where('id', $user_id);
                $result = $UsersModel->find();
                $userAddressModel = new UserAddressesModel();
                $CountriesModels = new CountriesModel();
                $StatesModels = new StatesModel();
                $CitiesModels = new CitiesModel();
                $userId = $user_id;
                $userAddresses = $userAddressModel->getUserAddressDetails($userId);
                $userAddressDetails = [];
                foreach ($userAddresses as $response) {
                    $CountriesModels->where('id', $response['country_id']);
                    $StatesModels->where('id', $response['state_id']);
                    $CitiesModels->where('id', $response['city_id']);

                    $userAddressDetails[] = [
                        'id' => $response['id'],
                        'title' => $response['title'],
                        'user_id' => $response['user_id'],
                        'street' => $response['address'],
                        'city' => $CitiesModels->first(),
                        'city_id' => $response['city_id'],
                        'pincode' => $response['pincode'],
                        'is_default' => $response['is_default'],
                        'country_code' => isset($response['country_code']) ? $response['country_code'] : null,
                        'phone' => $response['phone'],
                        'country_id' => $response['country_id'],
                        'state_id' => $response['state_id'],
                        'country' => $CountriesModels->first(),
                        'state' => $StatesModels->first(),
                        'type' => $response['type']
                    ];
                }

                $responsedata[] = [
                    'id' => $result[0]['id'],
                    'name' => $result[0]['name'],
                    'email' => $result[0]['email'],
                    'country_code' => '',
                    'phone' => $result[0]['phone'],
                    'profile_image_id' => 1,
                    'system_reserve' => 1,
                    'status' => 0,
                    'created_by_id' => 1,
                    'email_verified_at' => $result[0]['email_or_otp_verified'],
                    'created_at' => null,
                    'updated_at' => null,
                    'orders_count' => 8,
                    'role' => [],
                    'store' => null,
                    'point' => [],
                    'wallet' => [],
                    'address' => $userAddressDetails,
                    'vendor_wallet' => '',
                    'profile_image' => '',
                    'payment_account' => '',

                ];
                $data = $responsedata;
                $message = [
                    'message' => 'Success!',
                    'status' => 200
                ];

                return $this->response->setStatusCode(200)->setJSON(['messageobject' => $message, 'data' => $data]);
            } else {
                $message = [
                    'message' => 'userId not found',
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
    }   //////// ================ GETCUSTOMER ==================


    public function getStates()
    {
        try {
            $StatesModels = new StatesModel();
            $data = $StatesModels->findAll();

            $resposneData = [];
            foreach ($data as $resposne) {

                $resposneData[] = [
                    'id' => $resposne['id'],
                    'country_id' => $resposne['country_id'],
                    'name' => $resposne['name'],
                    'is_active' => $resposne['is_active'],
                    'created_at' => $resposne['created_at'],
                    'updated_at' => $resposne['updated_at'],
                    'deleted_at' => $resposne['deleted_at'],
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

    public function getcities()
    {
        try {
            $CitiesModels = new CitiesModel();
            $data = $CitiesModels->findAll();

            $resposneData = [];

            foreach ($data as $resposne) {

                $resposneData[] = [
                    'id' => $resposne['id'],
                    'state_id' => $resposne['state_id'],
                    'name' => $resposne['name'],
                    'is_active' => $resposne['is_active'],
                    'created_at' => $resposne['created_at'],
                    'updated_at' => $resposne['updated_at'],
                    'deleted_at' => $resposne['deleted_at'],
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

    public function getcountries()
    {
        try {
            $countriesModels = new countriesModel();
            $data = $countriesModels->findAll();

            $resposneData = [];

            foreach ($data as $resposne) {

                $resposneData[] = [
                    'id' => $resposne['id'],
                    'code' => $resposne['code'],
                    'name' => $resposne['name'],
                    'is_active' => $resposne['is_active'],
                    'created_at' => $resposne['created_at'],
                    'updated_at' => $resposne['updated_at'],
                    'deleted_at' => $resposne['deleted_at'],
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

    public function createContactus()
    {
        $json = $this->request->getJSON();
        $ContactusModel = new ContactusModel();
        $first_name = isset($json->first_name) ? $json->first_name : null;
        $last_name = isset($json->last_name) ? $json->last_name : null;
        $email = isset($json->email) ? $json->email : null;
        $phone = isset($json->phone) ? $json->phone : null;
        $support_for = isset($json->support_for) ? $json->support_for : null;
        $message = isset($json->message) ? $json->message : null;

        $data = [
            "first_name" => $first_name,
            "last_name" => $last_name,
            "email" => $email,
            "phone" => $phone,
            "support_for" => $support_for,
            "message" => $message
        ];

        $ContactusModel->insert($data);
        $result = [
            'message' => 'Inserted successfully',
            'status' => 200
        ];
        return $this->response->setStatusCode(200)->setJSON(['messageobject' => $result, 'data' => "Inserted successfully"]);

    }



    public function AddEditAddress()
    {
        $json = $this->request->getJSON();
        $userAddressModel = new UserAddressesModel();
        $addlist = false;
        if (isset($json->id)) {
            $userAddressModel->where('id', $json->id);
            $addlist = $userAddressModel->findAll();
        }
  if (isset($json->is_default) && ($json->is_default == 1||$json->is_default == "1")){
            // If is_default is set to 1, update all other addresses to not default
            $userAddressModel->set(['is_default' => 0])
                ->where('user_id', $json->user_id)
                ->update();
        } 

        $dataAddress = [
            'user_id' => $json->user_id,
            'country_id' => $json->country_id,
            "type" => $json->address_type,
            'state_id' => $json->state_id,
            'city_id' => $json->city_id,
            'address' => $json->address,
            'is_default' => $json->is_default,
            "phone" => $json->phone,
            "title" => $json->title,
            "pincode" => $json->pincode,
            "country_code" => $json->country_code,
            "created_at" => date('Y-m-d H:i:s')
        ];
        if ($addlist) {
            $userAddressModel->set($dataAddress)->where('id', $json->id)->update();
            $result = [
                'message' => 'updated Successfully',
                'status' => 200
            ];
            return $this->response->setStatusCode(200)->setJSON(['messageobject' => $result, 'data' => "updated successfully"]);

        } else {
            $userAddressModel->insert($dataAddress);
            $result = [
                'message' => 'created Successfully',
                'status' => 200
            ];
            return $this->response->setStatusCode(200)->setJSON(['messageobject' => $result, 'data' => "created successfully"]);

        }
    }



    // public function CopyAddress()
    // {
    //     $jsonArray = $this->request->getJSON(true);
    //     $userAddressModel = new UserAddressesModel();
    //     $u_id = $this->request->getGet('user_id');
    //     $responses = [];

    //     foreach ($jsonArray as $jsonData) {
    //         $user_id = $u_id;
    //         $title = $jsonData['title'];
    //         $street = $jsonData['street'];
    //         $city_id = $jsonData['city']['id'];
    //         $country_id = $jsonData['country']['id'];
    //         $state_id = $jsonData['state']['id'];
    //         $is_default = $jsonData['is_default'];
    //         $phone = $jsonData['phone'];
    //         $pincode = $jsonData['pincode'];
    //         $country_code = $jsonData['country_code'];
    //         $address_type = $jsonData['type'];

    //         $dataAddress = [
    //             'user_id' => $user_id,
    //             'country_id' => $country_id,
    //             // 'type' => $address_type,
    //             'type' => 'Billing',
    //             'state_id' => $state_id,
    //             'city_id' => $city_id,
    //             'address' => $street,
    //             'is_default' => $is_default,
    //             'phone' => $phone,
    //             'title' => $title,
    //             'pincode' => $pincode,
    //             'country_code' => $country_code,
    //             'created_at' => date('Y-m-d H:i:s')
    //         ];


    //         if (isset($jsonData['id'])) {
    //             $address_id = $jsonData['id'];
    //             $userAddressModel->where('id', $address_id);
    //             $existingAddress = $userAddressModel->findAll();
    //             if ($existingAddress) {

    //                 $userAddressModel->set($dataAddress)->where('id', $address_id)->update();
    //                 $responses[] = [
    //                     'message' => 'Address updated successfully',
    //                     'address_id' => $address_id,
    //                     'status' => 200
    //                 ];
    //             } else {

    //                 $responses[] = [
    //                     'message' => 'Address not found for ID: ' . $address_id,
    //                     'status' => 404
    //                 ];
    //             }
    //         } else {

    //             $userAddressModel->insert($dataAddress);
    //             $newAddressId = $userAddressModel->getInsertID();
    //             $responses[] = [
    //                 'message' => 'Address created successfully',
    //                 'new_address_id' => $newAddressId,
    //                 'status' => 200
    //             ];
    //         }
    //     }

    //     return $this->response->setStatusCode(200)->setJSON(['responses' => $responses]);
    // }

    public function CopyAddress()
    {
        $jsonArray = $this->request->getJSON(true);
        $userAddressModel = new UserAddressesModel();
        $u_id = $this->request->getGet('user_id');
        $responses = [];
    
        foreach ($jsonArray as $jsonData) {
            $user_id = $u_id;
            $title = $jsonData['title'];
            $street = $jsonData['street'];
            $city_id = $jsonData['city']['id'];
            $country_id = $jsonData['country']['id'];
            $state_id = $jsonData['state']['id'];
            $is_default = $jsonData['is_default'];
            $phone = $jsonData['phone'];
            $pincode = $jsonData['pincode'];
            $country_code = $jsonData['country_code'];
            $address_type = $jsonData['type']; 
    
            $dataAddress = [
                'user_id' => $user_id,
                'country_id' => $country_id,
                'state_id' => $state_id,
                'city_id' => $city_id,
                'address' => $street,
                'is_default' => $is_default,
                'phone' => $phone,
                'title' => $title,
                'pincode' => $pincode,
                'country_code' => $country_code,
                'created_at' => date('Y-m-d H:i:s'),
            ];
    
        
            $existingAddress = $this->checkExistingAddress($user_id, $street, $city_id, $state_id, $country_id, $pincode, $country_code,$address_type);
            if ($existingAddress) {
                return $this->response->setStatusCode(400)->setJSON([
                    'message' => 'Address already exists for this user.',
                    'status' => 400
                ]);
            }

            if ($address_type === 'Shipping' || $address_type === 'Billing') {
                $this->handleAddressInsertOrUpdate($userAddressModel, $address_type, $dataAddress, $jsonData, $responses);
            }
        }
    
       
    return $this->response->setStatusCode(200)->setJSON([
        'message' => 'Address copy operation completed successfully',
        'status' => 200
    ]);
    }
    
    private function checkExistingAddress($user_id, $street, $city_id, $state_id, $country_id, $pincode, $country_code,$address_type)
    {
        return (new UserAddressesModel())
            ->where('user_id', $user_id)
            ->where('address', $street)
            ->where('city_id', $city_id)
            ->where('state_id', $state_id)
            ->where('country_id', $country_id)
            ->where('pincode', $pincode)
            ->where('country_code', $country_code)
            ->where('type', $address_type) 
            ->first();
    }
    
    private function handleAddressInsertOrUpdate($userAddressModel, $address_type, $dataAddress, $jsonData, &$responses)
    {
   
        if ($address_type === 'Billing' && isset($jsonData['id'])) {
            $address_id = $jsonData['id'];
            $existingAddress = $userAddressModel->where('id', $address_id)->first();
    
            if ($existingAddress) {
                $userAddressModel->set($dataAddress)->where('id', $address_id)->update();
                return $this->response->setStatusCode(200)->setJSON([
                    'message' => 'Billing address updated successfully',
                    'address_id' => $address_id,
                    'status' => 200
                ]);
            } else {
                return $this->response->setStatusCode(404)->setJSON([
                    'message' => 'Billing address not found for ID: ' . $address_id,
                    'status' => 404
                ]);
            }
        } else {
          
            $dataAddress['type'] = $address_type;
            $userAddressModel->insert($dataAddress);
            $newAddressId = $userAddressModel->getInsertID();
            return $this->response->setStatusCode(200)->setJSON([
                'message' => ucfirst($address_type) . ' address created successfully',
                'new_address_id' => $newAddressId,
                'status' => 200
            ]);
        }
    }
    
    
    private function generateEmailTemplate($orderDetails, $userName,$image)
    {
      return "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 20px; }
                .email-container { max-width: 600px; margin: auto; background: #fff; padding: 20px; border-radius: 10px; box-shadow: 0px 0px 10px #ccc; }
                .banner { text-align: center; }
                .banner img { width: 100%; border-radius: 10px; }
                .content { padding: 20px; }
                .footer { text-align: center; font-size: 12px; color: #666; padding-top: 20px; border-top: 1px solid #ddd; }
                table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                th, td { padding: 10px; border: 1px solid #ddd; text-align: left; }
                th { background-color: #f8f8f8; }
                .btn { background-color: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block; margin-top: 20px; }
            </style>
        </head>
        <body>
            <div class='email-container'>
                <div class='banner'>
                    <img src='$image' alt='Planet Nursery Invoice'>
                </div>
                <div class='content'>
                    <h2>Invoice for Order #{$orderDetails['id']}</h2>
                    <p>Dear Customer,</p>
                    <p>Thank you for your purchase. Please find below the details of your order.</p>
    
                    <table>
                        <tr><th>Order Number</th><td>{$orderDetails['id']}</td></tr>
                        <tr><th>Order Date</th><td>{$orderDetails['created_at']}</td></tr>
                        <tr><th>Customer Name</th><td>{$userName}</td></tr>
                        <tr><th>Total Amount</th><td>{$orderDetails['TotalAmount']}</td></tr>
                    </table>
    
                  
                </div>
    
                <div class='footer'>
                    <p>Ground Floor, No. 5115/3/3 - Opposite To Al Amin Mosque North Al Khuwair,Muscat (Sultanate Of Oman), P.O. Box: 3519, PC: 111, CPO Seeb, Complex No. 245 – Way 4557</p>
                    <p>Email: planet@gmail.com | Phone: +968 2200 4900</p>
                </div>
            </div>
        </body>
        </html>
        ";
    
    }
    
//   <p>You can also download the invoice by clicking the button below:</p>
//                      <a href='http://188.135.62.197:8081/Planery-User-Api/public/uploads/invoices/invoice_{$orderDetails['id']}.pdf' class='btn'>Download Invoice</a>

    public function useraddressDelete()
    {

        $userAddress = new UserAddressesModel();
        $json = $this->request->getJSON();
        $userAddress->delete($json->id);
        $data = "Delete successfully";
        return $this->respond(['data' => $data]);
    }

// private function generateInvoicePDF($orderDetails)
// {
//     $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    
//     // PDF document information
//     $pdf->SetCreator(PDF_CREATOR);
//     $pdf->SetAuthor('Planet Nursery');
//     $pdf->SetTitle('Invoice - ' . ($orderDetails['order_number'] ?? ''));
//     $pdf->SetSubject('Order Invoice');
    
//     // Remove default header/footer
//     $pdf->setPrintHeader(false);
//     $pdf->setPrintFooter(false);
    
//     // Set margins
//     $pdf->SetMargins(15, 15, 15);
//     $pdf->SetAutoPageBreak(true, 25);
    
//     // Add a page
//     $pdf->AddPage();
    
//     // Invoice HTML content with TCPDF-compatible styling
//     $html = '
//     <style>
//         body {
//             font-family: helvetica;
//             font-size: 10pt;
//             color: #444444;
//             line-height: 1.5;
//         }
//         .invoice-header {
//             width: 100%;
//             margin-bottom: 15px;
//         }
//         .logo {
//             width: 150px;
//         }
//         .invoice-title {
//             font-size: 18pt;
//             color: #2a7ae2;
//             font-weight: bold;
//             text-align: right;
//         }
//         .details-table {
//             width: 100%;
//             margin-bottom: 15px;
//             border-collapse: collapse;
//             padding-top: 10px;
//         }
//         .details-cell {
//             width: 50%;
//             vertical-align: top;
//             padding: 10px 10px 20px 10px;
//             padding
//             border: 1px solid #e0e0e0;
//             background-color: #f8f9fa;
//         }
//         .section-title {
//             font-weight: bold;
//             color: #2a7ae2;
//             margin-bottom: 5px;
//             border-bottom: 1px solid #e0e0e0;
//             padding-bottom: 3px;
//         }
//         .products-table {
//             width: 100%;
//             border-collapse: collapse;
//             margin: 15px 0;
//         }
//         .products-table th {
//             background-color: #2a7ae2;
//             color: white;
//             text-align: left;
//             padding: 8px;
//             font-weight: bold;
//         }
//         .products-table td {
//             padding: 8px;
//             border-bottom: 1px solid #e0e0e0;
//         }
//         .products-table tr.alternate {
//         padding-top: 15px;
//         padding-bottom: 15px;
//             background-color: #f9f9f9;
//         }
//         .text-right {
//             text-align: right;
//         }
//         .text-center {
//             text-align: center;
//         }
//         .totals-table {
//             width: 40%;
//             float: right;
//             border-collapse: collapse;
//             margin-top: 15px;
//         }
//         .totals-table td {
//             padding: 6px 8px;
//             border-bottom: 1px solid #e0e0e0;
//         }
//         .grand-total {
//             font-weight: bold;
//             color: #2a7ae2;
//         }
//         .payment-info {
//             margin-top: 20px;
//             padding: 10px;
//             background-color: #fff8e1;
//             border-left: 4px solid #ffc107;
//             font-size: 9pt;
//         }
//         .footer {
//             margin-top: 30px;
//             padding-top: 10px;
//             border-top: 1px solid #e0e0e0;
//             font-size: 8pt;
//             text-align: center;
//             color: #777777;
//         }
//     </style>
    
//     <!-- Header -->
//     <table class="invoice-header">
//         <tr>
//             <td width="60%">
//                 <img class="logo" src="https://nexavault.net/pn-adminapi/uploads/media/1749394443_e8d25091acc4c46eace4.png" alt="Planet Nursery Logo">
//             </td>
//             <td width="40%" style="text-align: right;">
//                 <div class="invoice-title">INVOICE </div>
//                 <div><strong>Date:</strong> ' . (isset($orderDetails["created_at"]) ? date('F j, Y', strtotime($orderDetails["created_at"])) : date('F j, Y')) . '</div>
//                 <div><strong>Invoice #:</strong>' . ($orderDetails["order_number"] ?? '') . '</div>
//                 <div><strong>Status:</strong> ' . ($orderDetails["payment_status"] ?? 'UNPAID') . '</div>
//             </td>
//         </tr>
//     </table>
    
//     <!-- From and Bill To sections -->
//     <table class="details-table">
//         <tr>
//             <td class="details-cell">
//                 <div class="section-title">FROM</div>
//                 <div><strong>Planet Nursery</strong></div>
//                 <div>123 Green Street, City</div>
//                 <div>State 12345, Country</div>
//                 <div>Phone: +123456789</div>
//                 <div>Email: support@planetnursery.com</div>
//             </td>
//             <td class="details-cell">
//                 <div class="section-title">BILL TO</div>
//                 <div><strong>' . ($orderDetails["user_name"] ?? 'Customer Name') . '</strong></div>
//                 <div>' . ($orderDetails["user_email"] ?? 'Email not provided') . '</div>
//                 <div>' . ($orderDetails["user_phone"] ?? 'Phone not provided') . '</div>
//                 <div>' . ($orderDetails["shipping_address"] ?? 'Address not provided') . '</div>
//             </td>
//         </tr>
//     </table>
    
//     <!-- Products table -->
//     <table class="products-table">
//         <thead>
//             <tr>
//                 <th width="40%">Item</th>
//                 <th width="15%" class="text-right">Unit Price</th>
//                 <th width="10%" class="text-center">Qty</th>
//                 <th width="15%" class="text-right">Tax</th>
//                 <th width="20%" class="text-right">Amount</th>
//             </tr>
//         </thead>
//         <tbody>';

//     if (!empty($orderDetails["productslist"]) && is_array($orderDetails["productslist"])) {
//         $counter = 0;
//         foreach ($orderDetails["productslist"] as $product) {
//             if (!is_array($product)) continue;
//             $rowClass = ($counter % 2) ? 'class="alternate"' : '';
//             $html .= '<tr ' . $rowClass . '>
//                 <td>' . ($product["name"] ?? '') . '</td>
//                 <td class="text-right">$' . number_format($product["price"] ?? 0, 2) . '</td>
//                 <td class="text-center">' . ($product["qty"] ?? 0) . '</td>
//                 <td class="text-right">$' . number_format($product["tax_amount"] ?? 0, 2) . '</td>
//                 <td class="text-right">$' . number_format($product["total_cost"] ?? 0, 2) . '</td>
//             </tr>';
//             $counter++;
//         }
//     } else {
//         $html .= '<tr>
//             <td colspan="5" style="text-align:center;">No products found.</td>
//         </tr>';
//     }

//     $html .= '</tbody>
//     </table>
    
//     <!-- Totals section -->
//     <table class="totals-table">
//         <tr>
//             <td>Subtotal:</td>
//             <td class="text-right">$' . number_format($orderDetails["total_cost"] ?? 0, 2) . '</td>
//         </tr>
//         <tr>
//             <td>Tax:</td>
//             <td class="text-right">$' . number_format($orderDetails["total_tax"] ?? 0, 2) . '</td>
//         </tr>
//         <tr>
//             <td>Discount:</td>
//             <td class="text-right">-$' . number_format($orderDetails["discount_value"] ?? 0, 2) . '</td>
//         </tr>
//         <tr>
//             <td class="grand-total">Total Amount:</td>
//             <td class="text-right grand-total">$' . number_format($orderDetails["TotalAmount"] ?? 0, 2) . '</td>
//         </tr>
//     </table>
    
//     <!-- Payment information -->
//     <div class="payment-info">
//         <strong>PAYMENT INFORMATION:</strong><br>
//         Please make payment within 7 days of invoice date.<br>
//         Bank: National Bank | Account: 123456789 | Sort Code: 12-34-56<br>
//         PayPal: payments@planetnursery.com
//     </div>
    
//     <!-- Footer -->
//     <div class="footer">
//         <p>Thank you for your business with Planet Nursery</p>
//         <p>If you have any questions about this invoice, please contact<br>
//             support@planetnursery.com or call +123456789</p>
//         <p style="margin-top: 10px;">Planet Nursery &copy; ' . date('Y') . ' | All Rights Reserved</p>
//     </div>';

//     $pdf->writeHTML($html, true, false, true, false, '');
    
//     // Generate unique filename
//     $filename = 'PN-INV-' . ($orderDetails["id"] ?? uniqid()) . '.pdf';
//     $pdfFilePath = FCPATH . "uploads" . DIRECTORY_SEPARATOR . "invoices" . DIRECTORY_SEPARATOR . $filename;
    
//     // Save the PDF
//     $pdf->Output($pdfFilePath, 'F');
    
//     return $pdfFilePath;
// }

private function generateInvoicePDF($orderDetails,$image)
{
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    
    // PDF document information
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('Planet Nursery');
    $pdf->SetTitle('Invoice - ' . ($orderDetails['order_number'] ?? ''));
    $pdf->SetSubject('Order Invoice');
    
    // Remove default header/footer
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    
    // Set margins
    $pdf->SetMargins(15, 15, 15);
    $pdf->SetAutoPageBreak(true, 25);
    
    // Add a page
    $pdf->AddPage();
    
    // Helper function for safe value fallback
    $getValue = function($val, $fallback = '') {
        return ($val !== null && $val !== '' && $val !== false && $val !== 'undefined') ? $val : $fallback;
    };
    
    // Invoice HTML content with TCPDF-compatible styling
    $html = '
    <style>
        body {
            font-family: helvetica;
            font-size: 10pt;
            color: #444444;
            line-height: 1.5;
        }
        .invoice-title {
            font-size: 18pt;
            color: #2a7ae2;
            font-weight: bold;
            text-align: right;
            margin-bottom: 5px;
        }
        .details-cell {
            font-size: 10pt;
            color: #444444;
            margin-bottom: 10px;
        }
        .section-title {
            font-size: 11pt;
            font-weight: bold;
            color: #2a7ae2;
            margin-bottom: 3px;
        }
        .table-header {
            background-color: #2a7ae2;
            color: white;
            font-weight: bold;
            font-size: 11pt;
        }
        .grand-total {
            font-weight: bold;
            color: #2a7ae2;
            font-size: 12pt;
        }
        .payment-info {
            font-size: 9pt;
            background-color: #fff8e1;
            color: #444444;
            margin-top: 20px;
            padding: 10px;
        }
        .footer {
            font-size: 8pt;
            text-align: center;
            color: #777777;
            margin-top: 30px;
        }
    </style>
    
    <!-- Header: Logo and Invoice Info -->
    <table width="100%" cellpadding="0" cellspacing="0" border="0">
        <tr>
            <td width="60%">
                <img src='.$image.' alt="Planet Nursery Logo" width="150">
            </td>
            <td width="40%" style="text-align:right;">
                <div class="invoice-title">INVOICE</div>
                <div><strong>Date:</strong> ' . $getValue(isset($orderDetails["created_at"]) ? date('F j, Y', strtotime($orderDetails["created_at"])) : date('F j, Y')) . '</div>
                <div><strong>Invoice #:</strong> ' . $getValue($orderDetails["order_number"], '') . '</div>
                <div><strong>Status:</strong> ' . $getValue($orderDetails["payment_status"], 'UNPAID') . '</div>
            </td>
        </tr>
    </table>
    
    <!-- From and Bill To sections -->
    <table width="100%" cellpadding="0" cellspacing="10" border="0" style="margin-bottom: 10px;">
        <tr>
            <td width="50%" style="vertical-align: top;">
                <div class="section-title">FROM</div>
                <div class="details-cell">
                    <strong>' . $getValue($orderDetails["system_title"] ?? 'Planet Nursery') . '</strong><br>
                    ' . $getValue($orderDetails["topbar_location"] ?? 'No address') . '<br>
                    Phone: ' . $getValue($orderDetails["navbar_contact_number"] ?? 'No Phone') . '<br>
                    Email: ' . $getValue($orderDetails["topbar_email"] ?? 'No Email') . '
                </div>
            </td>
            <td width="50%" style="vertical-align: top;">
                <div class="section-title">BILL TO</div>
                <div class="details-cell">
                    <strong>' . $getValue($orderDetails["user_name"] ?? 'Customer Name') . '</strong><br>
                    ' . $getValue($orderDetails["user_email"] ?? 'Email not provided') . '<br>
                    ' . $getValue($orderDetails["user_phone"] ?? 'Phone not provided') . '<br>
                    ' . $getValue($orderDetails["billing_address"] ?? 'Address not provided') . '
                </div>
            </td>
        </tr>
    </table>
    
    <!-- Products table -->
    <table width="100%" cellpadding="6" cellspacing="0" border="0" style="margin-bottom: 10px;">
        <thead>
            <tr class="table-header">
                <th width="40%" align="left">Product</th>
                <th width="15%" align="right">Unit Price</th>
                <th width="10%" align="center">Qty</th>
                <th width="15%" align="right">Tax</th>
                <th width="20%" align="right">Total</th>
            </tr>
        </thead>
        <tbody>';

    if (!empty($orderDetails["productslist"]) && is_array($orderDetails["productslist"])) {
        foreach ($orderDetails["productslist"] as $index => $product) {
            if (!is_array($product)) continue;
            $bgColor = ($index % 2) ? '#f9f9f9' : 'transparent';
            $html .= '<tr bgcolor="' . $bgColor . '">
                <td>' . $getValue($product["name"], '') . '</td>
                <td align="right">' . number_format($getValue($product["price"], 0), 2) . '</td>
                <td align="center">' . $getValue($product["qty"], 0) . '</td>
                <td align="right">' . number_format($getValue($product["tax_amount"], 0), 2) . '</td>
                <td align="right">' . number_format($getValue($product["total_cost"], 0), 2) . '</td>
            </tr>';
        }
    } else {
        $html .= '<tr>
            <td colspan="5" align="center">No products found.</td>
        </tr>';
    }

    $html .= '</tbody>
    </table>
    
    <!-- Totals section -->
    <table width="40%" cellpadding="6" cellspacing="0" border="0" align="right" style="margin-top: 10px;">
        <tr>
            <td align="left">Subtotal:</td>
            <td align="right">' . number_format($getValue($orderDetails["total_cost"], 0), 2) . '</td>
        </tr>
        <tr>
            <td align="left">Tax:</td>
            <td align="right">' . number_format($getValue($orderDetails["total_tax"], 0), 2) . '</td>
        </tr>
      
        <tr>
            <td align="left">Discount:</td>
            <td align="right">-' . number_format($getValue($orderDetails["discount_value"], 0), 2) . '</td>
        </tr>
        <tr>
            <td align="left" class="grand-total">Grand Total:</td>
            <td align="right" class="grand-total">' . number_format($getValue($orderDetails["TotalAmount"], 0), 2) . '</td>
        </tr>
    </table>
    
    <!-- Payment information -->
    <div class="payment-info">
        <strong>PAYMENT INFORMATION:</strong><br>
        Payment Method: ' . $getValue($orderDetails['order_group']["payment_method"], 'N/A') . '<br>
        Status: ' . $getValue($orderDetails["payment_status"], 'UNPAID') . '
    </div>
    
    <!-- Footer -->
    <div class="footer">
        ' . $getValue($orderDetails["invoice_thanksgiving"] ?? 'Thank you for your business!') . '<br>
        ' . $getValue($orderDetails["system_title"] ?? 'Planet Nursery') . ' &copy; ' . date('Y') . ' | All Rights Reserved
    </div>';

    $pdf->writeHTML($html, true, false, true, false, '');
    
    // Generate unique filename
    $filename = 'PN-INV-' . ($orderDetails["id"] ?? uniqid()) . '.pdf';
    $pdfFilePath = FCPATH . "uploads" . DIRECTORY_SEPARATOR . "invoices" . DIRECTORY_SEPARATOR . $filename;
    
    // Save the PDF
    $pdf->Output($pdfFilePath, 'F');
    
    return $pdfFilePath;
}
    public function trackOrderlist($order_id)
{
   
    $OrdersModels = new OrdersModel();
    $OrdersItemsModels = new Order_Items_Model();
    $ProductVariationsModels = new ProductVariationsModel();
    $ProductsModels = new ProductsModel();
    $MediaManagersModel = new Media_managersModel();
    $TaxesModels = new TaxesModel();
    $ShopsModel = new ShopsModel();
    $OrdersModels->where('id', $order_id);
    $orderlist = $OrdersModels->first();
    $order_group= new Order_Groups_Model();

    $OrdersItemsModels->where('order_id', $order_id);
    $orderItemlist = $OrdersItemsModels->findAll();

    $products = [];

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
        $product['thumbnail_image'] = $thumbnail['media_file'];
        
        if ($product['tax_id']) {
            $taxData = $TaxesModels->where('id', $provarlist['tax_id'])->first();
            $taxValue = $taxData['value'];
        } else {
            $taxValue = 0; 
        }


        $discountAmount = ($list['qty'] * $provarlist['price'] * $provarlist['discount_value']) / 100;
        $totalDiscount += $discountAmount;

    
        $taxAmount = ($list['qty'] * $provarlist['price'] * $taxValue) / 100;
        $totalTax += $taxAmount;


        $amt = $list['qty'] * $provarlist['price'];
        $total_cost += $amt;

    
        // $ShopsModel->where('id', $provarlist['shop_id']);
        // $shops = $ShopsModel->findAll();

        $product['qty'] = $list['qty'];
  
        $product['discount_value'] = $discountAmount;
        $product['tax_amount'] = $taxAmount;
        $product['total_cost'] = $amt; 
        $products[] = $product;
    }

    $orderlist['productslist'] = $products;

    $orderlist['total_cost'] = $total_cost;
    $orderlist['total_tax'] = $totalTax;
    $orderlist['discount_value'] = $totalDiscount;


    $TotalAmount = ($totalTax - $totalDiscount) + $total_cost;

    $orderlist['TotalAmount'] = $TotalAmount;
    
$orderlist['order_group'] = $order_group->where('order_code', $order_id)->first();

   
    return $orderlist;
}


    
}




