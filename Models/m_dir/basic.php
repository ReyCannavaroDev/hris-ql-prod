<?php

namespace App\Models\BasicModels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Traits\ModelTrait;

class m_dir extends Model
{   
    use ModelTrait;

    protected $table    = 'm_dir';
    protected $guarded  = ["id"];
    protected $casts    = [
    "created_at"=> "datetime:d\/m\/Y H:i",
    "updated_at"=> "datetime:d\/m\/Y H:i"
	];
    protected $fillable = ["m_comp_id","nama","desc","is_active","creator_id","last_editor_id"];

    public $columns     = ["id","m_comp_id","nama","desc","is_active","creator_id","last_editor_id","created_at","updated_at"];
    public $columnsFull = ["id:bigint","m_comp_id:bigint","nama:string:100","desc:text","is_active:boolean","creator_id:bigint","last_editor_id:bigint","created_at:datetime","updated_at:datetime"];
    public $rules       = [];
    public $joins       = ["m_comp.id=m_dir.m_comp_id","default_users.id=m_dir.creator_id","default_users.id=m_dir.last_editor_id"];
    public $details     = [];
    public $heirs       = ["m_kary_det_pel","t_final_gaji_det","t_cuti","m_kary_det_kartu","m_kary_det_pemb","m_file","m_general","m_kary","default_users","t_spd","t_jadwal_kerja","m_pengguna","t_potongan","t_cuti_adjustment","t_grup_kerja","m_kary_det_bhs","m_kary_det_pk","t_pelamar","m_kary_det_kel","t_lembur","m_menu","t_hasil_tes","t_riwayat_posisi","m_kary_det_org","m_approval","t_perhitungan_gaji","m_dept","m_lembur","m_divisi","m_kary_det_pend","generate_approval","m_role","m_role_access","m_role_det","m_lokasi","m_zona","m_zona_det","m_jam_kerja","t_jadwal_kerja_det","t_sgp","generate_approval_log","t_loker","m_spd_det_biaya","m_tunj_kemahalan","m_knd_dinas","m_libur_nasional","m_spd","m_spd_det_transport","m_standart_gaji","t_mutasi","m_kary_det_pres","m_posisi"];
    public $detailsChild= [];
    public $detailsHeirs= [];
    public $unique      = [];
    public $required    = ["nama","is_active"];
    public $createable  = ["m_comp_id","nama","desc","is_active","creator_id","last_editor_id"];
    public $updateable  = ["m_comp_id","nama","desc","is_active","creator_id","last_editor_id"];
    public $searchable  = ["id","m_comp_id","nama","desc","is_active","creator_id","last_editor_id","created_at","updated_at"];
    public $deleteable  = true;
    public $cascade     = true;
    public $deleteOnUse = false;

    
    
    
    public function m_comp() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\m_comp', 'm_comp_id', 'id');
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
