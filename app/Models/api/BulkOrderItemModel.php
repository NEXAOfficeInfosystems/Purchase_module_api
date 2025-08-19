<?php namespace App\Models\Api;

use CodeIgniter\Model;

class BulkOrderItemModel extends Model
{
    protected $table = 'bulk_order_items';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true; 

    protected $allowedFields = [
        'id',
        'product_variation_id',
        'qty',
        'price',
        'total_price',
        'discount_type',
        
     'discount_value',
        'tax_id',
        'unit_id',
        'is_refunded',
        'location_id',
        'created_at',
        'updated_at',
        'bulk_order_id',
        
    ];

    public function getAllRequests()
    {
        return $this->findAll();
    }

  
}
