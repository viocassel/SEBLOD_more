<?php
/**
* @version 			SEBLOD 3.x More
* @package			SEBLOD (App Builder & CCK) // SEBLOD nano (Form Builder)
* @url				https://www.seblod.com
* @editor			Octopoos - www.octopoos.com
* @copyright		Copyright (C) 2009 - 2018 SEBLOD. All Rights Reserved.
* @license 			GNU General Public License version 2 or later; see _LICENSE.php
**/

defined( '_JEXEC' ) or die;

require_once JPATH_SITE.'/plugins/cck_storage_location/joomla_message/joomla_message.php';

// Class
class plgCCK_Storage_LocationJoomla_Message_Integration extends plgCCK_Storage_LocationJoomla_Message
{
	// onCCK_Storage_LocationAfterDispatch
	public static function onCCK_Storage_LocationAfterDispatch( &$data, $uri = array() )
	{
		$return	=	'&return_o='.substr( $uri['option'], 4 );

		if ( !$uri['layout'] ) {
			$do	=	$data['options']->get( 'add', 1 );
			$data['options']->set( 'add_alt_link', 'index.php?option=com_messages&view=message&layout=edit&cck=1' );
			if ( $do == 1 ) {
				JCckDevIntegration::addModalBox( $data['options']->get( 'add_layout', 'icon' ), $return, $data['options'] );
			} elseif ( $do == 2 ) {
				JCckDevIntegration::addDropdown( 'form', $return, $data['options'] );
			}
		} elseif ( $uri['layout'] == 'edit' && !$uri['id'] ) {
			if ( $data['options']->get( 'add_redirect', 1 ) ) {
				JCckDevIntegration::redirect( $data['options']->get( 'default_type' ), $return );
			}
		}
	}
	
	// onCCK_Storage_LocationAfterRender
	public static function onCCK_Storage_LocationAfterRender( &$buffer, &$data, $uri = array() )
	{
		if ( $uri['layout'] ) {
			return;
		}
		
		$data['doIntegration']	=	false; // integration for now!
		$data['search']			=	'#<a href="(.*)index.php\?option=com_messages&amp;view=message&amp;message_id=([0-9]*)"#';
	}
}
?>