<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FileCustomer extends Model
{
    protected $table = 'files_customers';

    protected $fillable = [
        'customer_id',
        'file_name',
        'file_path',
        'file_size',
        'file_type',
        'uploaded_by_id',
        'uploaded_by_type'
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
