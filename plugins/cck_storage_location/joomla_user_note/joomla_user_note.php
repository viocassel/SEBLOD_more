<?php
/**
* @version 			SEBLOD 3.x More
* @package			SEBLOD (App Builder & CCK) // SEBLOD nano (Form Builder)
* @url				http://www.seblod.com
* @editor			Octopoos - www.octopoos.com
* @copyright		Copyright (C) 2009 - 2016 SEBLOD. All Rights Reserved.
* @license 			GNU General Public License version 2 or later; see _LICENSE.php
**/

defined( '_JEXEC' ) or die;

JLoader::register( 'UsersTableNote', JPATH_ADMINISTRATOR.'/components/com_users/tables/note.php' );

// Plugin
class plgCCK_Storage_LocationJoomla_User_Note extends JCckPluginLocation
{
	protected static $type			=	'joomla_user_note';
	protected static $table			=	'#__user_notes';
	protected static $table_object	=	array( 'Note', 'UsersTable' );
	protected static $key			=	'id';
	
	protected static $access		=	'';
	protected static $author		=	'created_user_id';
	protected static $author_object	=	'joomla_user';
	protected static $created_at	=	'created_time';
	protected static $custom		=	'body';
	protected static $modified_at	=	'modified_time';
	protected static $parent		=	'catid';
	protected static $parent_object	=	'joomla_category';
	protected static $status		=	'state';
	protected static $to_route		=	'';
	
	protected static $context		=	'com_users.note';
	protected static $contexts		=	array();
	protected static $error			=	false;
	protected static $ordering		=	array( 'alpha'=>'subject ASC', 'newest'=>'created_time DESC', 'oldest'=>'created_time ASC' );
	protected static $ordering2		=	array();
	protected static $pk			=	0;
	protected static $routes		=	array();
	protected static $sef			=	array();
	
	// -------- -------- -------- -------- -------- -------- -------- -------- // Construct
	
	// onCCK_Storage_LocationConstruct
	public function onCCK_Storage_LocationConstruct( $type, &$data = array() )
	{
		if ( self::$type != $type ) {
			return;
		}
		if ( empty( $data['storage_table'] ) ) {
			$data['storage_table']	=	self::$table;
		}
	}
	
	// -------- -------- -------- -------- -------- -------- -------- -------- // Prepare
	
	// onCCK_Storage_LocationPrepareContent
	public function onCCK_Storage_LocationPrepareContent( &$field, &$storage, $pk = 0, &$config = array(), &$row = null )
	{
		if ( self::$type != $field->storage_location ) {
			return;
		}
		
		// Init
		$table	=	$field->storage_table;
		
		// Set
		if ( $table == self::$table ) {
			$storage			=	self::_getTable( $pk );
			$config['author']	=	$storage->{self::$author};
		} else {
			$storage			=	parent::g_onCCK_Storage_LocationPrepareContent( $table, $pk );
			if ( ! isset( $config['storages'][self::$table] ) ) {
				$config['storages'][self::$table]	=	self::_getTable( $pk );
				$config['author']					=	$config['storages'][self::$table]->{self::$author};
			}
		}
	}
	
	// onCCK_Storage_LocationPrepareDelete
	public function onCCK_Storage_LocationPrepareDelete( &$field, &$storage, $pk = 0, &$config = array() )
	{
		if ( self::$type != $field->storage_location ) {
			return;
		}
		
		// Init
		$table	=	$field->storage_table;
		
		// Set
		if ( $table == self::$table ) {
			$storage	=	self::_getTable( $pk );
		} else {
			$storage	=	parent::g_onCCK_Storage_LocationPrepareForm( $table, $pk );
		}
	}
	
	// onCCK_Storage_LocationPrepareForm
	public function onCCK_Storage_LocationPrepareForm( &$field, &$storage, $pk = 0, &$config = array() )
	{
		if ( self::$type != $field->storage_location ) {
			return;
		}
		
		// Init
		$table	=	$field->storage_table;
		
		// Set
		if ( $table == self::$table ) {
			$storage			=	self::_getTable( $pk );
			$config['author']	=	$storage->{self::$author};
			$config['custom']	=	( ! $config['custom'] ) ? self::$custom : $config['custom'];
		} else {
			$storage			=	parent::g_onCCK_Storage_LocationPrepareForm( $table, $pk );
		}
	}
	
	// onCCK_Storage_LocationPrepareItems
	public function onCCK_Storage_LocationPrepareItems( &$field, &$storages, $pks, &$config = array(), $load = false )
	{
		if ( self::$type != $field->storage_location ) {
			return;
		}
		
		// Init
		$table	=	$field->storage_table;
		
		// Prepare
		if ( $load ) {
			if ( $table == self::$table ) {
				$storages[$table]	=	JCckDatabase::loadObjectList( 'SELECT * FROM '.$table.' WHERE '.self::$key.' IN ('.$config['pks'].')', self::$key );
			} else {
				$storages[$table]	=	JCckDatabase::loadObjectList( 'SELECT * FROM '.$table.' WHERE id IN ('.$config['pks'].')', 'id' );
				if ( !isset( $storages[self::$table] ) ) {
					$storages['_']			=	self::$table;
					$storages[self::$table]	=	JCckDatabase::loadObjectList( 'SELECT * FROM '.self::$table.' WHERE '.self::$key.' IN ('.$config['pks'].')', self::$key );
				}
			}
		}
		$config['author']	=	$storages[self::$table][$config['pk']]->{self::$author};
	}
	
	// onCCK_Storage_LocationPrepareList
	public static function onCCK_Storage_LocationPrepareList( &$params )
	{
	}
	
	// onCCK_Storage_LocationPrepareOrder
	public function onCCK_Storage_LocationPrepareOrder( $type, &$order, &$tables, &$config = array() )
	{
		if ( self::$type != $type ) {
			return;
		}
		
		$order	=	( isset( self::$ordering[$order] ) ) ? $tables[self::$table]['_'] .'.'. self::$ordering[$order] : '';
	}
	
	// onCCK_Storage_LocationPrepareSearch
	public function onCCK_Storage_LocationPrepareSearch( $type, &$query, &$tables, &$t, &$config = array(), &$inherit = array(), $user )
	{
		if ( self::$type != $type ) {
			return;
		}
		
		// Prepare
		if ( ! isset( $tables[self::$table] ) ) {
			$tables[self::$table]	=	array( '_'=>'t'.$t++,
											   'fields'=>array(),
											   'join'=>1,
											   'location'=>self::$type
										);
		}
		
		// Set
		$t_pk	=	$tables[self::$table]['_'];
		if ( ! isset( $tables[self::$table]['fields'][self::$status] ) ) {
			$query->where( $t_pk.'.'.self::$status.' = 1' );
		}
	}
	
	// -------- -------- -------- -------- -------- -------- -------- -------- // Store
	
	// onCCK_Storage_LocationDelete
	public static function onCCK_Storage_LocationDelete( $pk, &$config = array() )
	{
		$app		=	JFactory::getApplication();
		$dispatcher	=	JDispatcher::getInstance();
		$table		=	self::_getTable( $pk );	
		
		if ( !$table ) {
			return false;
		}
		
		// Check
		$user 			=	JCck::getUser();
		$canDelete		=	$user->authorise( 'core.delete', 'com_cck.form.'.$config['type_id'] );
		$canDeleteOwn	=	$user->authorise( 'core.delete.own', 'com_cck.form.'.$config['type_id'] );
		if ( ( !$canDelete && !$canDeleteOwn ) ||
			 ( !$canDelete && $canDeleteOwn && $config['author'] != $user->get( 'id' ) ) ||
			 ( $canDelete && !$canDeleteOwn && $config['author'] == $user->get( 'id' ) ) ) {
			$app->enqueueMessage( JText::_( 'COM_CCK_ERROR_DELETE_NOT_PERMITTED' ), 'error' );
			return;
		}
		
		// Process
		$result	=	$dispatcher->trigger( 'onContentBeforeDelete', array( self::$context, $table ) );
		if ( in_array( false, $result, true ) ) {
			return false;
		}
		if ( !$table->delete( $pk ) ) {
			return false;
		}
		$dispatcher->trigger( 'onContentAfterDelete', array( self::$context, $table ) );
		
		return true;
	}
	
	// onCCK_Storage_LocationStore
	public function onCCK_Storage_LocationStore( $type, $data, &$config = array(), $pk = 0 )
	{
		if ( self::$type != $type ) {
			return;
		}
		
		if ( ! @$config['storages'][self::$table]['_']->pk ) {
			self::_core( $config['storages'][self::$table], $config, $pk );
			$config['storages'][self::$table]['_']->pk	=	self::$pk;
		}
		if ( $data['_']->table != self::$table ) {
			parent::g_onCCK_Storage_LocationStore( $data, self::$table, self::$pk, $config );
		}
		
		return self::$pk;
	}
	
	// -------- -------- -------- -------- -------- -------- -------- -------- // Protected
	
	// _core
	protected function _core( $data, &$config = array(), $pk = 0 )
	{
		if ( ! $config['id'] ) {
			$isNew	=	true;
			$config['id']	=	parent::g_onCCK_Storage_LocationPrepareStore();
		} else {
			$isNew	=	false;
		}
		
		// Init
		$table	=	self::_getTable( $pk );
		$isNew	=	( $pk > 0 ) ? false : true;
		self::_initTable( $table, $data, $config );
		
		// Check Error
		if ( self::$error === true ) {
			return false;
		}
		
		// Prepare
		if ( is_array( $data ) ) {
			$table->bind( $data );
		}
		$table->check();
		self::_completeTable( $table, $data, $config );
		
		// Store
		$dispatcher	=	JDispatcher::getInstance();
		if ( $isNew === true && parent::g_isMax( $table->{self::$author}, $table->{self::$parent}, $config ) ) {
			return;
		}
		if ( !$table->store() ) {
			JFactory::getApplication()->enqueueMessage( $table->getError(), 'error' );
			if ( $isNew ) {
				parent::g_onCCK_Storage_LocationRollback( $config['id'] );
			}
			return false;
		}
		
		// Checkin
		parent::g_checkIn( $table );
		
		self::$pk			=	$table->{self::$key};
		if ( !$config['pk'] ) {
			$config['pk']	=	self::$pk;
		}
		
		$config['author']	=	$table->{self::$author};
		$config['parent']	=	$table->{self::$parent};
		
		parent::g_onCCK_Storage_LocationStore( $data, self::$table, self::$pk, $config );
	}
	
	// _getTable
	protected static function _getTable( $pk = 0 )
	{
		$table	=	JTable::getInstance( 'Note', 'UsersTable' );
		
		if ( $pk > 0 ) {
			$table->load( $pk );
		}
		
		return $table;
	}
	
	// _initTable
	protected function _initTable( &$table, &$data, &$config, $force = false )
	{
		$user	=	JFactory::getUser();
		
		if ( ! $table->{self::$key} ) {
			parent::g_initTable( $table, ( ( isset( $config['params'] ) ) ? $config['params'] : $this->params->toArray() ), $force );
			$table->{self::$author}	=	$table->{self::$author} ? $table->{self::$author} : JCck::getConfig_Param( 'integration_user_default_author', 42 );
			if ( ( $user->get( 'id' ) > 0 && @$user->guest != 1 ) && !isset( $data[self::$author] ) ) {
				$data[self::$author]	=	$user->get( 'id' );
			}
		}
		$table->{self::$custom}	=	'';
	}
	
	// _completeTable
	protected function _completeTable( &$table, &$data, &$config )
	{
		if ( ! $table->{self::$key} ) {
			$table->modified_user_id	=	0;
		}
		
		parent::g_completeTable( $table, self::$custom, $config );
	}
	
	// -------- -------- -------- -------- -------- -------- -------- -------- // SEF
	
	// buildRoute
	public static function buildRoute( &$query, &$segments, $config, $menuItem = NULL )
	{
	}
	
	// getRoute
	public static function getRoute( $item, $sef, $itemId, $config = array() )
	{
		$route		=	'';
		
		return JRoute::_( $route );
	}
	
	// getRouteByStorage
	public static function getRouteByStorage( &$storage, $sef, $itemId, $config = array() )
	{
		if ( isset( $storage[self::$table]->_route ) ) {
			return JRoute::_( $storage[self::$table]->_route );
		}
		
		if ( $sef ) {
			$storage[self::$table]->_route	=	'';
		} else {
			$storage[self::$table]->_route	=	'';
		}
		
		return JRoute::_( $storage[self::$table]->_route );
	}
	
	// parseRoute
	public static function parseRoute( &$vars, $segments, $n, $config )
	{
	}
	
	// setRoutes
	public static function setRoutes( $items, $sef, $itemId )
	{
		if ( count( $items ) ) {
			foreach ( $items as $item ) {
				$item->link	=	self::getRoute( $item, $sef, $itemId );
			}
		}
	}
	
	// -------- -------- -------- -------- -------- -------- -------- -------- // Stuff
	
	// checkIn
	public static function checkIn( $pk = 0 )
	{
		if ( !$pk ) {
			return false;
		}
		
		$table	=	self::_getTable( $pk );
		
		return parent::g_checkIn( $table );
	}
	
	// getId
	public static function getId( $config )
	{
		return JCckDatabase::loadResult( 'SELECT id FROM #__cck_core WHERE storage_location="'.self::$type.'" AND pk='.(int)$config['pk'] );
	}
	
	// getStaticProperties
	public static function getStaticProperties( $properties )
	{
		static $autorized	=	array(
									'access'=>'',
									'author'=>'',
									'author_object'=>'',
									'created_at'=>'',
									'context'=>'',
									'contexts'=>'',
									'custom'=>'',
									'key'=>'',
									'modified_at'=>'',
									'ordering'=>'',
									'parent'=>'',
									'parent_object'=>'',
									/*'routes'=>'',*/
									'status'=>'',
									'table'=>'',
									'table_object'=>'',
									'to_route'=>''
								);
		
		if ( count( $properties ) ) {
			foreach ( $properties as $i=>$p ) {
				if ( isset( $autorized[$p] ) ) {
					$properties[$p]	=	self::${$p};
				}
				unset( $properties[$i] );
			}
		}
		
		return $properties;
	}
}
?>