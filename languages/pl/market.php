<?php
/**
 *   File functions:
 *   Polish language for markets menu
 *
 *   @name                 : market.php                            
 *   @copyright            : (C) 2004,2005,2006,2011,2012 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @version              : 1.5
 *   @since                : 29.02.2012
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
if (!isset($_GET['view']))
{
    define("MARKET1", "Tutaj jest rynek z minerałami. Masz parę opcji.");
    define("MARKET2", "Tutaj jest rynek z przedmiotami. Masz parę opcji.");
    define("MARKET3", "Tutaj jest rynek z miksturami. Masz parę opcji.");
    define("MARKET4", "Tutaj jest rynek z ziołami. Masz parę opcji.");
    define("MARKET5", "Tutaj jest rynek z komponentami astralnymi. Masz parę opcji.");
    define("MARKET6", "Tutaj jest rynek z wyrobami jubilerskimi. Masz parę opcji.");
    define("MARKET7", "Tutaj jest rynek z łupami z potworów. Masz parę opcji.");
    define("MARKET8", "Tutaj jest rynek z chowańcami. Masz parę opcji.");
    define("A_SHOW", "Zobacz oferty");
    define("A_ADD", "Dodaj ofertę");
    define("A_DELETE", "Skasuj wszystkie swoje oferty");
    define("A_LIST", "Spis wszystkich ofert na rynku");
    define("MARKET_INFO", "Idziesz główną ulicą ".$city1b.". Rozglądasz się po mieście zadowolony z siebie i z życia podśpiewujesz sobie pod nosem.<br />\"Czas się potargować, zobaczymy czy nie wyszedłem z wprawy\" - mówisz sam do siebie kierując swoje kroki w kierunku Rynku.<br />Zbliżasz się do olbrzymiego placu, na którym panuje niesamowity gwar. Wszyscy krzyczą, jeden przez drugiego. Miejscami  wybuchają ostrzejsze kłótnie pomiędzy handlującymi, jednak pilnujący porządku wojownicy szybko uspokajają nadgorliwych mieszkańców. Patrzysz na to wszystko rozbawiony i zafascynowany.<br />\"Tak tego mi brakowało\" - wzdychasz cicho do siebie powoli zanurzając się w ciżbę handlujących. Gdzie skierujesz swoje kroki");
    define("MARKET_INFO2", "Wielki Plac Targowy rozciąga się przed Twoimi oczami i nawet jeszcze daleko przed bramą Rynku słyszysz:<br />- Tani mithrill, prosto z kopalni...<br />- Najlepszy ekwipunek, wszystkie poziomy...<br />- Zioła, kupujcie zioła; świeżutkie trucizny z dynallcy...<br />Idziesz spokojnym krokiem, mijając Bramę Wejściową, sprawdzasz czy aby sakiewka dobrze trzyma się pasa - nigdy nie wiadomo, co może Cię spotkać - pełno tu hobbitów...<br />Mijasz kolejne stoiska, przy których widzisz rozmaite produkty. Gwar z każdym Twoim krokiem narasta.  W pewnej chwili podchodzi do Ciebie starsza kobieta z koszykiem na ręku i rzecze:<br />- Jaśnie Panie, tu sysko jes, sysko. Tyko tsa zloto miec. Cego potseba?");
    define("MARKET_INFO3", "Przechadzając się jedną z ulic ".$city1b." z coraz większym trudem mijasz przechodzących obok mieszkańców. Z każdym Twoim krokiem tłum gęstnieje.  Tuż obok Ciebie przebiegło właśnie dwóch hobbitów trzymając w rękach jakieś woreczki. Wychodzisz zza rogu jednego z budynków i widzisz przed sobą wielki plac, na którym krząta się ogromna rzesza kupców, rzemieślników i podróżnych. Miejsce to położone jest nieco poniżej poziomu ulicy, na której stoisz, to też bez trudu dostrzegasz, co dzieje się na przeciwległych krańcu. Dostać się tam można prowadzącymi z ośmiu stron brukowanymi uliczkami, po których nieustannie przewożony jest towar.<br />Centrum handlowego świata...Wielki Targ, którego sława sięga po najdalsze rubieże ".$gamename.". To właśnie tutaj jest miejsce, gdzie można kupić najbardziej wyszukane wyroby kowali, płatnerzy, alchemików i zielarzy. Tutaj też przy odrobinie szczęścia można kupić za odpowiednią cenę wszystkie znane światu minerały.<br />Z trudem udaje Ci się w tym tłumie zejść schodami w dół. Dla ucha przywykłego do ciszy i spokoju gwar oraz chaos, jaki tu panuje jest nie do zniesienia. Idąc główna ulicą targu rozglądasz się wokół szukając czegoś, co mogłoby Cię zainteresować...");
    define("A_MYOFERTS", "Moje oferty");
}

if (isset($_GET['view']) && $_GET['view'] == 'myoferts')
{
    define("MARKET1", "Rynek minerałów. Moich ofert:");
    define("MARKET2", "Rynek przedmiotów. Moich ofert:");
    define("MARKET3", "Rynek mikstur. Moich ofert:");
    define("MARKET4", "Rynek ziół. Moich ofert:");
    define("MARKET5", "Rynek komponentów astralnych. Moich ofert:");
    define("MARKET6", "Rynek wyrobów jubilerskich. Moich ofert:");
    define("MARKET7", "Rynek łupów z potworów. Moich ofert:");
    define("MARKET8", "Rynek z chowańcami. Moich ofert:");
    define("DELETE_ALL", "Wycofaj wszystkie oferty z wszystkich rynków");
    define("YOU_WANT2", "Na pewno chcesz wycofać wszystkie oferty?");
    define("A_BACK", "Wróć");
    define("DELETED_ALL", "Wycofałeś wszystkie swoje oferty z wszystkich rynków.");
    define("MIN1", "Mithril");
    define("MIN2", "Sztabki miedzi");
    define("MIN3", "Sztabki żelaza");
    define("MIN4", "Bryły węgla");
    define("MIN5", "Bryły adamantium");
    define("MIN6", "Kawałki meteorytu");
    define("MIN7", "Kryształów");
    define("MIN8", "Drewno sosnowe");
    define("MIN9", "Sztabki brązu");
    define("MIN10", "Sztabki mosiądzu");
    define("MIN11", "Sztabki stali");
    define("MIN12", "Drewno z leszczyny");
    define("MIN13", "Drewno cisowe");
    define("MIN14", "Drewno z wiązu");
    define("MIN15", "Ruda miedzi");
    define("MIN16", "Ruda cynku");
    define("MIN17", "Ruda cyny");
    define("MIN18", "Ruda żelaza");
    define("N_HERB1", "Illani");
    define("N_HERB2", "Illanias");
    define("N_HERB3", "Nutari");
    define("N_HERB4", "Dynallca");
    define("N_HERB5", "nasiona Illani");
    define("N_HERB6", "nasiona Illanias");
    define("N_HERB7", "nasiona Nutari");
    define("N_HERB8", "nasiona Dynallca");
    if (isset($_GET['type']))
    {
        define("NO_OFERTS", "Nie ma twoich ofert na tym rynku!");
        define("T_NAME", "Nazwa");
        define("T_AMOUNT", "Ilość");
        define("T_COST", "Cena");
        define("T_OPTIONS", "Opcje");
        define("T_POWER", "Siła");
        define("T_DUR", "Wytrzymałość");
        define("T_SPEED", "Szybkość");
        define("T_AGI", "Zręczność");
        define("T_LVL", "Poziom");
        define("T_EFECT", "Efekt");
        define("T_NUMBER", "Numer");
        define("MAP1", "Plan demoniczny");
        define("MAP2", "Plan ognisty");
        define("MAP3", "Plan piekielny");
        define("MAP4", "Plan pustynny");
        define("MAP5", "Plan wodny");
        define("MAP6", "Plan niebiański");
        define("MAP7", "Plan śmiertelny");
        define("PLAN1", "Astralny komponent");
        define("PLAN2", "Gwiezdny portal");
        define("PLAN3", "Świetlisty obelisk");
        define("PLAN4", "Płomienny znicz");
        define("PLAN5", "Srebrzysta fontanna");
        define("RECIPE1", "Magiczna esensja");
        define("RECIPE2", "Gwiezdna maść");
        define("RECIPE3", "Eliksir Illuminati");
        define("RECIPE4", "Astralne medium");
        define("RECIPE5", "Magiczny absynt");
        define("COMP1", "Ząb Glabrezu");
        define("COMP2", "Ognisty pył");
        define("COMP3", "Pazur Zgłębiczarta");
        define("COMP4", "Łuska Sorpendry");
        define("COMP5", "Macka Krakena");
        define("COMP6", "Piorun Tytana");
        define("COMP7", "Żebro Licha");
        define("CONST1", "Astralny komponent (konstrukcja)");
        define("CONST2", "Gwiezdny portal (konstrukcja)");
        define("CONST3", "Świetlny obelisk (konstrukcja)");
        define("CONST4", "Płomienny znicz (konstrukcja)");
        define("CONST5", "Srebrzysta fontanna (konstrukcja)");
        define("POTION1", "Magiczna esensja (mikstura)");
        define("POTION2", "Gwiezdna maść (mikstura)");
        define("POTION3", "Eliksir Illuminati (mikstura)");
        define("POTION4", "Astralne medium (mikstura)");
        define("POTION5", "Magiczny absynt (mikstura)");
        define("T_BONUS", "Premia");
        define("A_DELETE", "Wycofaj");
        define("A_CHANGE", "Zmień cenę");
        define("A_ADD", "Dodaj");
        define("NO_OFERT", "Nie ma takiej oferty!");      
        define("O_NUMBER", " Numer: ");
        if (isset($_GET['add']))
        {
            define("TO_OFERT", "do oferty: ");
            define("T_AMOUNT2", "sztuk");
            define("NO_AMOUNT", "Nie masz tylu sztuk ");
            define("YOU_ADD", "Dodałeś rzeczy do wybranej oferty.");
        }
        if (isset($_GET['change']))
        {
            define("T_OFERT", "oferty: ");
            define("YOU_CHANGE", "Zmieniłeś cenę za sztukę przedmiotu w wybranej ofercie");
            define("ON_A", "na");
            define("GOLD_COINS", "sztuk złota.");
        }
    }
}
?>
