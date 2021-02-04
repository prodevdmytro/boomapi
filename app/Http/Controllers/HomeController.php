<?php

namespace App\Http\Controllers;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Auth;

use Illuminate\Http\Request;
use App\Category;
use App\Service;
use App\Partner;
use App\Teams;
use App\User;
use App\Boom;
use Carbon\Carbon;
use Tymon\JWTAuth\Exceptions\JWTException;


class HomeController extends Controller
{

    private function getToken($email, $password)
    {
        $token = null;
        //$credentials = $request->only('email', 'password');
        try {
            if (!$token = JWTAuth::attempt(['email' => $email, 'password' => $password])) {
                return response()->json([
                    'response' => 'error',
                    'message' => 'Password or email is invalid..',
                    'token' => $token,
                ]);
            }
        } catch (JWTAuthException $e) {
            return response()->json([
                'response' => 'error',
                'message' => 'Token creation failed',
            ]);
        }
        return $token;
    }

    public function getAuthUser(Request $request) {
        try {

            if (!$user = JWTAuth::toUser($request->token)) {
                return 0;
            } else {

                $user = JWTAuth::toUser($request->token);
                return $user;
            }
        } catch (Exception $e) {

            return 0;

        }
    }


    public function create_categories(Request $request)
    {
        $category_name = $request->category_name;
        $service_name1 = $request->service_name1;
        $normal_price1 = $request->normal_price1;
        $boom_price1 = $request->boom_price1;
        $service_name2 = $request->service_name2;
        $normal_price2 = $request->normal_price2;
        $boom_price2 = $request->boom_price2;
        $service_name3 = $request->service_name3;
        $normal_price3 = $request->normal_price3;
        $boom_price3 = $request->boom_price3;
        $expires = $request->expires;
        $postcodes = $request->postcodes;

        $service_name_arr = array_filter([$service_name1,$service_name2,$service_name3]);
        $normal_price_arr = array_filter([$normal_price1,$normal_price2,$normal_price3]);
        $boom_price1_arr = array_filter([$boom_price1,$boom_price2,$boom_price3]);


        $categories = new Category();
        $categories->category_name = $category_name;
        $categories->expires = $expires;
        $categories->postcode = $postcodes;
        $categories->status = 0; 
        if($categories->save())
        {
            $categoryid = Category::select('id')->where('category_name', $request->category_name)->first();
            
            try{
                $service_data_arr = [];
                for($i=0; $i < count($service_name_arr); $i++){
                    $services = new Service();
                    $services->category_id = $categoryid->id;
                    $services->service_name = $service_name_arr[$i];
                    $services->normal_price = $normal_price_arr[$i];
                    $services->boom_price = $boom_price1_arr[$i];
                    $services->save();
                    $arr = ['service'.$i => $service_name_arr[$i],'normal price'.$i =>$normal_price_arr[$i],'boom price'.$i =>$boom_price1_arr[$i]];
                    array_push($service_data_arr, $arr);
                }

                $response = [
                    'status' => true,
                    'data' => [
                        'category_name' =>$categories->category_name,
                        'services' => $service_data_arr,
                        'expires'=> $categories->expires,
                        'postcodes '=>$categories->postcode,
                        'message'=>'Categories was added successfully.'
                    ],
                ];

            }catch(\Throwable $e){
                $response = ['status' => false, 'data' => 'Could not register user.'];
                return response()->json($response, 201);
            } 
        }
        else{
            $response = ['status' => false, 'data' => 'Could not register category.'];
            return response()->json($response, 201);
        }        
        
       return response()->json($response, 201);
    }


    public function create_partners(Request $request)
    {
        $category_id = $request->category_id;
        $partner_name = $request->partner_name;
        $address = $request->address;
        $postcodes = $request->postcodes;
        $contact_name = $request->contact_name;
        $email = $request->email;
        $tel_number = $request->telephone;
        
        $checkPartner = Partner::where('email', $request->email)->first();         
        
        if($checkPartner)
        {
            $response = [
                'status' => false,
                'message' => 'Used email address',
            ];
            return response()->json($response);
        }         

        $partners  = new partner();
        $partners->category_id = $category_id;
        $partners->partner_name = $partner_name;
        $partners->address = $address;
        $partners->postcodes = $postcodes;
        $partners->contact_name = $contact_name;
        $partners->email = $email;
        $partners->telephone = $tel_number;

        if($partners->save())
        {
            $response = [
                'status' => true,
                'data' => [
                    'category_name' => Category::where('id', $category_id)->first()->category_name,
                    'partner_name' => $partners->partner_name,
                    'address'=> $partners->address,
                    'postcodes'=> $partners->postcodes,
                    'contact_name'=> $partners->contact_name,
                    'email'=> $partners->email,
                    'telephone'=> $partners->telephone,
                    'message'=>'partners was added successfully.'
                ],
            ];
        }
        else{
            $response = ['status' => false, 'data' => 'Could not register partner.'];
            return response()->json($response, 201);
        }
        return response()->json($response, 201);
    }


    public function categories(Request $request)
    {
        $all_data = Category::all();
        if($all_data == null)
        {
            $response = ['status' => true, 'message'=>'data empty'];
            return response()->json($response, 201); 
        }

        $category_arr = [];
        foreach($all_data as $data)
        {
            $category_arr['name'] = $data->category_name;
            $category_arr['expires'] = $data->expires;
            //$category_arr['service'] = service::where('category_id',$data->id)->get();
            $category_arr['postcode'] = $data->postcode;
        }      
        
        $response = ['status' => true, 'data' => $category_arr];
        return response()->json($response, 201);

    }


    public function unlock_category(Request $request)
    { 
        $category_name = $request->category_name;
        $category = Category::where('category_name',$category_name)->get()->first();
       
        do {
            $boomid = substr(str_shuffle('0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ'),1,8);                    
            $current_code = Category::where('boomid', $boomid)->get()->first();
        }
        while(!empty($current_code));               
        $category->status = 1;
        $category->boomid = $boomid;   
        if($category->save())
        {
            $category_arr = [];
            $category_arr['name'] = $category->category_name;
            $category_arr['expires'] = $category->expires;
            if($category->status == 1)
            {
                $category_arr['expires'] = "UnLocked"; 
            }
            $category_arr['boomID'] = $category->boomid; 
            $category_arr['service'] = service::where('category_id',$category->id)->get();
            $category_arr['postcode'] = $category->postcode;

            $response = ['status' => true, 'data' => $category_arr];
            return response()->json($response, 201);
        }
        else{
            $response = ['status' => false, 'message' => "Data is empty"];
            return response()->json($response, 201);
        }

    }


    public function partners(Request $request)
    {
        $all_data = Partner::all();
        if($all_data == '')
        {
            $response = ['status' => true, 'message'=>'data empty'];
            return response()->json($response, 201); 
        }

        $partner_arr = [];
        foreach($all_data as $data)
        {
            $category_name = Category::select('category_name')->where('id', $data->category_id)->first();
            $partner_arr['category_id'] = $category_name;
            $partner_arr['partner_name'] = $data->partner_name;
            $partner_arr['address'] = $data->address;
            $partner_arr['postcodes'] = $data->postcodes;
            $first_name = User::select('first_name')->where('id', $data->contact_userid)->first();
            $last_name = User::select('last_name')->where('id', $data->contact_userid)->first();
            $partner_arr['contact_userid'] = $first_name->first_name." ".$last_name->last_name;
            $partner_arr['email'] = $data->email;
            $partner_arr['telephone'] = $data->telephone; 
            $partner_arr['team'] = teams::where('partner_id',$data->id)->get();           
        }      
        
        $response = ['status' => true, 'data' => $partner_arr];
        return response()->json($response, 201);

    }


    public function checkBoomid(Request $request)
    {
        $partner_name = $request->partner_name;
        $boomid = $request->boomid;
        $partner = Partner::where('partner_name',$partner_name)->get()->first();
        $category = Category::where('id',$partner->category_id)->where('boomid',$boomid)->get()->first();
        if($category)
        {
            $response = [
                'status' => 'ok',
                'data' => [
                    'partner name' =>$partner->partner_name,
                    'boomid' => $category->boomid,
                    'address'=> $partner->address,                    
                ],
            ];
        }
        else
        {
            $response = ['status' => false, 'message' => "Data is empty"];           
        }
        return response()->json($response, 201);
    }
    

    public function get_newboom(Request $request)
    {
        $user = $this->getAuthUser($request);
        $checkboom = Boom::where('boombtn_id', $user->homebtnid)->first();
        if(!empty($checkboom)) {
            $response = ['status' => false, 'message' => "You already have a boom"];  
            return response()->json($response, 201);
        }
        else{
            $partner = Partner::where('postcodes', $user->postcode)->first();
            if(empty($partner)) {
                $response = ['status' => false, 'message' => "No matched partner"];  
                return response()->json($response, 404);
            }
            else{
                $boom = new Boom();
                $boom->category_id = $partner->category_id;
                $boom->boombtn_id = $user->homebtnid;
                $boom->status = 'locked';
                if ($partner->category->expires == 'weekly') {

                    $boom->expired_date = Carbon::now()->addWeek();
                }
                else if ($partner->category->expires == 'monthly'){

                    $boom->expired_date = Carbon::now()->addMonth();
                }
                else{

                    $boom->expired_date = Carbon::now()->addYear(); 
                }
                
                $boom->save();
                $response = ['status' => true, 'message' => "A Bike boom service has been added"];  
                return response()->json($response, 201);
            }
        }
    }

    public function get_boomlist(Request $request)
    {
        $user = $this->getAuthUser($request);
        $booms = Boom::where('boombtn_id', $user->homebtnid)->get();
        if(empty($booms)){
            $response = ['status' => false, 'message' => "No booms, get new"];
            return response()->json($response, 404);
        }
        else{
            foreach($booms as $boom){
                $boom->categoryname = $boom->category->category_name;
                $expired_date = Carbon::parse($boom->expired_date);
                $now = Carbon::now();
                $boom->remaning_day = $expired_date->diffInDays($now);
            }
            $response = $booms;
            return response()->json($response, 201);
        }
    }

    public function unlockboom(Request $request)
    {
        
        $boom = Boom::where('id', $request->boomid)->first();
        if(empty($boom)){
            $response = ['status' => false, 'message' => "Invaild boom"];  
            return response()->json($request->boomid, 404);
        }
        else if($boom->status == 'locked'){
            do {
                $boom_id = substr(str_shuffle('0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ'),1,8);                    
                $current_code = Boom::where('boom_id', $boom_id)->get()->first();
            }
            while(!empty($current_code));
            $boom->status = "unlocked";
            $boom->boom_id = $boom_id;
            $boom->update();
            $response = ['boomid' => $boom_id, 'status' => true, 'message' => "Unlocked"];  
            return response()->json($response, 201);
        }
        else if($boom->status == 'unlocked'){
            $response = ['status' => false, 'message' => "Already unlocked"];  
            return response()->json($response, 201);
        }

    }

    public function boom_details(Request $request)
    {
        $category = Boom::where('boom_id', $request->boomid)->first();
        if(!empty($category)){
            $service = Service::where('category_id', $category->category->id)->get();
            if(!empty($service)){
                $response = $category->service;
                return response()->json($response, 201);
            }
            else{
                $response = ['status' => false, 'message' => "No services"];
                return response()->json($response, 404);
            }
        }
        else{
            $response = ['status' => false, 'message' => "No categories"];  
            return response()->json($response, 404);
        }
        

    }

    public function get_boomprice(Request $request)
    {
        $service[0] = Service::where('id', $request->service1_id)->first()->boom_price;
        $service[1] = Service::where('id', $request->service2_id)->first()->boom_price;
        $service[2] = Service::where('id', $request->service3_id)->first()->boom_price;
        return response()->json($service, 201);
    }


    public function show_partners(Request $request)
    {
        $user = $this->getAuthUser($request);
        $partners = Partner::where('postcodes', $user->postcode)->skip(0)->take(3)->get();
        return response()->json($partners, 201);
    }

    public function send_email(Request $request)
    {

    }

    public function get_notification(Request $request)
    {
        $user = $this->getAuthUser($request);
        $locked_booms = count(Boom::where('boombtn_id', $user->homebtnid)->where('status','locked')->get());    
        $unlocked_booms = count(Boom::where('boombtn_id', $user->homebtnid)->where('status','unlocked')->get());
        $response = ['status' => true, 'locked_booms' => $locked_booms, "unlocked_booms" =>$unlocked_booms]; 
        
        return response()->json($response, 201);
        
    }

    public function get_lockedbooms(Request $request)
    {
        $user = $this->getAuthUser($request);
        $locked_booms = Boom::where('boombtn_id', $user->homebtnid)->where('status','locked')->get();        
        if(!empty($locked_booms)){
            return response()->json($locked_booms, 201);
        }
        else{
            $response = ['status' => false, 'message' => "No locked booms"];  
            return response()->json($response, 404);
        }
        
    }


    public function get_unlockedbooms(Request $request)
    {
        $user = $this->getAuthUser($request);
        $unlocked_booms = Boom::where('boombtn_id', $user->homebtnid)->where('status','unlocked')->get();     
        if(!empty($locked_booms)){
            return response()->json($unlocked_booms, 201);
        }
        else{
            $response = ['status' => false, 'message' => "No unlocked booms"];  
            return response()->json($response, 404);
        }
    }
}
