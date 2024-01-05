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
	private $ubi_file_path;
	private $xmltopdf_url;
	private $mess_url;

	function __construct($client_idi,$client_secreti,$redirect_urii){
		$this->client_id=$client_idi;
		$this->client_secret=$client_secreti;
		$this->redirect_uri=$redirect_urii;
		$this->ubi_file_path=$ubi_file_path;
		$this->authorize_url='https://logincert.anaf.ro/anaf-oauth2/v1/authorize';
		$this->token_url='https://logincert.anaf.ro/anaf-oauth2/v1/token';
		//TEST URL - CHANGE IT IN PRODUCTION
		$this->upload_url='https://api.anaf.ro/test/FCTEL/rest/upload?standard=UBL&cif=';
		$this->status_url='https://api.anaf.ro/test/FCTEL/rest/stareMesaj?id_incarcare=';
		$this->download_url='https://api.anaf.ro/test/FCTEL/rest/descarcare?id=';
		$this->xmltopdf_url='https://webservicesp.anaf.ro/prod/FCTEL/rest/transformare/FACT1/DA';
		$this->mess_url='https://api.anaf.ro/prod/FCTEL/rest/listaMesajeFactura?zile=5&cif=';
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
	function GetLastmsgAnaf($token,$cif){
		$retval=array();
		$url = $this->mess_url.$cif;
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
		$arr = json_decode($server_output);
		return $arr;
	}
	function ConvertXmlToPdf($xml){
		$url = $this->xmltopdf_url;
		$headr = array();
		$headr[] = 'Authorization: Bearer '.$token;
		$headr[] = 'Content-Type: text/plain';
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_POST, 0);
		curl_setopt($ch, CURLOPT_POSTFIELDS,$xml); 
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headr);
		$server_output = curl_exec($ch);
		curl_close ($ch);
		return $server_output;
	}
	function CreateUBI($fact_data){
		//model creare UBI pentru factura cu TVA 0
		$x=new XMLWriter();
		$filename=$this->ubi_file_path;
		$x->openURI($filename);
		$x->startDocument('1.0','UTF-8','yes');
		$x->setIndent(true);
		$x->startElement('Invoice');
		$x->writeAttribute(
			'xmlns',
			'urn:oasis:names:specification:ubl:schema:xsd:Invoice-2'
		);
		$x->writeAttribute(
			'xmlns:cbc',
			'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2'
		);
		$x->writeAttribute(
			'xmlns:cac',
			'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2'
		);
		$x->writeAttribute(
			'xmlns:ns4',
			'urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2'
		);
		$x->writeAttribute(
			'xmlns:xsi',
			'http://www.w3.org/2001/XMLSchema-instance'
		);
		$x->writeAttribute(
			'xsi:schemaLocation',
			'urn:oasis:names:specification:ubl:schema:xsd:Invoice-2 http://docs.oasis-open.org/ubl/os-UBL-2.1/xsd/maindoc/UBL-Invoice-2.1.xsd'
		);
		$x->startElement('cbc:CustomizationID');
		$x->text('urn:cen.eu:en16931:2017#compliant#urn:efactura.mfinante.ro:CIUS-RO:1.0.1');
		$x->endElement();
		$x->startElement('cbc:ID');
		$x->text($fact_data[0]['numar_factura']);
		$x->endElement();
		$x->startElement('cbc:IssueDate');$x->text($fact_data[0]['data_factura']);$x->endElement();
		$x->startElement('cbc:DueDate');$x->text($fact_data[0]['data_valabilitate_factura']);$x->endElement();
		$x->startElement('cbc:InvoiceTypeCode');$x->text('380');$x->endElement();
		$x->startElement('cbc:DocumentCurrencyCode');$x->text('RON');$x->endElement();
		$x->startElement('cac:AccountingSupplierParty');
			$x->startElement('cac:Party');
				$x->startElement('cac:PostalAddress');
					$x->startElement('cbc:StreetName');$x->text($fact_data[0]['adresa_firma']);$x->endElement();
					$x->startElement('cbc:CityName');$x->text($fact_data[0]['oras_firma']);$x->endElement();
					$x->startElement('cbc:PostalZone');$x->text($fact_data[0]['codul_postal_firma']);$x->endElement();
					$x->startElement('cbc:CountrySubentity');$x->text($fact_data[0]['cod_judet_firma']);$x->endElement();
					$x->startElement('cac:Country');
						$x->startElement('cbc:IdentificationCode');$x->text('RO');$x->endElement();
					$x->endElement();
				$x->endElement();
		
				$x->startElement('cac:PartyLegalEntity');
					$x->startElement('cbc:RegistrationName');$x->text($fact_data[0]['nume_firma']);$x->endElement();
					$x->startElement('cbc:CompanyID');$x->text($fact_data[0]['codul_fiscal_firma']);$x->endElement();
				$x->endElement();
			$x->endElement();
		$x->endElement();
		
		$x->startElement('cac:AccountingCustomerParty');
			$x->startElement('cac:Party');
				$x->startElement('cac:PostalAddress');
					$x->startElement('cbc:StreetName');$x->text($fact_data[0]['adresa_client']);$x->endElement();
					$x->startElement('cbc:CityName');$x->text($fact_data[0]['oras_client']);$x->endElement();
					$x->startElement('cbc:PostalZone');$x->text($fact_data[0]['codul_postal_client']);$x->endElement();
					$x->startElement('cbc:CountrySubentity');$x->text($fact_data[0]['cod_judet_client']);$x->endElement();
					$x->startElement('cac:Country');
						$x->startElement('cbc:IdentificationCode');$x->text('RO');$x->endElement();
					$x->endElement();
				$x->endElement();
				$x->startElement('cac:PartyLegalEntity');
					$x->startElement('cbc:RegistrationName');$x->text($fact_data[0]['numefirma_client']);$x->endElement();
					$x->startElement('cbc:CompanyID');$x->text($fact_data[0]['codul_fiscal_client']);$x->endElement();
				$x->endElement();
			$x->endElement();
		$x->endElement();
		$x->startElement('cac:PaymentMeans');
			$x->startElement('cbc:PaymentMeansCode');$x->text('1');$x->endElement();
			$x->startElement('cac:PayeeFinancialAccount');
				$x->startElement('cbc:ID');$x->text($fact_data[0]['cod_iban_firma']);$x->endElement(); 
			$x->endElement();
		$x->endElement();
		$x->startElement('cac:TaxTotal');
			$x->startElement('cbc:TaxAmount');
			$x->writeAttribute(
				'currencyID',
				'RON'
			);
			$x->text('0.00');
			$x->endElement();
			$x->startElement('cac:TaxSubtotal');
				$x->startElement('cbc:TaxableAmount');
				$x->writeAttribute(
					'currencyID',
					'RON'
				);
				$x->text($fact_data[0]['total']);
				$x->endElement();
				$x->startElement('cbc:TaxAmount');
				$x->writeAttribute(
					'currencyID',
					'RON'
				);
				$x->text('0.00');
				$x->endElement();
				$x->startElement('cac:TaxCategory');
					$x->startElement('cbc:ID');$x->text('O');$x->endElement();
						$x->startElement('cbc:Percent');$x->text('0.00');$x->endElement();
			  $x->startElement('cbc:TaxExemptionReasonCode');$x->text('VATEX-EU-O');$x->endElement();
					$x->startElement('cac:TaxScheme');
						$x->startElement('cbc:ID');$x->text('VAT');$x->endElement();
					$x->endElement();
				$x->endElement();
			$x->endElement();
		$x->endElement();	
		$x->startElement('cac:LegalMonetaryTotal');
			$x->startElement('cbc:LineExtensionAmount');
			$x->writeAttribute(
				'currencyID',
				'RON'
			);
			$x->text($totals);
			$x->endElement();
			$x->startElement('cbc:TaxExclusiveAmount');
			$x->writeAttribute(
				'currencyID',
				'RON'
			);
			$x->text($totals);
			$x->endElement();
			$x->startElement('cbc:TaxInclusiveAmount');
			$x->writeAttribute(
				'currencyID',
				'RON'
			);
			$x->text($totals);
			$x->endElement();
			$x->startElement('cbc:PayableAmount');
			$x->writeAttribute(
				'currencyID',
				'RON'
			);
			$x->text($totals);
			$x->endElement();
		$x->endElement();
		
		$count = count($fact_data[0]['factura_randuri']);
		$ii=1;
		for ($i = 0; $i < $count; $i++) {
			$x->startElement('cac:InvoiceLine');
			$x->startElement('cbc:ID');$x->text($ii);$x->endElement();
			$x->startElement('cbc:InvoicedQuantity');
			$x->writeAttribute(
				'unitCode',
				'H87'
			);
			$x->text($fact_data[0]['factura_randuri'][$i]['cantitate']);
			$x->endElement();
			$x->startElement('cbc:LineExtensionAmount');
			$x->writeAttribute(
				'currencyID',
				'RON'
			);
			$x->text($fact_data[0]['factura_randuri'][$i]['valoare']);
			$x->endElement();
			$x->startElement('cac:Item');
				$x->startElement('cbc:Name');$x->text($fact_data[0]['factura_randuri'][$i]['denumire']);$x->endElement();
		
				$x->startElement('cac:ClassifiedTaxCategory');
					$x->startElement('cbc:ID');$x->text('O');$x->endElement();
					$x->startElement('cac:TaxScheme');
						$x->startElement('cbc:ID');$x->text('VAT');$x->endElement();
					$x->endElement();
				$x->endElement();
			$x->endElement();
			$x->startElement('cac:Price');
				$x->startElement('cbc:PriceAmount');
				$x->writeAttribute(
					'currencyID',
					'RON'
				);
				$x->text($fact_data[0]['factura_randuri'][$i]['valoare']);
				$x->endElement();
			$x->endElement();
		$x->endElement();
		$ii++;
		}
		$x->endElement(); // root
		$x->endDocument();
	}
}
?>