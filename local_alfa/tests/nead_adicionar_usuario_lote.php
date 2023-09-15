<style>
	textarea{
		width: 100%;
		height: 300px;
	}
</style>

<?php
/**
 * Testa a atribuição em lote de usuários em um curso
 */
require_once('soap_test.class.php');
require_once('../classes/alfa.class.php');
require_once('../../../config.php');

/*
 * page start
 */
if(!is_siteadmin() && !in_array($USER->username, $CFG->eadusers)){
    die('Você não tem permissão de executar este script.');
}

/*global $DB, $CFG;
$sql = "SELECT * FROM {user} WHERE username IN (?)";
$result = $DB->get_records_sql($sql, array('527813'));

echo "<pre>";
echo var_dump($result);
die();*/

/*
 * Recebe uma lista de usernames, verifica se eles existem no moodle
 * se não existem, busca informações no Alfa e adiciona, senão ignora o usuário
*/
$temp_user = 0;
if(isset($_POST['modifier'])){
    $temp_user = $USER->id;
    $USER->id = 0;
}

function create_users($usernames = null, $returnvalue = null, $all = false){

	global $DB, $CFG;
    require_once ($CFG->dirroot . '/user/lib.php');
    require_once ($CFG->dirroot . '/course/lib.php');
    require_once ($CFG->dirroot . '/lib/accesslib.php');
    require_once ($CFG->libdir . '/coursecatlib.php');



	if($usernames === null){
		return;
	}

	$placeholders = '';

	for($i = 0; $i < sizeof($usernames); $i++){
		$placeholders .= '?, ';
	}

	$placeholders = rtrim($placeholders, ', ');

	$sql = "SELECT * FROM {user} WHERE username IN (" . $placeholders . ")";
	$result = $DB->get_records_sql($sql, $usernames);

	$unexistentusernames = $usernames;

	foreach ($result as $line) {
		if(in_array($line->username, $unexistentusernames)){
			unset($unexistentusernames[array_search($line->username, $unexistentusernames)]);			
		}
	}

	if (!empty($unexistentusernames)) {
	    $newusers = Alfa::getUsersInformation($unexistentusernames);
	    if (!empty($newusers)) {
            // Get the polo and course field id to use later
            $polofieldid = $DB->get_record('user_info_field', array('shortname' => 'Polo'))->id;
            $coursefieldid = $DB->get_record('user_info_field', array('shortname' => 'Curso'))->id;
	        foreach ($newusers as $user) {
	            $user->lang = 'pt_br';//fix - O padrão do moodle é criar o usuário com lang 'en'
	            $userId = user_create_user($user);

                // Altera informações de polo e curso
                $info1 = new stdClass();
                $info1->userid = $userId;
                $info1->fieldid = $polofieldid;
                $info1->data = $user->polo;
                $info2 = new \stdClass();
                $info2->userid = $userId;
                $info2->fieldid = $coursefieldid;
                $info2->data = $user->course;
                $DB->insert_records('user_info_data', array(
                    $info1,
                    $info2,
                ));

	            echo "Usuário: ".$user->firstname.", inserido no Moodle atraves do Alfa.<br />";
	        }
	    }
    }

    if($returnvalue){
    	if($all){
    		$allusers = array();
    		$placeholders = '';

			for($i = 0; $i < sizeof($usernames); $i++){
				$placeholders .= '?, ';
			}

			$placeholders = rtrim($placeholders, ', ');

			$sql = "SELECT * FROM {user} WHERE username IN (" . $placeholders . ")";
			$result = $DB->get_records_sql($sql, $usernames);

			foreach ($result as $line) {
				$allusers[] = $line->id;
			}

			return $allusers;
    	}else{

    		$newusers = array();
    		$placeholders = '';

			for($i = 0; $i < sizeof($unexistentusernames); $i++){
				$placeholders .= '?, ';
			}

			$placeholders = rtrim($placeholders, ', ');

			$sql = "SELECT * FROM {user} WHERE username IN (" . $placeholders . ")";
			$result = $DB->get_records_sql($sql, $unexistentusernames);

			foreach ($result as $line) {
				$newusers[] = $line->id;
			}
			return $newusers;
    	}
    }else{
    	return;
    }        
}


function getStudentsFromList($list){
	global $DB;
	//$list = rtrim(preg_replace('/\s\s+/', '', $list), ';');
	//$list = preg_replace("/[\n\r\t]/","",$list);
	$list = rtrim(preg_replace("/[\n\r\t\s]/","",$list), ';');
	$studentslist = array_unique(explode(';', $list));

	$placeholder = '';

	for($i = 0; $i < count($studentslist); $i++){
		$placeholder .= "?, ";
	}
	$placeholder = '('.rtrim($placeholder, ', ').')';	

	$sql = "SELECT id, username, firstname, lastname
				FROM {user}
				WHERE username in ".$placeholder;
	$students = $DB->get_records_sql($sql, $studentslist);
	return $students;
}

function getTeachersFromList($list){
	global $DB;
	//$list = rtrim(preg_replace('/\s\s+/', '', $list), ';');
	//$list = preg_replace("/[\n\r\t]/","",$list);
	$list = rtrim(preg_replace("/[\n\r\t\s]/","",$list), ';');
	$teacherslist = array_unique(explode(';', $list));

	$placeholder = '';

	for($i = 0; $i < count($teacherslist); $i++){
		$placeholder .= "?, ";
	}
	$placeholder = '('.rtrim($placeholder, ', ').')';	

	$sql = "SELECT id, username, firstname, lastname
				FROM {user}
				WHERE username in ".$placeholder;
	$teachers = $DB->get_records_sql($sql, $teacherslist);
	return $teachers;
}

if(isset($_POST['evaluate'])){

	if(isset($_POST['discipline'])){
		if(is_numeric($_POST['discipline'])){
			$sql = "SELECT id, fullname
					FROM {course}
					WHERE id = ?";
			$course = $DB->get_records_sql($sql, array($_POST['discipline']));
			//$course[$_POST['discipline']]->id . ' - ' . $course[$_POST['discipline']]->fullname		
		}
	}

	if(isset($_POST['student-list'])){
		if(!empty($_POST['student-list'])){			
			$students = getStudentsFromList($_POST['student-list']);
			
		}
	}

	if(isset($_POST['teachers-list'])){
		if(!empty($_POST['teachers-list'])){			
			$teachers = getTeachersFromList($_POST['teachers-list']);			
		}
	}
	$groups = $DB->get_records('groups', Array('courseid' => $_POST['discipline']));
}

if(isset($_POST['production'])){

	echo "<h1>Resultado do processamento</h1>";	
	echo "<hr />";	

	if(isset($_POST['discipline']) && (isset($_POST['student-list']) || isset($_POST['teachers-list']))){

		$courseid = $_POST['discipline'];

		//$list = rtrim(preg_replace('/\s\s+/', '', $_POST['student-list']), ';');
		//$list = preg_replace("/[\n\r\t\s]/","",$_POST['student-list']);
		$list = rtrim(preg_replace("/[\n\r\t\s]/","",$_POST['student-list']), ';');
		$studentslist = array_unique(explode(';', $list));

		//$list = rtrim(preg_replace('/\s\s+/', '', $_POST['teachers-list']), ';');
		$list = rtrim(preg_replace("/[\n\r\t\s]/","",$_POST['teachers-list']), ';');
		$teacherslist = array_unique(explode(';', $list));

		$studentslist = create_users($studentslist, true, true);
		$teacherslist = create_users($teacherslist, true, true);

		/*
         * Adicionado os usuário no curso
         * Adding users to course
         */ 

        $context = context_course::instance($courseid, MUST_EXIST);
        $maninstance = $DB->get_record('enrol', array('courseid'=>$courseid, 'enrol'=>'manual'), '*', MUST_EXIST);
        $manual = enrol_get_plugin('manual');
        
        $sql = "SELECT shortname,id FROM {role} WHERE shortname in ('editingteacher','student')";
        $roles = $DB->get_records_sql($sql);
        $erros = '';
        foreach($studentslist as $user){
            $manual->enrol_user($maninstance, $user, $roles['student']->id);
            echo "Usuário com ID: ".$user." inserido como estudante.<br />";
            if($_POST['group'] > 0){
                $grp = new stdClass();
                $grp->groupid = $_POST['group'];
                $grp->userid = $user;
                $grp->timeadded = time();
                try {
                    $DB->insert_record('groups_members', $grp);
                    echo "&nbsp;&nbsp;&nbsp;&nbsp;Usuário com ID: ".$user." inserido no grupo ".$_POST['group']."<br />";
                }catch (Exception $e) {
                    $erros .= "$user, ";
                    echo "&nbsp;&nbsp;&nbsp;&nbsp;<b style='color:red;'>Usuário com ID</b>: ".$user." não inserido no grupo ".$_POST['group']."<br />";
                }
            }
            flush();
            ob_flush();
        }
        cache_helper::invalidate_by_definition('core', 'groupdata', array(), array($courseid));

        foreach($teacherslist as $user){
            $manual->enrol_user($maninstance, $user, $roles['editingteacher']->id);
            echo "Usuário com ID: ".$user." inserido como professor. <br />";
            flush();
            ob_flush();
        }
        if(isset($_POST['modifier'])){
            $USER->id = $temp_user;
        }
        echo "<h2>Finalizando processamento</h2>";        
        echo $erros;        
        echo "<br />";
        echo '<a href="'.$CFG->wwwroot.'/local/alfa/tests/nead_adicionar_usuario_lote.php" >Voltar</a>';
        die;

	}
}

?>


<form action="#" method="POST">
	<?php 
		if(!isset($course)){
	?>
		<label for="discipline">Id da disciplina a ser adicionada</label>
		<input type='text' name="discipline" placeholder="ID da disciplina" style="width:100%;" />
		<br />
	<?php
		}else{
	?>
		<label for="discipline">Id da disciplina a ser adicionada</label>
		<br />
		<input type="text" name="discipline" value="<?= $_POST['discipline']  ?>">
		<table width="100%" border="1">
			<thead style="background-color: #DFE5EF">
				<tr>
					<td colspan="2" stle="text-allign:center">Disciplina selecionado</td>					
				</tr>
				<tr>
					<td>ID</td>
					<td>Nome</td>
				</tr>		
			</thead>
			<tr>
				<td> <?= $course[$_POST['discipline']]->id ?> </td>
                <td> <a href="<?=$CFG->wwwroot?>/course/view.php?id=<?=$course[$_POST['discipline']]->id?>" target="_blank"><?= $course[$_POST['discipline']]->fullname ?></a> </td>
			</tr>
		</table>
	<?php
		}
	?>
	<hr />
	<?php 
		if(!isset($students)){
	?>
		<label for="student-list">Lista de codigos para os alunos (Devem ser separados por " ; ")</label>
		<br />
		<textarea name="student-list"></textarea>
	<?php
		}else{
	?>			
		<label for="student-list">Lista de codigos para os alunos (Devem ser separados por " ; ")</label>
		<br />
		<textarea name="student-list"><?= $_POST['student-list']?></textarea>
		<table width="100%" border="1">
			<thead style="background-color: #DFE5EF">
				<tr>
					<td colspan="3" stle="text-allign:center">Alunos selecionados</td>
				</tr>
				<tr>
					<td>ID</td>
					<td>Código</td>
					<td>Nome</td>
				</tr>		
			</thead>
			<?php
				foreach($students as $student){
					echo "<tr>";
					echo "	<td>".$student->id."</td>";
					echo "	<td>".$student->username."</td>";
					echo "	<td>".$student->firstname.' '.$student->lastname."</td>";
					echo "</tr>";
				}				
			?>
			
		</table>
	<?php
		}
	?>
    <select name="group" style="width: 100%;">
        <option value="0">No group</option>
        <?php
            foreach ($groups as $group) {
                echo "<option value='$group->id'>$group->name</option>";
            }
        ?>
        ?>
    </select>
    <label>Definir quem inserio como 0?</label><input type="checkbox" name="modifier" />
	<hr />
	<?php 
		if(!isset($teachers)){
	?>
		<label for="teachers-list">Lista de codigos para os professores (Devem ser separados por " ; ")</label>
		<br />
		<textarea name="teachers-list"></textarea>
	<?php
		}else{
	?>	
		<label for="teachers-list">Lista de codigos para os professores (Devem ser separados por " ; ")</label>
		<br />
		<textarea name="teachers-list"><?= $_POST['teachers-list']?></textarea>
		<table width="100%" border="1">
			<thead style="background-color: #DFE5EF">
				<tr>
					<td colspan="3" stle="text-allign:center">Professores selecionados</td>
				</tr>
				<tr>
					<td>ID</td>
					<td>Código</td>
					<td>Nome</td>
				</tr>		
			</thead>
			<?php
				foreach($teachers as $teacher){
					echo "<tr>";
					echo "	<td>".$teacher->id."</td>";
					echo "	<td>".$teacher->username."</td>";
					echo "	<td>".$teacher->firstname.' '.$teacher->lastname."</td>";
					echo "</tr>";
				}				
			?>
			
		</table>
	<?php
		}
	?>
	<hr />
	<input type="submit" name="evaluate" value="Enviar requisição para avaliação" />
	<?php if(isset($_POST['discipline']) && (isset($_POST['student-list']) || isset($_POST['teachers-list']))){ ?>
	<input type="submit" name="production" value="Enviar requisição produção" />
	<?php } ?>
		
</form>
<?php
if(isset($_POST['modifier'])){
    $USER->id = $temp_user;
}
