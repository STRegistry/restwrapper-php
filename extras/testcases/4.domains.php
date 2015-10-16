<?php
$config  = require_once(dirname(__FILE__) . '/config.php');

echo "# Test Domains" . PHP_EOL;

require_once(dirname(__FILE__) . '/../../STRegistry.php');
require_once(dirname(__FILE__) . '/../../ResponseHelper.php');

echo "-> init library" . PHP_EOL;
STRegistry::Init($config['api_host'], $config['api_port'], $config['use_ssl']);

$domainName = sprintf("%stestdomain.st", str_replace(array('_', '-', '.'), '', $config['api_login']));

echo "-> login";
$json = STRegistry::Session()->login($config['api_login'], $config['api_password']);
$json = ResponseHelper::fromJSON($json);
echo sprintf("	%s:%s", $json->code, $json->message) . PHP_EOL;
sleep(1);

echo "-> check domain ".$domainName;
$domainExist = STRegistry::Domains()->exist($domainName, __testcase('4.1'));
echo sprintf(" : domain %s (cltrid: " . __testcase('4.1') . ")", !$domainExist ? "available" : "busy") . PHP_EOL;
if ($domainExist) die("Unable to continue test case. Domain " . $domainName . " should be available for registration. Terminate this domain to proceed further." . PHP_EOL);
sleep(1);

echo "-> create";
$contactID = sprintf("N3W%s", strtoupper($config['api_login']));
if (!STRegistry::Contacts()->exist($contactID)) {
	$contact = new Contact($contactID);
	$contact->setEmail(uniqid() . "@example.st")
			->setName(uniqid());
	STRegistry::Contacts()->create($contact);
}
$domain = new Domain($domainName);
$domain->setContacts($contactID, $contactID, $contactID, $contactID);
$domain->addNameServer('ns1.nic.st');
$json = STRegistry::Domains()->create($domain, 1, __testcase('4.2'));
$json = ResponseHelper::fromJSON($json);
echo sprintf("	%s:%s", $json->code, $json->message) . " (cltrid: " . __testcase('4.2') . ")" . PHP_EOL;
sleep(1);

echo "-> fetch";
$json = STRegistry::Domains()->query($domainName, null, __testcase('4.3'));
$json = ResponseHelper::fromJSON($json);
echo sprintf("	%s:%s", $json->code, $json->message) . " (cltrid: " . __testcase('4.3') . ")" . PHP_EOL;
sleep(1);

echo "-> update contacts";
$contacts = array();
for ($i=1; $i <= 4; ++$i) {
	$contacts[$i] = sprintf("N3W%s%d", str_replace(array('_', '-', '.'), '', strtoupper($config['api_login'])), $i);
	$contact = new Contact($contacts[$i]);
	$contact->setEmail(uniqid() . "@example.st")
			->setName(uniqid());
	STRegistry::Contacts()->create($contact);
}
$json = STRegistry::Domains()->query($domainName);
$domain = Domain::fromJSON($json);
$domain->setContacts($contacts[1],$contacts[2],$contacts[3],$contacts[4]);
$json = STRegistry::Domains()->update($domain, __testcase('4.4'));
$json = ResponseHelper::fromJSON($json);
echo sprintf("	%s:%s", $json->code, $json->message) . " (cltrid: " . __testcase('4.4') . ")" . PHP_EOL;
sleep(1);

echo "-> update nameservers";
$json = STRegistry::Domains()->query($domainName);
$domain = Domain::fromJSON($json);
foreach ($domain->getNameServers() as $ns => $ips) {
	$domain->removeNameServer($ns);
}
$domain->addNameServer(uniqid().".registry.st")
	   ->addNameServer(uniqid().".registry.st")
	   ->addNameServer(uniqid().".registry.st")
	   ->addNameServer(uniqid().".".$domain->getName(), array('192.0.2.2', '2002:cb0a:3cdd:1::1'));
$json = STRegistry::Domains()->update($domain, __testcase('4.5'));
$json = ResponseHelper::fromJSON($json);
echo sprintf("	%s:%s", $json->code, $json->message) . " (cltrid: " . __testcase('4.5') . ")" . PHP_EOL;
sleep(1);

echo "-> update auth code";
$json = STRegistry::Domains()->query($domainName);
$domain = Domain::fromJSON($json);
$domain->setAuthCode(sprintf("%sC", uniqid()));
$json = STRegistry::Domains()->update($domain, __testcase('4.6'));
$json = ResponseHelper::fromJSON($json);
echo sprintf("	%s:%s", $json->code, $json->message) . " (cltrid: " . __testcase('4.6') . ")" . PHP_EOL;
sleep(1);

echo "-> renew";
$json = STRegistry::Domains()->query($domainName);
$json = ResponseHelper::fromJSON($json, 'info');
$json = STRegistry::Domains()->renew($domainName, 1, $json->result['exDate'], __testcase('4.7'));
$json = ResponseHelper::fromJSON($json);
echo sprintf("	%s:%s", $json->code, $json->message) . " (cltrid: " . __testcase('4.7') . ")" . PHP_EOL;
sleep(1);

echo "-> add statuses";
$json = STRegistry::Domains()->query($domainName);
$domain = Domain::fromJSON($json);
foreach (array(Domain::STATUS_HOLD, Domain::STATUS_DELETE_PROHIBITED, Domain::STATUS_RENEW_PROHIBITED) as $status) {
	$domain->addStatus($status);
}
$json = STRegistry::Domains()->update($domain, __testcase('4.8'));
$json = ResponseHelper::fromJSON($json);
echo sprintf("	%s:%s", $json->code, $json->message) . " (cltrid: " . __testcase('4.8') . ")" . PHP_EOL;
sleep(1);

echo "-> remove statuses";
$json = STRegistry::Domains()->query($domainName);
$domain = Domain::fromJSON($json);
foreach (array(Domain::STATUS_HOLD, Domain::STATUS_DELETE_PROHIBITED, Domain::STATUS_RENEW_PROHIBITED) as $status) {
	$domain->removeStatus($status);
}
$json = STRegistry::Domains()->update($domain, __testcase('4.9'));
$json = ResponseHelper::fromJSON($json);
echo sprintf("	%s:%s", $json->code, $json->message) . " (cltrid: " . __testcase('4.9') . ")" . PHP_EOL;
sleep(1);

echo "-> delete";
$json = STRegistry::Domains()->delete($domainName, __testcase('4.10'));
$json = ResponseHelper::fromJSON($json);
echo sprintf(" %s: %s:%s", $domainName, $json->code, $json->message) . " (cltrid: " . __testcase('4.10') . ")" . PHP_EOL;


function __testcase($num)
{
	global $config;
	
	return sprintf('%s-testcase-%s', $config['api_login'], $num);
}