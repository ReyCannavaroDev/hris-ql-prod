<?php

namespace App\Models\BasicModels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Traits\ModelTrait;

class aaa extends Model
{   
    use ModelTrait;

    protected $table    = 'aaa';
    protected $guarded  = ["id"];
    protected $casts    = [
    "created_at"=> "datetime:d\/m\/Y H:i",
    "updated_at"=> "datetime:d\/m\/Y H:i"
	];
    protected $fillable = ["seq","hey"];

    public $columns     = ["id","seq","hey"];
    public $columnsFull = ["id:bigint","seq:string:191","hey:string:191"];
    public $rules       = [];
    public $joins       = [];
    public $details     = [];
    public $heirs       = [];
    public $detailsChild= [];
    public $detailsHeirs= [];
    public $unique      = [];
    public $required    = ["seq","hey"];
    public $createable  = ["seq","hey"];
    public $updateable  = ["seq","hey"];
    public $searchable  = ["id","seq","hey"];
    public $deleteable  = true;
    public $cascade     = true;
    public $deleteOnUse = false;

    
    
    
}
