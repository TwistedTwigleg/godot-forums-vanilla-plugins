<?php if(!defined('APPLICATION')) exit();

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
 
 
$PluginInfo['FilteredForumSearch'] = array(
    'Name' => 'Filtered Forum Search',
    'Description' => 'Adds additional filters to search with.',
    'Version' => '2.0.3',
    'RequiredApplications' => array('Vanilla' => '2.4'),
    'RequiredTheme' => FALSE,
    'RequiredPlugins' => array ('QnA' => '1.4'),
    'MobileFriendly' => TRUE,
    'HasLocale' => FALSE,
    'RegisterPermissions' => FALSE,
    'Author' => "Created for the Godot community forums",
    'AuthorUrl' => 'https://github.com/TwistedTwigleg/godot-forums-vanilla-plugins',
    'License' => 'ApacheV2.0 (modifications licensed under MIT)',
);

class FilteredForumSearchPlugin extends Gdn_Plugin {

    // Overridden Index method of SearchController.php to retrieve category to search into from the form data
    // and to call the overridden model's search() function with the added $CategoryFilter variable
    //
    public function SearchController_Index_Create($Sender, $Page = '')
	{
        $Sender->AddJsFile('search.js');
        $Sender->Title(Gdn::translate('Search'));

        SaveToConfig('Garden.Format.EmbedSize', '160x90', FALSE);

        list($Offset, $Limit) = OffsetLimit($Page, C('Garden.Search.PerPage', 20));
        $Sender->SetData('_Limit', $Limit);

        $Search = $Sender->Form->GetFormValue('Search');
        $Mode = $Sender->Form->GetFormValue('Mode');
		
		// TwistedTwigleg note:
		//		Need to look at this file! Looked at most of it and it looks good, just need to
		//		double check a few things.
		//
		// Send all additional advance filter data in a single array
		$AdvanceParams = array();
		$AdvanceParams["ADV_Filter_Category"] = $Sender->Form->GetFormValue('ADV_Filter_Category');
		$AdvanceParams["ADV_Filter_QNA"] = $Sender->Form->GetFormValue('ADV_Filter_QNA');
		$AdvanceParams["ADV_Filter_CommentCount"] = $Sender->Form->GetFormValue('ADV_Filter_CommentCount');
		$AdvanceParams["ADV_Filter_Username"] = $Sender->Form->GetFormValue('ADV_Filter_Username');

        if ($Mode)
            $Sender->SearchModel->ForceSearchMode = $Mode;
		try
		{
			// Send all of the data collected and perform a search!
			$ResultSet = $Sender->SearchModel->Search($Search, $Offset, $Limit, $AdvanceParams);
        }
		catch (Gdn_UserException $Ex)
		{
            $Sender->Form->AddError($Ex);
            $ResultSet = array();
        }
		catch (Exception $Ex)
		{
            LogException($Ex);
            $Sender->Form->AddError($Ex);
            $ResultSet = array();
        }

        Gdn::UserModel()->JoinUsers($ResultSet, array('UserID'));
        $Sender->SetData('SearchResults', $ResultSet, TRUE);
        $Sender->SetData('SearchTerm', Gdn_Format::Text($Search), TRUE);
        if($ResultSet)
            $NumResults = count($ResultSet);
        else
            $NumResults = 0;
        
        if ($NumResults == $Offset + $Limit)
            $NumResults++;

		// TwistedTwigleg note:
		//		Not needed anymore? Will need to investigate...
        // Build a pager
        $PagerFactory = new Gdn_PagerFactory();
        $Sender->Pager = $PagerFactory->GetPager('MorePager', $Sender);
        $Sender->Pager->MoreCode = 'More Results';
        $Sender->Pager->LessCode = 'Previous Results';
        $Sender->Pager->ClientID = 'Pager';
        $Sender->Pager->Configure(
            $Offset,
            $Limit,
            $NumResults,
            'dashboard/search/%1$s/%2$s/?Search='.Gdn_Format::Url($Search)
        );

        $Sender->CanonicalUrl(Url('search', TRUE));

        $Sender->Render();
    }

        
    // This is needed to override searchmodel.php with local copy
    public function Gdn_Dispatcher_BeforeDispatch_Handler($Sender)
	{
        require_once 'plugins/FilteredForumSearch/class.searchmodel.php';
    }


    // Intercept render_before to render custom view instead of original forum/search?xx page
    public function SearchController_Render_Before($Sender) {
        
        $Sender->AddCssFile($this->GetResource('views/style.css', FALSE, FALSE));
        //$Sender->AddJsFile($this->GetResource('js/searchcategory.js', FALSE, FALSE));

        $View = 'dashboard/search/index.php';
        $ThemeView = CombinePaths(array(PATH_THEMES, $Sender->Theme, strtolower($this->GetPluginFolder(false)), $View));

        if (file_exists($ThemeView))
        {
            $Sender->View = $ThemeView;
        }
		else
		{
            $Result = $Sender->fetchViewLocation('index', '', 'plugins/FilteredForumSearch');			
			$Sender->View = $Result;
        }
    }
}

