<?php

$PluginInfo['AddToHeader'] = array(
	'Name' => 'Add To Header',
	'Description' => 'Adds predefined items to the header menu in Vanilla.',
	'Version' 	=>	 '1.2',
	'MobileFriendly' => TRUE,
	'RequiredApplications' => array('Vanilla' => '2.1'),
	'Author' 	=>	 "Godot Community Forums",
	'AuthorEmail' => 'TwistedTwigleg@randommomentania.com',
	'AuthorUrl' =>	 'https://GodotForums.org',
	'License' => 'MIT'
);

// Credit: https://open.vanillaforums.com/discussion/37026/adding-custom-navigation-links-to-keystone-on-mobile-resolved

class AddToHeaderPlugin extends Gdn_Plugin {

	public function base_render_before($sender) {
		// The first argument passed to that function is the current controller, the instance calling the method is named "$sender" by default in Vanilla.
		// Most probably you do not want to do anything in the dashboard.
		if (inSection('Dashboard')) {
		   return;
		}
		// Now simply add the menu items you want to see
		// For now, it is just the contact menu item.
		$sender->Menu->Items[] = [
			[
				'Permission' => '',
				'Url' => 'contact/',
				'Text' => 'Contact',
				'Attributes' => ''
			]
		];
	 }

	/**
	 * 1-time enable actions.
	 */
	public function setup() {

	}
}
