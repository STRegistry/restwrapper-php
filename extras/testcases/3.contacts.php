<?php
$config  = require_once(dirname(__FILE__) . '/config.php');

echo "# Test Contacts" . PHP_EOL;

require_once(dirname(__FILE__) . '/../../STRegistry.php');
require_once(dirname(__FILE__) . '/../../ResponseHelper.php');

echo "-> init library" . PHP_EOL;
STRegistry::Init($config['api_host'], $config['api_port'], $config['use_ssl']);

$contactID = 'ImArAnD0Mc0nTaCt';

echo "-> login";
$json = STRegistry::Session()->login($config['api_login'], $config['api_password']);
$json = ResponseHelper::fromJSON($json);
echo sprintf("	%s:%s", $json->code, $json->message) . PHP_EOL;
sleep(1);

echo "-> check ContactID \"".$contactID."\"";
$contactExist = STRegistry::Contacts()->exist($contactID, __testcase('3.1'));
echo sprintf(" : contact %s. ", !$contactExist ? "available" : "busy");
if ($contactExist) {
    echo "Trying to delete it - ";
    $json = STRegistry::Contacts()->delete($contactID);
    $json = ResponseHelper::fromJSON($json);
    if ($json->code==1000) {
        echo "SUCCESS";
    } else {
        die("FAILED. Unable to continue test case. ContactID " . $contactID . " should not be taken, release it to proceed further." . PHP_EOL);
    }
}
echo " (cltrid: " . __testcase('3.1') . ")" . PHP_EOL;
sleep(1);

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
		->setCity('Somewhere in Alaska')
		->setPostalCode(11111);
$json = STRegistry::Contacts()->create($contact, __testcase('3.2'));
$json = ResponseHelper::fromJSON($json);
echo sprintf("	%s:%s", $json->code, $json->message) . " (cltrid: " . __testcase('3.2') . ")" . PHP_EOL;
sleep(1);

echo "-> fetch";
$json = STRegistry::Contacts()->query($contactID, __testcase('3.3'));
$json = ResponseHelper::fromJSON($json);
echo sprintf("	%s:%s", $json->code, $json->message) . " (cltrid: " . __testcase('3.3') . ")" . PHP_EOL;
sleep(1);

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
		->setCity('Somewhere in Argentina')
		->setPostalCode(11222);
$json = STRegistry::Contacts()->update($contact, __testcase('3.4'));
$json = ResponseHelper::fromJSON($json);
echo sprintf("	%s:%s", $json->code, $json->message) . " (cltrid: " . __testcase('3.4') . ")" . PHP_EOL;
sleep(1);

echo "-> delete";
$json = STRegistry::Contacts()->delete($contactID, __testcase('3.5'));
$json = ResponseHelper::fromJSON($json);
echo sprintf("	%s:%s", $json->code, $json->message) . " (cltrid: " . __testcase('3.5') . ")" . PHP_EOL;

echo "# Done!" . PHP_EOL;

function __testcase($num)
{
	global $config;
	
	return sprintf('%s-testcase-%s', $config['api_login'], $num);
}
