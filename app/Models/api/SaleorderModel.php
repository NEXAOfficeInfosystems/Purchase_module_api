<?php namespace App\Models\api;

use CodeIgniter\Model;
use Ramsey\Uuid\Uuid;

class SaleorderModel extends Model
{

    
        protected $table      = 'Salesorders';
        protected $primaryKey = 'Id';
        protected $useAutoIncrement = true; 
        protected $allowedFields = [
            'Id',
            'OrderNumber',
            'Note',
            'order_id',
            'SaleReturnNote',
            'TermAndCondition',
            'IsSalesOrderRequest',
            'SOCreatedDate',
            'Status',
            'is_bulkorder',
            'DeliveryDate',
            'DeliveryStatus',
            'CustomerId',
            'TotalAmount',
            'TotalTax',
            'TotalDiscount',
            'TotalPaidAmount',
            'PaymentStatus',
            'CreatedDate',
            'CreatedBy',
            'ModifiedDate',
            'ModifiedBy',
            'DeletedDate',
            'DeletedBy',
            'IsDeleted'
    ];
    
    public function getSaleData()
    {
        return $this->findAll();
    }
    


}
