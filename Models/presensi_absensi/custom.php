<?php

namespace App\Models\CustomModels;
use Illuminate\Support\Facades\Validator;
use DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;


class presensi_absensi extends \App\Models\BasicModels\presensi_absensi
{
    private $helper;
    public function __construct()
    {
        parent::__construct();
        $this->helper = getCore('Helper');

    }

    public $fileColumns = [];

    public $createAdditionalData = ["creator_id" => "auth:id"];
    public $updateAdditionalData = ["last_editor_id" => "auth:id"];

    public function onRetrieved($model)
    {
        $model->checkout_foto = url('').'/'.$model->checkout_foto;
        $model->checkin_foto = url('').'/'.$model->checkin_foto;
    }

    public function custom_get_by_daily($req)
    {
        $req->month = $req->month.'-01';
        $weeks = $req->weeks;
        $start_date = '';
        $end_date = '';

        $weeksArr = explode('/', $weeks);
        if(count($weeksArr) > 1){
            $start_date = $weeksArr[0];
            $end_date = $weeksArr[1];
            $data = \DB::select("
                SELECT json_agg(json_build_object(
                    'all_days_of_month', all_days_of_month,
                    'date_to_idn', date_to_idn,
                    'day_name_idn', day_name_idn,
                    'type', type,
                    'presentase', presentase,
                    'attend', attend,
                    'cuti', cuti,
                    'alpha', alpha,
                    'total_kary', total_kary
                )) AS monthly_report
                FROM generate_weekly_report(?,?,?,?)",[$start_date, $end_date,$req->divisi_id,$req->dept_id]);
        }else{
            $data = \DB::select("
                SELECT json_agg(json_build_object(
                    'all_days_of_month', all_days_of_month,
                    'date_to_idn', date_to_idn,
                    'day_name_idn', day_name_idn,
                    'type', type,
                    'presentase', presentase,
                    'attend', attend,
                    'cuti', cuti,
                    'alpha', alpha,
                    'total_kary', total_kary
                )) AS monthly_report
                FROM generate_monthly_report(?,?,?)",[$req->month,$req->divisi_id,$req->dept_id]);
        }

        
        if(count($data)){   
            return $this->helper->customResponse('OK',200,json_decode($data[0]->monthly_report));
        }else{
            return $this->helper->customResponse('OK',200,[]);
        }
    }

    public function custom_get_by_date($req)
    {
        $data = \DB::select("
            SELECT json_agg(json_build_object(
                'm_kary_id', m_kary_id,
                'default_user_id', default_user_id,
                'kode', kode,
                'nama_lengkap', nama_lengkap,
                'dept', dept,
                'absensi', absensi
            )) AS att_report
            FROM get_employee_attendance_report(?,?,?)",[$req->date,$req->divisi_id,$req->dept_id]);

        
        if(count($data)){   
            return $this->helper->customResponse('OK',200,json_decode($data[0]->att_report));
        }else{
            return $this->helper->customResponse('OK',200,[]);
        }
    }

    public function custom_checkin($req)
    {
        $validator = Validator::make($req->all(), [
            "foto" => "required",
            "lat" => "required",
            "long" => "required",
            "address" => "required",
        ]);
        if ($validator->fails()) 
            return $this->helper->responseValidate($validator);

        DB::beginTransaction();
        try {
            $distance = $this->distance($req->lat, $req->long);
            if ($distance) {
                $data["on_scope"] = true;
                $data["region"] = $distance->nama;
                $data["checkin_lat"] = $req->lat;
                $data["checkin_long"] = $req->long;
                $data["checkin_address"] = $req->address;
                $data["catatan_in"] = null;
            } else {
                $data["on_scope"] = false;
                $data["region"] = "Out Scope";
                $data["checkin_lat"] = $req->lat;
                $data["checkin_long"] = $req->long;
                $data["checkin_address"] = $req->address;
                $data["catatan_in"] = $req->catatan_in ?? null;
            }

            $check_exists_absen = $this->where("tanggal", date("Y-m-d"))
                ->where("default_user_id", auth()->user()->id)
                ->exists();
            if ($check_exists_absen == true) 
                return $this->helper->customResponse("Anda sudah checkin hari ini", 422);

            if ($req->hasFile("foto")) {
                $file = $req->file("foto");
                $fileName =
                    auth()->user()->username .
                    ":::" .
                    md5(time()) .
                    "." .
                    $file->getClientOriginalExtension();
                $file->move(public_path("uploads/presensi"), $fileName);
            } else {
                trigger_error("IMAGE NOT VALID");
            }

            $this->create([
                "tanggal" => date("Y-m-d"),
                "checkin_time" => date("H:i:s"),
                "checkin_foto" => "uploads/presensi/$fileName",
                "checkin_lat" => $data["checkin_lat"],
                "checkin_long" => $data["checkin_long"],
                "checkin_address" => $data["checkin_address"],
                "checkin_region" => $data["region"],
                "checkin_on_scope" => $data["on_scope"],
                "catatan_in" => $data["catatan_in"],
                "default_user_id" => auth()->user()->id,
                "creator_id" => auth()->user()->id
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            return $this->helper->customResponse("Checkin gagal, coba kembali nanti - ".$e->getMessage(), 400);
        }
        return $this->helper->customResponse("Checkin berhasil", 200, $data);
    }

    public function custom_checkout($req)
    {
        $validator = Validator::make($req->all(), [
            "foto" => "required",
            "lat" => "required",
            "long" => "required",
            "address" => "required",
        ]);
        if ($validator->fails()) return $this->helper->responseValidate($validator);

        DB::beginTransaction();
        try {
            $distance = $this->distance($req->lat, $req->long);
            if ($distance) {
                $data["on_scope"] = true;
                $data["region"] = $distance->nama;
                $data["checkout_lat"] = $req->lat;
                $data["checkout_long"] = $req->long;
                $data["checkout_address"] = $req->address;
                $data["catatan_out"] = null;
            } else {
                $data["on_scope"] = false;
                $data["region"] = "Out Scope";
                $data["checkout_lat"] = $req->lat;
                $data["checkout_long"] = $req->long;
                $data["checkout_address"] = $req->address;
                $data["catatan_out"] = $req->catatan_out ?? null;
            }

            $check_exists_absen = $this->where("tanggal", date("Y-m-d"))
                ->where("default_user_id", auth()->user()->id)
                ->where("status", "ATTEND")
                ->exists();
            if ($check_exists_absen) 
                return $this->helper->customResponse("Anda sudah checkout hari ini", 422);
            
            $check_not_exists_checkin = $this->where("tanggal", date("Y-m-d"))
                ->where("default_user_id", auth()->user()->id)
                ->where("status", "WORKING")->exists();
             if ($check_exists_absen) 
                return $this->helper->customResponse("Anda belum checkin hari ini", 422);

            if ($req->hasFile("foto")) {
                $file = $req->file("foto");
                $fileName =
                    auth()->user()->username .
                    ":::" .
                    md5(time()) .
                    "." .
                    $file->getClientOriginalExtension();
                $file->move(public_path("uploads/presensi"), $fileName);
            } else {
                trigger_error("IMAGE NOT VALID");
            }

            $this->where("tanggal", date("Y-m-d"))
                ->where("default_user_id", auth()->user()->id)
                ->where("status", "WORKING")
                ->update([
                    "checkout_time" => date("H:i:s"),
                    "checkout_foto" => "uploads/presensi/$fileName",
                    "checkout_lat" => $data["checkout_lat"],
                    "checkout_long" => $data["checkout_long"],
                    "checkout_address" => $data["checkout_address"],
                    "checkout_region" => $data["region"],
                    "checkout_on_scope" => $data["on_scope"],
                    "catatan_out" => $data["catatan_out"],
                    "status" => "ATTEND",
                ]);
            DB::commit();
       } catch (\Exception $e) {
            DB::rollback();
            return $this->helper->customResponse("Checkout gagal, coba kembali nanti - ", 422);
        }
        return $this->helper->customResponse("Checkout berhasil", 200, $data);
    }

    private function distance($lat, $long)
    {
        $distance = DB::select("select distance_location(?,?)", [$lat, $long]);
        if (count($distance)) {
            $location = json_decode($distance[0]->distance_location);
            return @$location[0] ?? false;
        } else {
            return false;
        }
    }

    public function custom_distance_check($req)
    {
        $distance = $this->distance($req->lat, $req->long);
        if ($distance) {
            $data["on_scope"] = true;
            $data["region"] = $distance->nama;
            $data["lat"] = $distance->lat;
            $data["long"] = $distance->long;
            $data["address"] = $req->address;
            $data["office"] = $distance->nama;
        } else {
            $data["on_scope"] = false;
            $data["region"] = "Out Scope";
            $data["lat"] = $req->lat;
            $data["long"] = $req->long;
            $data["address"] = $req->address;
            $data["office"] = null;
        }
        return $this->helper->customResponse("OK", 200, $data);
    }

    public function custom_status($model)
    {
        $data = [
            'status' => $this->where('tanggal', date('Y-m-d'))->where('default_user_id', auth()->user()->id ?? 0)->pluck('status')->first() ?? 'NOT ATTEND'
        ];
        return $this->helper->customResponse("OK", 200, $data);
    }

    public function custom_status_get_jadwal_kerja(){
        $karyId = auth()->user()->m_kary_id;
        $getTodayNum = Carbon::today()->dayOfWeek;
        $startOfWeek = Carbon::today()->startOfWeek();
        $endOfweek = Carbon::today()->endOfweek();
        $now = Carbon::now();

        $t_jadwal_kerja_det = t_jadwal_kerja_det::where('m_kary_id',$karyId)->whereHas('t_jadwal_kerja_det_hari',function($query) {
            $query->whereIn('day_num',range(1,7));
        })->with(['t_jadwal_kerja_det_hari.m_jam_kerja' => function($select){
            $select->select('id','m_jam_kerja.is_hari_berikutnya');
        }])->get([
            'id','m_kary_id','t_jadwal_kerja_det_hari_id'
        ]);


        $t_jadwal_kerja_det = $t_jadwal_kerja_det->transform(function ($item){
            $tanggal = Carbon::today()->startOfWeek()->addDays($item->t_jadwal_kerja_det_hari->day_num - 1)->toDateString();
            $item->t_jadwal_kerja_det_hari->tanggal = $tanggal;
            $waktu_mulai = $item->t_jadwal_kerja_det_hari->waktu_mulai;
            $waktu_akhir = $item->t_jadwal_kerja_det_hari->waktu_akhir;
            $item->start_work = Carbon::parse("$tanggal $waktu_mulai")->subHours(2)->toDateTimeString();
            if($item->t_jadwal_kerja_det_hari->m_jam_kerja->is_hari_berikutnya == true){
                $item->end_work = Carbon::parse("$tanggal $waktu_akhir")->addDay()->addHours(2)->toDateTimeString();
            }else{
                 $item->end_work = Carbon::parse("$tanggal $waktu_akhir")->toDateTimeString();
            }
            return $item;
        });
        
        $startDayBeforeWeekend = $t_jadwal_kerja_det[6]['start_work'];
        $EndDayBeforeWeekend = $t_jadwal_kerja_det[6]['end_work'];

        $day_before = [
        "start_work" => Carbon::parse($startDayBeforeWeekend)->subWeek()->toDateTimeString(),
        "end_work" => Carbon::parse($EndDayBeforeWeekend)->subWeek()->toDateTimeString(),
        ];

        $t_jadwal_kerja_det->prepend($day_before);
        
        $getData = $t_jadwal_kerja_det->where('start_work', '<=', $now)
                            ->where('end_work', '>=', $now)
                            ->first();
        if($getData){
            $isPresent = $this->whereBetween('created_at', [$getData['start_work'],$getData['end_work']])->where('default_user_id', auth()->user()->id ?? 0)->pluck('status')->first() ?? 'NOT ATTEND';
            $data = [
                'status' => $isPresent 
            ];
        }else{
            $data = [
                'status' => "ATTEND" 
            ];
        }
        return $this->helper->customResponse("OK", 200, $data);
    }


    public function scopeFilter($model)
    {
        if(req('date_from') && req('date_to')){
            return $model->whereBetween('tanggal',[req('date_from'),req('date_to')])->where('default_user_id', auth()->user()->id ?? 0);
        }
    }

    public function custom_get_absen($req)
    {
        $periode = ($req->periode ?? date('Y-m')).'-1';
        $m_kary_id = auth()->user()->m_kary_id;

        $data = \DB::select("
            select * from employee_attendance_detail(?, ?);
        ", [$periode,$m_kary_id ?? 0]);

        // transform object for mobile
        foreach($data as $dt){
            $dt->status = @json_decode($dt->absensi)->status ?? null;
            $dt->tanggal = @json_decode($dt->absensi)->tanggal ?? null;
            $dt->catatan_in = @json_decode($dt->absensi)->catatan_in ?? null;
            $dt->catatan_out = @json_decode($dt->absensi)->catatan_out ?? null;
            $dt->checkin_lat = @json_decode($dt->absensi)->checkin_lat ?? null;
            $dt->checkin_foto = ($inPic=@json_decode($dt->absensi)->checkin_foto) ? (str_contains($inPic,'http')?$inPic:url($inPic)) : null;
            $dt->checkin_long = @json_decode($dt->absensi)->checkin_long ?? null;
            $dt->checkin_time = @json_decode($dt->absensi)->checkin_time ?? null;
            $dt->checkout_lat = @json_decode($dt->absensi)->checkout_lat ?? null;
            $dt->checkout_foto = ($outPic=@json_decode($dt->absensi)->checkout_foto) ? (str_contains($outPic,'http')?$outPic:url($outPic)) : null;
            $dt->checkout_long = @json_decode($dt->absensi)->checkout_long ?? null;
            $dt->checkout_time = @json_decode($dt->absensi)->checkout_time ?? null;
            $dt->checkin_region = @json_decode($dt->absensi)->checkin_region ?? null;
            $dt->checkin_address = @json_decode($dt->absensi)->checkin_address ?? null;
            $dt->checkout_region = @json_decode($dt->absensi)->checkout_region ?? null;
            $dt->checkin_on_scope = @json_decode($dt->absensi)->checkin_on_scope ?? null;
            $dt->checkout_address = @json_decode($dt->absensi)->checkout_address ?? null;
            $dt->checkout_on_scope = @json_decode($dt->absensi)->checkout_on_scope ?? null;
            $dt->presensi_absensi_id = @json_decode($dt->absensi)->presensi_absensi_id ?? null;
        }

        return $this->helper->customResponse("OK", 200, $data);
    }

    //new facereg function
    public function public_status($model)
    {
        $username = app()->request->employee_id;
        $checkId = default_users::whereRaw(
                'LOWER(username) = ?',
                [strtolower($username)]
            )->first()?->id;

        if(!$checkId){
            throw new \Exception("Akun karyawan tidak ditemukan");
        }

        $data = [
            'status' => $this->where('tanggal', date('Y-m-d'))->where('default_user_id', $checkId ?? 0)->pluck('status')->first() ?? 'NOT ATTEND'
        ];
        return $this->helper->customResponse("OK", 200, $data);
    }
    
    public function public_checkin($req)
    {
        $validator = Validator::make($req->all(), [
            "foto" => "required",
            "lat" => "required",
            "long" => "required",
            "address" => "required",
        ]);
        
        if ($validator->fails()) 
            return $this->helper->responseValidate($validator);
        
        // Penentuan waktu berdasarkan zona longitude
        if(floatval($req->long) < 114){
            $checkin_time = Carbon::now('Asia/Jakarta');
        } elseif(floatval($req->long) < 141){
            $checkin_time = Carbon::now('Asia/Makassar');
        } else {
            $checkin_time = Carbon::now('Asia/Jayapura');
        }

        DB::beginTransaction();
        try {
            $distance = $this->distance($req->lat, $req->long);

            $data["on_scope"] = $distance ? true : false;
            $data["region"]   = $distance->nama ?? "Out Scope";
            $data["checkin_lat"] = $req->lat;
            $data["checkin_long"] = $req->long;
            $data["checkin_address"] = $req->address;
            $data["catatan_in"] = $distance ? null : ($req->catatan_in ?? null);

            if (!$req->hasFile("foto")) {
                throw new \Exception("IMAGE NOT VALID");
            }

            // --- PROSES KOMPRESI ---
            $file = $req->file("foto");
            $rawImageData = $this->compressToWebp($file, 800, 75);

            if (!$rawImageData) {
                throw new \Exception("Gagal mengolah gambar");
            }

            // --- HIT API RECOGNIZE DENGAN DATA KOMPRESI ---
            $response = Http::asMultipart()
                ->attach(
                    'image',
                    file_get_contents($file),
                    'checkface.webp',
                    ['Content-Type' => 'image/webp']
                )
                ->post('https://hris.qqltech.com:7089/recognize');

            $result = $response->json();

            if (!isset($result['data']['employee_id'])) {
                throw new \Exception("Wajah tidak dikenali oleh sistem");
            }

            $employeeUsername = $result['data']['employee_id'];
            $user = default_users::whereRaw(
                'LOWER(username) = ?',
                [strtolower($employeeUsername)]
            )->first();
            
            if(!$user){
                throw new \Exception("Akun karyawan tidak ditemukan di database");
            }
            
            $employeeId = $user->id;

            if (($result['data']['match'] ?? false) !== true) {
                throw new \Exception("Wajah tidak cocok");
            }

            if (($result['data']['liveness_ok'] ?? false) !== true) {
                throw new \Exception("Gagal verifikasi liveness (spoofing terdeteksi)");
            }

            // --- VALIDASI DOUBLE CHECKIN ---
            $check_exists_absen = $this->where("tanggal", date("Y-m-d"))
                ->where("default_user_id", $employeeId)
                ->exists();

            if ($check_exists_absen) 
                return $this->helper->customResponse("Anda sudah checkin hari ini", 422);

            // --- SIMPAN GAMBAR KOMPRESI KE DISK ---
            $fileName = $employeeId . ":::" . md5(time()) . ".webp";
            $subfolder = date('Y/m/d');
            $destinationPath = public_path("uploads/presensi/" . $subfolder);

            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }

            // Simpan file dari memory ke disk
            file_put_contents($destinationPath . '/' . $fileName, $rawImageData);

            // --- CREATE ABSENSI ---
            $this->create([
                "tanggal" => date("Y-m-d"),
                "checkin_time" => $checkin_time->format("H:i:s"),
                "checkin_foto" => "uploads/presensi/$subfolder/$fileName",
                "checkin_lat" => $data["checkin_lat"],
                "checkin_long" => $data["checkin_long"],
                "checkin_address" => $data["checkin_address"],
                "checkin_region" => $data["region"],
                "checkin_on_scope" => $data["on_scope"],
                "catatan_in" => $data["catatan_in"],
                "default_user_id" => $employeeId,
                "creator_id" => $employeeId,
                "status" => "WORKING", // Menambahkan status awal agar bisa di-checkout nanti
            ]);

            DB::commit();

        } catch (\Exception $e) {
            DB::rollback();
            return $this->helper->customResponse(
                "Checkin gagal: " . $e->getMessage(),
                400
            );
        }

        return $this->helper->customResponse("Checkin berhasil", 200, $data);
    }

    public function public_checkout($req)
    {
        $validator = Validator::make($req->all(), [
            "foto"      => "required",
            "lat"       => "required",
            "long"      => "required",
            "address"   => "required",
        ]);
        if ($validator->fails()) return $this->helper->responseValidate($validator);

        // Penentuan waktu berdasarkan zona longitude
        if (floatval($req->long) < 114) {
            $checkout_time = Carbon::now('Asia/Jakarta');
        } elseif (floatval($req->long) < 141) {
            $checkout_time = Carbon::now('Asia/Makassar');
        } else {
            $checkout_time = Carbon::now('Asia/Jayapura');
        }

        DB::beginTransaction();
        try {
            $distance = $this->distance($req->lat, $req->long);

            $data["on_scope"] = $distance ? true : false;
            $data["region"]   = $distance->nama ?? "Out Scope";
            $data["checkout_lat"]      = $req->lat;
            $data["checkout_long"]     = $req->long;
            $data["checkout_address"]  = $req->address;
            $data["catatan_out"]       = $distance ? null : ($req->catatan_out ?? null);

            if (!$req->hasFile("foto")) {
                throw new \Exception("IMAGE NOT VALID");
            }

            // --- PROSES KOMPRESI ---
            // Panggil fungsi private yang sudah kita buat sebelumnya
            $file = $req->file("foto");
            $rawImageData = $this->compressToWebp($file, 800, 75);

            if (!$rawImageData) {
                throw new \Exception("Gagal mengolah gambar");
            }

            // --- HIT API RECOGNIZE DENGAN DATA KOMPRESI ---
            $response = Http::asMultipart()
                ->attach(
                    'image',
                    file_get_contents($file),
                    'checkface.webp',
                    ['Content-Type' => 'image/webp']
                )
                ->post('https://hris.qqltech.com:7089/recognize');

            $result = $response->json();

            // Validasi Response API
            if (!isset($result['data']['employee_id'])) {
                throw new \Exception("Wajah tidak dikenali oleh sistem");
            }

            $employeeUsername = $result['data']['employee_id'];
            $user = default_users::where('username', $employeeUsername)->first();
            
            if (!$user) {
                throw new \Exception("Akun karyawan tidak ditemukan");
            }

            if (($result['data']['match'] ?? false) !== true) {
                throw new \Exception("Wajah tidak cocok");
            }

            if (($result['data']['liveness_ok'] ?? false) !== true) {
                throw new \Exception("Gagal verifikasi liveness (spoofing terdeteksi)");
            }

            $employeeId = $user->id;

            // --- VALIDASI DOUBLE CHECKOUT & CHECKIN STATUS ---
            $today = date("Y-m-d");

            $alreadyCheckout = $this->where("tanggal", $today)
                ->where("default_user_id", $employeeId)
                ->where("status", "ATTEND")
                ->exists();

            if ($alreadyCheckout) {
                return $this->helper->customResponse("Anda sudah checkout hari ini", 422);
            }

            $hasCheckin = $this->where("tanggal", $today)
                ->where("default_user_id", $employeeId)
                ->where("status", "WORKING")
                ->first();

            if (!$hasCheckin) {
                return $this->helper->customResponse("Anda belum checkin atau data checkin tidak ditemukan", 422);
            }

            // --- SIMPAN GAMBAR KOMPRESI KE DISK ---
            $fileName = $employeeId . "::" . md5(time()) . ".webp";
            $subfolder = date('Y/m/d');
            $destinationPath = public_path("uploads/presensi/" . $subfolder);

            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }

            // Simpan file dari memory ke disk
            file_put_contents($destinationPath . '/' . $fileName, $rawImageData);

            // --- UPDATE DATABASE ---
            $hasCheckin->update([
                "checkout_time"     => $checkout_time->format("H:i:s"),
                "checkout_foto"     => "uploads/presensi/$subfolder/$fileName",
                "checkout_lat"      => $data["checkout_lat"],
                "checkout_long"     => $data["checkout_long"],
                "checkout_address"  => $data["checkout_address"],
                "checkout_region"   => $data["region"],
                "checkout_on_scope" => $data["on_scope"],
                "catatan_out"       => $data["catatan_out"],
                "status"            => "ATTEND",
            ]);

            DB::commit();

        } catch (\Exception $e) {
            DB::rollback();
            return $this->helper->customResponse(
                "Checkout gagal: ".$e->getMessage(),
                422
            );
        }

        return $this->helper->customResponse("Checkout berhasil", 200, $data);
    }

    public function public_registerFace($req)
    {
        $validator = Validator::make($req->all(), [
            "foto" => "required|image|max:5120", // Tambahkan validasi tipe & ukuran
            "username" => "required",
            "employee_id" => "required",
        ]);

        if ($validator->fails()) {
            return $this->helper->responseValidate($validator);
        }

        if ($req->hasFile('foto')) {
            $rawImageData = $this->compressToWebp($req->file('foto'), 800, 75);

            if (!$rawImageData) {
                return response()->json(['message' => 'Gagal memproses gambar / Format tidak didukung'], 400);
            }

            try {
                $response = \Illuminate\Support\Facades\Http::asMultipart()
                    ->attach(
                        'image', 
                        $rawImageData, 
                        'face.webp', 
                        ['Content-Type' => 'image/webp']
                    )
                    ->post('https://hris.qqltech.com:7089/register', [
                        'employee_id' => $req->employee_id,
                        'name'        => $req->username,
                    ]);

                return $response->json();

            } catch (\Exception $e) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Gagal menghubungi server Face Recognition: ' . $e->getMessage()
                ], 500);
            }
        }

        return response()->json(['message' => 'IMAGE NOT VALID'], 400);
    }

    private function compressToWebp($file, $maxWidth = 800, $quality = 75)
    {
        $ext = strtolower($file->getClientOriginalExtension());
        list($width, $height) = getimagesize($file->getRealPath());

        if ($width > $maxWidth) {
            $newWidth = $maxWidth;
            $newHeight = ($height / $width) * $maxWidth;
        } else {
            $newWidth = $width;
            $newHeight = $height;
        }

        switch ($ext) {
            case 'jpeg':
            case 'jpg': $src = imagecreatefromjpeg($file->getRealPath()); break;
            case 'png': $src = imagecreatefrompng($file->getRealPath()); break;
            default: return null;
        }

        $tmp = imagecreatetruecolor($newWidth, $newHeight);
        if ($ext == 'png') {
            imagealphablending($tmp, false);
            imagesavealpha($tmp, true);
            imagefill($tmp, 0, 0, imagecolorallocatealpha($tmp, 255, 255, 255, 127));
        }

        imagecopyresampled($tmp, $src, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

        ob_start();
        imagewebp($tmp, null, $quality);
        $rawImageData = ob_get_clean();

        imagedestroy($src);
        imagedestroy($tmp);

        return $rawImageData;
    }

}
