<?php
//EXAMPLE USAGE OF CLASS
//REBOOTCODE SOFT S.R.L.
//2023
//my config data
$site_client_id='XXXXXXXXXXXXXXXXXXXXXXXXXXXXXx';
$site_client_secret='XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX';
$site_redirect_uri='https://example.com';
$filepath_UBI='/path/to/xml_file.xml';
$cif='RO111111';
include "anaf.class.php";
$anaf=new myAnaf($site_client_id,$site_client_secret,$site_redirect_uri,$filepath_UBI,$cif);

//GET TOKEN
$code=$_GET['code'];
if (isset($_GET['op']) && $_GET['op']=="gettoken" && empty($code)){
	$anaf->AuthorizeAnaf();
}
if (!empty($code)) {
	$retval=$anaf->getTokenAnaf($code);
	$token=$retval['access_token'];
	$refresh_token=$retval['refresh_token'];
	//SAVE TOKEN HERE
}
//refresh token
if (isset($_GET['op']) && $_GET['op']=="refreshtoken"){
	$retval=$anaf->refreshTokenTokenAnaf($refresh_token); //USE REFRESH TOKEN FROM PREVIOUS STEP
	$token=$retval['access_token'];
	$refresh_token=$retval['refresh_token'];
	//SAVE TOKEN HERE
}
//CREATE INVOICE - GENERATE XML INVOICE UBI AND SAVE
$factura_data=array();
$fact_data[0]['numar_factura']='ABC 001';
$fact_data[0]['data_factura']='12.11.2023';
//etc
$anaf->CreateUBI($factura_data,$is_firma_tva,$total_fara_tva,$total_tva,$total_cu_tva,$tva);

//UPLOAD INVOICE	
if (isset($_GET['op']) && $_GET['op']=="uploadfact"){
	$fname=$filepath_UBI; //FILENAME OF XML
	//OPEN FILE, READ DATA
	$fullfile=$fname;
	$file = fopen($fullfile, "r");
	$data = fread($file, filesize($fullfile));
	fclose($file);
	$invoice_id=$anaf->uploadUBIAnaf($token,$data); //INVOICE ID
}
//GET STATUS 
$retval=$anaf->statusUBIAnaf($token,$invoice_id);
$status=$retval['status'];
$id_download= $retval['id'];
//DOWNLOAD ERROR
$datafile=$anaf->downloadUBIAnaf($token,$id_download);
//SAVE DATA IN FILE
$fp = fopen($id_descarcare.'.zip', 'w');
fwrite($fp, $datafile);
fclose($fp);
//UNZIP FILE
$zip = new ZipArchive;
$res = $zip->open($id_descarcare.'.zip');
if ($res === TRUE) {
    $zip->extractTo('ubl/');
    $zip->close();
}
//READ ERROR FROM FILE
$error = file_get_contents($subl_id.'.xml');
//get last anaf messages
$retval=$anaf->GetLastmsgAnaf($token);
    foreach ($retval as $a=>$b){
        foreach ($b as $xa=>$xb){
			$id_solicitare=$xb->id_solicitare;
			$id_intern=$xb->id;
			$detalii=$xb->detalii;
			$tip=$xb->tip;
			if ($xb->tip=="FACTURA PRIMITA"){
				//daca am primit noi o factura
				$datafile=$anaf->downloadUBIAnaf($token,$id_intern);
				$fp = fopen($id_intern.'.zip', 'w');
				fwrite($fp, $datafile);
				fclose($fp);
				//unpack file?
				$zip = new ZipArchive;
				$res = $zip->open($id_intern.'.zip');
				if ($res === TRUE) {
				  $zip->extractTo('ubl/');
				  $zip->close();
				}
				$fact = file_get_contents($id_solicitare.'.xml');
				//download pdf
				$fullfile=$id_solicitare.'.xml';
				$file = fopen($fullfile, "r");
				$data = fread($file, filesize($fullfile));
				fclose($file);
				$retval_pdf=$anaf->ConvertXmlToPdf($data);
				$fp = fopen($id_solicitare.'.pdf', 'w');
				fwrite($fp, $retval_pdf);
				fclose($fp);

			}

            
        }
        
    }
//UBL PARSER
include 'ublparser.php';
$xml = file_get_contents('Path of Invoice UBL (XML) File');
$parser = new UBLParser;
$parser->set($xml);
$result = $parser->get();
print_r($result);
?>