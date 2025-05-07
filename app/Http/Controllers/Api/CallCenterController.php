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
                $handleService->send($uniquId,$src,$dst);
                
                }
                
            }catch(\Exception $ex){
                return response()->json(['error'=>$ex->getMessage()],500);
            }
         }
    }
}
