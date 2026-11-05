<?php

namespace App\Models\CustomModels;

class t_cuti_adjustment extends \App\Models\BasicModels\t_cuti_adjustment
{    
    public function __construct()
    {
        parent::__construct();
    }
    
    public $fileColumns    = [ /*file_column*/ ];

    //public $createAdditionalData = ["creator_id"=>"auth:id"];
    //public $updateAdditionalData = ["last_editor_id"=>"auth:id"];

    // public function createBefore( $model, $arrayData, $metaData, $id=null )
    // {
    //   $newArrayData  = array_merge( $arrayData,[
    //     "nomor" => $this->helper->generateNomor("KODE CUTI"),
    //   ] );
    //   return [
    //       "model"  => $model,
    //       "data"   => $newArrayData,
    //       // "errors" => ['error1']
    //   ];
    // }
    

    
}