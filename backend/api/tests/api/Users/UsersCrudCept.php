<?php 
$randomNumber = rand(0,1000);
$I = new ApiTester($scenario);
$I->wantTo('Create User');
$I->amHttpAuthenticated(testUser, testPassword);
$I->haveHttpHeader('Content-Type', 'application/json');
$I->sendPOST('Users','{"name":"TestUser'.$randomNumber.'","User":"TestUser'.$randomNumber.'","Password":"200ceb26807d6bf99fd6f4f0d1ca54d4","IsSystemAdmin":1, "DefaultSecurityGroup":2, "DefaultRol":1,"owner":null}');
$response = $I->grabResponse();
$I->seeResponseCodeIs(200);
$I->seeResponseContains('{"Id":');
$jsonResponse = json_decode(substr($response, strpos($response,'{')));
$userId = $jsonResponse->Id;

$I->wantTo('Retrieve created User '.$userId);
$I->sendGET('users/'.$userId);
$I->seeResponseCodeIs(200);
$I->seeResponseContains('{"Id":');
$I->seeResponseContains('"owner":');

$I->wantTo('Update created user '.$userId);
$I->sendPUT('users/'.$userId,'{"Name":" Updated TestUser'.$randomNumber.'"}');
$I->seeResponseCodeIs(200);
$I->seeResponseContains('"Name":" Updated ');

$I->wantTo('Delete created User'.$userId);
$I->sendDELETE('users/'.$userId);
$I->seeResponseCodeIs(200);

$I->wantTo('Check if deleted User'.$userId);
$I->sendGET('users/'.$userId);
$I->seeResponseCodeIs(403);
?>

