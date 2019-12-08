<?php

trait VisitActions
{   

    // use ExportVisits;
    public $table_visits =  'visits';
    
    public function registerVisit($data) {
        if (!array_key_exists('id_lead', $data)) {
            $data['id_lead'] = 0;
        }
        $lng = '--';
        $lat = '--';
        $comment = '--';
        if (array_key_exists('lng', $data)) {
            $lng = $data['lng'];
        }
        if (array_key_exists('lat', $data)) {
            $lat = $data['lat'];
        }
        if (array_key_exists('comment', $data)) {
            $comment = $data['comment'];
        }
        if ( $data['grupo_catalogo'] == 57 && $data['id_item'] == 9) {
            self::sendCom($data['id_com'], $data['id_entidad'], $data['id_lead']);
        }
        $insert = [
            'id_item' => $data['id_item'],
            'id_entidad' => $data['id_entidad'],
            'id_com' => $data['id_com'],
            'id_recogida' => $data['id_recogida'],
            'grupo_catalogo' => $data['grupo_catalogo'],
            'id_lead' => $data['id_lead'],
            'lat' => $lat,
            'lng' => $lng,
            'comment' => $comment,
            
        ];
        
        if (!__Database::__insert($this->table_visits, $insert)) {
            print_r(mysqli_error(__Database::__dbConnection()));
        }
        return  true;
    }


    public function savecoords() {
        $data = $_POST;
        unset($data['user']);
        if (!__Database::__insert('comisionador_coords', $data)) {
            print_r(mysqli_error(__Database::__dbConnection()));
        }
        Http::response(['ok']);
    }

    public function editVisitAction($grupo, $item) {
        $pk = $this->GetRouter()->GetUrlParam('id');
       
        __Database::__update($this->table_visits, [
            'grupo_catalogo' => $grupo,
            'id_item' => $item
        ], "id = {$pk}");
    }
    

    public static function sendCom($id_com, $id_entity, $lead = null) {
        $sql = "SELECT *
        FROM localidades 
        WHERE id_entity = {$id_entity}";
       if ($qry = mysqli_query(__Database::__dbConnection(), $sql)) {
           $row = mysqli_fetch_array($qry);
            if ($id_entity) {
                $text_lead_or_entity = ", ID: ". $id_entity;
            } else {
                $text_lead_or_entity = ", Lead: ". $lead;
            }

           $message = "Visitar: ". $row['nombre']. $text_lead_or_entity;
           self::insertNotificationCom($id_com, $message);
       }

    }

    public static function insertNotificationCom($id_com, $message) {
        $insert = [
            'id_com' => $id_com,
            'message' => $message,
        ];
        if (!__Database::__insert('comisionador_notifications', $insert)) {
            print_r(mysqli_error(__Database::__dbConnection()));
        }

    }
 
    public static function notifyClientViaPush($id_entidad, $id_com, $message, $title = 'Matrices') {

            $curl = curl_init();

                
                $postf = [
                'id_entidad' => $id_entidad,
                'id_comisionador' => $id_com,
                'mensaje' => $message,
                'titulo' => $title,
                ];

                curl_setopt_array($curl, array(
                CURLOPT_URL => "http://matricesrecargas.com/preventa-api/comisionador/clientMessage",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => http_build_query($postf),
                CURLOPT_HTTPHEADER => array(
                    "Cache-Control: no-cache",
                    "Content-Type: application/x-www-form-urlencoded",
                    "Postman-Token: ffce8c7b-8b78-4896-a0bf-c345a0e836e3"
                ),
                ));

                $response = curl_exec($curl);
                $err = curl_error($curl);

                curl_close($curl);

                if ($err) {
                    // echo "cURL Error #:" . $err;
                } else {
                    // echo $response;
                }
    }



    public function Unaccent($string)
{
    return preg_replace('~&([a-z]{1,2})(?:acute|cedil|circ|grave|lig|orn|ring|slash|th|tilde|uml|caron);~i', '$1', htmlentities($string, ENT_COMPAT, 'UTF-8'));
}

    
    // public function getPieData() {
    //     return [
           
    //     ];
    //     // return [
    //     //     ['Con', 'Yuca'],
    //     //     ['V', (int)10],
    //     //     ['C', (int)2],
    //     //     ['Cd', (int)3],
    //     //     ['Cf',(int)5],
    //     //     ['Cg', (int)5],
    //     // ];
    // }

    public function getVisitsClientsData() {

        $where = '1 ';
        $case = '1 ';
        $caseE= '1 ';
        $caseE2= '1 ';

        $where_rec = '1 ';
        $fdd = ' ';
        $com_ar = '';
        $com_v = '';
        $com_r = '';
        if (array_key_exists('filter', $_GET)) {
            $filter = explode(',', $_GET['filter']);
            $pageno = $_GET['page'];
            $id_lead = $filter[0];
            $id_entidad = $filter[1];
            $comercio = $filter[2];
            $comisionador = $filter[3];
            $tipo_respuesta =$filter[4];
            $fd = $filter[5];
            $fh = $filter[6];
            $lvl = $filter[7];
          
           
            if (!empty(trim($id_entidad))) {
                $where .= " AND v.id_entidad like '%".$id_entidad. "%' ";
                $where_rec .= " AND r.id_cliente like '%".$id_entidad. "%' ";
               
            }
            if (!empty(trim($lvl))) {
                $where .= " AND c.nivel like '%".$lvl. "%' ";
            }
           
            if (!empty(trim($comisionador))) {
                $where .= " AND v.id_com = ".$comisionador. " ";
                $com_ar = " AND id_entidad in (SELECT distinct id_entidad FROM `entidades_comisionadores` WHERE id_comisionador = ".$comisionador. ")";
                $com_v = "  AND v.id_entidad in (SELECT distinct id_entidad FROM `entidades_comisionadores` WHERE id_comisionador =  ".$comisionador. ")";
                $com_r = " AND r.id_cliente in (SELECT distinct id_entidad FROM `entidades_comisionadores` WHERE id_comisionador = ".$comisionador. ")";
            }
            if (!empty(trim($fd))) {
                $where .= " AND date(v.fecha) >= '".$fd."' ";
                $case .= " AND date(ar.fecha) >= date('".$fd."') ";
                $caseE .= " AND date(ed.fecha_alta) >= date('".$fd."') ";
                $caseE2 .= " AND date(e.fecha_bloqueado) >= date('".$fd."') ";
                $where_rec .= " AND date(r.fecha_solicitud) >= date('".$fd."') ";
             $fdd .= " AND date(fecha) >= date('".$fd."') ";
            }
            if (empty(trim($fd))) {
                $where .= " AND date(v.fecha) >= date(now()) ";
                $case .= " AND date(ar.fecha) >=date(now()) ";
                $caseE .= " AND date(ed.fecha_alta) >=date(now()) ";
                $caseE2 .= " AND date(e.fecha_bloqueado) >=date(now()) ";
                $where_rec .= " AND date(r.fecha_solicitud) >=date(now()) ";
             $fdd .= " AND date(fecha) >=date(now()) ";
            }
            if (!empty(trim($fh))) {
                $where .= " AND date(v.fecha) <= '".$fh."' ";
                $case .= " AND date(ar.fecha) <= date('".$fh."') ";
                $caseE .= " AND date(ed.fecha_alta) <= date('".$fh."') ";
                $caseE2 .= " AND date(e.fecha_bloqueado) <= date('".$fh."') ";
                $where_rec .= " AND date(r.fecha_solicitud) <= date('".$fh."') ";
                $fdd .= " AND date(fecha) <= date('".$fh."') ";
            }
            if (empty(trim($fh))) {
                $where .= " AND date(v.fecha) <= date(now()) ";
                $case .= " AND date(ar.fecha) <= date(now()) ";
                $caseE .= " AND date(ed.fecha_alta) <= date(now()) ";
                $caseE2 .= " AND date(e.fecha_bloqueado) <= date(now()) ";
                $where_rec .= " AND date(r.fecha_solicitud) <= date(now()) ";
                $fdd .= " AND date(fecha) <= date(now()) ";
            }
        }
       
        // if (trim($case) == '1') {
        //     $sql = "SELECT 
        //     0 as total,
        //     0 as activo,
        //     0 as bloqueado,
        //     0 as other_entity_status,
        //     0 as no_interesa_lc,
        //     0  as leads,
        //     0 as cantidad_prestamos,
        //     0 as monto_prestamos
        //     from visits v 
        //     WHERE 1 limit 1";
        // } else {
            // count( case when {$caseE2}  AND e.status = 9 then 1 else null end)  as bloqueado,
            // count( case when {$caseE} AND e.status = 1 then 1 else null end)  as activo,
        //         $sql = "(SELECT 
        //         count( case when   e.status = 9 then 1 else null end)  as bloqueado,
        //         count( case when  e.status = 1 then 1 else null end)  as activo,
        //         count( case when  (e.status != 1 and  e.status != 9) then 1 else null end)  as others,
        //         0  as others_2,
        //         count( case when v.id_item = 11 and v.grupo_catalogo = 57 then 1 else null end)  as no_interesa_lc,
        //         count(case when {$case} then 1 else null end) as cantidad_prestamos,
        //         sum( case when {$case} then ar.monto else 0 end) as monto_prestamos
        //     from visits v 
        //     inner join entidades e on e.id = v.id_entidad
        //     inner join entidades_detalle ed on e.id_detalle = ed.id
        //     left join asignacion_recurrente ar on ar.id_entidad = e.id and ar.dias_faltantes != 0
        //     WHERE {$where} )
            
        //     UNION ALL
        //     (Select 
        //        0 as bloqueado,
        //        0 as activo,
        //        count(r.id)  as others,
        //        0  as others_2,
        //        0  as no_interesa_lc,
        //        0 as cantidad_prestamos,
        //        0 as monto_prestamos
        //  FROM 
        //    recogida r 
        //     left join entidades e on e.id = r.id_cliente
        //     left join leads l on l.id_entidad = r.id_cliente
        //     left join localidades lol on lol.id_entity = r.id_cliente 
        //     left join entidades_detalle ed on e.id_detalle = ed.id 
        //     left join catalogo gc on gc.id = null
        //     WHERE {$where_rec}  and r.monto_recogido = 0 and r.status = 0 
        //     and r.control_carioca != 100  order by r.id desc )
        //     ";

        $sql = "
        select count( c)   as total, 

count( case when  status = 1   then 1 else null end)  as activo ,
  count( case when   status = 9 then 1 else null end)  as bloqueado,
  count( case when   (status != 9 and status != 1 ) then 1 else null end)  as other_entity_status,
  
    count( case when ( id_lead != 0 and id_item = 11 and grupo_catalogo = 57 ) then 1 else null end)  as no_interesa_lc,
  sum( case when   id_lead != 0 then 1 else null end)  as leads,
  (select  
   count(id) as cantidad_prestamos from asignacion_recurrente where 1 {$com_ar}  {$fdd}  and dias_faltantes != 0 ) as cantidad_prestamos,
   (select  
   sum(monto) as montop from asignacion_recurrente where   1 {$com_ar} {$fdd}  and dias_faltantes != 0 ) as monto_prestamos
from ((
SELECT
    e.status,
    v.id_entidad as c,
    v.id_lead,
    v.grupo_catalogo,
    v.id_item
    
from
   visits v 
   inner join
      comisionadores c 
      on c.id = v.id_com 
   inner join
      catalogo gc 
      on gc.id_item = v.id_item 
      and gc.grupo_catalogo = v.grupo_catalogo 
     left join entidades e on e.id = v.id_entidad
   
    
where
   {$where}  {$com_v}
) 
UNION ALL

(
select
       e.status,
    r.id_cliente as c ,
   0     as id_lead,
    0  as grupo_catalogo,
    0 as id_item
FROM
   recogida r 
   left join
      entidades e 
      on e.id = r.id_cliente 
   left join
      leads l 
      on l.id_entidad = r.id_cliente 
   left join
      localidades lol 
      on lol.id_entity = r.id_cliente 
   left join
      entidades_detalle ed 
      on e.id_detalle = ed.id 
   left join
      catalogo gc 
      on gc.id = null 
WHERE
   {$where_rec} {$com_r}
   and r.monto_recogido = 0 
   and r.status = 0 
   and r.no_pay_reason = 0 
   and r.control_carioca != 100 
order by
   r.id desc ) ) as tmp
        
        ";

        // }

        // print_r($sql);die();
    
    mysqli_set_charset(__Database::__dbConnection(),"utf8");
    $qry = mysqli_query(__Database::__dbConnection(), $sql);
    // $clients = [];
    // while ($row = mysqli_fetch_array($qry) )  {
    //     $clients[] = $row;
    // }

   return mysqli_fetch_array($qry);

    }
    public function getAllVisitsData() {


        $where = '1 ';
        $where_rec = '1 ';
        $inner_ec = '';
        $where_ec = "  ";
        if (array_key_exists('filter', $_GET)) {
            $filter = explode(',', $_GET['filter']);
            $pageno = $_GET['page'];
            $id_lead = $filter[0];
            $id_entidad = $filter[1];
            $comercio = $filter[2];
            $comisionador = $filter[3];
            $tipo_respuesta =$filter[4];
            $fd = $filter[5];
            $fh = $filter[6];
            $lvl = $filter[7];

            if (!empty(trim($id_lead))) {
                $where .= " AND v.id_lead = ".$id_lead. " ";
                $where_rec .= " AND l.id = ".$id_lead. " ";
            }
            if (!empty(trim($id_entidad))) {
                $where .= " AND v.id_entidad like '%".$id_entidad. "%' ";
                $where_rec .= " AND r.id_cliente like '%".$id_entidad. "%' ";
                
            }
            if (!empty(trim($lvl))) {
                $where .= " AND c.nivel like '%".$lvl. "%' ";
            }
            if (!empty(trim($comercio))) {
                $where .= " AND (l.nombre_comercio like '%".$comercio. "%' ";
                $where .= " OR concat(ed.nombre, '  ', ed.apellido )  like '%".$comercio. "%') ";

                $where_rec .= " AND (l.nombre_comercio like '%".$comercio. "%' ";
                $where_rec .= " OR concat(ed.nombre, '  ', ed.apellido )  like '%".$comercio. "%') ";
            }
            if (!empty(trim($comisionador))) {
                $where .= " AND v.id_com = ".$comisionador. " ";
                $inner_ec = ' inner join entidades_comisionadores ec on ec.id_entidad = e.id ';
                $where_ec = " AND ec.id_comisionador =  ".$comisionador. " ";
            }
            if (!empty(trim($tipo_respuesta))) {
                $tipo_respuesta = explode(':', $filter[4]);
                $where .= " AND v.id_item = ".$tipo_respuesta[0]. " ";
                $where .= " AND v.grupo_catalogo = ".$tipo_respuesta[1]. " ";
            }
           
            if (!empty(trim($fd))) {
                $where .= " AND date(v.fecha) >= date('".$fd."') ";
                $where_rec .= " AND date(r.fecha_solicitud) >= date('".$fd."') ";
            } else  {
                $where .= " AND date(v.fecha) >= date(now()) ";
                $where_rec .= " AND date(r.fecha_solicitud) >= date(now()) ";
            }
            if (!empty(trim($fh))) {
                $where .= " AND date(v.fecha) <= date('".$fh."') ";
                $where_rec .= " AND date(r.fecha_solicitud) <= date('".$fh."') ";
            }
        }

        $no_of_records_per_page = 10;
        $offset = ($pageno-1) * $no_of_records_per_page; 
        
       
        $total_rows = $this->getAllVisitsDataTotal();

        $total_pages = ceil($total_rows / $no_of_records_per_page);

        $sql = "(SELECT 
            v.id as cnrid, 
            v.id_recogida as cnrid_recogida, 
            v.grupo_catalogo as cnr_grupo_catalogo, 
            v.id_lead as cnrid_lead, 
            v.fecha as cnrdate , date(v.fecha) as cnrdate_2, 
            v.lat as cnrlat , v.lng as cnrlng, 
            c.nombre as c_nombre, c.apellido as c_apellido, 
            concat(ed.nombre, '  ', ed.apellido ) as e_display_name, 
            trim(concat(lol.nombre, ' (',concat(ed.nombre, '  ', ed.apellido ), ')')) as empresa, 
            ed.nombre as e_nombre, 
            ed.apellido as e_apellido, 
            lol.nombre as nombre_empresa_localidad, 
            gc.* ,
            l.*,
            (case when ed.nombre is null then l.nombre_contacto else  concat(ed.nombre, '  ', ed.apellido ) end ) as e_display_name
        FROM visits v 
        inner join comisionadores c on c.id = v.id_com 
        left join entidades e on e.id = v.id_entidad 
        left join leads l on l.id = v.id_lead
        left join localidades lol on lol.id_entity = v.id_entidad
        left join entidades_detalle ed on e.id_detalle = ed.id 
        inner join catalogo gc on gc.id_item = v.id_item and gc.grupo_catalogo = v.grupo_catalogo
        WHERE {$where} group by v.id order by v.id desc limit {$offset}, {$no_of_records_per_page})";

        $sql .= " UNION ALL
        (SELECT 
        0 as cnrid
        , r.id as cnrid_recogida
        , 0 as cnr_grupo_catalogo
        , l.id as cnrid_lead
        , r.fecha_solicitud as cnrdate
         , date(r.fecha_solicitud) as cnrdate_2
        , r.latitud as cnrlat
         , r.longitud as cnrlng
        ,'--' as c_nombre
        , '--' as c_apellido
        , concat(ed.nombre, ' ', ed.apellido ) as e_display_name
        , trim(concat(lol.nombre, ' (',concat(ed.nombre, ' ', ed.apellido ), ')')) as empresa
        , ed.nombre as e_nombre
        , ed.apellido as e_apellido
        , lol.nombre as nombre_empresa_localidad
        , gc.* , l.*, 
         concat(ed.nombre, ' ', ed.apellido ) as e_display_name
         FROM 
           recogida r 
            left join entidades e on e.id = r.id_cliente
            {$inner_ec}
            left join leads l on l.id_entidad = r.id_cliente
            left join localidades lol on lol.id_entity = r.id_cliente 
            left join entidades_detalle ed on e.id_detalle = ed.id 
            left join catalogo gc on gc.id = null
            WHERE {$where_rec}  {$where_ec} and r.monto_recogido = 0 and r.status = 0 
            and r.control_carioca != 100 group by r.id order by r.id desc limit {$offset}, {$no_of_records_per_page})
            order by cnrid desc 
            ";

        
        //  print_r($sql);die();
           mysqli_set_charset(__Database::__dbConnection(),"utf8");
        $qry = mysqli_query(__Database::__dbConnection(), $sql);
        $clients = [];
        while ($row = mysqli_fetch_array($qry) )  {
            $clients[] = $row;
        }

       
        return [
            'clients' => $clients,
            'total' => $total_pages
        ];

    }
    public function getAllVisitsDataTotal() {

        $where = '1 ';

        if (array_key_exists('filter', $_GET)) {
            $filter = explode(',', $_GET['filter']);
            $id_lead = $filter[0];
            $id_entidad = $filter[1];
            $comercio = $filter[2];
            $comisionador = $filter[3];
            $tipo_respuesta =$filter[4];
            $fd = $filter[5];
            $fh = $filter[6];
            $lvl = $filter[7];


            $where_rec = '1 ';
            $inner_ec = '';
            $where_ec = "  ";
    
            if (!empty(trim($lvl))) {
                $where .= " AND c.nivel like '%".$lvl. "%' ";
            }

            if (!empty(trim($id_lead))) {
                $where .= " AND v.id_lead = ".$id_lead. " ";

            }
            if (!empty(trim($id_entidad))) {
                $where .= " AND v.id_entidad like '%".$id_entidad. "%' ";
                $where_rec .= " AND r.id_cliente like '%".$id_entidad. "%' ";

            }
            // if (!empty(trim($comercio))) {
            //     $where .= " AND (l.nombre_comercio like '%".$comercio. "%' ";
            //     $where .= " OR concat(ed.nombre, '  ', ed.apellido )  like '%".$comercio. "%') ";
            // }
            if (!empty(trim($comisionador))) {
                $where .= " AND v.id_com = ".$comisionador. " ";
                $inner_ec = ' inner join entidades_comisionadores ec on ec.id_entidad = e.id ';
                $where_ec = " AND ec.id_comisionador =  ".$comisionador. " ";
            }
            if (!empty(trim($tipo_respuesta))) {
                $tipo_respuesta = explode(':', $filter[4]);
                $where .= " AND v.id_item = ".$tipo_respuesta[0]. " ";
                $where .= " AND v.grupo_catalogo = ".$tipo_respuesta[1]. " ";
            }


            if (!empty(trim($fd))) {
                $where .= " AND date(v.fecha) >= '".$fd."' ";
                $where_rec .= " AND date(r.fecha_solicitud) >= date('".$fd."') ";

            } else {
                $where .= " AND date(v.fecha) >= date(now()) ";
                $where_rec .= " AND date(r.fecha_solicitud) >= date(now()) ";
            }
            if (!empty(trim($fh))) {
                $where .= " AND date(v.fecha) <= '".$fh."' ";
                $where_rec .= " AND date(r.fecha_solicitud) <= date('".$fh."') ";
            }
        }

        $sql = "(SELECT v.id
        FROM visits v
        inner join comisionadores c on c.id = v.id_com 
        WHERE {$where} group by v.id order by v.id )
        UNION ALL 
        (SELECT r.id
        FROM recogida r
        left join entidades e on e.id = r.id_cliente
        {$inner_ec}
        WHERE {$where_rec} {$where_ec} and r.monto_recogido = 0 and r.status = 0 
        and r.control_carioca != 100 group by r.id )
        ";
           mysqli_set_charset(__Database::__dbConnection(),"utf8");
        $qry = mysqli_query(__Database::__dbConnection(), $sql);
        $clients = [];
        while ($row = mysqli_fetch_array($qry) )  {
            $clients[] = $row;
        }
        // print_r($sql);die();
        return count($clients);

    }
    public function getPieData() {

        $where = '1 ';
        $where_rec = '1 ';
        $inner_ec = '';
        $where_ec = ' ';
        if (array_key_exists('filter', $_GET)) {
            $filter = explode(',', $_GET['filter']);
            $id_lead = $filter[0];
            $id_entidad = $filter[1];
            $comercio = $filter[2];
            $comisionador = $filter[3];
            $tipo_respuesta =$filter[4];
            $fd = $filter[5];
            $fh = $filter[6];
            $lvl = $filter[7];
            if (!empty(trim($lvl))) {
                $where .= " AND c.nivel like '%".$lvl. "%' ";
            }
            if (!empty(trim($id_lead))) {
                $where .= " AND v.id_lead = ".$id_lead. " ";
            }
            if (!empty(trim($id_entidad))) {
                $where .= " AND v.id_entidad like '%".$id_entidad. "%' ";
                $where_rec .= " AND r.id_cliente like '%".$id_entidad. "%' ";
        
            }
            // if (!empty(trim($comercio))) {
            //     $where .= " AND (l.nombre_comercio like '%".$comercio. "%' ";
            //     $where .= " OR concat(ed.nombre, '  ', ed.apellido )  like '%".$comercio. "%') ";
            // }
            if (!empty(trim($comisionador))) {
                $where .= " AND v.id_com = ".$comisionador. " ";
                // $where_rec .= " AND v.id_com = ".$comisionador. " ";
                $inner_ec = ' inner join entidades_comisionadores ec on ec.id_entidad = e.id ';
                $where_ec = " AND ec.id_comisionador =  ".$comisionador. " ";
            }
            if (!empty(trim($tipo_respuesta))) {
                $tipo_respuesta = explode(':', $filter[4]);
                $where .= " AND v.id_item = ".$tipo_respuesta[0]. " ";
                $where .= " AND v.grupo_catalogo = ".$tipo_respuesta[1]. " ";
            }
           
            if (!empty(trim($fd))) {
                $where .= " AND date(v.fecha) >= date('".$fd."') ";
                $where_rec .= " AND date(r.fecha_solicitud) >= date('".$fd."') ";
        
            }
            if (empty(trim($fd))) {
                $where .= " AND date(v.fecha) >= date(now()) ";
                $where_rec .= " AND date(r.fecha_solicitud) >= date(now()) ";
        
            }
            if (!empty(trim($fh))) {
                $where .= " AND date(v.fecha) <= date('".$fh."') ";
                $where_rec .= " AND date(r.fecha_solicitud) <= date('".$fh."') ";
            }

            if (empty(trim($fh)) && empty(trim($fd))) {
                $where .= " AND date(v.fecha) = date(now())";
                $where_rec .= " AND date(r.fecha_solicitud) <= date(now())";
            }
        }

        // $sql = "SELECT  gc.descripcion2,  count(v.id_item) as c 
        // from visits v 
        // inner join comisionadores c on c.id = v.id_com 
        // inner join catalogo gc on gc.id_item = v.id_item and gc.grupo_catalogo = v.grupo_catalogo
        // where {$where}
        // group by v.grupo_catalogo , v.id_item";
        $sql = "(SELECT  gc.descripcion2,  count(v.id_item) as c 
        from visits v 
        inner join comisionadores c on c.id = v.id_com 
        inner join catalogo gc on gc.id_item = v.id_item and gc.grupo_catalogo = v.grupo_catalogo
        where {$where}
        group by v.grupo_catalogo , v.id_item) 
        UNION ALL
        (select 'No Visitado' as descripcion2, 
            count(r.id) as c 
            FROM 
            recogida r 
            left join entidades e on e.id = r.id_cliente
            {$inner_ec}
            left join leads l on l.id_entidad = r.id_cliente
            left join localidades lol on lol.id_entity = r.id_cliente 
            left join entidades_detalle ed on e.id_detalle = ed.id 
            left join catalogo gc on gc.id = null
            WHERE {$where_rec}  {$where_ec} and r.monto_recogido = 0 and r.status = 0   and r.no_pay_reason = 0
            and r.control_carioca != 100  order by r.id desc )";
        // print_r($sql);die();
           mysqli_set_charset(__Database::__dbConnection(),"utf8");
        $qry = mysqli_query(__Database::__dbConnection(), $sql);
        $clients = [];
        while ($row = mysqli_fetch_array($qry) )  {
            $clients[] = [
                 $row['descripcion2'],
                 $row['c'],
            ];
        }

        return ($clients);

    }
    public function getVisitsData() {

        $id_com = $_GET['id_com'];
        $start_date = $_GET['start_date'];
        $end_date = $_GET['end_date'];

        $sql = "SELECT 
            v.id as cnrid, 
            v.id_recogida as cnrid_recogida, 
            v.grupo_catalogo as cnr_grupo_catalogo, 
            v.fecha as cnrdate , date(v.fecha) as cnrdate_2, 
            c.nombre as c_nombre, c.apellido as c_apellido, 
            concat(ed.nombre, '  ', ed.apellido ) as e_display_name, 
            ed.nombre as e_nombre, 
            ed.apellido as e_apellido, 
            gc.* ,
            l.*,
            (case when ed.nombre is null then l.nombre_comercio else  concat(ed.nombre, '  ', ed.apellido ) end ) as e_display_name
        FROM visits v 
        inner join comisionadores c on c.id = v.id_com 
        left join entidades e on e.id = v.id_entidad 
        left join leads l on l.id = v.id_lead
        left join entidades_detalle ed on e.id_detalle = ed.id 
        inner join catalogo gc on gc.id_item = v.id_item and gc.grupo_catalogo = v.grupo_catalogo
        WHERE id_com = {$id_com} and v.fecha >= '{$start_date}' and v.fecha <= '{$end_date}' group by v.id order by v.id desc ";
            
        $qry = mysqli_query(__Database::__dbConnection(), $sql);
        $clients = [];
        while ($row = mysqli_fetch_array($qry) )  {
            $clients[] = $row;
        }
        return $clients;

    }


}