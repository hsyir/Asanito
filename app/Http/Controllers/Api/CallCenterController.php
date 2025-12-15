<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\HandleRequestService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Services\UtilService;

class CallCenterController extends Controller
{
    public function getCdr(Request $request){
        $handleService = new HandleRequestService();
        $util = new UtilService();
         if($request->get('event_name') == "NewState") {
            
            // try{
                $uniqueId = $request->get('unique_id');
                $dst = $request->get('exten');
                $src = $request->get('participant');
                $state = $request->get('state');

                if($request->get('state') == 'Ringing'){

                    
                if($request->get('direction') == 'in'){
                     $rawSrc = $request->get('participant');
                     $src = $util->normalizeIranPhone($rawSrc);

                Cache::put($uniqueId, [
                'state' => $state,
                'src' => $src,
                'dst' => $request->get('exten'),
                 ], now()->addMinutes(60));

                $handleService->sendPopUp($uniqueId,$src,$dst);
                }else if($request->get('direction') == 'out'){

                         $rawDst = $request->get('participant');
                         $dst = $util->normalizeIranPhone($rawDst);

                Cache::put($uniqueId, [
                'state' => $state,
                'src' => $request->get('exten'),
                'dst' => $dst,
                 ], now()->addMinutes(60));

                $handleService->sendPopUp($uniqueId,$request->get('exten'),$dst);
                }
                }
                else {
                    $uniqueId = $request->get('unique_id');
                    $newState = $request->get('state');
                    $cachedData = Cache::get($uniqueId);
                    
                    if ($cachedData) {
                    $cachedState = $cachedData['state'] ?? null;
                    $cachedSrc = $cachedData['src'] ?? null;
                    $cachedDst = $cachedData['dst'] ?? null;
                    
                    $rawParti = $request->get('participant');
                    $parti = $util->normalizeIranPhone($rawParti);
                    
                    if ($cachedState == 'Ringing' && $newState =='Idle' ) {
                        //handle Service reject
                        
                        $handleService->endCall($uniqueId,$cachedSrc,$cachedDst,'reject');
                    } else if ($cachedState == 'Ringing' && $newState == 'InUse' ) {
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
                
                $rawDst = $request->get('dst');
                $dst = $util->normalizeIranPhone($rawDst);



                $uniquId = $request->get('unique_id');
                $callerId = $request->get('src');
                $destination = $dst;
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
