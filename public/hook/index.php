<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


//includes
include_once('../../private/config/include.php');
include_once('../../private/config/wp.php');


//config
if(isset($_REQUEST['project']) &&  array_key_exists($_REQUEST['project'], $config)){
    extract($config[$_REQUEST['project']]);
}else{    
    $data['request'] = $_REQUEST;
    $data['input'] = json_decode(trim(file_get_contents('php://input')));
    logger('bad_request', $data);
    die(print_pre($data));
}

//settings
$use_repo = true;
$exec =     false;
$log =      false;


//variables
$to_screen['request'] = $_REQUEST;
$to_screen['git_remote'] = json_decode(trim(file_get_contents('php://input')));


$ssh = "ssh -p 1027 githook@prod.rosalliance.com ";
$rsync = '/usr/bin/rsync -avz --delete -e "ssh -p 1027" ';

$remote_clone_root = "apache@prod.rosalliance.com:/var/www/html/wordpress/{$project}/wp-content";
$local_clone_root = "/var/www/html/alliance/private/clones";

$local_clone = "{$local_clone_root}/{$clone}";
$remote_clone = "{$remote_clone_root}";


//clone if it doesnt already exist
$commands = [];
if ($use_repo) {
    if (!is_dir($local_clone)) {
        $commands[] = '/usr/bin/git clone --recursive ' . escapeshellarg($repo) . ' ' . escapeshellarg($local_clone) .' 2>&1';
//otherwise pull origin master branch
    } else {
        $commands[] = 'cd ' . escapeshellarg($local_clone) . ';/usr/bin/git pull origin master 2>&1';
    }
}



$commands = array_merge($commands, [
    $rsync . escapeshellarg("{$local_clone}") . ' ' . escapeshellarg("{$remote_clone}"),            
        ]);

foreach ($commands as $i => $command) {
    $output = [];
    $to_screen['command'][$i] = $command;

    if ($exec) {
        exec($command, $output);
        $to_screen[$i]['output'] = $output;
    }
}


if($log)logger($project, $to_screen);
echo print_pre($to_screen);

