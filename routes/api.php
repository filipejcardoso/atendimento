<?php

use Illuminate\Http\Request;

Route::group(['prefix' =>'v1'],function()
{
	Route::group(['prefix' =>'senhas'],function()
	{
		Route::get('', ['uses' => 'SenhasController@index']);
		Route::get('{id}', ['uses' => 'SenhasController@show']);
		Route::post('', ['uses' => 'SenhasController@store']);
		Route::patch('{id}', ['uses' => 'SenhasController@update']);
		Route::delete('{id}', ['uses' => 'SenhasController@destroy']);
	});
	Route::group(['prefix' =>'chamadas'],function()
	{
		Route::get('', ['uses' => 'ChamadasController@index']);
		Route::get('{id}', ['uses' => 'ChamadasController@show']);
		Route::post('', ['uses' => 'ChamadasController@store']);
		Route::patch('{id}', ['uses' => 'ChamadasController@update']);
		Route::delete('{id}', ['uses' => 'ChamadasController@destroy']);
	});
});

