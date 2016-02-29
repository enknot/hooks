<?php

include_once('../../private/config/include.php');
include_once('../../private/config/plain.php');

$use_repo = true;
$exec = true;
$log = true;


$data['request'] = $_REQUEST;
$data['server'] = $_SERVER;
$data['input'] = json_decode(trim(file_get_contents('php://input')));

//config
if (isset($_REQUEST['project'])) {

    if (array_key_exists($_REQUEST['project'], $config)) {
        extract($config[$_REQUEST['project']]);
    } else {
        badrequest("bad request: invalid project.", $data);
    }
} else {
    $msg = 'bad_request: badly formed request';
    badrequest($msg, $data);
    die($msg . '\r' . print_pre($data));
}


function gitsite($project, $clone, $repo) {

    global $use_repo;
    global $exec;
    global $log;

    $to_screen['git_command'][] = $lab = json_decode(trim(file_get_contents('php://input')));

    /**
     * lab pushes only execute when master is branch is updated (and the execute
     *  option is on already)
     */
    if(isset($lab)){
        $exec = ($lab->ref == 'refs/heads/master')?$exec:false;
    }
    
    $ssh = "ssh -p 1027 githook@prod.rosalliance.com ";
    $rsync = '/usr/bin/rsync -avz --delete -e "ssh -p 1027" ';

    $web_root = "/var/www/html/plain";
    $remote_clone_root = "apache@prod.rosalliance.com:/var/www/html/plain";
    $local_clone_root = "/var/www/html/alliance/private/clones";

    $local_clone = "{$local_clone_root}/{$clone}";
    $remote_clone = "{$remote_clone_root}";



//clone if it doesnt already exist
    $commands = [];
    if ($use_repo) {
        if (!is_dir($local_clone)) {
            $commands[] = '/usr/bin/git clone ' . escapeshellarg($repo) . ' ' . escapeshellarg($local_clone) . ' 2>&1';
//otherwise pull origin master branch
        } else {
            $commands[] = 'cd ' . escapeshellarg($local_clone) . ';/usr/bin/git pull origin master 2>&1';
        }
    }


    $commands = array_merge($commands, [
        $rsync . escapeshellarg("{$local_clone}") . ' ' . escapeshellarg("{$remote_clone}")
    ]);


    foreach ($commands as $i => $command) {
        $output = [];
        $to_screen['command'][$i] = $command;
        if ($exec) {
            exec($command, $output);
            $to_screen[$i]['output'] = $output;
        }
    }


    if ($log)
        logger($project, $to_screen);
    echo print_pre($to_screen);
}

//go!
gitsite($project, $clone, $repo);
