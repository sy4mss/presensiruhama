<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;


class PresensiController extends Controller
{
    public function create()
    {
        $hariini = date("Y-m-d");
        $email = Auth::guard('karyawan')->user()->email;
        $cek = DB::table('presensi')->where('tgl_presensi', $hariini)->where('email', $email)->count();
        return view('presensi.create', compact('cek'));
    }

    public function store(Request $request)
    {
        $email = Auth::guard('karyawan')->user()->email;
        $tgl_presensi = date("Y-m-d");
        $jam = date("H:i:s");
        $latitudekantor = -6.315881738075358;
        $longitudekantor = 106.76022049158006;
        $lokasi= $request->lokasi;
        $lokasiuser = explode(",", $lokasi);
        $latitudeuser = $lokasiuser[0];
        $longitudeuser = $lokasiuser[1];

        $jarak = $this->distance($latitudekantor, $longitudekantor, $latitudeuser, $longitudeuser);
        $radius = round($jarak["meters"]);

        $cek = DB::table('presensi')->where('tgl_presensi', $tgl_presensi)->where('email', $email)->count();
        if ($cek > 0) {
            $ket = "out";
        }else{
            $ket = "in";
        }
        $image = $request->image;
        $folderPath = "public/upload/absensi";
        $formatName = $email. "-". $tgl_presensi ."-". $ket;
        $image_parts = explode(";base64", $image);
        $image_base64 = base64_decode($image_parts[1]);
        $fileName = $formatName . ".png";
        $file = $folderPath . $fileName;
        
        if($radius>10){
            echo "error|Maaf Anda Berada Diluar Radius, Jarak Anda ".$radius." Meter dari Kantor|radius";
        }else{
        if($cek>0){
            $data_pulang = [
                'jam_out' => $jam,
                'foto_out' => $fileName,
                'lokasi_out' => $lokasi
            ];
            $update = DB::table('presensi')->where('tgl_presensi', $tgl_presensi)->where('email', $email)->update($data_pulang);
            if($update){
                echo "success|Terimakasih, Hati-Hati Di Jalan!|out";
                Storage::put($file,$image_base64);
            } else{
                echo "error|Maaf Gagal Absen, Silahkan Hubungi Tim IT|out";
            }
        } else{
            $data = [
                'email' => $email,
                'tgl_presensi' => $tgl_presensi,
                'jam_in' => $jam,
                'foto_in' => $fileName,
                'lokasi_in' => $lokasi
            ];
            $simpan = DB::table('presensi')->insert($data);
            if($simpan){
                echo "success|Terimakasih, Selamat Bekerja!|in";;
                Storage::put($file,$image_base64);
            } else{
                echo "error|Maaf Gagal Absen, Silahkan Hubungi Tim IT|in";
            }
        }  
            }   
    }
    function distance($lat1, $lon1, $lat2, $lon2)
    {
        $theta = $lon1 - $lon2;
        $miles = (sin(deg2rad($lat1)) * sin(deg2rad($lat2))) + (cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta)));
        $miles = acos($miles);
        $miles = rad2deg($miles);
        $miles = $miles * 60 * 1.1515;
        $feet = $miles * 5280;
        $yards = $feet / 3;
        $kilometers = $miles * 1.609344;
        $meters = $kilometers * 1000;
        return compact('meters');
    }

    public function editprofile()
    {
        $email = Auth::guard('karyawan')->user()->email;
        $karyawan = DB::table('karyawan')->where('email', $email)->first();
        return view('presensi.editprofile', compact('karyawan'));
    }

    public function updateprofile(Request $request)
    {
        $email = Auth::guard('karyawan')->user()->email;
        $nama_lengkap = $request->nama_lengkap;
        $no_hp = $request->no_hp;
        $password = Hash::make($request->password);
        $karyawan = DB::table('karyawan')->where('email',$email)->first();
        if($request->hasFile('foto')){
            $foto = $email.".".$request->file('foto')->getClientOriginalExtension();
        }else{
            $foto = $karyawan->foto;
        }

        if(empty($password)){
            $data = [
                'nama_lengkap' => $nama_lengkap,
                'no_hp' => $no_hp,
                'foto' =>$foto
            ];
        } else {
            $data = [
                'nama_lengkap' => $nama_lengkap,
                'no_hp' => $no_hp,
                'password' => $password,
                'foto' => $foto
            ];
        }
        $update = DB::table('karyawan')->where('email', $email)->update($data);
        if ($update){
            if($request->hasFile('foto')){
                $folderPath = "public/uploads/karyawan/";
                $request->file('foto')->storeAs($folderPath, $foto);
            }
            return Redirect::back()->with(['success'=> 'Data berhasil di Update']);
        }else {
            return Redirect::back()->with(['error'=> 'Data Gagal di Update']);;
        }
    }
}
