<?php
namespace App\Helpers;
//------------------------------
class Helper
{

	static function lastURI($value)
	{
		$value_explode = explode('/', $value);

		$size = count($value_explode);

		return $value_explode[$size - 1];
	}

	static function firstVerb($value)
	{
		return $value[0];
	}

	static function codeVerb($value)
	{
		$code = null;
		switch($value)
		{
			case 'DELETE':
				$code = 1;
			break;
			case 'PATCH':
				$code = 2;
			break;
			case 'GET':
				$code = 4;
			break;
			case 'POST': 
				$code = 8;
			break;
		}

		return $code;
	}

	static function sortCollectionDesc($array, $keys)
	{
		$array_ord = [];
		$count = count($array);
		$ck = count($keys);

		$debug = "";

		for($i=0;$i<$count;$i++)
		{
			for($j=0;$j<$count-1;$j++)
			{
				for($k=0;$k<$ck;$k++)
				{
					$debug.=$array[$j]['nome']."(".$keys[$k].") = ".$array[$j][$keys[$k]]."<br/><br/>";
					$debug.=$array[$j+1]['nome']."(".$keys[$k].") = ".$array[$j+1][$keys[$k]]."<br/><br/>";
					$debug.="<br/><br/>";

					if($array[$j][$keys[$k]]<$array[$j+1][$keys[$k]])
					{
						$aux = $array[$j];
						$array[$j] = $array[$j+1];
						$array[$j+1] = $aux;

						$debug.=$array[$j]['nome']."(".$keys[$k].") = ".$array[$j][$keys[$k]]."<br/><br/>";
						$debug.="<br/><br/>";

						break;
					}
					else if($array[$j][$keys[$k]] > $array[$j+1][$keys[$k]])
					{
						$debug.=$array[$j]['nome']."(".$keys[$k].") = ".$array[$j][$keys[$k]]."<br/><br/>";
						$debug.="<br/><br/>";
						break;
					 }
				}
			}
		}
		return $array;
	}
}