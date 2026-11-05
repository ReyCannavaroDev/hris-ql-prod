@php
  $req = app()->request;
  $periode = $req->periode;

  $now =  $periode.'-'.date('d');

  $additional = "";

  if($periode){
    $additional = "  and case when k.tgl_berhenti is not null then to_char(k.tgl_berhenti, 'yyyy-mm') != '$periode' else true end
      and case when k.tgl_masuk is not null then to_char(k.tgl_masuk, 'yyyy-mm') = '$periode' end";
  }

  $raw = \DB::select("
   select 
  k.kode nik, k.nik no_ktp,k.nama_lengkap, a.nama_lengkap atasan, d.nama dir, dv.nama divisi, dp.nama dept, p.desc_kerja posisi, k.tgl_masuk, k.tgl_berhenti 
  from m_kary k 
  left join m_kary a on a.id = k.atasan_id 
  left join m_dir d on d.id = k.m_dir_id 
  left join m_divisi dv on dv.id = k.m_divisi_id 
  left join m_dept dp on dp.id = k.m_dept_id 
  left join m_posisi p on p.id = k.m_posisi_id
  where k.is_active = true 
    and case when  k.m_dir_id is not null then k.m_dir_id = coalesce(?,k.m_dir_id) end
    and k.m_divisi_id = coalesce(?,k.m_divisi_id) 
    and k.m_dept_id = coalesce(?,k.m_dept_id) 
    <!-- and case when k.m_posisi_id is not null then k.m_posisi_id = coalesce(?,k.m_posisi_id) end -->
    $additional
    order by k.tgl_masuk
", [ $req->m_dir_id, $req->m_divisi_id, $req->m_dept_id, $req->m_posisi_id]);
@endphp

<span style="width:100%;text-align:center;font-weight:bold;"> Karyawan Aktif </span><br/>
<br/>
<table width="100%" style="font-size: 8px;" cellpadding="2">
  <thead style="font-weight:semibold;">
    <tr style="">
      <td style="padding-right: 5px; padding-left: 7px; border:0.5px solid black; font-weight: bold; line-height: 20px;text-align:center; background-color: #c6c6c6;">No</td>
      <td style="padding-right: 5px; padding-left: 7px; border:0.5px solid black; font-weight: bold; line-height: 20px;text-align:center; background-color: #c6c6c6;">NIK</td>
      <td style="padding-right: 5px; padding-left: 7px; border:0.5px solid black; font-weight: bold; line-height: 20px;text-align:center; background-color: #c6c6c6;">No KTP</td>
      <td style="padding-right: 5px; padding-left: 7px; border:0.5px solid black; font-weight: bold; line-height: 20px;text-align:center; background-color: #c6c6c6;">Nama Lengkap</td>
      <td style="padding-right: 5px; padding-left: 7px; border:0.5px solid black; font-weight: bold; line-height: 20px;text-align:center; background-color: #c6c6c6;">Atasan</td>
      <td style="padding-right: 5px; padding-left: 7px; border:0.5px solid black; font-weight: bold; line-height: 20px;text-align:center; background-color: #c6c6c6;">Direktorat</td>
      <td style="padding-right: 5px; padding-left: 7px; border:0.5px solid black; font-weight: bold; line-height: 20px;text-align:center; background-color: #c6c6c6;">Divisi</td>
      <td style="padding-right: 5px; padding-left: 7px; border:0.5px solid black; font-weight: bold; line-height: 20px;text-align:center; background-color: #c6c6c6;">Departemen</td>
      <td style="padding-right: 5px; padding-left: 7px; border:0.5px solid black; font-weight: bold; line-height: 20px;text-align:center; background-color: #c6c6c6;">Posisi</td>
      <td style="padding-right: 5px; padding-left: 7px; border:0.5px solid black; font-weight: bold; line-height: 20px;text-align:center; background-color: #c6c6c6;">Tgl Bergabung</td>
    </tr>
  </thead>
  <tbody>
    @foreach($raw as $i => $d)
    @php
        $backgroundColor = $i % 2 === 1 ? '#f8f8f8' : ''; // Logika untuk menentukan warna latar belakang
    @endphp
    <tr style="background-color: {{$backgroundColor}}">
      <td style="padding-right: 5px; padding-left: 7px; border:0.5px solid black;text-align:center;">{{ $i+1 }}</td>
      <td style="padding-right: 5px; padding-left: 7px; border:0.5px solid black;text-align:left;">{{ $d->nik }}</td>
      <td style="padding-right: 5px; padding-left: 7px; border:0.5px solid black;text-align:left;">{{ $d->no_ktp }}</td>
      <td style="padding-right: 5px; padding-left: 7px; border:0.5px solid black;text-align:left;">{{ $d->nama_lengkap }}</td>
      <td style="padding-right: 5px; padding-left: 7px; border:0.5px solid black;text-align:left;">{{ $d->atasan }}</td>
      <td style="padding-right: 5px; padding-left: 7px; border:0.5px solid black;text-align:left;">{{ $d->dir }}</td>
      <td style="padding-right: 5px; padding-left: 7px; border:0.5px solid black;text-align:left;">{{ $d->divisi }}</td>
      <td style="padding-right: 5px; padding-left: 7px; border:0.5px solid black;text-align:left;">{{ $d->dept }}</td>
      <td style="padding-right: 5px; padding-left: 7px; border:0.5px solid black;text-align:left;">{{ $d->posisi }}</td>
      <td style="padding-right: 5px; padding-left: 7px; border:0.5px solid black;text-align:left;">{{ $d->tgl_masuk }}</td>
    </tr>
    @endforeach
  </tbody>
</table>  