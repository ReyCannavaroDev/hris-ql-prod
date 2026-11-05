<?php

namespace App\Models\CustomModels;

class presensi_maksi extends \App\Models\BasicModels\presensi_maksi
{    
    public function __construct()
    {
        parent::__construct();
    }
    
    public $fileColumns    = [ /*file_column*/ ];

    public $createAdditionalData = ["creator_id"=>"auth:id"];
    public $updateAdditionalData = ["last_editor_id"=>"auth:id"];

    public function custom_get_maksi(){
        $m_kary_id = auth()->user()->m_kary_id;
        $data = [];

        $currentDate = new \DateTime();
        $currentDate->modify('+1 day');
        $nextDay = $currentDate->format('Y-m-d');

        $check_exists = presensi_maksi_det::select('presensi_maksi_det.*')
            ->join('presensi_maksi','presensi_maksi.id','presensi_maksi_det.presensi_maksi_id')
            ->where('presensi_maksi_det.m_kary_id',$m_kary_id)
            ->where('presensi_maksi.tanggal', $nextDay)
            ->first();

        if($check_exists){
            $check_exists->sudah_pesan = true;
            return response(['data'=>$check_exists]);
        }
        $data = [];

        $data = $this->where('tanggal', $nextDay )
                ->whereRaw("
                    presensi_maksi.id not in(select d.presensi_maksi_id from presensi_maksi_det d where d.m_kary_id = ? and d.created_at = now())
                    and presensi_maksi.status = 'POSTED'
                ", [$m_kary_id ?? 0])->orderBy('id','desc')->first();
        if($data){
            // GROUP TIPE MENU
            $groupedData = 
                array_reduce(@$data->lauk ?? [], function ($carry, $item) {
                    $key = @$item['tipe_lauk_id'] . '-' . @$item['tipe_lauk_id'];
                    if (!isset($carry[$key])) {
                        $carry[$key] = @$item;
                    }
                    return $carry;
                }, []);
            $group_data = array_values($groupedData);

            $data->group_data = array_map(function ($item) use($data){
               
                $groupedDataLauk = 
                    array_reduce(@$data->lauk ?? [], function ($carry, $itm) use($item){
                        if (@$itm['tipe_lauk_id'] == @$item['tipe_lauk_id']) {
                            // Filter data lauk berdasarkan tipe_lauk.value yang sesuai
                            $carry[] = $itm;
                        }
                        return $carry;
                    }, []);
                
                return [
                    'tipe_lauk_id' => @$item['tipe_lauk_id'],
                    'tipe_lauk' => m_general::where('id', $item['tipe_lauk_id'])->pluck('value')->first(),
                    'detail'   => array_values($groupedDataLauk)
                ];
            }, $group_data);

            // hapus key lauk karena sudah dipakai semua valuenya
            unset($data->lauk);
        }

        if($data){
            $data->sudah_pesan = false;
        }else{
            $data['sudah_pesan'] = false;;
        }
        return response(['data'=>$data]);
    }

    public function custom_get_maksi_3hari()
    {
        $m_kary_id = auth()->user()->m_kary_id;

        $today = new \DateTime();
        $dayOfWeek = $today->format('N'); 

       if ($dayOfWeek == 3) { // Rabu
            $startDate = (clone $today)->modify('tomorrow')->format('Y-m-d'); // Kamis
            $endDate   = (clone $today)->modify('next saturday')->format('Y-m-d'); // Sabtu
        } elseif ($dayOfWeek == 6) { // Sabtu
            $startDate = (clone $today)->modify('next monday')->format('Y-m-d'); // Senin
            $endDate   = (clone $today)->modify('next wednesday')->format('Y-m-d'); // Rabu
        } else {
            $startDate = (clone $today)->modify('tomorrow')->format('Y-m-d');
            $endDate   = $startDate; 
        }


        $check_exists_dates = presensi_maksi_det::select('presensi_maksi.tanggal')
        ->join('presensi_maksi', 'presensi_maksi.id', '=', 'presensi_maksi_det.presensi_maksi_id')
        ->where('presensi_maksi_det.m_kary_id', $m_kary_id)
        ->whereBetween('presensi_maksi.tanggal', [$startDate, $endDate])
        ->pluck('presensi_maksi.tanggal')
        ->toArray();

        $data = $this
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->where('presensi_maksi.status', 'POSTED')
            ->orderBy('id', 'desc')
            ->get();

        if ($data->isEmpty()) {
            return response(['data' => ['sudah_pesan' => false]]);
        }

        $data->transform(function ($item) use ($check_exists_dates) {
            $groupedData = array_reduce(@$item->lauk ?? [], function ($carry, $lauk) {
                $key = @$lauk['tipe_lauk_id'];
                if (!isset($carry[$key])) {
                    $carry[$key] = $lauk;
                }
                return $carry;
            }, []);
            $group_data = array_values($groupedData);

            $item->group_data = array_map(function ($itm) use ($item) {
                $groupedDataLauk = array_filter(@$item->lauk ?? [], function ($l) use ($itm) {
                    return $l['tipe_lauk_id'] == $itm['tipe_lauk_id'];
                });
                return [
                    'tipe_lauk_id' => $itm['tipe_lauk_id'],
                    'tipe_lauk' => m_general::where('id', $itm['tipe_lauk_id'])->pluck('value')->first(),
                    'detail' => array_values($groupedDataLauk)
                ];
            }, $group_data);

            unset($item->lauk);
            $item->sudah_pesan = in_array($item->tanggal, $check_exists_dates);
            return $item;
        });

        return response(['data' => $data]);

    }




    public function custom_post($req)
    {
        $this->find($req->id)->update([
            'status' => 'POSTED'
        ]);
        return response(['message' => 'POST data berhasil']);
    }
    public function custom_closed($req)
    {
        $this->find($req->id)->update([
            'status' => 'CLOSED'
        ]);
        return response(['message' => 'Closed data berhasil']);
    }
}