<?php
/**
 *   File functions:
 *   Polish language for deity.php
 *
 *   @name                 : deity.php                            
 *   @copyright            : (C) 2004,2005,2006 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@users.sourceforge.net>
 *   @version              : 1.2
 *   @since                : 07.08.2006
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

define("A_SELECT", "Wybierz");
define("A_BACK", "Wróć");
define("GOD1", "Illuminati");
define("GOD2", "Karserth");
define("GOD3", "Anariel");
define("GOD4", "Heluvald");
define("GOD5", "Tartus");
define("GOD6", "Oregarl");
define("GOD7", "Daeraell");
define("GOD8", "Teathe-di");
define("ERROR", "Zapomnij o tym!");
define("ATEIST", "Pozostań ateistą");
define("YOU_HAVE", "Posiadasz już wyznanie!");

if (!isset($_GET['deity']) && !isset($_GET['step']))
{
    define("DEITY_INFO", "Tutaj możesz wybrać wyznanie swojej postaci. Każde bóstwo ofiarowuje swym wyznawcom inne rzeczy. Zastanów się dobrze, ponieważ poźniej nie będziesz już mógł zmienić swojego wyznania.");
}

if (isset($_GET['step']) && $_GET['step'] == 'change')
{
    define("CHANGE", "Czy na pewno chcesz zmienić swoje obecne wyznanie? Zmiana będzie ciebie kosztować");
    define("CHANGE2", "punktów wiary.");
    define("YOU_CHANGE", "Zrezygnowałeś z wiary w");
    define("YOU_MAY", ". Teraz możesz ponownie");
    define("A_SELECT2", "wybrać");
    define("T_DEITY", "wyznawane bóstwo.");
}

if (isset($_GET['deity']) && $player -> deity == '') 
{
    define("YOU_SELECT", "Wybrałeś wiarę w");
    define("CLICK", "Naciśnj");
    define("HERE", "tutaj");
    define("FOR_BACK", "aby wrócić");
    define("GOD1_INFO", "Illuminati to główne bóstwo panteonu Vallheru. Różne rasy różnie wyobrażają sobie ową istotę. Wśród Ludzi oraz Krasnoludów jest on mężczyzną danej rasy wśród Elfów jest to elficka kobieta. Bóstwo owo w przeciwieństwie do innych bóstw nie koncentruje się na jednym tylko aspekcie życia, lecz wspiera swoich wyznawców na wiele sposobów. Symbolem tego bóstwa jest biały płomień na tle złotego rombu");
    define("GOD2_INFO", "Wyznawców Karsertha, podobnie jak ich boga częściej można spotkać na polu bitwy niż w świątyni. Karserth jest panem wojny, miłuje swych wyznawców przede wszytkim za waleczność oraz odwagę, gardzi tchórzami. Poszczególnym rasom jawi się jako mężczyzna odziany w potężną, wspaniale zdobioną zbroję (wśród Ludzi oraz Krasnoludów) lub jako tajemniczy, smukły mężczyzna odziany w płaszcz tropicieli uzbrojony w bojowy łuk (wśród elfów). Swych wyznawców wspiera darowując im przede wszytkim siły oraz umiejętności do walki. Jego symbolem są skrzyżowany miecz oraz strzała");
    define("GOD3_INFO", "Najbardziej tajemnicza bogini spośród całego panteonu Vallheru. Jest to opiekunka magii oraz istot zajmujących się nią. Od swych wyznawców wymaga aby rozwijali swoje umiejetności związane z magią. Śmiertelnicy wyobrażają ją sobie jako kobiecą postać w magicznych szatach z nasuniętym głęboko na głowę kapturem. Wokół owej postaci emanuje aura potężnej magicznej mocy. Swych wyznawców bogini ta wspiera dodając sił oraz umiejętności związanych z magią. Jej symbolem jest niebieski wir.");
    define("GOD4_INFO", "Jest to najspokojniejszy spośród bogów Vallheru. Nie interesuje go moc czy przelew krwi. Heluvald jest opiekunem rzemieślników oraz handlarzy. Wśród wszystkich ras przedstawia się go albo jako niskiego, brodatego mężczyznę odzianego w fartuch kowalski albo jako przystojnego kupca w bogato strojonym ubraniu z tajemniczym uśmiechem na twarzy. Swych wyznawców wspiera ofiarowując im wiedzę oraz umiejętności związane z różnymi rzemiosłami. Jego symbolem jest stos złotych monet.");
    define("GOD5_INFO", "Jeden z upadłych Bogów, strącony przez wielką czwórkę. Niegdyś władający potężną mocą, potem wygnany i przeklęty. Minęły tysiąclecia, kiedy ponownie wrócił do świata śmiertelnych. Tartus wyglądem przypomina wysmukłego elfickiego wojownika o długich ciemnych włosach, dzierżącego w prawej ręce długi miecz, w lewej zaś magiczną sferę ognia. Jego obsydianowa zbroja płytowa i nagolenniki kontrastują z jasną, cielistą skórą. Z tułowia wyrastają mu wielkie płomienne skrzydła przypominające feniksa, dzięki którym potrafi monumentalnie utrzymywać się w powietrzu. Tartus jest mistrzem magii ognia i planów piekielnych. Podczas swego wygnania zawładnął i opanował wiele z upadłych planów astralnych, gdzie podporządkował sobie ogromne rzesze demonów i czartów. Stąd jest także biegły w wszelakiego rodzaju przywoływaniach demonów. Niegdyś czczony przez kilka plemion elfich i jaszczuroczłeków. Jednakże w dzisiejszych czasach jest potępiany przez większość ras. Ludzie i hobbici boją się jego demonicznej aury, uważając go za Boga zła i piekieł. Jaszczuroczłecy i krasnoludy nie tolerują jego magii i nazywają Bogiem Ciemności. Elfy zaś pamiętają tylko szczątki historii, kiedy Tartus cieszył się jeszcze uznaniem Wielkiej Czwórki.<br /><br /> Mimo swojej złej sławy Tartus stał się patronem magów bitewnych i elfów ognia, którzy czcili jego moc i czerpali od niego boskie wsparcie i siłę. Ostatnio w świecie Vallheru pojawia się coraz więcej wyznawców upadłego Bóstwa szczególnie wśród czarowników i przywoływaczy demonów. Ponadto Tartus potrafi wysłuchać i wspierać w bitewnej modlitwie wojowników władających broń białą.");
    define("GOD6_INFO", "Oregarl charakteryzuje się postawą silnego i krzepkiego krasnoluda o szerokich barkach oraz uwydatnionej klatce piersiowej.<br />Długa siwa broda spływa po lśniącej zbroi płytowej, którą zawsze nosi. W dłoniach dzierży wielki młot oraz tarczę. Na tarczy symbol: pięść skrzyżowana z młotem.<br /><br />Ponad tysiąc lat wstecz na ziemiach Vallheru w okolicach gór Kazad-nar toczyła się wielka wojna sił ciemności z zastępami krasnoludów. Kiedy zło rosło w siłę, krasnoludzcy kapłani poprzez swoje modlitwy prosili boga o błogosławieństwo. W świątyni głęboko pod ziemią ukazał się avatar boga Oregarla, przywołany przez kapłanów. Pobłogosławił on miasto i rasę walczących krasnoludów. Niestety, obrona twierdzy krasnoludzkiej nie wytrzymała. Miasto zostało zniszczone poza świątynią, w której przebywał owy avatar. Wejścia do świątyni zostały zapieczętowane glifami ochronnymi i runami tak, by żadne zło nie mogło dostać się do wewnątrz. Kapłani zabezpieczyli świątynię, jednak nie zdołali przekazać swej wiedzy potomnym. I tym sposobem religia została zapomniana.<br /><br />Oregarl opiekuje się krasnoludami walczącymi wręcz oraz tymi, którzy przyczyniają się do rozwoju sztuki obróbki kamieni i złóż metali oraz kowalstwa. Naucza lojalności, nieugiętości oraz wytwarzania dóbr materialnych. Obdarzą błogosławieństwem tych który walczą w jego imię przeciw siłom zła.");
    define("GOD7_INFO", "Bogini Żebraków i Poszkodowanych przez los, Dawczyni Nadziei.<br />Zrodziła się ze smutku i płaczu ludzi, by dać im nadzieję na odmianę losu. Legenda o jej narodzeniu została spisana przez elfy. Ponoć jednego razu sam Illuminati siedząc jednego dnia na jednej ze swych ulubionych leśnych polan, postanowił posłuchać myśli mieszkańców świata, zebrał magiczna moc pomiędzy swymi dłońmi i myśli mieszkańców z krain zaczęły wypełniać tę Przestrzeń. Jednakże ku jego zdziwieniu samymi smutkami, żalem, płaczem i bólem. Było tego tyle ze nie zdołał utrzymać i wyrzucił przed siebie. Tych myśli było tak dużo że zrodziło się z nich jeziorko w samym środku lasu, woda była w nim czarna i nieprzenikniona jak noc. Postanowił dać tym poszkodowanym jakieś słońce które rozjaśniało by im gorycz dnia codziennego. Wrzucił swój włos do jeziora i stworzył Daeraell, która odtąd miała nieść nadzieję poszkodowanym.  Jest postrzegana najczęściej jako kobieta o ciemno brązowej skórze z białymi oczyma, bardzo długimi białymi włosami które promieniują lekko zielonkawa aurą. Wokół niej zawsze latają jaskółki, mówią ze tam gdzie stąpnie tam wyrastają białe lilie. Krasnoludy nazywają ją Nerin Snowmirth.  Jej kult został zapomniany, jednakże nie umarł w Minas Tirth, skąd został do Krain Vallheru z powrotem sprowadzony. Legenda mówi ze obroniła biedne dzieci z miasta które podczas ataku się do niej modliły. Przykryła je swoimi włosami, powodując ze stały się niewidoczne dla najeźdźców.<br />Wyznawcy Kultu Daeraell trzymają małe kapliczki w których zawsze są umieszczone płatki białych lilii. Mówią ze wiara o niej nie zaginie dopóki będzie się zło i krzywda na świecie działa, czasami się zdarza że dzięki niej niektórzy się wzbogacają, pomaga rzemieślnikom aby ich praca przynosiła lepsze efekty, daje także posiłek dzięki czemu zabiera głód.");
    define("GOD8_INFO", "Teathe-di, patronka złodziejek, słynie z piękna i bogatej historii. Jej wizerunki przedstawiane są dość niejasno. Wygląda jak elfka, lecz jest niska niemal jak niziołka. Na jednych dziełach jej włosy są złote jak zachodzące słońce, a innym razem czarne jak płaszcz nocy. W jednym wszystkie przedstawienia się zgadzają - na jej twarzy kwitnie złośliwy uśmieszek odzwierciedlający jej charakter. Bogini ubrana jest w czarne szaty wojowniczki, a jej biodra przewiązane są szeroką szarfą. Przez plecy ma przewieszony kunsztowny łuk, a znad ramienia wystają lotki strzał.<br /><br />Starodawne podania mówią, że boska była kiedyś szybką łuczniczką podziwianą przez wszystkich bogów. Cały panteon zazdrościł jej wdzięków i zwinności. Podobno któregoś dnia zbuntowała się przeciw bogom ukazując swą prawdziwą naturę. Wysłuchując modlitw wiernych, ukradła z siedziby bóstw ważne astralne komponenty. Bogowie wyklęli ją i nazwali boginią podłych łotrzyc.<br /><br />Od tamtej pory Teathe-di wspiera czarne interesy złodziejek łuczniczek. Nadal wysłuchuje ich modlitw i z radością kontynuuje swoje astralne dzieło");
}
?>
