<?php

namespace App\Models\Customer;

use CodeIgniter\Model;

class ShopsModel extends Model
{
    protected $table            = 'shops';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        "id",
        "user_id",
        "is_approved",
        "is_verified_by_admin",
        "is_published",
        "shop_logo",
        "shop_name",
        "slug",
        "shop_rating",
        "shop_address",
        "min_order_amount",
        "admin_commission_percentage",
        "current_balance",
        "is_cash_payout",
        "is_bank_payout",
        "bank_name",
        "bank_acc_name",
        "bank_acc_no",
        "bank_routing_no",
        "created_at",
        "updated_at",
        "deleted_at"
    ];

    protected bool $allowEmptyInserts = false;

    // Dates
    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];
}
