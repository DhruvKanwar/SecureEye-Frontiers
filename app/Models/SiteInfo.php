<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SiteInfo extends Model
{
    use HasFactory;
    protected $table = 'site_info';
    protected $fillable = [
        'location', 'segment_ids',  'ext', 'cctv_count', 'status', 'created_at', 'updated_at', 'employee_count', 'attendance_mode'
    ];

    public function Segments()
    {
        return $this->hasOne('App\Models\SegmentDetail', 'id', 'segment_ids');
    }
}
