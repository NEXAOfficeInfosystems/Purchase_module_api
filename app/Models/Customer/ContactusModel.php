<?php namespace App\Models\Customer;

use CodeIgniter\Model;
use Ramsey\Uuid\Uuid;

class ContactusModel extends Model
{
    protected $table      = 'contact_us_messages';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true; 
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields = [
        "id",
        "first_name",
        "last_name",
        "email",
        "phone",
        "support_for",
        "message",
        "is_seen",
        "created_at",
        "updated_at",
        "deleted_at"
    ];

public function getProducts()
{
    return $this->findAll();
}
}
