<?php

namespace App\Models\CustomModels;

class presensi_m_menu_maksi_det extends \App\Models\BasicModels\presensi_m_menu_maksi_det
{    
    public function __construct()
    {
        parent::__construct();
    }
    
    public $fileColumns    = [ /*file_column*/ ];

    //public $createAdditionalData = ["creator_id"=>"auth:id"];
    //public $updateAdditionalData = ["last_editor_id"=>"auth:id"];

    
}