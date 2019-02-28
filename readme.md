Plzeňský barcamp – Web
======================

Požadavky
---------

PHP 7.2 nebo vyšší.


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

