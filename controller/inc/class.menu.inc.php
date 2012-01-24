<?php
	/**
	* phpGroupWare - controller: a part of a Facilities Management System.
	*
	* @author Erink Holm-Larsen <erik.holm-larsen@bouvet.no>
	* @author Torstein Vadla <torstein.vadla@bouvet.no>
	* @copyright Copyright (C) 2011,2012 Free Software Foundation, Inc. http://www.fsf.org/
	* This file is part of phpGroupWare.
	*
	* phpGroupWare is free software; you can redistribute it and/or modify
	* it under the terms of the GNU General Public License as published by
	* the Free Software Foundation; either version 2 of the License, or
	* (at your option) any later version.
	*
	* phpGroupWare is distributed in the hope that it will be useful,
	* but WITHOUT ANY WARRANTY; without even the implied warranty of
	* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	* GNU General Public License for more details.
	*
	* You should have received a copy of the GNU General Public License
	* along with phpGroupWare; if not, write to the Free Software
	* Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
	*
	* @license http://www.gnu.org/licenses/gpl.html GNU General Public License
	* @internal Development of this application was funded by http://www.bergen.kommune.no/
	* @package property
	* @subpackage controller
 	* @version $Id$
	*/

	class controller_menu
	{
		function get_menu()
		{
			$incoming_app = $GLOBALS['phpgw_info']['flags']['currentapp'];
			$GLOBALS['phpgw_info']['flags']['currentapp'] = 'controller';
			$menus = array();

			$menus['navbar'] = array
			(
				'controller' => array
				(
					'text'	=> lang('Controller'),
					'url'	=> $GLOBALS['phpgw']->link('/index.php', array('menuaction'=> 'controller.uicontrol.view_control_details') ),
					'image'	=> array('property', 'location'),
					'order'	=> 10,
					'group'	=> 'office'
				)
			);

			if($GLOBALS['phpgw']->acl->check('.usertype.superuser',PHPGW_ACL_ADD,'controller'))
			{
				$menus['navigation'] =  array
				(
					'control' => array
					(
						'text'	=> lang('Control'),
						'url'	=> $GLOBALS['phpgw']->link('/index.php', array('menuaction'=> 'controller.uicontrol.control_list') ),
						'image'	=> array('property', 'location_1'),
						'children' => array(
											'location_for_check_list' => array
											(
												'text'	=> lang('Location'),
												'url'	=> $GLOBALS['phpgw']->link('/index.php', array('menuaction'=> 'controller.uicontrol_location.index') ),
												'image'	=> array('property', 'location_1')
											),
											'component_for_check_list' => array
											(
												'text'	=> lang('component'),
												'url'	=> $GLOBALS['phpgw']->link('/index.php', array('menuaction'=> 'controller.uicheck_list_for_component.index') ),
												'image'	=> array('property', 'entity_1')
											)
										)
					),
					'location_for_check_list' => array
					(
						'text'	=> lang('location_connections'),
						'url'	=> $GLOBALS['phpgw']->link('/index.php', array('menuaction'=> 'controller.uicontrol_location.index') ),
						'image'	=> array('property', 'location_1')
					),
					'control_item' => array
					(
						'text'	=> lang('Control_item'),
						'url'	=> $GLOBALS['phpgw']->link('/index.php', array('menuaction'=> 'controller.uicontrol_item.index') ),
						'image'	=> array('property', 'location_1')
					),
					'control_group' => array
					(
						'text'	=> lang('Control_group'),
						'url'	=> $GLOBALS['phpgw']->link('/index.php', array('menuaction'=> 'controller.uicontrol_group.index') ),
						'image'	=> array('property', 'location_1')
					),
					'procedure' => array
					(
						'text'	=> lang('Procedure'),
						'url'	=> $GLOBALS['phpgw']->link('/index.php', array('menuaction'=> 'controller.uiprocedure.index') ),
						'image'	=> array('property', 'location_1'),
					),
					'check_list' => array
					(
						'text'	=> lang('Check_list'),
						'url'	=> $GLOBALS['phpgw']->link('/index.php', array('menuaction'=> 'controller.uicheck_list_for_location.index') ),
						'image'	=> array('property', 'location_1'),
					),
					'calendar_overview' => array
					(
						'text'	=> lang('Calendar_overview'),
						'url'	=> $GLOBALS['phpgw']->link('/index.php', array('menuaction'=> 'controller.uicalendar.view_calendar_for_year') ),
						'image'	=> array('property', 'location_1'),
					)
				);
			}
			else
			{
				$menus['navigation'] =  array
				(
					'check_list' => array
					(
						'text'	=> lang('Check_list'),
						'url'	=> $GLOBALS['phpgw']->link('/index.php', array('menuaction'=> 'controller.uicheck_list_for_location.index') ),
						'image'	=> array('property', 'location_1'),
					),
					'location_check_list' => array
					(
						'text'	=> lang('Check_list_location'),
						'url'	=> $GLOBALS['phpgw']->link('/index.php', array('menuaction'=> 'controller.uilocation_check_list.view_check_lists_for_location') ),
						'image'	=> array('property', 'location_1'),
					),
				);
			}

			if ( $GLOBALS['phpgw']->acl->check('run', phpgwapi_acl::READ, 'admin')
				|| $GLOBALS['phpgw']->acl->check('admin', phpgwapi_acl::ADD, 'controller'))
			{
				$menus['admin'] = array
				(
					'index'	=> array
					(
						'text'	=> lang('Configuration'),
						'url'	=> $GLOBALS['phpgw']->link('/index.php', array('menuaction' => 'admin.uiconfig.index', 'appname' => 'controller') )
					),
					'acl'	=> array
					(
						'text'	=> lang('Configure Access Permissions'),
						'url'	=> $GLOBALS['phpgw']->link('/index.php', array('menuaction' => 'preferences.uiadmin_acl.list_acl', 'acl_app' => 'controller') )
					),
					'control_cats'	=> array
					(
						'text'	=> lang('Control area'),
						'url'	=> $GLOBALS['phpgw']->link('/index.php', array('menuaction' => 'admin.uicategories.index', 'appname' => 'controller', 'location' => '.control', 'global_cats' => 'true', 'menu_selection' => 'admin::controller::control_cats') )
					),
					'responsibility_role'	=> array
					(
						'text'	=> lang('responsibility role'),
						'url'	=> $GLOBALS['phpgw']->link('/index.php', array('menuaction' => 'property.uigeneric.index', 'type' => 'responsibility_role', 'appname' => 'controller') )
					),
					'role_at_location'	=> array
					(
						'url'	=>	$GLOBALS['phpgw']->link('/index.php',array('menuaction'=> 'property.uilocation.responsiblility_role', 'menu_selection' => 'admin::controller::role_at_location') ),
						'text'	=>	lang('role at location'),
						'image'	=> array('property', 'responsibility_role')
					)
				);
			}

			if ( isset($GLOBALS['phpgw_info']['user']['apps']['preferences']) )
			{
				$menus['preferences'] = array
				(
					// in case of userprefs - need a hook for 'settings'
/*
					array
					(
						'text'	=> $GLOBALS['phpgw']->translation->translate('Preferences', array(), true),
						'url'	=> $GLOBALS['phpgw']->link('/preferences/preferences.php', array('appname' => 'controller', 'type'=> 'user') )
					),
*/
					array
					(
						'text'	=> $GLOBALS['phpgw']->translation->translate('Grant Access', array(), true),
						'url'	=> $GLOBALS['phpgw']->link('/index.php',array('menuaction'=> 'preferences.uiadmin_acl.aclprefs', 'acl_app'=> 'controller') )
					)
				);
/*
				$menus['toolbar'][] = array
				(
					'text'	=> $GLOBALS['phpgw']->translation->translate('Preferences', array(), true),
					'url'	=> $GLOBALS['phpgw']->link('/preferences/preferences.php', array('appname'	=> 'controller')),
					'image'	=> array('hrm', 'preferences')
				);
*/
			}

			//Nothing...
			//$menus['folders'] = phpgwapi_menu::get_categories('bergen');

			$GLOBALS['phpgw_info']['flags']['currentapp'] = $incoming_app;

			return $menus;
		}
	}
