<?php namespace App\Models\api;

use CodeIgniter\Model;
use Ramsey\Uuid\Uuid;

class CartsModel extends Model
{
    protected $table      = 'carts';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true; 
   
    protected $allowedFields = [
        "id",
        "user_id",
        "guest_user_id",
        "location_id",
        "product_variation_id",
        "qty",
        "created_at",
        "updated_at",
        "deleted_at",
        "product_id"
    ];

public function getProducts()
{
    return $this->findAll();
}
}
