<?php

namespace App\Models\BasicModels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Traits\ModelTrait;

class m_general extends Model
{   
    use ModelTrait;

    protected $table    = 'm_general';
    protected $guarded  = ["id"];
    protected $casts    = [
    "created_at"=> "datetime:d\/m\/Y H:i",
    "updated_at"=> "datetime:d\/m\/Y H:i"
	];
    protected $fillable = ["m_comp_id","m_dir_id","group","key","code","value","is_active","creator_id","last_editor_id","value_2","value_3"];

    public $columns     = ["id","m_comp_id","m_dir_id","group","key","code","value","is_active","creator_id","last_editor_id","created_at","updated_at","value_2","value_3"];
    public $columnsFull = ["id:bigint","m_comp_id:bigint","m_dir_id:bigint","group:string:191","key:string:191","code:string:191","value:string:191","is_active:boolean","creator_id:bigint","last_editor_id:bigint","created_at:datetime","updated_at:datetime","value_2:string:191","value_3:string:191"];
    public $rules       = [];
    public $joins       = ["m_comp.id=m_general.m_comp_id","m_dir.id=m_general.m_dir_id","default_users.id=m_general.creator_id","default_users.id=m_general.last_editor_id"];
    public $details     = [];
    public $heirs       = ["m_kary_det_pel","t_final_gaji_det","t_pelamar_det_pres","t_cuti","t_cuti","t_rpd_det","m_kary_det_kartu","m_kary_det_pemb","m_kary_det_pemb","m_kary_det_pemb","m_kary_det_pemb","t_pelamar_det_pk","m_kary","m_kary","m_kary","m_kary","m_kary","m_kary","m_kary","m_kary","m_kary","m_kary","m_kary","m_kary","m_kary","t_spd","t_jadwal_kerja","t_potongan","t_cuti_adjustment","t_pelamar_det_pel","m_kary_det_pk","t_pelamar","m_kary_det_kel","m_kary_det_kel","m_kary_det_kel","m_kary_det_kel","t_pelamar_det_peng","t_lembur","t_lembur","m_kary_det_org","m_kary_det_org","t_perhitungan_gaji","m_lembur","m_kary_det_pend","m_kary_det_pend","t_spd_det","t_pelamar_det_kartu","m_jam_kerja","t_sgp","t_loker","t_loker","t_riwayat_posisi_det","t_riwayat_posisi_det","m_spd_det_biaya","m_tunj_kemahalan","t_pelamar_det_pend","t_pelamar_det_pend","m_spd","m_spd_det_transport","presensi_m_menu_maksi_det","m_standart_gaji","t_mutasi","m_kary_det_pres","m_posisi","m_posisi","t_pelamar_det_org","t_pelamar_det_org"];
    public $detailsChild= [];
    public $detailsHeirs= [];
    public $unique      = [];
    public $required    = ["group","value","is_active"];
    public $createable  = ["m_comp_id","m_dir_id","group","key","code","value","is_active","creator_id","last_editor_id","value_2","value_3"];
    public $updateable  = ["m_comp_id","m_dir_id","group","key","code","value","is_active","creator_id","last_editor_id","value_2","value_3"];
    public $searchable  = ["id","m_comp_id","m_dir_id","group","key","code","value","is_active","creator_id","last_editor_id","created_at","updated_at","value_2","value_3"];
    public $deleteable  = true;
    public $cascade     = true;
    public $deleteOnUse = false;

    
    
    
    public function m_comp() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\m_comp', 'm_comp_id', 'id');
    }
    public function m_dir() :\BelongsTo
    {
        return $this->belongsTo('App\Models\BasicModels\m_dir', 'm_dir_id', 'id');
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
