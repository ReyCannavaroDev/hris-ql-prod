<?php

namespace App\Models\BasicModels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Traits\ModelTrait;

class presensi_m_menu_maksi extends Model
{   
    use ModelTrait;

    protected $table    = 'presensi_m_menu_maksi';
    protected $guarded  = ["id"];
    protected $casts    = [
    "created_at"=> "datetime:d\/m\/Y H:i",
    "updated_at"=> "datetime:d\/m\/Y H:i"
	];
    protected $fillable = ["judul","deskripsi","is_active","creator_id","last_editor_id"];

    public $columns     = ["id","judul","deskripsi","is_active","creator_id","last_editor_id","created_at","updated_at"];
    public $columnsFull = ["id:bigint","judul:string:191","deskripsi:string:191","is_active:boolean","creator_id:bigint","last_editor_id:bigint","created_at:datetime","updated_at:datetime"];
    public $rules       = [];
    public $joins       = ["default_users.id=presensi_m_menu_maksi.creator_id","default_users.id=presensi_m_menu_maksi.last_editor_id"];
    public $details     = ["presensi_m_menu_maksi_det"];
    public $heirs       = ["presensi_maksi"];
    public $detailsChild= [];
    public $detailsHeirs= [];
    public $unique      = [];
    public $required    = ["judul","is_active"];
    public $createable  = ["judul","deskripsi","is_active","creator_id","last_editor_id"];
    public $updateable  = ["judul","deskripsi","is_active","creator_id","last_editor_id"];
    public $searchable  = ["id","judul","deskripsi","is_active","creator_id","last_editor_id","created_at","updated_at"];
    public $deleteable  = true;
    public $cascade     = true;
    public $deleteOnUse = false;

    
    public function presensi_m_menu_maksi_det() :\HasMany
    {
        return $this->hasMany('App\Models\BasicModels\presensi_m_menu_maksi_det', 'presensi_m_menu_maksi_id', 'id');
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
