<?php

namespace App\Models\Customer;

use CodeIgniter\Model;

class ProductsModel extends Model
{
    protected $table = 'products';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'id',
        'shop_id',
        'added_by',
        'name',
        'slug',
        'brand_id',
        'unit_id',
        'thumbnail_image',
        'gallery_images',
        'product_tags',
        'short_description',
        'description',
        'price',
        'min_price',
        'max_price',
        'discount_value',
        'discount_type',
        'discount_start_date',
        'return_policy_days',
        'is_return',
        'discount_end_date',
        'sell_target',
        'stock_qty',
        'is_published',
        'is_featured',
        'min_purchase_qty',
        'max_purchase_qty',
        'min_stock_qty',
        'has_variation',
        'has_warranty',
        'total_sale_count',
        'standard_delivery_hours',
        'express_delivery_hours',
        'size_guide',
        'meta_title',
        'meta_description',
        'meta_img',
        'tax_id',
        'reward_points',
        'created_at',
        'updated_at',
        'deleted_at',
        'vedio_link',
        'created_by',
        'updated_by',
        'is_import',
        'name_arabic',
        'short_description_arabic',
        'wholesale_notes_arabic'
    ];

    protected bool $allowEmptyInserts = false;

    // Dates
    protected $useTimestamps = false;
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
    protected $beforeInsert = [];
    protected $afterInsert = [];
    protected $beforeUpdate = [];
    protected $afterUpdate = [];
    protected $beforeFind = [];
    protected $afterFind = [];
    protected $beforeDelete = [];
    protected $afterDelete = [];

    public function searchproduct($search)
    {
        
        // Base query
        // $sql = "SELECT p.* 
        //         FROM product_categories as pc
        //         JOIN categories as c ON c.id = pc.category_id
        //         JOIN products as p ON p.id = pc.product_id
        //         WHERE p.is_published = 1";
    
        // Add search condition if a search term is provided
    //     if (!empty($search)) {
    //         $sql .= " AND LOWER(CONCAT(p.name, ' ')) LIKE LOWER()";
    //         
    //     } else {
    //         // Return default result if no search term is provided
    //         
    //     }

    $sql= "SELECT p.* 
FROM product_categories as pc
JOIN categories as c ON c.id = pc.category_id
JOIN products as p ON p.id = pc.product_id
WHERE p.is_published = 1
AND LOWER(CONCAT(p.name, ' ')) LIKE LOWER('%$search%')
LIMIT 0, 25;";


return $search!=""| $search!=null ? $this->query($sql, ['search' => "%$search%"])->getResultArray()
   : $this->query($sql)->getResultArray();   
}   
}
