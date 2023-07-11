<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyReport extends Model
{
    use HasFactory;
    protected $table = 'daily_report';
    protected $fillable = [
        'location_id', 'segment_id', 'report_date', 'cctv_working', 'created_at', 'updated_at', 'saved_by_id', 'saved_by_name',
        'updated_by_id', 'updated_by_name'
    ];
    public function SiteInfos()
    {
        return $this->belongsTo('App\Models\SiteInfo', 'location_id');
    }
}
