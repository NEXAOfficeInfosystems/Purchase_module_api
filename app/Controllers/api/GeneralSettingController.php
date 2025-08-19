<?php
namespace App\Controllers\Api;
use CodeIgniter\RESTful\ResourceController;
use App\Models\api\System_settingsModel;
use App\Models\api\MediaModel;

class GeneralSettingController extends ResourceController
{

public function getSettings()
{
    $System_settingsModels = new System_settingsModel();
    $MediaModel = new MediaModel();
    $data = $System_settingsModels->findAll();
    $mockedData = [];

    foreach ($data as $setting) {
        $image_details=$MediaModel->where('id',$setting['image_id'])->first();
        $mockedData[$setting["entity"]] = [
            "key" => $setting['entity'],
            "value" => $setting['value'],
            "image_data"=> $image_details
        ];
    }
    $message = [
        'message' => 'Settings get Successfully!',
        'status' => 200
    ];

    return $this->response->setStatusCode(200)->setJSON(['messageobject' => $message, 'data' => $mockedData]);
    
}

}