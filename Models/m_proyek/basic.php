<?php

namespace App\Models\BasicModels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Traits\ModelTrait;

class m_proyek extends Model
{   
    use ModelTrait;

    protected $table    = 'm_proyek';
    protected $guarded  = ["id"];
    protected $casts    = [
    "created_at"=> "datetime:d\/m\/Y H:i",
    "updated_at"=> "datetime:d\/m\/Y H:i"
	];
    protected $fillable = ["proyek_nama","proyek_kode","creator_id","last_editor_id","deletor_id","deleted_at"];

    public $columns     = ["id","proyek_nama","proyek_kode","creator_id","last_editor_id","created_at","updated_at","deletor_id","deleted_at"];
    public $columnsFull = ["id:bigint","proyek_nama:string:200","proyek_kode:string:20","creator_id:bigint","last_editor_id:bigint","created_at:datetime","updated_at:datetime","deletor_id:bigint","deleted_at:datetime"];
    public $rules       = [];
    public $joins       = [];
    public $details     = [];
    public $heirs       = ["t_logbook_d"];
    public $detailsChild= [];
    public $detailsHeirs= [];
    public $unique      = [];
    public $required    = ["proyek_nama"];
    public $createable  = ["proyek_nama","proyek_kode","creator_id","last_editor_id","deletor_id","deleted_at"];
    public $updateable  = ["proyek_nama","proyek_kode","creator_id","last_editor_id","deletor_id","deleted_at"];
    public $searchable  = ["id","proyek_nama","proyek_kode","creator_id","last_editor_id","created_at","updated_at","deletor_id","deleted_at"];
    public $deleteable  = true;
    public $cascade     = true;
    public $deleteOnUse = false;

    
    
    
}
