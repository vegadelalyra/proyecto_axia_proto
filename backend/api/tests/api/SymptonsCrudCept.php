<?php 
$randomNumber = rand(0,1000);
$I = new ApiTester($scenario);
$I->amHttpAuthenticated(testUser, testPassword);
$I->haveHttpHeader('Content-Type', 'application/json');

$I->wantTo('Create Family');
$I->sendPOST('families','{"Name":"TestElement'.$randomNumber.'"}');
$response = $I->grabResponse();
$I->seeResponseCodeIs(200);
$I->seeResponseContains('{"Id":');
$I->seeResponseContains('"Securitygroup":');    
$jsonResponse = json_decode(substr($response, strpos($response,'{')));
$familyId = $jsonResponse->Id;

$I->wantTo('Fail to create element');
$I->sendPOST('symptoms','{"Name":"TestElement'.$randomNumber.'", "family":98765443}');
$response = $I->grabResponse();
$I->seeResponseCodeIs(403);

$I->wantTo('Create element');
$I->sendPOST('symptoms','{"Name":"TestElement'.$randomNumber.'", "family":'.$familyId.'}');
$response = $I->grabResponse();
$I->seeResponseCodeIs(200);
$I->seeResponseContains('{"Id":');
$I->seeResponseContains('"Family":');    
$jsonResponse = json_decode(substr($response, strpos($response,'{')));
$elementId = $jsonResponse->Id;



$I->wantTo('Retrieve created element '.$elementId);
$I->sendGET('symptoms/'.$elementId);
$I->seeResponseCodeIs(200);
$I->seeResponseContains('{"Id":');
$I->seeResponseContains('"Family":');  

$I->wantTo('Update created element '.$elementId);
$I->sendPUT('symptoms/'.$elementId,'{"name":"updated"}');
$I->seeResponseCodeIs(200);
$I->seeResponseContains('"Name":"updated"');

$I->wantTo('Delete created element'.$elementId);
$I->sendDELETE('symptoms/'.$elementId);
$I->seeResponseCodeIs(200);

$I->wantTo('Delete created family'.$elementId);
$I->sendDELETE('families/'.$familyId);
$I->seeResponseCodeIs(200);

$I->wantTo('Check if deleted element'.$elementId);
$I->sendGET('symptoms/'.$elementId);
$I->seeResponseCodeIs(404);
?>

