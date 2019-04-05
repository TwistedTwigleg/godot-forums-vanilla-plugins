<?php if (!defined('APPLICATION')) exit();



$PluginInfo['TagsExtended'] = array(
    'Description' => 'Extends the standard tags system.',
    'Name' => 'Tags Extended', 
    'Version' => 'alpha 0.1',
    'RequiredApplications' => array('Vanilla' => '2.2'),
    'RequiredTheme' => false,
    //'RequiredPlugins' => array('Tagging' => '>=1.8'),
    'HasLocale' => false,
    'License' => 'GNU GPL2',
    // 'SettingsPermission' => 'Garden.Settings.Manage',
    'Author' => "Original by André A. B. with modifications by Noah Beard",
    'AuthorUrl' => 'RandomMomentania.com'
);


class TagsExtendedPlugin extends Gdn_Plugin {

    /**
     * Plugin constructor
     *
     * This fires once per page load, during execution of bootstrap.php. It is a decent place to perform
     * one-time-per-page setup of the plugin object. Be careful not to put anything too strenuous in here
     * as it runs every page load and could slow down your forum.
     */
    public function __construct() {

    }

    /**
     * StyleCss Event Hook
     *
     * This is a good place to put UI stuff like CSS and Javascript inclusions.
     *
     * @param $sender Sending controller instance
     */
    public function assetModel_styleCss_handler($sender)
	{
        $sender->addCssFile('tagsextended.css', 'plugins/TagsExtended');
    }

    
    private function displayTags($sender, $args)
	{
		if (empty($args['Discussion']->Tags))
		{
			// No tags, nothing to do.
			return;
		}
		
        ob_start();
        
		echo '<ul class="TagsEx">';
		
		foreach ($args['Discussion']->Tags as $tag)
		{
			echo '<li>'.anchor(
				 Gdn_Format::text($tag['FullName']),
				"/discussions/tagged/".Gdn_Format::text($tag['Name']),
				array('id' => 'Tag_'.Gdn_Format::text($tag['Name']))
			  ).'</li>';
		}
        return ob_get_clean();
    }


    public function discussionsController_DiscussionMeta_handler($sender, $args)
	{
	   echo $this->displayTags($sender, $args);
	}
    
    public function categoriesController_DiscussionMeta_handler($sender, $args)
	{
		echo $this->displayTags($sender, $args);
	}

    public function discussionController_afterDiscussionBody_handler ($sender, $args)
	{
        if (empty($args['Discussion']->Tags))
		{
                // No tags, nothing to do.
                return;
		}
        echo '<br><span id="Tags">Tags</span> : '.$this->displayTags($sender, $args);
	}

}
