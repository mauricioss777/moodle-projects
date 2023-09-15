<?php

require_once('../../../config.php');
if (!isloggedin()) {
    header('HTTP/1.0 403 Forbidden');
    die();
}
function load_users($course = null){
	global $DB, $CFG;
	
	$users = $DB->get_records_sql("	
	SELECT DISTINCT u.id AS userid, u.firstname, c.id AS courseid
	FROM mdl_user u
	JOIN mdl_user_enrolments ue ON ue.userid = u.id
	JOIN mdl_enrol e ON e.id = ue.enrolid
	JOIN mdl_role_assignments ra ON ra.userid = u.id
	JOIN mdl_context ct ON ct.id = ra.contextid AND ct.contextlevel = 50
	JOIN mdl_course c ON c.id = ct.instanceid AND e.courseid = c.id
	JOIN mdl_role r ON r.id = ra.roleid AND r.shortname = 'student'
	WHERE e.status = 0 
	      AND u.suspended = 0 	
	      AND u.deleted = 0 
              AND u.email NOT LIKE '%@univates.br' 
	      AND c.id IN (?)
	ORDER BY u.firstname;", Array( implode(',', $course) ) );
	
	$userids = Array();
		
	foreach($users as $user){
		$userids[] = $user->userid;
	}
	
	return $userids;	
}

function load_logs($courses = null){
	
	global $DB, $CFG;
	$users = implode(',', load_users( $courses ) );
	$courses = implode(',', $courses );
	$sql = "SELECT DISTINCT mus.id, mus.fullname, mus.username, mus.email, mus.timecreated as tcreated, mtdlog.timecreated FROM (SELECT id, username, firstname || ' ' || lastname as fullname, email, timecreated FROM mdl_user WHERE id IN  ({$users}) ) mus LEFT JOIN mdl_logstore_standard_log mtdlog ON mus.id = mtdlog.userid AND mtdlog.courseid IN ({$courses}) ORDER BY timecreated DESC ;";
    $results = $DB->get_records_sql( $sql, Array($courses, $users) );

    foreach($results as $result){
        if( ( time() - $result->tcreated) < 5184000){
            $result->fullname.= "<b style='color:red'> (Calouro)</b>";
        }
     }

	return $results;	
}

function draw_combo(){
    $code  = "<form action='nead_usuarios_acessos.php' method='GET'>";
    $code .= "<select name='disciplina'>";
    $code .= "";
    $code .= "<option value='29699'>PROJETO INTEGRADOR II - INTRODUÇÃO À PESQUISA APLICADA - REF102223 </option>";
    $code .= "<option value='29700'>ANÁLISE E MODELAGEM DE DADOS - REF102905 </option>";
    $code .= "<option value='29401'> PROJETO INTEGRADOR II - RACIOCÍNIO LÓGICO E MATEMÁTICO - REF102907 </option>";
    $code .= "<option value='29702'> SEMINÁRIO INTEGRADOR II: TENDÊNCIAS NO ENSINO DE CIÊNCIAS BIOLÓGICAS I - REF102232 </option>";
    $code .= "<option value='28871'>MATEMÁTICA E ESTATÍSTICA APLICADA - REF102255 </option>";
    $code .= "<option value='29703'>CONTABILIDADE PARA INICIANTES E NÃO CONTADORES - REF102222 </option>";
    $code .= "<option value='29704'>SEMINÁRIO INTEGRADOR II: INTRODUÇÃO AO ESTUDO DA HISTÓRIA - REF102230 </option>";
    $code .= "<option value='29705'>SEMINÁRIO INTEGRADOR II: INTRODUÇÃO AOS ESTUDOS DA LINGUAGEM - REF102231 </option>";
    $code .= "<option value='29706'>DIDÁTICA GERAL - REF102227 </option>";
    $code .= "<option value='29707'>EDUCAÇÃO E TECNOLOGIAS DA INFORMAÇÃO E COMUNICAÇÃO - REF102228 </option>";
    $code .= "<option value='29708'>SEMINÁRIO INTEGRADOR II: EDUCAÇÃO E TECNOLOGIAS - REF102229 </option>";
    $code .= "</select>";
    $code .= "<input type='submit' value='Enviar' /> </form>";
    return $code;

}

function draw(){
	
	echo draw_combo();
	
	$logs = load_logs( Array($_GET['disciplina']) );
	
	$dias = Array();
	
	foreach($logs as $log){
		if($log->timecreated == ''){
			$dias[-1]['usuarios'][] = $log;
			continue;
		}
		
		$diff_tempo = time() - $log->timecreated;		
		
		
		if($diff_tempo < 86400){ //hoje
			$dias[0]['usuarios'][] = $log;						
		} else if($diff_tempo < 172800){ // ontem			
			$dias[1]['usuarios'][] = $log;			
		} else if($diff_tempo < 259200){ // três dias
			$dias[2]['usuarios'][] = $log;			
		} else if($diff_tempo < 345600){ // quatro dias
			$dias[3]['usuarios'][] = $log;
		} else if($diff_tempo < 432000){ //cinco dias
			$dias[4]['usuarios'][] = $log;
		} else if($diff_tempo < 518400){ //seis dias
			$dias[5]['usuarios'][] = $log;
		} else if($diff_tempo < 604800){ //sete dias
			$dias[6]['usuarios'][] = $log;
		} else { //mais de sete dias
			$dias[7]['usuarios'][] = $log;			
		}
	}
	
	
	echo "<h3>Usuários que nunca acessaram o Curso</h3>";
	echo "<table style=\"width:100%\"> <tr> <td>Código</td><td>Nome</td><td>Email</td> </tr>";
	foreach($dias[-1]['usuarios'] as $usuario){		
		echo "<tr><td><a target='_BLANK' href='https://www.sistemas.univates.br/alfa/index.php?class=Basico::PessoasFisicas::PessoasFisicasListControl::onEdit({$usuario->username})'>{$usuario->username}</a></td><td>{$usuario->fullname}</td><td>{$usuario->email}</td></tr>";				
	}
	echo "</table><hr />";

	
	echo "<h3>Usuários que acessaram o curso hoje</h3>";
	echo "<table style='width:100%;'> <tr> <td>Código</td><td>Nome</td><td>Email</td> <td>Ultimo acesso</td> </tr>";
	foreach($dias[0]['usuarios'] as $usuario){		
		echo "<tr><td><a target='_BLANK' href='https://www.sistemas.univates.br/alfa/index.php?class=Basico::PessoasFisicas::PessoasFisicasListControl::onEdit({$usuario->username})'>{$usuario->username}</a></td><td>{$usuario->fullname}</td><td>{$usuario->email}</td><td>".date('d/m/Y', $usuario->timecreated)." - ".date('H:i:s', $usuario->timecreated)."</td></tr>";
				
	}
	echo "</table><hr />";
	
	echo "<h3>Usuários que não acessam o curso a um dia</h3>";
	echo "<table style='width:100%;'> <tr> <td>Código</td><td>Nome</td><td>Email</td> <td>Ultimo acesso</td> </tr>";
	foreach($dias[1]['usuarios'] as $usuario){		
		echo "<tr><td><a target='_BLANK' href='https://www.sistemas.univates.br/alfa/index.php?class=Basico::PessoasFisicas::PessoasFisicasListControl::onEdit({$usuario->username})'>{$usuario->username}</a></td><td>{$usuario->fullname}</td><td>{$usuario->email}</td><td>".date('d/m/Y', $usuario->timecreated)." - ".date('H:i:s', $usuario->timecreated)."</td></tr>";
	}
	echo "</table><hr />";	

	echo "<h3>Usuários que não acessam o curso a dois dias</h3>";
	echo "<table style='width:100%;'> <tr> <td>Código</td><td>Nome</td><td>Email</td> <td>Ultimo acesso</td> </tr>";
	foreach($dias[2]['usuarios'] as $usuario){		
		echo "<tr><td><a target='_BLANK' href='https://www.sistemas.univates.br/alfa/index.php?class=Basico::PessoasFisicas::PessoasFisicasListControl::onEdit({$usuario->username})'>{$usuario->username}</a></td><td>{$usuario->fullname}</td><td>{$usuario->email}</td><td>".date('d/m/Y', $usuario->timecreated)." - ".date('H:i:s', $usuario->timecreated)."</td></tr>";
	}
	echo "</table><hr />";	

	echo "<h3>Usuários que não acessam o curso a três dias</h3>";
	echo "<table style='width:100%;'> <tr> <td>Código</td><td>Nome</td><td>Email</td> <td>Ultimo acesso</td> </tr>";
	foreach($dias[3]['usuarios'] as $usuario){		
		echo "<tr><td><a target='_BLANK' href='https://www.sistemas.univates.br/alfa/index.php?class=Basico::PessoasFisicas::PessoasFisicasListControl::onEdit({$usuario->username})'>{$usuario->username}</a></td><td>{$usuario->fullname}</td><td>{$usuario->email}</td><td>".date('d/m/Y', $usuario->timecreated)." - ".date('H:i:s', $usuario->timecreated)."</td></tr>";
	}
	echo "</table><hr />";	


	echo "<h3>Usuários que não acessam o curso a quatro dias</h3>";
	echo "<table style='width:100%;'> <tr> <td>Código</td><td>Nome</td><td>Email</td> <td>Ultimo acesso</td> </tr>";
	foreach($dias[4]['usuarios'] as $usuario){				
		echo "<tr><td><a target='_BLANK' href='https://www.sistemas.univates.br/alfa/index.php?class=Basico::PessoasFisicas::PessoasFisicasListControl::onEdit({$usuario->username})'>{$usuario->username}</a></td><td>{$usuario->fullname}</td><td>{$usuario->email}</td><td>".date('d/m/Y', $usuario->timecreated)." - ".date('H:i:s', $usuario->timecreated)."</td></tr>";				
	}
	echo "</table><hr />";	


	echo "<h3>Usuários que não acessam o curso a cinco dias</h3>";
	echo "<table style='width:100%;'> <tr> <td>Código</td><td>Nome</td><td>Email</td> <td>Ultimo acesso</td> </tr>";
	foreach($dias[5]['usuarios'] as $usuario){				
		echo "<tr><td><a target='_BLANK' href='https://www.sistemas.univates.br/alfa/index.php?class=Basico::PessoasFisicas::PessoasFisicasListControl::onEdit({$usuario->username})'>{$usuario->username}</a></td><td>{$usuario->fullname}</td><td>{$usuario->email}</td><td>".date('d/m/Y', $usuario->timecreated)." - ".date('H:i:s', $usuario->timecreated)."</td></tr>";				
	}
	echo "</table><hr />";	



	echo "<h3>Usuários que não acessam o curso a seis dias</h3>";
	echo "<table style='width:100%;'> <tr> <td>Código</td><td>Nome</td><td>Email</td> <td>Ultimo acesso</td> </tr>";
	foreach($dias[6]['usuarios'] as $usuario){		
		echo "<tr><td><a target='_BLANK' href='https://www.sistemas.univates.br/alfa/index.php?class=Basico::PessoasFisicas::PessoasFisicasListControl::onEdit({$usuario->username})'>{$usuario->username}</a></td><td>{$usuario->fullname}</td><td>{$usuario->email}</td><td>".date('d/m/Y', $usuario->timecreated)." - ".date('H:i:s', $usuario->timecreated)."</td></tr>";				
	}
	echo "</table><hr />";	


	echo "<h3>Usuários que não acessam o curso a mais de sete dias</h3>";
	echo "<table style='width:100%;'> <tr> <td>Código</td><td>Nome</td><td>Email</td> <td>Ultimo acesso</td> </tr>";
	foreach($dias[7]['usuarios'] as $usuario){			
		
		echo "<tr><td><a target='_BLANK' href='https://www.sistemas.univates.br/alfa/index.php?class=Basico::PessoasFisicas::PessoasFisicasListControl::onEdit({$usuario->username})'>{$usuario->username}</a></td><td>{$usuario->fullname}</td><td>{$usuario->email}</td><td>".date('d/m/Y', $usuario->timecreated)." - ".date('H:i:s', $usuario->timecreated)."</td></tr>";				
	}
	echo "</table><hr />";	
}



$PAGE->set_url(new moodle_url( '/local/alfa/tests/nead_usuarios_acessos.php') );
$PAGE->set_pagelayout('mydashboard');
$PAGE->set_heading( 'adsadas' );
$PAGE->set_title('Monitoramento - Assiduidade');
echo $OUTPUT->header();
echo $OUTPUT->heading('Monitoramento - Assiduidade');
draw();
echo $OUTPUT->footer();
