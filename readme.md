Plzeňský barcamp – Web
======================

Požadavky
---------

- PHP 7.2 nebo vyšší
- MySQL 5+


Instalace na Localhost
----------------------

Pro spuštění na localhostu potřebujete [Docker](https://www.docker.com/). Ten je potřeba mít naisntalovaný a spuštěný.

Pro nastarování aplikace zavolejte v rootu aplikace:
```
docker-composer up -d
```

Po spuštění bude aplikace dostupná na URL: http://localhost:8080/

V konfiguraci Dockeru je nastavení webového serveru i MySQL serveru, vše je nakonfigurováno. Při zcela prvním spuště je
potřeba ještě několik úkonů:

1. vytvořte soubor `/app/config/config.local.neon` – klidně prázdný,
2. vytvořte v databázi tabulky, které aplikace potřebuje (konfigurace bude doplněna…)

Instalace na server
-------------------

Webový server musí číst soubory z adresáře `/www`.

Pokud není možné nastavit přímo `DocumentRoot`, např. na webhostingu Wedos,
použijte soubor `.htaccess.wedos`, tento přejmenujte na `.htaccess`, a nahrajte do aktuální složky `DocumentRoot` na hostingu.
V takovém případě pak nahrajte aplikaci do podadresáře `/plzenskybarcamp.cz/` v `DocumentRoot`.  