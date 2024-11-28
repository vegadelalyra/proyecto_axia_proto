<?php 
$randomNumber = rand(0,1000);
$I = new ApiTester($scenario);
$I->wantTo('Create Family');
$I->amHttpAuthenticated(testUser, testPassword);
$I->haveHttpHeader('Content-Type', 'application/json');
$I->sendPOST('families','{"Name":"TestFamily'.$randomNumber.'"}');
$response = $I->grabResponse();
$I->seeResponseCodeIs(200);
$I->seeResponseContains('{"Id":');
$I->seeResponseContains('"Securitygroup":');    
$jsonResponse = json_decode(substr($response, strpos($response,'{')));
$elementId = $jsonResponse->Id;

$I->wantTo('Retrieve created Family '.$elementId);
$I->sendGET('families/'.$elementId);
$I->seeResponseCodeIs(200);
$I->seeResponseContains('{"Id":');
$I->seeResponseContains('"Securitygroup":');  

$I->wantTo('Update created lfamily '.$elementId);
$I->sendPUT('families/'.$elementId,'{"name":"updated"}');
$I->seeResponseCodeIs(200);
$I->seeResponseContains('"Name":"updated"');

$I->wantTo('Delete created family'.$elementId);
$I->sendDELETE('families/'.$elementId);
$I->seeResponseCodeIs(200);

$I->wantTo('Check if deleted family'.$elementId);
$I->sendGET('families/'.$elementId);
$I->seeResponseCodeIs(404);
?>

