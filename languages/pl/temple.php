<?php
/**
 *   File functions:
 *   Polish language for temple
 *
 *   @name                 : temple.php                            
 *   @copyright            : (C) 2004,2005,2006,2011,2012,2013 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @version              : 1.7
 *   @since                : 05.02.2013
 *
 */

//
//
//       This program is free software; you can redistribute it and/or modify
//   it under the terms of the GNU General Public License as published by
//   the Free Software Foundation; either version 2 of the License, or
//   (at your option) any later version.
//
//   This program is distributed in the hope that it will be useful,
//   but WITHOUT ANY WARRANTY; without even the implied warranty of
//   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//   GNU General Public License for more details.
//
//   You should have received a copy of the GNU General Public License
//   along with this program; if not, write to the Free Software
//   Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
//
// $Id$

define("ERROR", "Zapomnij o tym!");
define("YOU_DEAD", "Nie możesz pracować dla świątyni ponieważ jesteś martwy!");
define("NO_ENERGY", "Nie masz tyle energii!");

if ($player -> location == 'Altara')
{
    define("TEMPLE_INFO", "Witaj w świątyni. Możesz tutaj modlić się do");
    define("TEMPLE_INFO2", "Aby twoja modlitwa została wysłuchana, musisz posiadać odpowiednią ilość Punktów Wiary. Punkty zdobywasz służąc w świątyni. To czym ciebie obdaruje bóg zależy tylko od niego.<br />Przy jednej z bocznych naw dostrzegasz leżącą na niewielkim cokole starą księgę. Na ścianach wiszą wizerunki poszczególnych bóstw zaś obok znadują się ich opisy.");
}
    else
{
    define("TEMPLE_INFO", "Wkraczasz do Świętego kasztanowca... Uderza Cię tajemnicza aura boskości panująca w tym miejscu. W każdym z rogów pomieszczenia stoi posąg jednego z Bóstw. Podchodzi do Ciebie nieznana postać z kapturem. Domyślasz się, że jest to kapłan opiekujący się Świątynią...<br /><i>- Witaj w tym świętym przybytku. Jeśli chcesz się pomodlić i uzyskać błogosławieństwo musisz najpierw popracować, aby zyskać przychylność Bogów i zdobyć ich czas. Gdy już to uczynisz to możesz próbować  zdobyć błogosławieństwo.</i>");
}

if (!isset($_GET['temp'])) 
{
    define("A_WORK", "Pracuj dla świątyni");
    define("A_PRAY", "Módl się do boga");
    define("A_BOOK", "Podejdź do księgi");
    define("A_PANTHEON", "Panteon ".$gamename);
}

if (isset ($_GET['temp']) && $_GET['temp'] == 'sluzba') 
{
    define("YOU_WORK", "Pracowałeś przez pewien czas dla świątyni i zdobywasz ");
    define("YOU_WORK2", " Punkt(ów) Wiary.");
    define("TEMPLE_INFO_W", "Pracując dla świątyni, sprawiasz, że");
    define("TEMPLE_INFO2_W", "spogląda na ciebie przychylniejszym okiem. Za każde 0,2 energii zdobywasz 1 Punkt Wiary. Czy chcesz służyć w świątyni?");
    define("I_WANT", "Chcę pracować dla świątyni");
    define("T_AMOUNT", "razy");
    define("A_WORK", "Pracuj");
    define("NO_DEITY", "Nie możesz pracować w świątyni ponieważ jesteś ateistą!");
}

if (isset ($_GET['temp']) && $_GET['temp'] == 'modlitwa') 
{
    define("NO_PW", "Nie masz Punktów Wiary aby się modlić!");
    define("YOU_PRAY", "Modliłeś się przez pewien czas do ");
    define("BUT_FAIL", ". Niestety został(a) głuchy(a) na twe prośby.");
    define("A_PRAY", "Modlitwa");
    define("NO_DEITY", "Nie możesz modlić się w świątyni ponieważ jesteś ateistą!");
    define("NO_RACE", "Nie możesz modlić się w świątyni ponieważ nie wybrałeś jeszcze rasy!");
    define("YOU_HAVE", "Posiadasz już błogosławieństwo od boga!");
    define("P_SUCCESS", ", który(a) okazał(a) się zadowolony(a) z twej pobożności. Otrzymujesz błogosławieństwo do ");
    define("P_DEAD", " lecz on(a) okazał(a) się nieprzychylny(a). Twe modły rozdrażniły bóstwo. Nagle posąg ");
    define("P_DEAD2", " wystrzelił w Twoją stronę błyskawicę, która uśmierciła Cię na miejscu....");
    define("AGI", "Zręczności");
    define("STR", "Siły");
    define("INTELI", "Inteligencji");
    define("WIS", "Siły Woli");
    define("SPE", "Szybkości");
    define("CON", "Kondycji");
    define("SMI", "Kowalstwa");
    define("ALC", "Alchemii");
    define("FLE", "Stolarstwa");
    define("WEA", "Walki bronią");
    define("SHO", "Strzelectwa");
    define("DOD", "Uników");
    define("CAS", "Rzucania czarów");
    define("BRE", "Hodowli");
    define("MINI", "Górnictwa");
    define("LUMBER", "Drwalnictwa");
    define("HERBS", "Zielarstwa");
    define("ENERGY_PTS2", "pkt energii");
    define("PRAY1", "Pacierz");
    define("PRAY2", "Modlitwa");
    define("PRAY3", "Psalm");
    define("PRAY4", "Adoracja");
    define("BLESS_FOR", "Błogosławieństwo do ");
    define("PW_PTS", "pkt Wiary");
    define("JEWEL", "Jubilerstwa");
}

if (isset ($_GET['temp']) && $_GET['temp'] == 'book') 
{
    define("NEXT_PAGE", "Następna strona");
    if (!isset($_GET['book']))
    {
        define("BOOK_INFO", "Podchodzisz bliżej do cokołu i uważnie przyglądasz się znalezisku. Zdziwiony jesteś iż dopiero teraz ją zauważyłeś. Księga wygląda na starą, jej okładka wykonana jest z brązowej skróry, na grzbiecie książki widzisz nie znany sobie symbol. Zaciekawiony ostrożnie otwierasz księgę...");
        define("BOOK_TEXT1", "Drzwi do świątyni otworzyły się  nagle i jedno z potężnych, okutych skrzydeł rozpadło się z niemiłosiernym hukiem i trzaskiem po uderzeniu w ścianę. Jasne światło dnia wdarło się do ciemnej komnaty tworząc coś na kształt drogi od wejścia aż do ołtarza, przy którym klęczał kapłan Neh.<br />
W oka mgnieniu starzec podniósł się i próbował podbiec do drzwi, jednak równie szybko jak wstał, zatrzymał się w połowie drogi. W progu stała jakaś postać... Gdy jego oczy przywykły do światła, zaczął powoli rozróżniać jej kształty. Zbliżała się powoli i cicho roznosząc wokół siebie słodki, choć senny zapach jaśminu i bzu...<br />
Była to wysoka, smukła elfka. Jej złota skóra i tejże barwy długie  włosy kontrastowały z lśniącymi czernią oczami. Trzymała w dłoni hełm pokryty runami i na modłę wojowników północy, odziana była w cienką bluzę, czarny, matowy napierśnik i nakolanniki, nałożone na lekkie, skórzane spodnie i wysokie do połowy ud buty. U pasa połyskiwały dumnie srebrne ostrogi, magiczna różdżka, sakiewka z mithrilem i krótka mizerykordia. Zza pleców elfki, niczym skrzydła, wystawały jelce półtoraręcznych mieczy.<br />
-Nie zabijaj na poświęconej ziemi...!-krzyknął kapłan. Uśmiechnęła się i wyciągnęła do niego dłoń, na której pysznił się najeżony górskimi kryształami Pierścień Żywiołów. Przerażone oczy kapłana zmieniły nagle swój wyraz i zaszkliły się tkliwymi łzami.- Nubia! Tyle lat! -szepnął klękając. -Czekaliśmy na ciebie, Pani!<br />
-Wstań, Neh. Nie czas na oddawanie pokłonów -powiedziała twardo, choć nie bez drżenia wzruszenia w głosie. -Nie po hołdy wróciłam do ".$city1b."...<br />
Starzec podniósł się z kolan i ruchem dłoni odprawił młodych kapłanów i zwabionych hałasem gapiów zebranych na schodach świątyni. Ukłonił się nisko elfce.<br />
-Nubio, kiedy odeszłaś... Kiedy opuściłaś miasto, myślałem, że twoja łaska już nigdy tu nie wróci, nie po tym, co się wydarzyło...<br />
-Wybacz, ale nie wróciłam tu dla was- przerwała mu chłodno, a jej wzrok skupił się na kamiennej posadzce, tak jakby nie chciała mówić tego, co wypływało z jej ust. -Chciałam tylko zobaczyć króla Thindila i prosić go o pomoc w zdobyciu kilku rzeczy... Muszę już iść. Przygotuj wszystko tak jak dawniej. Urządzimy wieczorem uroczystość i wszystko ci opowiem. Zjawię się o zmroku.<br />
To rzekłszy wyszła, zostawiając Neha pod ołtarzem Illuminati.<br />
-Kto to był, Panie? -nieśmiały głos żebraka wyrwał kapłana z zadumy. Neh westchnął ciężko i rzucił hobbitowi monetę.<br />
-Prawdziwa bogini, mój mały... Jedyna prawdziwa...<br /><br /><br /><br />
*******<br /><br />
Śpiąca ".$city1." budziła się bladym świtem, gdy uroczystość dobiegła końca. Nubia i Neh wyszli bocznymi drzwiami świątyni i rozsiewając zapach palonych kadzideł  kroczyli w kierunku wieży, w której mieszkała elfka. Teraz nie miała na sobie podróżnego ubrania wojownika. Była odziana w szatę maga, która mglistą bielą rozbijała szarość poranka powoli, powłóczyście ogarniając go i rozświetlając każdym postawionym krokiem.<br />
-Dawno nocne ceremonie nie były tak udane... -mówił Neh idąc z założonymi do tyłu rękami. Jego zgarbiona postać, zdawała się być bardziej pochylona niż zwykle. -Boję się odpowiedzi na pytania, które chcę ci zadać... Ale muszę to zrobić... -dodał po chwili. -Co się z tobą działo przez te wszystkie lata i dlaczego wróciłaś?<br />
-Kiedy mój klasztor spłonął, wyruszyłam w świat i zarabiałam na chleb jako najemny mag i wojownik. Gdzie tylko można było ryzykować życiem, a zarobić wiele, tam się pojawiałam.<br />
-Po co ci były pieniądze? -przerwał Nubii. -Przecież Thindil dałby ci ile zechcesz, gdybyś tylko o to poprosiła...<br />
-Widzisz... -westchnęła ciężko.- Pierwsza zasada magii mówi, że nie stworzysz czegoś z niczego, więc nie mogłam wyczarować złota, a od Thindila nie chciałam go brać. Potrzebowałam fortuny, by kupić magiczne księgi, które nauczyłyby mnie, jak cofnąć czas i uratować mój klasztor przed zagładą... Byłam wtedy bezsilna, a dziś... dziś już wiem, że nie cofnę wskazówek zegara, ale mogę zrobić coś innego, a do tego potrzebuję mocy kilku przedmiotów. Wiem, że są w ".$city1a."...<br />
-Król da ci je na pewno...<br /> 
-Nie, on ich nie ma, bo są ukryte tak, że potrzeba wieków, żeby je odnalazł, ale w ".$city1a." mieszka wielu śmiałków, którzy będą potrafili je zdobyć... Oczywiście za odpowiednią nagrodę...<br />
-Oczywiście... -powtórzył jak echo Neh.");
    }
        elseif ($_GET['book'] == 1)
    {
        define("BOOKTEXT2", "Herold rozwinął pergamin i wyprostowany jak struna zaczął czytać:<br /><br />
        ... Mieszkańcy ".$city1b."! Elficka Bogini Żywiołów jest znowu wśród nas! Nie żąda danin, ani modłów! Nie chce klęczników, ani wiernych! Chroni nas przed żywiołami, a w zamian prosi o pomoc w poszukiwaniach! Każda para rąk się przyda! Oto co głosi Nubia:<br /><br />Tam, gdzie żywioły nie mają wstępu<br />
        Tkwi skryta różdżka z okiem z diamentu.<br />
        Potwór jej strzeże, co płonie wiecznie<br />
        I wśród przestworzy żyje bezpiecznie,<br />
        Jednakże wieki już tam nie bywał,<br />
        Bo różdżkę magii przez światem skrywał.<br />
        Wygraj z potworem, skarb zabierz śmiało.<br />
        W zamian nagroda, chwały niemało,<br />
        Uznanie dam, rycerzy, władców,<br />
        Gdy różdżka spocznie już w moim skarbcu.<br />
        Pamiętaj jednak: wśród tej zabawy<br />
        Z życiem uchodzi tylko człek prawy!<br /><br />
        ******************************");
    }
        elseif ($_GET['book'] == 2)
    {
        define("BOOKTEXT3", "Neh stał przy oknie świątyni pogrążony w zadumie. Kula wyroczni powiedziała mu o nadchodzących zmianach, ale jak zwykle enigmatycznie i tak lakonicznie, że nie był w stanie znaleźć klucza do tych słów:<br /><br />...i żywe złoto zmienisz w zimne mury,<br />Czas cofnąć? Nie! Zakrzywić? Nie!<br />Nie zniesiesz jednym słowem góry<br />Lecz jej podnóżem staniesz się!<br /><br />Stary kapłan wiedział, że nic dobrego nie niesie z sobą taka zapowiedź, ale nie był w stanie przypasować jej do niczego, co mogłoby się dziać w ".$city1a.". Przeciągnął pomarszczoną dłonią po zmęczonej twarzy i przetarł piekące oczy. Głębokie westchnienie przebiegło echem główną nawę świątyni i kilku niższych kleryków odwróciło się do niego z zainteresowaniem.<br />-Pomóc w czymś, Panie? -skłonił się jeden z nich.<br />-Nie... Dziękuję, Pelosie... Pójdę się położyć. Znużył mnie dzisiejszy dzień.<br />-A cóż takiego się działo? -przerwał mu radosny głos Nubii, która właśnie wkroczyła do świątyni ciągnąc za sobą umorusaną i siną z przemęczenia postać.<br />-Nubio, wyrocznia przemówiła dziś słowami, których nie potrafię do niczego odnieść. Są tak dziwne! Zacytuję ci- może ty je wyjaśnisz, otóż...<br />-Nie ważne! Nie muszę ich słyszeć, bo wiem, co zwiastują -mówiła rozgorączkowana złota elfka nie dopuszczając Neha do słowa. -Ten wojownik zdobył różdżkę! Teraz wiem, co kryło się w słowach zagadki! *Tam, gdzie żywioły nie mają wstępu*- w labiryncie nie odczuwa się działań żywiołów i tam też poszedł nasz śmiałek. Tam właśnie znalazł sekretne przejście do ukrytego świata! W każdych innych czekała śmierć i niebezpieczeństwa -tu zasępiła się na myśl o tych, którzy nie mieli szczęścia i zginęli w labiryncie. Jednak po chwili dziecięca wręcz radość znów przejęła jej myśli.<br />-Teraz wszystko będzie jak dawniej... Jako bogini żywiołów mam moc równą im samym, ale z tą różdżką, będę silniejsza niż ogień, który zniszczył mój klasztor. Odbiorę mu to, co zabrał wieki temu! Pelos, Derhm! -zawołała obu kleryków Neha. -Proszę, przygotujcie wszystko do ceremonii...!<br /><br />********************************************<br /><br />Neh, bohaterski zdobywca różdżki, wszyscy klerycy i pomniejsi kapłani stali w kręgu wokół ołtarza z naczynkami z wodą w jednej i płonącymi  pochodniami w drugiej dłoni. Zapach kadzideł, duszące opary świec i pochodni zagęściły resztki niewypalonego wiatru buszującego za dnia w świątyni. Trudno było oddychać parzącym w nozdrza i szczypiącym w oczy powietrzem. Jednak nikt z zebranych nie oddałby tej chwili za żadne skarby i nie chciałby teraz być w innym miejscu: napięcie, magia, dziwne ciepło i czyjaś niewidzialna obecność...<br />Elfka Nubia stanęła przed otwartą Księgą Natury i wyciągnęła w górę ręce. Tkwiły w nich trzy niewielkie flakony, w których zamknięte były krople wody, odrobina ziemi lasu i powiew morskiej bryzy. W pewnej chwili zderzyła z sobą buteleczki i ich zawartości zmieszały się z sobą, a gdy opadały i powoli spływały po poranionych dłoniach wtapiając się z jej krwią, Nubia inkantowała zaklęcie:<br /><br />Ty, Matko Ziemio, wspomóż mnie, proszę!<br />Wietrze i Wodo, dajcie mi moc!<br />Niech Wasza siła mą będzie dzisiaj<br />I przezwycięży minione zło!<br />Odmienię czyny wściekłego Ognia,<br />Co zniszczyć wszystko zawsze chciał.<br />Potem na wieki zostanę cicha<br />I próśb nie poślę żadnych Wam.<br />Moc Wasza słuchać musi różdżki,<br />Skrytej w feniksa labiryncie,<br />Mam ją w mej dłoni-spójrzcie, o Wielcy!<br />I o co proszę, bez słowa czyńcie!<br /><br />Krzycząc ostatnie wersy, uniosła poranionymi dłońmi różdżkę, z której diamentowego oka popłynął oślepiający blask. Na znak Nubii wszyscy zgasili pochodnie zalewając je wodą i wówczas  moc wezwanych żywiołów wstąpiła w elfkę. Wyrzucała z siebie kolejne czary i wzywała duchy przeszłości, a kamień różdżki świecił co raz jaśniej i jaśniej. Nagle, podłoga pod nogami zebranych zatrzęsła się, a przez okno wdarł się krzyk przerażonych przechodniów:<br />-Ziemia się rozstąpiła!!!! Coś wychodzi spod ziemi!!!<br />-Mój klasztor wrócił ze Strony Nocy... -szepnęła elfka z ulgą.<br />Nubia miała na sobie szatę Magów Południa, odkrywającą znacznie więcej niż zakrywała i w świetle bijącym z magicznego kamienia jej złote ciało zaczęło przypominać posąg odlany z metalu. Neh zdał sobie z tego sprawę i już wiedział, co mówiła przepowiednia, ale było za późno...!<br />Różdżka zapłonęła nagle żywym ogniem, szybko wypaliła się jasnym płomieniem, a gdy zgasła ziemia przestała drżeć. Świątynię zalała ciemność i zapadła gryząca, bolesna cisza.<br /><br />-Otwierać!!! Otwierać natychmiast!!! -dało się słyszeć zza drzwi, jednak nikt nie kwapił się, by do nich podejść. Po chwili topory gwardii przybocznej króla wpuściły do świątyni świeże powietrze i jasność, ale przede wszystkim do nawy głównej wkroczył sam Thindil. Początkowo musiał przyzwyczaić wzrok do półmroku, ale gdy tylko jego oczy rozróżniły kontury, krzyknął i podbiegł do ołtarza. Nubia leżała na nim oddając ostatnie tchnienie, zgodnie z obietnicą daną żywiołom. Resztką sił uniosła powieki i wyjrzała przez wrota świątyni. Za plecami wchodzących żołnierzy widać było strzeliste mury klasztoru i powiewającą na jego wieży flagę Czerwonego Smoka...<br />Thindil wziął elfkę na ręce i płacząc patrzył w jej czarne, gasnące oczy. Nikły uśmiech przebiegł po jej blednącej twarzy, a policzkami popłynęły łzy szczęścia. Dłonie Thindila starły je szybko, gdy próbowała coś mu powiedzieć lecz śmierć zamknęła jej usta na wieki...<br />Neh podszedł cicho. Położył rękę na ramieniu króla, a klerycy zabrali zimne ciało elfki. Wszystko umilkło, jakby pokryte żałobnym całunem, a i Thindil milczał ciągle, bezsilnie wpatrując się w nicość. Nagle poczuł coś magicznego i niepojętego w powietrzu. Miał dziwne wrażenie, że coś... że ktoś...! Rozprostował palce zaciśniętej dłoni, którą wcześniej ocierał twarz Nubii. Na jego ręce leżały dwie perły w kształcie łez... <br />Z nadzieją, że żyje podbiegł i wydarł elfkę klerykom. Jednak jej oddech już nie wrócił. Mimo to król nie wypuszczał z objęć ciała czarodziejki, a kiedy padał na kolana z goryczą zakrzyknął w niebo:<br />-Oddajcie mi dziecko...!!!");
    }
}

if (isset ($_GET['temp']) && $_GET['temp'] == 'pantheon') 
{
    define("GOD1", "Illuminati");
    define("GOD2", "Karserth");
    define("GOD3", "Anariel");
    define("GOD4", "Heluvald");
    define("GOD5", "Tartus");
    define("GOD6", "Oregarl");
    define("GOD7", "Daeraell");
    define("GOD8", "Teathe-di");
    define("GOD1_INFO", "Illuminati to główne bóstwo panteonu Vallheru. Różne rasy różnie wyobrażają sobie ową istotę. Wśród Ludzi oraz Krasnoludów jest on mężczyzną danej rasy, wśród Elfów jest to elficka kobieta. Bóstwo owo w przeciwieństwie do innych bóstw nie koncentruje się na jednym tylko aspekcie życia, lecz wspiera swoich wyznawców na wiele sposobów. Symbolem tego bóstwa jest biały płomień na tle złotego rombu");
    define("GOD2_INFO", "Wyznawców Karsertha, podobnie jak ich boga częściej można spotkać na polu bitwy niż w świątyni. Karserth jest panem wojny, miłuje swych wyznawców przede wszystkim za waleczność oraz odwagę, gardzi tchórzami. Poszczególnym rasom jawi się jako mężczyzna odziany w potężną, wspaniale zdobioną zbroję (wśród Ludzi oraz Krasnoludów) lub jako tajemniczy, smukły mężczyzna odziany w płaszcz tropicieli uzbrojony w bojowy łuk (wśród elfów). Swych wyznawców wspiera darowując im przede wszystkim siły oraz umiejętności do walki. Jego symbolem są skrzyżowany miecz oraz strzała");
    define("GOD3_INFO", "Najbardziej tajemnicza bogini spośród całego panteonu Vallheru. Jest to opiekunka magii oraz istot zajmujących się nią. Od swych wyznawców wymaga aby rozwijali swoje umiejętności związane z magią. Śmiertelnicy wyobrażają ją sobie jako kobiecą postać w magicznych szatach z nasuniętym głęboko na głowę kapturem. Wokół owej postaci emanuje aura potężnej magicznej mocy. Swych wyznawców bogini ta wspiera dodając sił oraz umiejętności związanych z magią. Jej symbolem jest niebieski wir.");
    define("GOD4_INFO", "Jest to najspokojniejszy spośród bogów Vallheru. Nie interesuje go moc czy przelew krwi. Heluvald jest opiekunem rzemieślników oraz handlarzy. Wśród wszystkich ras przedstawia się go albo jako niskiego, brodatego mężczyznę odzianego w fartuch kowalski albo jako przystojnego kupca w bogato strojonym ubraniu z tajemniczym uśmiechem na twarzy. Swych wyznawców wspiera ofiarowując im wiedzę oraz umiejętności związane z różnymi rzemiosłami. Jego symbolem jest stos złotych monet.");
    define("GOD5_INFO", "Jeden z upadłych Bogów, strącony przez wielką czwórkę. Niegdyś władający potężną mocą, potem wygnany i przeklęty. Minęły tysiąclecia, kiedy ponownie wrócił do świata śmiertelnych. Tartus wyglądem przypomina wysmukłego elfickiego wojownika o długich ciemnych włosach, dzierżącego w prawej ręce długi miecz, w lewej zaś magiczną sferę ognia. Jego obsydianowa zbroja płytowa i nagolenniki kontrastują z jasną, cielistą skórą. Z tułowia wyrastają mu wielkie płomienne skrzydła przypominające feniksa, dzięki którym potrafi monumentalnie utrzymywać się w powietrzu. Tartus jest mistrzem magii ognia i planów piekielnych. Podczas swego wygnania zawładnął i opanował wiele z upadłych planów astralnych, gdzie podporządkował sobie ogromne rzesze demonów i czartów. Stąd jest także biegły w wszelakiego rodzaju przywoływaniach demonów. Niegdyś czczony przez kilka plemion elfich i jaszczuroczłeków. Jednakże w dzisiejszych czasach jest potępiany przez większość ras. Ludzie i hobbici boją się jego demonicznej aury, uważając go za Boga zła i piekieł. Jaszczuroczłecy i krasnoludy nie tolerują jego magii i nazywają Bogiem Ciemności. Elfy zaś pamiętają tylko szczątki historii, kiedy Tartus cieszył się jeszcze uznaniem Wielkiej Czwórki.<br /><br /> Mimo swojej złej sławy Tartus stał się patronem magów bitewnych i elfów ognia, którzy czcili jego moc i czerpali od niego boskie wsparcie i siłę. Ostatnio w świecie Vallheru pojawia się coraz więcej wyznawców upadłego Bóstwa szczególnie wśród czarowników i przywoływaczy demonów. Ponadto Tartus potrafi wysłuchać i wspierać w bitewnej modlitwie wojowników władających broń białą.");
    define("GOD6_INFO", "Oregarl charakteryzuje się postawą silnego i krzepkiego krasnoluda o szerokich barkach oraz uwydatnionej klatce piersiowej.<br />Długa siwa broda spływa po lśniącej zbroi płytowej, którą zawsze nosi. W dłoniach dzierży wielki młot oraz tarczę. Na tarczy symbol: pięść skrzyżowana z młotem.<br /><br />Ponad tysiąc lat wstecz na ziemiach Vallheru w okolicach gór Kazad-nar toczyła się wielka wojna sił ciemności z zastępami krasnoludów. Kiedy zło rosło w siłę, krasnoludzcy kapłani poprzez swoje modlitwy prosili boga o błogosławieństwo. W świątyni głęboko pod ziemią ukazał się avatar boga Oregarla, przywołany przez kapłanów. Pobłogosławił on miasto i rasę walczących krasnoludów. Niestety, obrona twierdzy krasnoludzkiej nie wytrzymała. Miasto zostało zniszczone poza świątynią, w której przebywał owy avatar. Wejścia do świątyni zostały zapieczętowane glifami ochronnymi i runami tak, by żadne zło nie mogło dostać się do wewnątrz. Kapłani zabezpieczyli świątynię, jednak nie zdołali przekazać swej wiedzy potomnym. I tym sposobem religia została zapomniana.<br /><br />Oregarl opiekuje się krasnoludami walczącymi wręcz oraz tymi, którzy przyczyniają się do rozwoju sztuki obróbki kamieni i złóż metali oraz kowalstwa. Naucza lojalności, nieugiętości oraz wytwarzania dóbr materialnych. Obdarza błogosławieństwem tych który walczą w jego imię przeciw siłom zła.");
    define("GOD7_INFO", "Bogini Żebraków i Poszkodowanych przez los, Dawczyni Nadziei.<br />Zrodziła się ze smutku i płaczu ludzi, by dać im nadzieję na odmianę losu. Legenda o jej narodzeniu została spisana przez elfy. Ponoć jednego razu sam Illuminati siedząc jednego dnia na jednej ze swych ulubionych leśnych polan, postanowił posłuchać myśli mieszkańców świata, zebrał magiczna moc pomiędzy swymi dłońmi i myśli mieszkańców z krain zaczęły wypełniać tę Przestrzeń. Jednakże ku jego zdziwieniu samymi smutkami, żalem, płaczem i bólem. Było tego tyle ze nie zdołał utrzymać i wyrzucił przed siebie. Tych myśli było tak dużo, że zrodziło się z nich jeziorko w samym środku lasu, woda była w nim czarna i nieprzenikniona jak noc. Postanowił dać tym poszkodowanym jakieś słońce które rozjaśniało by im gorycz dnia codziennego. Wrzucił swój włos do jeziora i stworzył Daeraell, która odtąd miała nieść nadzieję poszkodowanym.  Jest postrzegana najczęściej jako kobieta o ciemno brązowej skórze z białymi oczyma, bardzo długimi białymi włosami które promieniują lekko zielonkawa aurą. Wokół niej zawsze latają jaskółki, mówią, że tam gdzie stąpnie tam wyrastają białe lilie. Krasnoludy nazywają ją Nerin Snowmirth.  Jej kult został zapomniany, jednakże nie umarł w Minas Tirth, skąd został do Krain Vallheru z powrotem sprowadzony. Legenda mówi, że obroniła biedne dzieci z miasta, które podczas ataku się do niej modliły. Przykryła je swoimi włosami, powodując ze stały się niewidoczne dla najeźdźców.<br />Wyznawcy Kultu Daeraell trzymają małe kapliczki w których zawsze są umieszczone płatki białych lilii. Mówią ze wiara o niej nie zaginie dopóki będzie się zło i krzywda na świecie działa, czasami się zdarza że dzięki niej niektórzy się wzbogacają, pomaga rzemieślnikom aby ich praca przynosiła lepsze efekty, daje także posiłek dzięki czemu zabiera głód.");
    define("GOD8_INFO", "Teathe-di, patronka złodziejek, słynie z piękna i bogatej historii. Jej wizerunki przedstawiane są dość niejasno. Wygląda jak elfka, lecz jest niska niemal jak niziołka. Na jednych dziełach jej włosy są złote jak zachodzące słońce, a innym razem czarne jak płaszcz nocy. W jednym wszystkie przedstawienia się zgadzają - na jej twarzy kwitnie złośliwy uśmieszek odzwierciedlający jej charakter. Bogini ubrana jest w czarne szaty wojowniczki, a jej biodra przewiązane są szeroką szarfą. Przez plecy ma przewieszony kunsztowny łuk, a znad ramienia wystają lotki strzał.<br /><br />Starodawne podania mówią, że boska była kiedyś szybką łuczniczką podziwianą przez wszystkich bogów. Cały panteon zazdrościł jej wdzięków i zwinności. Podobno któregoś dnia zbuntowała się przeciw bogom ukazując swą prawdziwą naturę. Wysłuchując modlitw wiernych, ukradła z siedziby bóstw ważne astralne komponenty. Bogowie wyklęli ją i nazwali boginią podłych łotrzyc.<br /><br />Od tamtej pory Teathe-di wspiera czarne interesy złodziejek łuczniczek. Nadal wysłuchuje ich modlitw i z radością kontynuuje swoje astralne dzieło");
}
?>
