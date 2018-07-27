<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Chamadas extends Model
{
	protected $table = 'chamadas';
    protected $primaryKey = 'id';
    protected $fillable = ['senha','sala'];

    static public function relacoes()
    {
        return []; 
    }

    static public function relacoesModel()
    {
        return [];
    }

}
