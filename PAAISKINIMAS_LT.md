# Paaiškinimas lietuviškai

## Kas tai yra

Tai nedidelė užduočių sąrašo **REST API**. API yra programa, kuri priima HTTP užklausas ir grąžina JSON. Pavyzdžiui, `POST /tasks` sukuria užduotį, o `GET /tasks` grąžina jų sąrašą. Ekrano sąsajos nėra, nes užduotis prašo žiniatinklio paslaugos, o ją galima išbandyti naršykle, Postman arba Swagger Editor.

Pasirinkau **PHP**, nes jį jau pažįsti. **SQLite** yra duomenų bazė viename faile; nereikia įdiegti MySQL ar konfigūruoti serverio. **Tai ne Gradle projektas:** jame nėra `build.gradle`, `settings.gradle` ar `gradlew`. Gradle skirtas kitiems projektų tipams, o šiam PHP projektui nereikia nei Gradle, nei Java, nei Composer. Reikia PHP 8.2 ar naujesnės versijos su `pdo_sqlite` ir `mbstring` plėtiniais.

## Projekto atidarymas IntelliJ IDEA

1. Paleisk **IntelliJ IDEA**. Pasirink **File → Open…** ir nurodyk aplanką `/Users/vaidotas/Projects/ws-final`. Atidaryk **patį `ws-final` aplanką**, o ne vieną PHP failą. Projektas jau yra šiame kompiuteryje, todėl jo klonuoti dar kartą nereikia.
2. Jei dirbtum kitame kompiuteryje, IntelliJ pradžios lange rinkis **Clone Repository** (arba **File → New → Project from Version Control**), įrašyk `https://github.com/vaidotassackus/ws-final.git` ir klonavimo vietą. Šiame kompiuteryje šį žingsnį praleisk.
3. Jei IntelliJ klausia, ar importuoti projektą kaip **Gradle**, nesirink Gradle importo: atidaryk kaip įprastą aplanką.

Šiame Mac PHP jau įdiegtas adresu `/opt/homebrew/bin/php`. IntelliJ meniu **View → Tool Windows → Terminal** atidaryk *IntelliJ vidinį terminalą*. Jo pradinis aplankas paprastai yra `ws-final`. Ten gali patikrinti:

```bash
php -v
php -m
```

Pirmoji komanda turi parodyti PHP versiją, antrosios sąraše turi būti `pdo_sqlite` ir `mbstring`. Jeigu PHP komanda nerandama, IntelliJ vidiniame terminale gali įvesti `brew install php` ir iš naujo atidaryti terminalo kortelę.

### Patogiausias paleidimas su PHP papildiniu

[JetBrains nurodo](https://www.jetbrains.com/help/idea/php-specific-guidelines.html), kad PHP papildinys skirtas **IntelliJ IDEA Ultimate** ir nėra įdiegtas pagal nutylėjimą. Jei turi šią versiją:

1. IntelliJ atidaryk **Settings → Plugins → Marketplace**, paieškoje įrašyk **PHP**, įdiek papildinį ir, jei paprašys, perkrauk IntelliJ.
2. Atidaryk **Settings → PHP**. Prie **CLI Interpreter** pasirink vietinį PHP vykdomąjį failą `/opt/homebrew/bin/php` (jei jo sąraše nėra, spausk `…` → `+` → **Local**). Patikrink, kad IntelliJ rodo PHP versiją.
3. Pasirink **Run → Edit Configurations… → + → PHP Built-in Web Server**. Užpildyk: **Host** `127.0.0.1`, **Port** `8000`, **Document root** projekto `public` aplankas. Įjunk **Use router script** ir pasirink `public/index.php`. Pavadink konfigūraciją `Tasks API`, išsaugok.
4. Viršuje pasirink `Tasks API` ir spausk žalią **Run ▶**. IntelliJ **Run** lange turi matytis, kad serveris veikia. Naršyklėje atidaryk <http://127.0.0.1:8000/tasks>.
5. Stabdyk serverį raudonu **Stop ■** mygtuku IntelliJ **Run** lange.

Šio projekto maršrutams būtinas **Use router script** pasirinkimas. Vien tik `public/index.php` paleidimas kaip **PHP Script** neužkuria HTTP serverio. Serverio konfigūracijos laukai aprašyti [JetBrains dokumentacijoje](https://www.jetbrains.com/help/phpstorm/run-debug-configuration-php-built-in-web-server.html).

### Jei PHP papildinio nėra

Vis tiek gali naudoti **IntelliJ IDEA**: atidaryk **View → Tool Windows → Terminal** ir tame vidiniame terminale įvesk:

```bash
php -S 127.0.0.1:8000 -t public public/index.php
```

Palik šią IntelliJ terminalo kortelę atvirą. Naršyklėje atidaryk <http://127.0.0.1:8000/tasks>. Iš pradžių pamatysi `{"items":[],...}`. Pirmą kartą automatiškai atsiras `data/tasks.sqlite` duomenų bazės failas. Sustabdymui terminale spausk `Ctrl+C`.

Jei 8000 prievadas užimtas, IntelliJ serverio konfigūracijos **Port** laukelyje pakeisk jį į 8001. Jei naudoji IntelliJ terminalą, komandoje pakeisk `:8000` į `:8001`. Tuomet Postman kolekcijoje ir Swagger apraše pakeisk serverio adresą į `http://127.0.0.1:8001`.

## Ką išbandyti

Pirmiausia paleisk serverį per IntelliJ. Tada **IntelliJ Project** lange rasi `openapi.yaml` ir `postman_collection.json`: šiuos failus naudos žemiau aprašyti įrankiai. Kodo keisti nereikia. HTTP būsenos: `201` reiškia sukurta, `200` sėkmingai perskaityta ar pakeista, `204` ištrinta, `404` nerasta, `400` blogas JSON, `422` netinkami laukai.

### Swagger Editor

1. Atidaryk <https://editor.swagger.io/>.
2. Pasirink **File → Import file** ir nurodyk IntelliJ atidaryto projekto failą `/Users/vaidotas/Projects/ws-final/openapi.yaml`.
3. Dešinėje matysi visus API kelius. Atidaryk `POST /tasks`, spausk **Try it out**, **Execute**. Paskui išbandyk `GET /tasks`.
4. API leidžia Swagger Editor svetainės CORS užklausas. Jei naršyklė vis tiek blokuoja ryšį su vietiniu serveriu, rezultatus gali patikrinti pateikta Postman kolekcija arba `curl`.

### Postman

1. Atidaryk Postman, pasirink **Import → Files** ir nurodyk IntelliJ atidaryto projekto failą `/Users/vaidotas/Projects/ws-final/postman_collection.json`.
2. Paleisk užklausas kolekcijoje iš eilės: **Sąrašas**, **Sukurti**, **Viena užduotis**, **Pakeisti**, **Ištrinti**.
3. Sukūrimo užklausa išsaugo ID į kolekcijos kintamąjį `taskId`, todėl kitų užklausų taisyti nereikia. Postman **Tests** skirtuke yra automatiniai būsenos kodo patikrinimai.

## Testai

IntelliJ vidiniame terminale atidaryk **naują kortelę** ir joje paleisk:

```bash
php tests/run.php
```

Skriptas pats trumpam paleidžia **atskirą** HTTP serverį su laikina SQLite duomenų baze. Jis tikrina `TaskValidator`, `SqliteTaskRepository`, `TaskApi` klases ir tikras HTTP užklausas. Sėkmės atveju paskutinė eilutė yra `Visi testai praėjo.` Jei turi PHP papildinį, gali ir IntelliJ **Project** lange dešiniuoju pelės mygtuku spustelėti `tests/run.php` bei rinktis **Run**.

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

Klasės turi vieną pagrindinę atsakomybę, o `TaskApi` priklauso nuo `TaskStorage` sąsajos. Tai paprastas **S.O.L.I.D.** principų pritaikymas. PHP klasės ir metodai aprašyti **PHPDoc** komentarais. Iš jų sugeneruota `docs/PHPDOC.md` santrauka; ją atnaujinsi IntelliJ vidiniame terminale komanda `php tools/generate_docs.php`. JavaDoc yra tik Java kodo dokumentavimo priemonė ir PHP projektui netinka.

Duomenų bazės struktūra: `tasks(id INTEGER PRIMARY KEY, title TEXT NOT NULL, done INTEGER NOT NULL)`. Ji automatiškai sukuriama paleidžiant programą. SQL užklausose naudojami parametrai, todėl naudotojo įvestis nejungiama į SQL tekstą. Bazės failas ignoruojamas Git, kad asmeniniai duomenys nepatektų į repozitoriją.

## REST reikalavimai

- **Be sesijų (stateless):** kiekviena užklausa turi visą jai reikalingą informaciją; serveris nelaiko kliento sesijos.
- **Ištekliai ir metodai:** `/tasks` yra kolekcija, `/tasks/{id}` yra vienas išteklius; `GET`, `POST`, `PUT`, `DELETE` turi skirtingas paskirtis.
- **Podėlis (cacheable):** `GET` atsakymas turi `ETag` ir `Cache-Control`. Pakartojus užklausą su `If-None-Match`, nepasikeitę duomenys grąžina `304` be turinio.
- **Hiperteksto nuorodos:** atsakymų `_links` laukai nurodo, kur kreiptis dėl sąrašo, įrašo ar pakeitimo. Tai Richardson modelio 3 lygio požymis. Užduotyje parašytas „4 lygis“ yra klaida: [Richardson modelis apibrėžia 0–3 lygius](https://martinfowler.com/articles/richardsonMaturityModel.html).

## Komandos darbas ir Git

Užduotyje reikalaujama **2–5 studentų**, skirtingų vaidmenų ir kiekvieno autoriaus atskirų commit. Šiame projekte šiuo metu dirba **vienas žmogus**, todėl negaliu sąžiningai nurodyti kitų studentų ar sukurti jų commit. Galimas vaidmenų pasiskirstymas, kai atsiras komanda: kūrėjas – PHP ir duomenų bazė; QA – Postman bei automatiniai testai; analitikas – OpenAPI ir reikalavimų patikra. Vienas asmuo gali turėti kelis vaidmenis, bet reikia bent dviejų tikrų komandos narių, kad būtų įvykdyta ši sąlyga.

Kodas kurtas atskiroje `codex/task-api` šakoje ir sujungtas į `master`. [Viešoje GitHub Projects lentoje](https://github.com/users/vaidotassackus/projects/1/views/1) yra keturios su repozitorijos Issues susietos užduotys: trys atliktos, o antro komandos nario peržiūra laukia. Jei prisijungs kitas studentas, jis turėtų sukurti **savo šaką**, atlikti savo darbą ir sukurti **savo commit**, po to šaką sujungti į `master`.

## Kas šiame variante ribota

Tai mokomasis vietinis API: nėra naudotojų prisijungimo, viešo serverio ar grafinės sąsajos, nes užduočių CRUD demonstracijai jų nereikia. Jei dėstytojas reikalauja būtent Java programos ar tikro JavaDoc failo, reikia atskirai suderinti PHP pasirinkimą; užduoties tekste PHP tiesiogiai nepaminėtas, tačiau aiškiai leidžiama naudoti **bet kurią platformą**.
