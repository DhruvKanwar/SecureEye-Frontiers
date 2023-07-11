<?php

namespace App\Http\Controllers;

use App\Exports\BiometricExport;
use Illuminate\Http\Request;
use App\Models\SegmentDetail;
use App\Models\SiteInfo;
use App\Models\DailyReport;
use Illuminate\Support\Facades\Auth;
use App\Exports\CCTVExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;




class ReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $segment_data = SegmentDetail::get();
        $site_info = SiteInfo::get();
        foreach ($site_info as $value) {
            $ids[] = $value->segment_ids;
        }

        // for ($i = 0; $i < sizeof($ids); $i++) {
        //     echo "<pre>";
        //     print_r($ids[$i]);
        // }
        // exit;
        // echo "<pre>";
        // print_r($site_info);
        // // print_r($ids);
        // exit;
        return view('pages.daily-report', ['site_info' => $site_info, 'segment_ids' => $ids, 'segment_data' => $segment_data]);
    }

    public function get_locaion_segment(Request $request)
    {
        $data = $request->all();
        $get_segments = SiteInfo::where('id', $data['location_id'])->get();
        $segment_ids = $get_segments[0]->segment_ids;
        $explode_segments = explode(',', $segment_ids);
        $segment_details = array();

        for ($i = 0; $i < sizeof($explode_segments); $i++) {
            $id = $explode_segments[$i];
            $get_ter_data = SegmentDetail::where('id', $id)->first();
            $segment_details[$i]['segment_name'] = $get_ter_data->name;
            $segment_details[$i]['seg_ids']  = $get_ter_data->id;
        }
        return $segment_details;
    }

    public function submit_daily_report(Request $request)
    {
        $data = $request->all();
        $details = Auth::user();

        $store_data = array();
        $fetch_data = DailyReport::where('location_id', $data['location_id'])->where('report_date', date('d-m-Y'))->first();
        // return $fetch_data;
        if (empty($fetch_data)) {
            $store_data['segment_id'] = implode(',', $data['module']);
            $store_data['cctv_working'] = $data['cctv_working'];
            $store_data['location_id'] = $data['location_id'];
            $store_data['saved_by_name'] = $details->name;
            $store_data['saved_by_id'] = $details->id;
            $store_data['updated_by_name'] = $details->name;
            $store_data['updated_by_id'] = $details->id;
            $store_data['report_date'] = date('d-m-Y');
            $store = DailyReport::create($store_data);
            $location_id = $data['location_id'];
            if ($store) {

                $currentDate = date('j');

                $cctv_headings = array_merge(['S No', 'Location', 'Cameras Installed','Count of Cameras not Working',], range(1, $currentDate));
                $today_date = date('d-m-Y');

                $cctv_export = new CCTVExport($cctv_headings, $location_id);
                $filePath = 'exports/cctv_data_' . $today_date . '.xlsx';
                $cctv = Excel::store($cctv_export, $filePath, 'public');

                $biometric_headings = array_merge(['S No', 'Location', 'Attendance Mode', 'Employee Count',], range(1, $currentDate));

                $biometric_export = new BiometricExport($biometric_headings, $location_id);
                $filePath = 'exports/biometric_data_' . $today_date . '.xlsx';
                $cctv = Excel::store($biometric_export, $filePath, 'public');
            
                return $cctv;

                return response()->json(['message' => 'Excel file stored successfully']);


                // // Generate the collection
                // $collection = $export->collection();
                // return $collection;

            }

            return 1;
        } else {
            return 0;
        }
    }
}
