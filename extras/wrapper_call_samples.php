<?
	//ST Registry REST API PHP wrapper
	//This wrapper cover all functionality provided by ST Registry through REST API:
	//Session Login/Logout/Validate
	//Domain Check/Create/Query/Update/Delete/Renew/Transfer/WHOIS/Privacy/Search
	//Contact Check/Create/Query/Update/Delete/Search
	//Host Check/Create/Query/Update/Delete/Search
	//Poll Request/Acknowledge
	//Query Registrar details

	//****************************************************************************
	//****************************Session management******************************
	//****************************************************************************

	//Initialization
	STRegistry::Init($ApiHost, $apiPort, $apiVersion = '1.0', $apiContentType='application/json');
	$apiHost = STRegistry::getAPIHost();
	$apiPort = STRegistry::getAPIPort();
	$apiVersion = STRegistry::getAPIVersion();
	$apiContentType = STRegistry::getAPIContentType();

	//Authentication
	$jsonResponse = STRegistry::Session()->Login($login, $password);
	
	//authToken can be obtained through getAuthToken() method
	$authToken = STRegistry::Session()->getAuthToken();
	
	//Check current session expire date
	$sessionExpireTime = STRegistry::Session-()>getExpireDateTime();
	//Validate current session on ST Registry side. 
	//Response format: http://kb.registry.st/rest-api/rest-session-handling/#Auth_token_validation
	$jsonResponse = STRegistry::Session()()->Validate();
	//Destroy current session
	STRegistry::Session()->Logout()
	
	//****************************************************************************
	//***************************Response helper**********************************
	//Response helper can be used to simplify response validation
	//****************************************************************************
	
	$responseObj = ResponseHelper::fromJSON($jsonResponse);
	//4 values exist as object attributes and are available in all API responses
	print $responseObj->code; //response code
	print $responseObj->message; //response message
	print $responseObj->cltrid; //client/registrar transaction id
	print $responseObj->svtrid; //server transaction id
	//Other values located as "result" array
	print $responseObj->result['time']; //request execution time
	print $responseObj->result['expires']; //session expire date/time
	print $responseObj->result['created']; //date/time when session was created
	print $responseObj->result['reqtime']; //date/time when validation request was made. Might be important to sync ST Registry time with local server time
	
	//Check if last request was successful
	if ($responseObj->code == 1000) {
		//Request was successful
	} else {
		//Some error occurred. Refer to the error codes table for clarification
	}

	//****************************************************************************
	//****************************Domains management******************************
	//****************************************************************************
	
	//Check domain availability. 
	//Response format: http://kb.registry.st/rest-api/rest-domain-management/#1_Check_domain_availability
	$isDomainExists = STRegistry::Domains()->Exist('check.st');
	
	//Query domain WHOIS details. 
	//Response format: http://kb.registry.st/rest-api/rest-domain-management/#9_Query_domainWHOIS_details
	$jsonResponse = STRegistry::Domains()->WHOIS('whois.st');
	
	//Domain renewal. 
	//Response format: http://kb.registry.st/rest-api/rest-domain-management/#7_Renew_domain
	$renewalYears = 1;
	// query domain info before renewal in order to get current domain expiration date. This is required to prevent several renewals at same time
	$jsonResponse = STRegistry::Domains()->query('renewdomain.st');
	$domainInfo   = ResponseHelper::fromJSON($jsonResponse, 'info');
	$jsonResponse = STRegistry::Domains()->Renew('renewdomain.st', $renewalYears, $domainInfo->result['exDate']);

	//Define privacy contacts for domain.
	//It is possible to exclude any contact type from WHOIS privacy protection.
	//Response format: http://kb.registry.st/rest-api/rest-domain-management/#10_WHOIS_Privacy
	$jsonResponse = STRegistry::Domains()->SetPrivacy('privacydomain.st', 'REGISTRANT_PRIVACY_ID', 'ADMIN_PRIVACY_ID', 'BILLING_PRIVACY_ID', 'TECH_PRIVACY_ID');

	//Remove privacy protection from all domain contacts.
	$jsonResponse = STRegistry::Domains()->RemovePrivacy('privacydomain.st');
	
	//Request domain transfer.
	//Response format: http://kb.registry.st/rest-api/rest-domain-management/#81_Transfer_command_8211_Request
	$jsonResponse = STRegistry::Domains()->TransferRequest('transferdomain.st', $renewalYears, $authInfo);
	
	//Query domain transfer state.
	//Response format: http://kb.registry.st/rest-api/rest-domain-management/#85_Transfercommand_8211Query
	$jsonResponse = STRegistry::Domains()->TransferQuery('transferdomain.st', $authInfo);
	
	//Cancel pending domain transfer by requesting registrar.
	//Response format: http://kb.registry.st/rest-api/rest-domain-management/#82_Transfercommand_8211_Cancel
	$jsonResponse = STRegistry::Domains()->TransferCancel('transferdomain.st');
	
	//Reject pending transfer request by loosing registrar.
	//Response format: http://kb.registry.st/rest-api/rest-domain-management/#84_Transfercommand_8211Reject
	$jsonResponse = STRegistry::Domains()->TransferReject('transferdomain.st');
	
	//Approve pending transfer request by loosing registrar
	//Response format: http://kb.registry.st/rest-api/rest-domain-management/#83_Transfer_command_8211Approve
	$jsonResponse = STRegistry::Domains()->TransferApprove('transferdomain.st');
	
	//Fetch transfers history (both pending and completed transfers)
	$jsonResponse = STRegistry::Domains->TransfersHistory();
	//Fetch transfers history for certain domain.
	$jsonResponse = STRegistry::Domains()->TransfersHistory('historydomain.st');

	$domain = new Domain('createdomain.st');
	$domain->setContacts('REGISTRANTID', 'ADMINID', 'BILLINGID', 'TECHID'); //All contacts are required
	//or
	$domain->setRegistrantContactId('REGISTRANTID')
	       ->setAdminContactId('ADMINID')
	       ->setTechContactId('TECHID')
	       ->setBillingContactId('BILLINGID');

	$contacts = $domain->getContacts(); // return all domain contacts array
	print $domain->getRegistrantContactID(); // return domain registrant contact ID
	print $domain->getAdminContactID(); // return admin contact ID
	print $domain->getBillingContactID(); // return billing contact ID
	print $domain->getTechContactID(); // return technical contact ID
	print $domain->getRegistrantPrivacyContactID(); // return domain registrant privacy contact ID
	print $domain->getAdminPrivacyContactID(); // return admin privacy contact ID
	print $domain->getBillingPrivacyContactID(); // return billing privacy contact ID
	print $domain->getTechPrivacyContactID(); // return technical privacy contact ID
	//Add name servers to domain. OPTIONAL
	//Domains without name servers will be created with status "inactive"
	$domain->addNameServer('ns1.createdomain.st', array('192.168.0.1', '0::1')); // IPS array is OPTIONAL
	$domain->addNameServer('ns1.externalhost.st');
	// remove domain nameserver
	$domain->removeNameServer('ns1.createdomain.st');
	//SET WHOIS Privacy contacts. OPTIONAL.
	$domain->setPrivacyContacts("PRIVACY_REGISTRANTID", null, null, null); //You are able to define WHOIS privacy contact for any contact type. In this particular case we are masking registrant contact
	//or 
	$domain->setRegistrantPrivacyContactId('PRIVACY_REGISTRANTID')
	       ->setAdminPrivacyContactId('PRIVACY_ADMINID')
	       ->setTechPrivacyContactId('PRIVACY_TECHID')
	       ->setBillingPrivacyContactId('PRIVACY_BILLINGID');

	//Define AuthCode for domain
	$domain->setAuthCode('AuthC0de'); //OPTIONAL. If not provided then ST Registry will generate unique code.
	// get domain auth code.
	$domain->getAuthCode();

	//List of all statuses available for client:
	//Domain::STATUS_HOLD - clientHold
	//Domain::STATUS_UDPATE_PROHIBITED - clientUpdateProhibited
	//Domain::STATUS_DELETE_PROHIBITED - clientDeleteProhibited 
	//Domain::STATUS_RENEW_PROHIBITED  - clientRenewProhibited
	//Information about every status is available on: http://kb.registry.st/domains/domain-statuses/
	//Status management examples:
	//remove client status from domain
	$domain->removeStatus(Domain::STATUS_UDPATE_PROHIBITED);
	//add client status to domain
	$domain->addStatus(Domain::STATUS_HOLD);
	//get list of all domain statuses
	$statuses = $domain->getStatuses();
	
	//validate domain attributes on ST Registry side. If any contact doesn't exist on ST Registry side then validation will no pass through
	$isDomainValid = $domain->Validate();

	if (!$isDomainValid) {
		// access to validation error details
		$code = $domain->getValidationErrorCode();
		$message = $domain->getValidationErrorMessage();
	}
	
	//Register domain.
	//Response format: http://kb.registry.st/rest-api/rest-domain-management/#2_Create_domain
	if ($isDomainValid) {
		$jsonResponse = STRegistry::Domains()->Create(
							$domain, //domain object
							1, //registration period in years
							);
	}
	
	//Query domain details for domains OUTSIDE registrar domains portfolio using AuthCode.
	$jsonResponse = STRegistry::Domains()->Query('querydomain.st', 'AuthC0de');
	
	//Query domain details for domains WITHIN registrar domains portfolio. Response format: http://kb.registry.st/rest-api/rest-domain-management/#41_Query_domain_info_by_authorized_Client
	$jsonResponse = STRegistry::Domains()->Query('queryowndomain.st');
	$domain = Domain::fromJSON($jsonResponse);
	
	//Update domain.
	//NULL values will be ignored and remain unchanged on ST Registry side.
	//Response format: http://kb.registry.st/rest-api/rest-domain-management/#5_Domain_update
	$jsonResponse = STRegistry::Domains()->Update($domain);
			
	//Domain termination. Response format: http://kb.registry.st/rest-api/rest-domain-management/#6_Delete_domain
	$jsonResponse = STRegistry::Domains()->Delete('querydomain.st');


	//****************************************************************************
	//****************************Contacts management*****************************
	//****************************************************************************

	//Check contact ID availability.
	//Response format: http://kb.registry.st/rest-api/rest-contact-management/#1_Check_Contact_availability
	if(STRegistry::Contacts()->Exist('CONTACTID')) {
		//Contact exist in Registrar repository
	} else {
		//Contact not found in Registrar repository
	}
	
	
	//Query contact details WITHIN Registrar repository
	//Response format: http://kb.registry.st/rest-api/rest-contact-management/#3_Query_Contact_info
	$jsonResponse = STRegistry::Contacts()->Query('CONTACTID');
	
	//Convert JSONE response into Contact object
	$contact = Contact::fromJSON($jsonResponse);

	//Create contact. By default all values defined as internationalised contact.
	//Response format: http://kb.registry.st/rest-api/rest-contact-management/#2_Create_Contact
	$contact = new Contact($id = 'CONTACTID'); // Contact ID OPTIONAL
	$contact->setContactID('CONTACTID'); //OPTIONAL. If not provided - will be generated UNIQUE Contact ID by ST Registry when create new contact.
	$contact->setPhoneNumber('+1.123456789'); //OPTIONAL
	$contact->setFaxNumber('+1.123456789'); //OPTIONAL
	$contact->setEmail('name@demoemail.st'); //REQUIRED
	//International format
	$contact->setName('John Smith'); //REQUIRED
	//Local format
	$contact->setName('John Smith', Contact::POSTALINFO_LOCAL); //OPTIONAL
	//International format
	$contact->setOrganization('Organization name'); //OPTIONAL
	//Local format
	$contact->setOrganization('Organization name', Contact::POSTALINFO_LOCAL); //OPTIONAL

	//International format
	$contact->setAddress('street1', 'street2', 'street3'); //OPTIONAL
	//Local format
	$contact->setAddress('street1', 'street2', 'street3', Contact::POSTALINFO_LOCAL); //OPTIONAL

	//International format
	$contact->setCity('City name'); //OPTIONAL
	//Local format
	$contact->setCity('City name', Contact::POSTALINFO_LOCAL); //OPTIONAL

	//International format
	$contact->setPostalCode('12345'); //OPTIONAL
	//Local format
	$contact->setPostalCode('12345', Contact::POSTALINFO_LOCAL); //OPTIONAL

	//International format
	$contact->setCountryCode('US'); //OPTIONAL
	//Local format
	$contact->setCountryCode('US', Contact::POSTALINFO_LOCAL); //OPTIONAL

	//International format
	$contact->setState('Alaska'); //OPTIONAL
	//Local format
	$contact->setState('Alaska', Contact::POSTALINFO_LOCAL); //OPTIONAL
	
	//Perform contact attributes validation on ST Registry side
	$isContactValid = $contact->Validate();

	if (!$isContactValid) {
		// access to validation error details
		$code = $contact->getValidationErrorCode();
		$message = $contact->getValidationErrorMessage();
	}
	
	if ($isContactValid) $jsonResponse = STRegistry::Contacts()->Create($contact);
	
	//Update contact.
	//Response format: http://kb.registry.st/rest-api/rest-contact-management/#4_Update_Contact
	$jsonResponse = STRegistry::Contacts()->Update($contact);
	
	//Delete existing contact (unused).
	//Response format: http://kb.registry.st/rest-api/rest-contact-management/#5_Delete_Contact
	$jsonResponse = STRegistry::Contacts()->Delete('CONTACTID');
	
	
	//****************************************************************************
	//******************************Hosts management******************************
	//****************************************************************************
	$host = new Host('ns1.demohost.st');
	$host->addIPv4('192.0.2.1');
	$host->addIPv4('192.0.2.2');
	$host->addIPv6('2001:db8:8:4::2');

	//Perform host attributes validation on ST Registry side
	$isHostValid = $host->Validate();

	//Create valid host
	//Response format: http://kb.registry.st/rest-api/rest-host-management/#2_Create_Host
	if ($isHostValid) STRegistry::Hosts->Create($host);
	
	//Validate if host exist in Registrar repository on ST Registry side
	if(STRegistry::Hosts->Exist('ns1.demohost.st')) {
		//Host exist
	} else {
		//Host not found
	}
	
	//Query host details
	//Response format: http://kb.registry.st/rest-api/rest-host-management/#3_Query_Host
	$jsonResponse = STRegistry::Hosts()->Query('ns1.demohost.st');
	
	//Convert JSON host details into host object
	$host = Host::fromJSON($jsonResponse);
	//output array of IPv4 addresses
	print_r($host->getIPv4());
	//output array of IPv6 addresses
	print_r($host->getIPv6());	
	
	//Updating host attributes
	$host->removeIPv4('192.0.2.2');
	$host->addIPv4('192.0.2.3');
	$host->removeIPv6('2001:db8:8:4::2');
	
	//Update host attributes
	//Outcome for this operation will be host without IPv6 addresses and with following IPv4 addresses: 192.0.2.1, 192.0.2.3
	//Response format: http://kb.registry.st/rest-api/rest-host-management/#4_Update_Host
	$jsonResponse = STRegistry::Hosts()->Update($host);
	
	//Another way to update existing host
	$host = new Host('ns1.demohost.st'); 
	$host->addIPv4('192.0.2.1');
	$host->addIPv4('192.0.2.3');

	//Update host attributes
	//Outcome for this operation will be updated host without IPv6 addresses and with following IPv4 addresses: 192.0.2.1, 192.0.2.3
	$jsonResponse = STRegistry::Hosts()->Update($host);
	
	//You can even copy all attributes from existing host and create new one
	$host->setName('ns2.demohost.st');
	$jsonResponse = STRegistry::Hosts()->Create($host);
	
	//Get contacts using search rules/criteria
	$criteria = new SearchCriteria('or'); //default logical operation is 'and' but this can be redefined 'or'
	$criteria->name->like('%.hostname.st')
					->v4->equal('192.0.2.1')
					->v6->equal('2001:db8:8:4::2')
					->crDate->greaterThan(strtotime('-5 days'))
					->status->notEqual('linked');
	$jsonResponse = STRegistry::Hosts->Search($criteria, $limit = 100, $offset = 0, $sort = array('name' => 'asc'));

	//Delete existing host (unused)
	//Response format: http://kb.registry.st/rest-api/rest-host-management/#5_Delete_Host
	$jsonResponse = STRegistry::Hosts->Delete('ns1.demohost.st');
	
	//****************************************************************************
	//*************************Poll messages management***************************
	//****************************************************************************
	
	//Query poll messages
	//Limit and Offset are required
	//Result also contain summary information about poll messages queue
	$limit = 10; //Limit result
	$offset = 0; //starting position offset
	$jsonResponse = STRegistry::Poll()->Request($limit, $offset);
	
	//Acknowledge poll message (mark as read and exclude from poll queue)
	$messageId = 1;
	$jsonResponse = STRegistry::Poll()->Ack($messageId);
	
	//****************************************************************************
	//*************************Registrar details***************************
	//****************************************************************************
	//return all details about current registrar like profile and billing plan details
	$jsonResponse = STRegistry::Client()->Query();
	

	//return client blling records
	$jsonResponse = STRegistry::Client()->billingRecords();
	//****************************************************************************
	//*****************************Search criterias*******************************
	//Usage of SearchCriteria to apply filters for querying Domains/Contacts/Hosts
	//collection within Registrar repository.
	//Possible instructions:
	//1. like - used to match pattern using '%'
	//2. notLike - used to match pattern using '%'
	//3. greaterThan - equal to instruction '>'
	//4. lowerThan - equal to instruction '<'
	//5. greaterThanEqual - equal to instruction '>='
	//6. lowerThanEqual - equal to instruction '<='
	//5. equal - equal to instruction '='
	//6. notEqual - equal to instruction '!='
	//Instructions 'equal' and 'notEqual' accept string or array as arguments
	//****************************************************************************
	
	//Search for domains within Registrar repository
	//It is possible to apply search criteria for following domain attribute(s):
	//1. name - domain name
	//2. crDate - domain registration date
	//3. upDate - domain update date
	//4. exDate - domain expire date
	//5. crId - ID of Registrar who originally registered domain
	//6. rewgistrant - domain registrant
	//7. contacts - all domain contacts(registrant, admin, technical and billing contacts)
	//8. ns - nameservers
	//9. status - domain status(es)
	
	//Search for domain(s) with name that end with 'name.st'
	$criteria = new SearchCriteria(); //default logical operation is 'and' but this can be redefined 'or'
	$criteria->name->like('%name.st');
	//Search fom domain(s) with registration date greater than -15 days from now
	$criteria->crDate->greaterThan(strtotime('-15 days'));
	//it is also possible to define several rules with one line of code
	//For example additionally to previous rules query domains that was originally registered by specific registrar ID and have expire date within next month
	$criteria->registrant->equal('REGISTRANTID')
				->exDate->greaterThan(strtotime('+1 month'))
				->exDate->lowerThan(strtotime('+2 months'));
				
	//We can also search for domains with some 
	$jsonResponse = STRegistry::Domains->Search($criteria, $limit = 100, $offset = 0, $sort = array('exDate' => 'desc'));

	//Search for domains with status clientTransferProhibited and clientUpdateProhibited 
	$criteria->status->equal(array('clientTransferProhibited','clientUpdateProhibited'));
	//We can exclude offline/expired domains to get more accurate list
	$criteria->status->notEqual('serverHold');
	$jsonResponse = STRegistry::Domains->Search($criteria, $limit = 100, $offset = 0, $sort = array('exDate' => 'desc'));
	 
	//Search for contact(s) within Registrar repository
	//It is possible to apply search criteria for following contact attribute(s):
	//1. id - contact id
	//2. voice - phone number
	//3. fax - fax number
	//4. email - contact email address
	//5. crDate - contact creation date 
	//6. name - contact name
	//7. org - organisation name
	//8. street - contact street address (search performed in street1, street2 and street3 values)
	//9. city -  city name
	//10. pc - postal code
	//11. cc - 2 letter country code
	//12. sp - state/province
	//13. assignedTo - domains which use contact
	//14. crDate - date when contact was created
	//15. upDate - date when contact was updated

	//Search for contacts with email address that end with 'example.st'
	$criteria = new SearchCriteria(); //default logical operation is 'and' but this can be redefined 'or'
	$criteria->email->like('%email.st');
	
	//We can also fetch for all contacts that are used in certain domains
	$criteria->assignedTo->equal(array('domain1.st', 'domain2.st'));
	$jsonResponse = STRegistry::Contacts->Search($criteria, $limit = 100, $offset = 0, $sort = array('name' => 'asc'));
	
	//Search for host(s) within Registrar repository
	//It is possible to apply search criteria for following host attribute(s):
	//1. name - host name
	//2. crDate - date when host was created
	//3. upDate - date when host was updated
	//4. v4 - IPv4 address(es)
	//5. v6 - IPv6 address(es)
	//6. status - host status(es)
	//7. assignedTo - domains which use contact
	
	//Example of how to search for host(s) (GLUE records) that is using IP 192.0.2.1 OR host is used by domain domain1.st
	$criteria = new SearchCriteria('or'); //default logical operation is 'and' but this can be redefined 'or'
	$criteria->v4->equal('192.0.2.1');
	$criteria->assignedTo->equal('domain1.st');
	
	$jsonResponse = STRegistry::Hosts->Search($criteria, $limit = 100, $offset = 0, $sort = array('name' => 'asc'));
	
	//We can also fetch all hosts that are not used with any domains
	$criteria = new SearchCriteria(); //default logical operation is 'and' but this can be redefined 'or'
	$criteria->status->notEqual('linked');
	
	$jsonResponse = STRegistry::Hosts->Search($criteria, $limit = 100, $offset = 0, $sort = array('name' => 'asc'));
?>