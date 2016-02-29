<?php

include_once('../../private/config/include.php');

$project = isset($_GET['p']) ? $_GET['p'] : false;
$file = (!$project && (isset($_GET['f']))) ? $_GET['f'] : false;

$output = $echo = '';

if ($project) {

    $folder = LOG_ROOT . "/{$project}";
    $ls = ls($folder);


    foreach ($ls as $item) {
        $output .= "<li><a href='/logs/?project={$project}&f=$item'>{$item}<a/></li>";
    }

    $echo =  "<h3>Hook Projects: {$project}</h3><ul>$output<ul>";
    
    
} elseif ($file) {
    
    $project = isset($_GET['project']) ? $_GET['project'] : false;
    $log_file = LOG_ROOT . "/{$project}/{$file}";
    
    if(is_file($log_file)){
        $echo = "<h3>Hook Projects: <a href='/logs/?p={$project}'>{$project}<a/>/{$file}</h3><p><pre>" . file_get_contents($log_file) . "</pre></p>";
    }else{
        $echo = 'bad file';
    }
    
} else {

    
    $ls = ls(LOG_ROOT);

    foreach ($ls as $item) {
        $output .= "<li><a href='/logs/?p=$item'>{$item}<a/></li>";
    }

    $echo =   "<h3>Hook Projects</h3><ul>$output<ul>";
}

echo "<html><head></head><body>{$echo}<body></html>";

