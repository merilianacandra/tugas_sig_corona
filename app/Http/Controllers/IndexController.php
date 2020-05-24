<?php

namespace App\Http\Controllers;

use DB;
use App\Data;
use App\Kabupaten;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;


class IndexController extends Controller
{
    // private $dateTimeNow;
    // private $dateNow;
    // private $dateFormatName;

    // public function __construct()
    // {
    //     $this->dateTimeNow = Carbon::now()->addHours(8);
    //     $this->dateNow = Carbon::now()->format('Y-m-d');
    //     $this->dateFormatName = Carbon::now()->locale('id')->isoFormat('LL');
    // }   
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $tanggalSekarang = CARBON::now()->locale('id')->isoFormat('LL');
        $dateNow = Carbon::now()->format('Y-m-d');
        $data = Data::select('tb_data.id','id_kabupaten','kabupaten','sembuh','rawat','positif','meninggal')
                ->join('tb_kabupaten','tb_data.id_kabupaten','=','tb_kabupaten.id')
                ->where('tgl_data', $dateNow)->orderBy('positif','desc')
                ->get();
                $meninggal = Data::select(DB::raw('COALESCE(SUM(meninggal),0) as meninggal'))->where('tgl_data',$dateNow)->get();
                $positif = Data::select(DB::raw('COALESCE(SUM(positif),0) as positif'))->where('tgl_data',$dateNow)->get();
                $rawat = Data::select(DB::raw('COALESCE(SUM(rawat),0) as rawat'))->where('tgl_data',$dateNow)->get();
                $sembuh = Data::select(DB::raw('COALESCE(SUM(sembuh),0) as sembuh'))->where('tgl_data',$dateNow)->get();
        $kabupaten = Kabupaten::all();
        $labels = Kabupaten::select('kabupaten')->get();
        
        return view('index',compact('kabupaten','data','sembuh','positif','rawat','meninggal','tanggalSekarang'));
    }


    public function search(Request $request){
        
        $tanggal = $request->tanggal;
        $tanggalSekarang = Carbon::parse($request->tanggal)->format('d F Y');
        $cekData = Data::select('kabupaten','id_kabupaten','meninggal','positif','rawat','sembuh','tgl_data')
            ->rightjoin('tb_kabupaten','tb_data.id_kabupaten','=','tb_kabupaten.id')
            ->where('tgl_data',$request->tanggal)
            ->orderBy('id_kabupaten','ASC')
            ->get();
        if (count($cekData) == 0) {
            $data = Kabupaten::select('kabupaten',DB::raw('IFNULL("0",0) as meninggal'), DB::raw('IFNULL("0",0) as positif'), DB::raw('IFNULL("0",0) as rawat'),DB::raw('IFNULL("0",0) as sembuh'))->get();
        }else{
            $data = $cekData;
        }
        $meninggal = Data::select(DB::raw('COALESCE(SUM(meninggal),0) as meninggal'))->where('tgl_data',$request->tanggal)->get();
        $positif = Data::select(DB::raw('COALESCE(SUM(positif),0) as positif'))->where('tgl_data',$request->tanggal)->get();
        $rawat = Data::select(DB::raw('COALESCE(SUM(rawat),0) as rawat'))->where('tgl_data',$request->tanggal)->get();
        $sembuh = Data::select(DB::raw('COALESCE(SUM(sembuh),0) as sembuh'))->where('tgl_data',$request->tanggal)->get();
        
        return view('index',compact("data","meninggal","positif","rawat","sembuh","tanggalSekarang","tanggal"));
    }


    public function getData(Request $request)
    {
        $dateNow = Carbon::now()->format('Y-m-d');
        if (is_null($request->date)) {
            $tanggal = $dateNow;
        }else{
            $tanggal = $request->date;
        }

        $data = Data::select('tb_data.id','id_kabupaten','kabupaten','sembuh','rawat','positif','meninggal')
                ->rightjoin('tb_kabupaten','tb_data.id_kabupaten','=','tb_kabupaten.id')
                ->where('tgl_data',$tanggal)
                ->orderBy('id_kabupaten','ASC')
                ->get();
        return $data;
    }

    public function getPositif(Request $request)
    {
        $dateNow = Carbon::now()->format('Y-m-d');
        if (is_null($request->date)) {
            $tanggal = $dateNow;
        }else{
            $tanggal = $request->date;
        }

        $pos = Data::select('tb_data.id','id_kabupaten','kabupaten','sembuh','rawat','positif','meninggal')
                ->rightjoin('tb_kabupaten','tb_data.id_kabupaten','=','tb_kabupaten.id')
                ->where('tgl_data',$tanggal)
                ->orderBy('positif','DESC')
                ->get();
        return $pos;
    }


    public function createPallette(Request $request)
    {

        $HexFrom = ltrim($request->start, '#');
        $HexTo = ltrim($request->end, '#');

    
        $ColorSteps = 9;
        $FromRGB['r'] = hexdec(substr($HexFrom, 0, 2));
        $FromRGB['g'] = hexdec(substr($HexFrom, 2, 2));
        $FromRGB['b'] = hexdec(substr($HexFrom, 4, 2));
    
        $ToRGB['r'] = hexdec(substr($HexTo, 0, 2));
        $ToRGB['g'] = hexdec(substr($HexTo, 2, 2));
        $ToRGB['b'] = hexdec(substr($HexTo, 4, 2));
    
        $StepRGB['r'] = ($FromRGB['r'] - $ToRGB['r']) / ($ColorSteps - 1);
        $StepRGB['g'] = ($FromRGB['g'] - $ToRGB['g']) / ($ColorSteps - 1);
        $StepRGB['b'] = ($FromRGB['b'] - $ToRGB['b']) / ($ColorSteps - 1);
    
        $GradientColors = array();
    
        for($i = 0; $i <= $ColorSteps; $i++) {
        $RGB['r'] = floor($FromRGB['r'] - ($StepRGB['r'] * $i));
        $RGB['g'] = floor($FromRGB['g'] - ($StepRGB['g'] * $i));
        $RGB['b'] = floor($FromRGB['b'] - ($StepRGB['b'] * $i));
    
        $HexRGB['r'] = sprintf('%02x', ($RGB['r']));
        $HexRGB['g'] = sprintf('%02x', ($RGB['g']));
        $HexRGB['b'] = sprintf('%02x', ($RGB['b']));
    
        $GradientColors[] = implode(NULL, $HexRGB);
        }
        $collect = collect($GradientColors);
        $filtered = $collect->filter(function($value, $key){
            return strlen($value) == 6;
        });
        return $filtered;
    }

    
    function len($val){
        return (strlen($val) == 6 ? true : false );
    }
   
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
