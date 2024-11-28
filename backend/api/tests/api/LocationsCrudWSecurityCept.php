<?php 
$randomNumber = rand(0,1000);
$I = new ApiTester($scenario);
$I->wantTo('Create Location');
$I->amHttpAuthenticated(testUser, testPassword);
$I->haveHttpHeader('Content-Type', 'application/json');
$I->sendPOST('Locations','{"Name":"TestLocation'.$randomNumber.'","lat":43.67 ,"lng":4.67}');
$response = $I->grabResponse();
$I->seeResponseCodeIs(200);
$I->seeResponseContains('{"Id":');
$I->seeResponseContains('"Securitygroup":');    
$jsonResponse = json_decode(substr($response, strpos($response,'{')));
$elementId = $jsonResponse->Id;

$I->wantTo('Retrieve created Location '.$elementId);
$I->sendGET('locations/'.$elementId);
$I->seeResponseCodeIs(200);
$I->seeResponseContains('{"Id":');
$I->seeResponseContains('"Securitygroup":');  

$I->sendPOST('SecurityGroups','{"Name":"TestGroup'.$randomNumber.'","description":"Test group descriptionr'.$randomNumber.'"}');
$response = $I->grabResponse();
$I->seeResponseCodeIs(200);
$I->seeResponseContains('{"Id":');
$jsonResponse = json_decode(substr($response, strpos($response,'{')));
$securityId = $jsonResponse->Id;

$I->wantTo('Create user for test');
$I->sendPOST('Users','{"name":"TestUser'.$randomNumber.'","User":"TestUser'.$randomNumber.'","Password":"7488e331b8b64e5794da3fa4eb10ad5d","IsSystemAdmin":0, "DefaultSecurityGroup":'.$securityId.', "DefaultRol":1,"owner":null}');

$response = $I->grabResponse();
$I->seeResponseCodeIs(200);
$I->seeResponseContains('{"Id":');
$jsonResponse = json_decode(substr($response, strpos($response,'{')));
$userId = $jsonResponse->Id;

$I->wantTo('Assign groups to user');
$I->sendPOST('Users/'.$userId.'/securitygroups',"".$securityId."");
$response = $I->grabResponse();
$I->seeResponseCodeIs(200);
$I->seeResponseContains('"securitygroups_id":'.$securityId);


$I->amHttpAuthenticated('TestUser'.$randomNumber, 'admin12345');
$I->haveHttpHeader('Content-Type', 'application/json');

$I->wantTo('Fail creating');
$I->sendPOST('Locations','{"Name":"TestLocation2-'.$randomNumber.'","lat":43 ,"lng":4}');
$response = $I->grabResponse();
$I->seeResponseCodeIs(403);

$I->amHttpAuthenticated(testUser, testPassword);
$I->haveHttpHeader('Content-Type', 'application/json');

$I->wantTo('Assign rol to user');
$I->sendPOST('Users/'.$userId.'/roles','1');
$response = $I->grabResponse();
$I->seeResponseCodeIs(200);

$I->amHttpAuthenticated('TestUser'.$randomNumber, 'admin12345');
$I->haveHttpHeader('Content-Type', 'application/json');


$I->wantTo('Create Location');
$I->sendPOST('Locations','{"Name":"TestLocation2-'.$randomNumber.'","lat":43 ,"lng":4}');
$response = $I->grabResponse();
$I->seeResponseCodeIs(200);

$I->seeResponseContains('{"Id":');
$I->seeResponseContains('"Securitygroup":');    
$jsonResponse = json_decode(substr($response, strpos($response,'{')));
$elementId2 = $jsonResponse->Id;

$I->wantTo('Retrieve Locations ');
$I->sendGET('locations/');
$I->seeResponseCodeIs(200);
$I->seeResponseContains('"total":1');

$I->wantTo('Fail to retrieve location from other ');
$I->sendGET('locations/'.$elementId);
$I->seeResponseCodeIs(404);


$I->wantTo('Delete created location'.$elementId2);
$I->sendDELETE('locations/'.$elementId2);
$I->seeResponseCodeIs(200);

$I->amHttpAuthenticated(testUser, testPassword);
$I->haveHttpHeader('Content-Type', 'application/json');

$I->wantTo('Unassgngn securitygroup '.$securityId);
$I->sendDELETE('Users/'.$userId.'/securitygroups/'.$securityId);
$I->seeResponseCodeIs(200);


$I->wantTo('Delete created User'.$userId);
$I->sendDELETE('users/'.$userId);
$I->seeResponseCodeIs(200);

$I->wantTo('Delete created SecurityGroups'.$securityId);
$I->sendDELETE('securityGroups/'.$securityId);
$I->seeResponseCodeIs(200);

$I->wantTo('Delete created location'.$elementId);
$I->sendDELETE('locations/'.$elementId);
$I->seeResponseCodeIs(200);

?>

