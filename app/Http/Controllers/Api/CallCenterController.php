<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\HandleRequestService;
use Illuminate\Http\Request;

class CallCenterController extends Controller
{
    public function getCdr(Request $request){
        
         if($request->get('event_name') == "NewState") {
            
            try{

                if($request->get('state') == 'Ringing'){

                $uniquId = $request->get('unique_id');
                $dst = $request->get('exten');
                $src = $request->get('participant');

                $handleService = new HandleRequestService();
                $handleService->sendPopUp($uniquId,$src,$dst);
                
                }

            }catch(\Exception $ex){
                return response()->json(['error'=>$ex->getMessage()],500);
            }
         }else if($request->get('event_name') == 'Cdr'){
            try{
                //اینجا duration مدت کل تماس هست 
                $uniquId = $request->get('unique_id');
                $callerId = $request->get('src');
                $destination = $request->get('dst');
                $buildSeconds = $request->get('duration') * 1000;
                $duration = $request->get('billsec') * 1000;
                $callStatus =  $request->get('disposition');
                $fileUrl = $request->get('record','');
                
                $handleService = new HandleRequestService();
                $handleService->sendCdr($uniquId,$callerId,$destination,$buildSeconds,$duration,$callStatus,$fileUrl);

                
            }catch(\Exception $ex){
                return response()->json(['error'=>$ex->getMessage()],500);
            }
         }
    }
}
