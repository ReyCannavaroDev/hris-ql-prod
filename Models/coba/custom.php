<?php

namespace App\Models\CustomModels;

class coba extends \App\Models\BasicModels\coba
{    
    public function __construct()
    {
        parent::__construct();
    }
    
    public $fileColumns    = [ /*file_column*/ ];

    //public $createAdditionalData = ["creator_id"=>"auth:id"];
    //public $updateAdditionalData = ["last_editor_id"=>"auth:id"];

    public function custom_datacustomer(){
        $request = app()->request();
        $datacustomer = coba::with('coba1')->get();
        return response($datacustomer,200);
    }

    public function scopeWithDetail(){
        return $this->with('coba1');
    }
}