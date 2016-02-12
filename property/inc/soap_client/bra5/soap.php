<?php
	/**
	 * phpGroupWare
	 *
	 * @author Sigurd Nes <sigurdne@online.no>
	 * @copyright Copyright (C) 2012 Free Software Foundation, Inc. http://www.fsf.org/
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
	 * @internal Development of this application was funded by http://www.bergen.kommune.no/bbb_/ekstern/
	 * @package soap
	 * @subpackage communication
	 * @version $Id: soap.php 12727 2015-02-10 13:09:35Z sigurdne $
	 */
	/*
	  Example testurl:
	  http://localhost/~hc483/savannah_trunk/property/inc/soap_client/bra5/soap.php?domain=default&location_id=54&section=BraArkiv
	 */

	$GLOBALS['phpgw_info'] = array();

	$GLOBALS['phpgw_info']['flags'] = array
		(
		'disable_Template_class' => true,
		'currentapp' => 'login',
		'noheader' => true,
		'noapi' => true  // this stops header.inc.php to include phpgwapi/inc/function.inc.php
	);

	$GLOBALS['phpgw_info']['flags']['session_name'] = 'soapclientsession';

	/**
	 * Include phpgroupware header
	 */
	require_once '../../../../header.inc.php';

	unset($GLOBALS['phpgw_info']['flags']['noapi']);

	$GLOBALS['phpgw_info']['message']['errors'] = array();
	$system_name = $GLOBALS['phpgw_info']['server']['system_name'];

	if (!isset($_GET['domain']) || !$_GET['domain'])
	{
		$GLOBALS['phpgw_info']['message']['errors'][] = "{$system_name}::domain not given as input";
	}
	else
	{
		$_REQUEST['domain'] = $_GET['domain'];
		$_domain_info = isset($GLOBALS['phpgw_domain'][$_GET['domain']]) ? $GLOBALS['phpgw_domain'][$_GET['domain']] : '';
		if (!$_domain_info)
		{
			$GLOBALS['phpgw_info']['message']['errors'][] = "{$system_name}::not a valid domain";
		}
		else
		{
			$GLOBALS['phpgw_domain'] = array();
			$GLOBALS['phpgw_domain'][$_GET['domain']] = $_domain_info;
		}
	}

	require_once PHPGW_API_INC . '/functions.inc.php';

	$location_id = phpgw::get_var('location_id', 'int');
	$section = phpgw::get_var('section', 'string');
	$bygningsnr = (int)phpgw::get_var('bygningsnr', 'int');
	$fileid = phpgw::get_var('fileid', 'string');

	if (!$fileid && !$bygningsnr)
	{
		$GLOBALS['phpgw_info']['message']['errors'][] = "{$system_name}::Bygningsnr ikke angitt som innparameter";
	}

	$c = CreateObject('admin.soconfig', $location_id);

	$login = $c->config_data[$section]['anonymous_user'];
	$passwd = $c->config_data[$section]['anonymous_pass'];
	$location_url = $c->config_data[$section]['location_url'];
	$braarkiv_user = $c->config_data[$section]['braarkiv_user'];
	$braarkiv_pass = $c->config_data[$section]['braarkiv_pass'];
	$classname = $c->config_data[$section]['arkd'];

	$_POST['submitit'] = "";

	//avoid confusion
	$GLOBALS['phpgw_info']['server']['usecookies'] = false;

	$GLOBALS['sessionid'] = $GLOBALS['phpgw']->session->create($login, $passwd);

	if (!$GLOBALS['sessionid'])
	{
		$lang_denied = lang('Anonymous access not correctly configured');
		if ($GLOBALS['phpgw']->session->reason)
		{
			$lang_denied = $GLOBALS['phpgw']->session->reason;
		}
		$GLOBALS['phpgw_info']['message']['errors'][] = "{$system_name}::{$lang_denied}";
	}

	if ($GLOBALS['phpgw_info']['message']['errors'])
	{
		_debug_array($GLOBALS['phpgw_info']['message']['errors']);
		$GLOBALS['phpgw']->common->phpgw_exit();
	}

	/**
	 * @global object $GLOBALS['server']
	 */
	ini_set('memory_limit', '512M');
	ini_set('display_errors', true);
	error_reporting(-1);
	/**
	 * Load autoload
	 */
	require_once PHPGW_API_INC . '/soap_client/bra5/Bra5Autoload.php';

	$wdsl = "{$location_url}?WSDL";
	$options = array();

	$options[Bra5WsdlClass::WSDL_URL] = $wdsl;
	$options[Bra5WsdlClass::WSDL_ENCODING] = 'UTF-8';
	//$options[Bra5WsdlClass::WSDL_CACHE_WSDL] = WSDL_CACHE_NONE;
	$options[Bra5WsdlClass::WSDL_TRACE] = false;
	$options[Bra5WsdlClass::WSDL_SOAP_VERSION] = SOAP_1_2;

	$wsdlObject = new Bra5WsdlClass($options);

	$bra5ServiceLogin = new Bra5ServiceLogin();
	if ($bra5ServiceLogin->Login(new Bra5StructLogin($braarkiv_user, $braarkiv_pass)))
	{
		$secKey = $bra5ServiceLogin->getResult()->getLoginResult()->LoginResult;
	}
	else
	{
		print_r($bra5ServiceLogin->getLastError());
	}

	if ($fileid)
	{
		$bra5ServiceGet = new Bra5ServiceGet();

		if ($bra5ServiceGet->getFileAsByteArray(new Bra5StructGetFileAsByteArray($secKey, $fileid)))
		{
			$file_result = $bra5ServiceGet->getResult()->getFileAsByteArrayResult;
			$file = base64_decode($file_result->getFileAsByteArrayResult);
			/*
			  $bra5ServiceGet->getFileName(new Bra5StructGetFileName($secKey, $fileid));
			  $filename = $bra5ServiceGet->getResult()->getFileNameResult->getFileNameResult;
			 */
			$browser = CreateObject('phpgwapi.browser');
			$browser->content_header("{$fileid}.pdf", 'application/pdf');

			echo $file;

			$GLOBALS['phpgw']->common->phpgw_exit();
		}
		else
		{
			_debug_array($bra5ServiceGet->getLastError());
			$GLOBALS['phpgw']->common->phpgw_exit();
		}
	}
	$bra5ServiceSearch = new Bra5ServiceSearch();
	/*
	  if($bra5ServiceSearch->searchDocument(new Bra5StructSearchDocument($secKey,$_baseclassname = 'Eiendomsarkiver',$classname,$_where = "Byggnr = {$bygningsnr}",$_maxhits = 2)))
	  {
	  //		_debug_array($bra5ServiceSearch->getResult());
	  }
	  else
	  {
	  print_r($bra5ServiceSearch->getLastError());
	  }
	 */
	if ($bra5ServiceSearch->searchAndGetDocuments(new Bra5StructSearchAndGetDocuments($secKey, $_baseclassname = 'Eiendomsarkiver', $classname, $_where = "Byggnr = {$bygningsnr}", $_maxhits = -1)))
	{
//		_debug_array($bra5ServiceSearch->getResult());die();
		$_result = $bra5ServiceSearch->getResult()->getsearchAndGetDocumentsResult()->getExtendedDocument()->getsearchAndGetDocumentsResult()->ExtendedDocument;
	}

	$html = <<<HTML
	<table>
HTML;
	$bra5ServiceLogout = new Bra5ServiceLogout();
	$bra5ServiceLogout->Logout(new Bra5StructLogout($secKey));

	if (!$_result)
	{
		echo "<H2> Ingen treff </H2>";
		$GLOBALS['phpgw']->common->phpgw_exit();
	}


	$skip_field = array
		(
		'ASTA_Signatur',
		'Adresse',
		'Sakstype',
		'Saksnr',
		'Tiltakstype',
		'Tiltaksart',
		'Gradering',
		'Skjerming',
		'BrukerID',
		'Team'
	);

	$html .='<th>';
	$html .='Last ned';
	$html . '</th>';

	$location_id = phpgw::get_var('location_id', 'int');
	$section = phpgw::get_var('section', 'string');

	$base_url = $GLOBALS['phpgw']->link('/property/inc/soap_client/bra5/soap.php', array(
		'domain' => $_GET['domain'],
		'location_id' => $location_id,
		'section' => $section
		)
	);
	foreach ($_result[0]->getAttributes()->Attribute as $attribute)
	{
		if (in_array($attribute->Name, $skip_field))
		{
			continue;
		}
		$html .='<th>';
		$html .=$attribute->Name;
		$html . '</th>';
	}

	foreach ($_result as $document)
	{
		$attributes = $document->getAttributes()->Attribute;
	}

	$case_array = array();
	foreach ($_result as $document)
	{
		$_html = '<tr>';
		$_html .='<td>';
		$_html .="<a href ='{$base_url}&fileid={$document->ID}' title = '{$document->Name}' target = '_blank'>{$document->ID}</a>";
		$_html .='</td>';

		foreach ($document->getAttributes()->Attribute as $attribute)
		{
			if (in_array($attribute->Name, $skip_field))
			{
				continue;
			}

			if ($attribute->Name == 'Saksdato')
			{
				$_key = strtotime($attribute->Value->anyType);
			}

			$_html .='<td>';

			if (is_array($attribute->Value->anyType))
			{
				$_html .= '<table>';

				foreach ($attribute->Value->anyType as $value)
				{
					$_html .= '<tr>';
					$_html .= '<td>';

					if (isset($value->enc_stype) && $value->enc_stype == 'Matrikkel')
					{
						$_html .= $value->enc_value->GNr;
						$_html .= '/' . $value->enc_value->BNr;
					}
					else
					{
						$_html .= $value;
					}

					$_html .= '</td>';
					$_html .= '</tr>';
				}
				$_html .= '</table>';
			}
			else
			{
				$_html .=$attribute->Value->anyType;
			}
			$_html .='</td>';
		}

		$_html .= '</tr>';

		$case_array[$_key][] = $_html;
	}

	ksort($case_array);
//_debug_array($case_array);
	foreach ($case_array as $case)
	{
		$html .= implode('', $case);
	}

	$html .=<<<HTML
	</table>
HTML;

	echo $html;

	$GLOBALS['phpgw']->common->phpgw_exit();
