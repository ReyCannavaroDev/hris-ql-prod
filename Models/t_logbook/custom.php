<?php

namespace App\Models\CustomModels;

class t_logbook extends \App\Models\BasicModels\t_logbook
{   
    private $helper; 
    public function __construct()
    {
        parent::__construct();
        $this->helper = getCore('Helper');

    }
    
    public $fileColumns    = [ /*file_column*/ ];

    public $createAdditionalData = ["creator_id"=>"auth:id"];
    public $updateAdditionalData = ["last_editor_id"=>"auth:id"];


    public function custom_group_by_proyek(){
        $today = \Carbon::today();
        $date = request('date');
        $startDate = $today->copy()->startOfMonth();
        $endDate = $today->copy()->endOfMonth();
        
        $outstanding = request('outstanding');
        $data = $this->with(['t_logbook_d' => function($item){
                    $item->join('m_proyek','t_logbook_d.m_proyek_id','m_proyek.id')->select('t_logbook_d.*','m_proyek.proyek_nama','m_proyek.proyek_kode');
                }])->where('t_logbook.m_kary_id',auth()->user()->m_kary_id)->whereBetween('tanggal',[$startDate, $endDate]);
        
        if($date){
            $data = $data->whereDate('tanggal',$date);
        }

        $data = $data->get();

        $data = $data->map(function ($item) use ($today, $outstanding) {
            $dateCarbon = \Carbon::parse($item->tanggal);

            $item->group_data = $item->t_logbook_d->groupBy('m_proyek_id')->map(function ($group, $proyekId) use($dateCarbon, $today, $outstanding){
                $result =  [
                    'proyek_id' => $proyekId,
                    'proyek_nama' => $group->first()->proyek_nama,
                    'proyek_kode' => $group->first()->proyek_kode,
                    'detail' => $group->filter(function ($logbookItem)use($dateCarbon, $today, $outstanding){
                        $logbookItem->outstanding = $dateCarbon->lessThan($today);

                        if($outstanding){
                            return $logbookItem->outstanding && $logbookItem->status !== "DONE";
                        };
                        return $logbookItem;
                    })->map(function($logbookItem){
                        return $logbookItem;
                    })->values(),
                ];
                // if($result['detail']->isEmpty()) return 
                return $result;
            })->values();

            unset($item->t_logbook_d);
            return $item;
        });
        return $this->helper->customResponse('OK',200,$data);
    }

    public function custom_save($req){
        $date = \Carbon::createFromFormat('d/m/Y',$req['tanggal'])->toDateString();
        try{
            \DB::beginTransaction();
            $logbook = t_logbook::updateOrCreate(['tanggal' => $date,'m_kary_id' => $req['m_kary_id']],[
                'keterangan' => $req['keterangan'],
                'tanggal' => $date,
                'm_kary_id' => $req['m_kary_id']
            ]);
            
            $collect = collect($req['t_logbook_d']);
            foreach($collect as $single){
                $single['t_logbook_id'] = $logbook['id'];
                t_logbook_d::updateOrCreate(['id'=> @$single['id']], $single);
            }

            \DB::commit();
            return $this->helper->customResponse("Success",201);
        } catch(\Exception $e) {
            \DB::rollback();
            return $this->helper->customResponse("Terjadi kesalahan, silahkan hubungin admin - ".$e->getMessage(),500);
        }
    }

    public function custom_delete_group($req){
        $getData = t_logbook_d::where('m_proyek_id',$req['m_proyek_id'])->where('t_logbook_id',$req['t_logbook_id'])->delete();
        if($getData){
            return $this->helper->customResponse("Success",201);
        }else{
            return $this->helper->customResponse("No Matching Records found",500);
        }
    }

    public function custom_delete_proyek_by_date($req){
        $getData = t_logbook_d::where('m_proyek_id',$req['m_proyek_id'])->whereHas('t_logbook',function($query) use($req){
            $query->where('tanggal',$req['tanggal']);
        })->delete();
        if($getData){
            return $this->helper->customResponse("Success",201);
        }else{
            return $this->helper->customResponse("No Matching Records found",500);

        }
    }


}