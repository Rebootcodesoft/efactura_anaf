<?php
class myAnaf {
	private $client_id;
	private $client_secret;
	private $redirect_uri;
	private $authorize_url;
	private $token_url;
	private $upload_url;
	private $status_url;
	private $download_url;

	function __construct($client_idi,$client_secreti,$redirect_urii){
		$this->client_id=$client_idi;
		$this->client_secret=$client_secreti;
		$this->redirect_uri=$redirect_urii;
		$this->authorize_url='https://logincert.anaf.ro/anaf-oauth2/v1/authorize';
		$this->token_url='https://logincert.anaf.ro/anaf-oauth2/v1/token';
		//TEST URL - CHANGE IT IN PRODUCTION
		$this->upload_url='https://api.anaf.ro/test/FCTEL/rest/upload?standard=UBL&cif=';
		$this->status_url='https://api.anaf.ro/test/FCTEL/rest/stareMesaj?id_incarcare=';
		$this->download_url='https://api.anaf.ro/test/FCTEL/rest/descarcare?id=';
	}
	function AuthorizeAnaf(){
		$url = $this->authorize_url;
		$url .='?client_id='.$this->client_id;
		$url .='&client_secret='.$this->client_secret;
		$url .='&response_type=code';
		$url .='&redirect_uri='.$this->redirect_uri;
		header('Location: '.$url);
	}
	function getTokenAnaf($code){
		$retval=array();
		$url = $this->token_url;
		$fields = [
			'client_id'      => $this->client_id,
			'client_secret' => $this->client_secret,
			'code'         => $code,
			'redirect_uri'	=> $this->redirect_uri,
			'grant_type' => 'authorization_code'
		];
		$fields_string = http_build_query($fields);
		$ch = curl_init();
		curl_setopt($ch,CURLOPT_URL, $url);
		curl_setopt($ch,CURLOPT_POST, true);
		curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER, true); 
		$jsonobj = curl_exec($ch);
		$arr = json_decode($jsonobj, true);
		$retval['access_token']=$arr["access_token"];
		$retval['refresh_token']=$arr["refresh_token"];
		return $retval;
	}
	function refreshTokenTokenAnaf($refresh_token){
		$retval=array();
		$url = $this->token_url;
		$fields = [
			'client_id'      => $this->client_id,
			'client_secret' => $this->client_secret,
			'refresh_token' => $refresh_token,
			'redirect_uri'	=> $this->redirect_uri,
			'grant_type' => 'refresh_token'
		];
		$fields_string = http_build_query($fields);
		$ch = curl_init();
		curl_setopt($ch,CURLOPT_URL, $url);
		curl_setopt($ch,CURLOPT_POST, true);
		curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER, true); 
		$jsonobj = curl_exec($ch);
		$arr = json_decode($jsonobj, true);
		$retval['access_token']=$arr["access_token"];
		$retval['refresh_token']=$arr["refresh_token"];
		return $retval;
	}
	function uploadUBIAnaf($token,$cif,$xml){
		$url = $this->upload_url.$cif;
		$headr = array();
		$headr[] = 'Authorization: Bearer '.$token;
		$headr[] = 'Content-Type: text/plain';
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS,$xml); 
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headr);
		$server_output = curl_exec($ch);
		curl_close ($ch);
		$xml = json_encode(simplexml_load_string($server_output));
		$json=json_decode($xml);
		foreach ($json as $a=>$b){
			return $b->index_incarcare;
		}
	}
	function statusUBIAnaf($token,$fact_id){
		$retval=array();
		$url = $this->status_url.$fact_id; 
		$headr = array();
		$headr[] = 'Authorization: Bearer '.$token;
		$headr[] = 'Content-Type: text/plain';
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_POST, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headr);
		$server_output = curl_exec($ch);
		curl_close ($ch);
		$xml = json_encode(simplexml_load_string($server_output));
		$json=json_decode($xml);
		foreach ($json as $a=>$b){
			$retval['id']= $b->id_descarcare;
			$retval['status']= $b->stare;
		}
		return $retval;
	}
	function downloadUBIAnaf($token,$down_id){
		$retval=array();
		$url = $this->download_url.$down_id; 
		$headr = array();
		$headr[] = 'Authorization: Bearer '.$token;
		$headr[] = 'Content-Type: text/plain';
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_POST, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headr);
		$server_output = curl_exec($ch);
		curl_close ($ch);
		return $server_output;
	}
}
?>