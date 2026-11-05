<?php

namespace App\Models\BasicModels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Traits\ModelTrait;

class t_logbook extends Model
{   
    use ModelTrait;

    protected $table    = 't_logbook';
    protected $guarded  = ["id"];
    protected $casts    = [
    "created_at"=> "datetime:d\/m\/Y H:i",
    "updated_at"=> "datetime:d\/m\/Y H:i"
	];
    protected $fillable = ["m_kary_id","tanggal","keterangan","creator_id","last_editor_id","deletor_id","deleted_at"];

    public $columns     = ["id","m_kary_id","tanggal","keterangan","creator_id","last_editor_id","created_at","updated_at","deletor_id","deleted_at"];
    public $columnsFull = ["id:bigint","m_kary_id:bigint","tanggal:date","keterangan:string:200","creator_id:bigint","last_editor_id:bigint","created_at:datetime","updated_at:datetime","deletor_id:bigint","deleted_at:datetime"];
    public $rules       = [];
    public $joins       = ["m_kary.id=t_logbook.m_kary_id"];
    public $details     = ["t_logbook_d"];
    public $heirs       = [];
    public $detailsChild= [];
    public $detailsHeirs= [];
    public $unique      = [];
    public $required    = ["m_kary_id","tanggal"];
    public $createable  = ["m_kary_id","tanggal","keterangan","creator_id","last_editor_id","deletor_id","deleted_at"];
    public $updateable  = ["m_kary_id","tanggal","keterangan","creator_id","last_editor_id","deletor_id","deleted_at"];
    public $searchable  = ["id","m_kary_id","tanggal","keterangan","creator_id","last_editor_id","created_at","updated_at","deletor_id","deleted_at"];
    public $deleteable  = true;
    public $cascade     = true;
    public $deleteOnUse = false;

    
    public function t_logbook_d() :\HasMany
    {
        return $this->hasMany('App\Models\BasicModels\t_logbook_d', 't_logbook_id', 'id');
    }
    
    
    public function m_kary() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\m_kary', 'm_kary_id', 'id');
    }
}
