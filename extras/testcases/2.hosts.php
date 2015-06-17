<?php
echo "# Test Hosts" . PHP_EOL;

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

$domain = createtestdomain();
$hostname = sprintf("ns1.%s", $domain->getName());

echo "-> check host";
$hostExist = STRegistry::Hosts()->exist($hostname, __testcase('2.2'));
echo sprintf(" : hostname %s", !$hostExist ? "available" : "busy") . PHP_EOL;

echo "-> create host";
$host = new Host($hostname);
$host->addIPv4('192.0.2.1')
 	 ->addIPv6('2001:db8:8:4::2');
$json = STRegistry::Hosts()->create($host, __testcase('2.3'));
$json = ResponseHelper::fromJSON($json);
echo sprintf("	%s:%s", $json->code, $json->message) . PHP_EOL;

echo "-> fetch host";
$json = STRegistry::Hosts()->query($host->getName(), __testcase('2.4'));
$json = ResponseHelper::fromJSON($json);
echo sprintf("	%s:%s", $json->code, $json->message) . PHP_EOL;

echo "-> update host v1";
$json = STRegistry::Hosts()->query($host->getName());
$host = Host::fromJSON($json);
$host->addIPv4('192.0.2.2')->addIPv6('2002:cb0a:3cdd:1::1');
$json = STRegistry::Hosts()->update($host, __testcase('2.5'));
$json = ResponseHelper::fromJSON($json);
echo sprintf("	%s:%s", $json->code, $json->message) . PHP_EOL;

echo "-> update host v2";
$json = STRegistry::Hosts()->query($host->getName());
$host = Host::fromJSON($json);
$host->removeIPv4('192.0.2.1')->removeIPv6('2001:db8:8:4::2');
$json = STRegistry::Hosts()->update($host, __testcase('2.6'));
$json = ResponseHelper::fromJSON($json);
echo sprintf("	%s:%s", $json->code, $json->message) . PHP_EOL;

echo "-> delete host";
$json = STRegistry::Hosts()->delete($host->getName(), __testcase('2.7'));
$json = ResponseHelper::fromJSON($json);
echo sprintf("	%s:%s", $json->code, $json->message) . PHP_EOL;

echo "# Done!" . PHP_EOL;


function createtestdomain()
{	
	global $config;

	$domainName = sprintf("%s-GLUERECORDDOMAIN.st", str_replace(array('_', '-', '.'), '', $config['api_login']));
	if (!STRegistry::Domains()->exist($domainName)) {
		$domain = new Domain($domainName);
		$contact = new Contact(uniqid());
		$contact->setEmail(uniqid() . "@example.st")
				->setName(uniqid());

		STRegistry::Contacts()->create($contact);
		$domain->setContacts($contact->getContactId(), $contact->getContactId(), $contact->getContactId(), $contact->getContactId());
		STRegistry::Domains()->create($domain,1, __testcase('2.1'));	
	} else {
		$json = STRegistry::Domains()->query($domainName, __testcase('2.1'));
		$domain = Domain::fromJSON($json);
	}

	return $domain;
}

function __testcase($num)
{
	global $config;
	
	return sprintf('%s-testcase-%s', $config['api_login'], $num);
}
