<?php

echo "# Test Poll " . PHP_EOL;

$config = array(
	'api_login'     => '',
	'api_password'  => '',
	'api_host'      => '',
);

require_once(dirname(__FILE__) . '/../../STRegistry.php');
require_once(dirname(__FILE__) . '/../../ResponseHelper.php');

echo "-> init library" . PHP_EOL;
STRegistry::Init($config['api_host']);

echo "-> login";
$json = STRegistry::Session()->login($config['api_login'], $config['api_password']);
$json = ResponseHelper::fromJSON($json);
echo sprintf("	%s:%s", $json->code, $json->message) . PHP_EOL;

echo "-> prepare";
$domainName = sprintf("%s-WHOISPRIVACYDOMAIN.st", str_replace(array('_', '-', '.'), '', $config['api_login']));
if (!STRegistry::Domains()->exist($domainName)) {
	$domain = new Domain($domainName);
	$contact = new Contact(uniqid());
	$contact->setEmail(uniqid() . "@example.st")
			->setName(uniqid());

	STRegistry::Contacts()->create($contact);
	$domain->setContacts($contact->getContactId(), $contact->getContactId(), $contact->getContactId(), $contact->getContactId());
	STRegistry::Domains()->create($domain,1, __testcase('7.1'));	
} else {
	$json = STRegistry::Domains()->query($domainName, __testcase('2.1'));
	$domain = Domain::fromJSON($json);
}

$contacts = array();
for($i = 1; $i <= 8; ++$i) {
	$contacts[$i] = sprintf("PRV%s%d", str_replace(array('_', '-', '.'), '', strtoupper($config['api_login'])), $i);
	$contact = new Contact($contacts[$i]);
	$contact->setEmail(uniqid() . "@example.st")
			->setName(uniqid());
	STRegistry::Contacts()->create($contact, __testcase('7.2'));
}

echo "	done." . PHP_EOL;

echo "-> create privacy";
$json = STRegistry::Domains()->setPrivacy($domain->getName(), $contacts[1], $contacts[2], $contacts[3], $contacts[4], __testcase('7.3'));
$json = ResponseHelper::fromJSON($json);
echo sprintf("	%s:%s", $json->code, $json->message) . PHP_EOL;

echo "-> update privacy";
$json = STRegistry::Domains()->setPrivacy($domain->getName(), $contacts[5], $contacts[6], $contacts[7], $contacts[8], __testcase('7.4'));
$json = ResponseHelper::fromJSON($json);
echo sprintf("	%s:%s", $json->code, $json->message) . PHP_EOL;

echo "-> remove privacy";
$json = STRegistry::Domains()->removePrivacy($domain->getName(), __testcase('7.5'));
$json = ResponseHelper::fromJSON($json);
echo sprintf("	%s:%s", $json->code, $json->message) . PHP_EOL;

echo "-> create privacy for several contacts";
$json = STRegistry::Domains()->setPrivacy($domain->getName(), $contacts[1], $contacts[2], null, null, __testcase('7.6'));
$json = ResponseHelper::fromJSON($json);
echo sprintf("	%s:%s", $json->code, $json->message) . PHP_EOL;


function __testcase($num)
{
	global $config;
	
	return sprintf('%s-testcase-%s', $config['api_login'], $num);
}