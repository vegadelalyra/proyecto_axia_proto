<?php 
$I = new ApiTester($scenario);
$I->wantTo('Get user list via API');
$I->amHttpAuthenticated(testUser, testPassword);
$I->haveHttpHeader('Content-Type', 'application/json');
$I->sendGET('users');
$I->seeResponseCodeIs(200);
$I->seeResponseContains('{"Id":');

?>
