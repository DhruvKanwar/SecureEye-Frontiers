<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use App\Models\DailyReport;
use App\Models\SiteInfo;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Sheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class ITCCTVExport implements FromCollection, WithHeadings, WithEvents
{
    protected $headings;
    protected  $location_id;



    public function __construct($headings)
    {
        $this->headings = $headings;
    }

    public function collection()
    {

        $currentDate = date('Y-m-d'); // Get the current date in the format "YYYY-MM-DD"
        $firstDayOfMonth = date('Y-m-01'); // Get the first day of the current month
        $to = \Carbon\Carbon::parse($firstDayOfMonth);
        $from = \Carbon\Carbon::parse($currentDate);

        $days = $to->diffInDays($from);

        $currentMonth = date('m');
        $currentYear = date('Y');
        $data = array();
        $data = DailyReport::with('Itinfo')->whereMonth('created_at', $currentMonth)->whereYear('created_at', $currentYear)->get();

        $result_array = array();
        $location_id = array();
        $new_array = [];
        for ($i = 0; $i < sizeof($data); $i++) {

            $location_id[$i] = $data[$i]->location_id;
        }
        $uniqueLocationData = array_unique($location_id, SORT_REGULAR);
        $unique_location_ids = array_values($uniqueLocationData); // Resetting array keys
        $data = [];

        // print_r(sizeof($unique_location_ids));
        // exit;
        for ($i = 0; $i < sizeof($unique_location_ids); $i++) {
            $data = DailyReport::with('Itinfo')->where('location_id', $unique_location_ids[$i])->whereMonth('created_at', $currentMonth)->whereYear('created_at', $currentYear)->get();
//    print_r($data);
//         exit;
            $day=1;
            for ($day = 1; $day <= $days + 1; $day++) {
                $get_day_date = "";
                if (strlen($day) == 1) {
                    $day_new = '0' . $day;
                } else {
                    $day_new = $day;
                }

                $date = $day_new . '-' . $currentMonth . '-' . $currentYear;
                $get_day_date = DailyReport::with('SiteInfos')->where('location_id', $unique_location_ids[$i])->where('report_date', $date)->first();
                // $get_day_date = DB::table('daily_report')->with('SiteInfos')->where('location_id', $this->location_id)->where('report_date', $date)->get();


                if (!empty($get_day_date->segment_id)) {
                    // foreach ($get_day_date as $report) {
                    $segment_ids = $get_day_date->segment_id;

                    $explode_segments = explode(',', $segment_ids);
                    if (in_array(1, $explode_segments)) {
                        $new_data[$day] = array(
                            'working' . '' . $day => 1
                        );
                        // $new_data[] = "Working";
                    } else {
                        $new_data[$day] = array(
                            'not-working' . '' . $day => 0
                        );
                    }
                    // }
                } else {
                    $new_data[$day] = array(
                        'not-found' . '' . $day => 2
                    );
                }
            }

            $arr_instrulist_excel[] = array(
                's.no.' => $i + 1,
                'location'  => $data[$i]->SiteInfos->location,
                'cctv_count'   => $data[$i]->SiteInfos->cctv_count,
                'cctv_not_working'   => $data[$i]->cctv_working,
            );

            $new_array = array_merge($arr_instrulist_excel, $new_data);
            $combinedArray = array();
            $combinedArray = array_merge(...$new_array);
            $result_array[$i] = $combinedArray;
        }
        return collect($result_array);
    
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $highestRow = $event->getSheet()->getHighestRow();
                $highestColumn = $event->getSheet()->getHighestColumn();

                $columnStart = 'D'; // Start from column D

                $columnEndIndex = Coordinate::columnIndexFromString($highestColumn);



                for ($row = 2; $row <= $highestRow; $row++) {
                    for ($columnIndex = Coordinate::columnIndexFromString($columnStart); $columnIndex <= $columnEndIndex; $columnIndex++) {
                        $cellValue = $event->getSheet()->getCellByColumnAndRow($columnIndex, $row)->getValue();

                        if ($cellValue == 1) {
                            $event->getSheet()->getStyleByColumnAndRow($columnIndex, $row)
                                ->getFill()
                                ->setFillType(Fill::FILL_SOLID)
                                ->getStartColor()
                                ->setRGB('00FF00'); // Green color
                        } else if ($cellValue == 0) {
                            $event->getSheet()->getStyleByColumnAndRow($columnIndex, $row)
                                ->getFill()
                                ->setFillType(Fill::FILL_SOLID)
                                ->getStartColor()
                                ->setRGB('FF0000'); // Red color
                        } else if ($cellValue == 2) {
                            $event->getSheet()->getStyleByColumnAndRow($columnIndex, $row)
                                ->getFill()
                                ->setFillType(Fill::FILL_SOLID)
                                ->getStartColor()
                                ->setRGB('FFFFFF'); // White color
                        }
                    }
                }
            },
        ];
    }


    public function headings(): array
    {
        return $this->headings;
    }
}
