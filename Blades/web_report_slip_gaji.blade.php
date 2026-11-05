@php
  $req = app()->request;
  $periode_from = $req->periode_from ?? '2023-01-01';
  $periode_to = $req->periode_to ?? '2023-12-30';
  $raw = \DB::select("
    select f.periode_awal, f.nomor nomor_gaji, f.periode_akhir, f.desc,k.nama_lengkap, k.nik,kd.nama dir, kdi.nama divisi, kde.nama dept, r.* from t_final_gaji_det_rincian r
    join t_final_gaji_det d on d.id = r.t_final_gaji_det_id 
    join m_kary k on k.id = d.m_kary_id 
    join t_final_gaji f on f.id = d.t_final_gaji_id 
    left join m_dir kd on kd.id = k.m_dir_id 
    left join m_divisi kdi on kdi.id = k.m_divisi_id 
    left join m_dept kde on kde.id = k.m_dept_id  
    where d.m_kary_id = ? and f.id = coalesce(?,f.id) -- f.id ini nanti bisa milih final gaji yg mana
    order by r.seq ;
  ", [ $req->m_kary_id, $req->f_id ]);
    function formatRupiah($number) {
    return 'Rp ' . number_format($number, 0, ',', '.');
    }

@endphp
@if(count($raw))
<span style="width:700px;text-align:center;font-weight:bold;"> Slip Gaji </span><br/>
<br/>
  <table style="border-collapse: collapse; width: 100%; ">
  <tr>
    <td style="width: 50%; float: left;">
      <table style="border-collapse: collapse; width: 100%; font-size: 10px;">
        <tr>
          <td style="width: 25%; font-size: 10pt">NIK</td>
          <td style="font-size: 10pt">: {{ $raw[0]->nik }} </td>
        </tr>
        <tr>
          <td style="font-size: 10pt">Karyawan</td>
          <td style="font-size: 10pt">:  {{ $raw[0]->nama_lengkap }}</td>
        </tr>
        <tr>
          <td style="font-size: 10pt">Direktorat</td>
          <td style="font-size: 10pt">:  {{ $raw[0]->dir }}</td>
        </tr>
        <tr>
          <td style="font-size: 10pt">Divisi</td>
          <td style="font-size: 10pt">:  {{ $raw[0]->divisi }}</td>
        </tr>
        <tr>
          <td style="font-size: 10pt">Departemen</td>
          <td style="font-size: 10pt">:  {{ $raw[0]->dept }}</td>
        </tr>
      </table>
    </td>
    <td style="width: 50%; float: left;">
      <table style="border-collapse: collapse; width: 100%; font-size: 10px;">
        <tr>
          <td style="width: 25%; font-size: 10pt">Nomor Gaji</td>
          <td>:  {{ $raw[0]->nomor_gaji }}</td>
        </tr>
        <tr>
          <td style="font-size: 10pt">Catatan</td>
          <td>:  {{ $raw[0]->desc }}</td>
        </tr>
      </table>
    </td>
  </tr>
</table>
<br/>
<table width="700px" style="font-size:10px; border: none;" cellpadding="1" id="lastTable">
  <thead style="font-weight:semibold;">
    <tr style="border: none;">
      <td style="font-weight: bold; line-height: 20px;text-align:center; background-color: #c8daf8;">Komponen</td>
      <td style="font-weight: bold; line-height: 20px;text-align:center; background-color: #c8daf8;">Nilai</td>
      <td style="font-weight: bold; line-height: 20px;text-align:center; background-color: #c8daf8;">Catatan</td>
    </tr>
  </thead>
  <tbody>
    @foreach($raw as $i => $d)
    @php
        $backgroundColor = $i % 2 === 1 ? '#f8f8f8' : ''; 
    @endphp
    <tr style="background-color: {{$backgroundColor}}">
      <td style="line-height: 20px;text-align:left;  {{ $d->factor == '=' ? 'font-weight:bold' : '' }}">{{ $d->label }}</td>
      <td style="line-height: 20px;text-align:right; {{ $d->factor == '=' ? 'font-weight:bold' : '' }}" >{{ ($d->factor == '-' ? '(-)' : '').formatRupiah($d->value) }}</td>
      <td style="line-height: 20px;text-align:left;  {{ $d->factor == '=' ? 'font-weight:bold' : '' }}">{{ $d->deskripsi }}</td>
    </tr>
    <!-- <tr style="background-color: {{$backgroundColor}}">
      <td style="border:0.5px solid black;text-align:left;  {{ $d->factor == '=' ? 'background-color:#95a5a6;font-weight:bold' : '' }}">{{ $d->label }}</td>
      <td style="border:0.5px solid black;text-align:right; {{ $d->factor == '=' ? 'background-color:#95a5a6;font-weight:bold' : '' }}" >{{ ($d->factor == '-' ? '(-)' : '').formatRupiah($d->value) }}</td>
      <td style="border:0.5px solid black;text-align:left;  {{ $d->factor == '=' ? 'background-color:#95a5a6;font-weight:bold' : '' }}">{{ $d->deskripsi }}</td>
    </tr> -->
    @endforeach
  </tbody>
</table>
@endif