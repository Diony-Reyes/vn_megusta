<?php
    use Psr\Http\Message\ResponseInterface as Response;
    use Psr\Http\Message\ServerRequestInterface as Request;

    require __DIR__.'/../models/Doctors_Model.php';
    
    // $app = new Slim\App();
    
    // GET doctor by id
    $app->get("/doctor/{id}", function(Request $request, Response $response, $arg) {
        $doctor_model = new Doctors_Model();

        $doctor_id = $arg['id'];
        $result = $doctor_model->get_doctor_by_id($doctor_id);

        return $response->write($result);
    });
    
?>
