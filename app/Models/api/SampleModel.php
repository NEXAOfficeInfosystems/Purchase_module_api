<?php namespace App\Models\api;

use CodeIgniter\Model;
use Ramsey\Uuid\Uuid;

class SampleModel extends Model
{
    protected $table      = 'SampleTest';
    protected $primaryKey = 'ID';
    protected $useAutoIncrement = false; 
    protected $allowedFields = ['Summary', 'Description', 'IsActive','IsDeleted','ModifiedOn'];

    public function insertSampleData()
    {
        $guid = Uuid::uuid4()->toString();

        $data = [
            'ID' => $guid,
            'Summary' => 'Sample Summary',
            'Description' => 'Sample Description',
            'IsActive' => 1,
            'IsDeleted' => 0,
            'ModifiedOn' => date('Y-m-d H:i:s'),
        ];

        return $this->insert($data);
    }
}
