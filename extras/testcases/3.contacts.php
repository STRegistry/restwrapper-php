<?php
echo "# Test Contacts" . PHP_EOL;

$config = array(
	'api_login'     => '',
	'api_password'  => '',
	'api_host'      => '',
);

require_once(dirname(__FILE__) . '/../../STRegistry.php');
require_once(dirname(__FILE__) . '/../../ResponseHelper.php');

echo "-> init library" . PHP_EOL;
STRegistry::Init($config['api_host']);

$contactID = 'ImArAnD0Mc0nTaCt';

echo "-> login";
$json = STRegistry::Session()->login($config['api_login'], $config['api_password']);
$json = ResponseHelper::fromJSON($json);
echo sprintf("	%s:%s", $json->code, $json->message) . PHP_EOL;

echo "-> check";
$contactExist = STRegistry::Contacts()->exist($contactID, __testcase('3.1'));
echo sprintf(" : contact %s", !$contactExist ? "available" : "busy") . PHP_EOL;

echo "-> create";
$contact = new Contact($contactID);
$contact->setEmail(sprintf("%s@registry.st", $config['api_login']))
		->setPhoneNumber('+1.123456789')
		->setFaxNumber('+1.123456789')
		->setName('Test Contact')
		->setOrganization('Test Contact Org')
		->setAddress('test street', 'test street 2', 'test street 3')
		->setCountryCode('US')
		->setState('Alaska')
		->setCity('Somwhere in Alaska')
		->setPostalCode(11111);
$json = STRegistry::Contacts()->create($contact, __testcase('3.2'));
$json = ResponseHelper::fromJSON($json);
echo sprintf("	%s:%s", $json->code, $json->message) . PHP_EOL;

echo "-> fetch";
$json = STRegistry::Contacts()->query($contactID, __testcase('3.3'));
$json = ResponseHelper::fromJSON($json);
echo sprintf("	%s:%s", $json->code, $json->message) . PHP_EOL;

echo "-> update";
$json = STRegistry::Contacts()->query($contactID);
$contact = Contact::fromJSON($json);
$contact->setEmail(sprintf("%s-upd@registry.st", $config['api_login']))
		->setPhoneNumber('+2.123456789')
		->setFaxNumber('+2.123456789')
		->setName('Test Contact-upd')
		->setOrganization('Test Contact Org-upd')
		->setAddress('test street-upd', 'test street-upd 2', 'test street-upd 3')
		->setCountryCode('AR')
		->setState('')
		->setCity('Somwhere in Argentina')
		->setPostalCode(11222);
$json = STRegistry::Contacts()->update($contact, __testcase('3.4'));
$json = ResponseHelper::fromJSON($json);
echo sprintf("	%s:%s", $json->code, $json->message) . PHP_EOL;

echo "-> delete";
$json = STRegistry::Contacts()->delete($contactID, __testcase('3.5'));
$json = ResponseHelper::fromJSON($json);
echo sprintf("	%s:%s", $json->code, $json->message) . PHP_EOL;

echo "# Done!" . PHP_EOL;

function __testcase($num)
{
	global $config;
	
	return sprintf('%s-testcase-%s', $config['api_login'], $num);
}
