<?php
$config  = require_once(dirname(__FILE__) . '/config.php');

echo "# Test Poll " . PHP_EOL;

require_once(dirname(__FILE__) . '/../../STRegistry.php');
require_once(dirname(__FILE__) . '/../../ResponseHelper.php');

echo "-> init library" . PHP_EOL;
STRegistry::Init($config['api_host'], $config['api_port'], $config['use_ssl']);

echo "-> login";
$json = STRegistry::Session()->login($config['api_login'], $config['api_password']);
$json = ResponseHelper::fromJSON($json);
echo sprintf("	%s:%s", $json->code, $json->message) . PHP_EOL;
sleep(1);

echo "-> request messages";
$json = STRegistry::Poll()->request(100, 0, __testcase('6.1'));
$json = ResponseHelper::fromJSON($json);
echo sprintf("	%s:%s", $json->code, $json->message) . " (cltrid: " . __testcase('6.1') . ")" . PHP_EOL;
sleep(1);

echo "-> acknowledge messages" . PHP_EOL;
$json = STRegistry::Poll()->request(100, 0);
$messages =  ResponseHelper::fromJSON($json, 'searchRes');
foreach ($messages->result as $id => $message) {
	echo sprintf("	-> ack message #%s %s", $id, $message['type']);
	$json = STRegistry::Poll()->ack($id, __testcase('6.2'));
	$json = ResponseHelper::fromJSON($json);
	echo sprintf("	%s:%s", $json->code, $json->message) . " (cltrid: " . __testcase('6.2') . ")" . PHP_EOL;
}


echo "# Done!" . PHP_EOL;

function __testcase($num)
{
	global $config;
	
	return sprintf('%s-testcase-%s', $config['api_login'], $num);
}