<?php

namespace App\Models\BasicModels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Traits\ModelTrait;

class presensi_m_menu_maksi_det extends Model
{   
    use ModelTrait;

    protected $table    = 'presensi_m_menu_maksi_det';
    protected $guarded  = ["id"];
    protected $casts    = [
    "created_at"=> "datetime:d\/m\/Y H:i",
    "updated_at"=> "datetime:d\/m\/Y H:i"
	];
    protected $fillable = ["presensi_m_menu_maksi_id","tipe_lauk_id","lauk","creator_id"];

    public $columns     = ["id","presensi_m_menu_maksi_id","tipe_lauk_id","lauk","creator_id","created_at","updated_at"];
    public $columnsFull = ["id:bigint","presensi_m_menu_maksi_id:bigint","tipe_lauk_id:bigint","lauk:string:191","creator_id:bigint","created_at:datetime","updated_at:datetime"];
    public $rules       = [];
    public $joins       = ["presensi_m_menu_maksi.id=presensi_m_menu_maksi_det.presensi_m_menu_maksi_id","m_general.id=presensi_m_menu_maksi_det.tipe_lauk_id","default_users.id=presensi_m_menu_maksi_det.creator_id"];
    public $details     = [];
    public $heirs       = [];
    public $detailsChild= [];
    public $detailsHeirs= [];
    public $unique      = [];
    public $required    = ["lauk"];
    public $createable  = ["presensi_m_menu_maksi_id","tipe_lauk_id","lauk","creator_id"];
    public $updateable  = ["presensi_m_menu_maksi_id","tipe_lauk_id","lauk","creator_id"];
    public $searchable  = ["id","presensi_m_menu_maksi_id","tipe_lauk_id","lauk","creator_id","created_at","updated_at"];
    public $deleteable  = true;
    public $cascade     = true;
    public $deleteOnUse = false;

    
    
    
    public function presensi_m_menu_maksi() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\presensi_m_menu_maksi', 'presensi_m_menu_maksi_id', 'id');
    }
    public function tipe_lauk() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\m_general', 'tipe_lauk_id', 'id');
    }
    public function creator() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\default_users', 'creator_id', 'id');
    }
}
