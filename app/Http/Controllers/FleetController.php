<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\FleetUnits;
use Auth;
use App\Helpers\Api;

class FleetController extends Controller
{
    public function __construct()
    {
        $this->status   = "true";
        $this->data     = [];
        $this->errorMsg = null;
    }    
    
    public function index(Request $request) {
        try {
            $query = FleetUnitsDB::orderBy('id','DESC')->get();
            if($request->has('limit'))
                $query = FleetUnits::paginate($request->limit);

            $this->data = $query;
        } catch (\Exception $e) {
            $this->status   = "false";
            $this->errorMsg = $e->getMessage();
        }

        return response()->json(Api::format($this->status, $this->data, $this->errorMsg), 201);
    }
    public function submit(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'fleet_model' => 'required|string|max:255',
                'no_pol' => 'required|string|max:255',
                'service_book' => 'required',
                'stnk'  => 'required'
            ]);
    
            if($validator->fails()){
                return response()->json($validator->errors(), 400);
            }
        
            // $image = str_replace('data:image/png;base64,', '', $image);
            // $image = str_replace(' ', '+', $image);
            $imageName = str_random(10).time();
    
            
            $back_look = $request->input('back_look');
            $front_look = $request->input('front_look');
            $service_book = $request->input('service_book');
            $stnk = $request->input('stnk');
    
            if($back_look){
                list($type_back_look, $back_look) = explode(';', $back_look);
                list(, $back_look)      = explode(',', $back_look);
                $back_look = base64_decode($back_look);
                $ext_back_look = explode('/', $type_back_look);
                $back_look_name = 'uploads/'.$imageName.'-4.'.$ext_back_look[1];
                file_put_contents($back_look_name, $back_look);
            }else{
                $back_look_name = '';
            }
          
            if($front_look){
                list($type_front_look, $front_look) = explode(';', $front_look);
                list(, $front_look)      = explode(',', $front_look);
                $front_look = base64_decode($front_look);
                $ext_front_look = explode('/', $type_front_look);
                $front_look_name = 'uploads/'.$imageName.'-3.'.$ext_front_look[1];
                file_put_contents($front_look_name, $front_look);
            }else{
                $front_look_name='';
            }

            list($type_service_book, $service_book) = explode(';', $service_book);
            list(, $service_book)      = explode(',', $service_book);
            $service_book = base64_decode($service_book);
            $ext_service_book = explode('/', $type_service_book);
            $service_book_name = 'uploads/'.$imageName.'-2.'.$ext_service_book[1];
            file_put_contents($service_book_name, $service_book);

            list($type_stnk, $stnk) = explode(';', $stnk);
            list(, $stnk)      = explode(',', $stnk);
            $stnk = base64_decode($stnk);
            $ext_stnk = explode('/', $type_stnk);
            $stnk_name = 'uploads/'.$imageName.'-1.'.$ext_stnk[1];
            file_put_contents($stnk_name, $stnk);

            $data_post = [
                'no_pol' => $request->get('no_pol'),
                'fleet_model' => $request->get('fleet_model'),
                'back_look' => $back_look_name,
                'front_look' => $front_look_name,
                'service_book' => $service_book_name,
                'stnk' => $stnk_name,

            ];
            // echo "<pre>";
            // print_r($data_post);
            // exit;
            $result = FleetUnits::create($data_post);
            
            if ($result){
                // $file->move($photo_url, $file->getClientOriginalName());
                // rename($photo_url.$file->getClientOriginalName(), $filename);

                $param  = [
                    'no_pol'  => $request->get('no_pol'), 
                    'fleet_model' => $request->get('fleet_model')
                ];

                // Mail::send('emails.daftar', $param, function ($message) use ($param){
                //     $message->from(env('MAIL_USERNAME'));
                //     $message->to($param['email']);
                //     $message->subject("Registration Success!");
                // });
            }

            $this->data = $result;
        } catch (\Exception $e) {
            $this->status   = "false";
            $this->errorMsg = $e->getMessage();
        }

        return response()->json(Api::format($this->status, $this->data, $this->errorMsg), 201);
    }
}


