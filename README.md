##IngatlanRobot

Keresési feltételek alapján összegyűjti naponta 2 alkalommal a legfrissebb 
ingatlan hirdetéseket, valamint emailben értesítést küld. A projekt célja, hogy
ingatlan keresés esetén ne kelljen egyesével átnezni a hirető portálokat, valamint
időben értesülhessünk a számunkra megfelelő új hirdetésekről

> **v.0.3.0:**
>
> - Új neve van a robotnak: Mikrobi
> - Env fix, email fixek

> **v.0.2.3:**
>
> - Publikus stabil verzió

> **TODO/Roadmap:**
> - További ingatlanos oldalak integrációja
> - Details / Részletek
> - Hozzáadás kedvencekhez

> **Függőségek:**
> - Composer

Telepítés:
```
composer install
```

- Hozzuk létre az adatbázist a db.sql alapján
- Az env/.env.sample file-ban állítsuk be a konfigurációs értékeket, majd:

```
cd env
cp .env.sample .env
```

- A keresési feltételeknek megfelelő linkeket az ingatlan.php $links tömbjében állíthatjuk be:

```
    $links = array(
        'https://ingatlan.jofogas.hu/pest/budakalasz+dunakeszi+piliscsaba+pilisvorosvar+pomaz+szentendre/haz?max_price=26000000&min_size=60&st=s',
        'https://ingatlan.com/lista/elado+telek+pest-megye-buda-kornyeke+8-mFt-ig',
        'https://koltozzbe.hu/elado-csaladi_haz+ikerhaz+sorhaz+telek+nyaralo-budapest+pomaz+szentendre+dunakeszi+pilisvorosvar+piliscsaba?p2=25000000&order=2'
    );
```

CRON beállítás:
```
php ~/web/host/public_html/ingatlan.php

Min
0Hour
*/12Day
*Month
*Day of week
*
```

Adatbázis ütemezett törlése (nem kötelező):
```
php ~/web/host/public_html/ingatlan.php --flush

Min
0Hour
23Day
31Month
*Day of week
*
```

Jelenleg támogatott portálok:
- ingatlan.com
- ingatlan.jofogas.hu
- koltozzbe.hu

Szigorúan magáncélú felhasznlásra! Üzleti felhasználás esetén NEM vállalok felelősséget!

Fejlesztés / Közreműködés
-------------------

Amennyiben fejlesztőként szeretnél csatlakozni a modul fejlesztéséhez, kérjük a develop branchet checkoutold ki, majd a fejlesztéseid visszavezetéséhez nyiss egy új pull-requestet!

Amennyiben hibát találtál, vagy fejlesztési ötleted/igényed van, kérjük jelezd ezt új Issue formájában!