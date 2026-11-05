@php
  $req = app()->request;
  $date = $req->date;
  $dateEx = explode('/',$date);
  if(count($dateEx)){
    $date = @$dateEx[2] ? ($dateEx[2].'/'.$dateEx[1].'/'.$dateEx[0]) : $date;
  }
  $raw = \DB::select("
    select to_char(d.created_at, 'DD/MM/yyyy HH24:MI:SS') created_at, m.tanggal, k.nama_lengkap, d.lauk from presensi_maksi_det d 
    join m_kary k on k.id = d.m_kary_id 
    join presensi_maksi m on m.id = d.presensi_maksi_id 
    where m.tanggal = ? order by k.nama_lengkap
  ", [ $date ]);
  

  $laukspauks = [];

  foreach($raw as $d){
    $d->lauk = json_decode($d->lauk);

    foreach($d->lauk as $l){
        $detailLaukEx = explode(', ',$l->detail_text);
        foreach($detailLaukEx as $d){
          $laukspauks[] = $l->tipe_lauk.' | '.$d;
        }

    }

  }
  $summary = array_count_values($laukspauks);
  ksort($summary);

  function formatTanggalIndonesia($tanggal, $dataEx = null) {
      // Array untuk nama hari dan bulan dalam bahasa Indonesia
      $hari = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
      $bulan = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

      // Membuat objek DateTime dari string tanggal
      if($dataEx){
        $date = DateTime::createFromFormat('Y/m/d', $tanggal);
      }else{
        $date = DateTime::createFromFormat('Y-m-d', $tanggal);
      }

      // Menentukan hari dan bulan
      $namaHari = $hari[$date->format('N') - 1]; // N memberikan angka 1-7 untuk hari
      $namaBulan = $bulan[$date->format('n') - 1]; // n memberikan angka 1-12 untuk bulan

      // Mengembalikan format yang diinginkan
      return "{$namaHari}, {$date->format('d')} {$namaBulan} {$date->format('Y')}";
  }
@endphp
<span style="width:100%;text-align:center;font-weight:bold; font-size:14pt"> Rekap Pesan Makan Siang </span><br/>
<span style="width:100%;text-align:center; font-size:12pt"> {{ formatTanggalIndonesia($date, @$dateEx[2]) }}</span><br/>
<span style="width:100%;text-align:center; font-size:10pt"> {{ @$raw[0]->keterangan }}</span>
<span style="width:100%;text-align:center; font-size:10pt; font-style: italic">Print By. {{ @$req->infoprint ?? '-' }}</span><br/>
<br/>
<table width="50%" style="font-size:11px;" cellpadding="2">
  <thead style="font-weight:semibold;">
    <tr style="">
      <td style="border:0.5px solid black; line-height: 20px;text-align:center; width: 100%; font-size: 11px; font-weight: bold; background-color: #c6c6c6;" colspan="2">INFO TOTAL PESANAN</td>
    </tr>
    @foreach($summary as $item => $count)
    <tr style="">
      <td style="border:0.5px solid black; line-height: 20px;text-align:left; width: 90%; font-size: 11px;">{{$item}}</td>
      <td style="border:0.5px solid black; line-height: 20px;text-align:right; width: 10%; font-size: 11px;">{{$count}}</td>
    </tr>
    @endforeach
  </thead>
</table>  
<br/>
<br/>
<table width="100%" style="font-size:11px;" cellpadding="2">
  <thead style="font-weight:semibold;">
    <tr style="">
      <td style="border:0.5px solid black; font-weight: bold; line-height: 20px;text-align:center; background-color: #c6c6c6; width: 4%; font-size: 11px;">No</td>
      <td style="border:0.5px solid black; font-weight: bold; line-height: 20px;text-align:left; background-color: #c6c6c6; width: 25%; font-size: 11px;">Pemesan</td>
      <td style="border:0.5px solid black; font-weight: bold; line-height: 20px;text-align:left; background-color: #c6c6c6; width: 15%; font-size: 11px;">Dipesan Pada</td>
      <td style="border:0.5px solid black; font-weight: bold; line-height: 20px;text-align:center; background-color: #c6c6c6; width: 55%; font-size: 11px;">Detail</td>
    </tr>
  </thead>
  <tbody>
    @foreach($raw as $i => $d)
    <tr>
      <td style="border:0.5px solid black;text-align:center; width: 4%; font-size: 11px;">{{ $i+1 }}</td>
      <td style="border:0.5px solid black;text-align:left; width: 25%; font-size: 11px;">{{ $d->nama_lengkap }}</td>
      <td style="border:0.5px solid black;text-align:center; width: 15%; font-size: 10px;">{{ $d->created_at }}</td>
      <td style="border:0.5px solid black;text-align:left; width: 55%; font-size: 11px;">
        <ul>
          @foreach($d->lauk as $l)
            <li>{{ $l->tipe_lauk }} : {{ $l->detail_text }}</li>
          @endforeach
        </ul>
      </td>
    </tr>
    @endforeach
  </tbody>
</table>  