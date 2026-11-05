<?php

namespace App\Models\BasicModels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Traits\ModelTrait;

class coba1 extends Model
{   
    use ModelTrait;

    protected $table    = 'coba1';
    protected $guarded  = ["id"];
    protected $casts    = [
    "created_at"=> "datetime:d\/m\/Y H:i",
    "updated_at"=> "datetime:d\/m\/Y H:i"
	];
    protected $fillable = ["customerid","address","city","state","zipcode","country"];

    public $columns     = ["id","customerid","address","city","state","zipcode","country","created_at","updated_at"];
    public $columnsFull = ["id:bigint","customerid:bigint","address:string:191","city:string:191","state:string:191","zipcode:string:191","country:string:191","created_at:datetime","updated_at:datetime"];
    public $rules       = [];
    public $joins       = ["coba.id=coba1.customerid"];
    public $details     = [];
    public $heirs       = [];
    public $detailsChild= [];
    public $detailsHeirs= [];
    public $unique      = [];
    public $required    = ["address","city","state","zipcode","country"];
    public $createable  = ["customerid","address","city","state","zipcode","country"];
    public $updateable  = ["customerid","address","city","state","zipcode","country"];
    public $searchable  = ["id","customerid","address","city","state","zipcode","country","created_at","updated_at"];
    public $deleteable  = true;
    public $cascade     = true;
    public $deleteOnUse = false;

    
    
    
    public function customerid() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\coba', 'customerid', 'id');
    }
}
