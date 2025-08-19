<?php

namespace App\Models\api;

use CodeIgniter\Model;

class ProductSelectedVariationsModel extends Model
{
    protected $table = 'product_variation_combination'; 
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'product_variation_id',
        'product_id',
        'variation_type_id',
        'selected_value'
    ];

    protected $useTimestamps = true; 
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    protected $validationRules = [
        'product_variation_id' => 'required|integer',
        'product_id' => 'required|integer',
        'variation_type_id' => 'required|integer'
    ];
    protected $validationMessages = [];
    protected $skipValidation = false;
}