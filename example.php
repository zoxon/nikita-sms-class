<?php
	$sms = new Vendors_NikitaSms();
	$sms->test = false;
	$sms->setLoginData('login', 'password', 'name');

	$messageUser = 'Sms example message';

	$phoneUser = '+996123123123';
	$phoneUser = preg_replace('/[^0-9\+]/', '', $phoneUser);
	$res = $sms->send($messageUser, $phoneUser);

	$smsResponce = new SimpleXMLElement($res['content']);

	if ($smsResponce->status == 0 || $smsResponce->status == 11) {
		echo 'Sms send successfully!';
	}

?>
