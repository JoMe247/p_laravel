<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $table = 'customers';
    protected $primaryKey = 'ID';
    public $timestamps = false;

    protected $fillable = [
        'Name','Phone','Email1','Email2','Address','City','State','ZIP_Code',
        'Drivers_License','DL_State','DOB','Source','Office','Marital','Gender',
        'CID','Added','Agent_of_Record','Alert','Picture','Agency'
    ];
}
