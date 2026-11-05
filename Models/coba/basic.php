<?php

namespace App\Models\BasicModels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Traits\ModelTrait;

class coba extends Model
{   
    use ModelTrait;

    protected $table    = 'coba';
    protected $guarded  = ["id"];
    protected $casts    = [
    "created_at"=> "datetime:d\/m\/Y H:i",
    "updated_at"=> "datetime:d\/m\/Y H:i"
	];
    protected $fillable = ["firstname","lastname","email","phone"];

    public $columns     = ["id","firstname","lastname","email","phone","created_at","updated_at"];
    public $columnsFull = ["id:bigint","firstname:string:191","lastname:string:191","email:string:191","phone:string:191","created_at:datetime","updated_at:datetime"];
    public $rules       = [];
    public $joins       = [];
    public $details     = ["coba1"];
    public $heirs       = [];
    public $detailsChild= [];
    public $detailsHeirs= [];
    public $unique      = [];
    public $required    = ["firstname","lastname","email","phone"];
    public $createable  = ["firstname","lastname","email","phone"];
    public $updateable  = ["firstname","lastname","email","phone"];
    public $searchable  = ["id","firstname","lastname","email","phone","created_at","updated_at"];
    public $deleteable  = true;
    public $cascade     = true;
    public $deleteOnUse = false;

    
    public function coba1() :\HasMany
    {
        return $this->hasMany('App\Models\BasicModels\coba1', 'customerid', 'id');
    }
    
    
}
