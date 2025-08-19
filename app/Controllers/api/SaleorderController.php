<?php
namespace App\Controllers\Api;
use App\Models\api\SaleorderModel;
use CodeIgniter\RESTful\ResourceController;

class SaleorderController extends ResourceController
{
    public function index()
    {
        $SaleorderModels = new SaleorderModel();
        $OrderNumber = $this->request->getGet('OrderNumber');
        $CustomerId = $this->request->getGet('CustomerId');
        if (isset($OrderNumber)) {
            $SaleorderModels->where('OrderNumber', $OrderNumber);
        }
        if (isset($CustomerId)) {
            $SaleorderModels->where('CustomerId', $CustomerId);
        }
        $result = $SaleorderModels->findAll();
        return $this->respond($result);
    }

    // public function getsaleorder()
    // {
    //     $SaleOrderModels = new SaleorderModel;
    //     $requestMethod = $this->request->getMethod();
    //     if ($requestMethod !== 'get') {
    //         return $this->response->setStatusCode(400)->setJSON(['error' => 'Invalid request method']);
    //     } else {
    //         $headers = $this->request->getHeaders();
    //         if (!isset($headers['AUTHORIZATION'])) {
    //             $orderNumber = 'SO#00001';
    //             if (isset($orderNumber)) {
    //                 $result = $SaleOrderModels->getSaleOrderData($orderNumber);
    //                 return $this->response->setStatusCode(200)->setJSON(['mesaage' => 'Successfully Fetch Data', 'ResponseData' => $result]);
    //             } else {
    //                 return $this->response->setStatusCode(401)->setJSON(['error' => 'Invaild Order Id']);
    //             }
    //         } else {
    //             return $this->response->setStatusCode(401)->setJSON(['error' => 'Authorization header is missing']);

    //         }
    //     }

    // }  // ============= GETSALEORDR ================
}
