<?php

	// COBRO AL TOKEN ON DEMAND
	require realpath(dirname( __FILE__ ) . '/main.php');
	try {
		$c->reference_code( time() );
		$c->reconcile_code('R' . time() );
		$c->charge_subscription('5664149557546656604009', '75', 'DOP');
	} catch ( Exception $e ) {
		echo $e->getCode() . ': ' . $e->getMessage() . '<br />';
	} 
	echo '<pre>';
		print_r( $c->request );
		print_r( $c->response );
	echo '</pre>';
// EOL