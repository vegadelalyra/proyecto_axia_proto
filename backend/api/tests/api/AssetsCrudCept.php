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

$I->wantTo('Fail to create asset');
$I->sendPOST('assets','{"Name":"TestElement'.$randomNumber.'", "family":98765443}');
$response = $I->grabResponse();
$I->seeResponseCodeIs(403);

$I->wantTo('Create assets');
$I->sendPOST('assets','{"Code":"TCodeAsset'.$randomNumber.'", "Name":"TestAsset'.$randomNumber.'", "family":'.$familyId.'}');
$response = $I->grabResponse();
$I->seeResponseCodeIs(200);
$I->seeResponseContains('{"Id":');
$I->seeResponseContains('"Family":');    
$I->seeResponseContains('"Securitygroup":');
$jsonResponse = json_decode(substr($response, strpos($response,'{')));
$elementId = $jsonResponse->Id;



$I->wantTo('Retrieve created asset '.$elementId);
$I->sendGET('assets/'.$elementId);
$I->seeResponseCodeIs(200);
$I->seeResponseContains('{"Id":');
$I->seeResponseContains('"Family":');  

$I->wantTo('Update created asset '.$elementId);
$I->sendPUT('assets/'.$elementId,'{"name":"updated"}');
$I->seeResponseCodeIs(200);
$I->seeResponseContains('"Name":"updated"');

$I->wantTo('Delete created asset'.$elementId);
$I->sendDELETE('assets/'.$elementId);
$I->seeResponseCodeIs(200);

$I->wantTo('Delete created family'.$elementId);
$I->sendDELETE('families/'.$familyId);
$I->seeResponseCodeIs(200);

$I->wantTo('Check if deleted element'.$elementId);
$I->sendGET('assets/'.$elementId);
$I->seeResponseCodeIs(404);
?>
