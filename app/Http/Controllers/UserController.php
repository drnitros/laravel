<?php

namespace App\Http\Controllers;

use App\User;
use App\Helpers\Api;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use JWTAuth, Auth;
use Tymon\JWTAuth\Exceptions\JWTException;

class UserController extends Controller
{
    public function __construct()
    {
        $this->status   = "true";
        $this->data     = [];
        $this->errorMsg = null;
    }    

    public function index(Request $request)
    {
        try {
            $query = User::paginate();
            if($request->has('limit'))
                $query = User::paginate($request->limit);

            $this->data = $query;
        } catch (JWTException $e) {
            $this->status   = "false";
            $this->errorMsg = $e->getMessage();
        }

        return response()->json(Api::format($this->status, $this->data, $this->errorMsg), 200);
    }

    public function detail($id)
    {
        try {
            $query = User::find($id);

            $this->data = $query;
        } catch (JWTException $e) {
            $this->status   = "false";
            $this->errorMsg = $e->getMessage();
        }

        return response()->json(Api::format($this->status, $this->data, $this->errorMsg), 200);
    }

    public function login(Request $request)
    {
        try {
            $credentials = $request->only(['email', 'password']);
            $token = JWTAuth::attempt($credentials);
            if(!$token)
                return response()->json(Api::format("false", $this->data, 'Email or Password not match.'), 200);

        } catch (JWTException $e) {
            return response()->json(Api::format("false", $this->data, $e->getMessage()), 200);
        }

        $user =  Auth::user();
        $name = $user->fullname;
        $id = $user->id;

        if(empty($user->active))
            return response()->json(Api::format("false", $this->data, 'Account Not Activated'), 200);

        return response()->json(Api::format($this->status, compact('token', 'name', 'id'), $this->errorMsg), 200);
    }

    public function register(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|string|max:255|unique:users',
                'fullname' => 'required|string|max:255',
                'password' => 'required|string|min:6',
                'profile_photo'  => 'required|string'
            ]);
    
            if($validator->fails()){
                return response()->json($validator->errors(), 400);
            }
            $image = $request->profile_photo; 
            $img = base64_decode($image);
            $ext = explode('/', $image);

            // $image = str_replace('data:image/png;base64,', '', $image);
            // $image = str_replace(' ', '+', $image);
            $imageName = str_random(10);
    
            
            $img = $request->input('profile_photo');
    
            list($type, $img) = explode(';', $img);
            list(, $img)      = explode(',', $img);
            $img = base64_decode($img);
            $ext = explode('/', $type);
            
            file_put_contents('uploads/'.$imageName.'.'.$ext[1], $img);
           
            $data_post = [
                'email' => $request->get('email'),
                'fullname' => $request->get('fullname'),
                'password' => Hash::make($request->get('password')),
                'profile_photo' => 'uploads/'.$imageName.'.'.$ext[1]
            ];

            $result = User::create($data_post);
            if ($result){
                // $file->move($photo_url, $file->getClientOriginalName());
                // rename($photo_url.$file->getClientOriginalName(), $filename);

                $param  = [
                    'name'  => $request->fullname, 
                    'email' => $request->email
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

    public function activation($email){
		try{
            $MsUser = User::where('email', $email)->first();
            if(empty($MsUser))
                throw new \Exception('User is not found.', 404);

            // activate user
            User::where('email', $email)->update(['active' => 1]);

            $this->data = ['id' => $MsUser->id, 'fullname' => $MsUser->fullname];
		} catch(\Exception $e) {
            $this->status   = "false";
            $this->errorMsg = $e->getMessage();
		}
        return response()->json(Api::format($this->status, $this->data, $this->errorMsg), 200);
    }

    public function getAuthenticatedUser()
    {
        try {
            if (! $user = JWTAuth::parseToken()->authenticate())
                return response()->json(['user_not_found'], 404);
        } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            return response()->json(['token_expired'], $e->getStatusCode());
        } catch (Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            return response()->json(['token_invalid'], $e->getStatusCode());
        } catch (Tymon\JWTAuth\Exceptions\JWTException $e) {
            return response()->json(['token_absent'], $e->getStatusCode());
        }

        return response()->json(compact('user'));
    }
}