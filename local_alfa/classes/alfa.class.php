<?php
require_once('tsoapclient.class.php');

/**
 * Camada de integração com o alfa.
 *
 * @since  set/2014
 * @author Maurício S. Silva mss@univates.br
 * @author Artur H. Welp ahwelp@univates.br
 * @author Alexandre S. Wolf awolf@univates.br
 */
class Alfa
{
    /**
     * Busca no alfa as informações para um determinado usuário
     * @param $username código de aluno/professor
     * @return $user retorna um objeto contendo as informações do usuário.
     */
    public static function getUserInformation($username)
    {
        $array = Alfa::getUsersInformation(array($username));

        return $array[0];
    }

    /**
     * Busca no alfa as informações para um array usuários
     * @param $username array códigos de aluno/professor
     * @return $user Object retorna um array de objetos contendo as informações dos usuários.
     */
    public static function getUsersInformation($usernames)
    {
        $users = array();
        $soapClient = new TSoapClientAlfa();
        try {
            if($soapClient) {
                $users = $soapClient->executaMetodoModel('Basico::Pessoas', 'getUsersInformation', array($usernames));
            }
        } catch (Exception $e) {
            error_log("LOCAL_ALFA_ERROR::getUsersIformation:: Alfa Soap Error: " . $e->getMessage());

            return;
        }
        $countries = array_map('mb_strtoupper', get_string_manager()->get_list_of_countries());
        foreach ($users as $key => $user) {// simula os dados recebidos do Alfa
            $fullname = explode(" ", trim(mb_convert_encoding($user->name, "UTF-8", "ISO-8859-1")));
            $u = new stdClass();
            $u->username = $user->codigo;
            $u->firstname = array_shift($fullname);;
            $u->lastname = implode(' ', $fullname);
            $u->idnumber = trim(mb_convert_encoding($user->username, "UTF-8", "ISO-8859-1")); //login de usuário
            $u->email = trim(mb_convert_encoding($user->email, "UTF-8", "ISO-8859-1"));
            $u->auth = 'ldap';
            $u->confirmed = '1';
            $u->mnethostid = '3';//Usuário Local do Moodle (ver http://moodlesql.blogspot.com.br/2011/02/campo-mnethostid-da-tabela-mdluser-do.html)
            $u->city = trim(mb_convert_encoding($user->city, "UTF-8", "ISO-8859-1"));
            $u->country = array_search(trim(mb_convert_encoding($user->country, "UTF-8", "ISO-8859-1")), $countries);
            $u->lang = 'pt_br';
            $u->polo = trim(mb_convert_encoding($user->polo, "UTF-8", "ISO-8859-1"));
            $u->poloid = trim(mb_convert_encoding($user->poloId, "UTF-8", "ISO-8859-1"));
            $u->institution = trim(mb_convert_encoding($user->polo, "UTF-8", "ISO-8859-1"));
            $u->course = trim(mb_convert_encoding($user->curso, "UTF-8", "ISO-8859-1"));
            $u->courseid = trim(mb_convert_encoding($user->cursoId, "UTF-8", "ISO-8859-1"));
            $u->department = trim(mb_convert_encoding($user->curso, "UTF-8", "ISO-8859-1"));
            $u->ppg = trim(mb_convert_encoding($user->ppgId, "UTF-8", "ISO-8859-1"));
            $u->curriculo = trim(mb_convert_encoding($user->curriculoId, "UTF-8", "ISO-8859-1"));

            //Nova Mutum?
            if($u->institution == 'Univates Centro Oeste'){
                $u->timezone = "America/Havana";
            }

            $users[$key] = $u;
        }

        return $users;
    }

    /**
     * Busca as informações para uma determinada oferta no alfa.
     * Esta função retorna as mesmas informações que são recebidas no momento em que o curso é criado.
     * É utilizada para fazer a atualização de usuários e informações do curso.
     * @param $idoffer idnumber da tabela mdl_course
     * @return $course_info Object retorna um objeto igual ao documentado na função local_alfa_create_course
     */
    public static function getCourseInformation($idoffer)
    {
        if(is_array($idoffer)) {
            //curso com várioas ofertas vinculadas
            $idoffer = array_shift($idoffer);// Um dia, se necessário, fazer um tratamento melhor.
        } else {
            //curso com apenas uma oferta vinculada
        }
        $soapClient = new TSoapClientAlfa();
        try {
            if($soapClient) {
                $c = (array) $soapClient->executaMetodoModel('Academico::Turmas', 'getCourseInformation', array(
                    $idoffer,
                    true,
                ));
                if(isset($c[0]) AND empty($c[0])) {
                    return false;
                }
                //print_r($c);
                $c['fullname'] = mb_convert_encoding($c['fullname'], "UTF-8", "ISO-8859-1");
                $c['shortname'] = mb_convert_encoding($c['fullname'], "UTF-8", "ISO-8859-1");
                $c['dayshift'] = mb_convert_encoding($c['dayshift'], "UTF-8", "ISO-8859-1");
                $c['dayofweek'] = mb_convert_encoding($c['dayofweek'], "UTF-8", "ISO-8859-1");
                $c['category'] = mb_convert_encoding($c['category'], "UTF-8", "ISO-8859-1");
                $dates = array_map('trim', explode('/', $c['inidate']));
                usort($dates, 'Alfa::_usort_alfa_date');
                $c['inidate'] = array_shift($dates);//pega a menor data
                $dates = array_map('trim', explode('/', $c['enddate']));
                usort($dates, 'Alfa::_usort_alfa_date');
                $c['enddate'] = end($dates);//pega a maior data

                return $c;
            }
        } catch (Exception $e) {
            error_log("LOCAL_ALFA_ERROR::getCourseInformation:: Alfa Soap Error: " . $e->getMessage());

            return false;
        }
    }

    /**
     * Busca as informações para uma determinada oferta no alfa.
     * Esta função retorna as mesmas informações que são recebidas no momento em que o curso é criado.
     * É utilizada para fazer a atualização de usuários e informações do curso.
     * @param $idoffer idnumber da tabela mdl_course
     * @return $course_info Object retorna um objeto igual ao documentado na função local_alfa_create_course
     */
    public static function getCourseTCCInformation($idoffer)
    {
        $soapClient = new TSoapClientAlfa();
        try {
            if($soapClient) {
                $c = (array) $soapClient->executaMetodoModel('Academico::Turmas', 'getCourseInformationTCC', array(
                    $idoffer,
                    true,
                ));
                $c['fullname'] = mb_convert_encoding($c['fullname'], "UTF-8", "ISO-8859-1");
                $c['shortname'] = mb_convert_encoding($c['fullname'], "UTF-8", "ISO-8859-1");
                $c['dayshift'] = mb_convert_encoding($c['dayshift'], "UTF-8", "ISO-8859-1");
                $c['dayofweek'] = mb_convert_encoding($c['dayofweek'], "UTF-8", "ISO-8859-1");
                $c['category'] = mb_convert_encoding($c['category'], "UTF-8", "ISO-8859-1");
                $dates = array_map('trim', explode('/', $c['inidate']));
                usort($dates, 'Alfa::_usort_alfa_date');
                $c['inidate'] = array_shift($dates);//pega a menor data
                $dates = array_map('trim', explode('/', $c['enddate']));
                usort($dates, 'Alfa::_usort_alfa_date');
                $c['enddate'] = end($dates);//pega a maior data

                return $c;
            }
        } catch (Exception $e) {
            error_log("LOCAL_ALFA_ERROR::getCourseInformationTCC:: Alfa Soap Error: " . $e->getMessage());

            return false;
        }
    }

    /**
     * Busca as informações de um determinado curso no alfa.
     * É utilizada para sincronizar as informaçõe de um determinado currículo(
     * @param $idoffer idnumber da tabela mdl_course
     * @return $course_info Object retorna um objeto igual ao documentado na função local_alfa_create_course
     */
    public static function getCurriculumInformation($curriculumid, $skipdatecheck = false)
    {
        if(is_array($curriculumid)) {
            //curso com várioas ofertas vinculadas
            $courseid = array_shift($curriculumid);// Um dia, se necessário, fazer um tratamento melhor.
        } else {
            //curso com apenas uma oferta vinculada
        }
        $soapClient = new TSoapClientAlfa();
        try {
            if($soapClient) {
                return (array) $soapClient->executaMetodoModel('Academico::Curriculos', 'getPessoasVinculadasCurriculo', array($curriculumid, !$skipdatecheck));
            }
        } catch (Exception $e) {
            error_log("LOCAL_ALFA_ERROR::getCurriculumInformation:: Alfa Soap Error: " . $e->getMessage());

            return false;
        }
    }

    /**
     * Busca no alfa os usuários que fizeram alguma modificação em seu perfil de usuário.
     * Esta função retorna um array de usuários com as modificações realizadas em seus perfis. Ex.
     *      $users['341039']->email     = 'mss@univates.br';
     *      $users['341039']->city      = 'Encantado';
     *      $users['527813']->firstname = 'ARTUR';
     *      $users['527813']->lastname  = 'H. WELP';
     * Os campos suportados para atualização são: nome, e-mail e cidade.
     *
     * @param $lastupdate date/timestamp data/hora da última atualização.
     * @return $users array retorna um array de usuários com as modificações realizadas em seus perfis.
     */
    public static function getModifiedUsersInformation($lastupdate = null)
    {// OK
        global $DB;
        $users = array();
        if( ! isset($lastupdate) || $lastupdate == '') {
            $lastupdate = time() - (24 * 60 * 60 * 2);//sempre busca as modificações das últimas 24 horas e aplica
        } else {
            $lastupdate = $lastupdate - (60 * 60 * 2);//2 horas de diferença entre o timestamp do moodle e do alfa.
        }
        $date = DateTime::createFromFormat('U', $lastupdate);
        $date = $date->format('Y-m-d H:i:s');//formato de data aceito pelo alfa
        $soapClient = new TSoapClientAlfa();
        try {
            if($soapClient) {
                $usersalfa = $soapClient->executaMetodoModel('Basico::Logs', 'getModifiedUsersInformation', array($date));
                $users = array();
                foreach ($usersalfa as $username => $userinfo) {
                    $user = new stdClass();
                    if(isset($usersalfa[$username]->email)) {
                        $user->email = mb_convert_encoding($usersalfa[$username]->email, "UTF-8", "ISO-8859-1");
                        $pos = strpos($user->email, ',');//gambi - o alfa permite que uma pessoa cadastre 2 e-mail separados por virgula
                        if($pos !== false) {
                            $user->email = substr($user->email, 0, $pos);
                        }
                    }
                    if(isset($usersalfa[$username]->cidade)) {
                        $user->city = mb_convert_encoding($usersalfa[$username]->cidade, "UTF-8", "ISO-8859-1");
                    }
                    if(isset($usersalfa[$username]->nome)) {
                        $fullname = explode(" ", trim(mb_convert_encoding($usersalfa[$username]->nome, "UTF-8", "ISO-8859-1")));
                        $user->firstname = array_shift($fullname);;
                        $user->lastname = implode(' ', $fullname);
                    }
                    if(isset($usersalfa[$username]->polo)) {
                        $user->polo = trim(mb_convert_encoding($usersalfa[$username]->polo, "UTF-8", "ISO-8859-1"));
                    }
                    if(isset($usersalfa[$username]->curso)) {
                        $user->course = trim(mb_convert_encoding($usersalfa[$username]->curso, "UTF-8", "ISO-8859-1"));
                    }
                    $users[$username] = $user;
                }

                return $users;
            }
        } catch (Exception $e) {
            error_log("LOCAL_ALFA_ERROR::getModifiedUsersInformation:: Alfa Soap Error: " . $e->getMessage());

            return false;
        }

        return true;
    }

    /**
     * Esta função retorna a imagem do usuário
     * @param $username Integer Cógido de aluno/professor
     * @return base64_string
     */
    public static function getUserPicture($username)
    {

        $soapClient = new TSoapClientAlfa();
        try {
            if($soapClient) {
                $pic = $soapClient->executaMetodoModel('Basico::Pessoas', 'getUserPicture', array($username));
                if($pic != '') {
                    $file = base64_decode($pic);
                    //header('Content-type: image/png');
                    //@header('Content-Disposition: inline; filename="'.$username.'.png"');
                    //echo $file; die();
                    return $file;
                }
            }
        } catch (Exception $e) {
            error_log("LOCAL_ALFA_ERROR::updateLinkEaD:: Alfa Soap Error: " . $e->getMessage());

            return false;
        }

        return false;
    }

    /**
     * Atualiza/Adiciona no alfa o link EaD para um array de ofertas
     * @param $idoffers   Array Código da oferta que receberá o novo link ead
     * @param $newlinkead String Novo link EaD
     * @return
     */
    public static function updateLinkEaD($idoffers, $newlinkead)
    {
        if( ! is_array($idoffers)) {
            error_log("idoffer must be Array");

            return false;
        }
        $soapClient = new TSoapClientAlfa();
        try {
            if($soapClient) {
                foreach ($idoffers as $idoffer) {
                    $soapClient->executaMetodoModel('Academico::Turmas', 'atualizaLinkEad', array(
                        $idoffer,
                        $newlinkead,
                    ));
                }
            }
        } catch (Exception $e) {
            error_log("LOCAL_ALFA_ERROR::updateLinkEaD:: Alfa Soap Error: " . $e->getMessage());

            return false;
        }

        return true;
    }

    /**
     * Busca no alfa os novos usuários inseridos no alfa e adiciona no Moodle
     * @param date última atualização
     * @return stdClass Users
     */
    public static function getNewUsers($lastupdate = null)
    {

        if( ! isset($lastupdate)) {
            $lastupdate = time() - (24 * 60 * 60 * 2);//busca os usuários inseridos no alfa a dois dias atrás
        }
        $soapClient = new TSoapClientAlfa();
        try {
            if($soapClient) {

                //******
                // A DATA DE CADASTRO PRECISA SER DIA - 2
                // ISSO ACONTECE PORQUE O USERNAME NÃO É CRIADO JUNTO COM O CADASTRO DO ALFA. 
                // ESTE PROCESSO ACONECE DURANTE A MADRUGADA DO DIA SEGUINTE.
                // DE A DATA NÃO ESTIVER CONFIGURADA PARA DIA -2 O CAMPO USERNAME/IDNUMBER E PAIS RETORNARÃO EM BRANCO.
                //******
                $date = DateTime::createFromFormat('U', $lastupdate);
                $date = $date->format('Y-m-d');//formato de data aceito pelo alfa
                error_log(print_r($date, true));
                $newusers = $soapClient->executaMetodoModel('Basico::Pessoas', 'getNewUsers', array($date));

                return Alfa::getUsersInformation($newusers);
            }
        } catch (Exception $e) {
            error_log("LOCAL_ALFA_ERROR::getNewUsers:: Alfa Soap Error: " . $e->getMessage());

            return false;
        }

        return false;
    }

    /**
     * Verifica se o plano de enino já foi preenchido para uma determinada oferta.
     * @param idnumber
     * @return boolean
     */
    public static function isTeachingPlan($idnumber)
    {// OK
        global $CFG;
        $soapClient = new TSoapClientAlfa();
        try {
            if($soapClient) {
                if($soapClient->executaMetodoControl('Academico::ConteudoProgramatico::ConteudoProgramaticoPdfControl',
                    'verificaConteudoProgramaticoPreenchido', array(
                        $idnumber,
                        true,
                    ))) {
                    return true;
                }

                return false;
            }
        } catch (Exception $e) {
            error_log("LOCAL_ALFA_ERROR::isTeachingPlan:: Alfa Soap Error: " . $e->getMessage());

            return false;
        }
    }

    /**
     * Gera o PDF(binário) do plano de ensino
     * @param idnumber
     * @return pdf file
     */
    public static function getTeachingPlan($idnumber)
    {
        global $CFG, $USER;
        
        $soapClient = new TSoapClientAlfa();
        try {
            if($soapClient) {
                return $soapClient->executaMetodoControl('Academico::ConteudoProgramatico::ConteudoProgramaticoPdfControl', 'geraConteudoProgramatico', array(
                    $idnumber,
                    true,
                    is_number($USER->username) ? $USER->username : null,
                ));
            }
        } catch (Exception $e) {
            error_log("LOCAL_ALFA_ERROR::getTeachingPlan:: Alfa Soap Error: " . $e->getMessage());
            return;
        }
    }

    /**
     * Busca o campus do estudante
     * @param  idnumber
     * @return string polo
     */
    public static function getUserPolo($idnumber){
        $soapClient = new TSoapClientAlfa();
        try {
            if($soapClient) {
                return $soapClient->executaMetodoModel('Academico::Campus', 'getEstabelecimentosPessoa', $idnumber);
            }
        } catch (Exception $e) {
            error_log("LOCAL_ALFA_ERROR::getEstabelecimentosPessoa:: Alfa Soap Error: " . $e->getMessage());
        }
    }

    /**
     * Busca os ducumentos faltantes para o estudante
     * @param  String userid
     * @return Array Documentos Array(codigo, aluno_ead, Array(documento))
     */
    public static function getDocumentosPendentesPessoa($userid){
        global $USER;
        $soapClient = new TSoapClientAlfa();
        try {
            if($soapClient) {
                $info = $soapClient->executaMetodoModel('Basico::Documentos', 'getDocumentosPendentesPessoa', $userid);
                $documents = $info['documentacao_pendente'];
                foreach (@$documents as $key => $item){
                    if(strpos($item, 'escolar') > -1 ||
                        strpos($item, 'Ensino') > -1 ||
                        strpos($item, 'Diploma') > -1)
                    {
                        $documents[$key] = 'Histórico escolar ou diploma <span style="color:red">autenticado</span>, ou cópia frente e verso.';
                    }
                    if(strpos($item, 'Casamento') > -1){
                        $documents[$key] = 'Certidão de Nascimento ou Casamento';
                    } 
                }
                @$info['documentacao_pendente'] = array_unique($documents);
                return $info;
            }
        } catch (Exception $e) {
            error_log("LOCAL_ALFA_ERROR::getDocumentosPendentesPessoa:: Alfa Soap Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Busca se usuário necessíta alterar senha
     * @param integer userid
     * @return boolean resultado se usuário necessíta alterar senha
     */
    public static function pessoaPrecisaRecadastrarSenha($username){
        $soapClient = new TSoapClientAlfa();
        try {
            return $soapClient->executaMetodoModel('Basico::Pessoas','precisaRecadastrarSenha',array($username));
        } catch (Exception $e) {
            return false;
            error_log("LOCAL_ALFA_ERROR::precisaRecadastrarSenha:: Alfa Soap Error: " . $e->getMessage());
        }
    }

    /**
    * Registrar certificado de extensão
    *
    * @param integer coursereference
    * @param integer username
    * @param integer grade 
    * @param bool approved 
    */
    public static function registrarAprovacaoEmExtensao($coursereference, $username, $grade = 10, $approved = true){
        $soapClient = new TSoapClientAlfa();
        try {
            return $soapClient->executaMetodoModel('Academico::Matriculas','setAptoCertificadoExtensao', 
                array( $coursereference, $username, $grade, $approved )
            );
        } catch (Exception $e) {
            error_log("LOCAL_ALFA_ERROR::setAptoCertificadoExtensao Alfa Soap Error: " . $e->getMessage());
            return false;
        }
        return true;
    }

    /**
    * PDF do certificado de extensão para o usuário no curso 
    *
    * @param integer coursereference
    * @param integer username
    */
    public static function geraCertificadoMatriculaExtensaoPorAlunoTurma($coursereference, $username){
        $soapClient = new TSoapClientAlfa();
        try {
            return $soapClient->executaMetodoModel( 'Academico::Matriculas','geraCertificadoMatriculaExtensaoPorAlunoTurma', array( $coursereference, $username ) );
        } catch (Exception $e) {
            error_log("LOCAL_ALFA_ERROR::  Alfa Soap Error: geraCertificadoMatriculaExtensaoPorAlunoTurma" . $e->getMessage());
            return false;
        }
        return false;
    }

    /**
    * Buscar matrículas para o usuário
    *
    * @param integer username
    * @param string  periodo
    * @param bool    canceladas
    */
    public static function buscarMatriculasUsuario($username, $periodo, $canceladas = false){
        
        $filter = new stdClass();
        $filter->_ref_pessoa = ['bas_pessoas.id = '.$username];
        $filter->_ref_periodo = ["acd_matriculas.ref_periodo = '$periodo'"];

        if(!$canceladas){
            $filter->_dt_cancelamento = ["acd_matriculas.dt_cancelamento IS NULL"];
        }

        $soapClient = new TSoapClientAlfa();
        try{
            return $soapClient->executaMetodoModel( 'Academico::Matriculas','getMatriculas', $filter);
        }
        catch(Exception $e) {
            error_log( print_r($e, true) );
            //code to print caught exception
        }
        return false;

    }


    /**
    * Buscar estudantes em adaptação 
    *
    * @param integer username
    */
    public static function usuarioEmAdaptacao(){
        $soapClient = new TSoapClientAlfa();
        try{
            return $soapClient->executaMetodoModel( 'BiAcademico::FatTendenciaEvasao','isAlunoNovoAdaptacaoEAD', array() );
        }
        catch(Exception $e) {
            error_log( print_r($e, true) );
            //code to print caught exception
        }
        return false;

    }

    /**
    * Buscar estudantes em adaptação 
    *
    * @param integer username
    */
    public static function titulosAbertos($username){
        $soapClient = new TSoapClientAlfa();
        try{
            return $soapClient->executaMetodoModel( 'Financeiro::Titulos','getTitulosEmAberto', array($username) );
        }
        catch(Exception $e) {
            error_log( print_r($e, true) );
            //code to print caught exception
        }
        return false;

    }

    /**
    * PDF do certificado Carta Apresentacao Estagio
    *
    * @param integer coursereference
    * @param integer username
    */
    public static function geraCertificadoCartaApresentacaoEstagio($params){
        $soapClient = new TSoapClientAlfa();
        try {
            $return = $soapClient->executaMetodoControl( 'Academico::Atestados::AtestadosPdfControl','geraCartaApresentacaoEstagio', $params );
            return $return['content'];
        } catch (Exception $e) {
            error_log("LOCAL_ALFA_ERROR::  Alfa Soap Error: geraCertificadoCartaApresentacaoEstagio" . $e->getMessage());
            return false;
        }
        return false;
    }

    /**
    * Enviar notificação pelo App para os Usuários de uma turma 
    *
    * @param integer   reference 
    * @param string    titulo
    * @param string    mensagem 
    * @param integer[] estudantes
    */
    public static function enviarNotificacaoApp($reference, $titulo, $mensagem, $estudantes = null){        
        global $CFG;

        // Não enviar notificaçao em servidor de devel
        if($CFG->wwwroot != 'https://www.univates.br/virtual'){
            return 0;
        }

        $soapClient = new TSoapClientAlfa();
        try {
            return $soapClient->executaMetodoModel( 'Academico::Turmas','sendNotificacaoAPPUnivates', 
                array($reference, $titulo, $mensagem, $estudantes) 
            );
        } catch (Exception $e) {
            error_log("LOCAL_ALFA_ERROR::  Alfa Soap Error: sendNotificacaoAPPUnivates" . $e->getMessage());
            return '0'; 
        }
        return true;
    }

    /**
     * Função auxiliar que ordena as datas vindas pelo alfa
     **/
    public static function _usort_alfa_date($a, $b)
    {
        $a = strtotime($a);
        $b = strtotime($b);

        return $a - $b;
    }


}
