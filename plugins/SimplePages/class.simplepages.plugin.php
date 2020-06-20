<?php

$PluginInfo['SimplePages'] = array(
	'Name' => 'Simple Pages',
	'Description' => 'Admin-only "Use as Page" option for new discussions that gives it full-page layout at fixed, 1-level URLs and stashes them at the very end of the discussions list, closed & sunk.',
	'Version' 	=>	 '1.2',
	'MobileFriendly' => TRUE,
	'RequiredApplications' => array('Vanilla' => '2.1'),
	'Author' 	=>	 "Lincoln Russell",
	'AuthorEmail' => 'lincoln@vanillaforums.com',
	'AuthorUrl' =>	 'http://lincolnwebs.com',
	'License' => 'GNU GPL2'
);

/**
 * Just what it says on the tin.
 *
 * Changelog:
 *  1.1  - Added Garden.Settings.Manage permission option for 2.1 compatibility.
 * 	   - Renamed 'Page Slug' to 'URL Slug' and added helper text.
 * 	   - Added validation rule to enforce choosing a slug if making page.
 *  1.2  - Changed re-dispatch to use path instead of discussionUrl(), which broke for subfolders & non-Rewrite sites.
 * 	   - Added icon.
 */
class SimplePagesPlugin extends Gdn_Plugin {

	/**
    * Add 'Use as Page' option to new discussion form.
    */
    public function postController_afterDiscussionFormOptions_handler($sender) {
      if (CheckPermission('Garden.Community.Manage') || CheckPermission('Garden.Settings.Manage')) {
         // Checkbox to make a page
         echo Wrap($sender->Form->CheckBox('Page', T('Show as Page'), array('value' => '1')),
         	'div', array('class' => 'P'));
         // Page Slug box and helper text
         echo Wrap($sender->Form->Label('URL Slug', 'ForeignID').
         	$sender->Form->TextBox('ForeignID', array('style' => 'margin-bottom:0;')).
         	Wrap(sprintf(T('Page Slug helper text', 'Required. Must be unique. Example: &lsquo;simplepage&rsquo; would appear at <code>%s</code>.
         		Only letters, numbers, and hyphens are allowed.'), Url(T('Page Slug example slug', 'simplepage'), true)),
         		'span', array('class' => 'page-slug-help', 'style' => 'font-size: 11px;')),
         	'div', array('class' => 'P PageSlug Hidden'));

         // onclick for Show
			echo Wrap("jQuery(document).ready(function($){
				$('#Form_Page').change(function() {
				   if($(this).is(':checked')) {
						$('.PageSlug').removeClass('Hidden');
				   } else {
						$('.PageSlug').addClass('Hidden');
				   }
    			});
			});", 'script');
		}
   }

   /**
	 * Set DateLastComment to null & sink & close if this is an insert and 'Show as Page' was selected.
	 */
   public function discussionModel_beforeSaveDiscussion_handler($sender, &$args) {
   	if (CheckPermission('Garden.Community.Manage') || CheckPermission('Garden.Settings.Manage')) {
      	if ($args['Insert'] && $args['FormPostValues']['Page'] == 1) {
         	//$args['FormPostValues']['DateLastComment'] = NULL;
         	//$args['FormPostValues']['Sink'] = 1;
         	$args['FormPostValues']['Closed'] = 1;
         	$args['FormPostValues']['Type'] = 'SimplePage';
         	if (StringIsNullOrEmpty($args['FormPostValues']['ForeignID'])) {
         		$sender->Validation->AddValidationResult('ForeignID', 'URL Slug required for pages.');
				}
			}
		}
   }

	/**
	 * Special display rules for SimplePage discussions.
	 */
	public function discussionController_render_before($sender) {
		//if (!val('Type', $sender->Data('Discussion')) == 'SimplePage') {
		//	return;
		//}

		if (val('Type', $sender->Data('Discussion')) == 'SimplePage') {
			// If we've gotten here thru dummy discussion, redirect to canonical.
			$slug = val('ForeignID', $sender->Data('Discussion'));
			if (!C('SimplePage.Found')) {
				Redirect($slug, 301);
			}

			// No Panel, and allow more styling via body class 'SimplePage'
			$sender->CssClass .= ' NoPanel SimplePage';
			unset($sender->Assets['Panel']);

			// Hide postbit, bookmark, reactions, & closed notification.
			$sender->AddAsset('Head', Wrap('
				.DiscussionHeader, .Bookmark, .Closed, .Reactions { display: none; }
			', 'style'));

			// Fix canonical
			$sender->CanonicalUrl(Url($slug), TRUE);
		}
	}

	/**
	 * Use 404 handler to look for a SimplePage.
	 */
	public function gdn_dispatcher_notFound_handler($dispatcher, $args) {
      $requestUri = Gdn::Request()->Path();
      $discussionModel = new DiscussionModel();
      $result = $discussionModel
      	->GetWhere(array('Type' => 'SimplePage', 'ForeignID' => $requestUri))
      	->FirstRow(DATASET_TYPE_ARRAY);

		// Page exists with requested slug, so dispatch; no redirect.
		if ($discussionID = val('DiscussionID', $result)) {
			SaveToConfig('SimplePage.Found', true, false);
			Gdn::Dispatcher()->Dispatch('/discussion/'.$discussionID);
			exit();
		}
	}

	/**
	 * 1-time enable actions.
	 */
	public function setup() {

	}
}
