<?php
require_once('../nuSOAP/nusoap.php');
$zerb = new soap_server();
$zerb -> configureWSDL('egiaztatuPasahitza','urn:egiaztatuPasahitza');

$zerb -> wsdl -> addComplexType(
	'pasahitza',
	'ticket',
	array(
		'pasahitza' => array('name' => 'pasahitza', 'type' => 'xsd:string'),
		'ticket' => array('name' => 'ticket', 'type' => 'xsd:string')
	)
);
$zerb -> wsdl -> addComplexType(
	'return',
	array('name' => 'return', 'type' => 'xsd:string')
);
$zerb -> register(
	'egokiaDa',
	array('pasahitza' => 'tns:pasahitza', 'ticket' => 'tns:string'),
	array('return' => 'tns:string'),
	'urn:egiaztatuPasahitza',
	'urn:egiaztatuPasahitza#egokiaDa',
	'rpc',
	'encoded',
	'pasahitza arrunta den ala ez aztertzen du. Ez bada ala \'BALIOZKOA\' itzuliko du, \'BALIOGABEA\' bestela.'
);

function egokiaDa($p){
	if(strcmp($p['ticket'],'1010') == 0){
		$f = file('http://localhost/AzkLan/baliabideak/toppasswords.txt');

		foreach ($f as $zk => $lerro){
			if(strcmp(trim($lerro),$p['pasahitza']) == 0){return 'BALIOGABEA';}
		}
		return 'BALIOZKOA';
	}
	else{
		return 'ZERBITZURIK GABE';
	}
}
$HTTP_RAW_POST_DATA = isset($HTTP_RAW_POST_DATA) ? $HTTP_RAW_POST_DATA :'';
$zerb -> service($HTTP_RAW_POST_DATA);
?>