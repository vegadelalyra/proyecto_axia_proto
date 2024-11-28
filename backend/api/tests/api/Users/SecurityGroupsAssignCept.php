<?php 
$randomNumber = rand(0,1000);
$I = new ApiTester($scenario);
$I->wantTo('Create SecurityGroup 1');
$I->amHttpAuthenticated(testUser, testPassword);
$I->haveHttpHeader('Content-Type', 'application/json');

$I->sendPOST('SecurityGroups','{"Name":"TestGroup'.$randomNumber.'","description":"Test group descriptionr'.$randomNumber.'"}');
$response = $I->grabResponse();
$I->seeResponseCodeIs(200);
$I->seeResponseContains('{"Id":');
$jsonResponse = json_decode(substr($response, strpos($response,'{')));
$elementId = $jsonResponse->Id;

$I->wantTo('Create SecurityGroup 2');
$I->sendPOST('SecurityGroups','{"Name":"TestGroup'.($randomNumber+1).'","description":"Test group descriptionr'.$randomNumber.'"}');
$response = $I->grabResponse();
$I->seeResponseCodeIs(200);
$I->seeResponseContains('{"Id":');
$jsonResponse = json_decode(substr($response, strpos($response,'{')));
$elementId2 = $jsonResponse->Id;

$I->wantTo('Create user for test');
$I->sendPOST('Users','{"name":"TestUser'.$randomNumber.'","User":"TestUser'.$randomNumber.'","Password":"200ceb26807d6bf99fd6f4f0d1ca54d4","IsSystemAdmin":1, "DefaultSecurityGroup":'.$elementId.', "DefaultRol":1,"owner":null}');
$response = $I->grabResponse();
$I->seeResponseCodeIs(200);
$I->seeResponseContains('{"Id":');
$jsonResponse = json_decode(substr($response, strpos($response,'{')));
$userId = $jsonResponse->Id;

$I->wantTo('Assign groups to user');
$I->sendPOST('Users/'.$userId.'/securitygroups',$elementId.','.$elementId2);
$response = $I->grabResponse();
$I->seeResponseCodeIs(200);
$I->seeResponseContains('"securitygroups_id":'.$elementId);
$I->seeResponseContains('"securitygroups_id":'.$elementId2);

$I->wantTo('Retrieve assigned SecurityGroups ');
$I->sendGET('Users/'.$userId.'/securitygroups');
$I->seeResponseCodeIs(200);
$I->seeResponseContains('"total":2');
//$I->seeResponseContains('"owner":');

$I->wantTo('Unassgngn securitygroup '.$elementId);
$I->sendDELETE('Users/'.$userId.'/securitygroups/'.$elementId);
$I->seeResponseCodeIs(200);

$I->wantTo('Unassgngn securitygroup '.$elementId2);
$I->sendDELETE('Users/'.$userId.'/securitygroups/'.$elementId2);
$I->seeResponseCodeIs(200);


$I->wantTo('Delete created User'.$userId);
$I->sendDELETE('users/'.$userId);
$I->seeResponseCodeIs(200);

$I->wantTo('Delete created SecurityGroups'.$elementId);
$I->sendDELETE('securityGroups/'.$elementId);
$I->seeResponseCodeIs(200);

$I->wantTo('Delete created SecurityGroups'.$elementId2);
$I->sendDELETE('securityGroups/'.$elementId);
$I->seeResponseCodeIs(200);


?>

