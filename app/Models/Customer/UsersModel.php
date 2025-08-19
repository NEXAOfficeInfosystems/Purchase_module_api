<?php

namespace App\Models\Customer;

use CodeIgniter\Model;

class UsersModel extends Model
{
    protected $table            = 'users';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'id',
        'role_id',
        'user_type',
        'name',
        'email',
        'phone',
        'token',
        'email_or_otp_verified',
        'verification_code',
        'new_email_verification_code',
        'password',
        'remember_token',
        'provider_id',
        'avatar',
        'postal_code',
        'location_id',
        'address',
        'user_balance',
        'is_banned',
        'is_active',
        'shop_id ',
        'email_verified_at',
        'created_at',
        'updated_at',
        'deleted_at',
        'created_by',
        'updated_by',
        'salary'
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
