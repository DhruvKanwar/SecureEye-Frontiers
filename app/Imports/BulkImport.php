<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use App\Models\SiteInfo;
use App\Models\SegmentDetail;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

date_default_timezone_set('Asia/Kolkata');

class BulkImport implements ToModel, WithHeadingRow
{
    /**
    * @param Collection $collection
    */
    public function model(array $row)
    {

        if ($_POST['import_type'] == 1) {
            return new SiteInfo([
                'location' => $row['site_name'],
                'segment_ids'  => $row['segment_id'],
                'ext' => $row['extn'],
                'employee_count'    => $row['employee_count'],
                'attendance_mode' => $row['atten_mode'],
                'cctv_count' =>    $row['cctv_count'],
                'status' => 1,

            ]);
        }
        if ($_POST['import_type'] == 2) {
            return new SegmentDetail([
                'name' => $row['segment_name'],
                'location_ids'  => $row['locations_id'],
                'status' => 1,
            ]);
        }
        
    }
}
