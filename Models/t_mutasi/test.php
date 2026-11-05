<?php
namespace Tests;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Laravel\Passport\Passport;
use App\Models\Defaults\User;

class tMutasiTest extends TestCase
{
    use DatabaseTransactions;

//   select 
//     (select mg1.value from m_general mg1 where mg1.id = tpd.tingkat_id), tp.tanggal, tp.nomor,tp.nama_pelamar,tp.ktp_no,tp.telp, tp.tgl_lahir, tp.tempat_lahir, mg.value jk,tp.salary,tp.deskripsi,kd.nama dir,kdi.nama div, kde.nama dept,mp.desc_kerja posisi
//     from t_pelamar tp
//       join m_general mg on mg.id = tp.jk_id
//       join t_pelamar_det_pend tpd on tpd.t_pelamar_id = tp.id
//       left join m_dir kd on kd.id = tp.m_dir_id 
//       left join m_divisi kdi on kdi.id = tp.m_divisi_id 
//       left join m_dept kde on kde.id = tp.m_dept_id
//       left join m_posisi mp on mp.id = tp.m_posisi_id
//       where tp.status = 'active' and tpd.tingkat_id = (SELECT MAX(tingkat_id) FROM t_pelamar_det_pend) and tp.tanggal BETWEEN '2023-01-01' AND '2023-12-30'


    // select 
    // ts.tgl, ts.nomor,ts.keterangan,ts.status,ts.no_dokumen, k.nik, k.nama_lengkap, kd.nama dir, mg.value tipe_sgp  
    // from t_sgp ts
    //   join m_kary k on k.id = ts.m_kary_id 
    //   join m_general mg on mg.id = ts.tipe_sgp_id
    //   left join m_dir kd on kd.id = ts.m_dir_id 
    //   where ts.status = 'POSTED' and ts.tgl BETWEEN '2023-01-01' AND '2023-12-31'


    //     select 
    // tm.tgl, tm.nomor,tm.deskripsi,tm.catatan,tm.keterangan,tm.status, k.nik, k.nama_lengkap, kd.nama dir, kdi.nama divisi_lama, kdi1.nama divisi_baru, kde.nama dept_lama
    // ,kde1.nama dept_baru, mp.desc_kerja posisi_lama, mp1.desc_kerja posisi_baru, mg.kode gaji_lama, mg1.kode gaji_baru  
    // from t_mutasi tm
    //   join m_kary k on k.id = tm.m_kary_id 
    //   left join m_dir kd on kd.id = tm.m_dir_id 
    //   left join m_divisi kdi on kdi.id = tm.m_divisi_lama_id
    //   left join m_divisi kdi1 on kdi1.id = tm.m_devisi_baru_id 
    //   left join m_dept kde on kde.id = tm.m_dept_lama_id
    //   left join m_dept kde1 on kde1.id = tm.m_dept_baru_id
    //   left join m_posisi mp on mp.id = tm.m_posisi_lama_id
    //   left join m_posisi mp1 on mp1.id = tm.m_posisi_baru_id
    //   left join m_standart_gaji mg on mg.id = tm.m_standart_posisi_id
    //   left join m_standart_gaji mg1 on mg1.id = tm.m_standart_baru_id  
    //   where tm.status = 'POSTED' and tm.tgl BETWEEN '2023-11-06' AND '2023-11-17'

    public function testReadingData()
    {
        Passport::actingAs(User::first());
        $payload = [
            'paginate' => 25
        ];

        $this->call('GET', '/operation/t_mutasi', $payload);

        $responseArr = json_decode( $this->response->getContent(),true );
        ff( $responseArr, 'dump data' );

        $this->assertTrue(true);
    }

    public function testCreatingData()
    {
        $user = User::where('username', 'USERNAME')->first();
        $this->assertNotEmpty( $user );
        
        Passport::actingAs($user);

        $payload = [
		    "id" => "bigint:optional:autocreate",
		    "nomor" => "string:50:optional",
		    "m_comp_id" => "bigint:optional",
		    "m_dir_id" => "bigint:optional",
		    "m_kary_id" => "bigint:required",
		    "tgl" => "date:required",
		    "status_kary_id" => "bigint:required",
		    "tipe_mutasi" => "string:191:required",
		    "m_divisi_lama_id" => "bigint:required",
		    "m_dept_lama_id" => "bigint:optional",
		    "m_posisi_lama_id" => "bigint:required",
		    "m_standart_posisi_id" => "bigint:required",
		    "m_devisi_baru_id" => "bigint:required",
		    "m_dept_baru_id" => "bigint:optional",
		    "m_posisi_baru_id" => "bigint:required",
		    "m_standart_baru_id" => "bigint:required",
		    "no_dokumen" => "string:191:required",
		    "file_dokumen" => "string:191:required",
		    "deskripsi" => "text:required",
		    "catatan" => "string:191:optional",
		    "keterangan" => "text:optional",
		    "status" => "string:50:optional",
		    "creator_id" => "bigint:optional",
		    "last_editor_id" => "bigint:optional",
		    "created_at" => "datetime:optional:autocreate",
		    "updated_at" => "datetime:optional:autocreate"
		];

        $this->call('POST', '/operation/t_mutasi', $payload);

        $responseArr = json_decode( $this->response->getContent(),true );
        // ff( $responseArr, 'dump data' );

        $this->assertEquals( 200, $this->response->status() );
        // $this->seeJsonStructure( ['status'] );

        $this->seeInDatabase('t_mutasi', array_filter($payload, function($dt){
            return !is_array($dt);
        } ));
    }
}