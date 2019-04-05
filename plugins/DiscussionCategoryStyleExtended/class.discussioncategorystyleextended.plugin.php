<?php if (!defined('APPLICATION')) exit();

$PluginInfo['DiscussionCategoryStyleExtended'] = array(
    'Description' => 'Extends the style of the discussion categories when in the discussion view.',
    'Name' => 'Disccusion Category Style Extended', 
    'Version' => '1.0.0',
    'RequiredApplications' => array('Vanilla' => '2.2'),
    'RequiredTheme' => false,
    'HasLocale' => false,
    'License' => 'MIT',
    'Author' => "Original by Noah Beard",
    'AuthorUrl' => 'RandomMomentania.com'
);


class CategoryDiscussionsExtendedPlugin extends Gdn_Plugin
{
	// When in the discussions view...
    public function discussionsController_render_before($sender, $args)
    {
		$Session = Gdn::Session();
		if ($Session->IsValid() && $this->GetUserMeta($Session->UserID, 'DC_Extended_Enabled', true, true))
		{
			if ($this->GetUserMeta($Session->UserID, 'DC_Extended_Enabled', true, true) == true)
			{
				$sender->addJsFile('pluginFunctionsDiscussions.js', 'plugins/DiscussionCategoryStyleExtended');
				$sender->addCssFile('pluginFunctionsDiscussions.css', 'plugins/DiscussionCategoryStyleExtended');
			}
		}
    }
	
	
	
	// User preference dropdown for theme selection on "Edit Profile" page.
    public function ProfileController_EditMyAccountAfter_Handler($Sender) {
        $Session = Gdn::Session();
		$Selected = $this->GetUserMeta($Session->UserID, 'DC_Extended_Enabled', true, true);
		
		if ($Selected == true)
		{
			//echo $Sender->Form->Label('Discussion Category Style Extended: Enabled');
			echo $Sender->Form->CheckBox('DC_Extended_Checkbox', "Use Discussion Category Style Extended", array('checked' => false));
		}
		else
		{
			//echo $Sender->Form->Label('Discussion Category Style Extended: Disabled');
			echo $Sender->Form->CheckBox('DC_Extended_Checkbox', "Use Discussion Category Style Extended", array());
		}
    }
	// Save the preferences of the user.
	public function UserModel_AfterSave_Handler($Sender) {
		$FormValues = $Sender->EventArguments['FormPostValues'];
		
        $UserID = val('UserID', $FormValues, 0);
        $plugin_enabled = val('DC_Extended_Checkbox', $FormValues, false);
        $this->SetUserMeta($UserID, 'DC_Extended_Enabled', $plugin_enabled);
    }
	
	
}