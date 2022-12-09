<?php

require_once('Task1.php');

// write
jsonhelper::write('users.json.php',[['username'=>'NoahD','email'=>'deesn1@nku.edu','password'=>''],['username'=>'testdummy','email'=>'testdummy@nku.edu','password'=>'']],false,true); 

jsonhelper::write('users.json.php',['a'=>['username'=>'NoahD','email'=>'deesn1@nku.edu','password'=>''],'b'=>['username'=>'testdummy','email'=>'testdummy@nku.edu','password'=>'']],true,true);

// update
jsonhelper::update('users.json.php',0,['username'=>'NoahD','email'=>'deesn1@nku.edu','password'=>'','age'=>'22']]);

// read
echo '<pre>';print_r(jsonhelper::read('users.json.php'));

// delete
jsonhelper::delete('users.json.php',1,true);
jsonhelper::delete('users.json.php');