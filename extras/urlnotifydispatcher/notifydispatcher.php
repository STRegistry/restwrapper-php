<?php

$notificationTypes = array('billing', 'lifecycle', 'transfers');
$notificationType = @$_GET['ntype'];
$notification = json_decode(file_get_contents("php://input"), true);

if (empty($notificationType) || !in_array($notificationType, $notificationTypes)) {
	$notificationType = __detectNotificationType($notification);	
}

if (!$notificationType) exit;

$printable = var_export($notification, true);
$logFileName = sprintf("%s_%s.log", $notification['event'], date('YmdHi'));

file_put_contents(sprintf("./notify_log/%s/%s", $notificationType, $logFileName), $printable . PHP_EOL, FILE_APPEND);

function __detectNotificationType($notification)
{	
	$event = $notification['event'];
	if (in_array($event, array('DOMAIN_14D_EXPIRE_NOTICE', 'DOMAIN_30D_EXPIRE_NOTICE', 'DOMAIN_EXPIRE', 'DOMAIN_REDEMPTION', 'DOMAIN_DELETE'))) {
		return 'lifecycle';
	}
	if (in_array($event, array('INSUFFICIENT_FUNDS', 'BALANCE_LOW', 'AUTO_INVOICE', 'UNPAID_INVOICES'))) {
		return 'billing';
	}
	if (in_array($event, array('TRANSFER_REQUEST', 'TRANSFER_CANCEL', 'TRANSFER_REJECT', 'TRANSFER_SUCCESS', 'TRANSFER_FORBIDDEN'))) {
		return 'transfers';
	}

	return false;
}