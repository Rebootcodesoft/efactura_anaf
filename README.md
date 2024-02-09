# RO E-FACTURA ANAF
Incarcare e-factura in SPV Anaf
Generare e-factura XML varianta UBI

Documentatia pentru inregistrare aplicatie in sistem:
https://static.anaf.ro/static/10/Anaf/Informatii_R/API/Oauth_procedura_inregistrare_aplicatii_portal_ANAF.pdf

Dupa inregistrare se poate obtine variabilele site_client_id si site_client_secret.

Generare xml UBI - trebuie modificat, personalizat, aceasta varianta este pentru firme neplatitori de TVA.

Update februarie 2024
Adaugat: ublparser.php
Am adaugat un UBL parser, creat pe baza: https://github.com/ahmeti/ubl-parser-php/tree/master
Am facut niste modificari pentru e-factura.


```php
include 'ublparser.php';


$xml = file_get_contents('Path of Invoice UBL (XML) File');


$parser = new UBLParser;

$parser->set($xml);

$result = $parser->get();

print_r($result);

```

Update ianuarie 2024
- get mesages from Anaf
- vizualizare e-facturi primite
- generare pdf din e-factura primita
