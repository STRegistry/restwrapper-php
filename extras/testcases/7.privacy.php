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

echo "-> prepare for test case: " . PHP_EOL;
$domainName = sprintf("%s-WHOISPRIVACYDOMAIN.st", str_replace(array('_', '-', '.'), '', $config['api_login']));
echo "Creating domain " . $domainName . ":";
if (!STRegistry::Domains()->exist($domainName)) {
	$domain = new Domain($domainName);
	$contact = new Contact(uniqid());
	$contact->setEmail(uniqid() . "@example.st")
			->setName(uniqid());

	STRegistry::Contacts()->create($contact);
	$domain->setContacts($contact->getContactId(), $contact->getContactId(), $contact->getContactId(), $contact->getContactId());
	STRegistry::Domains()->create($domain,1, __testcase('7.1'));
    echo " DONE (cltrid: " . __testcase('7.1') . ")" . PHP_EOL;
} else {
    echo " already exist. Skipping. WARNING: TEST INCOMPLETE!!!" . PHP_EOL;
	$json = STRegistry::Domains()->query($domainName, __testcase('2.1'));
	$domain = Domain::fromJSON($json);
}
sleep(1);

$contacts = array();
for($i = 1; $i <= 8; ++$i) {
	$contacts[$i] = sprintf("PRV%s%d", str_replace(array('_', '-', '.'), '', strtoupper($config['api_login'])), $i);
    echo "Creating privacy contact " . $contacts[$i].": ";
    if(!STRegistry::Contacts()->exist($contacts[$i])){
        $contact = new Contact($contacts[$i]);
        $contact->setEmail(uniqid() . "@example.st")
            ->setName(uniqid());
        STRegistry::Contacts()->create($contact, __testcase('7.2'));
        echo "DONE (cltrid: " . __testcase('7.2') . ")" . PHP_EOL;
    } else {
        echo "already exist. Skipping. WARNING: TEST INCOMPLETE!!!" . PHP_EOL;
    }
}
sleep(1);

echo "	preparation finished." . PHP_EOL;

echo "-> create domain WHOIS privacy";
$json = STRegistry::Domains()->setPrivacy($domain->getName(), $contacts[1], $contacts[2], $contacts[3], $contacts[4], __testcase('7.3'));
$json = ResponseHelper::fromJSON($json);
echo sprintf("	%s:%s", $json->code, $json->message) . " (cltrid: " . __testcase('7.3') . ")" . PHP_EOL;
sleep(1);

echo "-> update domain WHOIS privacy";
$json = STRegistry::Domains()->setPrivacy($domain->getName(), $contacts[5], $contacts[6], $contacts[7], $contacts[8], __testcase('7.4'));
$json = ResponseHelper::fromJSON($json);
echo sprintf("	%s:%s", $json->code, $json->message) . " (cltrid: " . __testcase('7.4') . ")" . PHP_EOL;
sleep(1);

echo "-> remove domain WHOIS privacy";
$json = STRegistry::Domains()->removePrivacy($domain->getName(), __testcase('7.5'));
$json = ResponseHelper::fromJSON($json);
echo sprintf("	%s:%s", $json->code, $json->message) . " (cltrid: " . __testcase('7.5') . ")" . PHP_EOL;
sleep(1);

echo "-> create domain WHOIS privacy for registrant and admin contacts ONLY";
$json = STRegistry::Domains()->setPrivacy($domain->getName(), $contacts[1], $contacts[2], null, null, __testcase('7.6'));
$json = ResponseHelper::fromJSON($json);
echo sprintf("	%s:%s", $json->code, $json->message) . " (cltrid: " . __testcase('7.6') . ")" . PHP_EOL;
sleep(1);

echo "-> cleaning up " . PHP_EOL;

echo "Remove all privacy contacts from domain " . $domain->getName();
$json = STRegistry::Domains()->setPrivacy($domain->getName(), null, null, null, null);
$json = ResponseHelper::fromJSON($json);
echo sprintf("	%s:%s", $json->code, $json->message) . PHP_EOL;

foreach ($contacts as $contact) {
    echo "Removing contact \"" . $contact . "\": ";
    $json = STRegistry::Contacts()->delete($contact);
    $json = ResponseHelper::fromJSON($json);
    echo sprintf("	%s:%s", $json->code, $json->message) . PHP_EOL;
}

echo "Terminate domain " . $domain->getName();
$json = STRegistry::Domains()->delete($domain->getName());
$json = ResponseHelper::fromJSON($json);
echo sprintf("	%s:%s", $json->code, $json->message) . PHP_EOL;

echo "# Done!" . PHP_EOL;

function __testcase($num)
{
	global $config;
	
	return sprintf('%s-testcase-%s', $config['api_login'], $num);
}