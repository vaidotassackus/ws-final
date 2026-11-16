# Paaiškinimas lietuviškai

## Kas tai yra

Tai nedidelė užduočių sąrašo **REST API**. API yra programa, kuri priima HTTP užklausas ir grąžina JSON. Pavyzdžiui, `POST /tasks` sukuria užduotį, o `GET /tasks` grąžina jų sąrašą. Ekrano sąsajos nėra, nes užduotis prašo žiniatinklio paslaugos, o ją galima išbandyti naršykle, Postman arba Swagger Editor.

Pasirinkau **PHP**, nes jį jau pažįsti. **SQLite** yra duomenų bazė viename faile; nereikia įdiegti MySQL ar konfigūruoti serverio. **Tai ne Gradle projektas:** jame nėra `build.gradle`, `settings.gradle` ar `gradlew`. Gradle skirtas kitiems projektų tipams, o šiam PHP projektui nereikia nei Gradle, nei Java, nei Composer. Reikia PHP 8.2 ar naujesnės versijos su `pdo_sqlite` ir `mbstring` plėtiniais.

## Kokia buvo dėstytojo užduotis

Trumpai: sukurti veikiančią žiniatinklio paslaugą, kuri priima HTTP užklausas, naudoja duomenų bazę ir grąžina JSON; ją aprašyti, išbandyti rankiniu būdu ir automatiniais testais. Sąlygose taip pat prašyta 2–5 žmonių komandos, paskirstytų vaidmenų, internetinės užduočių lentos, Git šakų, tvarkingo ir dokumentuoto kodo, vienetinių testų bei S.O.L.I.D. principų. Galima rinktis bet kurią programavimo platformą, todėl čia pasirinktas PHP.

Šiame projekte paslauga tvarko paprastą **užduočių sąrašą**. Viena užduotis turi ID, pavadinimą `title` ir požymį `done` (atlikta ar ne). API leidžia užduotį sukurti, pamatyti, pakeisti ir ištrinti. Duomenys saugomi SQLite faile. Funkcionalumas aprašytas `openapi.yaml`, paruošta Postman kolekcija ir automatiniai testai. Vieno žmogaus darbas negali įvykdyti 2–5 autorių reikalavimo — tai svarbu pasakyti pristatant.

## Projekto atidarymas PhpStorm

1. Paleisk **PhpStorm**. Pasirink **File → Open…** ir nurodyk aplanką `/Users/vaidotas/Projects/ws-final`. Atidaryk **patį `ws-final` aplanką**, o ne vieną PHP failą. Projektas jau yra šiame kompiuteryje, todėl jo klonuoti dar kartą nereikia.
2. Jei dirbtum kitame kompiuteryje, PhpStorm pradžios lange rinkis **Clone Repository**, įrašyk `https://github.com/vaidotassackus/ws-final.git` ir klonavimo vietą. Šiame kompiuteryje šį žingsnį praleisk.
3. Projektą atidaryk kaip įprastą aplanką; Gradle importo nereikia.

Šiame Mac PHP jau įdiegtas adresu `/opt/homebrew/bin/php`. PhpStorm meniu **View → Tool Windows → Terminal** atidaryk *PhpStorm vidinį terminalą*. Jo pradinis aplankas paprastai yra `ws-final`. Ten gali patikrinti:

```bash
php -v
php -m
```

Pirmoji komanda turi parodyti PHP versiją, antrosios sąraše turi būti `pdo_sqlite` ir `mbstring`. Jeigu PHP komanda nerandama, PhpStorm vidiniame terminale gali įvesti `brew install php` ir iš naujo atidaryti terminalo kortelę.

### Paleidimas PhpStorm mygtuku

PhpStorm jau turi PHP funkcijas. Papildinio diegti nereikia.

1. Atidaryk **Settings → PHP**. Prie **CLI Interpreter** pasirink vietinį PHP vykdomąjį failą `/opt/homebrew/bin/php` (jei jo sąraše nėra, spausk `…` → `+` → **Local**). Patikrink, kad PhpStorm rodo PHP versiją.
2. Pasirink **Run → Edit Configurations… → + → PHP Built-in Web Server**. Užpildyk: **Host** `127.0.0.1`, **Port** `8000`, **Document root** projekto `public` aplankas. Įjunk **Use router script** ir pasirink `public/index.php`. Pavadink konfigūraciją `Tasks API`, išsaugok.
3. Viršuje pasirink `Tasks API` ir spausk žalią **Run ▶**. PhpStorm **Run** lange turi matytis, kad serveris veikia. Naršyklėje atidaryk <http://127.0.0.1:8000/tasks>.
4. Stabdyk serverį raudonu **Stop ■** mygtuku PhpStorm **Run** lange.

Šio projekto maršrutams būtinas **Use router script** pasirinkimas. Vien tik `public/index.php` paleidimas kaip **PHP Script** neužkuria HTTP serverio. Serverio konfigūracijos laukai aprašyti [PhpStorm dokumentacijoje](https://www.jetbrains.com/help/phpstorm/run-debug-configuration-php-built-in-web-server.html).

### Atsarginis paleidimas PhpStorm terminale

Jei serverio konfigūracijos sukurti nepavyksta, atidaryk **View → Tool Windows → Terminal** ir tame PhpStorm vidiniame terminale įvesk:

```bash
php -S 127.0.0.1:8000 -t public public/index.php
```

Palik šią PhpStorm terminalo kortelę atvirą. Naršyklėje atidaryk <http://127.0.0.1:8000/tasks>. Sąraše matysi esamas užduotis arba tuščią `items` masyvą. Pirmą kartą automatiškai atsiras `data/tasks.sqlite` duomenų bazės failas. Sustabdymui terminale spausk `Ctrl+C`.

Jei 8000 prievadas užimtas, PhpStorm serverio konfigūracijos **Port** laukelyje pakeisk jį į 8001. Jei naudoji PhpStorm terminalą, komandoje pakeisk `:8000` į `:8001`. Tuomet Postman kolekcijoje ir Swagger apraše pakeisk serverio adresą į `http://127.0.0.1:8001`.

## Ką išbandyti

Pirmiausia paleisk serverį per PhpStorm. Tada **Project** lange rasi `openapi.yaml` ir `postman_collection.json`: šiuos failus naudos žemiau aprašyti įrankiai. Kodo keisti nereikia. HTTP būsenos: `201` reiškia sukurta, `200` sėkmingai perskaityta ar pakeista, `204` ištrinta, `404` nerasta, `400` blogas JSON, `422` netinkami laukai.

### Swagger Editor

1. Atidaryk <https://editor.swagger.io/>.
2. Pasirink **File → Import file** ir nurodyk PhpStorm atidaryto projekto failą `/Users/vaidotas/Projects/ws-final/openapi.yaml`.
3. Dešinėje matysi visus API kelius. Atidaryk `POST /tasks`, spausk **Try it out**, **Execute**. Paskui išbandyk `GET /tasks`.
4. API leidžia Swagger Editor svetainės CORS užklausas. Jei naršyklė vis tiek blokuoja ryšį su vietiniu serveriu, rezultatus gali patikrinti pateikta Postman kolekcija arba `curl`.

### Postman

1. Atidaryk Postman, pasirink **Import → Files** ir nurodyk PhpStorm atidaryto projekto failą `/Users/vaidotas/Projects/ws-final/postman_collection.json`.
2. Paleisk užklausas kolekcijoje iš eilės: **Sąrašas**, **Sukurti**, **Viena užduotis**, **Nepasikeitė (ETag)**, **Pakeisti**, **Senas ETag nebegalioja**, **Ištrinti**, **Ištrinta užduotis nerandama**.
3. Sukūrimo užklausa išsaugo ID į kolekcijos kintamąjį `taskId`, o skaitymo užklausa išsaugo `ETag`; kitų užklausų taisyti nereikia. Postman **Tests** skirtuke yra automatiniai būsenos kodų ir nuorodų patikrinimai.

## Testai

PhpStorm vidiniame terminale atidaryk **naują kortelę** ir joje paleisk:

```bash
php tests/run.php
```

Skriptas pats trumpam paleidžia **atskirą** HTTP serverį su laikina SQLite duomenų baze. Jis tikrina `TaskValidator`, `SqliteTaskRepository`, `TaskApi` klases ir tikras HTTP užklausas. Sėkmės atveju paskutinė eilutė yra `Visi testai praėjo.` Taip pat gali PhpStorm **Project** lange dešiniuoju pelės mygtuku spustelėti `tests/run.php` bei rinktis **Run**.

## Failai ir sprendimai

| Failas | Paskirtis |
| --- | --- |
| `public/index.php` | Vienintelis HTTP įėjimo taškas: sukuria objektus ir išsiunčia atsakymą. |
| `src/TaskStorage.php` | Saugyklos sąsaja, kad API nepriklausytų nuo SQLite. |
| `src/SqliteTaskRepository.php` | SQL lentelė ir duomenų skaitymas, įrašymas, keitimas, šalinimas. |
| `src/TaskValidator.php` | Tikrina JSON laukus. |
| `src/TaskApi.php` | Parenka kelią, HTTP metodą ir būsenos kodą. |
| `openapi.yaml` | API aprašas Swagger Editor. |
| `postman_collection.json` | Paruoštos Postman užklausos. |
| `tests/run.php` | Vienetiniai ir priėmimo testai. |

Klasės turi vieną pagrindinę atsakomybę, o `TaskApi` priklauso nuo `TaskStorage` sąsajos. Tai paprastas **S.O.L.I.D.** principų pritaikymas. PHP klasės ir metodai aprašyti **PHPDoc** komentarais. Iš jų sugeneruota `docs/PHPDOC.md` santrauka; ją atnaujinsi PhpStorm vidiniame terminale komanda `php tools/generate_docs.php`. JavaDoc yra tik Java kodo dokumentavimo priemonė ir PHP projektui netinka.

Duomenų bazės struktūra: `tasks(id INTEGER PRIMARY KEY, title TEXT NOT NULL, done INTEGER NOT NULL)`. Ji automatiškai sukuriama paleidžiant programą. SQL užklausose naudojami parametrai, todėl naudotojo įvestis nejungiama į SQL tekstą. Bazės failas ignoruojamas Git, kad asmeniniai duomenys nepatektų į repozitoriją.

## REST reikalavimai

- **Be sesijų (stateless):** kiekviena užklausa turi visą jai reikalingą informaciją; serveris nelaiko kliento sesijos.
- **Ištekliai ir metodai:** `/tasks` yra kolekcija, `/tasks/{id}` yra vienas išteklius; `GET`, `POST`, `PUT`, `DELETE` turi skirtingas paskirtis.
- **Podėlis (cacheable):** `GET` atsakymas turi `ETag` ir `Cache-Control`. Pakartojus užklausą su `If-None-Match`, nepasikeitę duomenys grąžina `304` be turinio.
- **Hiperteksto nuorodos:** atsakymų `_links` laukai nurodo, kur kreiptis dėl sąrašo, įrašo ar pakeitimo. Tai Richardson modelio 3 lygio požymis. Užduotyje parašytas „4 lygis“ yra klaida: [Richardson modelis apibrėžia 0–3 lygius](https://martinfowler.com/articles/richardsonMaturityModel.html).

## Kaip pristatyti dėstytojui (apie 6–8 min.)

### Pasiruošimas prieš pristatymą

1. PhpStorm atidaryk `/Users/vaidotas/Projects/ws-final`, pagal aukščiau esančius žingsnius paleisk `Tasks API` serverį ir naršyklėje patikrink <http://127.0.0.1:8000/tasks>. Tuščias sąrašas **nebūtinas** — duomenų bazėje gali būti ankstesnių įrašų.
2. Postman importuok **naujausią** `postman_collection.json` failą. Atidaryk [Swagger Editor](https://editor.swagger.io/) ir importuok `openapi.yaml`. Naršyklėje atsidaryk [GitHub repozitoriją](https://github.com/vaidotassackus/ws-final) ir [užduočių lentą](https://github.com/users/vaidotassackus/projects/1/views/1).
3. Nešalink `data/tasks.sqlite` vien dėl demonstracijos. Kolekcija pati sukuria naują įrašą, naudoja jo ID ir pabaigoje jį ištrina.

### Pristatymo eiga

1. **Įžanga.** Pasakyk: „Užduotis buvo sukurti REST žiniatinklio paslaugą su duomenų baze, API dokumentacija ir testais. Pasirinkau PHP bei SQLite, kad projektą būtų paprasta paleisti.“ Parodyk, kad tai **ne Gradle** projektas.
2. **Git ir užduočių lenta.** GitHub parodyk `master` šaką, `codex/task-api` šaką ir commit istoriją. Lentoje parodyk atliktus darbus. Aiškiai pasakyk, kad dirbai vienas, todėl 2–5 studentų ir kiekvieno atskirų commit sąlyga neįvykdyta.
3. **Failų sandara PhpStorm.** Parodyk `public/index.php` (priima HTTP užklausą), `src/TaskApi.php` (maršrutai), `src/TaskValidator.php` (įvesties tikrinimas), `src/SqliteTaskRepository.php` (SQLite užklausos). Parodyk `data/tasks.sqlite` failą, jei jis jau sukurtas. Paaiškink, kad `TaskApi` naudoja `TaskStorage` sąsają, todėl logika atskirta nuo DB — taip paprastai taikomi S.O.L.I.D. principai.
4. **Paleidimas ir Swagger.** PhpStorm viršuje parodyk veikiančią `Tasks API` konfigūraciją, kurios **Document root** yra `public`, o **router script** — `public/index.php`. Swagger Editor parodyk `GET /tasks`, `POST /tasks`, `GET /tasks/{id}`, `PUT /tasks/{id}`, `DELETE /tasks/{id}`. Jei leidžia naršyklė, iš Swagger išsiųsk `GET /tasks`.
5. **Postman demonstracija.** Vykdyk kolekcijos užklausas **iš eilės**. Parodyk `201` po **Sukurti**, `200` po **Viena užduotis**, JSON laukus ir `_links` nuorodas. **Nepasikeitė (ETag)** grąžina `304` be turinio; po **Pakeisti** senas ETag grąžina `200`, nes turinys pasikeitė. **Ištrinti** grąžina `204`, paskutinė užklausa — `404`.
6. **Automatiniai testai.** PhpStorm vidiniame terminale paleisk `php tests/run.php`; parodyk paskutinę eilutę `Visi testai praėjo.` Pasakyk, kad tikrinamos konkrečios klasės ir tikros HTTP užklausos su laikina duomenų baze.
7. **Pabaiga.** Parodyk `docs/PHPDOC.md` ir paaiškink, kad PHP projekto komentarai yra PHPDoc, o JavaDoc taikomas Java. Paminėk, kad API yra be sesijų, `GET` atsakymai turi ETag ir `_links`; [Richardson modelio](https://martinfowler.com/articles/richardsonMaturityModel.html) aukščiausias lygis yra 3, nors užduotyje parašyta 4.

### Jei paklaustų „kodėl taip?“

| Klausimas | Trumpas atsakymas |
| --- | --- |
| Kodėl PHP, o ne Java? | Sąlyga leidžia bet kurią platformą; PHP moku ir jam nereikia Gradle. |
| Kodėl SQLite? | Mokomajam projektui pakanka vieno duomenų bazės failo; nereikia atskiro DB serverio. |
| Ką reiškia REST? | Kiekviena užduotis turi adresą `/tasks/{id}`, o HTTP metodas nusako veiksmą. |
| Ką reiškia „stateless“? | API nelaiko kliento sesijos; kiekviena užklausa apdorojama pagal joje pateiktus duomenis. |
| Kam ETag? | Klientas gali paklausti, ar turinys pasikeitė; jei ne, serveris grąžina trumpą `304` atsakymą. |
| Kodėl nėra JavaDoc? | Tai PHP projektas; jo atitikmuo yra PHPDoc, o API aprašyta OpenAPI faile. |
| Ar visi sąlygų punktai įvykdyti? | Techniniai API ir testavimo punktai įgyvendinti; komandos 2–5 studentų punktas neįvykdomas dirbant vienam. |

## Komandos darbas ir Git

Užduotyje reikalaujama **2–5 studentų**, skirtingų vaidmenų ir kiekvieno autoriaus atskirų commit. Šiame projekte šiuo metu dirba **vienas žmogus**, todėl negaliu sąžiningai nurodyti kitų studentų ar sukurti jų commit. Galimas vaidmenų pasiskirstymas, kai atsiras komanda: kūrėjas – PHP ir duomenų bazė; QA – Postman bei automatiniai testai; analitikas – OpenAPI ir reikalavimų patikra. Vienas asmuo gali turėti kelis vaidmenis, bet reikia bent dviejų tikrų komandos narių, kad būtų įvykdyta ši sąlyga.

Kodas kurtas atskiroje `codex/task-api` šakoje ir sujungtas į `master`. [Viešoje GitHub Projects lentoje](https://github.com/users/vaidotassackus/projects/1/views/1) yra keturios su repozitorijos Issues susietos užduotys: trys atliktos, o antro komandos nario peržiūra laukia. Jei prisijungs kitas studentas, jis turėtų sukurti **savo šaką**, atlikti savo darbą ir sukurti **savo commit**, po to šaką sujungti į `master`.

## Kas šiame variante ribota

Tai mokomasis vietinis API: nėra naudotojų prisijungimo, viešo serverio ar grafinės sąsajos, nes užduočių CRUD demonstracijai jų nereikia. Jei dėstytojas reikalauja būtent Java programos ar tikro JavaDoc failo, reikia atskirai suderinti PHP pasirinkimą; užduoties tekste PHP tiesiogiai nepaminėtas, tačiau aiškiai leidžiama naudoti **bet kurią platformą**.
