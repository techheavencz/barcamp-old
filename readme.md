Plzeňský barcamp – Web
======================

Požadavky
---------

- PHP 7.2 nebo vyšší
- MySQL 5+
- [Composer](https://getcomposer.org/)


Instalace na Localhost
----------------------

Pro spuštění na localhostu potřebujete [Docker](https://www.docker.com/). Ten je potřeba mít nainstalovaný a spuštěný.

Pro nastarování aplikace zavolejte v rootu aplikace:
```
docker-composer up -d
```

Po spuštění bude aplikace dostupná na URL: http://localhost:8080/

V konfiguraci Dockeru je nastavení webového serveru i MySQL serveru, vše je nakonfigurováno. Při zcela prvním spuště je
potřeba ještě několik úkonů:

1. Nainstalujte závislosti Composeru (`composer install`)
2. vytvořte soubor `/local/config.local.neon` – klidně prázdný,
3. vytvořte v databázi tabulky, které aplikace potřebuje (konfigurace bude doplněna…)

První instalace na Server
-------------------------

Webový server musí číst soubory z adresáře `/www`.

Pokud není možné nastavit přímo `DocumentRoot`, např. na webhostingu Wedos,
použijte soubor `.htaccess.wedos`, tento přejmenujte na `.htaccess`, a nahrajte do aktuální složky `DocumentRoot` na
hostingu. V takovém případě pak nahrajte aplikaci do podadresáře `/plzenskybarcamp.cz/` v `DocumentRoot`.  

Deploy nové verze
-----------------

Pro průběžný deploy je vhodné použít připravený nástroj `ftp-deployment`. Jeho použití je snadné, jen zavoláním příkazu:
```
php vendor/bin/deployment deployment.php

```
Volání musí být provedeno z kořenového adresáře! 


**Před prvním spuštěním** je ovšem nezbytné nastavit `user` a `password` na FTP, které není uloženo v repozitáři.
Vytvořte soubor `/local/deployment.local.php`, který bude obsahovat kód:

```php
<?php

return [
    'user' => '',
    'password' => '',
];
```
Do hodnot vyplňte přihlašovací údaje na FTP.

Pro náhled seznamu souborů, které se liší (a budou tedy deployovány) zavolejte výše uvedený příkaz s modifikátorem `-t`,
tedy:
```
php vendor/bin/deployment deployment.php -t

```
[**Více informací o nástroji FTP Deployment**](https://github.com/dg/ftp-deployment#readme)

### POZOR!
Pokud na serveru vytváříte soubory dynamicky (např. uložené soubory, která do aplikace nahraje uživatel), je potřeba
takto používaný adresář vyloučit z deploye, **jinak dojde k jeho smazání!** 

Vyloučení souboru nebo adresáře se provede v `/deployment.php` přidáním do pole `ignore`.
