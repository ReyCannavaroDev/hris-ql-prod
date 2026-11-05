<?php

namespace App\Models\BasicModels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Traits\ModelTrait;

class presensi_maksi_det extends Model
{   
    use ModelTrait;

    protected $table    = 'presensi_maksi_det';
    protected $guarded  = ["id"];
    protected $casts    = [
    "created_at"=> "datetime:d\/m\/Y H:i",
    "updated_at"=> "datetime:d\/m\/Y H:i"
	];
    protected $fillable = ["presensi_maksi_id","m_kary_id","lauk","creator_id","last_editor_id"];

    public $columns     = ["id","presensi_maksi_id","m_kary_id","lauk","creator_id","last_editor_id","created_at","updated_at"];
    public $columnsFull = ["id:bigint","presensi_maksi_id:bigint","m_kary_id:bigint","lauk:json","creator_id:bigint","last_editor_id:bigint","created_at:datetime","updated_at:datetime"];
    public $rules       = [];
    public $joins       = ["presensi_maksi.id=presensi_maksi_det.presensi_maksi_id","m_kary.id=presensi_maksi_det.m_kary_id","default_users.id=presensi_maksi_det.creator_id","default_users.id=presensi_maksi_det.last_editor_id"];
    public $details     = [];
    public $heirs       = [];
    public $detailsChild= [];
    public $detailsHeirs= [];
    public $unique      = [];
    public $required    = ["lauk"];
    public $createable  = ["presensi_maksi_id","m_kary_id","lauk","creator_id","last_editor_id"];
    public $updateable  = ["presensi_maksi_id","m_kary_id","lauk","creator_id","last_editor_id"];
    public $searchable  = ["id","presensi_maksi_id","m_kary_id","lauk","creator_id","last_editor_id","created_at","updated_at"];
    public $deleteable  = true;
    public $cascade     = true;
    public $deleteOnUse = false;

    
    
    
    public function presensi_maksi() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\presensi_maksi', 'presensi_maksi_id', 'id');
    }
    public function m_kary() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\m_kary', 'm_kary_id', 'id');
    }
    public function creator() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\default_users', 'creator_id', 'id');
    }
    public function last_editor() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\default_users', 'last_editor_id', 'id');
    }
}
