<?php
    use Psr\Http\Message\ResponseInterface as Response;
    use Psr\Http\Message\ServerRequestInterface as Request;

    require __DIR__.'/../controllers/webservice.php';

    $app = new Slim\App();
    
    // get patient cards
    $app->get("/get_patient_cards/{id}", function(Request $request, Response $response, $arg) {
        $webservice = new Webservice();

        $patient_id = $arg['id'];
        $result = $webservice->vn_patient_cards($patient_id);

        return $response->write($result);
    });

    // get patient preferred card
    $app->get("/get_patient_prefereed_card/{id}", function(Request $request, Response $response, $arg) {
        $webservice = new Webservice();

        $patient_id = $arg['id'];
        $result = $webservice->vn_patient_get_preferred($patient_id);

        return $response->write($result);
    });
?>