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

class BiometricExport implements FromCollection, WithHeadings, WithEvents
{
    /**
    * @return \Illuminate\Support\Collection
    */
    protected $headings;
    protected  $location_id;



    public function __construct($headings, $location_id)
    {
        $this->headings = $headings;
        $this->location_id = $location_id;
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
        $data = DailyReport::with('SiteInfos')->where('location_id', $this->location_id)->whereMonth('created_at', $currentMonth)->whereYear('created_at', $currentYear)->get();


        $new_data = [];

        // $arr_instrulist_excel[] = 1;
        // $arr_instrulist_excel[] = $data[0]->SiteInfos->location;
        // $arr_instrulist_excel[] = $data[0]->SiteInfos->cctv_count;

        // dd($data[$i]->CourierCompany->courier_name);

        $arr_instrulist_excel[] = array(
            's.no.' => 1,
            'location'  => $data[0]->SiteInfos->location,
            'cctv_count'   => $data[0]->SiteInfos->cctv_count,

        );
        for ($day = 1; $day <= $days + 1; $day++) {
            $get_day_date = "";
            if (strlen($day) == 1) {
                $day_new = '0' . $day;
            } else {
                $day_new = $day;
            }

            $date = $day_new . '-' . $currentMonth . '-' . $currentYear;
            $get_day_date = DailyReport::with('SiteInfos')->where('location_id', $this->location_id)->where('report_date', $date)->first();
            // $get_day_date = DB::table('daily_report')->with('SiteInfos')->where('location_id', $this->location_id)->where('report_date', $date)->get();


            if (!empty($get_day_date->segment_id)) {
                // foreach ($get_day_date as $report) {
                $segment_ids = $get_day_date->segment_id;

                $explode_segments = explode(',', $segment_ids);
                if (in_array(1, $explode_segments)) {
                    $new_data[] = array(
                        'working' . '' . $day => 1
                    );
                    // $new_data[] = "Working";
                } else {
                    $new_data[] = array(
                        'not-working' . '' . $day => 0
                    );
                }
                // }
            } else {
                $new_data[] = array(
                    'not-found' . '' . $day => 2
                );
            }
        }
        
        $new_array = [];
        $new_array = array_merge($arr_instrulist_excel, $new_data);
        $combinedArray = array();

        $combinedArray = array_merge(...$new_array);
        $result_array = array();
        $result_array[0] = $combinedArray;
        // print_r($arr_instrulist_excel);
        // print_r($result_array);
        // exit;


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
