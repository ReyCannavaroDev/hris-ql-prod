<?php

namespace App\Models\BasicModels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Traits\ModelTrait;

class presensi_maksi extends Model
{   
    use ModelTrait;

    protected $table    = 'presensi_maksi';
    protected $guarded  = ["id"];
    protected $casts    = [
    "created_at"=> "datetime:d\/m\/Y H:i",
    "updated_at"=> "datetime:d\/m\/Y H:i"
	];
    protected $fillable = ["presensi_m_menu_maksi_id","tanggal","lauk","status","keterangan","creator_id","last_editor_id"];

    public $columns     = ["id","presensi_m_menu_maksi_id","tanggal","lauk","status","keterangan","creator_id","last_editor_id","created_at","updated_at"];
    public $columnsFull = ["id:bigint","presensi_m_menu_maksi_id:bigint","tanggal:date","lauk:json","status:string:191","keterangan:string:191","creator_id:bigint","last_editor_id:bigint","created_at:datetime","updated_at:datetime"];
    public $rules       = [];
    public $joins       = ["presensi_m_menu_maksi.id=presensi_maksi.presensi_m_menu_maksi_id","default_users.id=presensi_maksi.creator_id","default_users.id=presensi_maksi.last_editor_id"];
    public $details     = ["presensi_maksi_jadwal_ambil","presensi_maksi_det"];
    public $heirs       = [];
    public $detailsChild= [];
    public $detailsHeirs= [];
    public $unique      = [];
    public $required    = ["tanggal","lauk"];
    public $createable  = ["presensi_m_menu_maksi_id","tanggal","lauk","status","keterangan","creator_id","last_editor_id"];
    public $updateable  = ["presensi_m_menu_maksi_id","tanggal","lauk","status","keterangan","creator_id","last_editor_id"];
    public $searchable  = ["id","presensi_m_menu_maksi_id","tanggal","lauk","status","keterangan","creator_id","last_editor_id","created_at","updated_at"];
    public $deleteable  = true;
    public $cascade     = true;
    public $deleteOnUse = false;

    
    public function presensi_maksi_jadwal_ambil() :\HasMany
    {
        return $this->hasMany('App\Models\BasicModels\presensi_maksi_jadwal_ambil', 'presensi_maksi_id', 'id');
    }
    public function presensi_maksi_det() :\HasMany
    {
        return $this->hasMany('App\Models\BasicModels\presensi_maksi_det', 'presensi_maksi_id', 'id');
    }
    
    
    public function presensi_m_menu_maksi() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\presensi_m_menu_maksi', 'presensi_m_menu_maksi_id', 'id');
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
