<?php

namespace App\Models\CustomModels;

class t_perhitungan_gaji extends \App\Models\BasicModels\t_perhitungan_gaji
{    
    private $helper;
    public function __construct()
    {
        parent::__construct();
        $this->helper = getCore('Helper');
    }
    
    public $fileColumns    = [ /*file_column*/ ];

    public $createAdditionalData = ["creator_id"=>"auth:id"];
    public $updateAdditionalData = ["last_editor_id"=>"auth:id"];


    protected $factorAdded = [];

    private function factorSalary($standart_gaji, $kary = null, $periode = null)
    {
        $firstDayOfMonth = "$periode-01";
        $date = new \DateTime($firstDayOfMonth);

        // Set the date to the last day of the month
        $date->modify('last day of this month');

        // Get the last day as a string in 'Y-m-d' format
        $lastDayOfMonth = $date->format('Y-m-d');

        $defaultColumns = [
            [
                'name'  => 'gaji_pokok',
                'type'  => 'gaji_pokok_periode'
            ],
            [
                'name'  => 'uang_saku',
                'type'  => 'uang_saku_periode'
            ],
            [
                'name'  => 'tunjangan_posisi',
                'type'  => 'tunjangan_posisi_periode'
            ],
            [
                'name'  => 'tunjangan_kemahalan_id',
                'table' => 'm_tunj_kemahalan',
                'type'  => 'tunjangan_kemahalan_periode'
            ],
            [
                'name'  => 'uang_makan',
                'type'  => 'uang_makan'
            ],
            [
                'name'  => 'tunjangan_tetap',
                'type'  => 'tunjangan_tetap'
            ]
        ];

        foreach($defaultColumns as $idx => $key ){
            $defaultColumns[$idx]['label']  = $this->helper->snakeCaseToCapitalize($key['name']);
            $defaultColumns[$idx]['factor'] = '+'; // pasti tambah karena default kolom tunjangan

            // dynamic 
            if(@!$key['table']) {
                    $defaultColumns[$idx]['value']  = (float)$standart_gaji[$key['name']] ?? 0;
                    $defaultColumns[$idx]['type']   = $standart_gaji[$key['type']];
              
            }else{
                if($key['table'] == 'm_tunj_kemahalan'){
                    $defaultColumns[$idx]['label']  = "Tunjangan Kemahalan";
                    $defaultColumns[$idx]['value']  = (float)\DB::table($key['table'])->where('id', $standart_gaji[$key['name']])->pluck('besaran')->first() ?? 0;
                    $defaultColumns[$idx]['type']   = $standart_gaji[$key['type']];
                }
            }
            $defaultColumns[$idx]['can_adjust'] = 1;
            if ($defaultColumns[$idx]['value'] == 0) {
                unset($defaultColumns[$idx]);
            }
        }

        // faktor lain dari table m_standart_gaji_det
        $standart_gaji_det = m_standart_gaji_det::where('m_standart_gaji_id', $standart_gaji->id ?? 0)->get();
        foreach($standart_gaji_det as $d){
            $defaultColumns[] = [
                'label'    => $d->komponen,
                'factor'   => $d->faktor,
                'value'    => $d->nilai,
                'type'     => $d->periode,
                'can_adjust' => 1
                
            ];
        }

        if(!$kary) return $defaultColumns;

        // tunjangan masa kerja
        $general_masa_kerja = m_general::where('group', 'TUNJANGAN MASA KERJA')->where('key','01')->pluck('value')->first();
        if($general_masa_kerja && $kary->tgl_masuk) {
            $general_masa_kerja = (float)$general_masa_kerja;
            $date_from = \DateTime::createFromFormat('Y-m-d', $kary->tgl_masuk);
            $date_to = \DateTime::createFromFormat('Y-m-d', date('Y-m-d'));
            $interval = @$date_from->diff($date_to) ?? 0;
            $jumlah_tahun = floor($interval->days / 365);

            $total_tunjangan = $general_masa_kerja * pow(2, $jumlah_tahun);
            if($total_tunjangan > 0){
                $defaultColumns[] = [
                    'label'    => "Tunjangan Masa Kerja ($jumlah_tahun)",
                    'factor'   => '+',
                    'value'    => $total_tunjangan,
                    'type'     => 'Bulanan',
                    'can_adjust' => 1
                ];
            }
        }

        // faktor lain :Potongan
        $t_potongan = t_potongan::where('m_kary_id', @$kary->id ?? 0)->orWhere('is_all_kary', true)->whereRaw("date_from >= ? and date_to <= ?",[$firstDayOfMonth,$lastDayOfMonth])->get();
        if(count($t_potongan)) {
            foreach($t_potongan as $d){
                $nilai_netto = ((float)$d->nilai * (float)$d->percentage)/100;
                $defaultColumns[] = [
                    'label'    => "Potongan - $d->nomor",
                    'factor'   => '-',
                    'value'    => $nilai_netto,
                    'type'     => 'Bulanan',
                    'can_adjust' => 1,
                    't_potongan_id' => $d->id
                ];
            }
        }

        // check kehadiran karyawan
        $attendance = \DB::select("select public.employee_attendance(?,?)",[$firstDayOfMonth,@$kary->id ?? 0]);
        if(count($attendance)){
            $att = $attendance[0]->employee_attendance;
            $att = json_decode($att);

            $jml_hari_sebulan = $att->work_days_in_month;
            $tidak_masuk_kerja = $att->work_not_present;
            $cuti_reguler = @$att->cuti_reguler;
            $sisa_cuti_reguler = @$att->sisa_cuti_reguler;
            $sisa_cuti_masa_kerja = @$att->sisa_cuti_masa_kerja;
            $potongan_cuti = @$att->potongan_cuti;
            $sisa_cuti = @$sisa_cuti_reguler+$sisa_cuti_masa_kerja;

            // gaji perhari
            $gaji_per_hari = 0;
            $makan_per_hari = 0;
            $gaji_pokok = @$standart_gaji['gaji_pokok'] ?? 0;
            if($gaji_pokok){
                $gaji_per_hari = $gaji_pokok / $jml_hari_sebulan;
            }
            $standart_gaji = @$standart_gaji['uang_makan'];
            if($standart_gaji){
                $makan_per_hari = $standart_gaji / $jml_hari_sebulan;
            }

            // potongan tidak hadir dan jatah semua cuti sudah habis
            if(($sisa_cuti-$tidak_masuk_kerja) < 0){
                $sisa_cuti -= $tidak_masuk_kerja;
                $value = $gaji_per_hari*$tidak_masuk_kerja;
                $defaultColumns[] = [
                    'label'    => "Potongan Tidak Masuk Kerja ($tidak_masuk_kerja)",
                    'factor'   => '-',
                    'value'    => $value,
                    'type'     => 'Bulanan',
                    'can_adjust' => 1
                ];
            }
            

            // ketika jatah cuti reguler masih ada -> potong uang makan
            if($sisa_cuti > 0 && $potongan_cuti > 0){
                $value = $makan_per_hari*$potongan_cuti;
                $defaultColumns[] = [
                    'label'    => "Potongan Uang Makan Cuti ($potongan_cuti)",
                    'factor'   => '-',
                    'value'    => $value,
                    'type'     => 'Bulanan',
                    'can_adjust' => 1
                ];
            }

            // ketika jatah cuti reguler tidak ada -> potong gaji 
            if($sisa_cuti <= 0 && $potongan_cuti > 0){
                $value = $gaji_per_hari*$potongan_cuti;
                $defaultColumns[] = [
                    'label'    => "Potongan Cuti ($potongan_cuti)",
                    'factor'   => '-',
                    'value'    => $value,
                    'type'     => 'Bulanan',
                    'can_adjust' => 1
                ];
            }

          
        }
        

        // faktor lain :Cuti
        // $t_cuti = t_cuti::where('m_kary_id', @$kary->id ?? 0)->whereRaw("status = 'APPROVED' and date_from >= ? and date_to <= ?",[$firstDayOfMonth,$lastDayOfMonth])->get();
        // if(count($t_cuti)) {
        //     $sisa_cuti = m_kary::where('id', @$kary->id ?? 0)->pluck('cuti_sisa_reguler')->first() ?? 0;
        //     $count = t_cuti::where('m_kary_id', @$kary->id ?? 0)->whereRaw("attachment is not null and status = 'APPROVED' and date_from >= ? and date_to <= ?",[$firstDayOfMonth,$lastDayOfMonth])->count();
        //     foreach($t_cuti as $d){
        //         $date_from = \DateTime::createFromFormat('Y-m-d', $d->date_from);
        //         $date_to = \DateTime::createFromFormat('Y-m-d', $d->date_to);
        //         $interval = @$date_from->diff($date_to) ?? 0;
        //         $jumlah_hari = $interval->days;
        //         if($sisa_cuti > 0){
        //             $jumlah_hari = $jumlah_hari - $sisa_cuti;
        //         }

        //         $gaji_per_hari = 0;
        //         $makan_per_hari = 0;
        //         $gaji_pokok = @$standart_gaji['gaji_pokok'] ?? 0;
        //         if($gaji_pokok){
        //             $gaji_per_hari = $gaji_pokok / (int)date('t');
        //         }
        //         $makan_per_hari = @$standart_gaji['uang_makan'] / (int)date('t');
        //         $potongan_cuti = $gaji_per_hari*$jumlah_hari;
        //         $potongan_makan = $makan_per_hari * $jumlah_hari;

        //         if($count > 7){
        //             $defaultColumns[] = [
        //                 'label'    => "Potongan Cuti ($jumlah_hari)",
        //                 'factor'   => '-',
        //                 'value'    => $potongan_cuti,
        //                 'type'     => 'Bulanan',
        //                 'can_adjust' => 1,
        //                 't_cuti_id' => $d->id
        //             ];
                    
        //         }else{
        //             $defaultColumns[] = [
        //                 'label'    => "Potongan Cuti (Uang Makan) ($jumlah_hari)",
        //                 'factor'   => '-',
        //                 'value'    => $potongan_cuti,
        //                 'type'     => 'Bulanan',
        //                 'can_adjust' => 1,
        //                 't_cuti_id' => $d->id
        //             ];
        //         }
                
        //     }
        // }

        return $defaultColumns;
    }

    private function countPPH21($kary, $netto = 0)
    {
        $getBasicSalary = [];
        // pengurangan dari perhitungan pph21
        // ------------------------- contoh perhitungan ---------------------------
        // Penghasilan Neto dalam setahun Rp9.400.000 x 12	    = Rp112.800.000
        // PTKP Status Lajang	                                = Rp54.000.000 (-)
        // Pendapatan Kena Pajak (PKP):	
        // PKP setahun Rp112.800.000 – Rp54.000.000	            = Rp58.800.000

        $tanggungan = m_general::find($kary->tanggungan_id);
        if($tanggungan){

            // persentase pajak <= Rp50.000.000                 = 5%
            // persentase pajak > Rp50.000.000  – Rp250.000.000 = 15%
            // persentase pajak > Rp250.000.000 – Rp500.000.000 = 25%
            // persentase pajak > Rp250.000.000 – Rp500.000.000 = 30%

            $nilaiTanggungan = @$tanggungan->value_2 ?? 0;
            $nettoYear = $netto*12;
            $nettoPTKP = $nettoYear-$nilaiTanggungan;

            // hentikan fungsi ketika gaji masih dibawah jumlah tanggungan 
            if($nettoPTKP <= 0) return $getBasicSalary;

            $percent   = 0;
            if($nettoPTKP <= 50000000){
                $before_value = 0;
                $before_percent = $percent;
                $percent = 5;
            }elseif(
                $nettoPTKP > 50000000 
                && $nettoPTKP <= 250000000
            ){
                $before_value = 50000000;
                $before_percent = $percent;
                $percent = 15;
        
            }elseif($nettoPTKP > 250000000 && $nettoPTKP <= 500000000){ 
                $before_value = 250000000;
                $before_percent = $percent;
                $percent = 25;
              
            }elseif($nettoPTKP > 500000000){
                $before_value = 500000000;
                $before_percent = $percent;
                $percent = 30;
            }
            $getBasicSalary = $this->countTaxDetail(
                $tanggungan, 
                $nettoPTKP, 
                $before_value, 
                $before_percent, 
                $percent, 
                $getBasicSalary
            );
        }
        return $getBasicSalary;
    }

    private function countTaxDetail(
        $tanggungan, 
        $nettoPTKP, 
        $before_value, 
        $before_percent, 
        $percent, 
        $mergingArr
    )
    {
        $outstanding = $nettoPTKP-$before_value;
        $tax1 = $before_percent*$before_value/100;
        $tax2 = $percent*$outstanding/100;
        $total_tax = $tax1+$tax2;
        // insert dari kondisi gaji sebelumnya sebelumnya
        // ex: 5% x 50.000.000
        // ex: 15% x 800.0000
        $detail = [];
        if($before_percent != 0){
            // jika netto / before value memiliki sisa
            $detail = [
                [
                    'label'    => "$before_percent% x $before_value",
                    'factor'   => '+',
                    'value'    => $tax1,
                    'type'     => 'Tahunan'
                ],
                [
                    'label'    => "$percent% x $outstanding",
                    'factor'   => '+',
                    'value'    => $tax2,
                    'type'     => 'Tahunan'
                ]
            ];
        }else{
            // jika netto / before value tidak memiliki sisa (konidisi pertama)
            $detail = [
                [
                    'label'    => "$percent% x $nettoPTKP",
                    'factor'   => '+',
                    'value'    => $tax2,
                    'type'     => 'Tahunan',
                ]
            ];
        }

        $mergingArr[] = [
            'label'    => "PTKP $tanggungan->value (perbulan)",
            'factor'   => '-',
            'value'    => $total_tax/12,
            'type'     => 'Bulanan',
            'can_adjust' => 0,
            'detail'   => $detail
        ];
        return $mergingArr;
    }

    private function summarySubSalary($arrConfig) 
    {   
        return array_reduce($arrConfig, function ($carry, $item) {
            if(is_numeric($item['value'])){
                $value = (float)$item['value'];
                if($value != 0){
                    if($item['factor'] == '+'){
                        $carry = $carry + $item['value'];
                    }elseif($item['factor'] == '-'){
                        $carry = $carry - $item['value'];
                    }
                }
            }
            return $carry;
        }, 0);
    }



    public function salaryOfKary($id, $periode = null)
    {
        try{
            $m_kary_id = $id;
            $kary = m_kary::find($m_kary_id);
            if(!@$kary->m_standart_gaji_id) return [
                'm_kary_id'  => $m_kary_id,
                'total_gaji' => 0,
                'total_tax'  => 0,
                'netto'      => 0,
                'detail'     => []
            ];
            $m_standart_gaji = m_standart_gaji::find($kary->m_standart_gaji_id);

            // default summary salary
            $getBasicSalary = $this->factorSalary($m_standart_gaji, $kary, $periode);
            $netto          = $this->summarySubSalary($getBasicSalary);
            $getBasicSalary    = array_merge($getBasicSalary, [
                [
                    'label'    => 'Total Gaji',
                    'factor'   => '=',
                    'value'    => $netto,
                    'type'     => '-'
                ]
            ]);

            $nettoFinish    = $this->summarySubSalary($getBasicSalary);

            // default summary tax
            $arrPPH         = $this->countPPH21($kary, $netto);
            $totalTax = @$arrPPH[0]['value'];
            if(count($arrPPH)){
                $getBasicSalary = array_merge($getBasicSalary, $arrPPH);
                $nettoFinish    = $this->summarySubSalary($getBasicSalary);
                $getBasicSalary    = array_merge($getBasicSalary, [
                    [
                        'label'    => 'Total Gaji (Setelah PPH 21)',
                        'factor'   => '=',
                        'value'    => $nettoFinish,
                        'type'     => '-'
                    ]
                ]);
            }

            return [
                'm_kary_id'  => $m_kary_id,
                'total_gaji' => $netto,
                'total_tax'  => $totalTax,
                'netto'      => $nettoFinish,
                'detail'     => $getBasicSalary
            ];
        } catch (\Exception $e) {
            return $this->helper->responseCatch($e);
        }
    }

    public function generateSalary()
    {
        try{
            $req =  app()->request;
            $kary = m_kary::selectRaw("m_kary.*,m_general.value periode_text, m_dir.nama dir, m_divisi.nama divisi, m_dept.nama dept")
                ->leftJoin('m_dir','m_dir.id','m_kary.m_dir_id')
                ->leftJoin('m_divisi','m_divisi.id','m_kary.m_divisi_id')
                ->leftJoin('m_dept','m_dept.id','m_kary.m_divisi_id')
                ->join('m_general','m_general.id','m_kary.periode_gaji_id')
                ->whereRaw('m_kary.m_standart_gaji_id in(select s.id from m_standart_gaji s where s.is_active = true)');

            if($req->m_dept_id) $kary = $kary->where('m_kary.m_dept_id', $req->m_dept_id);
            if($req->m_divisi_id) $kary = $kary->where('m_kary.m_divisi_id', $req->m_divisi_id);

            $kary = $kary->get();
            
            $date_from = \DateTime::createFromFormat('Y-m-d', $req->periode_awal.'-20');
            $date_to = \DateTime::createFromFormat('Y-m-d', $req->periode_akhir.'-20');

            // Menghitung jumlah bulan antara tanggal_awal dan tanggal_akhir
            $interval = $date_from->diff($date_to);
            $jumlah_bulan = (($interval->y) * 12) + ($interval->m);


            $data = [];
            for ($i = 0; $i <= $jumlah_bulan; $i++) {

                $date = $date_from->format('Y-m-d');
                foreach($kary as $key)
                {
                    $gaji = $this->salaryOfKary($key->id, $date_from->format('Y-m'));
                    $data[] = [
                        'm_kary_id'         => $key->id,
                        'm_kary.nik'        => $key->nik,
                        'm_kary_dir_id'     => $key->m_dir_id,
                        'm_kary_dir.nama'   => $key->dir,
                        'm_kary_divisi_id'  => $key->m_divisi_id,
                        'm_kary_divisi.nama'=> $key->divisi,
                        'm_kary_dept_id'    => $key->m_dept_id,
                        'm_kary_dept.nama'  => $key->dept,
                        'nik'               => $key->nik,
                        'nama_lengkap'      => $key->nama_lengkap,
                        'periode'           => $date_from->format('d-m-Y'),
                        'periode_in_date'   => $date,
                        'periode_id'        => $key->periode_gaji_id,
                        'periode_text'      => $key->periode_text,
                        'total_tax'         => $gaji['total_tax'] ?? 0,
                        'total_gaji'        => $gaji['total_gaji'],
                        'netto'             => $gaji['netto'],
                        'detail_gaji'       => $gaji['detail'],
                    ];
                }

                // Menambahkan satu bulan untuk iterasi berikutnya
                $date_from->add(new \DateInterval('P1M'));
            }
            

            return $this->helper->customResponse('OK', 200, $data);
        } catch (\Exception $e) {
            return $this->helper->responseCatch($e);
        }
    }

    public function public_generate()
    {
        $data =  $this->salaryOfKary(app()->request->id ?? 8, '2024-03');
       
        return response(['msg'=>$data]);
    }

    public function custom_generate()
    {
        return $this->generateSalary();
    }

    public function custom_generatePPH($req)
    {
        $netto = $req->netto;
        $kary = m_kary::find($req->m_kary_id);
        return $this->countPPH21($kary, $netto);
    }

    public function custom_save($req)
    {
        $counter = count($req->detail);
        if($counter){
            $nomor = $this->helper->generateNomor('KODE PERHITUNGAN GAJI');
            foreach($req->detail as $key){
                $checkAndDelete = $this->where('m_kary_id', @$key['m_kary_id'])->where('periode', @$key['periode'])->delete();
                $key['nomor'] =  $nomor;
                $key['detail_gaji'] = json_encode($key['detail_gaji']);
                $hdr = $this->create($key);
            }
        }
        return $this->helper->customResponse("$counter Data berhasil disimpan");
    }
    
       
    public function scopeGenerateForFinal($model)
    {
        $req = app()->request;
        $date_from = \DateTime::createFromFormat('Y-m-d', $req->periode_awal.'-01') ?? null;
        $date_to = \DateTime::createFromFormat('Y-m-d', $req->periode_akhir.'-30') ?? null;

        $model = $model->whereBetween('periode_in_date', [$date_from,$date_to]);
        if($req->m_divisi_id) $model = $model->where('t_perhitungan_gaji.m_kary_divisi_id', $req->m_divisi_id);
        if($req->m_dept_id) $model = $model->where('t_perhitungan_gaji.m_kary_dept_id', $req->m_dept_id);

        return $model;
    }
}