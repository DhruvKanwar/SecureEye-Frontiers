<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SendEmail extends Model
{
    use HasFactory;
    protected $table = 'send_email';
    protected $fillable = [
        'name', 'status',  'phone_no', 'email', 'location_id', 'created_at', 'updated_at'
    ];
}
