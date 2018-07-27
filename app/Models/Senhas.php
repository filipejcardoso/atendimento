<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Senhas extends Model
{
	protected $table = 'senhas';
    protected $primaryKey = 'id';
    protected $fillable = ['senha','guiche','ip','setor'];

    static public function relacoes()
    {
        return []; 
    }

    static public function relacoesModel()
    {
        return [];
    }

}
