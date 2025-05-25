<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CallCenterController;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/getcdr',[CallCenterController::class,"getCdr"]);

Route::get('/asanito/selfupdate', function () {
                 try {

                     chdir("..");
                     $output = shell_exec('git pull origin');
                     echo "<pre>$output</pre>";
                     echo "<br>";

                    //  $output = Artisan::call(
                    //      'migrate',
                    //      [
                    //          '--force' => true
                    //      ]
                    //  );

                     echo "<pre>$output</pre>";

                 } catch (\Exception $ex) {
                     return $ex->getMessage();
                 }

             });