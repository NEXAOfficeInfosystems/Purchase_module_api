<?php namespace App\Models\Api;

use CodeIgniter\Model;

class Bulk_Order_RequestModel extends Model
{
    protected $table = 'bulk_order_request';
    protected $primaryKey = 'Id';
    protected $useAutoIncrement = true; 

    protected $allowedFields = [
       'Id',
        'Product_variation_id',
        'bulkorder_id',
        'bulk_discount',
        'description',
        'Product_name',
        'total_tax',
        'sub_total',
        'total_discount',
        'shipping_cost',
        'grand_total',
        'Email',
        'Customer_Id',
        'Mob_number',
        'Shippingaddress_id',
        'Bulk_qty',
        'Status',
        'expected_delivery_date',
        'CreatedDate',
        'CreatedBy',
        'ModifiedDate',
        'ModifiedBy',
        'DeletedDate',
        'DeletedBy',
        'IsDeleted',
    ];

    public function getAllRequests()
    {
        return $this->findAll();
    }

  
}
