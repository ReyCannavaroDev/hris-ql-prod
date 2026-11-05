@php
  $req = app()->request;
  $periode_from = $req->periode_from ?? date('Y-m').'-01';
  $periode_to = $req->periode_to ?? date('Y-m-d');
  $raw = \DB::select("
    select 
    tl.tanggal, tl.jam_mulai, tl.jam_selesai, tl.nomor,tl.keterangan,tl.status,tl.no_doc, k.kode nik, k.nama_lengkap, kd.nama dir, kdi.nama div,kde.nama dept, mg.value tipe_lembur, mg1.value alasan  
    from t_lembur tl
      join m_kary k on k.id = tl.m_kary_id 
      left join m_general mg on mg.id = tl.tipe_lembur_id
      left join m_general mg1 on mg1.id = tl.alasan_id
      left join m_dir kd on kd.id = k.m_dir_id 
      left join m_divisi kdi on kdi.id = k.m_divisi_id 
      left join m_dept kde on kde.id = k.m_dept_id 
      where tl.tanggal BETWEEN ? AND ? 
      and case when kd.id is not null then kd.id = coalesce(?,kd.id) else true end
      and case when kdi.id is not null then kdi.id = coalesce(?,kdi.id) else true end
      and case when kde.id is not null then kde.id = coalesce(?,kde.id) else true end
      and case when k.m_posisi_id is not null then k.m_posisi_id = coalesce(?,k.m_posisi_id) else true end
      and case when tl.tipe_lembur_id is not null then tl.tipe_lembur_id = coalesce(?,tl.tipe_lembur_id) else true end
      and case when k.id is not null then k.id =  coalesce(?,k.id) else true end
      and tl.status = 'APPROVED'
  order by tl.id
", [ $periode_from, $periode_to, $req->m_dir_id, $req->m_divisi_id, $req->m_dept_id, $req->m_posisi_id, $req->tipe_lembur_id, $req->m_kary_id ]);
@endphp
<span style="width:100%;text-align:center;font-weight:bold;"> Surat Perintah Lembur </span><br/>
@php
  $periode_from = date('d-m-Y', strtotime($periode_from));
  $periode_to = date('d-m-Y', strtotime($periode_to));
@endphp
<span style="width:100%;text-align:center; font-size:10pt"> {{ $periode_from }} - {{ $periode_to }}</span><br/>
<br/>
<table width="100%" style="font-size:8px;" cellpadding="2">
  <thead style="font-weight:semibold;">
    <tr style="">
      <td style="border:0.5px solid black; font-weight: bold; line-height: 20px;text-align:center; background-color: #c6c6c6;">No</td>
      <td style="border:0.5px solid black; font-weight: bold; line-height: 20px;text-align:center; background-color: #c6c6c6;">Tanggal</td>
      <td style="border:0.5px solid black; font-weight: bold; line-height: 20px;text-align:center; background-color: #c6c6c6;">No Lembur</td>
      <td style="border:0.5px solid black; font-weight: bold; line-height: 20px;text-align:center; background-color: #c6c6c6;">NIK</td>
      <td style="border:0.5px solid black; font-weight: bold; line-height: 20px;text-align:center; background-color: #c6c6c6;">Nama Karyawan</td>
      <td style="border:0.5px solid black; font-weight: bold; line-height: 20px;text-align:center; background-color: #c6c6c6;">Direktorat</td>
      <td style="border:0.5px solid black; font-weight: bold; line-height: 20px;text-align:center; background-color: #c6c6c6;">Divisi</td>
      <td style="border:0.5px solid black; font-weight: bold; line-height: 20px;text-align:center; background-color: #c6c6c6;">Departemen</td>
      <td style="border:0.5px solid black; font-weight: bold; line-height: 20px;text-align:center; background-color: #c6c6c6;">Jam Mulai</td>
      <td style="border:0.5px solid black; font-weight: bold; line-height: 20px;text-align:center; background-color: #c6c6c6;">Jam Selesai</td>
      <td style="border:0.5px solid black; font-weight: bold; line-height: 20px;text-align:center; background-color: #c6c6c6;">Tipe Lembur</td>
      <td style="border:0.5px solid black; font-weight: bold; line-height: 20px;text-align:center; background-color: #c6c6c6;">Alasan</td>
      <td style="border:0.5px solid black; font-weight: bold; line-height: 20px;text-align:center; background-color: #c6c6c6;">No Dokumen</td>
      <td style="border:0.5px solid black; font-weight: bold; line-height: 20px;text-align:center; background-color: #c6c6c6;">Keterangan</td>
      <td style="border:0.5px solid black; font-weight: bold; line-height: 20px;text-align:center; background-color: #c6c6c6;">Status</td>
    </tr>
  </thead>
  <tbody>
    @foreach($raw as $i => $d)
    @php
        $backgroundColor = $i % 2 === 1 ? '#f8f8f8' : ''; // Logika untuk menentukan warna latar belakang
    @endphp
    <tr style="background-color: {{$backgroundColor}}">
      <td style="border:0.5px solid black;text-align:center;">{{ $i+1 }}</td>
      <td style="border:0.5px solid black;text-align:left;">{{ $d->tanggal }}</td>
      <td style="border:0.5px solid black;text-align:left;">{{ $d->nomor }}</td>
      <td style="border:0.5px solid black;text-align:left;">{{ $d->nik }}</td>
      <td style="border:0.5px solid black;text-align:left;">{{ $d->nama_lengkap }}</td>
      <td style="border:0.5px solid black;text-align:left;">{{ $d->dir }}</td>
      <td style="border:0.5px solid black;text-align:left;">{{ $d->div }}</td>
      <td style="border:0.5px solid black;text-align:left;">{{ $d->dept }}</td>
      <td style="border:0.5px solid black;text-align:left;">{{ $d->jam_mulai }}</td>
      <td style="border:0.5px solid black;text-align:left;">{{ $d->jam_selesai }}</td>
      <td style="border:0.5px solid black;text-align:left;">{{ $d->tipe_lembur }}</td>
      <td style="border:0.5px solid black;text-align:left;">{{ $d->alasan }}</td>
      <td style="border:0.5px solid black;text-align:left;">{{ $d->no_doc }}</td>
      <td style="border:0.5px solid black;text-align:left;">{{ $d->keterangan }}</td>
      <td style="border:0.5px solid black;text-align:left;">{{ $d->status }}</td>
    </tr>
    @endforeach
  </tbody>
</table>  