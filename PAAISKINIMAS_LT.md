# Paaiškinimas lietuviškai

## Kas tai yra

Tai nedidelė užduočių sąrašo **REST API**. API yra programa, kuri priima HTTP užklausas ir grąžina JSON. Pavyzdžiui, `POST /tasks` sukuria užduotį, o `GET /tasks` grąžina jų sąrašą. Ekrano sąsajos nėra, nes užduotis prašo žiniatinklio paslaugos, o ją galima išbandyti naršykle, Postman arba Swagger Editor.

Pasirinkau **PHP**, nes jį jau pažįsti. **SQLite** yra duomenų bazė viename faile; nereikia įdiegti MySQL ar konfigūruoti serverio. **Composer ir Java nereikalingi**. Reikia PHP 8.2 ar naujesnės versijos su `pdo_sqlite` ir `mbstring` plėtiniais.

## Paleidimas nuo nulio

1. Įsidiek PHP (pavyzdžiui, macOS: `brew install php`; Windows: naudok PHP paketą iš [php.net](https://www.php.net/downloads.php)). Atidaryk terminalą.
2. Patikrink: `php -v`. Taip pat paleisk `php -m` ir sąraše rask `pdo_sqlite` bei `mbstring`.
3. Parsisiųsk projektą ir pereik į jo aplanką:

   ```bash
   git clone https://github.com/vaidotassackus/ws-final.git
   cd ws-final
   ```

4. Paleisk vietinį serverį:

   ```bash
   php -S 127.0.0.1:8000 -t public public/index.php
   ```

5. Palik šį terminalą atvirą. Naršyklėje atidaryk <http://127.0.0.1:8000/tasks>. Iš pradžių pamatysi `{"items":[],...}`. Pirmą kartą automatiškai atsiras `data/tasks.sqlite` duomenų bazės failas.
6. Sustabdyti serverį gali paspaudęs `Ctrl+C`.

Jei 8000 prievadas užimtas, gali naudoti, pavyzdžiui, 8001. Tuomet Postman kolekcijoje ir Swagger apraše pakeisk serverio adresą į `http://127.0.0.1:8001`.

## Ką išbandyti

Atidaryk **kitą** terminalo langą ir iš eilės paleisk:

```bash
curl -i http://127.0.0.1:8000/tasks
curl -i -X POST http://127.0.0.1:8000/tasks -H 'Content-Type: application/json' -d '{"title":"Pirkti pieno"}'
curl -i http://127.0.0.1:8000/tasks/1
curl -i -X PUT http://127.0.0.1:8000/tasks/1 -H 'Content-Type: application/json' -d '{"title":"Pirkti duonos","done":true}'
curl -i -X DELETE http://127.0.0.1:8000/tasks/1
```

Skaičius `1` yra pirmos sukurtos užduoties ID. Jei duomenų bazėje jau yra užduočių, paimk ID iš `POST` atsakymo arba `Location` antraštės. `-i` parodo HTTP būseną bei antraštes: `201` reiškia sukurta, `200` sėkmingai perskaityta ar pakeista, `204` ištrinta, `404` nerasta, `400` blogas JSON, `422` netinkami laukai.

### Swagger Editor

1. Atidaryk <https://editor.swagger.io/>.
2. Pasirink **File → Import file** ir nurodyk `openapi.yaml`.
3. Dešinėje matysi visus API kelius. Atidaryk `POST /tasks`, spausk **Try it out**, **Execute**. Paskui išbandyk `GET /tasks`.
4. API leidžia Swagger Editor svetainės CORS užklausas. Jei naršyklė vis tiek blokuoja ryšį su vietiniu serveriu, rezultatus gali patikrinti pateikta Postman kolekcija arba `curl`.

### Postman

1. Atidaryk Postman, pasirink **Import → Files** ir nurodyk `postman_collection.json`.
2. Paleisk užklausas kolekcijoje iš eilės: **Sąrašas**, **Sukurti**, **Viena užduotis**, **Pakeisti**, **Ištrinti**.
3. Sukūrimo užklausa išsaugo ID į kolekcijos kintamąjį `taskId`, todėl kitų užklausų taisyti nereikia. Postman **Tests** skirtuke yra automatiniai būsenos kodo patikrinimai.

## Testai

Kai serveris sustabdytas arba veikia kitame terminale, projekto aplanke paleisk:

```bash
php tests/run.php
```

Skriptas pats trumpam paleidžia **atskirą** HTTP serverį su laikina SQLite duomenų baze. Jis tikrina `TaskValidator`, `SqliteTaskRepository`, `TaskApi` klases ir tikras HTTP užklausas. Sėkmės atveju paskutinė eilutė yra `Visi testai praėjo.`

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

Klasės turi vieną pagrindinę atsakomybę, o `TaskApi` priklauso nuo `TaskStorage` sąsajos. Tai paprastas **S.O.L.I.D.** principų pritaikymas. PHP klasės ir metodai aprašyti **PHPDoc** komentarais. Iš jų sugeneruota `docs/PHPDOC.md` santrauka; ją atnaujinsi komanda `php tools/generate_docs.php`. JavaDoc yra tik Java kodo dokumentavimo priemonė ir PHP projektui netinka.

Duomenų bazės struktūra: `tasks(id INTEGER PRIMARY KEY, title TEXT NOT NULL, done INTEGER NOT NULL)`. Ji automatiškai sukuriama paleidžiant programą. SQL užklausose naudojami parametrai, todėl naudotojo įvestis nejungiama į SQL tekstą. Bazės failas ignoruojamas Git, kad asmeniniai duomenys nepatektų į repozitoriją.

## REST reikalavimai

- **Be sesijų (stateless):** kiekviena užklausa turi visą jai reikalingą informaciją; serveris nelaiko kliento sesijos.
- **Ištekliai ir metodai:** `/tasks` yra kolekcija, `/tasks/{id}` yra vienas išteklius; `GET`, `POST`, `PUT`, `DELETE` turi skirtingas paskirtis.
- **Podėlis (cacheable):** `GET` atsakymas turi `ETag` ir `Cache-Control`. Pakartojus užklausą su `If-None-Match`, nepasikeitę duomenys grąžina `304` be turinio.
- **Hiperteksto nuorodos:** atsakymų `_links` laukai nurodo, kur kreiptis dėl sąrašo, įrašo ar pakeitimo. Tai Richardson modelio 3 lygio požymis. Užduotyje parašytas „4 lygis“ yra klaida: [Richardson modelis apibrėžia 0–3 lygius](https://martinfowler.com/articles/richardsonMaturityModel.html).

## Komandos darbas ir Git

Užduotyje reikalaujama **2–5 studentų**, skirtingų vaidmenų ir kiekvieno autoriaus atskirų commit. Šiame projekte šiuo metu dirba **vienas žmogus**, todėl negaliu sąžiningai nurodyti kitų studentų ar sukurti jų commit. Galimas vaidmenų pasiskirstymas, kai atsiras komanda: kūrėjas – PHP ir duomenų bazė; QA – Postman bei automatiniai testai; analitikas – OpenAPI ir reikalavimų patikra. Vienas asmuo gali turėti kelis vaidmenis, bet reikia bent dviejų tikrų komandos narių, kad būtų įvykdyta ši sąlyga.

Kodas kurtas atskiroje `codex/task-api` šakoje ir sujungtas į `master`. Komandos užduotys registruojamos GitHub repozitorijos **Issues** sąraše. GitHub Projects lentai reikalingas papildomas `project` prisijungimo leidimas, kurio dabartinis prisijungimas neturi. Jį suteikus, šiuos Issues galima perkelti į Projects lentą. Jei prisijungs kitas studentas, jis turėtų sukurti **savo šaką**, atlikti savo darbą ir sukurti **savo commit**, po to šaką sujungti į `master`.

## Kas šiame variante ribota

Tai mokomasis vietinis API: nėra naudotojų prisijungimo, viešo serverio ar grafinės sąsajos, nes užduočių CRUD demonstracijai jų nereikia. Jei dėstytojas reikalauja būtent Java programos ar tikro JavaDoc failo, reikia atskirai suderinti PHP pasirinkimą; užduoties tekste PHP tiesiogiai nepaminėtas, tačiau aiškiai leidžiama naudoti **bet kurią platformą**.
