<?php namespace App\Models\api;

use CodeIgniter\Model;
use Ramsey\Uuid\Uuid;


class Table2 extends Model
{
    protected $table      = 'table2';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true; 
    protected $allowedFields = [
        'id',
        'col_name',
        'percentage'
       
];

public function getProducts()
{
    return $this->findAll();
}



  



}
