<?php
session_start();

// if(array_key_exists('visits',$_COOKIE)){
//     setcookie("visits", $_COOKIE['visits']+1, time()+60*60*24*7*4*12,'/');
//      // expires after 60 seconds
// }else{
//     setcookie("visits", 0, time()+60*60*24*7*4*12,'/');
// }
// echo"<h1>You have visited: {$_COOKIE['visits']} times.</h1>";