<?php 
$randomNumber = rand(0,1000);
$I = new ApiTester($scenario);
$I->amHttpAuthenticated(testUser, testPassword);
$I->haveHttpHeader('Content-Type', 'application/json');

$I->wantTo('Get Correct login');
$I->sendGET('users/0');
$response = $I->grabResponse();
$I->seeResponseCodeIs(200);
$I->seeResponseContains('{"Id":');
$I->amHttpAuthenticated(testUser, "ghjkdgykfndagbb");
$I->haveHttpHeader('Content-Type', 'application/json');
$I->wantTo('Get wrong login');
$I->sendGET('users/0');
$response = $I->grabResponse();
$I->seeResponseCodeIs(401);


?>