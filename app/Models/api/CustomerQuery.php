<?php namespace App\Models\api;

use CodeIgniter\Model;
use Ramsey\Uuid\Uuid;

class CustomerQuery extends Model
{
    protected $table      = 'customer_query';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = false; 
   
    protected $allowedFields = [
        'id',
        'name',
        'email',
        'image_url',
        'created_at',
        'message',
        'order_id',   
];

public function getQuery()
{
    return $this->findAll();
}


}
