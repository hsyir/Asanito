<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\HandleRequestService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CallCenterController extends Controller
{
    public function getCdr(Request $request){
        $handleService = new HandleRequestService();
         if($request->get('event_name') == "NewState") {
            
            // try{
                $uniqueId = $request->get('unique_id');
                $dst = $request->get('exten');
                $src = $request->get('participant');
                $state = $request->get('state');



                if($request->get('state') == 'Ringing'){

                Cache::put($uniqueId, [
                'state' => $state,
                'src' => $request->get('participant'),
                'dst' => $request->get('exten'),
                 ], now()->addMinutes(60));

                $handleService->sendPopUp($uniqueId,$src,$dst);
                
                }
                else {
                    $uniqueId = $request->get('unique_id');
                    $newState = $request->get('state');
                    $cachedData = Cache::get($uniqueId);
                    
                    if ($cachedData) {
                    $cachedState = $cachedData['state'] ?? null;
                    $cachedSrc = $cachedData['src'] ?? null;
                    $cachedDst = $cachedData['dst'] ?? null;

                    
                    if ($cachedState == 'Ringing' && $newState =='Idle' && $cachedSrc == $request->get('participant') && $cachedDst == $request->get('exten')) {
                        //handle Service reject
                        
                        $handleService->endCall($uniqueId,$cachedSrc,$cachedDst,'reject');
                    } else if ($cachedState == 'Ringing' && $newState == 'InUse' && $cachedSrc == $request->get('participant') && $cachedDst == $request->get('exten')) {
                        //handle Service Answer

                        $handleService->endCall($uniqueId,$cachedSrc,$cachedDst,'answer');
                    }
                }
                }

            // }catch(\Exception $ex){
            //     return response()->json(['error'=>$ex->getMessage()],500);
            // }
         }else if($request->get('event_name') == 'Cdr'){
            try{
                
                $uniquId = $request->get('unique_id');
                $callerId = $request->get('src');
                $destination = $request->get('dst');
                $buildSeconds = $request->get('duration') ;
                $duration = $request->get('billsec') ;
                $callStatus =  $request->get('disposition');
                $fileUrl = $request->get('record','');
                
                $handleService = new HandleRequestService();
                $handleService->sendCdr($uniquId,$callerId,$destination,$duration,$buildSeconds,$callStatus,$fileUrl);

                
            }catch(\Exception $ex){
                return response()->json(['error'=>$ex->getMessage()],500);
            }
         }
    }
}
