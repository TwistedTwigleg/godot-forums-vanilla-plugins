<?php if (!defined('APPLICATION')) exit();

/*  Copyright 2019 Noah Beard and Godot Community Forums team
 *
 *  Originally a plugin by "Franco Solerio" - "http://digitalia.fm" - "franco@solerio.net"
 *  
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

class SearchModel extends Gdn_Model
{	
	
	public function Search($Search, $Offset = 0, $Limit = 20, $AdvanceParams = array())
	{
	  
		// TwistedTwigleg note:
		//		See
		//			https://docs.vanillaforums.com/developer/framework/datasets/
		//			https://docs.vanillaforums.com/developer/framework/database/
		// 		for information on how to use SQL with Vanilla.
		
		// If there is no search, and no advance parameters, then abort.
		// This provides the same functionality as default Vanilla.
		if (empty($Search) == true)
		{
			// Check to see if a username not been set. If there is no username, then abort!
			//
			// TwistedTwigleg note:
			//		The reason we are only checking the username is because
			// 		a empty search with just a username allows users to check all of the posts made by
			// 		a single user, which could be helpful.
			//	
			// 		However, we should abort for empty searches with category/QnA filters, because there
			//		are already ways to filter that through other already installed plugins.
			if (empty($AdvanceParams["ADV_Filter_Username"]) == true)
			{
				return array();
			}
		}
		
		// TwistedTwigleg note:
		//			Any data we need to query to get, it must be done *before* we build the final SQL query.
		//			So in this case, we have to get the UserID we are searching with BEFORE we build the query
		// ADV_Filter: Username Setup: Get UserID from Username
		$ADV_Filter_Username_ID = null;
		if (array_key_exists("ADV_Filter_Username", $AdvanceParams) == true)
		{
			$Filter_Username = $AdvanceParams["ADV_Filter_Username"];
			if (!empty($Filter_Username))
			{
				try
				{
					$ADV_Filter_Username_ID = Gdn::userModel()->getByUsername($Filter_Username);
				}
				catch (Exeception $e)
				{
					// Something went wrong (probably no user with that name)
					$ADV_Filter_Username_ID = null;
				}
				
				// If ADV_Filter_Username_ID is still empty, then either there is no user with the inputted
				// username, or some unknown error. Either way, abort the search!
				if (empty($ADV_Filter_Username_ID))
				{
					return array();
				}
			}
		}
		
		
		// The SQL query will always need the following as the beginning, regardless of which filter(s) are applied.
		$SQL_Query = Gdn::sql();
		$SQL_Query->reset();
		$SQL_Query->select('gdn_comment.*');
		$SQL_Query->select('gdn_discussion.Name', '', 'Discussion_Name');
		$SQL_Query->select('gdn_discussion.CategoryID', '', 'Discussion_CategoryID');
		$SQL_Query->from('Comment gdn_comment');
		$SQL_Query->join('Discussion gdn_discussion', 'gdn_comment.DiscussionID = gdn_discussion.DiscussionID');
		
		
		// Add variables/conditions needed across multiple filters here!
		// *************************************
		// Variables for storing which type of SQL query will be made based on the inputted text, and a variable to hold
		// the search text so it can be modified without modifying the passed in $search variable.
		$SQL_Query_Search_Mode = "LIKE";
		$SQL_Query_Search_Text = $Search;
		// Determine which mode the search will use based on the length of the inputted search text
		if (strlen($SQL_Query_Search_Text) > 4)
		{
			$SQL_Query_Search_Mode = "MATCH";
		}
		else
		{
			$SQL_Query_Search_Mode = "LIKE";
		}
		
		
		// Add filters here!
		// *************************************
		
		
		// ADV_Filter: Category
		if (array_key_exists("ADV_Filter_Category", $AdvanceParams) == true)
		{
			$Filter_Category = $AdvanceParams["ADV_Filter_Category"];
			if (!empty($Filter_Category))
			{
				$SQL_Query->where('gdn_discussion.CategoryID', $Filter_Category);
			}
		}
		
		
		// ADV_Filter: Answer
		if (array_key_exists("ADV_Filter_QNA", $AdvanceParams) == true)
		{
			$Filter_Answer = $AdvanceParams["ADV_Filter_QNA"];
			if (!empty($Filter_Answer))
			{
				if ($Filter_Answer == "Answered")
				{
					$SQL_Query->where('gdn_discussion.QnA', 'Answered');
				}
				else if ($Filter_Answer == "Accepted")
				{
					$SQL_Query->where('gdn_discussion.QnA', 'Accepted');
				}
				else if ($Filter_Answer == "Unanswered")
				{
					$SQL_Query->where('gdn_discussion.QnA', 'Unanswered');
				}
				else if ($Filter_Answer == "No_QA")
				{
					$SQL_Query->where('gdn_discussion.QnA IS NULL');
				}
				else if ($Filter_Answer == "Only_QA")
				{
					$SQL_Query->where('gdn_discussion.QnA IS NOT NULL');
				}
			}
		}
		
		
		// ADV_Filter: Comment Count
		if (array_key_exists("ADV_Filter_CommentCount", $AdvanceParams) == true)
		{
			$Filter_CommentCount = $AdvanceParams["ADV_Filter_CommentCount"];
			if (!empty($Filter_CommentCount))
			{
				if ($Filter_CommentCount == "over_zero")
				{
					$SQL_Query->where('gdn_discussion.CountComments >', 0);
				}
				else if ($Filter_CommentCount == "over_one")
				{
					$SQL_Query->where('gdn_discussion.CountComments >', 1);
				}
				else if ($Filter_CommentCount == "over_two")
				{
					$SQL_Query->where('gdn_discussion.CountComments >', 2);
				}
				else if ($Filter_CommentCount == "over_five")
				{
					$SQL_Query->where('gdn_discussion.CountComments >', 5);
				}
				else if ($Filter_CommentCount == "over_ten")
				{
					$SQL_Query->where('gdn_discussion.CountComments >', 10);
				}
			}
		}
		
		
		// ADV_Filter: Username
		if (array_key_exists("ADV_Filter_Username", $AdvanceParams) == true)
		{
			if (!empty($ADV_Filter_Username_ID) == true)
			{
				$SQL_Query->where('gdn_comment.InsertUserID', $ADV_Filter_Username_ID->UserID);
			}
		}
		
		
		// ADV_Filter: SearchOccurrence
		// TwistedTwigleg note:
		//				Not perfect, but it does do a better job of returning any results that contain the keywords
		//				instead of results that ONLY contain the keyword.
		if (array_key_exists("ADV_Filter_SearchOccurrence", $AdvanceParams) == true)
		{
			$Filter_SearchOccurance = $AdvanceParams["ADV_Filter_SearchOccurrence"];
			if (!empty($Filter_SearchOccurance))
			{
				// Only search for the exact search term:
				if ($Filter_SearchOccurance == "exact_only")
				{
					if ($SQL_Query_Search_Mode == "MATCH")
					{
						$SQL_Query_Search_Text = $this->SQL_Filter_SearchOccurrence_Format_MATCH($SQL_Query_Search_Text, true);
					}
					else if ($SQL_Query_Search_Mode == "LIKE")
					{
						$SQL_Query_Search_Text = $this->SQL_Filter_SearchOccurrence_Format_LIKE($SQL_Query_Search_Text, true);
					}
				}
				// Search for any occurrence of the inputted search term(s):
				else if ($Filter_SearchOccurance == "any_occurrence")
				{
					if ($SQL_Query_Search_Mode == "MATCH")
					{
						$SQL_Query_Search_Text = $this->SQL_Filter_SearchOccurrence_Format_MATCH($SQL_Query_Search_Text, false);
					}
					else if ($SQL_Query_Search_Mode == "LIKE")
					{
						$SQL_Query_Search_Text = $this->SQL_Filter_SearchOccurrence_Format_LIKE($SQL_Query_Search_Text, false);
					}
				}
				// Use whatever the default is for searching, if an unknown SearchOccurrence filter is passed.
				// (as of when this was written, it is the same as 'any occurrence')
				else
				{
					if ($SQL_Query_Search_Mode == "MATCH")
					{
						$SQL_Query_Search_Text = $this->SQL_Filter_SearchOccurrence_Format_MATCH($SQL_Query_Search_Text);
					}
					else if ($SQL_Query_Search_Mode == "LIKE")
					{
						$SQL_Query_Search_Text = $this->SQL_Filter_SearchOccurrence_Format_LIKE($SQL_Query_Search_Text);
					}
				}
			}
			// If the SearchOccurrence filter is empty, then use whatever the default for the format function.
			// (as of when this was written, it is the same as 'any occurrence')
			else
			{
				if ($SQL_Query_Search_Mode == "MATCH")
				{
					$SQL_Query_Search_Text = $this->SQL_Filter_SearchOccurrence_Format_MATCH($SQL_Query_Search_Text);
				}
				else if ($SQL_Query_Search_Mode == "LIKE")
				{
					$SQL_Query_Search_Text = $this->SQL_Filter_SearchOccurrence_Format_LIKE($SQL_Query_Search_Text);
				}
			}
		}
		// If there is no SearchOccurrence filter in the array, then use whatever the default for the format function.
		// (as of when this was written, it is the same as 'any occurrence')
		else
		{
			if ($SQL_Query_Search_Mode == "MATCH")
			{
				$SQL_Query_Search_Text = $this->SQL_Filter_SearchOccurrence_Format_MATCH($SQL_Query_Search_Text);
			}
			else if ($SQL_Query_Search_Mode == "LIKE")
			{
				$SQL_Query_Search_Text = $this->SQL_Filter_SearchOccurrence_Format_LIKE($SQL_Query_Search_Text);
			}
		}
		
		
		// ADV_Filter: SearchIn
		//
		// TwistedTwigleg note:
		//		I'm not sure if the LIKE/MATCH-AGAINST SQL command needs to be last, but when I was writing the plugin using
		//		direct SQL queries, it worked best if the LIKE/MATCH-AGAINST command was the last command passed.
		//
		if (array_key_exists("ADV_Filter_SearchIn", $AdvanceParams) == true)
		{
			$Filter_SearchIn = $AdvanceParams["ADV_Filter_SearchIn"];
			if (!empty($Filter_SearchIn))
			{
				// Search only by discussion text
				if ($Filter_SearchIn == "only_text")
				{
					if ($SQL_Query_Search_Mode == "MATCH")
					{
						$SQL_Query->where("MATCH(gdn_comment.Body) AGAINST('{$SQL_Query_Search_Text}' IN BOOLEAN MODE)", null, false, false);
					}
					else if ($SQL_Query_Search_Mode == "LIKE")
					{
						$SQL_Query->like('gdn_comment.Body', $SQL_Query_Search_Text);
					}
				}
				// Search only by discussion title
				else if ($Filter_SearchIn == "only_title")
				{
					if ($SQL_Query_Search_Mode == "MATCH")
					{
						$SQL_Query->where("MATCH(gdn_discussion.Name) AGAINST('{$SQL_Query_Search_Text}' IN BOOLEAN MODE)", null, false, false);
					}
					else if ($SQL_Query_Search_Mode == "LIKE")
					{
						$SQL_Query->like('gdn_discussion.Name', $SQL_Query_Search_Text);
					}
				}
				// If for some reason the SearchIn filter passed is an unknown value, search in both the discussion title and text.
				else
				{
					if ($SQL_Query_Search_Mode == "MATCH")
					{
						$SQL_Query->where("MATCH(gdn_discussion.Name, gdn_comment.Body) AGAINST('{$SQL_Query_Search_Text}' IN BOOLEAN MODE)", null, false, false);
					}
					else if ($SQL_Query_Search_Mode == "LIKE")
					{
						$SQL_Query->beginWhereGroup();
						$SQL_Query->like('gdn_comment.Body', $SQL_Query_Search_Text);
						$SQL_Query->Orlike('gdn_discussion.Name', $SQL_Query_Search_Text);
						$SQL_Query->endWhereGroup();
					}
				}
			}
			// If the SearchIn filter passed is empty, search in both the discussion title and text.
			else
			{
				if ($SQL_Query_Search_Mode == "MATCH")
				{
					$SQL_Query->where("MATCH(gdn_discussion.Name, gdn_comment.Body) AGAINST('{$SQL_Query_Search_Text}' IN BOOLEAN MODE)", null, false, false);
				}
				else if ($SQL_Query_Search_Mode == "LIKE")
				{
					$SQL_Query->beginWhereGroup();
					$SQL_Query->like('gdn_comment.Body', $SQL_Query_Search_Text);
					$SQL_Query->Orlike('gdn_discussion.Name', $SQL_Query_Search_Text);
					$SQL_Query->endWhereGroup();
				}
			}
		}
		// If the SearchIn filter is not within the array, search in both the discussion title and text.
		else
		{
			if ($SQL_Query_Search_Mode == "MATCH")
			{
				$SQL_Query->where("MATCH(gdn_discussion.Name, gdn_comment.Body) AGAINST('{$SQL_Query_Search_Text}' IN BOOLEAN MODE)", null, false, false);
			}
			else if ($SQL_Query_Search_Mode == "LIKE")
			{
				$SQL_Query->beginWhereGroup();
				$SQL_Query->like('gdn_comment.Body', $SQL_Query_Search_Text);
				$SQL_Query->Orlike('gdn_discussion.Name', $SQL_Query_Search_Text);
				$SQL_Query->endWhereGroup();
			}
		}
		
		// *************************************
		
		// Finally, make sure that the query searches from newest to oldest, and only returns 20 results.
		$SQL_Query->orderBy('gdn_comment.DateInserted', 'desc');
		$SQL_Query->limit($Limit, $Offset);
		
		// Get the results of the SQL query!
		$Result = $SQL_Query->get()->ResultArray();
		
		// Go through the results...
		foreach ($Result as $Key => $Value)
		{
			// Use this to quickly see info within $Value.
			//var_dump($Value);
			
			$Ret_Val = array();
			$Ret_Val["Relavence"] = $Key;
			$Ret_Val["PrimaryID"] = $Value["DiscussionID"];
			$Ret_Val["Title"] = $Value["Discussion_Name"];
			
			// See class.searchcontroller.php in Application/Dashboard to find this function being used!
			$Summary_Text = searchExcerpt(htmlspecialchars(Gdn_Format::plainText($Value['Body'], $Value['Format'])), $Search);
			$Ret_Val["Summary"] = $Summary_Text;
			
			$Ret_Val["Format"] = $Value['Format'];
			$Ret_Val["CategoryID"] = $Value["Discussion_CategoryID"];
			
			$Comment_Model = new CommentModel();
			$Comment_ID = $Comment_Model->GetID($Value["CommentID"]);
			$Ret_Val["Url"] = commentUrl($Comment_ID, $withDomain=true);
			
			$Ret_Val["DateInserted"] = $Value['DateInserted'];
			$Ret_Val["UserID"] = $Value['InsertUserID'];
			
			$Result[$Key] = $Ret_Val;
		}

		return $Result;
		
	}
	
	
	
	// Both of the functions below are helper functions for the SearchOccurance filter!
	// Edits the search so it is formatted and ready to be used in a SQL LIKE query.
	public function SQL_Filter_SearchOccurrence_Format_LIKE($Search_Text, $Format_Search_For_Exact=false)
	{
		if ($Format_Search_For_Exact == false)
		{
			// Search for all occurances of the word(s) inputted (akin to Google search)
			// We can do this by splitting the terms by space, adding at the beginning and end of each term.
			// It is not perfect, but it's better than nothing.
			$Search_Terms = explode(' ', $Search_Text);
			$Search_Text = '';
			foreach ($Search_Terms as $Key => $Value)
			{
				$Search_Text = '%'.$Search_Text.$Value.'%';
			}
		}
		else
		{
			// Do nothing!
		}
		
		return $Search_Text;
	}
	// Edits the search so it is formatted and ready to be used in a SQL MATCH AGAINST query.
	public function SQL_Filter_SearchOccurrence_Format_MATCH($Search_Text, $Format_Search_For_Exact=false)
	{	
		if ($Format_Search_For_Exact == false)
		{
			// Search for all occurances of the word(s) inputted (akin to Google search)
			// We can do this by splitting the terms by space, adding ',' between each term.
			$Search_Terms = explode(' ', $Search_Text);
			$Search_Text = '';
			foreach ($Search_Terms as $Key => $Value)
			{
				$Search_Text = $Search_Text.$Value.',';
			}
		}
		else
		{
			$Search_Text = '"'.$Search_Text.'"';
		}
		
		return $Search_Text;
	}
	
}