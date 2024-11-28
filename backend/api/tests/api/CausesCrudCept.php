<?php 
$randomNumber = rand(0,1000);
$I = new ApiTester($scenario);
$I->wantTo('Create Cause');
$I->amHttpAuthenticated(testUser, testPassword);
$I->haveHttpHeader('Content-Type', 'application/json');
$I->sendPOST('Causes','{"Name":"TestCause'.$randomNumber.'"}');
$response = $I->grabResponse();
$I->seeResponseCodeIs(200);
$I->seeResponseContains('{"Id":');
$I->seeResponseContains('"Securitygroup":');    
$jsonResponse = json_decode(substr($response, strpos($response,'{')));
$elementId = $jsonResponse->Id;

$I->wantTo('Retrieve created Cause '.$elementId);
$I->sendGET('Causes/'.$elementId);
$I->seeResponseCodeIs(200);
$I->seeResponseContains('{"Id":');
$I->seeResponseContains('"Securitygroup":');  

$I->wantTo('Update created Cause '.$elementId);
$I->sendPUT('Causes/'.$elementId,'{"Name":"TestCauseGuay"}');
$I->seeResponseCodeIs(200);
$I->seeResponseContains('"Name":"TestCauseGuay');

$I->wantTo('Delete created SecurityGroups'.$elementId);
$I->sendDELETE('Causes/'.$elementId);
$I->seeResponseCodeIs(200);

$I->wantTo('Check if deleted SecurityGroup'.$elementId);
$I->sendGET('Causes/'.$elementId);
$I->seeResponseCodeIs(404);
?>

