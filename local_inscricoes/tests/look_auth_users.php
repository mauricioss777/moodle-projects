<?php

//define('CLI_SCRIPT', true);

require_once('../../../config.php');

if(!is_siteadmin() && !in_array($USER->username, $CFG->eadusers)){
    die('Você não tem permissão de executar este script.');
}

die();

$users = $DB->get_records_sql("SELECT * FROM mdl_user WHERE id IN (SELECT userid FROM mdl_role_assignments WHERE contextid IN (select id from mdl_context WHERE contextlevel = 50 and instanceid = 34861)) AND auth = 'manual'") ;

foreach($users as $user){
    if( !is_numeric($user->username) ){ continue; }

    //echo $user->username. "<br />";

    if(getLogin($user->username)){
        //$user->password = md5($user->email);
        $user->auth = 'ldap';
        $DB->update_record('user', $user);
        echo 'Tem: '.$user->username . "<br />";
    }else{
        echo 'Não Tem: '.$user->username . "<br />";
    }
}

//var_dump(getLogin('723110'));

function getLogin($ref_pessoa){
    //$ldap = parse_ini_file('app/config/ldap.ini');

    $ldap_server       = "ldap://ensino.univates.br";
    $ldap_server_slave = "ldap://ldapacad1.univates.br";
    $ldap_base         = "dc=univates,dc=br";
    $ldap_port         = '389';

    $ldap_admin_name   = 'cn=ntidevel,dc=univates,dc=br';
    $ldap_admin_pass   = 'd3v3lNT1';
                  
    $ds=ldap_connect($ldap_server, $ldap_port);
    ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, 3);
    @$r=ldap_bind($ds, $ldap_admin_name, $ldap_admin_pass);

    if ( ! $r ){
        $ds=ldap_connect($ldap_server_slave, $ldap_port);
        ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, 3);
        $r=ldap_bind($ds, $ldap_admin_name, $ldap_admin_pass);
    }

    $login = '';
    if ($r){
        $sr= ldap_search($ds, $ldap_base, "(&(objectclass=univates)(codAluno=$ref_pessoa))");
        @$info=ldap_get_entries($ds,$sr);

        $login = null;

       
        if(isset($info[0]["uid"][0]))
        {
            $login = $info[0]["uid"][0];
        }
    }

    ldap_close($ds);

    return $login;
}
