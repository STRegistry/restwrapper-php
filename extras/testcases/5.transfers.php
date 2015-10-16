<?php
/*
 * Environment should be prepared in advance by ST Registry before running this test:
 * 1. Domain [CLIENT_LOGIN]transferin.st should be outside Registrar repository and available for transfer
 * 2. Domain [CLIENT_LOGIN]transferout1.st should be created IN Registrar repository with pendingTransfer state
 * 3. Domain [CLIENT_LOGIN]transferout2.st should be created IN Registrar repository with pendingTransfer state
 */
$config  = require_once(dirname(__FILE__) . '/config.php');

echo "# Test Transfers" . PHP_EOL;

require_once(dirname(__FILE__) . '/../../STRegistry.php');
require_once(dirname(__FILE__) . '/../../ResponseHelper.php');

echo "-> init library" . PHP_EOL;
STRegistry::Init($config['api_host'], $config['api_port'], $config['use_ssl']);

//AuthCode for domain [CLIENT_LOGIN]transferin.st to test transfer request/cancellation
$inAuthCode = '';

$in = sprintf("%stransferin.st", str_replace(array('_', '-', '.'), '', $config['api_login']));
$out1  = sprintf("%stransferout1.st", str_replace(array('_', '-', '.'), '', $config['api_login']));
$out2  = sprintf("%stransferout2.st", str_replace(array('_', '-', '.'), '', $config['api_login']));

echo "-> login";
$json = STRegistry::Session()->login($config['api_login'], $config['api_password']);
$json = ResponseHelper::fromJSON($json);
echo sprintf("	%s:%s", $json->code, $json->message) . PHP_EOL;
sleep(1);

echo "-> transfer request";
$json = STRegistry::Domains()->transferRequest($in, 1, $inAuthCode, __testcase('5.1'));
$json = ResponseHelper::fromJSON($json);
echo sprintf("	%s:%s", $json->code, $json->message) . " (cltrid: " . __testcase('5.1') . ")" . PHP_EOL;
sleep(1);

echo "-> transfer cancel";
$json = STRegistry::Domains()->transferCancel($in, __testcase('5.2'));
$json = ResponseHelper::fromJSON($json);
echo sprintf("	%s:%s", $json->code, $json->message) . " (cltrid: " . __testcase('5.2') . ")" . PHP_EOL;
sleep(1);

echo "-> transfer accept";
$json = STRegistry::Domains()->transferApprove($out1, __testcase('5.3'));
$json = ResponseHelper::fromJSON($json);
echo sprintf("	%s:%s", $json->code, $json->message) . " (cltrid: " . __testcase('5.3') . ")" . PHP_EOL;
sleep(1);

echo "-> transfer reject";
$json = STRegistry::Domains()->transferReject($out2, __testcase('5.4'));
$json = ResponseHelper::fromJSON($json);
echo sprintf("	%s:%s", $json->code, $json->message) . " (cltrid: " . __testcase('5.4') . ")" . PHP_EOL;

echo "# Done!" . PHP_EOL;
function __testcase($num)
{
	global $config;
	
	return sprintf('%s-testcase-%s', $config['api_login'], $num);
}

