<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SegmentDetail extends Model
{
    use HasFactory;
    protected $table = 'segment_details';
    protected $fillable = [
        'name', 'status',  'created_at', 'updated_at'
    ];
}
