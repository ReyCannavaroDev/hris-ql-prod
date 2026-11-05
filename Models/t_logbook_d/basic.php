<?php

namespace App\Models\BasicModels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Traits\ModelTrait;

class t_logbook_d extends Model
{   
    use ModelTrait;

    protected $table    = 't_logbook_d';
    protected $guarded  = ["id"];
    protected $casts    = [
    "created_at"=> "datetime:d\/m\/Y H:i",
    "updated_at"=> "datetime:d\/m\/Y H:i"
	];
    protected $fillable = ["t_logbook_id","m_proyek_id","task","status","creator_id","last_editor_id","deletor_id","deleted_at"];

    public $columns     = ["id","t_logbook_id","m_proyek_id","task","status","creator_id","last_editor_id","created_at","updated_at","deletor_id","deleted_at"];
    public $columnsFull = ["id:bigint","t_logbook_id:bigint","m_proyek_id:bigint","task:string:200","status:string:191","creator_id:bigint","last_editor_id:bigint","created_at:datetime","updated_at:datetime","deletor_id:bigint","deleted_at:datetime"];
    public $rules       = [];
    public $joins       = ["t_logbook.id=t_logbook_d.t_logbook_id","m_proyek.id=t_logbook_d.m_proyek_id"];
    public $details     = [];
    public $heirs       = [];
    public $detailsChild= [];
    public $detailsHeirs= [];
    public $unique      = [];
    public $required    = ["m_proyek_id","task","status"];
    public $createable  = ["t_logbook_id","m_proyek_id","task","status","creator_id","last_editor_id","deletor_id","deleted_at"];
    public $updateable  = ["t_logbook_id","m_proyek_id","task","status","creator_id","last_editor_id","deletor_id","deleted_at"];
    public $searchable  = ["id","t_logbook_id","m_proyek_id","task","status","creator_id","last_editor_id","created_at","updated_at","deletor_id","deleted_at"];
    public $deleteable  = true;
    public $cascade     = true;
    public $deleteOnUse = false;

    
    
    
    public function t_logbook() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\t_logbook', 't_logbook_id', 'id');
    }
    public function m_proyek() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\m_proyek', 'm_proyek_id', 'id');
    }
}
