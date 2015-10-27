<?php
namespace App\Services\Ddrtty;
use App\Services\Ddrtty\Crypt3Des as Crypt3Des;

class Ddrtty{
			
	protected  $sequence;
	protected  $agentCode;
	protected  $interfaceCode; 
	protected  $key;
	protected  $url;
	protected  $crypt;

	public function __construct(){

		$this->version  = '1.0'; 
		$this->sequence = 'you sequence!';
		$this->agentCode  = 'you agentCode'
		$this->interfaceCode  = 'you interfaceCode';
		$this->key  = 'you key';		
		$this->url  = 'http://116.90.86.26:8080/InterTicket/interfaceAction';
		$this->crypt = new Crypt3Des($this->key);
	}
	
	function getProduct()
	{
		$data = array();
		$data['currentPage'] = 1;
		$requestStr = $this->buildBodyXML($data,'getProduct');
		$result = $this->curlpost($requestStr,$this->url);
		return $result;
	}

	function placeOrder($orderCode,$supplyProductId,$number,$phone,$sendMessage=2){
		
		$data = array(
			"orderCode"			=> $orderCode,
			"supplyProductId"	=> $supplyProductId,
			"number"			=> $number,
			"phone"				=> $phone,
			"sendMessage"		=> $sendMessage,
		);
		$requestStr = $this->buildBodyXML($data,'placeOrder');
		$result  = $this->curlpost($requestStr,$this->url);
		return $result;
	}

	public function queryOrder($orderCode)
	{
		$data = array();
		$data['orderCode'] = $orderCode;
		$requestStr = $this->buildBodyXML($data,'queryOrder');
		$response   = $this->curlpost($requestStr,$this->url);
		return $response;
	}

	public function refundTicket($orderCode,$number,$refundWater){
		$data = array();
		$data['orderCode'] 	 = $orderCode;
		$data['number'] 	 = $number;
		$data['refundWater'] = $refundWater;
		$requestStr = $this->buildBodyXML($data,'refundTicket');
		$response = $this->curlpost($requestStr,$this->url);
		return $response;
	}

	public function resolveBackOrder($data)
	{
		$decrypt = $this->crypt->decrypt($data);
		$response_obj = simplexml_load_string($decrypt);
		return $response_obj;
	}

	function buildBodyXML($param,$method) {

		$xml_body = <<<STR_xml
<?xml version="1.0" encoding="UTF-8"?><body>
STR_xml;
	
		$xml_body_content = '';
		foreach ($param as $key => $value) {
			if(is_array($value)){
				$xml_body_content.="<$key>";
				foreach ($value as $key2 => $value2) {
						$xml_body_content.="<$key2>$value2</$key2>";
				}
				$xml_body_content.="</$key>";
			}else{
				$xml_body_content.="<$key>$value</$key>";
			}
		}
		$xml_body_content.="</body>";
		$xml_body_content = str_replace(" ", "", $xml_body_content);
		$xml_body_content = str_replace("\n", "", $xml_body_content);
		$xml_body_content = str_replace("\t", "", $xml_body_content);
		$xml_body_content = str_replace("\r", "", $xml_body_content);
		$xml_body.=$xml_body_content;

		$xml_body = str_replace("\n", "", $xml_body);
		$xml_body = str_replace("\t", "", $xml_body);
		$xml_body = str_replace("\r", "", $xml_body);
		$sign = base64_encode(md5($this->sequence.$this->agentCode.$this->key.$xml_body,true));
		$body = $this->crypt->encrypt($xml_body);
		$request_body = '<?xml version="1.0" encoding="UTF-8"?><request><head><version>'.$this->version.'</version><sequence>'.$this->sequence.'</sequence><agentCode>'.$this->agentCode.'</agentCode><interfaceCode>'.$this->interfaceCode.'</interfaceCode><method>'.$method.'</method><sign>'.$sign.'</sign></head><body>'.$body.'</body></request>';	

		return $request_body;
	}

	function curlpost($sendData,$postUrl) {
		$sendDataContent = array();
		$sendDataContent['xml'] = $sendData;
		$ch = curl_init ();
		curl_setopt ( $ch, CURLOPT_URL, $postUrl );
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt ( $ch, CURLOPT_POST, true );
		curl_setopt ( $ch, CURLOPT_POSTFIELDS, $sendDataContent );
		curl_setopt ( $ch, CURLOPT_TIMEOUT, 30 );
		curl_setopt ( $ch, CURLOPT_HEADER, 0 );
		$output = curl_exec ( $ch );
		curl_close ($ch);

		if($output)
		{
			$response = simplexml_load_string($output);
			$decrypt = $this->crypt->decrypt($response->body);
			$bodyObj =  simplexml_load_string($decrypt);
			return $bodyObj; 
		}else{
			return false;
		}

	}

}
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		