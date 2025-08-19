<?php

namespace App\Models\api;

use CodeIgniter\Model;

class ProductVariationsModel extends Model
{
    protected $table = 'product_variations';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        "id",
        "product_id",
        "location_id",
        "variation_key",
        "variation_type",
        "variation_size",
        "variation_colour",
        "variation_name",
        "capacity",
        "sku",
        "code",
        "price",
        "stock_qty",
        "thumbnail_image",
        "short_description",
        "is_active",
        "is_published",
        "is_featured",
        "min_purchase_qty",
        "max_purchase_qty",
        "has_warranty",
        "total_sale_count",
        "standard_delivery_hours",
        "express_delivery_hours",
        "discount_value",
        "discount_type",
        "discount_start_date",
        "discount_end_date",
        "min_quantity_wholesale",
        "max_quantity_wholesale",
        "wholesale_discount",
        "wholesale_notes",
        "created_at",
        "updated_at",
        "deleted_at",
        "is_return",
        "return_policy_days"
    ];

    protected bool $allowEmptyInserts = false;

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    // Validation
    protected $validationRules = [];
    protected $validationMessages = [];
    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
   
}
