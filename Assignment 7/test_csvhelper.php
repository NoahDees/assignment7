<?php

require_once('Task1.php');

//Write
csvhelper::write('users.csv',array('Noah','deesn1@nku.edu'));
csvhelper::write('users.csv',array('example','definitelyanemail@nku.edu'));

//Read
echo '<pre>';print_r(csvhelper::read('users.csv'));

//Update
csvhelper::update('users.csv',1,['name'=>'Jimmy','email'=>'jimj221@nku.edu']);

//Delete
csvhelper::delete('users.csv',1);