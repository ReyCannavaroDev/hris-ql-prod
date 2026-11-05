@php
  $req = app()->request;
  $periode_from = $req->periode_from ?? '2023-01-01';
  $periode_to = $req->periode_to ?? '2023-12-30';
  $raw = \DB::select("
    select 
      k.nik, k.nama_lengkap, kd.nama dir, kdi.nama divisi, kde.nama dept, (fd.netto+fd.total_tax) total_gaji, fd.total_tax, fd.netto, fd.periode_in_date , fd.periode 
    from t_final_gaji_det fd
      join t_final_gaji f on fd.t_final_gaji_id = f.id
      join m_kary k on k.id = fd.m_kary_id 
      left join m_dir kd on kd.id = fd.m_kary_dir_id 
      left join m_divisi kdi on kdi.id = fd.m_kary_divisi_id 
      left join m_dept kde on kde.id = fd.m_kary_dept_id  
      where f.status = 'POSTED' 
      and f.periode_awal >= ? and f.periode_akhir <= ? 
      and case when kd.id is not null then kd.id = coalesce(?,kd.id) else true end
      and case when kdi.id is not null then kdi.id = coalesce(?,kdi.id) else true end
      and case when kde.id is not null then kde.id = coalesce(?,kde.id) else true end
      and case when k.m_posisi_id is not null then k.m_posisi_id = coalesce(?,k.m_posisi_id) else true end
      and case when k.id is not null then k.id = coalesce(?,k.id) else true end
  ", [ $periode_from, $periode_to, $req->m_dir_id, $req->m_divisi_id, $req->m_dept_id, $req->m_posisi_id, $req->m_kary_id ]);
@endphp
<span style="width:100%;text-align:center;font-weight:bold;"> Rekapitulasi Gaji </span><br/>

@php
  $periode_from = date('d-m-Y', strtotime($periode_from));
  $periode_to = date('d-m-Y', strtotime($periode_to));
@endphp

<span style="width:100%;text-align:center; font-size:10pt"> {{ $periode_from }} - {{ $periode_to }}</span><br/>
<br/>
<table width="100%" style="font-size:10px;" cellpadding="2">
  <thead style="font-weight:semibold;">
    <tr style="">
      <td style="border:0.5px solid black; font-weight: bold; line-height: 20px;text-align:center; background-color: #c6c6c6;">No</td>
      <td style="border:0.5px solid black; font-weight: bold; line-height: 20px;text-align:center; background-color: #c6c6c6;">Tanggal</td>
      <td style="border:0.5px solid black; font-weight: bold; line-height: 20px;text-align:center; background-color: #c6c6c6;">NIK</td>
      <td style="border:0.5px solid black; font-weight: bold; line-height: 20px;text-align:center; background-color: #c6c6c6;">Nama Karyawan</td>
      <td style="border:0.5px solid black; font-weight: bold; line-height: 20px;text-align:center; background-color: #c6c6c6;">Direktorat</td>
      <td style="border:0.5px solid black; font-weight: bold; line-height: 20px;text-align:center; background-color: #c6c6c6;">Divisi</td>
      <td style="border:0.5px solid black; font-weight: bold; line-height: 20px;text-align:center; background-color: #c6c6c6;">Departemen</td>
      <td style="border:0.5px solid black; font-weight: bold; line-height: 20px;text-align:center; background-color: #c6c6c6;">Gaji</td>
      <td style="border:0.5px solid black; font-weight: bold; line-height: 20px;text-align:center; background-color: #c6c6c6;">PPH</td>
      <td style="border:0.5px solid black; font-weight: bold; line-height: 20px;text-align:center; background-color: #c6c6c6;">Total Gaji</td>
    </tr>
  </thead>
  <tbody>
    @foreach($raw as $i => $d)
    @php
        $backgroundColor = $i % 2 === 1 ? '#f8f8f8' : '';
        $formatted_gaji = ($d->total_gaji != 0) ? number_format($d->total_gaji, 2, ',', '.') : $d->total_gaji ;
        $formatted_tax = ($d->total_tax != 0) ? number_format($d->total_tax, 2, ',', '.') : $d->total_tax;
        $formatted_netto = ($d->netto != 0) ? number_format($d->netto, 2, ',', '.') : $d->netto;
    @endphp
    <tr style="background-color: {{$backgroundColor}}">
      <td style="border:0.5px solid black;text-align:center;">{{ $i+1 }}</td>
      <td style="border:0.5px solid black;text-align:left;">{{ $d->periode }}</td>
      <td style="border:0.5px solid black;text-align:left;">{{ $d->nik }}</td>
      <td style="border:0.5px solid black;text-align:left;">{{ $d->nama_lengkap }}</td>
      <td style="border:0.5px solid black;text-align:left;">{{ $d->dir }}</td>
      <td style="border:0.5px solid black;text-align:left;">{{ $d->divisi }}</td>
      <td style="border:0.5px solid black;text-align:left;">{{ $d->dept }}</td>
      <td style="border:0.5px solid black;text-align:right;">{{ $formatted_gaji }}</td>
      <td style="border:0.5px solid black;text-align:right;">{{ $formatted_tax }}</td>
      <td style="border:0.5px solid black;text-align:right;">{{ $formatted_netto}}</td>
    </tr>
    @endforeach
  </tbody>
</table>