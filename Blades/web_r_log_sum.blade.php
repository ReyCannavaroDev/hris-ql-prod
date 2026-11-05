@php
$req = app()->request;
$date_now = date('d/m/Y H:i:s');
@endphp

<table>
  <tr><td style="height:20px" colspan="2"></td></tr>
  <tr>
    <td style="font-size:13px;text-align:center;font-weight:bold;" colspan="2">LAPORAN LOGBOOK SUMMARY</td>
  </tr>
</table>
<table style="font-size:8px; width:100%">
  <tr><td style="height:20px"></td></tr>
  <tr>
    <td style="left:20px;line-height:1;">{{$date_now}}</td>
  </tr>
  <tr><td style="height:5px important"></td></tr>
</table>
<table class="table-border" style="font-size:7px;width:100%" cellpadding="2">
  <thead>
    <tr>
      <th style="text-align:center;width:3%;vertical-align:middle;font-weight:bold;border-bottom: 1px solid black;border-top: 1px solid black;">No</th>
      <th style="text-align:center;width:34.25%;vertical-align:middle;font-weight:bold;border-bottom: 1px solid black;border-top: 1px solid black;">Karyawan</th>
      <th style="text-align:center;width:34.25%;vertical-align:middle;font-weight:bold;border-bottom: 1px solid black;border-top: 1px solid black;">Project</th>
      <th style="text-align:center;width:14.25%;vertical-align:middle;font-weight:bold;border-bottom: 1px solid black;border-top: 1px solid black;">Jumlah Task</th>
      <th style="text-align:center;width:14.25%;vertical-align:middle;font-weight:bold;border-bottom: 1px solid black;border-top: 1px solid black;">Jumlah Task Selesai</th>
    </tr>
  </thead>
  <tbody>
    <tr>
        <td style="text-align:center;width:3%;line-height:1.5;">1</td>
        <td style="text-align:left;width:34.25%;line-height:1.5;">Hafiz Dar Dere Anwar</td>
        <td style="text-align:left;width:34.25%;line-height:1.5;">KSP Wonokoyo</td>
        <td style="text-align:right;width:14.25%;line-height:1.5;">100</td>
        <td style="text-align:right;width:14.25%;line-height:1.5;">99</td>
    </tr>
    <tr>
        <td style="text-align:center;width:3%;line-height:1.5;">2</td>
        <td style="text-align:left;width:34.25%;line-height:1.5;">Traviz Dar Dere Anwar</td>
        <td style="text-align:left;width:34.25%;line-height:1.5;">TIA</td>
        <td style="text-align:right;width:14.25%;line-height:1.5;">1</td>
        <td style="text-align:right;width:14.25%;line-height:1.5;">1</td>
    </tr>
  </tbody>
</table>