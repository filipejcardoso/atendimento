<?php
namespace App\Helpers;
//------------------------------
class Result
{
	public $userMessage;
	public $internalMessage;
	public $code;

	public function setCode($value)
	{
		$this->code = $value;

		switch($value)
		{
			case 200:
				$this->userMessage = "Eyerything is working";
			break;
			case 201:
				$this->userMessage = "New resource has been created";
			break;
			case 202:
				$this->userMessage = "The resource has been updated";
			break;
			case 400:
				$this->userMessage = "The request was invalid or cannot be served. The Params is not valid";
			break;
			case 401:
				$this->userMessage = "The request requires an user authentication";
			break;
			case 403:
				$this->userMessage = "The server understood the request, but is refusing it or the access is not allowed";
			break;
			case 404:
				$this->userMessage = "There is no resource behind the URI";
			break;
			case 440:
				$this->userMessage = "The client's session has expired and must log in again";
			break;
			case 500:
				$this->userMessage = "Internal Server Error";
			break;
		}
	}
}