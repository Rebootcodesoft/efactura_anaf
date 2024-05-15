# RO E-FACTURA ANAF
Incarcare e-factura in SPV Anaf, generare e-factura XML varianta UBI, descarcare mesaje din SPV, ublparser

### Date necesare:

Aplicatia trebuie inregistrata la Anaf. Documentatia pentru inregistrare aplicatie in sistem:

https://static.anaf.ro/static/10/Anaf/Informatii_R/API/Oauth_procedura_inregistrare_aplicatii_portal_ANAF.pdf

Dupa inregistrare se poate obtine variabilele site_client_id si site_client_secret.

```php
$site_client_id='XXXXXXXXXXXXXXXXXXXXXXXXXXXXXx';
$site_client_secret='XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX';
$site_redirect_uri='https://example.com';
$filepath_UBI='/path/to/xml_file.xml';
$cif='RO111111';
```

### Utilizare:

```php
include "anaf.class.php";
$anaf=new myAnaf($site_client_id,$site_client_secret,$site_redirect_uri,$filepath_UBI,$cif);
```

Obtinere token de la Anaf:

```php
//primul pas necesita dispozitivul de semnatura digitala
$anaf->AuthorizeAnaf();
//dupa autorizare veti primi un cod prin GET
$code=$_GET['code'];
$retval=$anaf->getTokenAnaf($code);
$token=$retval['access_token'];
$refresh_token=$retval['refresh_token'];
```
Exemplu incarcare factura xml in SPV:

```php
$fname=$filepath_UBI; //FILENAME OF XML
//OPEN FILE, READ DATA
$fullfile=$fname;
$file = fopen($fullfile, "r");
$data = fread($file, filesize($fullfile));
fclose($file);
$invoice_id=$anaf->uploadUBIAnaf($token,$data); //INVOICE ID
```

Mai multe exemple gasiti in fisierul test.php

### Update

Update mai 2024
- adaugare mod testare in anaf class.

Update martie 2024
- modificare generare xml UBI cu setari TVA
- introducerea cod fiscal propriu in anaf class
- separare url test si productie

Update februarie 2024
- Adaugat: ublparser.php
- Am adaugat un UBL parser, creat pe baza: https://github.com/ahmeti/ubl-parser-php/tree/master
- Am facut niste modificari pentru e-factura.

Update ianuarie 2024
- get mesages from Anaf
- vizualizare e-facturi primite
- generare pdf din e-factura primita
