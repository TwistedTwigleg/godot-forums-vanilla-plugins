<?php if (!defined('APPLICATION')) exit(); ?>

<div class="SearchForm">

<?php
	$Form = $this->Form;
	$Form->InputPrefix = '';


	$ADV_Filter_SearchedSearchIn=$Form->GetFormValue('ADV_Filter_SearchIn');
	$ADV_Filter_SearchedCategory=$Form->GetFormValue('ADV_Filter_Category');
	$ADV_Filter_SearchedQNA=$Form->GetFormValue('ADV_Filter_QNA');
	$ADV_Filter_SearchedCommentCount=$Form->GetFormValue('ADV_Filter_CommentCount');


	// Variables for discussion/title searching
	$SearchInDropdownOptions = array(
				'' => 'Search in discussion contents and title',
				'only_text' => Gdn::translate('Search in discussion contents'),
				'only_title' => Gdn::translate('Search in discussion title'));
	$SearchInDropdownFields = array('TextField' => 'Text', 'ValueField' => 'Code', 'Value' => $ADV_Filter_SearchedSearchIn);

	// Variables for answered dropdown
	$AnswerDropdownOptions = array(
				'' => Gdn::translate('All Discussions and Questions'),
				'Answered' => Gdn::translate('Only Answered Questions'),
				'Accepted' => Gdn::translate('Only Accepted Questions'),
				'Unanswered' => Gdn::translate('Only Unanswered Questions'),
				'No_QA' => Gdn::translate('Only Discussions (No Questions)'),
				'Only_QA' => Gdn::translate('Only Questions (No Discussions)'));
	$AnswerDropdownFields = array('TextField' => 'Text', 'ValueField' => 'Code', 'Value' => $ADV_Filter_SearchedQNA);

	// Variables for post count dropdown
	$CommentCountDropdownOptions = array(
				'' => 'All Discussions',
				'over_zero' => Gdn::translate('Only Over 0 comments/replies'),
				'over_one' => Gdn::translate('Only Over 1 comments/replies'),
				'over_two' => Gdn::translate('Only Over 2 comments/replies'),
				'over_five' => Gdn::translate('Only Over 5 comments/replies'),
				'over_ten' => Gdn::translate('Only Over 10 comments/replies'));
	$CommentCountDropdownFields = array('TextField' => 'Text', 'ValueField' => 'Code', 'Value' => $ADV_Filter_SearchedCommentCount);


	echo  
	$Form->Open(array('action' => Url('/search'), 'method' => 'get')),
		
		// Search input
		'<div class="SiteSearch">',
		$Form->Label('Search Text', 'Search'),
		$Form->TextBox('Search'),
		$Form->Button('Search', array('Name' => '')),
		'</div>',
		
		'<br />',
		
		// Collapse button
		'<button type="button" class="sidebar-toggle" data-toggle="collapse" data-target=".advance-search-collapse">',
		Gdn::translate('Additional Search Filters'),
		'</button>',
		
		// Start collapse div
		'<div class="advance-search-collapse collapse">',
		
		// SearchIn dropdown
		'<div class="SearchInDropdown">',
		$Form->Label('Filter search location', 'ADV_Filter_SearchIn'), ' ',
		$Form->DropDown('ADV_Filter_SearchIn', $SearchInDropdownOptions, $SearchInDropdownFields).
		'</div>',
		
		// Category dropdown (provided by SearchCategory plugin code!)
		'<div class="SearchCategoryDropdown">',
		$Form->Label('Filter by Category', 'ADV_Filter_Category'), ' ',
		$Form->CategoryDropDown('ADV_Filter_Category', array('Value' => $ADV_Filter_SearchedCategory, 'IncludeNull' => true)).
		'</div>',
		
		// Q&A dropdown
		'<div class="SearchAnswerDropdown">',
		$Form->Label('Filter by Q&A', 'ADV_Filter_QNA'), ' ',
		$Form->DropDown('ADV_Filter_QNA', $AnswerDropdownOptions, $AnswerDropdownFields).
		'</div>',
		
		// Comment count dropdown
		'<div class="SearchCommentCountDropdown">',
		$Form->Label('Filter by Comment Count', 'ADV_Filter_CommentCount'), ' ',
		$Form->DropDown('ADV_Filter_CommentCount', $CommentCountDropdownOptions, $CommentCountDropdownFields).
		'</div>',
		
		// Username input
		'<div class="SearchUsername">',
		$Form->Label('Filter by Username (case sensitive)', 'ADV_Filter_Username'), ' ',
		$Form->TextBox('ADV_Filter_Username'),
		'</div>',
		
		'<div class="SearchNote">',
		'<br />',
		'<center><h4>',
		$Form->Label('Submit search to apply filter(s)', 'ADV_Search_Note'), ' ',
		'</h4></center>',
		'<br />',
		'</div>',
		
		// Close collapse div
		'</div>',
		$Form->Errors(),
		$Form->Close();

?>

</div>

<?php

	if (!is_array($this->SearchResults) || count($this->SearchResults) == 0)
	{
		if (empty($this->SearchTerm))
		{
			echo '<p class="NoResults">', Gdn::translate('Input text and/or a Username to search.'), '</p>';
		}
		else
		{
			echo '<p class="NoResults">', sprintf(Gdn::translate('No results for %s.', 'No results for <b>%s</b>.'), htmlspecialchars($this->SearchTerm)), '</p>';
		}
	}
	else
	{
	   $ViewLocation = $this->FetchViewLocation('results');
	   include($ViewLocation);
	}

