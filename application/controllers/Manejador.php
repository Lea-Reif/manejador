<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Manejador extends CI_Controller {


    public function __construct() {
        parent::__construct();        
            $this->load->model('Query','man');         
          }

	public function index()
	{
        $db = json_decode($this->man->getDb(),true);
        $this->layout->view('test',['dbs'=>$db]);
    }
    
    function string_between_two_string($str, $starting_word, $ending_word) 
{ 
    $subtring_start = strpos($str, $starting_word); 
    
    $subtring_start += strlen($starting_word);   
    //Length of our required sub string 
    $size = strpos($str, $ending_word, $subtring_start) - $subtring_start;   
    // Return the substring from the index substring_start of length size  
    return substr($str, $subtring_start, $size);   
} 
	public function ejecutar()
	{
        $postData = json_encode($this->input->post());
        $json_file = json_decode($this->man->getDb(),true);
        $data = json_decode($postData,true);
        $dbs= $json_file; 

        $errores = [];
        $correctas = [];
        $respuesta= [];
        foreach ($data['id'] as  $id) {

            foreach ($dbs as $key => $db) {
                if($id == $db['id']){
                    $myPDO = new PDO("mysql:host={$db['host']};dbname={$db['name']};port={$db['port']}", $db['user'], $db['pass']);
                    try{
                        if (strstr($data['query'], "CREATE  PROCEDURE")) {
                            $mysqli = new mysqli($db['host'], $db['user'], $db['pass'], $db['name'],$db['port']);
                            $nombre_proc = $this->string_between_two_string ($data['query'],"CREATE  PROCEDURE", '(');
                            if (!$mysqli->query("DROP PROCEDURE IF EXISTS $nombre_proc") ||  !$mysqli->query($data['query'])) {
                                array_push($errores, "Falló la creación del procedimiento almacenado: (" . $mysqli->errno . ") " . $mysqli->error);
                            } else {
                                array_push($correctas, $db['name']. " correcto");

                            }
                        } else if (strstr((strtolower($data['query'])), "select"))
                        {
                            $config['hostname'] = $db['host'];
                            $config['username'] = $db['user'];
                            $config['password'] = $db['pass'];
                            $config['database'] = $db['name'];
                            $config['dbdriver'] = 'mysqli';
                            $this->load->database($config);

                            $select = $this->db->query($data['query']);
                            
                            if ( $select) {
                                $select = $select->result_array();
                                $columnas= [];
                                foreach ($select[0] as $key => $value) {
                                    $columnas[] = $key;
                                }

                               $table = '<div class="card strpied-tabled-with-hover">
                                <div class="card-header ">
                                    <h4 class="card-title">Datos desde '. $db['db'].'</h4>
                                </div>
                                <div class="card-body table-full-width table-responsive">
                                    <table class="table table-wrapper table-hover table-striped">
                                    <thead>
                                    <tr>';

                                        foreach ($columnas as $value) {
                                            $value = ucwords($value);
                                            $table .= "<th>$value</th>";
                                        }

                                        $table .= '</tr></thead>
                                        <tbody>';

                                        foreach ($select as $item) {
                                            $table .= '<tr>';
                                            foreach ($item as  $val) {
                                                $table .= "<td>$val</td>";
                                            }
                                            $table .= '</tr>';
                                        }

                                        $table .= '</tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div></br>';
                                
                                array_push($correctas, $table);
                            } else {
                                array_push($errores,$this->db->error()['message']);

                            }
                            $this->db->close();

                        }
                         else{
                            $myPDO->beginTransaction();

                            $result= $myPDO->prepare($data['query']);
                            $pan= $result->execute();
                            if($pan == false){
                                if ($myPDO->inTransaction()) {
                                    $myPDO->rollback();
                                }
                                array_push($errores, "error en la Base de datos {$db['name']}: ".$result->errorInfo()[2]);
                                
                            }  else {
                                $myPDO->commit();
                                
                                array_push($correctas, $db['name']. " correcto");
                            }
                        }
                        
                            } catch(PDOException $e){
                                if ($myPDO->inTransaction()) {
                                    $myPDO->rollback();
                                }
                                array_push($errores,"error en la Base de datos {$db['name']}: ". $e->getMessage());
                            }
                }
            }

        }
        if(!empty($errores)){
            array_push($respuesta,['errores' =>$errores]);
        }else{
            array_push($respuesta,['errores' =>null]);
        }
        if(!empty($correctas)){
            array_push($respuesta,['correcto' =>$correctas]);
        } else{
            array_push($respuesta,['correcto' =>null]);
        }
        print_r( json_encode(['respuesta' =>$respuesta]));
    }
    
    public function consultas(){
        $consultas = $this->man->getConsultas();
        $this->layout->view('consultas',['consultas'=>$consultas]);

    }
    public function getConsultas(){
        $consultas = $this->man->getConsultas();
        return print_r(json_encode($consultas));
    }
    public function saveConsulta(){
        $postData = json_encode($this->input->post());
        $this->man->saveConsulta(json_decode($postData,true));
    }

    public function updateConsulta(){

        $postData = json_encode($this->input->post());
        $json_file = json_decode($this->man->getConsultas(),true);
        $data = json_decode($postData,true);
        $consultas= $json_file;
        $array_final=[];
        foreach ($consultas as $key => $consulta) {
            if($consulta['id'] == $data['id']){
                unset($data['id']);
                array_push($array_final,$data);
            }else{
                unset($consulta['id']);
                array_push($array_final,$consulta);
            }
            
        }


        $final_data = json_encode($array_final);
    file_put_contents(dirname(__FILE__,3).'\json\consultas.json', $final_data);

        return print_r($array_final);

    }

 public function prueba(){



$arrayquerys=['query1-bien','query2-bien','query3-bien','query4-mal','query5-bien'];

$arrayRollback = [];
        foreach ($arrayquerys as $b) {
            array_push($arrayRollback,"echo {$b}");
            echo $b;
            if (strpos($b, 'mal') !== false) { 
                echo "Aqui";
                break 1;  // this will break both foreach loops
            }
            // unset($arrayRollback);
        }
    if (!empty($arrayRollback)) { 
       foreach ($arrayRollback as $key => $rollback) {
           call_user_func($rollback);
       }
    }
 }

}


