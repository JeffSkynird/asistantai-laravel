<?php

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group(['prefix' => 'v1'], function () {
    Route::group([
        'prefix' => 'auth',
    ], function () {
        Route::post('login', 'App\Http\Controllers\v1\Seguridad\AuthController@login');
        Route::post('logout', 'App\Http\Controllers\v1\Seguridad\AuthController@logout')->middleware('auth:api');
    });

    Route::post('generate/pdf', 'App\Http\Controllers\v1\Administracion\GenerationController@generateFromPdf');
    Route::post('generate/image', 'App\Http\Controllers\v1\Administracion\GenerationController@generateFromImage');
    Route::post('generate/audio', 'App\Http\Controllers\v1\Administracion\GenerationController@audio');
    
    Route::post('job', 'App\Http\Controllers\v1\Administracion\GenerationController@jobInit');

    Route::get('generations', 'App\Http\Controllers\v1\Administracion\GenerationController@index');

    Route::put('user', 'App\Http\Controllers\v1\Seguridad\UsuarioController@updateAuth');
    Route::get('user', 'App\Http\Controllers\v1\Seguridad\UsuarioController@showAuth');
    
    Route::get('generation/{id}', 'App\Http\Controllers\v1\Administracion\GenerationController@show');
    
    Route::get('/export_generation/{id}', 'App\Http\Controllers\v1\Administracion\GenerationController@exportPdf');

    Route::get('obtenerToken/{id}', 'App\Http\Controllers\v1\Seguridad\UsuarioController@obtenerToken');
    Route::post('update_membership', 'App\Http\Controllers\v1\Administracion\MembershipController@updateMembership');

    Route::middleware('auth:api')->group(function () {
        Route::get('get_last_generations', 'App\Http\Controllers\v1\Administracion\GenerationController@getLastGenerations');
        Route::get('get_generation_kpis', 'App\Http\Controllers\v1\Administracion\GenerationController@getGenerationKpis');
        Route::post('generate', 'App\Http\Controllers\v1\Administracion\GenerationController@create');
        Route::post('generate/{id}', 'App\Http\Controllers\v1\Administracion\GenerationController@updateResults');

        Route::put('user', 'App\Http\Controllers\v1\Seguridad\UsuarioController@updateAuth');
        Route::get('user', 'App\Http\Controllers\v1\Seguridad\UsuarioController@showAuth');

        Route::get('memberships', 'App\Http\Controllers\v1\Administracion\MembershipController@index');
        
        Route::post('change_membership', 'App\Http\Controllers\v1\Seguridad\UsuarioController@changeMembership');
        Route::post('cancel_membership', 'App\Http\Controllers\v1\Administracion\MembershipController@cancelMembership');

        

        Route::post('payment', 'App\Http\Controllers\v1\Administracion\MembershipController@payment');
        

        Route::post('activate_membership', 'App\Http\Controllers\v1\Administracion\MembershipController@activateMembership');
        Route::post('tags', 'App\Http\Controllers\v1\Administracion\TagsController@create');
        Route::put('tags/{id}', 'App\Http\Controllers\v1\Administracion\TagsController@update');
        Route::get('tags/{id}', 'App\Http\Controllers\v1\Administracion\TagsController@show');
        Route::get('tags', 'App\Http\Controllers\v1\Administracion\TagsController@index');
        Route::delete('tags/{id}', 'App\Http\Controllers\v1\Administracion\TagsController@delete');
        Route::delete('tags_by_generation/{id}', 'App\Http\Controllers\v1\Administracion\TagsController@deleteByGeneration');

        
        Route::get('results/{id}', 'App\Http\Controllers\v1\Administracion\GenerationController@getResults');

        Route::post('add_tags_generations', 'App\Http\Controllers\v1\Administracion\TagsController@asignarTag');
        
        Route::get('tags/{id}/generations', 'App\Http\Controllers\v1\Administracion\TagsController@getByTag');
    });

});
