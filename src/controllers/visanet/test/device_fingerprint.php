<?php

	require realpath(dirname( __FILE__ ) . '/main.php');

	$c->card( '4111111111111111', '12', '2022', '123' )
		->bill_to( array(
			'firstName' => 'John',
			'lastName' => 'Doe',
			'street1' => '123 Main Street',
			'street2' => 'Apple Building',
			'city' => 'Columbia',
			'state' => 'SC',
			'postalCode' => '29201',
			'country' => 'US',
			'email' => 'john.doe@example.com',
			'phoneNumber' => '+662-2962000'
		) );

	$c->reference_code('BAY' . time() );
	$c->device_fingerprint_id($_GET['df_id']);
	
	// $c->authorize('5.55'); // DOP
	$c->authorize('1572.0', 'DOP');

	$reference_code = $c->response->merchantReferenceCode;
	$amount         = $c->response->ccAuthReply->amount;
	$currency       = $c->response->purchaseTotals->currency;
	$request_id     = $c->response->requestID;

	$link = "capture_auth_request_id.php"
	      . "?reference_code=" . $reference_code
	      . "&amount=" . $amount
	      . "&currency=" . $currency
	      . "&request_id=" . $request_id;

?>

<a href="<?php echo $link ?>">capture_auth_request_id</a>

<?php

	echo '<pre>';
	print_r( $c->request );
	print_r( $c->response );
	echo '</pre>';

// EOL