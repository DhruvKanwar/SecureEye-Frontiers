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
use App\Imports\BulkImport;
use Response;



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

    public function segment_report()
    {
        $segment_data = SegmentDetail::get();
        // $site_info = SiteInfo::get();
        foreach ($segment_data as $value) {
            $ids[] = $value->location_ids;
        }

        return view('pages.segments-daily-report', ['segment_ids' => $ids, 'segment_data' => $segment_data]);
    }


    public function get_locaions(Request $request)
    {
        $data = $request->all();
        $id = $data['segment_id'];

        $get_segment_data = SegmentDetail::where('id', $id)->first();
        $explode_locations = explode(',', $get_segment_data->location_ids);
        $location_details = array();

        for ($i = 0; $i < sizeof($explode_locations); $i++) {
            $get_location_data = SiteInfo::where('id', $explode_locations[$i])->first();
            $location_details[$i]['name'] = $get_location_data->location;
            $location_details[$i]['location_id']  = $get_location_data->id;
        }


        return $location_details;
    }

    // public function get_locaion(Request $request)
    // {
    //     $data = $request->all();
    //     $location_ids = $data['segment_id'];
    //     $explode_locations = explode(',', $location_ids);
    //     $location_details = array();

    //     for ($i = 0; $i < sizeof($explode_locations); $i++) {
    //         $id = $explode_locations[$i];
    //         $get_ter_data = SiteInfo::where('id', $id)->first();
    //         $location_details[$i]['name'] = $get_ter_data->location;
    //         $location_details[$i]['location_id']  = $get_ter_data->id;
    //     }


    //     return $location_details;
    // }

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
        if (!empty($fetch_data)) {

            $currentDate = date('j');

            $cctv_headings = array_merge(['S No', 'Location', 'Cameras Installed', 'Count of Cameras not Working',], range(1, $currentDate));

            $cctv_export = new ITCCTVExport($cctv_headings);
            // $collection = $cctv_export->collection();
            // return $collection;

            $cctv_filePath = 'IT_exports/it_cctv_report.xlsx';
            // exit;
            $cctv = Excel::store($cctv_export, $cctv_filePath, 'public');
            // exit;

            $biometric_headings = array_merge(['S No', 'Location', 'Attendance Mode', 'Employee Count',], range(1, $currentDate));

            $biometric_export = new ITBIOMETRICExport($biometric_headings);
            //     $collection = $biometric_export->collection();
            // return $collection;
            $biometric_filePath = 'IT_exports/it_biometric_report.xlsx';
            $biometric = Excel::store($biometric_export, $biometric_filePath, 'public');

            $iPphone_headings = array_merge(['S No', 'Location', 'Ext'], range(1, $currentDate));

            $ipPhone_export = new ITIPphoneExport($iPphone_headings);
            $ipPhone_filePath = 'IT_exports/it_ipPhone_report.xlsx';
            $ipPhone = Excel::store($ipPhone_export, $ipPhone_filePath, 'public');

            if ($cctv && $biometric && $ipPhone) {
                $data["email"] = 'dhroov.kanwar@eternitysolutions.net';
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

                // print_r("Mail Stopped in Local");
                // exit;
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
                        public_path('storage') => storage_path('app/public/' . $cctv_filePath),
                    ];
                    $biometric_file = [
                        public_path('storage') => storage_path('app/public/' . $biometric_filePath),
                    ];
                    $ipPhone_files = [
                        public_path('storage') => storage_path('app/public/' . $ipPhone_filePath),
                    ];

                    print_r("Mail stopped in local");
                    exit;
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

            return 1;
        } else {
            return 0;
        }
    }

    public function submit_segment_report(Request $request)
    {
        $data = $request->all();
        $selected_locations = array();
        $selected_locations = $data['selected_locatiion_id'];
        // print_r(sizeof($selected_locations));
        // exit;
        $details = Auth::user();
        $locations_done = array();
        $store_data = array();
        $update_flag=array();
        $flag_update=array();
        $store_new_segment_id = array();
        $store_new_segment_id = explode(',', $data['segment_id']);
        $check_existence_flag = false;



        if (!empty($data['cctv_working'])) {
            $store_data['cctv_working'] = $data['cctv_working'];
        }
        for ($i = 0; $i < sizeof($selected_locations); $i++) {
            $fetch_data = DailyReport::where('location_id', $selected_locations[$i])->where('report_date', date('d-m-Y'))->first();
            $site_info_data = SiteInfo::where('id', $selected_locations[$i])->first();

            // return $fetch_data;
            if (empty($fetch_data)) {
                // $store_data['segment_id'] = implode(',', $data['module']);
                if ($data['segment_id'] == 1) {
                    $store_data['cctv_flag'] = 1;
                }
                if ($data['segment_id'] == 2) {
                    $store_data['biometric_flag'] = 1;
                }
                if ($data['segment_id'] == 3) {
                    $store_data['ipPhone1_flag'] = 1;
                }
                if ($data['segment_id'] == 4) {
                    $store_data['ipPhone2_flag'] = 1;
                }
                if ($data['segment_id'] == 5) {
                    $store_data['ipPhone3_flag'] = 1;
                }
                if ($data['segment_id'] == 6) {
                    $store_data['ipPhone4_flag'] = 1;
                }

                $store_data['segment_id'] = $data['segment_id'];
                $store_data['location_id'] = $selected_locations[$i];
                $store_data['saved_by_name'] = $details->name;
                $store_data['saved_by_id'] = $details->id;
                $store_data['updated_by_name'] = $details->name;
                $store_data['updated_by_id'] = $details->id;
                $store_data['report_date'] = date('d-m-Y');
                $store = DailyReport::create($store_data);
            } else {
                $actual_segment_ids = $site_info_data->segment_ids;
                $daily_report_segment_ids = $fetch_data->segment_id;
                $explode_actual_segments = explode(',', $actual_segment_ids);
                $explode_daily_report_segments = explode(',', $daily_report_segment_ids);


                // Previous Code Starts
                // $diff1 = array_diff($explode_actual_segments, $explode_daily_report_segments);
                // $diff2 = array_diff($explode_daily_report_segments, $explode_actual_segments);

                // if (empty($diff1) && empty($diff2)) {
                //     $locations_done['name'][$i] = $site_info_data->location;
                //     $check_existence_flag = true;
                //     DailyReport::where('id', $fetch_data->id)->update([
                //         'segment_flag' => 1,
                //         'saved_by_name' => $details->name, 'saved_by_id' => $details->id
                //     ]);
                // }
                // Previous Code Ends

                if (
                    $fetch_data->cctv_flag && $fetch_data->biometric_flag && $fetch_data->ipPhone1_flag && $fetch_data->ipPhone2_flag
                    && $fetch_data->ipPhone3_flag && $fetch_data->ipPhone4_flag
                ) {
                    $locations_done['name'][$i] = $site_info_data->location;
                    $check_existence_flag = true;
                    DailyReport::where('id', $fetch_data->id)->update([
                        'segment_flag' => 1,
                        'saved_by_name' => $details->name, 'saved_by_id' => $details->id
                    ]);
                } else {
                    $mergedData = array_merge($explode_daily_report_segments, array_diff($store_new_segment_id, $explode_daily_report_segments));

                    $filteredArray = array_filter($mergedData);
                    $resultString = implode(',', $filteredArray);
                  

                  
                    if (!empty($data['cctv_working'])) {
                        DailyReport::where('id', $fetch_data->id)->update([
                            'segment_id' => $resultString,
                            'saved_by_name' => $details->name, 'saved_by_id' => $details->id, 'cctv_working' => $data['cctv_working']
                        ]);
                    } else {
                        DailyReport::where('id', $fetch_data->id)->update([
                            'segment_id' => $resultString,
                            'saved_by_name' => $details->name, 'saved_by_id' => $details->id
                        ]);
                    }

                    if ($data['segment_id'] == 1) {
                        $update_flag['cctv_flag'] = 1;
                    }
                    if ($data['segment_id'] == 2) {
                        $update_flag['biometric_flag'] = 1;
                    }
                    if ($data['segment_id'] == 3) {
                        $update_flag['ipPhone1_flag'] = 1;
                    }
                    if ($data['segment_id'] == 4) {
                        $update_flag['ipPhone2_flag'] = 1;
                    }
                    if ($data['segment_id'] == 5) {
                        $update_flag['ipPhone3_flag'] = 1;
                    }
                    if ($data['segment_id'] == 6) {
                        $update_flag['ipPhone4_flag'] = 1;
                    }

                    DailyReport::where('id', $fetch_data->id)->update($update_flag);
                    $update_flag=array();
                }
            }
        }


        $get_segments_locations = SegmentDetail::where('id', $data['segment_id'])->first();
        $location_id = explode(',', $get_segments_locations->location_ids);

        $records = SegmentDetail::select('id')->get();

        $total_segment_ids = $records->pluck('id')->toArray();


        $explode_location_id = array_map('intval', $location_id);
        $store_data = array();
        for ($i = 0; $i < sizeof($explode_location_id); $i++) {
            $fetch_data = DailyReport::where('location_id', $explode_location_id[$i])->where('report_date', date('d-m-Y'))->first();
           
            if (empty($fetch_data)) {
                // $store_data['segment_id'] = implode(',', $data['module']);
                if ($data['segment_id'] == 1) {
                    $store_data['cctv_flag'] = 1;
                }
                if ($data['segment_id'] == 2) {
                    $store_data['biometric_flag'] = 1;
                }
                if ($data['segment_id'] == 3) {
                    $store_data['ipPhone1_flag'] = 1;
                }
                if ($data['segment_id'] == 4) {
                    $store_data['ipPhone2_flag'] = 1;
                }
                if ($data['segment_id'] == 5) {
                    $store_data['ipPhone3_flag'] = 1;
                }
                if ($data['segment_id'] == 6) {
                    $store_data['ipPhone4_flag'] = 1;
                }
                $store_data['location_id'] = $explode_location_id[$i];
                $store_data['saved_by_name'] = $details->name;
                $store_data['saved_by_id'] = $details->id;
                $store_data['updated_by_name'] = $details->name;
                $store_data['updated_by_id'] = $details->id;
                $store_data['report_date'] = date('d-m-Y');
                $store = DailyReport::create($store_data);
            }

        

            $site_full_info = SiteInfo::where('id', $explode_location_id[$i])->first();
            $seg_ids = explode(',', $site_full_info->segment_ids);
            $explode_site_seg_ids = array_map('intval', $seg_ids);
            $missing_ids = array_values(array_diff($total_segment_ids, $explode_site_seg_ids));

            for ($j = 0; $j < sizeof($missing_ids); $j++) {
                if ($missing_ids[$j] == 1) {
                    $flag_update['cctv_flag'] = 1;
                }
                if ($missing_ids[$j] == 2) {
                    $flag_update['biometric_flag'] = 1;
                }
                if ($missing_ids[$j] == 3) {
                    $flag_update['ipPhone1_flag'] = 1;
                }
                if ($missing_ids[$j] == 4) {
                    $flag_update['ipPhone2_flag'] = 1;
                }
                if ($missing_ids[$j] == 5) {
                    $flag_update['ipPhone3_flag'] = 1;
                }
                if ($missing_ids[$j] == 6) {
                    $flag_update['ipPhone4_flag'] = 1;
                }
                $fetch_data = DailyReport::where('location_id', $explode_location_id[$i])->where('report_date', date('d-m-Y'))->first();

                DailyReport::where('id', $fetch_data->id)->update($flag_update);
                $flag_update=array();
            }
        }
// start

    $not_checked_locations=$data['unchecked_location_ids'];

    if(!empty($not_checked_locations))
    {

    $explode_not_checked_locations=explode(',',$not_checked_locations);
        $update_flag = array();
    for($i=0;$i<sizeof($explode_not_checked_locations);$i++)
    {

        $fetch_data=DailyReport::where('report_date',date('d-m-Y'))->where('location_id', $explode_not_checked_locations[$i])->first();

        if ($data['segment_id'] == 1) {
            $update_flag['cctv_flag'] = 1;
        }
        if ($data['segment_id'] == 2) {
            $update_flag['biometric_flag'] = 1;
        }
        if ($data['segment_id'] == 3) {
            $update_flag['ipPhone1_flag'] = 1;
        }
        if ($data['segment_id'] == 4) {
            $update_flag['ipPhone2_flag'] = 1;
        }
        if ($data['segment_id'] == 5) {
            $update_flag['ipPhone3_flag'] = 1;
        }
        if ($data['segment_id'] == 6) {
            $update_flag['ipPhone4_flag'] = 1;
        }

        DailyReport::where('id', $fetch_data->id)->update($update_flag);
        $update_flag = array();
    }
}

        // end

        // start

        // if in that location segment is not present


        // end


        // start
  

        $check_all_flags = DailyReport::where('report_date', date('d-m-Y'))->where('cctv_flag', 1)->where('biometric_flag', 1)->where('ipPhone1_flag', 1)->where('ipPhone2_flag', 1)->where('ipPhone3_flag', 1)->where('ipPhone4_flag', 1)->get();
        for ($k = 0; $k < sizeof($check_all_flags); $k++) {
            $update_segment_flag=DailyReport::where('id',$check_all_flags[$k]->id)->update(['segment_flag'=>1]);
            }
        // end


        $last_check = DailyReport::where('report_date', date('d-m-Y'))->where('cctv_flag', 1)->where('biometric_flag', 1)->where('ipPhone1_flag', 1)->where('ipPhone2_flag', 1)->where('ipPhone3_flag', 1)->where('ipPhone4_flag', 1)->get();
        $site_info = SiteInfo::get();
        if(sizeof($site_info) == sizeof($last_check))
        {
          return "101";
        }
      

        if ($check_existence_flag) {
            return $locations_done;
        } else {
            return 1;
        }
    }


    public function import_data()
    {
        return view('pages.import-data');
    }

    public function send_regional_email()
    {
        $fetch_data = DailyReport::with('SiteInfos')->where('report_date', date('d-m-Y'))->where('segment_flag', 1)->where('mail_flag', 0)->get();

        $store_location_info = array();
        for ($i = 0; $i < sizeof($fetch_data); $i++) {
            $store_location_info['id'][$i] = $fetch_data[$i]->SiteInfos->id;
            $store_location_info['name'][$i] = $fetch_data[$i]->SiteInfos->location;
        }
        return view('pages.regional-email', ['data' => $store_location_info]);
    }
    public function import_master(Request $request)
    {
        // return $request->all();

        if ($_POST['import_type'] == 1) {
            $type = $_POST['import_type'];
            //echo'<pre>'; print_r($_FILES); die;
            $data = Excel::import(new BulkImport, request()->file('file'));
            $response['success'] = true;
            $response['import_type'] = $type;
            $response['messages'] = 'Succesfully imported';
            return Response::json($response);
        }
        if ($_POST['import_type'] == 2) {
            $type = $_POST['import_type'];
            //echo'<pre>'; print_r($_FILES); die;
            $data = Excel::import(new BulkImport, request()->file('file'));
            $response['success'] = true;
            $response['import_type'] = $type;
            $response['messages'] = 'Succesfully imported';
            return Response::json($response);
        }
    }

    public function mail_locationwise_report(Request $request)
    {
        $data = $request->all();
        $details = Auth::user();
        $location_id = $data['location_id'];
        $get_location_info = SiteInfo::where('id', $location_id)->first();
        $location_name = $get_location_info->location;
        $currentDate = date('j');

        $cctv_headings = array_merge(['S No', 'Location', 'Cameras Installed', 'Count of Cameras not Working',], range(1, $currentDate));
        $cctv_export = new CCTVExport($cctv_headings, $location_id);
        $cctv_filePath = 'exports/cctv_data_' . $location_name . '.xlsx';
        $cctv = Excel::store($cctv_export, $cctv_filePath, 'public');

        $biometric_headings = array_merge(['S No', 'Location', 'Attendance Mode', 'Employee Count',], range(1, $currentDate));

        $biometric_export = new BiometricExport($biometric_headings, $location_id);
        $biometric_filePath = 'exports/biometric_data_' . $location_name . '.xlsx';
        $biometric = Excel::store($biometric_export, $biometric_filePath, 'public');

        $iPphone_headings = array_merge(['S No', 'Location', 'Ext'], range(1, $currentDate));

        $ipPhone_export = new IPphoneExport($iPphone_headings, $location_id);
        $ipPhone_filePath = 'exports/ipPhone_data_' . $location_name . '.xlsx';
        $ipPhone = Excel::store($ipPhone_export, $ipPhone_filePath, 'public');

        // print_r("Mail Stopped in Local");
        // exit;

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

            try {
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
                $fetch_data = DailyReport::where('location_id', $location_id)->where('report_date', date('d-m-Y'))->first();

                DailyReport::where('id', $fetch_data->id)->update([
                    'mail_flag' => 1,
                    'saved_by_name' => $details->name, 'saved_by_id' => $details->id
                ]);
            } catch (\Exception $e) {
                // return $e;
            }
            return 1;
        }



        return response()->json(['message' => 'Excel file stored successfully']);


        // // Generate the collection
        // $collection = $export->collection();
        // return $collection;


    }
}
