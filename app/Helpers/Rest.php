<?php
namespace App\Helpers;
//------------------------------
use App\Helpers\Result;
use App\Exceptions\EmptyException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;
use DB;
//------------------------------
class Rest
{
    public $model;
    public $input;
    public $instance;
    public $builder;
    public $relation;
    private $response;

    public function __construct()
    {
        $this->relation = true;
    }

    public function getBuilder()
    {
        $filter = [];
        $sort = [];
        $fields = [];
        $limit = 0;
        $page = 0;

        $relacoes = $this->model::relacoes();
        $relacoesModel = $this->model::relacoesModel();

        foreach($this->input as $key=>$value)
        {
            if($key == 'sort')
            {
                $valor = explode(',', $value);

                foreach($valor as $v)
                {
                    $sort_array = str_split($v);

                    $operacao = $sort_array[0];

                    $o = strtolower($operacao);

                    if($o == 'a')
                        $order = 'asc';
                    else if($o == 'd')
                        $order = 'desc';

                    array_shift($sort_array);

                    $sort_str = implode("",$sort_array);
                  
                    array_push($sort,[$sort_str, $order]);  
                }
            }
            else if ($key == 'fields') 
            {
                $fields = explode(',', $value);  
                
                $relacoes_aux = [];
                $relacoes_model_aux = [];

                foreach($fields as $k=>$field)
                {
                    if(in_array($field, $relacoes))
                    {
                        array_push($relacoes_aux, $field);  
                        array_push($relacoes_model_aux, $relacoesModel[array_search($field, $relacoes)]);  
                        $fields[$k] = $field.'_id';
                    }
                }

                $relacoes = [];
                $relacoesModel = [];

                $relacoes = $relacoes_aux;
                $relacoesModel = $relacoes_model_aux;
            }
            else if ($key == 'page') 
            {
                $page = $value;
            }
            else if ($key == 'limit')
            {
                $limit = $value;
            }
            else
            {
                $value_array = [];
                if(is_array($value))
                    $value_array = $value;
                else
                    array_push($value_array, $value);
                foreach($value_array as $val)
                {            
                    $filtro = [];

                    //pega o nome das realacoes
                    $relacoes_filter = $this->model::relacoes();                
                    
                    //explode a string em virgurlas
                    $value_explode = explode(',',$val);
                    
                    //pega o tamanho do vetor para o caso particular de igual
                    $count = count($value_explode);

                    //LOGIC
                    $filter_array = str_split($key);
                   
                    if(stristr(substr($key, 0, 2),'or'))
                        $filtro['logic'] = 'OR';
                    else if(stristr(substr($key, 0, 3),'and'))
                        $filtro['logic'] = 'AND';

                    //logic default AND
                    if(array_key_exists('logic',$filtro))
                    {
                        for($i=0;$i<strlen($filtro['logic']);$i++)
                            array_shift($filter_array);
                    }
                    else
                        $filtro['logic'] = 'AND';

                    $filter_str = implode("",$filter_array);

                    $filtro['column'] = $filter_str; 
                    $filtro['operator'] = '=';
                    $filtro['value'] = $value_explode[0];                        

                    if($count>1)
                    {
                        $filtro['operator'] = $value_explode[0];
                        $filtro['value'] = $value_explode[1];
                    }

                    //relationship
                    if(in_array($filter_str, $relacoes_filter))
                    {
                        $filtro['column'] = $value_explode[0];
                        $filtro['operator'] = '=';
                        $filtro['value'] = $value_explode[1];
                        $filtro['relation'] = $filter_str;
                             
                        if($count>2)
                        {
                            $filtro['operator'] = $value_explode[1];
                            $filtro['value'] = $value_explode[2];
                        }

                    } 
                    array_push($filter, $filtro);
                }
            }
        }

        //RELACOES EM CASCATA
        $relacoes_cascade=[];
        foreach($relacoes as $key=>$relacao)
        {
            $method = 'relacoes';
            
            if(method_exists($relacoesModel[$key], $method))
            {
                $sub_relations = $relacoesModel[$key]::$method();

                foreach($sub_relations as $sub)
                {
                    if(array_key_exists('fields', $this->input))
                    {
                        $fieldsSub = explode(',', $this->input['fields']);  
                        if(in_array($sub, $fieldsSub))
                        {
                            array_push($relacoes_cascade, $relacao.".".$sub);
                            unset($fields[array_search($sub, $fields)]);
                        }
                    }
                    else
                        array_push($relacoes_cascade, $relacao.".".$sub);
                }
            }
            if(empty($sub_relations))
                array_push($relacoes_cascade, $relacao);
        }
        if(!empty($relacoes_cascade))
            $relacoes = $relacoes_cascade;


        //MAKE RESPONSE
        $this->response['filter'] = $filter;
        $this->response['sort'] = $sort;
        $this->response['fields'] = $fields;
        $this->response['limit'] = $limit;
        $this->response['page'] = $page;

        //INSTANCE BUILDER
        $builder = $this->model::query();

        //FILTER
        $builder->whereRaw('( 1=1');
        foreach($filter as $k=>$f)
        {    
            //FILTER SELF
            if(!array_key_exists('relation', $f))
            {
                $filter[$k]['value'] = str_replace("$", "%", $f['value']);
                
                if($f['logic'] == "AND")
                    $builder->where($f['column'], $f['operator'], $filter[$k]['value']);
                else if($f['logic'] == "OR")
                    $builder->orWhere($f['column'], $f['operator'], $filter[$k]['value']);
            }
            else //FILTER RELATIONSHIP
            {
                $filter[$k]['value'] = str_replace("$", "%", $f['value']);

                if($f['logic'] == "AND")
                    $builder = $builder->whereHas($f['relation'], function ($query) use ($f, $filter, $k){            
                        $query->where($f['column'], $f['operator'], $filter[$k]['value']);});
                else if($f['logic'] == "OR")
                    $builder = $builder->orWhereHas($f['relation'], function ($query) use ($f, $filter, $k){            
                        $query->where($f['column'], $f['operator'], $filter[$k]['value']);});
            }
        }
        $builder->whereRaw('1=1 )');

        //FIELDS
        foreach($fields as $field)
            $builder->addSelect($field);

        //SORT
        foreach($sort as $s)
            $builder->OrderBy($s[0],$s[1]);

        //REALTIONSHIP
        if($this->relation)
            $builder = $builder->with($relacoes);

        $this->builder = $builder;

        return $this->builder;
    }

    public function getCollection($metodo, $value)
    {
        $result = new Result();

        $model = [];
        $paginate = [];

        try{

            if($this->builder == null)
                $builder = $this->getBuilder();
            else
                $builder = $this->builder;
            if($metodo == 'paginate')
            {
                if($this->response['limit']>0)
                {
                    $paginator = $builder->$metodo($this->response['limit'], null, null, $this->response['page']);

                    $paginate['per_page'] = $paginator->perPage();
                    $paginate['current_page'] = $paginator->currentPage();
                    $paginate['last_page'] = $paginator->lastPage();
                    $paginate['count'] = $paginator->total();

                    $model = $paginator->getCollection();
                }
                else
                {
                    $model = $builder->get($value);
                }
            }
            else if($metodo == 'get')
            {
                $model = $builder->$metodo($value);
            }
            else if($metodo == 'find')
            {
                $registro = $builder->$metodo($value);

                $model = collect($registro);
            }

            $result->internalMessage = 'OK';
            $result->setCode(200);

            if($model->isEmpty())
                throw new EmptyException();

        } catch(\Illuminate\Database\QueryException $e){    
            
            $result->internalMessage = $e->getMessage();
            $result->setCode(400);
        } catch(EmptyException $e){

            $result->internalMessage = $e->getMessage();
            $result->setCode(404);
        } catch(Exception $e){

            $result->internalMessage = $e->getMessage();
            $result->setCode(500);

        } finally{

            $response['records'] = $model;
            $response['paginate'] = $paginate;
            $response['result'] = $result;

           return $response;
        }
    }

    public function insert()
    {
        $result = new Result();

        $response = [];

        try
        {
            $relacoes = $this->model::relacoes();
            $relacoesModel = $this->model::relacoesModel();
            foreach($this->input['records'] as $valor)
            {
                $this->instance->fill($valor);
                foreach($relacoes as $key=>$value)
                {
                    if(array_key_exists($value, $valor))
                    {   
                        try
                        {
                            $relationInstance;
                            if(array_key_exists('id', $valor[$value]))
                            {
                                $relationInstance = $relacoesModel[$key]::findOrFail($valor[$value]['id']);

                                $chave = $value.'_id';
                                $this->instance->$chave = $relationInstance->id;
                            }
                            else
                            {
                                throw new ModelNotFoundException();   
                            }
                            
                        }
                        catch(ModelNotFoundException $e)
                        {
                            $relationInstance = new $relacoesModel[$key];

                            $relationInstance->fill($valor[$value]);

                            $relationInstance->save();

                            $chave = $value.'_id';

                            $this->instance->$chave = $relationInstance->id;
                        }
                    }
                }
                $this->instance->save();
            }

            $result->internalMessage = 'Resource created successfully';
            $result->setCode(201);
            $response['records'] = $this->instance;
            $response['result'] = $result; 

            return $response;
        }

        catch(Exception $e)
        {
            $result->internalMessage = $e->getMessage();
            $result->setCode(500);

            $response['result'] = $result;

            return $response;
        }
    }

    public function renew()
    {
        $result = new Result();

        $response = [];

        try
        {
            $relacoes = $this->model::relacoes();
            $relacoesModel = $this->model::relacoesModel();

            foreach($this->input['records'] as $valor)
            {
                $this->instance->fill($valor);
                foreach($relacoes as $key=>$value)
                {
                    if(array_key_exists($value, $valor))
                    {    
                        try
                        {
                            $relationInstance;
                            if(array_key_exists('id', $valor[$value]))
                            {
                                $relationInstance = $relacoesModel[$key]::findOrFail($valor[$value]['id']);

                                $chave = $value.'_id';

                                $this->instance->$chave = $relationInstance->id;

                                if(property_exists($relationInstance, 'onePerOne'))
                                    if($relationInstance->onePerOne)
                                    {
                                        $relationInstance->fill($valor[$value]);
                                         $relationInstance->save();
                                    }
                            }
                            else
                            {
                                throw new ModelNotFoundException();   
                            }
                            
                        }
                        catch(ModelNotFoundException $e)
                        {

                            $chave = $value.'_id';

                            $relationInstance = $relacoesModel[$key]::findOrFail($this->instance->$chave);

                            $relationInstance->fill($valor[$value]);

                            $relationInstance->save();
                        }
                    }
                }
                $this->instance->save();
                $result->internalMessage = 'Update successfully';
                $result->setCode(202);
            }

            $response['records'] = $this->instance;
            $response['result'] = $result; 

            return $response;
        }

        catch(Exception $e)
        {
            $result->internalMessage = $e->getMessage();
            $result->setCode(500);

            $response['result'] = $result;

            return $response;
        }
    }
}