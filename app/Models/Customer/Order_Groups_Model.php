<?php

namespace App\Models\Customer;

use CodeIgniter\Model;


class Order_Groups_Model extends Model
{
    protected $DBGroup = 'default';
    protected $table = 'order_groups';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [

        "id",
        "user_id",
        "guest_user_id",
        "order_code",
        "shipping_address_id",
        "billing_address_id",
        "location_id",
        "phone_no",
        "alternative_phone_no",
        "sub_total_amount",
        "total_tax_amount",
        "total_coupon_discount_amount",
        "total_shipping_cost",
        "grand_total_amount",
        "payment_method",
        "payment_status",
        "payment_details",
        "is_manual_payment",
        "manual_payment_details",
        "is_pos_order",
        "pos_order_address",
        "additional_discount_value",
        "additional_discount_type",
        "total_discount_amount",
        "total_tips_amount",
        "created_at",
        'updated_at',
        'deleted_at'
    ];

    // Dates
    protected $useTimestamps = false;
    protected $dateFormat = 'datetime';
    // protected $createdField  = 'created_at';
    // protected $updatedField  = 'updated_at';
    // protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules = [];
    protected $validationMessages = [];
    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert = [];
    protected $afterInsert = [];
    protected $beforeUpdate = [];
    protected $afterUpdate = [];
    protected $beforeFind = [];
    protected $afterFind = [];
    protected $beforeDelete = [];
    protected $afterDelete = [];
}
