<?php
/**
 *   File functions:
 *   Polish language for select race
 *
 *   @name                 : rasa.php                            
 *   @copyright            : (C) 2004-2005 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@users.sourceforge.net>
 *   @version              : 0.9 beta
 *   @since                : 20.07.2005
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

define("A_BACK", "Wróć");
define("A_SELECT", "Wybierz");
define("YOU_HAVE", "Masz już wybraną rasę!");

if (!isset($_GET['rasa']))
{
    define("RACE_INFO", " Tutaj możesz wybrać rasę swojej postaci. Każda rasa ma swoje plusy i minusy (ich opis znajdziesz po kliknięciu w link). Zastanów się dobrze, ponieważ poźniej nie będziesz już mógł zmienić swojej rasy.");
    define("A_HUMAN", "Człowiek");
    define("A_ELF", "Elf");
    define("A_DWARF", "Krasnolud");
    define("A_HOBBIT", "Hobbit");
    define("A_REPTILION", "Jaszczuroczłek");
    define("A_GNOME", "Gnom");
}

if (isset($_GET['rasa']) && $_GET['rasa'] == 'czlowiek' && $player -> race == '') 
{
    define("YOU_SELECT", "<br />Wybrałeś rasę ludzką. Kliknij <a href=stats.php>tutaj</a> aby wrócić.");
    define("RACE_INFO", "Ludzie są najbardziej zróżnicowaną i wszechstronną rasą. Są dość wysocy gdyż mierzą około 150 - 190 cm wzrostu lecz są osoby którzy są o wiele wyżsi ale i niżsi. Maja rożną posturę jednak najczęściej są umięśnieni i szczupli. Dzięki tej różnorodności wśród tej rasy mogą być oni wyćwiczeni w każdej dziedzinie. Wielu ludzkich magów pokonało niejednego elfiego maga oraz nie mniej wojowników pokonało wielu krasnoludzkich wojowników. Nie maja określonych najlepszych i najgorszych stron - we wszystkim są dobrzy ale tylko jeśli trenują jedna dziedzinę - inaczej są dobrzy, lecz nie najlepsi. Atutem ludzi jest ich wszechstronność. Najlepszym przykładem są zarówno potężni magowie jak i wojownicy. Należą oni do najbardziej zuchwałych i ambitnych ras. Najczęściej walczą dla bogactwa i sławy niźli dla osobistego kształtowania swych umiejętności. Ludzie jako rasa najlepiej przyswajają nową wiedze, toteż szybko staja się potężni. Nie są wrogo nastawieni do innych w przeciwieństwie do krasnoludów i elfów. Są także uznawani za najbardziej ekscentryczną z ras, znów z powodu ich różnorodności. Ludzie są najlepsza rasa gdyż potrafią przystosować się do każdego środowiska w szybkim czasie i dlatego tez szybko uczą się wybranej przez siebie profesji.<br />
    Za 1 AP ludzie dostają
    <ul>
    <li>+2.5 do siły</li>
    <li>+2.5 do zręczności</li>
    <li>+2.5 do inteligencji</li>
    <li>+2.5 do szybkości</li>
    <li>+2.5 do wytrzymałości</li>
    <li>+2.5 do siły woli</li>
    </ul>");
}

if (isset($_GET['rasa']) && $_GET['rasa'] == 'elf' && $player -> race == '') 
{
    define("YOU_SELECT", "<br />Wybrałeś rasę Elfów. Kliknij <a href=stats.php>tutaj</a> aby wrócić.");
    define("RACE_INFO", "Elfy to bardzo dziwne istoty. Są skryte i najczęściej tolerują tylko innych ze swej rasy. Są szczupłe i niskiego wzrostu bo około 135 - 165 cm wzrostu. Jednak mimo iż wyglądają na słabych maja niezrównane zdolności magiczne. Niewielu przeżywa spotkanie z doświadczonym i rozwścieczonym elfim magiem. Mimo iż są świetnymi magami również nie są dyskryminowane jako wojownicy. Ich finezja w walce porównywana jest do ruchów kota a siła do tygrysa. Zwinność jest największym atutem elfa ponieważ jako mag musi nie tylko znać magiczne formuły lecz wykonywać szybkie i zręczne magiczne gesty, a gdyby zwinność zawiodła go w walce bronią wojownika - byłby zgubiony. Nie przepadają za innymi rasami gdyż o każdej twierdzą co innego. Ludzie są dla nich nieobyci a krasnoludy zupełnie niezabawne. Podroż zaczynają głównie dla chęci wyruszenia i poznania świata. Najbardziej lubią ukazywać swa zdolność do zwinnej walki mieczem i łukiem, no i oczywiście posługiwaniem się magią. Są bardzo wyniosłe i eleganckie aczkolwiek aroganckie i nietolerancyjne. Elfy są najlepszą rasą gdyż ich atutem jest szybkość i zwinność dzięki których potrafią z niebywałą prędkością dopadać przeciwników.<br />
    Za 1 AP Elfowie dostają:
    <ul>
    <li>+2 do siły</li>
    <li>+3 do zręczności</li>
    <li>+2.5 do inteligencji</li>
    <li>+3 do szybkości</li>
    <li>+2 do wytrzymałości</li>
    <li>+2.5 do siły woli</li>
    </ul>");
}

if (isset($_GET['rasa']) && $_GET['rasa'] == 'krasnolud' && $player -> race == '') 
{
    define("YOU_SELECT", "<br />Wybrałeś rasę Krasnoludów. Kliknij <a href=stats.php>tutaj</a> aby wrócić.");
    define("RACE_INFO", "Krasnoludy znane są ze swej ogromnej siły, zdolności bojowej oraz picia piwa w karczmie. Może nie zadziwiają nikogo wzrostem bo mierzą około 120 - 135 cm wzrostu - jednak nie należy ich przez to lekceważyć. Są bardzo krzepkie i umięśnione oraz każdy z nich nosi brodę (krasnolud nie mający brody uznawany jest za zhańbionego). Ich atutem jest siła - i oczywiście to oni są najlepszymi kowalami Krain. Podróżują głównie dla zdobycia chwały i szacunku dla swojego klanu. Żyją do 400 lat może nawet dłużej wiec maja dużo czasu na osiągniecie w sobie doskonałości w danej profesji. Maja dość neutralne relacje z ludźmi lecz co do elfów maja duże uprzedzenia. Mimo iż kiedyś elfy walczyły ramie w ramie z krasnoludami teraz czują do siebie dużą antypatię. Nie są powszechnie lubiana rasa gdyż są zadziorni i bardzo uparci. Są najlepszą rasa bo potrafią wykorzystać swoje największe umiejętności czyli siłę i wytrzymałość do walki skuteczniej niż inne rasy.<br />
    Za 1 AP Krasnoludowie dostaje
    <ul>
    <li>+3.5 do siły</li>
    <li>+2 do zręczności</li>
    <li>+2.5 do inteligencji</li>
    <li>+2 do szybkości</li>
    <li>+3 do wytrzymałości</li>
    <li>+2.5 do siły woli</li>
    </ul>");
}

if (isset($_GET['rasa']) && $_GET['rasa'] == 'hobbit' && $player -> race == '') 
{
    define("YOU_SELECT", "<br />Wybrałeś rasę Hobbitów. Kliknij <a href=stats.php>tutaj</a> aby wrócić.");
    define("RACE_INFO", "Hobbici niedawno przybyli do Vallheru, lecz już zdążyli zaznaczyć swoją obecność. Znani są z pogodnego charakteru oraz umiejętności wyczyszczenia spiżarni w czasie krótszym niż legion wygłodniałych Krasnoludów. Są najniższą znaną rasą - mierzą od 90 do 120 cm wzrostu. Mają najczęście ciemnobrązowe, kędzierzawe włosy oraz brązowe oczy. Cechą charakterystyczną tej rasy jest fakt iż żaden z jej przedstawicieli nie nosi obuwia. Spowodowane jest to tym, że posiadają na stopach niesamowicie bujne owłosienie, dzięki któremu mogą na bosaka wędrować nawet po śniegu. Ogólnie przypominają Ludzi stąd zwykło nazywać się ich Małymi Ludźmi. Większość Hobbitów jest nieco otyła, co wprowadziło już w błąd wielu przedstawicieli innych ras, albowiem jeżeli Hobbit zechce może porusząć się równie zwinnie jak Elf. Są przy tym bardzo wytrzymali na trudy. Jednak rzadko owe cechy ujawniają się w tej rasie, ponieważ nad brud gościnca oraz dreszczyk przygód, rasa ta przedkłada ciepło domowego kominka. Jeżeli już wyruszają w świat, to raczej z czystej ciekawości niż ze względu na jakieś szczytne cele. Jest to rasa bardzo pokojowa, żyje z dala od spraw wielkiego świata i nie miesza się do sporów innych istot. Mało który z nich para się wojaczką czy magą ale ze względu na swoją pracowitość, przedmioty wykonane przez Hobbitów bez problemu mogą konkurować z dziełami mistrzów elfich czy też krasnoludzkich - przynajmniej jeżeli chodzi o wykonanie. Według niepotwierdzonych informacji wielu z podróżników tej rasy, którzy przybyli do ".$city1b." zasiliło szeregi przestępczej organizacji znanej jako Gildia Złodziei. Śledztwo w tej sprawie trwa.
    <br />
    Za 1 AP Hobbit dostaje
    <ul>
    <li>+1.5 do siły</li>
    <li>+3.5 do zręczności</li>
    <li>+2.5 do inteligencji</li>
    <li>+1.5 do szybkości</li>
    <li>+2.5 do wytrzymałości</li>
    <li>+2.5 do siły woli</li>
    </ul>");
}

if (isset($_GET['rasa']) && $_GET['rasa'] == 'reptilion' && $player -> race == '') 
{
    define("YOU_SELECT", "<br />Wybrałeś rasę Jaszczuroludzi. Kliknij <a href=stats.php>tutaj</a> aby wrócić.");
    define("RACE_INFO", "Jaszczuroludzie to jedna z najstarszych ras inteligentnych jakie znajdują się na ziemiach Vallheru. Dawni panowie tego świata, dziś już tylko cienie własnej potęgi sprzed lat. Obecnie jako nomadowie zamieszkują Pustynię Hansid el-Suda. Jako że najlepiej czują się w gorącym klimacie dopiero od niedawna spotkać można przedstawicieli tej rasy w ".$city1a.". Z wyglądu przypominają potężną dwunożną jaszczurkę. Najwyższy znany przedstawiciel tej rasy miał ok 2,5 metra wzrostu, najniższy - 150 cm. Ich skóra z biegiem czasu pokrywa się twardymi łuskami które mogą pełnić rolę dodatkowego pancerza. Najczęściej mają zielony lub brązowy kolor skóry. Jednak największą dumą każdego Reptiliona jest jego ogon - są bardzo przeczuleni na jego punkcie. Ich gadzie, pozbawione emocji twarze sprawiają iż przedstawiciele innych ras czują się w ich towarzystwie bardzo nieswojo. Większość Jaszczuroludzi jest niespokojna i agresywna - idealny materiał na wojownika. Znani są ze swej siły oraz szybkości. Jednak o ich inteligencji krążą wśród innych ras setki dowcipów. W swojej ojczyźnie praktycznie żaden Gadoczłek nie zajmuje się magią, jedynie odstępcy, którzy opóścili swych rodaków i przybyli do ".$city1b." próbują nią się zajmować, najczęściej jednak z mizernym skutkiem. Wielu z nich nie cierpi jakiejkolwiek formy magii. Ci co opuścili swoje klany to najczęsciej żadni bogactw i chwały awanturnicy. Niestety klimat Vallheru sprawia iż rasa ta na innych terenach niż pustynie nie może pokazać wszystkich swych możliwości. Jest to spowodowane tym iż Jaszczuroludzie są zmiennocieplni, stąd najlepiej czują się w bardzo gorącym klimacie - a tych jest niestety dość mało. Wyroby ich kowali czy też łuczników nie są może zbyt piękne ale za to bardzo funkcjonalne. Dawniej rasa ta była władcą tych ziem, jednak obecnie mało kto liczy się ze zdaniem jej przedstawicieli. Reptilioni sami odizolowalisię od innych ras kontaktując się najczęściej jedynie z przedstawicielami swojego gatunku. Większość z tych co przybywają do ".$city1b.", ze względu na swoją porywczość i nieokrzesanie nazywa się barbarzyńcami.<br />
    Za 1 AP Jaszczuroczłek dostaje
    <ul>
    <li>+3.5 do siły</li>
    <li>+2.5 do zręczności</li>
    <li>+1.5 do inteligencji</li>
    <li>+3 do szybkości</li>
    <li>+2.5 do wytrzymałości</li>
    <li>+1.5 do siły woli</li>
    </ul>");
}

if (isset($_GET['rasa']) && $_GET['rasa'] == 'gnome' && $player -> race == '') 
{
    define("YOU_SELECT", "<br />Wybrałeś rasę Gnomów. Kliknij <a href=stats.php>tutaj</a> aby wrócić.");
	define("RACE_INFO", "Gnomy pojawiły się na Vallheru całkiem niedawno. Złośliwi twierdzą iż są rasa ta jest efektem ubocznym jakiegoś zaklęcia. I rzeczywiście, ich odporność na magię jest nieco niższa niż u innych ras (może z wyjątkiem Jaszczuroludzi). Gnomy znane są z nieco chaotycznego charakteru, ich pęd do poznania całego świata nie raz wpędził przedstawiciela tej rasy (oraz jego przyjaciół) w kłopoty. Z zachowania nieco podobni do Hobbitów oraz Krasnoludów, lubują się przede wszystkim w rzemiośle oraz różnych dziwnych wynalazkach. Podekscytowany Gnom potrafi wyrzucać z siebie słowa szybciej niż najszybszy elfi łucznik strzały. Są jedną z najniższych znanych ras istot inteligentnych - mierzą od 100 do 130 cm wzrostu. Z budowy ciała podobni nieco do Krasnoludów (ci ostatni bardzo energicznie wypierają się jakichkolwiek porównań), o prostych jasnych włosach (rzecz jasna, jeżeli wcześniej dany osobnik nie stracił ich podczas eksperymentów alchemicznych) oraz czarnych oczach. Żywe usposobienie sprawia iż ciężko nie czuć sympatii do Gnomów - przynajmniej do ich pierwszego wybryku. Rzadko zajmują się magią - brak im cierpliwości do czegoś czego efektów nie widać od razu. Jako rasa pokojowo nastawiona, rzadko również zostają wojownikami - choć taki Gnom uzbrojony i wyposażony w najnowsze wynalazki gnomiej techniki potrafi siać prawdziwe spustoszenie na polu walki. Niestety również i wśród własnych towarzyszy. Ich zamiłowanie do rzemiosła oraz alchemii jest ogólnie znane. To ostatnie również bardzo widowiskowe. Wyroby ich rzemieślników są doskonałej jakości, choć czasami zbytnio udziwnione. Są przyjacielsko nastawieni do wszystkich znanych ras.<br />Gnomy mają podwojoną premię z profesji Rzemieślnik.<br />
    Za 1 AP Gnom dostaje
    <ul>
    <li>+1.5 do siły</li>
    <li>+2.5 do zręczności</li>
    <li>+2.5 do inteligencji</li>
    <li>+1.5 do szybkości</li>
    <li>+2.5 do wytrzymałości</li>
    <li>+2 do siły woli</li>
    </ul>");
}
?>
