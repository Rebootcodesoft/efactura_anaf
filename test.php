<?php
//EXAMPLE USAGE OF CLASS
//REBOOTCODE SOFT S.R.L.
//2023
//my config data
$site_client_id='XXXXXXXXXXXXXXXXXXXXXXXXXXXXXx';
$site_client_secret='XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX';
$site_redirect_uri='https://example.com';

include "anaf.class.php";
$anaf=new myAnaf($site_client_id,$site_client_secret,$site_redirect_uri);

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
//UPLOAD INVOICE	
if (isset($_GET['op']) && $_GET['op']=="uploadfact"){
	//STEP 1 - GENERATE XML INVOICE UBI AND SAVE
	$fname=$_GET['fname']; //FILENAME OF XML
	//OPEN FILE, READ DATA
	$fullfile=$fname;
	$file = fopen($fullfile, "r");
	$data = fread($file, filesize($fullfile));
	fclose($file);
	$invoice_id=$anaf->uploadUBIAnaf($token,'YOUR-cif',$data); //INVOICE ID
}
//GET STATUS 
$retval=$anaf->statusUBIAnaf($token,$invoice_id);
$status=$retval['status'];
$id_download= $retval['id'];
//DOWNLOAD ERROR
$datafile=$anaf->downloadUBIAnaf($token,$id_download);
//SAVE DATA IN FILE
$fp = fopen($global_dir.'ubl/'.$id_descarcare.'.zip', 'w');
fwrite($fp, $datafile);
fclose($fp);
//UNZIP FILE
$zip = new ZipArchive;
$res = $zip->open($global_dir.'ubl/'.$id_descarcare.'.zip');
if ($res === TRUE) {
    $zip->extractTo($global_dir.'ubl/');
    $zip->close();
}
//READ ERROR FROM FILE
$error = file_get_contents($global_dir.'ubl/'.$subl_id.'.xml');
?>