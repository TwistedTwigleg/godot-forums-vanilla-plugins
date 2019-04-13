<?php if (!defined('APPLICATION')) exit();

$PluginInfo['DiscordEmbedWidget'] = array(
    'Description' => 'Adds a Discord embed widget to a forum',
    'Name' => 'Discord Embed Widget', 
    'Version' => '1.0.0',
    'RequiredApplications' => array('Vanilla' => '2.2'),
    'RequiredTheme' => false,
    'HasLocale' => false,
    'License' => 'MIT',
    'Author' => "Created for the Godot community forums",
    'AuthorUrl' => 'https://github.com/TwistedTwigleg/godot-forums-vanilla-plugins',
    
    'SettingsUrl' => '/settings/DiscordEmbedWidget',
	'SettingsPermission' => 'Garden.Settings.Manage',
);

class DiscordEmbedWidgetPlugin extends Gdn_Plugin
{
	
	// NOTE: If you are using the Panel asset, then you can use the code below to make the Discord plugin add itself to the side panel.
	//
	// Because for the Godot Forums we are using a custom side panel structure, we'll just add the module in the TPL file.
	/*
    public function base_render_before($sender)
	{
        if (InSection("Dashboard"))
            return;
		
		$Session = Gdn::Session();
		if ($Session->IsValid() && $this->GetUserMeta($Session->UserID, 'Discord_Embed_Widget_Enabled', true, true))
		{
			if ($this->GetUserMeta($Session->UserID, 'Discord_Embed_Widget_Enabled', true, true) == true)
			{
				if (GetValue('Panel',$Sender->Assets) && $Sender->MasterView != 'admin')
				{
					$DiscordEmbedWidgetModule = new DiscordEmbedWidgetModule($Sender);
					$Sender->AddModule($DiscordEmbedWidgetModule);
				}
			}
		}
    }
	*/
	
	
    /**
     * Configure settings page in dashboard.
     *
     * @param SettingsController $sender
     * @param array $args
     */
    public function SettingsController_DiscordEmbedWidget_Create($sender, $args) {
        $sender->permission('Garden.Settings.Manage');
        $cf = new ConfigurationModule($sender);
		
		$cf->initialize(array('Plugins.DiscordEmbedWidget.DiscordServerID' => array(
            'LabelCode' => 'Discord server ID for widget',
            'Control' => 'TextBox',
            'Default' => C('Plugins.DiscordEmbedWidget.DiscordServerID', ''),
            'Description' => 'Place the Discord server ID for the Discord server you want to use. You can find instructions on <a href="https://support.discordapp.com/hc/en-us/articles/206346498-Where-can-I-find-my-User-Server-Message-ID-">how to obtain the ID here</a>.'
        ),
        ));
        
        $sender->setData('Title', t('DiscordEmbedWidget'));
        $cf->renderAll();
    }
    
	
	// User preference dropdown for theme selection on "Edit Profile" page.
    public function ProfileController_EditMyAccountAfter_Handler($Sender) {
        $Session = Gdn::Session();
		$Selected = $this->GetUserMeta($Session->UserID, 'Discord_Embed_Widget_Enabled', true, true);
		
		if ($Selected == true)
		{
			echo $Sender->Form->CheckBox('Discord_Embed_Widget_Checkbox', "Add Godot Discord widget", array('checked' => false));
		}
		else
		{
			echo $Sender->Form->CheckBox('Discord_Embed_Widget_Checkbox', "Add Godot Discord widget", array());
		}
    }
	// Save the preferences of the user.
	public function UserModel_AfterSave_Handler($Sender) {
		$FormValues = $Sender->EventArguments['FormPostValues'];
		
        $UserID = val('UserID', $FormValues, 0);
        $plugin_enabled = val('Discord_Embed_Widget_Checkbox', $FormValues, false);
        $this->SetUserMeta($UserID, 'Discord_Embed_Widget_Enabled', $plugin_enabled);
    }
	
	
}