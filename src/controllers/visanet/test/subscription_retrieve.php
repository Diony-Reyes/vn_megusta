<?php

	require realpath(dirname( __FILE__ ) . '/main.php');
	
	try {

		// $subscription_id = '5053459900976821203008';
		$subscription_id = '5665067606486498804011';
		$c->reference_code($subscription_id);
		$subscription = $c->retrieve_subscription($subscription_id);
		print_r($subscription);
	}
	catch ( Exception $e ) {
		echo $e->getCode() . ': ' . $e->getMessage() . '<br />';
	}
	
	echo '<pre>';
	print_r( $c->request );
	echo '---<--br>';
	print_r( $c->response );
	echo '</pre>';
// EOL