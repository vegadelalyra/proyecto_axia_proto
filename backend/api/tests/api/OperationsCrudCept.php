<?php 
$randomNumber = rand(0,1000);
$I = new ApiTester($scenario);
$I->wantTo('Create operation');
$I->amHttpAuthenticated(testUser, testPassword);
$I->haveHttpHeader('Content-Type', 'application/json');
$I->sendPOST('operations','{"Name":"Testoperation'.$randomNumber.'"}');
$response = $I->grabResponse();
$I->seeResponseCodeIs(200);
$I->seeResponseContains('{"Id":');
$I->seeResponseContains('"Securitygroup":');    
$jsonResponse = json_decode(substr($response, strpos($response,'{')));
$elementId = $jsonResponse->Id;

$I->wantTo('Retrieve created operation '.$elementId);
$I->sendGET('operations/'.$elementId);
$I->seeResponseCodeIs(200);
$I->seeResponseContains('{"Id":');
$I->seeResponseContains('"Securitygroup":');  

$I->wantTo('Update created operation '.$elementId);
$I->sendPUT('operations/'.$elementId,'{"Name":"TestoperationGuay"}');
$I->seeResponseCodeIs(200);
$I->seeResponseContains('"Name":"TestoperationGuay');

$I->wantTo('Delete created SecurityGroups'.$elementId);
$I->sendDELETE('operations/'.$elementId);
$I->seeResponseCodeIs(200);

$I->wantTo('Check if deleted SecurityGroup'.$elementId);
$I->sendGET('operations/'.$elementId);
$I->seeResponseCodeIs(404);
?>

