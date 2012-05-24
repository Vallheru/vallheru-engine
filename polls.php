<?php
/**
 *   File functions:
 *   Polls in game
 *
 *   @name                 : polls.php                            
 *   @copyright            : (C) 2004,2005,2006,2011,2012 Vallheru Team based on Gamers-Fusion ver 2.5
 *   @author               : thindil <thindil@vallheru.net>
 *   @version              : 1.6
 *   @since                : 24.05.2012
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

$title = "Hala zgromadzeń";
require_once("includes/head.php");

/**
* Get the localization for game
*/
require_once("languages/".$lang."/polls.php");

if ($player->location == 'Lochy') 
  {
    error(ERROR);
  }

/**
* Select active poll
*/
$objPollid = $db -> Execute("SELECT `id` FROM `polls` WHERE `lang`='".$lang."' ORDER BY `id` DESC");

/**
* Show active poll
*/
if (!isset($_GET['action']))
{
    if (!$objPollid -> fields['id'])
    {
        $smarty -> assign("Pollid", 0);
    }
        else
    {
        $objPoll = $db -> Execute("SELECT `poll`, `votes`, `days`, `members`, `desc` FROM `polls` WHERE `id`=".$objPollid -> fields['id']);
        $arrPoll = array();
        $arrVotes = array();
        $intVotes = 0;
        $i = 0;
        while (!$objPoll -> EOF)
        {
            if ($objPoll -> fields['votes'] < 0)
            {
                $strQuestion = $objPoll -> fields['poll'];
                $intDays = $objPoll -> fields['days'];
                $intMembers = $objPoll -> fields['members'];
		$strDesc = $objPoll->fields['desc'];
            }
                else
            {
                $arrPoll[$i] = $objPoll -> fields['poll'];
                $arrVotes[$i] = $objPoll -> fields['votes'];
                $intVotes = $intVotes + $objPoll -> fields['votes'];
                $i++ ;
            }
            $objPoll -> MoveNext();
        }
        /**
        * Count percent for each option
        */
        $arrPercentvotes = array();
        $i = 0;
        foreach ($arrVotes as $intVote)
        {
            if ($intVote && $intVotes)
            {
                $arrPercentvotes[$i] = ($intVote / $intVotes) * 100;
                $arrPercentvotes[$i] = round($arrPercentvotes[$i]);
            }
                else
            {
                $arrPercentvotes[$i] = 0;
            }
            $i++ ;
        }
        /**
        * Count percent for voting players
        */
        if ($intDays)
        {
            $objQuery = $db -> Execute("SELECT count(`id`) FROM `players`");
            $intMembers = $objQuery -> fields['count(`id`)'];
            $objQuery -> Close();
            $fltVoting = ($intVotes / $intMembers) * 100;
        }
            else
        {
            $fltVoting = ($intVotes / $intMembers) * 100;
        }
        $fltVoting = round($fltVoting, 2);
        /**
        * Check for aviable for voting
        */
        if ($intDays)
        {
            $strVoting = 'Y';
        }
            else
        {
            $strVoting = 'N';
        }
        if ($player -> poll == 'N' && $strVoting == 'Y')
        {
            $strVoting = 'Y';
        }
            else
        {
            $strVoting = 'N';
        }
        $objPoll -> Close();
        /**
         * Check amount of comments to poll
         */
        $objComments = $db -> Execute("SELECT count(`id`) FROM `polls_comments` WHERE `pollid`=".$objPollid -> fields['id']);
        $intComments = $objComments -> fields['count(`id`)'];
        $objComments -> Close();
        $smarty -> assign(array("Pollid" => $objPollid -> fields['id'],
				"Question" => $strQuestion,
				"Answers" => $arrPoll,
				"Voting" => $strVoting,
				"Votes" => $arrVotes,
				"Summaryvotes" => $intVotes,
				"Percentvotes" => $arrPercentvotes,
				"Summaryvoting" => $fltVoting,
				"Commentsamount" => $intComments,
				"Acomments" => A_COMMENTS,
				"Days" => $intDays,
				"Tdesc" => "Dodatkowe informacje",
				"Desc" => $strDesc));
    }
}

/**
* Vote in poll
*/
if (isset($_GET['action']) && $_GET['action'] == 'vote')
{
    if (!isset($_POST['answer']))
    {
        error(ERROR);
    }
    checkvalue($_GET['poll']);
    if ($_GET['poll'] != $objPollid -> fields['id'])
    {
        error(ERROR);
    }
    if ($player -> poll == 'Y')
    {
        error(ERROR);
    }
    $strAnswer = $db -> qstr($_POST['answer'], get_magic_quotes_gpc());
    $db -> Execute("UPDATE `polls` SET `votes`=`votes`+1 WHERE `id`=".$_GET['poll']." AND `poll`=".$strAnswer);
    $db -> Execute("UPDATE `players` SET `poll`='Y' WHERE `id`=".$player -> id);
    $smarty -> assign("Message", VOTE_SUCC);
}

$objPollid -> Close();

/**
* Show last 10 polls
*/
if (isset($_GET['action']) && $_GET['action'] == 'last')
{
    $objPollsid = $db -> SelectLimit("SELECT `id` FROM `polls` WHERE `lang`='".$lang."' AND `votes`=-1 ORDER BY `id` DESC", 10) or $db -> ErrorMsg();
    $arrQuestions = array();
    $arrPolls = array(array());
    $arrVotes = array(array());
    $arrSumvotes = array();
    $arrPercentvotes = array(array());
    $arrPercentmembers = array();
    $arrPercentvoting = array();
    $arrCommenst = array();
    $arrPollid = array();
    $i = 0;
    while (!$objPollsid -> EOF)
    {
        $j = 0;
        $objPoll = $db -> Execute("SELECT `poll`, `votes`, `members` FROM `polls` WHERE `id`=".$objPollsid -> fields['id']);
        while (!$objPoll -> EOF)
        {
            if ($objPoll -> fields['votes'] < 0)
            {
                $arrQuestions[$i] = $objPoll -> fields['poll'];
                $arrSumvotes[$i] = 0;
                if ($objPoll -> fields['members'])
                {
                    $intMembers = $objPoll -> fields['members'];
                }
                    else
                {
                    $objQuery = $db -> Execute("SELECT count(`id`) FROM `players`");
                    $intMembers = $objQuery -> fields['count(`id`)'];
                    $objQuery -> Close();
                }
            }
                else
            {
                $arrPolls[$i][$j] = $objPoll -> fields['poll'];
                $arrVotes[$i][$j] = $objPoll -> fields['votes'];
                $arrSumvotes[$i] = $arrSumvotes[$i] + $objPoll -> fields['votes'];
                $j++ ;
            }
            $objPoll -> MoveNext();
        }
        $objPoll -> Close();
        /**
        * Count percent for each option
        */
        $j = 0;
        foreach ($arrVotes[$i] as $intVote)
        {
            if ($intVote && $arrSumvotes[$i])
            {
                $arrPercentvotes[$i][$j] = ($intVote / $arrSumvotes[$i]) * 100;
                $arrPercentvotes[$i][$j] = round($arrPercentvotes[$i][$j]);
            }
                else
            {
                $arrPercentvotes[$i][$j] = 0;
            }
            $j++ ;
        }
        /**
        * Count percent for voting players
        */
        if ($arrSumvotes[$i] && $intMembers)
        {
            $arrPercentvoting[$i] = ($arrSumvotes[$i] / $intMembers) * 100;
            $arrPercentvoting[$i] = round($arrPercentvoting[$i], 2);
        }
            else
        {
            $arrPercentvoting[$i] = 0;
        }
        /**
         * Count comments to poll
         */
        $objComments = $db -> Execute("SELECT count(`id`) FROM `polls_comments` WHERE `pollid`=".$objPollsid -> fields['id']);
        $arrComments[$i] = $objComments -> fields['count(`id`)'];
        $objComments -> Close();
        $arrPollid[$i] = $objPollsid -> fields['id'];
        $i++ ;
        $objPollsid -> MoveNext();
    }
    $smarty -> assign(array("Questions" => $arrQuestions,
        "Answers" => $arrPolls,
        "Votes" => $arrVotes,
        "Lastinfo" => LAST_INFO,
        "Summaryvotes" => $arrSumvotes,
        "Percentvotes" => $arrPercentvotes,
        "Commentsamount" => $arrComments,
        "Acomments" => A_COMMENTS,
        "Pollid" => $arrPollid,
        "Percentvoting" => $arrPercentvoting));
}

/**
* Comments to text
*/
if (isset($_GET['action']) && $_GET['action'] == 'comments')
{
    $smarty -> assign(array("Amount" => '',
                            "Rank" => $player -> rank));
    
    require_once('includes/comments.php');
    /**
    * Display comments
    */
    if (!isset($_GET['step']))
      {
	if (!isset($_GET['page']))
	  {
	    $intPage = -1;
	  }
	else
	  {
	    $intPage = $_GET['page'];
	  }
        $intAmount = displaycomments($_GET['poll'], 'polls', 'polls_comments', 'pollid');
        $smarty -> assign(array("Tauthor" => $arrAuthor,
				"Taid" => $arrAuthorid,
            "Tbody" => $arrBody,
            "Amount" => $intAmount,
            "Cid" => $arrId,
            "Tdate" => $arrDate,
            "Poll" => $_GET['poll'],
            "Nocomments" => NO_COMMENTS,
            "Addcomment" => ADD_COMMENT,
            "Adelete" => A_DELETE,
            "Aadd" => A_ADD,
            "Tpages" => $intPages,
	    "Tpage" => $intPage,
	    "Fpage" => "Idź do strony:",
            "Writed" => WRITED));
      }

    /**
    * Add comment
    */
    if (isset($_POST['body']))
      {
        addcomments($_GET['poll'], 'polls_comments', 'pollid');
      }

    /**
    * Delete comment
    */
    if (isset($_GET['step']) && $_GET['step'] == 'delete')
      {
        deletecomments('polls_comments');
      }
}

/**
* Initialization of variable
*/
if (!isset($_GET['action']))
{
    $_GET['action'] = '';
    if ($player -> location == 'Altara')
    {
        $fltLogins = fmod($player -> logins, 2);
        if ($fltLogins)
        {
            $strPollsinfo = POLLS_INFO;
        }
            else
        {
            $strPollsinfo = POLLS_INFO2;
        }
    }
    else
    {
        $strPollsinfo = POLLS_INFO;
    }
    $smarty -> assign(array("Pollsinfo" => $strPollsinfo,
            "Nopolls" => NO_POLLS,
            "Lastpoll" => LAST_POLL,
            "Asend" => A_SEND,
            "Alast10" => A_LAST_10,
            "Polldays" => POLL_DAYS,
            "Tdays" => T_DAYS,
            "Pollend" => POLL_END));
}

/**
* Assign variables to template and display page
*/
$smarty -> assign(array("Action" => $_GET['action'],
    "Aback" => A_BACK,
    "Tvotes" => T_VOTES,
    "Sumvotes" => SUM_VOTES,
    "Tmembers" => T_MEMBERS,
    "Location" => $player -> location));
$smarty -> display('polls.tpl');

require_once("includes/foot.php");
?>
