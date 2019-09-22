<?php if (!defined('APPLICATION')) exit();
/*
 This file is part of Mediator

 Mediator is free software: you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation, either version 3 of the License, or
 (at your option) any later version.

 Foobar is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Mediator. If not, see <http://www.gnu.org/licenses/>.
*/

$PluginInfo['Mediator'] = array(
	'Name' => 'Mediator',
	'Description' => 'Allows automatic embedding of media by converting media links to embed code. Supports Youtube, Vimeo, Imgur, Pastebin, Soundcloud [Bandcamp], Gyazo, GfyCat, GitHub-gist.',
	'Version' => '0.4.18',
	'Date' => '01 Feb 2012',
	'Author' => 'Seon-Wook Park (extended by the Godot forums team)',
	'AuthorEmail' => 'twistedtwigleg@randommomentania.com',
	'AuthorUrl' => 'https://godotforums.org/',
	'RequiredTheme' => FALSE,
	'RequiredPlugins' => FALSE,
	'RegisterPermissions' => FALSE,
	'SettingsPermission' => FALSE,
	'License' => 'GNU GPL3'
);

class MediatorPlugin implements Gdn_IPlugin {

	public function base_render_before(&$Sender) {
        if (InSection("Dashboard"))
            return;
        
        $Sender->AddJsFile('mediator.js', 'plugins/Mediator');
        $Sender->AddCssFile('style.css', 'plugins/Mediator');
	}

    public function Setup() {
    }
}
