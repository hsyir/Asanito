<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\HandleRequestService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Services\UtilService;

class CallCenterController extends Controller
{
   public function getCdr(Request $request)
{
    $handleService = new HandleRequestService();
    $util = new UtilService();

    $eventName = $request->get('event_name');

    if ($eventName === 'NewState') {

        $uniqueId  = $request->get('unique_id');
        $state     = $request->get('state');
        $direction = $request->get('direction');
        $exten     = $request->get('exten');
        $rawParti  = $request->get('participant');

        /*
        |--------------------------------------------------------------------------
        | INCOMING CALL
        |--------------------------------------------------------------------------
        */
        if ($direction === 'in' && $state === 'Ringing') {

            $src = $util->normalizeIranPhone($rawParti);
            $dst = $exten;

            Cache::put($uniqueId, [
                'state' => $state,
                'src'   => $src,
                'dst'   => $dst,
            ], now()->addMinutes(60));

            $handleService->sendPopUp($uniqueId, $src, $dst);
            return response()->json(['status' => 'ok']);
        }

        /*
        |--------------------------------------------------------------------------
        | OUTGOING CALL
        |--------------------------------------------------------------------------
        */
        if ($direction === 'out') {

            $dst = $util->normalizeIranPhone($rawParti);
            $src = $exten;

            /*
            | Ringing → Popup
            */
            if ($state === 'Ringing') {

                Cache::put($uniqueId, [
                    'state' => $state,
                    'src'   => $src,
                    'dst'   => $dst,
                ], now()->addMinutes(60));

                $handleService->sendPopUp($uniqueId, $src, $dst);
                return response()->json(['status' => 'ok']);
            }

            /*
            | InUse → Popup + Answer  (درخواست شما)
            */
            if ($state === 'InUse') {

                $cachedData = Cache::get($uniqueId);

                if (!$cachedData) {
                    Cache::put($uniqueId, [
                        'state' => 'Ringing',
                        'src'   => $src,
                        'dst'   => $dst,
                    ], now()->addMinutes(60));
                }

                // popup
                $handleService->sendPopUp($uniqueId, $src, $dst);

                // answer
                $handleService->endCall($uniqueId, $src, $dst, 'answer');

                Cache::put($uniqueId, [
                    'state' => 'Ringing',
                    'src'   => $src,
                    'dst'   => $dst,
                ], now()->addMinutes(60));

                return response()->json(['status' => 'ok']);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | STATE CHANGE (Idle / InUse)
        |--------------------------------------------------------------------------
        */
        $cachedData = Cache::get($uniqueId);

        if ($cachedData) {
            $cachedState = $cachedData['state'];
            $cachedSrc   = $cachedData['src'];
            $cachedDst   = $cachedData['dst'];

            if ($cachedState === 'Ringing' && $state === 'Idle') {
                $handleService->endCall($uniqueId, $cachedSrc, $cachedDst, 'reject');
            }

            if ($cachedState === 'Ringing' && $state === 'InUse') {
                $handleService->endCall($uniqueId, $cachedSrc, $cachedDst, 'answer');
            }
        }

        return response()->json(['status' => 'ok']);
    }

    /*
    |--------------------------------------------------------------------------
    | CDR EVENT
    |--------------------------------------------------------------------------
    */
    if ($eventName === 'Cdr') {

        try {
            $dst = $util->normalizeIranPhone($request->get('dst'));

            $handleService->sendCdr(
                $request->get('unique_id'),
                $request->get('src'),
                $dst,
                $request->get('billsec'),
                $request->get('duration'),
                $request->get('disposition'),
                $request->get('record', '')
            );

            return response()->json(['status' => 'ok']);
        } catch (\Exception $ex) {
            return response()->json(['error' => $ex->getMessage()], 500);
        }
    }
}

}
