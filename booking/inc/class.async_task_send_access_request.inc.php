<?php
	phpgw::import_class('phpgwapi.datetime');
	phpgw::import_class('booking.async_task');

	class booking_async_task_send_access_request extends booking_async_task
	{

		private $account, $config;

		public function __construct()
		{
			parent::__construct();
			$this->account	 = $GLOBALS['phpgw_info']['user']['account_id'];
			$this->config	 = CreateObject('phpgwapi.config', 'booking')->read();
		}

		public function get_default_times()
		{
			return array('min'	 => '*', 'hour'	 => '*', 'dow'	 => '*', 'day'	 => '*', 'month'	 => '*',
				'year'	 => '*');
		}

		public function run( $options = array() )
		{

			$request_method = !empty($this->config['e_lock_request_method']) ? $this->config['e_lock_request_method'] : 'Stavanger_e_lock.php';

			if (!$request_method)
			{
				throw new LogicException('request_method not chosen');
			}

			$file = PHPGW_SERVER_ROOT . "/booking/inc/custom/default/{$request_method}";

			if (!is_file($file))
			{
				throw new LogicException("request method \"{$request_method}\" not available");
			}

			require_once $file;

			$e_lock_integration = new booking_e_lock_integration();

			$db = & $GLOBALS['phpgw']->db;

			$reservation_types = array
				(
//				'booking',
				'event',
//				'allocation'
			);

			$stages = array(
				0	 => 60 * 60 * 3, // 3 hours : send SMS and email as reminder
				1	 => 60 * 10, // 10 minutes : request access
				2	 => 60 * 5, // 5 minutes : get request status
			);

			$so_resource = CreateObject('booking.soresource');

			//SMS
			$sms_service = CreateObject('sms.sms');

			foreach ($stages as $stage => $time_ahead)
			{
				foreach ($reservation_types as $reservation_type)
				{
					$bo = CreateObject('booking.bo' . $reservation_type);

					/**
					 * Condition: < $_stage
					 * update to $_stage after check
					 */
					$_stage = $stage +1;

					$request_access = $bo->find_request_access($_stage, $time_ahead);

					if (!is_array($request_access) || !isset($request_access['results']))
					{
						continue;
					}

					$db->transaction_begin();

					if (count($request_access['results']) > 0)
					{
						foreach ($request_access['results'] as $reservation)
						{

							$resources = $so_resource->read(array('filters'	 => array('where' => 'bb_resource.id IN(' . implode(', ', $reservation['resources']) . ')'),
								'results'	 => 100));

							foreach ($resources['results'] as $resource)
							{
								if (!$resource['e_locks'])
								{
									continue;
								}

								if ($stage == 0)
								{
									/**
									 * send SMS
									 */
									$sms_text = "Hei {$reservation['contact_name']}\n "
										. "Du har fått tilgang til {$resource['name']} i tidsrommet {$reservation['from_']} - {$reservation['to_']}";

									try
									{
										$sms_res = $sms_service->websend2pv($this->account, $reservation['contact_phone'], $sms_text);
									}
									catch (Exception $ex)
									{
										//implement me
										$this->log('sms_error', $ex->getMessage());
									}

									if (!empty($sms_res[0][0]))
									{
										$comment = 'Melding om tilgang er sendt til ' . $reservation['contact_phone'];
										$bo->add_single_comment($reservation['id'], $comment);
									}

									/**
									 * send email
									 */
									$this->send_mailnotification($reservation['contact_email'], 'Melding om tilgang', nl2br($sms_text));

									$this->log('sms_tekst', $sms_text);
								}
								else if ($stage == 1)
								{
									/**
									 * send request
									 */
									foreach ($resource['e_locks'] as $e_lock)
									{
										$to = $this->round_to_next_hour($reservation['to_']);

										$post_data = array
											(
											'desc'	 => $reservation['contact_name'],
											'email'	 => $reservation['contact_email'],
											'from'	 => date('Y-m-d\TH:i:s.v', phpgwapi_datetime::user_localtime()) . 'Z',
											'mobile' => $reservation['contact_phone'],
											'resid'	 => (int)$e_lock['e_lock_resource_id'],
											'system' => (int)$e_lock['e_lock_system_id'],
									//		'to'	 => date('Y-m-d\TH:i:s.v', strtotime($reservation['to_'])) . 'Z',
											'to'	=> $to->format('Y-m-d\TH:i:s.v') . 'Z',
										);

										$http_code = $e_lock_integration->resources_create($post_data);

										//							_debug_array($http_code);

										$log_data = _debug_array($post_data, false);
										$this->log('post_data', $log_data);
										$this->log('http_code', $http_code);
									}
								}
								else if ($stage == 2)
								{
									/**
									 * Get status
									 */
									foreach ($resource['e_locks'] as $e_lock)
									{
										$get_data = array
										(
											'resid'		 => (int)$e_lock['e_lock_resource_id'],
											'reserved'	 => 1,
											'system'	 => (int)$e_lock['e_lock_system_id'],
										);

										$status_arr = $e_lock_integration->get_status($get_data);

										$log_data = _debug_array($get_data, false);
										$this->log('get_data', $log_data);
										$log_data = _debug_array($status_arr, false);
										$this->log('status_arr', $log_data);

										/**
										 * look for contact_phone, and send email/sms with key
										 */
										$found_reservation = false;
										foreach ($status_arr as $status)
										{
											if ($status['mobile'] == $reservation['contact_phone'])
											{
												$reservation_from	 = strtotime($reservation['from_']);// - phpgwapi_datetime::user_timezone();
												$reservation_to		 = strtotime($reservation['to_']);// - phpgwapi_datetime::user_timezone();
												$status_from		 = strtotime($status['from']);
												$status_to			 = strtotime($status['to']);
												$e_lock_name		 = $e_lock['e_lock_name'] ? $e_lock['e_lock_name'] : 'låsen';

												if ($e_lock['access_code_format'] && preg_match('/__key__/i', $e_lock['access_code_format']))
												{
													$e_loc_key = str_replace('__key__', $status['key'], $e_lock['access_code_format']);
												}
												else
												{
													$e_loc_key = $status['key'];
												}

												if ($reservation_to >= $status_from && $reservation_to <= $status_to)
												{
													$found_reservation	 = true;
													/**
													 * send SMS
													 */
													$sms_text			 = "Hei {$reservation['contact_name']}\n "
														. "Du har fått tilgang til {$resource['name']} i tidsrommet {$reservation['from_']} - {$reservation['to_']}.\n "
														. "Koden for {$e_lock_name} er: {$e_loc_key}";

													try
													{
														$sms_res = $sms_service->websend2pv($this->account, $reservation['contact_phone'], $sms_text);
													}
													catch (Exception $ex)
													{
														$this->log('sms_error', $ex->getMessage());
													}

													if (!empty($sms_res[0][0]))
													{
														$comment = 'Melding om tilgang og kode er sendt til ' . $reservation['contact_phone'];
														$bo->add_single_comment($reservation['id'], $comment);
													}

													/**
													 * send email
													 */
													if ($this->send_mailnotification($reservation['contact_email'], 'Melding om tilgang', nl2br($sms_text)))
													{
														$comment = "Melding om tilgang og kode for {$e_lock['e_lock_system_id']}::{$e_lock['e_lock_resource_id']} er sendt til {$reservation['contact_email']}";
														$bo->add_single_comment($reservation['id'], $comment);
													}

													$this->log('sms_tekst', $sms_text);

													break;
												}
											}
										}

										if (!$found_reservation)
										{
											$error_msg	 = "Fann ikkje reservasjonen i adgangskontrollen";
											$sms_res	 = $sms_service->websend2pv($this->account, $reservation['contact_phone'], $error_msg);
											$this->send_mailnotification($reservation['contact_email'], 'Melding om tilgang', nl2br($error_msg));
										}
									}
								}
							}
						}

						$bo->complete_request_access($request_access['results'], $_stage);
					}

					$db->transaction_commit();
				}
			}
		}

		private function send_mailnotification( $receiver, $subject, $body )
		{
			$rcpt	 = false;
			$send	 = CreateObject('phpgwapi.send');

			$from = isset($this->config['email_sender']) && $this->config['email_sender'] ? $this->config['email_sender'] : "noreply<noreply@{$GLOBALS['phpgw_info']['server']['hostname']}>";

			if (strlen(trim($body)) == 0)
			{
				return false;
			}

			if (strlen($receiver) > 0)
			{
				try
				{
					$rcpt = $send->msg('email', $receiver, $subject, $body, '', '', '', $from, 'AktivKommune', 'html');
				}
				catch (Exception $e)
				{
					// TODO: Inform user if something goes wrong
				}
			}
			return $rcpt;
		}

		private function log( $what, $value = '' )
		{
			$GLOBALS['phpgw']->log->message(array(
				'text'	 => "what: %1, <br/>value: %2",
				'p1'	 => $what,
				'p2'	 => $value ? $value : ' ',
				'line'	 => __LINE__,
				'file'	 => __FILE__
			));
			$GLOBALS['phpgw']->log->commit();
		}

		function round_to_next_hour( $dateString )
		{
			$date	 = new DateTime($dateString);
			$minutes = $date->format('i');
			if ($minutes > 0)
			{
				$date->modify("+1 hour");
				$date->modify('-' . $minutes . ' minutes');
			}
			return $date;
		}
	}