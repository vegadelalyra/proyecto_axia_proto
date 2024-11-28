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

$I->wantTo('Update created location '.$elementId);
$I->sendPUT('locations/'.$elementId,'{"lat":43.78}');
$I->seeResponseCodeIs(200);
$I->seeResponseContains('"Lat":43.78');

$I->wantTo('Delete created SecurityGroups'.$elementId);
$I->sendDELETE('locations/'.$elementId);
$I->seeResponseCodeIs(200);

$I->wantTo('Check if deleted SecurityGroup'.$elementId);
$I->sendGET('locations/'.$elementId);
$I->seeResponseCodeIs(404);
?>

