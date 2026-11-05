<?php

namespace App\Models\BasicModels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Traits\ModelTrait;

class t_kary_salary_det_tarif extends Model
{   
    use ModelTrait;

    protected $table    = 't_kary_salary_det_tarif';
    protected $guarded  = ["id"];
    protected $casts    = [
    "created_at"=> "datetime:d\/m\/Y H:i",
    "updated_at"=> "datetime:d\/m\/Y H:i"
	];
    protected $fillable = ["m_kary","date","m_tarif_group_id","m_tarif_id","tarif","tarif_desc","qty","subtotal","keterangan","pic_id","creator_id","last_editor_id"];

    public $columns     = ["id","m_kary","date","m_tarif_group_id","m_tarif_id","tarif","tarif_desc","qty","subtotal","keterangan","pic_id","creator_id","last_editor_id","created_at","updated_at"];
    public $columnsFull = ["id:bigint","m_kary:bigint","date:date","m_tarif_group_id:bigint","m_tarif_id:bigint","tarif:decimal","tarif_desc:string:191","qty:decimal","subtotal:decimal","keterangan:string:191","pic_id:bigint","creator_id:bigint","last_editor_id:bigint","created_at:datetime","updated_at:datetime"];
    public $rules       = [];
    public $joins       = ["m_kary.id=t_kary_salary_det_tarif.m_kary","m_tarif_group.id=t_kary_salary_det_tarif.m_tarif_group_id","m_tarif.id=t_kary_salary_det_tarif.m_tarif_id","default_users.id=t_kary_salary_det_tarif.pic_id"];
    public $details     = [];
    public $heirs       = [];
    public $detailsChild= [];
    public $detailsHeirs= [];
    public $unique      = [];
    public $required    = ["m_kary","date","m_tarif_id","tarif","tarif_desc","qty","subtotal","pic_id"];
    public $createable  = ["m_kary","date","m_tarif_group_id","m_tarif_id","tarif","tarif_desc","qty","subtotal","keterangan","pic_id","creator_id","last_editor_id"];
    public $updateable  = ["m_kary","date","m_tarif_group_id","m_tarif_id","tarif","tarif_desc","qty","subtotal","keterangan","pic_id","creator_id","last_editor_id"];
    public $searchable  = ["id","m_kary","date","m_tarif_group_id","m_tarif_id","tarif","tarif_desc","qty","subtotal","keterangan","pic_id","creator_id","last_editor_id","created_at","updated_at"];
    public $deleteable  = true;
    public $cascade     = true;
    public $deleteOnUse = false;

    
    
    
    public function m_kary() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\m_kary', 'm_kary', 'id');
    }
    public function m_tarif_group() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\m_tarif_group', 'm_tarif_group_id', 'id');
    }
    public function m_tarif() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\m_tarif', 'm_tarif_id', 'id');
    }
    public function pic() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\default_users', 'pic_id', 'id');
    }
}
