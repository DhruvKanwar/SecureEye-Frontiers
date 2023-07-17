<?php

namespace App\Http\Controllers;

use App\Exports\BiometricExport;
use Illuminate\Http\Request;
use App\Models\SegmentDetail;
use App\Models\SiteInfo;
use App\Models\SendEmail;
use App\Models\DailyReport;
use Illuminate\Support\Facades\Auth;
use App\Exports\CCTVExport;
use App\Exports\IPphoneExport;
use App\Exports\ITBIOMETRICExport;
use App\Exports\ITCCTVExport;
use App\Exports\ITIPphoneExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;





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


    public function send_email_to_it(Request $request)
    {
        $details = Auth::user();
        $fetch_data = DailyReport::where('report_date', date('d-m-Y'))->first();
        if(!empty($fetch_data))
        {

                $currentDate = date('j');

                $cctv_headings = array_merge(['S No', 'Location', 'Cameras Installed', 'Count of Cameras not Working',], range(1, $currentDate));
                $today_date = date('d-m-Y');

                $cctv_export = new ITCCTVExport($cctv_headings);
            // $collection = $cctv_export->collection();
            // return $collection;

                $cctv_filePath = 'IT_exports/cctv_data_' . $today_date . '.xlsx';
                $cctv = Excel::store($cctv_export, $cctv_filePath, 'public');
               
                return $cctv;

                $biometric_headings = array_merge(['S No', 'Location', 'Attendance Mode', 'Employee Count',], range(1, $currentDate));

                $biometric_export = new ITBIOMETRICExport($biometric_headings);
                $biometric_filePath = 'IT_exports/biometric_data_' . $today_date . '.xlsx';
                $biometric = Excel::store($biometric_export, $biometric_filePath, 'public');

                $iPphone_headings = array_merge(['S No', 'Location', 'Ext'], range(1, $currentDate));

                $ipPhone_export = new ITIPphoneExport($iPphone_headings);
                $ipPhone_filePath = 'IT_exports/ipPhone_data_' . $today_date . '.xlsx';
                $ipPhone = Excel::store($ipPhone_export, $ipPhone_filePath, 'public');

                if ($cctv && $biometric && $ipPhone) {
                    $get_email = SendEmail::where('location_id', $location_id)->first();
                    $data["email"] = $get_email->email;
                    $data["title"] = "Daily Report " . date('d-m-Y');
                    $data["body"] = "Please Find the file attachment for ter List";

                    $cctv_file = [
                        public_path('storage') => storage_path('app/public/' . $cctv_filePath),
                    ];
                    $biometric_file = [
                        public_path('storage') => storage_path('app/public/' . $biometric_filePath),
                    ];
                    $ipPhone_files = [
                        public_path('storage') => storage_path('app/public/' . $ipPhone_filePath),
                    ];

                    Mail::send('emails.sendDailyReport', $data, function ($message) use ($data, $cctv_file, $biometric_file, $ipPhone_files) {
                        $message->to($data["email"], $data["email"])
                            ->subject($data["title"]);

                        foreach ($cctv_file as $file) {
                            $message->attach($file);
                        }
                        foreach ($biometric_file as $file) {
                            $message->attach($file);
                        }
                        foreach ($ipPhone_files as $file) {
                            $message->attach($file);
                        }
                    });
                }



                return response()->json(['message' => 'Excel file stored successfully']);


                // // Generate the collection
                // $collection = $export->collection();
                // return $collection;

            

        }
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

                $cctv_headings = array_merge(['S No', 'Location', 'Cameras Installed', 'Count of Cameras not Working',], range(1, $currentDate));
                $today_date = date('d-m-Y');


                $cctv_export = new CCTVExport($cctv_headings, $location_id);
                $cctv_filePath = 'exports/cctv_data_' . $today_date . '.xlsx';
                $cctv = Excel::store($cctv_export, $cctv_filePath, 'public');

                $biometric_headings = array_merge(['S No', 'Location', 'Attendance Mode', 'Employee Count',], range(1, $currentDate));

                $biometric_export = new BiometricExport($biometric_headings, $location_id);
                $biometric_filePath = 'exports/biometric_data_' . $today_date . '.xlsx';
                $biometric = Excel::store($biometric_export, $biometric_filePath, 'public');

                $iPphone_headings = array_merge(['S No', 'Location', 'Ext'], range(1, $currentDate));

                $ipPhone_export = new IPphoneExport($iPphone_headings, $location_id);
                $ipPhone_filePath = 'exports/ipPhone_data_' . $today_date . '.xlsx';
                $ipPhone = Excel::store($ipPhone_export, $ipPhone_filePath, 'public');

                if ($cctv && $biometric && $ipPhone) {
                    $get_email = SendEmail::where('location_id', $location_id)->first();
                    $data["email"] = $get_email->email;
                    $data["title"] = "Daily Report " . date('d-m-Y');
                    $data["body"] = "Please Find the file attachment for ter List";

                    $cctv_file = [
                        public_path('storage') => storage_path('app/public/'.$cctv_filePath),
                    ];
                    $biometric_file = [
                        public_path('storage') => storage_path('app/public/' . $biometric_filePath),
                    ];
                    $ipPhone_files = [
                        public_path('storage') => storage_path('app/public/' . $ipPhone_filePath),
                    ];

                    print_r("Mail stopped in local");
                    exit;
                    Mail::send('emails.sendDailyReport', $data, function ($message) use ($data, $cctv_file,$biometric_file,$ipPhone_files) {
                        $message->to($data["email"], $data["email"])
                            ->subject($data["title"]);

                        foreach ($cctv_file as $file) {
                            $message->attach($file);
                        }
                        foreach ($biometric_file as $file) {
                            $message->attach($file);
                        }
                        foreach ($ipPhone_files as $file) {
                            $message->attach($file);
                        }
                    });
                }



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
