<?php
	phpgw::import_class('booking.socommon');
	
	class booking_soapplication extends booking_socommon
	{
		function __construct()
		{
			parent::__construct('bb_application', 
				array(
					'id'		=> array('type' => 'int'),
                    'id_string' => array('type' => 'string', 'required' => false, 'default' => '0', 'query' => true),
					'active'	=> array('type' => 'int'),
					'display_in_dashboard' => array('type' => 'int'),
					'type'		=> array('type' => 'string'),
					'status'	=> array('type' => 'string', 'required' => true),
					'secret'	=> array('type' => 'string', 'required' => true),
					'created'	=> array('type' => 'timestamp'),
					'modified'	=> array('type' => 'timestamp'),
					'building_name' => array('type' => 'string', 'required'=> true, 'query' => true),
					'frontend_modified'	=> array('type' => 'timestamp'),
					'owner_id'	=> array('type' => 'int', 'required' => true),
					'case_officer_id'	=> array('type' => 'int', 'required' => false),
					'activity_id'	=> array('type' => 'int', 'required' => true),
					'status'	=> array('type' => 'string', 'required' => true),
					'customer_identifier_type' 		=> array('type' => 'string', 'required' => true),
					'customer_ssn' 						=> array('type' => 'string', 'query' => true, 'sf_validator' => createObject('booking.sfValidatorNorwegianSSN', array('full_required'=>false)), 'required' => false),
					'customer_organization_number' 	=> array('type' => 'string', 'query' => true, 'sf_validator' => createObject('booking.sfValidatorNorwegianOrganizationNumber', array(), array('invalid' => '%field% is invalid'))),
					'owner_name'	=> array('type' => 'string', 'query' => true,
						  'join' 		=> array(
							'table' 	=> 'phpgw_accounts',
							'fkey' 		=> 'owner_id',
							'key' 		=> 'account_id',
							'column' 	=> 'account_lid'
					)),
					'activity_name'	=> array('type' => 'string',
						  'join' 		=> array(
							'table' 	=> 'bb_activity',
							'fkey' 		=> 'activity_id',
							'key' 		=> 'id',
							'column' 	=> 'name'
					)),
					'description'	=> array('type' => 'string', 'query' => true, 'required' => true),
					'contact_name'	=> array('type' => 'string', 'query' => true, 'required'=> true),
					'contact_email'	=> array('type' => 'string', 'required'=> true, 'sf_validator' => createObject('booking.sfValidatorEmail', array(), array('invalid' => '%field% is invalid'))),
					'contact_phone'	=> array('type' => 'string'),
					'case_officer_name'	=> array('type' => 'string', 'query' => true,
						'join' => array(
							'table' => 'phpgw_accounts',
							'fkey' => 'case_officer_id',
							'key' => 'account_id',
							'column' => 'account_lid'
					)),
					'audience' => array('type' => 'int', 'required' => true,
						  'manytomany' => array(
							'table' => 'bb_application_targetaudience',
							'key' => 'application_id',
							'column' => 'targetaudience_id'
					)),
					'agegroups' => array('type' => 'int', 'required' => true,
						  'manytomany' => array(
							'table' => 'bb_application_agegroup',
							'key' => 'application_id',
							'column' => array('agegroup_id' => array('type' => 'int', 'required' => true), 'male' => array('type' => 'int', 'required' => true), 'female' => array('type' => 'int', 'required' => true)),
					)),
					'dates' => array('type' => 'timestamp', 'required' => true,
						  'manytomany' => array(
							'table' => 'bb_application_date',
							'key' => 'application_id',
							'column' => array('from_', 'to_', 'id')
					)),
					'comments' => array('type' => 'string',
						  'manytomany' => array(
							'table' => 'bb_application_comment',
							'key' => 'application_id',
							'column' => array('time', 'author', 'comment', 'type')
					)),
					'resources' => array('type' => 'int', 'required' => true,
						  'manytomany' => array(
							'table' => 'bb_application_resource',
							'key' => 'application_id',
							'column' => 'resource_id'
					)),
				)
			);
		}

		protected function doValidate($entity, booking_errorstack $errors)
		{
			$event_id = $entity['id'] ? $entity['id'] : -1;
			// Make sure to_ > from_
			foreach($entity['dates'] as $date)
			{
				$from_ = new DateTime($date['from_']);
				$to_ = new DateTime($date['to_']);
				$start = $from_->format('Y-m-d H:i');
				$end = $to_->format('Y-m-d H:i');
				if($from_ > $to_ || $from_ == $to_)
				{
					$errors['from_'] = lang('Invalid from date');
				}
			}
            if(strlen($entity['contact_name']) > 50) {
                $errors['contact_name'] = lang('Contact information name is to long. max 50 characters');
            }
		}

		function get_building_info($id)
		{
			$this->db->limit_query("SELECT bb_building.id, bb_building.name FROM bb_building, bb_resource, bb_application_resource WHERE bb_building.id=bb_resource.building_id AND bb_resource.id=bb_application_resource.resource_id AND bb_application_resource.application_id=" . intval($id), 0, __LINE__, __FILE__, 1);
			if(!$this->db->next_record())
			{
				return False;
			}
			return array('id' => $this->db->f('id', false),
						 'name' => $this->db->f('name', false));
		}

		function get_tilsyn_email($id)
		{
			$this->db->limit_query("SELECT tilsyn_email FROM bb_building where id=" . intval($id), 0, __LINE__, __FILE__, 1);
			if(!$this->db->next_record())
			{
				return False;
			}
			return $this->db->f('email', false);
		}

		function get_resource_name($id)
		{
			$list = implode(",",$id);
			$results = array();		
			$this->db->query("SELECT name FROM bb_resource where id IN ($list)",__LINE__, __FILE__);
			while ($this->db->next_record())
			{
				$results[] = $this->db->f('name', false);
			}
			return $results;

		}

		function get_building($id)
		{
			$this->db->limit_query("SELECT name FROM bb_building where id=" . intval($id), 0, __LINE__, __FILE__, 1);
			if(!$this->db->next_record())
			{
				return False;
			}
			return $this->db->f('name', false);
		}

		function get_buildings()
		{
            $results = array();
			$results[] = array('id' =>  0,'name' => lang('Not selected'));
			$this->db->query("SELECT id, name FROM bb_building WHERE active != 0 ORDER BY name ASC", __LINE__, __FILE__);
			while ($this->db->next_record())
			{
				$results[] = array('id' => $this->db->f('id', false),
						           'name' => $this->db->f('name', false));
			}
			return $results;
		}

        function get_activities_main_level()
        {
		    $results = array();
			$results[]  = array('id' =>0,'name' => lang('Not selected'));
			$this->db->query("SELECT id,name FROM bb_activity WHERE parent_id is NULL", __LINE__, __FILE__);
			while ($this->db->next_record())
			{
				$results[] = array('id' => $this->db->f('id', false), 'name' => $this->db->f('name', false));
			}
			return $results;

        }
        function get_activities($id)
        {
			$results = array();
			$this->db->query("select id from bb_activity where id = ($id) or  parent_id = ($id) or parent_id in (select id from bb_activity where parent_id = ($id))", __LINE__, __FILE__);
			while ($this->db->next_record())
			{
				$results[] = $this->_unmarshal($this->db->f('id', false), 'int');
			}
			return $results;

        }

		public function update_id_string() {
			$table_name = $this->table_name;
			$db = $this->db;
			$sql = "UPDATE $table_name SET id_string = cast(id AS varchar)";
			$db->query($sql, __LINE__, __FILE__);
		}
             


		
		/**
		 * Check if a given timespan is available for bookings or allocations
		 *
		 * @param resources 
		 * @param timespan start
		 * @param timespan end
		 *
		 * @return boolean
		 */
		function check_timespan_availability($resources, $from_, $to_)
		{
			$rids = join(',', array_map("intval", $resources));
			$nrids = count($resources);
			$this->db->query("SELECT id FROM bb_season 
			                  WHERE id IN (SELECT season_id 
							               FROM bb_season_resource 
							               WHERE resource_id IN ($rids,-1) 
							               GROUP BY season_id 
							               HAVING count(season_id)=$nrids)", __LINE__, __FILE__);
			while ($this->db->next_record())
			{
				$season_id = $this->_unmarshal($this->db->f('id', false), 'int');
				if (CreateObject('booking.soseason')->timespan_within_season($season_id, new DateTime($from_), new DateTime($to_)))
				{
					return true;
				}
			}
			return false;
		}
	}

	class booking_soapplication_association extends booking_socommon
	{
		function __construct()
		{
			parent::__construct('bb_application_association', 
				array(
					'id'					=> array('type' => 'int'),
					'application_id'		=> array('type' => 'int'),
					'type'	=> array('type' => 'string', 'required' => true),
					'from_'	=> array('type' => 'timestamp'),
					'to_'	=> array('type' => 'timestamp')));
		}
	}
