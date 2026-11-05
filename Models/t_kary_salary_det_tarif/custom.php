<?php

namespace App\Models\CustomModels;

class t_kary_salary_det_tarif extends \App\Models\BasicModels\t_kary_salary_det_tarif
{    
    public function __construct()
    {
        parent::__construct();
    }
    
    public $fileColumns    = [ /*file_column*/ ];

    //public $createAdditionalData = ["creator_id"=>"auth:id"];
    //public $updateAdditionalData = ["last_editor_id"=>"auth:id"];

    
}