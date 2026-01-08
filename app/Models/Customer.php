<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $table = 'customers';
    protected $primaryKey = 'ID';
    public $timestamps = false;

    protected $fillable = [
        'Name',
        'Phone',
        'Phone2',
        'Email1',
        'Email2',
        'Address',
        'City',
        'State',
        'ZIP_Code',
        'Drivers_License',
        'DL_State',
        'DOB',
        'Source',
        'Office',
        'Marital',
        'Gender',
        'CID',
        'Added',
        'Agent_of_Record',
        'Alert',
        'Picture',
        'Agency'
    ];

    public function notes()
    {
        return $this->hasMany(\App\Models\CustomerNote::class, 'customer_id', 'ID');
    }
}
