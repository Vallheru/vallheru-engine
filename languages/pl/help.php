<?php
/**
 *   File functions:
 *   Polish language help
 *
 *   @name                 : help.php                            
 *   @copyright            : (C) 2004-2005 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@users.sourceforge.net>
 *   @version              : 0.8 beta
 *   @since                : 15.03.2005
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
// 

define ("H_AUTHOR", "Autor pomocy: CronNo");

if (!isset($_GET['help']))
{
    define("HELP_INFO", "W czym potrzebujesz pomocy? Wiem, że jest niedokończona, ale jakiś początek już jest.");
    define("A_HISTORY", "Historia świata");
    define("A_OVERVIEW", "Ogólne informacje");
    define("A_EQUIPMENT", "Ekwipunek");
    define("A_EVENTLOG", "Dziennik");
    define("A_INDOCRON", "Altara");
    define("A_BATTLE2", "Walka");
    define("A_MONEY", "Pieniądze");
    define("A_ENERGY", "Energia");
    define("A_FAQ", "FAQ");
}
    else
{
    define("A_BACK", "Powrót");
}

if (isset($_GET['help']) && $_GET['help'] == 'history')
{
    define("HELP1", "<b>Stworzenie Świata</b><br />
    Planeta Arian - Tarun to małe ciało niebieskie rozmiarów mniej więcej Ziemi. Głównym kontynentem (również największym) jest Illantari i jest on położony mnie więcej na środku i jest otoczony wyspami : Everland, Barradar i innymi o wiele mniejszymi wysepkami. Sam kontynent dzieli się na Wschód i Zachód. Jednak na Wschodzie cywilizacje pojawiły się o wiele wcześniej niż na drugiej części kontynentu na której nigdy nie było spokoju. Od niepamiętnych czasów ludzie oraz elfy toczyły boje z barbarzyńskimi ludami zachodu. Jednak nie ulegli i końcu ustabilizowali swoje własne państwa. W podziemiach Illantari tak zwanym Undertide mieszkają Deugarowie. Jest to szczep krasnoludów który zszedł do podziemi w poszukiwaniu klejnotów i drogocennych kamieni, jednak byli tak zachłanni, że postanowili tam pozostać do końca swoich dni nie przejmując się zbytnio tym co dzieje się na powierzchni. Wojny na Illantari nie były niczym dziwnym, wręcz było to zjawisko dosyć powszechnie spotykane. Największą i najdłuższą wojną była tzw. Wojna Pokoleń między elfami a barbarzyńskimi plemionami Zachodu. Jednak zacznijmy od początku ...<br />
    <b>Początek</b><br />Nic tak właściwie nie wiadomo na temat jak powstało na Arian - Tarun życie. Nawet najstarsi bogowie nie wiedzą nic na ten temat (albo nie chcą wiedzieć ...). Jednak historia ras, która była przekazywana ustnie przetrwała do dziś a to za sprawą elfiego kronikarza. Jego nazwisko nie przetrwało do naszych czasów. Ale zacznijmy od początku, czyli od pierwszych wzmiankach o życiu na planecie.<br /> 
    Pierwszymi mieszkańcami na Arian - Tarun były gadopodobne rasy wywodzące się od jaszczurzego boga magii Iqaar Quarssira który stworzył ich na swoje podobieństwo. Ponieważ pochodzili od boga magii już od pierwszych chwil życia znali większość jej tajników. Po niedługim czasie zaczęli budować kamienne miasta i ogromne, również z kamienia, twierdze. Byli bardzo dobrze wykształceni ale po obaleniu Iqaar'a przez kobiecą połboginie, która narodziła się po związku złego boga - kapłana Myrkula z jedną z jaszczurzych kobiet, o imieniu Mystri zaczęli praktykować, zakazane dotąd, wszelakie tworzenie nowych istot. Tak powstała między innymi rasa brutalnych Orków a potem jej kolejny szczep - gobliny. Jednak jaszczury nie zamartwiali się zbytnio tymi rzeczami a coraz to nowe formy życia wypędzali po prostu do dżungli. Po wiekach spokojnej egzystencji nadeszło nagłe ochłodzenie klimatu co spowodowało, że ciepłolubne jaszczury po prostu przestały istnieć. Ich dzieła przeżyły ich samych ...<br /> 
    I tu zaczyna się kolejny rozdział w historii Arian - Tarun. Nowe gatunki które przeżyły ochłodzenie klimatu zaczęły ewoluować. I tak po latach w końcu powstało pierwsze państwo elfów, jak nazwano potem te stworzenia. Jak ziemskie imperium Rzymu po około ośmiu wiekach, gdy było już największym z ówczesnych państw, których było zaledwie sześć, zostało najechany przez wspomnianych wcześniej orków. Ostatni wiek elfiego imperium nazwano Latami Zmierzchu ...<br />
    <b>Lata Zmierzchu</b><br />
    Przez wiek elfom udawało się jako tako bronić ich królestwa przez co raz to zuchwalszymi wypadami orków w głąb imperium. Jednak zabrakło im po prostu pieniędzy na opłacenie wojska a niezadowolona ludność zaczęła uchodzić z kraju. Po 100 latach obrony Wielkie Elfie Imperium upadło a ostatni władca wraz z poddanymi uciekł na piękną i malowniczą wyspę Everland, gdzie z garstką ocalałych elfów zaczął wszystko od początku. Do legendy przeszły jego słowa, gdy ze statku oglądał płonącą stolicę. Słowa te nazwano Proroctwem Ostatniego. <i>Wszystko co ma początek ma i koniec, ale wszystko co prawe przetrwa. Jeszcze tam wrócimy...</i>. Proroctwo miało się spełnić dopiero za pięć wieków kiedy to potomek  (elfy żyją około pięćset lat) Ostatniego, Sundabar, zebrał wojsko i wraz z krasnoludami z wyspy Barradar przeprawił łodziami na kontynent i w bitwie, którą stoczyli na ruinach stolicy upadłego imperium, pokonał odziały orków dzięki aurze tamtego miejsca. Legendy głoszą, że po zwycięstwie był tak szczęśliwy, że całymi dniami wpatrywał się w ruiny dawnego pałacu królewskiego który był siedzibą dawnych władców Imperium.<br />
    Wracając do wydarzeń na Illantari wspomnieć trzeba o cywilizacjach Wschodu i dodać trzeba, że mało kto wędrował pomiędzy Wschodem i Zachodem i odwrotnie ze względu na ogromną wręcz pustynie która dzieliła obie strony. Jednak ludy wschodu żyły zupełnie inaczej niż ich bracia z Zachodu. Mało kto ze sobą walczył w tamtych krainach i przestępstwa i mordy były bardzo rzadko spotykane. Rozwinęły się tam w Latach Zmierzchu potężne i wielkie cywilizacje między innymi Imperium Wschodu Turach, które podobnie do ziemskiej Japonii było zamknięte handlowo i kulturalnie aż do tego czasu gdy pewien krasnoludki wojownik nie został pojmany za to, że bez pozwolenia wdarł się do pałacu władcy i powiedział co o tym wszystkim sądzi i jeżeli nie będą współpracować z innymi rasami i państwami wkrótce spotka ich to samo co Elfie Imperium którego władcy również woleli robić wszystko sami. Władca Turach wszystko przemyślał i po dwudziestu latach otworzył swój kraj przed obcymi. Było wielki krok do przodu. Jednak już po pięciu latach od otwarcia granic Turach najechały zastępy Trolli które zeszły z gór w poszukiwaniu pożywienia, a dodać trzeba, że ziemie Turach były jedne z żyźniejszych na Wschodzie. Trolle połączyły się z gigantami, którym również zaczęło brakować pożywienia. Gdy Turach było bliskie upadku sąsiednie państwo zaatakowało je ponieważ byli zainteresowani bogactwem tej Perły Wschodu. I wtedy nastąpił przełom. Władca Turach i akcie rozpaczy poprosił o sojusz trolle i giganty w zamian za możliwość osiedlenia się na jego ziemiach ale w zamian za pomoc w walce ze zdrajcą. Wbrew oczekiwać króla trolle zgodziły się na tą propozycje. Na ziemiach Turach oraz ziemiach podbitych ludzie nauczyli się koegzystować wspólnie z trollami oraz gigantami.<br /> 
    Wydarzenia na Zachodzie nie były już tak pomyślne. Ludzie oraz Złote Krasnoludy starały się żyć w zgodzie z plemionami goblinów. Jednak przez pewien czas, mimo paru konfliktów, które udało się zażegnać przez wybuchu wojny, udawało się zachować pokój. Kwitł handel z plemionami, które sprowadzały żywność a w szczególności ryby i mleko za różne wyroby rzemieślnicze. Ludzie handlowali płótnem, które gobliny wyrabiały przedniej jakości. Jednak to było zbyt piękne, żeby trwało długo. Gobliny weź większych oporów zaatakowały kraj ludzi. Po wielu bitwach ludzie z pomocą krasnoludów pokonali hordy goblinów a to dzięki jednemu z krasnoludzkich generałów, który podczas ostatniej bitwy sprowokował gobliny aby goniły z pozoru uciekające wojsko. Jednak z tyłu pozostawił duży odział pikinierów. Gdy dał komendę do zatrzymania się, gobliny w biegu wpadły na długie piki oddziału włóczników. Spłoszone uciekły a sam generał stracił tylko dziesięciu ludzi. Dzięki temu wspaniałemu zwycięstwu wojna została zażegnana.<br />
    <b>Początek Magii</b><br />
    Według starych kronik dopiero elfy z Imperium (oczywiście po jaszczurach) zaczęły używać magii. Jednak po migracji w Latach Zmierzchu rozniosły tą wiedzę po całym kontynencie. I tak nawet ludy wojownicze i barbarzyńskie zaczęły jej używać. Na początku pojawili się szamani i znachorzy, jednak nie długo potem już zwykli ludzie znali jej tajniki. Pierwszym magiem według legend był Llorkh. Potężny elfi czarownik, który na szczęście dla elfów i ludzi był z charakteru dobry i nie wdawał się w nekromancję. Potem magów było coraz więcej i byli coraz potężniejsi.<br />
    <b>Wojna Pokoleń</b><br />
    Już po dokonaniu się Proroctwa i wielu innych zdarzeniach elfy postanowiły ponownie stworzyć państwo na Illantari. Jednak zrobili to w nieodpowiednim czasie. Parę mil od granic w Elfie Zatoce (nazwano ja tak jeszcze w czasach Imperium) wylądowali barbarzyńcy. Całe hordy barbarzyńców (była to mieszanina ras) zalały najpierw Kraj Elfów a potem wszystkie inne. Elfy wraz z innymi utworzyli partyzantkę. Po stu latach partyzanckich walk ludzie zorganizowali sojusz który nazwano potem <i>Świętym Przymierzem</i>. Po kolejnych czterech wiekach walk, wyczerpani, w końcu pokonali barbarzyńców. Historia lubi się powtarzać i znowu decydująca bitwa miała miejsce na ruinach stolicy dawnego Imperium. Wszystko wróciło do normy, i znowu przez lata rozkwitał handel a Święte Przymierze nadal chroni granic państw ...<br />
    <b>Teraźniejszość</b><br />
    Przez wieki upadło wiele państw a wiele rozkwitło. Z niepozornego związku miast nadmorskich wyrosło potężne Vallheru, w którym władze objął Thindil wraz z Radą Lordów, czyli sześciu najbogatszych mieszkańcom kraju. Pojawili się Paladyni, którzy mieli chronić ludzi od  zła i nieszczęścia. Jednak było ich zamało i nie dawali sobie rady z falą przestępczości. Władca musiał sprowadzić Gildie Upadłych Paladynów, którzy chociaż byli z natury źli bez większych problemów poradzili sobie z mordercami i złodziejami. Na największą gildię złodziejską wyrośli Zabójcy Cienia, którzy prowadzili krwawe wojny z Paladynami. Za granicami Vallheru wyrosły nowe kraje elfów i krasnoludów. Kwitnie handel i przemysł. Święte Przymierze czuwa nad pokojem. Orkowie żyją w pokoju chociaż czasami potrzebny jest zbrojny konflikt to jednak nie stanowią zagrożenia. Udało się ujarzmić wielką pustynię pomiędzy Wschodem, więc nie brakuje podróżników. Zjednoczyły się plemiona krasnoludzie na kontynencie pod władzą Duriona. Wydobywają one spore ilości kamieni szlachetnych i klejnotów, wiec rozwija się handel na Zachodniej tronie Illantari. Na Wschodzie dominuje Zjednoczone Królestwo Turach, które jako jedyne państwo na Arian - Tarun sprzedaje na Zachód katany i inne wyroby militarne. Przyszłość zapowiada się obiecująco ...");
}

if (isset($_GET['help']) && $_GET['help'] == 'overview')
{
    define("HELP2", "Informacje ogólne zawierają dane dotyczace postaci gracza . Dzielą sie na 3 działy :
    <ol>
    <li>Statystyki w grze</li>
    <li>Informacje</li>
    <li>Umiejętności</li>
    </ol>
    <i><b>Statystyki w grze :</b></i><br /><br />
    <b>AP - Astralne Punkty</b> . - Są to punkty , które postać otzymuje przy każdym awansie o poziom wyżej . Za każdy poziom naliczane jest 5 pnktów AP . Punkty te slużą do podnoszenia statystyk postaci . W zależności od rasy i klasy odpowiednio można podnosić Zręczność , Siłe , Inteligencję , Siłe Woli , Szybkość oraz Wytrzymałość o pewną liczbę .<br />
    <b>Rasa</b> - Określa rasę postaci . W grze wystepują trzy rasy tj.Człowiek , Elf oraz Krasnolud .<br />
    <b>Klasa</b> - Określa klasę postaci (profesję) . W grze wystepują trzy klasy tj. Wojownik , Mag oraz Obywatel .<br />
    <b>Zręczność</b> - Jest to liczba określająca zręczność postaci . Odpowiedzialna za szansę trafienia przeciwnika w przypadku ataku oraz zwieksza obrażenia zadawane podczas walki przy użyciu łuku . Dla gracza broniącego sie zwieksza prawdopodobieństwo uniknięcia ciosu .<br />
    <b>Siła</b> - Jest to liczba określająca siłę postaci . Odpowiedzialna za ilość obrażen zadawanych podczas walki (Wprost proporcionalna)<br />
    <b>Inteligencja</b> - Jest to liczba określająca inteligencję postaci . Odpowiedzialna za ilość obrażen zadawanych w walce magicznej oraz ilość Punktów Magii .<br />
    <b>Siła Woli</b> - Jest to liczba określająca siłę woli postaci . Odpowiedzialna za efektywność czarów obronnych podczas walki magicznej oraz ilość Punktów Magii .<br />
    <b>Szybkość</b> - Jest to liczba określająca szybkość postaci . Odpowiedzialna za inicjatywe podczas walki oraz ilość możliwych ataków wykonanych podczas jednej rundy walki .<br />
    <b>Wytrzymałość</b> - Jest to liczba określająca wytrymałość postaci . Odpowiedzialna za obrażenia przyjmowane podczas walki (Odwrotnie proporcionalna)<br />
    <b>Punkty Magii</b> -  Jest to liczba punktów magii jakie posiada postać . Odpowiedzialna za ilość czarów jakie może rzucić dana postać w jednej walce . (w zależności od rodzaju czaru ilość ta może sie zwiekszać lub zmniejszać) Liczba posiadanych punktów magii przez gracza zależna jest od jego inteligencji oraz siły woli (wprost proporcionalna).<br />
    <b>Punkty Wiary</b> - Jest to liczba punktów wiary jakie posiada postać . Punkty wiary można zdobyć poprzez prace dla Świątyni (mieści sie w ".$city1a.") oraz nastepnie wykorzystać je na modlitwe do boga również w Świątyni .<br />
    <b>Wyniki</b> - Są to trzy liczby określające kolejno : wygrane pojedynki / przegrane pojedynki / ogólna liczba stoczonych pojedynków<br />
    <b>Ostatnio zabity</b> - Jest to informacja z pseudonimem gracza ostatnio pokonanego przez naszą postać w pojedynku .<br />
    <b>Ostatnio zabity przez</b> - Jest to informacja z pseudonimem gracza , który jako ostatni zwyciężył naszą postać w pojedynku .<br /><br />
    <b><i>Informacje :</i></b><br /><br />
    <b>Ranga</b> -  Określa rangę naszej postaci w społeczeństwie . Rangi dzialą sie na : mieszkaniec, prawnik, ławnik, sędzia, rycerz, dama, książę ,władca, bohater<br />
    <b>Lokacja</b> -  Określa lokację w jakiej sie właśnie znajdujemy . Lokacje dzielą sie na ".$city1." , Góry Kazad-nar oraz Las Avantiel .<br />
    <b>Wiek</b> -  Określa wiek naszej postaci .<br />
    <b>Logowań</b> - Określa liczbe logowań na nasze konto w przeciągu całej naszej gry .<br />
    <b>IP</b> - Ip komputera z ktorego jestesmy zalogowani do gry .<br />
    <b>Email</b> - Nasz adres internetowej poczty na którą zalogowane jest nasze konto .<br />
    <b>Klan</b> -  Informacja na temat klanu do , którego przynależymy .<br /><br />
    <b><i>Umiejętności :</i></b><br /><br />
    <b>Kowalstwo</b> - Liczba określająca zaawansowanie umiejętności kowalstwo . Odpowiedzialna za prawdopodobieństwo wykucia przedmiotu (wprost proporcjonalna)<br />
    <b>Alchemia</b> - Liczba określająca zaawansowanie umiejętności alchemia . Odpowiedzialna za prawdopodobieństwo wykonania mikstury (wprost proporcjonalna)<br />
    <b>Stolarstwo</b> - Liczba określająca zaawansowanie umiejętności stolarstwo . Odpowiedzialna za prawdopodobieństwo wykonania łuku lub strzał .<br />
    <b>Walka bronią</b> - Liczba określająca zaawansowanie umiejętności walka bronią . Wraz ze wzrostem tej umiejetności wzrastaja premie w walce bronią .Im wieksze premie tym wieksze prawdopodobieństwo wygrania walki .<br />
    <b>Strzelectwo</b> - Liczba określająca zaawansowanie umiejętności strzelectwo . Wraz ze wzrostem tej umiejetności wzrastaja premie w walce przy urzyciu łuku .Im wieksze premie tym wieksze prawdopodobieństwo wygrania walki .<br />
    <b>Unik</b> - Liczba określająca zaawansowanie umiejętności unik. Odpowiedzialna za prawdopodobieństwo unikniecia ciosiu przeciwnika w walce .(wprost proporcjonalna)<br />
    <b>Rzucanie czarów</b> - Liczba określająca zaawansowanie umiejętności rzucanie czarów . Wraz ze
    wzrostem tej umiejetności wzrastaja premie w walce przy urzyciu magii .Im wieksze premie tym wieksze prawdopodobieństwo wygrania walki .");
}

if (isset($_GET['help']) && $_GET['help'] == 'eventlog')
{
    define("HELP3", "W Dzienniku są zapisane informacje na temat przebiegu wydarzen zwiazanych z Twoja postacia w świecie Vallheru .Dziennik zawiera treści na temat przebiegu pojedynków stoczonych z innymi postaciami , informuje o zaakceptowanych ofertach sprzedarzy towaru wczesniej wystawionego przez Ciebie na rynku tj. minerały , przedmioty , zioła itp.oraz powiadamia o nowych wiadomościach na Twojej poczcie Vallheru . Dziennik notuje wydarzenia zarówno podczas naszej obecności jak i nieobecności w grze . Liczba w nawiasie znajdująca sie obok odsyłacza <b>Dziennik</b> informuje o ilości nowych wydarzeń.");
}

if (isset($_GET['help']) && $_GET['help'] == 'equipment')
{
    define("HELP4", "W Ekwipunku znajdują sie przedmioty jakie posiada nasza postać .<br /><br />
    W przypadku klasy postaci wojownik lub obywatel :<br /><br />
    <b>Obecnie używane przedmioty</b> - są to przedmioty obecnie używane przez nasza postać . Dzielą sie na :<br /><br />
    <b><i>Broń</i></b> - czyli broń jaka w danej chwili sie poslugujemy podczas walki . Broń określana jest nastepującymi charakterystykami :
    <br />np<br />
    -  brązowy sztylet (+1) (+0% szyb) (10/10 wytrzymałości)<br />(+1) - czyli w walce otzymujemy premię +1 do obrażeń ,<br />(+0% szyb.) -
    czyli podczas walki otrzymujemy premię do szybkości w wysokości +0%  ,<br />(10/10 wytrzymałości) - określa poziom zniszczenia naszej broni . W przypadku dopuszczenia do spadku wytrzymałości naszej broni do 0/10 , broń ulega zniszczeniu .<br /><br />
    <b><i>Hełm</i></b> - czyli okrycie głowy  postaci jakiego aktualnie używamy podczas walki .Hełm określa się następującymi charaterystykami :
    <br />np<br />
    - skórzany kaptur (+1) (10/10 wytrzymałości)<br />(+1) - czyli w walce otrzymujemy premię +1 do obrony ,<br />(10/10 wytrzymałości) - określa poziom zniszczenia naszego hełmu  . W przypadku dopuszczenia do spadku wytrzymałości naszego hełmu do 0/10 ,hełm ulega zniszczeniu.<br /><br />
    <b><i>Zbroja</i></b> - czyli okrycie korpusu postaci jakiego aktualnie używamy podczas walki .Zbroja określa się następującymi charaterystykami :
    <br />np<br />
    - grube ubranie (+1) (-1 % zr) (10/10 wytrzymałości)<br />(+1) - czyli w walce otrzymujemy premię +1 do obrony ,<br>(-1 % zr) - czyli podczas walki zbroja ogranicza nasza zręczność o 1% ,<br />(10/10 wytrzymałości) - określa poziom zniszczenia naszej zbroi  . W przypadku dopuszczenia do spadku wytrzymałości naszej zbroi do 0/10 , zbroja ulega zniszczeniu .<br /><br />
    <b><i>Nagolenniki</i></b> - czyli okrycie dolnych parti ciała postaci aktualnie używanych podczas walki . Nagolenniki określają sie następującymi charaterystykami :
    <br />np<br />
    - skórzane ochraniacze (+1) (-0 % zr) (10/10 wytrzymałości)<br>(+1) - czyli w walce otrzymujemy premię +1 do obrony ,<br>(-0 % zr) - czyli podczas walki nagolenniki ograniczają naszą zręczność o 0% ,<br />(10/10 wytrzymałości) - określa poziom zniszczenia naszych nagolenników. W przypadku dopuszczenia do spadku wytrzymałości naszych nagolenników do 0/10 , nagolenniki  ulegają zniszczeniu .<br /><br />
    Nastepnie znajduje sie pozycja [napraw używane] . W przypadku użycia tej opcji wszystkie przedmoity znajdujące sie w danej chwili w <b>obecnie używanych przedmiotach</b> oraz posiadające jakiekolwiek zniszczenia - czyli niepełną wytrzymałość - zostaną naprawione kosztem naszego złota .<br /><br />
    W przypadku nie używania jakich kolwiek przedmiotow naszego ekwipunku , pojawią sie one poniżej jako <b>przedmioty zapasowe</b> . W takim przypadku można je sprzedać za 75% wartości detalicznej , lub też każdy z osobna naprawić .<br /><br /><br />
    W przypadku klasy postaci mag :<br /><br />
    <b>Obecnie używane przedmioty</b> - są to przedmioty obecnie używane przez nasza postać . Dzielą sie na :<br /><br />
    <b><i>Różdżka</i></b> - czyli przedmiot magiczny jakim w danej chwili sie poslugujemy podczas walki . Różdżka określana jest nastepującymi charakterystykami :
    <br />np<br />
    - elfia różdżka (+10 % siły czarów)<br>(+10 % siły czarów)- czyli podczas walki magicznej otrzymujemy premie do siły czarów +10%<br /><br />
    <b><i>Hełm</i></b> - czyli okrycie głowy  postaci jakiego aktualnie używamy podczas walki .Hełm określa się następującymi charaterystykami
    :<br />np<br />
    - skórzany kaptur (+1) (10/10 wytrzymałości)<br />(+1) - czyli w walce otrzymujemy premię +1 do obrony ,<br />(10/10 wytrzymałości) - określa poziom zniszczenia naszego hełmu  . W przypadku dopuszczenia do spadku wytrzymałości naszego hełmu do 0/10 ,hełm ulega zniszczeniu.<br /><br />
    <b><i>Szata</i></b>  - czyli okrycie korpusu postaci jakiego aktualnie używamy podczas walki .Szata określa się następującymi charaterystykami :
    <br />np<br />
    -elfie szaty (+10 % punktów magii)<br />(+10 % punktów magii) - czyli podczas walki nasze punkty magii zwiekszają sie o 10%<br /><br />
    <b><i>Nagolenniki</i></b> - czyli okrycie dolnych parti ciała postaci aktualnie używanych podczas walki . Nagolenniki określają sie następującymi charaterystykami :
    <br />np<br />
    - skórzane ochraniacze (+1) (-0 % zr) (10/10 wytrzymałości)<br />(+1) - czyli w walce otrzymujemy premię +1 do obrony ,<br />(-0 % zr) - czyli podczas walki nagolenniki ograniczają naszą zręczność o 0% ,<br />(10/10 wytrzymałości) - określa poziom zniszczenia naszych nagolenników. W przypadku dopuszczenia do spadku wytrzymałości naszych nagolenników do 0/10 , nagolenniki  ulegają zniszczeniu .<br /><br />
    Nastepnie znajduje sie pozycja [napraw używane] . W przypadku użycia tej opcji wszystkie przedmoity znajdujące sie w danej chwili w <b>obecnie używanych przedmiotach</b> oraz posiadające jakiekolwiek zniszczenia - czyli niepełną wytrzymałość - zostaną naprawione kosztem naszego złota .<br /><br />
    W przypadku nie używania jakich kolwiek przedmiotow naszego ekwipunku , pojawią sie one poniżej jako <b>przedmioty zapasowe</b> . W takim przypadku można je sprzedać za 75% wartości detalicznej , lub też każdy z osobna naprawić .");
}

if (isset($_GET['help']) && $_GET['help'] == 'battle')
{
    define("HELP5", "Walka jest głównym punktem gry. Im więcej posiadasz Zręczności, tym częściej trafisz przeciwnika. Im więcej posiadasz Siły, tym więcej obrażeń zadajesz. Oprócz tego w grze dostępne są również bronie i zbroje. Im silniejsza jest twoja broń (pokazywana z +) tym więcej jest dodawane do zadawanych obrażeń. Im silniejsza jest twoja zbroja (ponownie, pokazywana z +) tym mniej obrażeń otrzymujesz podczas ataku przeciwnika. Na przykład, przeciwnik ma 13 siły a ty masz zbroję +10. Otrzymujesz tylko 3 obrażenia. Ten, kto straci jako pierwszy wszystkie Punkty Życia przegrywa walkę, natomiast jego przeciwnik zyskuje PD oraz sztuki złota za walkę. Jeżeli PD przekroczą odpowiednią wartość, zwycięzca zdobywa poziom.");
}

if (isset($_GET['help']) && $_GET['help'] == 'money')
{
    define("HELP6", "Pieniądze w grze, dzielą się na dwa typy: sztuki złota oraz sztuki mithrilu. Sztuki złota są podstawową jednostką monetarną w grze i są najczęściej używane. Sztuki mithrilu są znacznie rzadsze i mogą być znalezione tylko w trzech miejscach w grze (w <b>Labiryncie</b>, można je również kupić w <b>Sklepie z Mithrilem</b> oraz na <b>Rynku</b> od innych graczy). Mithril na razie może być używany tylko do zakupu kopalni oraz do zakupu darmowego leczenia przez klan.");
}

if (isset($_GET['help']) && $_GET['help'] == 'energy')
{
    define("HELP7", "Energia jest decydującym współczynnikiem, co możesz robić w ciągu dnia. Jeżeli osiągnie zerową wartość, musisz poczekać do resetu, wtedy zostanie odnowiona. Reset zdarza się o 12 godzinie i parę razy dziennie o różnych porach.");
}

if (isset($_GET['help']) && $_GET['help'] == 'faq')
{
    define("HELP8", "Tutaj znajdziesz odpowiedzi, na najczęściej zadawane pytania. Jeżeli chciałbyś się czegoś dowiedzieć o grze, po prostu skontaktuj się ze mną");
    define("HELP8_1", "mailem");
    define("HELP8_2", ", wysyłając pocztę w grze, zadając swoje pytanie na <b>Forum</b> lub podczas rozmowy w <b>Karczmie</b>. Najciekawsze i najczęściej powtarzające się pytania zostaną tutaj umieszczone wraz z odpowiedzami.<br /><br />
    <ul>
    <li>Jak odzyskiwać energię?<br />
    Energię odzyskujesz podczas resetu gry. Resety zdarzają się o godzinach 12 (nowy dzień na Vallheru) oraz o 14,16,18,20,22 czasu gry.
    </li>
    <li>Czy można wymieniać Mithril na złoto ? Jak tak to gdzie?<br />
    Mithril możesz sprzedawać na targu, jednak wszystko zależy od tego czy ktokolwiek zechce go kupić.
    </li>
    <li>Czy można mieć dwa konta na Vallheru ?<br />
    Nie
    </li>
    <li>Kiedy będzie najbliższy nabór na księcia ? i czy mam szansę nim zostać ?<br />
    O wyborze księcia decyduje sejm , tak samo jak i o jego odwołaniu. Jeśli twoja kandydatura otrzyma wystarczające poparcie , wtedy masz szansę zostać Księciem.
    </li>
    <li>Czy aby przyłączyć się do któregoś z klanów powinienem pytać o pozwolenie przywódcę klanu, i czy są jakieś ograniczenia musze mieć odpowiedni poziom czy coś w tym rodzaju ?<br />
    Przywódca przyjmuje twoją kandydaturę więc nie można dostać się do klanu nie kontaktując się z jego przywódcą. Wymagania każdy klan stawia sobie sam i jeśli nie zechcą mogą Cię nie przyjąć , jednak w grze niema żadnych fizycznych (jeśli można to tak ująć) ograniczeń poziomu itd.
    </li>
    <li>Czy jeśli dołączę do jakiegoś klanu to już nie będę mógł go opuścić ?<br />
    Klan można opuścić w dowolnym momencie, nie potrzebna jest do tego zgoda przywódcy.
    </li>
    <li>Za co odpowiada  każda z cech ?<br />
    Patrz : Pomoc - Ogólne informacje
    </li>
    <li>Jak można dostać immunitet ?<br />
    Immunitety przyznaje władca na prośbę gracza bądź też samodzielnie w Opcjach konta.
    </li>
    <li>Jak pozbyć się immunitetu ?<br />
    Poprzez reset postaci. ( Opcja ta dostępna jest z poziomu menu \"Opcje Konta\" )
    </li>
    <li>Czy to normalne że jak się jest na (np.) 86 poziomie to pokonuje się potworki na (np.) 185 poziomie ?<br />
    Oczywiście że tak. Potwory w przeciwieństwie do ekwipunku nie mają wymagań minimalnego poziomu. Poziom potwora oznacza poziom trudności przeciwnika w tym przypadku.
    </li>
    <li>Czy ilość uzyskanego drewna w lesie zależy od statystyk(np. siły, zręczności)? A może to po prostu są stałe liczby wybierane losowo? (To samo pytanie odnośnie ziółek itd.)<br />
    Ilość ziół które znajdujesz generowana jest losowo, materiały kopalne zależą generalnie od siły postaci (tak jak to jest w kopalni) . Obywatele mają jeszcze bonus od poziomu, jednak tyczy się to tylko tej klasy.
    </li>
    <li>Nie mogę walczyć na arenie chowańców , czy musze mieć 2 przeciwnika ?<br />
    Wchodząc na arenę chowańców powinna wyświetlić ci się list stworków które możesz zaatakować, jeśli takowa się nie pojawiła musisz przejść do zakładki \"moje chowańce\"  a tam po wybraniu Chowańca którego chcesz wysłać do walki aktywować go.
    </li>
    <li>Cały czas zdobywam doświadczenie w posługiwaniu się różdżkami, jaki wpływ ma to na walkę ?<br />
    Umiejętność posługiwania się różdżkami u magów to dokładnie to samo co umiejętność walki bronią u wojowników , czy strzelectwo u łuczników. Im więcej takiej cech masz tym skuteczniej posługujesz się danym rodzajem broni i większe bonusy do ataku dostajesz.
    </li>
    <li>Od czego zależy szansa na umagicznienie przedmiotu ?<br />
    Od zdolności rzucania czarów, poziomu czaru oraz poziomu przedmiotu.
    </li>
    <li>Dlaczego kiedy chcę kupić broń która jest słabsza od tej której używam pojawia się informacja o zbyt niskim poziomie ?<br />
    Ponieważ broń którą chcesz kupić ma wyższy poziom podstawowy niż ten , którego możesz używać. Broń może zostać umagiczniona lub wykonana przez nader zręcznego kowala , dzięki czemu jej właściwości przekraczają poziom podstawowy , ta jednak utrzymuje swój minimalny poziom użytkowania.
    </li>
    <li>Do czego służą Vallary ?<br />
    Jak na razie nie znamy ich zastosowania , badania trwają
    </li>
    <li>Ile potrzeba punktów magii na ulepszenie przedmiotu ?<br />
    Tyle ile wynosi poziom rzucanego czaru.
    </li>
    <li>Jak zdobywa się punkty magii ?<br />
    Są dwie metody: Po pierwsze poprzez wypicie mikstury regenerującej , po drugie przez opcję odpoczynku znajdującą się w ekranie statystyk
    </li>
    <li>Jakie sa szanse na to że gdy się modli do jakiegoś boga to się otrzyma dar ?<br />
    Otrzymanie daru jest czynnikiem w pełni losowym, może zdarzyć się iż zużywając 100 punktów wiary 60 razy otrzymasz jakiś dar , a może zdarzyć się iż nie otrzymasz prawie nic.
    </li>
    <li>Czy jak będę miał więcej punktów wiary to mam większe szanse podczas modlenia się na cud podczas modlenia się ?<br />
    Tak , jest to prosta zależność : im więcej masz punktów wiary , tym więcej masz szans na cud. ( 1 punkt = 1 losowanie )
    </li>
    <li>Dlaczego mikstury many są takie drogie ?<br />
    Im większy poziom Inteligencji , Siły Woli i Poziom Postaci tym więcej kosztuje Cię miksturka
    </li>
    <li>Od czego zależy ilość sztuk złota zdobytych za jeden worek śmieci ?<br />
    Od poziomu postaci.
    </li>
    </ul>");
}

if (isset($_GET['help']) && $_GET['help'] == 'indocron')
{
    if (!isset($_GET['step']))
    {
        define("HELP_INFO2", "Altara jest stolicą świata Vallheru. Znajdziesz tutaj wiele interesujących miejsc. Miasto jest podzielone na kilka dzielnic, w każdej z nich znajdują się różne interesujące lokacje.");
        define("A_BATTLE", "Wojenne pola");
        define("A_WEST", "Zachodnia strona");
        define("A_JOB", "Praca");
        define("A_SOC", "Społeczność");
        define("A_HOME", "Dzielnica mieszkalna");
        define("A_SOUTH", "Dzielnica południowa");
        define("A_VILLAGE", "Podgrodzie");
        define("A_CASTLE", "Zamek");
        define("INFO", "Kliknij na nazwę dzielnicy, aby zobaczyć co się w niej znajduje.");
    }
    if (isset($_GET['step']) && $_GET['step'] == 'mieszkalna')
    {
        define("HELP_INFO3", "W <b>Dzielnicy mieszkalnej</b> znajdziesz różne interesujące informacje na temat osób grających w Valleru. Jeżeli chcesz się dowiedzieć więcej o jakiejś lokacji, kliknij na jej nazwie.");
        define("A_LIST", "Spis mieszkańców");
        define("A_MONUMENTS", "Posągi");
        if (isset($_GET['step2']) && $_GET['step2'] == 'spis')
        {
            define("HELP_TEXT", "W <b>Spisie mieszkańców</b> znajdziesz listę wszystkich osób grających w Vallheru. Podane są tutaj numer gracza, imię, ranga (Władca, Książę, Mieszkaniec itd) oraz poziom. Jeżeli chcesz się dowiedzieć czegoś więcej o jakimś graczu, kliknij na jego imię. Pojawią się wtedy dodatkowe informacje: ranga, ostatnio widziany - gdzie ostatnio przebywał, wiek (wiek postaci w dniach), poziom, status (żywy/martwy), klan do którego należy, maksymalne PŻ (maksymalne zdrowie), wyniki (bitwy z innymi graczami - pierwsza liczba to wygrane, druga przegrane), ostatnio zabił (ostatnio zabity gracz przez tą osobę, ostatnio zabity przez (kto go ostatnio zabił), poleceni (ile osób zapisało się do gry z jego linku polecającego. Więcej o poleconych w opisie <i>Poleceni</i>), Profil (dodatkowe informacje na temat gracza). Na dole znajduje się opcja <b>Atak</b> - klikając na nią przystępujesz do ataku na danego gracza.");
        }
        if (isset($_GET['step2']) && $_GET['step2'] == 'posagi')
        {
            define("HELP_TEXT", "W <b>Posągach</b> otrzymamy informacje na temat postaci, które osiągneły najwyższy poziom w danej dziedzinie . Są to np statystyki graczy lub ich umiejetności czy tez ilość posiadanego bogactwa. W każdej tabeli znajduje się 5 najlepszych graczy w danej dziedzinie.");
        }
    }
    if (isset($_GET['step']) && $_GET['step'] == 'podgrodzie')
    {
        define("HELP_INFO4", "Na <b>Podgrodziu</b> znajdują się dwie lokacje. Jeżeli chcesz się dowiedzieć czegoś więcej o nich, po prostu kliknij na nazwę miejsca.");
        define("A_SCHOOL", "Szkoła");
        define("A_MINE", "Kopalnia");
        define("A_CORE", "Polana Chowańców");
        if (isset($_GET['step2']) && $_GET['step2'] == 'szkola')
        {
            define("HELP_TEXT", "Szkoła jest czymś w rodzaju siłowni w której za opłatą możemy podnieść swoje statystyki. W zależności od rasy i klasy ilość podniesionej statystyki ulega zmianie. Cena jednego ćwiczenia uzależniona jest od poziomu postaci. Jeżeli chcesz trenować jakąś cechę, po prostu wybierz odpowiednią cechę, w polu obok wpisz ilę razy chcesz ćwiczyć i wciśnij przycisk <i>Trenuj</i>.");
        }
        if (isset($_GET['step2']) && $_GET['step2'] == 'kopalnia')
        {
            define("HELP_TEXT", "Tu mozna wykupić obszar kopalni dzieki któremu zapeniamy sobie stałe dochody . W póżniejszych fazach rozwoju możemy dokupić sobie obszary kopalni za złoto.");
        }
        if (isset($_GET['step2']) && $_GET['step2'] == 'chowance')
        {
            define("HELP_TEXT", "Chowance to takie Vallherowskie pokemony. Cala zabawa polega na tym aby złapać chowańca i go tresować , a nastepnie wystawiać go na walki. Za wygrane walki chowancow mozemy zarobic Mithril .");
        }
    }
    if (isset($_GET['step']) && $_GET['step'] == 'poludniowa')
    {
        define("HELP_INFO5", "W <b>Południowej dzielnicy</b> znajduje się największy rynek na Vallheru oraz Stajnia, skąd możesz wyruszyć w inne strony świata Vallheru. Aby dowiedzieć się czegoś więcej o danej lokacji, kliknij na jej nazwę.");
        define("A_MARKET", "Rynek");
        define("A_TRAVEL", "Stajnia");
        if (isset($_GET['step2']) && $_GET['step2'] == 'rynek')
        {
            define("HELP_TEXT", " Na rynku możemy zarówno zakupić jak i wystawić ofertę sprzedarzy  róznego rodzaju przedmiotów , ziół , minerałów oraz mikstur.");
        }
        if (isset($_GET['step2']) && $_GET['step2'] == 'stajnia')
        {
            define("HELP_TEXT", "Stajnia to miejsce z którego możemy podróżować po innych lokacjach w świecie Vallheru . Znajdują sie tam lokacje takie jak :
            <ol>
            <li> ".$city1." - stolica Vallheru</li>
            <li> Góry Kazad-nar</li>
            <li> Las Avantiel</li>
            </ol>
            Każdy jednorazowa podróż w jedna strone koszuje 1000 zl.");
        }
    }
    if (isset($_GET['step']) && $_GET['step'] == 'praca')
    {
        define("HELP_INFO6", "Jeżeli brakuje ci pieniędzy, możesz podjąć jakąś pracę.");
        define("A_CLEAR", "Oczyszczanie miasta");
        define("A_SMITH", "Kuźnia");
        define("A_ALCHEMY", "Pracownia alchemiczna");
        define("A_LUMBERMILL", "Tartak");
        if (isset($_GET['step2']) && $_GET['step2'] == 'oczyszczanie')
        {       
            define("HELP_TEXT", "Tu mozemy zarobic na zycie w postaci prac porządkowych dla miasta . Praca ta odbywa sie kosztem naszej energii 1 energi za kazde 25 zł razy poziom gracza.");
        }
        if (isset($_GET['step2']) && $_GET['step2'] == 'kuznia')
        {
            define("HELP_TEXT", "Kuźnia to miejsce,  w którym możemy zając sie wykonywaniem broni. Aby wykonać jaki kolwiek przedmiot należy najpierw zakupić plan danej broni , a nastepnie wybrać opcje <b>Idź do kużni</b>.");
        }
        if (isset($_GET['step2']) && $_GET['step2'] == 'alchemik')
        {
            define("HELP_TEXT", "W pracowni alchemicznej możemy podjąć sie wykonywania mikstur . Aby to zrobić należy w pierwszej kolejności zakupić przepis na miksture , a nastepnie udać sie do pracowni aby ją sporządzić .");
        }
        if (isset($_GET['step2']) && $_GET['step2'] == 'tartak')
        {
            define("HELP_TEXT", "Tartak jest miejscem gdzie możemy podjąć sie wykonania łuków lub strzał . Aby to zrobic należy w pierwszej kolejnośći zakupic plany danego przedmiotu a następnie udać sie do tartaku aby go wykonać .");
        }
    }
    if (isset($_GET['step']) && $_GET['step'] == 'spol')
    {
        define("HELP_INFO7", "W dziale <b>Społeczność</b> znajdziesz różne iformacje na temat społeczności w grze. Oprócz tego, tutaj znajdują się miejsca, gdzie możesz orozmawiać z innymi graczmi oraz klany graczy. Jeżeli chcesz się dowiedzieć więcej na jakiś temat, po prostu kliknij na interesującą ciebie rzecz.");
        define("A_NEWS", "Plotki");
        define("A_CLANS", "Klany");
        define("A_INN", "Karczma");
        define("A_FORUMS2", "Forum");
        define("A_MAIL2", "Poczta");
        if (isset($_GET['step2']) && $_GET['step2'] == 'plotki')
        {
            define("HELP_TEXT", "Miejskie Plotki, to miejsce gdzie znajdziesz różne informacje z życia społeczności Vallheru, takie jak promocje graczy na wyższe rangi, ogłoszenia o konkursach, kary dla oszustów oraz tym podobne informacje.");
        }
        if (isset($_GET['step2']) && $_GET['step2'] == 'forum')
        {
            define("HELP_TEXT", "Na Forum możesz wymienać się poglądami z innymi członkami społeczności. Na stronie jest pokazane są tematy ich autorzy oraz ilość odpowiedzi. Jeżeli chcesz zacząć nową dyskusję, napisz nazwę tematu oraz swoją wypowiedź. Jeżeli ineteresuje ciebie jakiś temat po prostu kliknij na niego. Będziesz mógł wtedy przeczytać wszystkie wypowiedzi innych jakie zamieszczone są w tym temacie. Na końcu znajduje się formularz w którym możesz napisać odpowiedź na dany temat. Odnośnik do Forum znajduje się również w menu <i>Nawigacja</i>");
        }
        if (isset($_GET['step2']) && $_GET['step2'] == 'karczma')
        {
            define("HELP_TEXT", "Karczma to po prostu czat, na którym możesz porozmawiać z innymi graczami. Co jakiś czas to co znajduje się w czacie jest usuwane. Możesz tutaj rozmawiać na dowolne tematy (opcja szepnij na razie nie jest aktywna). Jeżeli chcesz odpowiedzieć komuś, po prostu wpisz swoją wiadomość w polu u góry ekranu i naciśnij Enter lub klawisz Wyślij. Obok pola znajduje się opcja odświeżania czata (<b>odśwież</b>). Na dole ekranu możesz zobaczyć, kto jest na czacie, ile jest wypowiedzi na nim oraz liczbę graczy na czacie. Opcje na dole ekranu są chwilowo nie aktywne. Sam czat posiada opcję automatycznego odświeżania. Odnośnik do Karczmy znajduje się również w menu <i>Nawigacja</i>, tutaj obok odnośnika znajduje się również informacja, ilu obecnie jest graczy na czacie (jeżeli jesteś na czacie, to liczone są wszystkie osoby oprócz ciebie)");
        }
        if (isset($_GET['step2']) && $_GET['step2'] == 'poczta')
        {
            define("HELP_TEXT", "Poczta jest to twoja wewnętrzna skrzynka pocztowa w grze. Możesz tutaj wysyłać informacje do innych graczy. Opcja <b>Skrzynka</b> zaprowadzi cię do twojej skrzynki pocztowej. Tutaj zobaczysz wszystkie listy jakie posiadasz. W tabeli jest podane od kogo jest dany list, numer nadawcy w grze, temat listu oraz opcja <b>czytaj</b> - czyli po prostu przeczytanie danego listu. Podczas czytania listu, masz również możliwość odpowiedzenia na niego, przy pomocy opcji <b>Odpisz</b>. Na dole znajdują się dodatkowe opcje: <b>Wyczyść skrzynkę</b> - usuwa wszystkie listy jakie posiadasz w skrzynce, <b>Napisz</b> napisz list do kogoś (ta opcja znajduje się również na głównym ekranie skrzynki). Aby napisać do kogoś list (bądź odpowiedzieć na czyjś list). Musisz wypełnić odpowiednie pola: <i>Do (ID Numer)</i> tutaj musisz wpisać numer (nie imię, tylko numer) osoby, do której chcesz wysłać list. <i>Temat:</i> - po prostu temat wiadomości. W polu <i>Treść</i> wpisz po prostu treść listu. Aby wysłać list, kliknij przycisk Wyślij. Za każdym razem, kiedy dostaniesz jakiś list od kogoś, zostaniesz o tym powiadomiony w <b>Dzienniku</b>.");
        }
        if (isset($_GET['step2']) && $_GET['step2'] == 'klany')
        {
            define("HELP_TEXT", "Tutaj znajdziemy informacje na temat istniejących już w grze klanów w odnośniku <b>zobacz liste klanów</b>. Mozemy także założyć własny klan kosztem nie małych pieniędzy , a również w przypadku przynależenia do klanu korzystać z opcji zawartych w odnośniku <b>Mój klan</b>.");
        }
    }
    if (isset($_GET['step']) && $_GET['step'] == 'wojenne')
    {
        define("HELP_INFO8", "Wojenne Pola to miejsce, gdzie znajdziesz lokacje związane z walką. Kliknij na nazwę miejsca aby otrzymać więcej wiadomości na jego temat.");
        define("A_ARENA2", "Arena Walk");
        define("A_WEAPON", "Zbrojmistrz");
        define("A_ARMOR", "Płatnerz");
        define("A_FLETCHER", "Fleczer");
        define("A_OUTPOST", "Strażnica");
        if (isset($_GET['step2']) && $_GET['step2'] == 'arena')
        {
            define("HELP_TEXT", "Arena Walk, to miejsce, w którym odbywają sie wszelkie pojednynki .Dostępne są tutaj 3 opcje:
            <ul>
            <li>Pokaż mi listę wszystkich osób na danym poziomie.</li>
            <li>Chcę walczyć z osobami na tym samym poziomie, co ja...</li>
            <li>Chcę trenować z potworami.</li>
            </ul>
            Opcja pierwsza <b>Pokaż mi listę wszystkich osób na danym poziomie</b> pozwala nam na obejrzenie wszystkich  graczy , ułożonych według zaawansowania poziomu postaci .Po wybraniu poziomu , który nas interesuje ukazuje sie lista postaci , ułożonych kolejno według nr ID . Przy każdej postaci z osobna znajduje sie opcja <b>atakuj</b> . W przypadku wybrania tej opcji nastepuje pojedynek pomiedzy nasza postacią oraz postacią wybrana przez nas z listy.<br /><br />
            Opcja druga <b>Chcę walczyć z osobami na tym samym poziomie, co ja...</b> automatycznie wyświetla postacie o tym samym poziomie zaawansowania co nasza postać , ułożonych kolejno według nr ID .Przy każdej postaci z osobna znajduje sie opcja <b>atakuj</b> . W przypadku wybrania tej opcji nastepuje pojedynek pomiedzy nasza postacią oraz postacią wybrana przez nas z listy.<br /><br />
            Opcja trzecia <b>Chcę trenować z potworami</b> wyswietla liste potworów , ułożonych kolejno według poziomu zaawansowania oraz ilości życia . Nastepnie należy wybrać z listy potwora z którym chcemy stoczyć pojedynek . Po wybraniu na dole pojawi sie opcja umożliwiająca wybór ilości potworów z jaką chcemy walczyć . Po wpisaniu tej liczby wybierając opcję atakuj rozpoczynamy pojedynek .");
        }
        if (isset($_GET['step2']) && $_GET['step2'] == 'zbrojmistrz')
        {
            define("HELP_TEXT", "Zbrojmistrz oferuje usługi w zakresie sprzedarzy broni  . W swojej ofercie posiada spis owych przedmiotów ułożonych kolejno według wymaganego zaawansowania poziomu postaci .Przedmioty zawierają charakterystyke przedstawiającą nazwę przedmiotu , efekt ,szybkość , wytrzymałość , cenę , wymagany poziom oraz opcję <b>kup</b> dzieki której możemy zakupić daną rzecz .");
        }
        if (isset($_GET['step2']) && $_GET['step2'] == 'platnerz')
        {
            define("HELP_TEXT", "Płatnerz oferuje usługi w zakresie sprzedarzy przedmiotów takich jak zbroje , hełmy oraz nagolenniki . W swojej ofercie posiada spis owych przedmiotów ułożonych kolejno według wymaganego zaawansowania poziomu postaci .Przedmioty zawierają charakterystyke przedstawiającą nazwę przedmiotu , wytrzymałość , efekt , cenę , wymagany poziom , ograniczenie zręczności oraz opcję <b>kup</b> dzieki której możemy zakupić daną rzecz .");
		}
        if (isset($_GET['step2']) && $_GET['step2'] == 'fleczer')
        {
            define("HELP_TEXT", "Fleczer oferuje usługi w zakresie sprzedarzy łuków i strzał  . W swojej ofercie posiada spis owych przedmiotów ułożonych kolejno według wymaganego zaawansowania poziomu postaci .Przedmioty zawierają charakterystyke przedstawiającą nazwę przedmiotu , efekt ,szybkość , wytrzymałość , cenę , wymagany poziom oraz opcję <b>kup</b> dzieki której możemy zakupić daną rzecz .");
        }
        if (isset($_GET['step2']) && $_GET['step2'] == 'straznica')
        {
            define("HELP_TEXT", "Strażnica to mini gra strategiczna. Aby w nią zagrać, musisz wykupić bilet za 500 sztuk złota. Kiedy to zrobisz, będziesz miał dostęp do specjalnego menu. Opcja <b>Moja Strażnica</b> zawiera ogólne informacje na temat twojej strażnicy, posiadanych kopalni oraz żołnierzy i fortyfikacji. W opcji <b>Kopalnie</b> możesz wydobywać minerały, kupować nowe kopalnie, itp. W opcji <b>Sklep w Strażnicy</b> możesz kupować żołnierzy, fortyfikacje oraz powiększać swoją strażnicę. Opcja <b>Atakuj Strażnicę</b> pozwala ci zaatakować strażnicę innego gracza. Dokładny opis gry znajduje się w opcji <b>Instrukcja Strażnicy</b>");
        }
    }
    if (isset($_GET['step']) && $_GET['step'] == 'zachodni')
    {
        define("HELP_INFO9", "Zachodnia strona to najstarsza dzielnica miasta. Znajdziesz tutaj tylko dwie lokacje. Kliknij na nazwę miejsca aby otrzymać więcej wiadomości na jego temat.");
        define("A_LABYRYNTH", "Labirynt");
        define("A_TOWER", "Wieża");
        define("A_MITH", "Sklep z mithrilem");
        define("A_TEMPLE", "Świątynia");
        define("A_ALCHEMY", "Alchemik");
        if (isset($_GET['step2']) && $_GET['step2'] == 'labirynt')
        {
            define("HELP_TEXT", "Labirynt to miejsce, które przy urzyciu punktów energi możemy zwiedzać. W przypadku zwiedzania losowo natrafiamy na róznego rodzaju  zdarzenia np znaleźć złoto , regeneracja energi itp. lub poprostu zmarnować troche energi na bezużyteczne bieganie w koło .");
        }
        if (isset($_GET['step2']) && $_GET['step2'] == 'mithril')
        {
            define("HELP_TEXT", "Jak sama nazwa wskazuje , można tutaj nabyć mithril . Cena mithrilu jest inna każdego dnia . Można powiedzieć ze jest to cos w rodzaju giełdy papierów wartościowych .");
        }
        if (isset($_GET['step2']) && $_GET['step2'] == 'wieza')
        {
            define("HELP_TEXT", "W Magicznej Wieży można nabyć przedmioty magiczne w które wyposażyć mogą sie jedynie magowie . Są to takie przedmioty jak : szaty , różdżki oraz czary .W swojej ofercie Magiczna Wieża posiada spis owych przedmiotów ułożonych kolejno według wymaganego zaawansowania poziomu postaci .Przedmioty zawierają charakterystyke przedstawiającą nazwę przedmiotu , siłe (w przypadku różdżki oraz szat) , obrażenia (w przypadku czarów) , cenę , wymagany poziom oraz opcję <b>kup</b> dzieki której możemy zakupić daną rzecz .");
        }
        if (isset($_GET['step2']) && $_GET['step2'] == 'swiatynia')
        {
            define("HELP_TEXT", "W Świątyni boga Illuminati możemy zarówno pracować jak i sie modlić . Aby sie modlić potrzebne są punkty wiary , które zdobywamy pracując dla świątyni kosztem naszej energii . Modląc sie możemy zyskać np dodatkowe pkt. do statystyk , złoto , pkt. życia itp. oraz istnieje szansa , że utracimy znaczną część punktów życia .");
        }
        if (isset($_GET['step2']) && $_GET['step2'] == 'alchemik')
        {
            define("HELP_TEXT", "Alchemik oferuje usługi w zakresie sprzedarzy magicznych mikstur. W swojej ofercie posiada spis owych mikstur zawierających charakterystyke przedstawiającą nazwę mikstury , efekt , cenę oraz opcję <b>kup</b> dzieki której możemy zakupić daną miksturę .");
        }
    }
    if (isset($_GET['step']) && $_GET['step'] == 'zamek')
    {
        define("HELP_INFO10", "Zamek to siedziba władcy Vallheru, Thindila. Znajdziesz tutaj różnie informacje związane ze sprawami technicznymi gry. Aby zobaczyć opis jakiegoś miejsca po prostu kliknij na jego nazwę.");
        define("A_NEWS", "Wieści");
        define("A_TIMER", "Zegar miejski");
        define("A_REFF", "Poleceni");
        if (isset($_GET['step2']) && $_GET['step2'] == 'wiesci')
        {
            define("HELP_TEXT", "Tutaj znajdziesz informacje na temat technicznej strony gry (nowości w grze, zmiany w plikach i tym podobne sprawy. Aby zobaczyć ostatnie 10 Wieści kliknij po prostu na <i>Ostatnie 10 wieści</i>");
        }
        if (isset($_GET['step2']) && $_GET['step2'] == 'zegar')
        {
            define("HELP_TEXT", "Tutaj możesz zobaczyć obecny czas oraz kiedy będzie następny restart gry (czyli odzyskanie energi, uzdrowienie, itd. Główny restart o 12 to początek nowego dnia na Vallheru, wtedy dodatkowo zmieniają się również ceny mithrilu w <i>Sklepie z mithrilem</i>");
        }
        if (isset($_GET['step2']) && $_GET['step2'] == 'poleceni')
        {
            define("HELP_TEXT", "Tutaj możesz zobaczyć specjalny link, dzięki któremu możesz zbierać poleconych (czyli osoby, które zapisały się do gry przez twój link). <b>Uwaga!</b> Jeżeli dojdzie do mnie informacja, że ktoś wysyła spam, w takim wypadku, będę kasował jego konto bez chwili wachania! Oprócz tego możesz tutaj zobaczyć ilu już masz poleconych. W przyszłości za zdobycie odpowiedniej liczby poleconych będzie można dostać jakieś specjalne przedmioty.");
        }
    }
}

?>
