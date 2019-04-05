<?php if (!defined('APPLICATION')) exit();

$PluginInfo['ThemeChooser'] = array(
    'Name' => 'ThemeChooser',
    'Description' => 'Add the possibility to choose from predefined css-files (themes) in Edit Profile.',
    'Version' => '1.1',
    'RequiredApplications' => array('Vanilla' => '2.1.1'),
    'MobileFriendly' => true,
    'Author' => 'GhostX and exetico - Extended by Noah Beard',
    'AuthorUrl' => 'https://randommomentania.com',
    'License' => 'GNU GPL2',
	
	//"settingsUrl": "/settings/somepagehere",
	//"settingsPermission": "Garden.Settings.Manage",
);

class ThemeChooserPlugin extends Gdn_Plugin {
	
    public function Base_Render_Before($Sender) {
		
		// Don't change the theme when there is no panel to change, and when in
		// the dashboard or at any other admin-only page!
		if (!isset($Sender->Assets['Panel']) || $Sender->MasterView == 'admin')
		{
			return;
		}
		
		// Get the CSS file we want to enable.
		$cssFile = $this->EnabledValue();
		
		// Remove the godot_lightly.css file first, as it is what the forums are set to by default.
		// If we did not do this, then styling applied by godot_lightly.css would be enabled on ALL themes.
		//
		// NOTE: note that we are getting the CSS file from the boostrap theme folder!
		$Sender->removeCssFile("custom_godot_lightly.css", "themes/bootstrap/design");
		
		// Add the CSS file that goes with the theme the user has chosen!
		$Sender->AddCssFile($cssFile.'.css', "themes/bootstrap/design");
    }

    // Check for mobile view and user preference
    private function Enabled() {
        $Session = Gdn::Session();
        return !(
            ($Session->IsValid() && !$this->GetUserMeta($Session->UserID, 'Enable', true, true)) ||
            (!C('ThemeChooser.Mobile', false) && IsMobile())
        );
    }

    private function EnabledValue(){
        $Session = Gdn::Session();
        if ($Session->IsValid() && $this->GetUserMeta($Session->UserID, 'Enable', true, true))
        {
            return $this->GetUserMeta($Session->UserID, 'Enable', true, true);
        }
        return false;
    }


    // User preference dropdown for theme selection on "Edit Profile" page.
    // You're able to use whatever you like. Just remeber
    public function ProfileController_EditMyAccountAfter_Handler($Sender) {
        $Session = Gdn::Session();
		
		// No value by default theme, cause of the normal style is loaded by Vanilla - Add a value if you like.
		$Options = array('' => ('Default'),
			// Add new lines here, if you want, in this style: 'value' => ('NameOfTheValue'),
			'custom_godot_lightly' => ('Godot Lightly'),
			'custom_godot_darkly' => ('Godot Darkly'));
		
        $Selected = ($this->GetUserMeta($Session->UserID, 'Enable', true, true));
        $Fields = array('TextField' => 'Text', 'ValueField' => 'Code', 'Value' => $Selected);

        echo $Sender->Form->Label('Theme');
        echo Wrap(
            $Sender->Form->Dropdown('ThemeChooser', $Options, $Fields),
            'li',
            array('class' => 'ThemeChooser')
        );
    }

    public function UserModel_AfterSave_Handler($Sender) {
        $FormValues = $Sender->EventArguments['FormPostValues'];
        $UserID = val('UserID', $FormValues, 0);
        $ThemeChooser = val('ThemeChooser', $FormValues, false);

        $this->SetUserMeta($UserID, 'Enable', $ThemeChooser);
    }

}