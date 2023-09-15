<?php
/**
 * Testa a criação de cursos via webservice
 */
require_once('soap_test.class.php');
require_once('../classes/alfa.class.php');
require_once('../../../config.php');

/**
 * Class de auxílio para chamar a função do webservice do Moodle
 */
class alfa_create_course extends soap_test{
    private $course = null;
    function __construct(){
        parent::__construct('local_alfa_create_course',true);
    }
    function test(){
        return parent::execute_test($this->course);
    }
    function create_course($c){//just an alias for 'test'
        $this->course = $c;
        return $this->test();
    }
}//


/*
 * page start
 */
if(!is_siteadmin() && !in_array($USER->username, $CFG->eadusers)){
    die('Você não tem permissão de executar este script.');
}



//86407;85038;85039;86655;
if(isset($_POST['idnumbers'])){ 
    if(isset($_POST['idnumbers'])){ 
        $idnumbers = explode(';', rtrim($_POST['idnumbers'],';'));

        foreach($idnumbers as $idnumber){
            if(!empty($idnumber)){
                $c = Alfa::getCourseInformation($idnumber);//85752//busca informações de uma oferta e cria o ambiente
                $sql = "SELECT id, 
                               idnumber,
                               courseid
                          FROM {local_alfa}
                         WHERE idnumber = ?";
                $course = $DB->get_records_sql($sql, array($idnumber));
                if(!$course){
                    $createcourse = new alfa_create_course($c);
                    $courseid = $createcourse->create_course($c);
                    $linkead = $CFG->wwwroot.'/course/view.php?id='.$courseid;
                    Alfa::updateLinkEaD(array($idnumber),$linkead);
                    mtrace('Criando curso: <a href="'.$linkead.'" target="_blank">'.$idnumber.' - '.$c['fullname'].'</a>');
                    
                }else{
                    $linkead = $CFG->wwwroot.'/course/view.php?id='.array_shift($course)->courseid;
                    mtrace('<font color="red">O curso <a href="'.$linkead.'" target="_blank">'.$idnumber.' - '.$c['fullname'].'</a> já existe no Moodle.</font>');
                }
                mtrace("<br />\n");
                ob_flush();
            }
        }
    }

    mtrace('<br /><br />');
    mtrace('<a href="'.$CFG->wwwroot.'/local/alfa/tests/nead_criar_cursos.php" >Voltar</a>');
    die();
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf8" />
    <title>Nead - Criar cursos</title>
</head>
<body>
    <form action="nead_criar_cursos.php" method="post">
        <textarea name="idnumbers" id="idnumbers" rows="10" cols="80" placeholder="Adicione os código de oferta das disciplinas separados por ponto e vírgula."></textarea>
        <br />
        <input type="submit" value="Criar cursos" />
    </form>
</body>
</html>
