<?php if (!defined('APPLICATION')) exit();

class DiscordEmbedWidgetModule extends Gdn_Module
{
   
   
   public function AssetTarget()
   {
      return 'Panel';
   }

   public function ToString()
   {
	   
		$Session = Gdn::Session();
		
		// See (https://open.vanillaforums.com/discussion/comment/217856/) and (https://open.vanillaforums.com/discussion/29011/access-custom-profile-fields-profile-extender-fields-from-plugin)
		$user_module = Gdn::userMetaModel();
		
		if ($Session->IsValid())
		{
			// How to show all data stored in meta data:
			//$All_User_Meta_Data = $user_module->GetUserMeta($Session->UserID, '%', false);
			//var_dump($All_User_Meta_Data);
			
			// Get the meta data from the Discord Embed Widget plugin so we can figure out whether we are
			// supposed to show the plugin or not.
			$Selected = $user_module->GetUserMeta($Session->UserID, 'Plugin.DiscordEmbedWidget.Discord_Embed_Widget_Enabled', false);
			
			if ($Selected['Plugin.DiscordEmbedWidget.Discord_Embed_Widget_Enabled'] == true)
			{
				// TODO: find a way to get the Discord link through a setting or something...
				return parent::toString();
			}
		}
		
		return '';
		
   }   
}