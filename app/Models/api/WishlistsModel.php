<?php namespace App\Models\api;

use CodeIgniter\Model;

class WishlistsModel extends Model
{
    protected $table      = 'wishlists';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true; 
   
    protected $allowedFields = [
        "id",
        "product_id",
        "user_id",
        "product_variation_id",
        "created_at",
        "updated_at",
        "deleted_at"
];

public function getProducts()
{
    return $this->findAll();
}
}
