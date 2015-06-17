<?php
echo "# Test Session" . PHP_EOL;

$config = array(
	'api_login'    => '',
	'api_password' => '',
	'api_host'     => '',
);

require_once(dirname(__FILE__) . '/../../STRegistry.php');
require_once(dirname(__FILE__) . '/../../ResponseHelper.php');

echo "-> init library" . PHP_EOL;
STRegistry::Init($config['api_host']);

echo "-> login";
$json = STRegistry::Session()->login($config['api_login'], $config['api_password'], __testcase('1.1'));
$json = ResponseHelper::fromJSON($json);
echo sprintf("	%s:%s", $json->code, $json->message) . PHP_EOL;

echo "-> validate";
$json = STRegistry::Session()->validate(__testcase('1.2'));
$json = ResponseHelper::fromJSON($json);
echo sprintf("	%s:%s", $json->code, $json->message) . PHP_EOL;

echo "-> logout";
$json = STRegistry::Session()->logout(__testcase('1.3'));
$json = ResponseHelper::fromJSON($json);
echo sprintf("	%s:%s", $json->code, $json->message) . PHP_EOL;

echo "# Done." . PHP_EOL;

function __testcase($num)
{
	global $config;
	
	return sprintf('%s-testcase-%s', $config['api_login'], $num);
}