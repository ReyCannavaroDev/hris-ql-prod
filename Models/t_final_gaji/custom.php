<?php

namespace App\Models\CustomModels;

class t_final_gaji extends \App\Models\BasicModels\t_final_gaji
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

    public function createBefore( $model, $arrayData, $metaData, $id=null )
    {
        $req = app()->request;
        $newArrayData  = array_merge( $arrayData,[
            'nomor' => $this->helper->generateNomor('KODE FINAL GAJI')
        ]);
       
        return [
            "model"  => $model,
            "data"   => $newArrayData,
            // "errors" => ['error1']
        ];
    }

    public function custom_tes()
    {
        $det = t_final_gaji_det::with(['t_final_gaji_det_rincian' => function ($query) {
        $query->where('t_potongan_id', '!=', null);
        }])
        ->where('t_final_gaji_id', 1)
        ->get();


        return response()->json($det);
    }
    
    public function custom_postData($request)
    {
        \DB::beginTransaction();
        $data = t_final_gaji::find($request->id);

        if (!$data) {
            return response()->json(['error' => 'Data tidak ditemukan.'], 404);
        }

        try {

            $update = $data->update([
                'status' => "POSTED"
            ]);
            $create = true;
            $det = t_final_gaji_det::with(['t_final_gaji_det_rincian' => function ($query) {
            $query->where('t_potongan_id', '!=', null);
            }])
            ->where('t_final_gaji_id', $request->id)
            ->get();
            foreach($det as $dt){
                foreach($dt['t_final_gaji_det_rincian'] as $dt1){
                    $potongan = t_potongan::where('id', $dt1['t_potongan_id'])->first();
                    $create = t_potongan_det_bayar::create([
                        'm_potongan_id' => $dt1['t_potongan_id'],
                        't_final_gaji_id' => $request->id,
                        'percentage' =>  $potongan['percentage'],
                        'nilai' => $dt1['value'],
                        'paid_at' => \Carbon::now()
                    ]) && $create;
                }
                // if($create){
                //     $cekAngsuran = t_potongan_det_bayar::where('t_potongan_id')
                // }
            }

            if ($update && $create) {
                 \DB::commit(); 
                return response()->json(['message' => 'Data berhasil diposting.']);
            } else {
                \DB::rollback();
                return response()->json(['error' => 'Gagal memperbarui status.'], 500);
            }
        } catch (\Exception $e) {
            \DB::rollBack();
            // Handle exception, log error messages, etc.
            return response()->json(['error' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }   
}