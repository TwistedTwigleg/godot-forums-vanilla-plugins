<?php if (!defined('APPLICATION')) exit();

/**
 * ImgurUpload Plugin
 *
 * Adds an image upload feature (with drag and drop!) that utilises the Imgur API
 *
 * @author James Ducker <james.ducker@gmail.com>
 * @license http://opensource.org/licenses/gpl-3.0.html GPL
 * @package Addons
 */

// Define the plugin:
$PluginInfo['ImgurUpload'] = array(
	'Description' => 'Adds an image upload feature (with drag and drop!) that utilises the Imgur API',
	'Version' => '1.1.14',
	'RequiredApplications' => array('Vanilla' => '2.4'),
	'RequiredTheme' => FALSE,
	'RequiredPlugins' => FALSE,
	'HasLocale' => FALSE,
	'MobileFriendly' => TRUE,
	'SettingsUrl' => '/settings/ImgurUpload',
	'SettingsPermission' => 'Garden.Settings.Manage',
	'Author' => "James Ducker (With modifications by the Godot community forums team)",
	'AuthorEmail' => 'james.ducker@gmail.com',
	'AuthorUrl' => 'https://github.com/jamesinc',
	'License' => 'GPL-3.0'
);

class ImgurUploadPlugin extends Gdn_Plugin {

	public function Setup() {}

	/**
     * Configure settings page in dashboard.
     *
     * @param SettingsController $sender
     * @param array $args
     */
    public function SettingsController_ImgurUpload_Create($sender, $args) {
        $sender->permission('Garden.Settings.Manage');
        $cf = new ConfigurationModule($sender);
		
		$cf->initialize(array('Plugins.ImgurUpload.ClientID' => array(
            'LabelCode' => 'Imgur API Client ID',
            'Control' => 'TextBox',
            'Default' => C('Plugins.ImgurUpload.ClientID', ''),
            'Description' => 'Register for Imgur API access at: <a href="https://api.imgur.com/oauth2/addclient">https://api.imgur.com/oauth2/addclient</a>'
        ),
            'Plugins.ImgurUpload.ProcessImageURLs' => array(
                'LabelCode' => 'Process image URLs',
                'Control' => 'Checkbox',
                'Default' => C('Plugins.ImgurUpload.ProcessImageURLs', ''),
                'Description' => 'Check the below checkbox to have the plugin attempt to identify when the user is dragging in an image URL.
												This is usually if the user is dragging an image across from another web page.
												If an image URL is detected, it will be wrapped with image markup.'
            ),
            'Plugins.ImgurUpload.ResizeImages' => array(
                'LabelCode' => 'Resize images',
                'Control' => 'Checkbox',
                'Default' => C('Plugins.ImgurUpload.ResizeImages', ''),
                'Description' => 'Check the below checkbox to display resized images that link to the original resolution image. Useful for speeding up page load when users are uploading lots of photos from phone cameras etc.'
            ),
            'Plugins.ImgurUpload.ShowImagesBtn' => array(
                'LabelCode' => 'Show \'Add Images\' button on desktop',
                'Control' => 'Checkbox',
                'Default' => C('Plugins.ImgurUpload.ShowImagesBtn', '1'),
                'Description' => 'Check the below checkbox to display the \'Add Images\' button on desktop. If not checked, only mobile/touchscreen users will see the button. Desktop users will only be able to upload via drag\'n\'drop.'
            ),
            'Plugins.ImgurUpload.EnableDragDrop' => array(
                'LabelCode' => 'Allow drag\'n\'drop',
                'Control' => 'Checkbox',
                'Default' => C('Plugins.ImgurUpload.EnableDragDrop', '1'),
                'Description' => 'Check the below checkbox to allow images to be drag\'n\'dropped onto the comment box.'
            )
        ));
        
        $sender->setData('Title', t('ImgurUpload'));
        $cf->renderAll();
    }

	/**
	 * DiscussionController_Render_Before HOOK
	 *
	 * Calls ImgurUpload::PrepareController
	 *
	 * @access public
	 * @param mixed $Sender The hooked controller
	 * @see ImgurUpload::PrepareController
	 * @return void
	 */
	public function DiscussionController_Render_Before($Sender) {
		$this->PrepareController($Sender);
	}

	/**
	 * ConversationController_Render_Before HOOK
	 *
	 * Calls ImgurUpload::PrepareController
	 *
	 * @access public
	 * @param mixed $Sender The hooked controller
	 * @see ImgurUpload::PrepareController
	 * @return void
	 */
	public function MessagesController_Render_Before($Sender) {
		$this->PrepareController($Sender);
	}

	/**
	 * PostController_Render_Before HOOK
	 *
	 * Calls ImgurUpload::PrepareController
	 *
	 * @access public
	 * @param mixed $Sender The hooked controller
	 * @see ImgurUpload::PrepareController
	 * @return void
	 */
	public function PostController_Render_Before($Sender) {
		$this->PrepareController($Sender);
	}

	/**
	 * PrepareController function.
	 *
	 * Adds CSS and JS includes to the header of the discussion or post.
	 *
	 * @access protected
	 * @param mixed $Controller The hooked controller
	 * @return void
	 */
	protected function PrepareController($Controller) {
		$ImgurClientID = C('Plugins.ImgurUpload.ClientID', '');
		$ProcessImageURLs = C('Plugins.ImgurUpload.ProcessImageURLs', '');
		$ResizeImages = C('Plugins.ImgurUpload.ResizeImages', '');
		$ShowImagesBtn = C('Plugins.ImgurUpload.ShowImagesBtn', '');
		$EnableDragDrop = C('Plugins.ImgurUpload.EnableDragDrop', '');
		$ImgurThumbnailSuffix = C('Plugins.ImgurUpload.ImgurThumbnailSuffix', '');

		$Controller->AddDefinition('imguruploadmarkupformat', c('Garden.InputFormatter', 'Html'));
		// This becomes accessible in JS as gdn.definition("imgurclientid");
		$Controller->AddDefinition('imgurclientid', $ImgurClientID);
		$Controller->AddDefinition('processimageurls', $ProcessImageURLs);
		$Controller->AddDefinition('resizeimages', $ResizeImages);
		$Controller->AddDefinition('showimagesbtn', $ShowImagesBtn);
		$Controller->AddDefinition('enabledragdrop', $EnableDragDrop);
		$Controller->AddDefinition('imgurthumbnailsuffix', $ImgurThumbnailSuffix);
		$Controller->AddDefinition('imgmaxheight', $ImgMaxHeight);
		$Controller->AddJsFile('dropzone.min.js', 'plugins/ImgurUpload');
		$Controller->AddJsFile('imgurupload.js', 'plugins/ImgurUpload');
		$Controller->AddCssFile('imgurupload.css', 'plugins/ImgurUpload');
	}
}
