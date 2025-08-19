<?php

namespace App\Models\Customer;

use CodeIgniter\Model;

class PaymentgatwayModel extends Model
{
    protected $table            = 'payment_gatway';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'id',
        'order_code',
        'customer_name',
        'customer_card_number',
        'card_exp_date',
        'amount',
        'balance_amount',
        'address_city',
        'address_country',
        'address_line1',
        'address_line1_check',
        'address_line2',
        'address_state',
        'address_zip',
        'address_zip_check',
        'brand',
        'country',
        'cvc_check',
        'dynamic_last4',
        'exp_month',
        'exp_year',
        'funding',
        'card_id',
        'last4',
        'name',
        'create_at',
        'transaction_id',
        'transaction_created',
        'transaction_client_ip',
        'transaction_response',

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
