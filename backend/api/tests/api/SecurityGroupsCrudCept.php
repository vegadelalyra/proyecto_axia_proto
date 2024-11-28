<?php 
$randomNumber = rand(0,1000);
$I = new ApiTester($scenario);
$I->wantTo('Create SecurityGroup');
$I->amHttpAuthenticated(testUser, testPassword);
$I->haveHttpHeader('Content-Type', 'application/json');
$I->sendPOST('SecurityGroups','{"Name":"TestGroup'.$randomNumber.'","description":"Test group descriptionr'.$randomNumber.'"}');
$response = $I->grabResponse();
$I->seeResponseCodeIs(200);
$I->seeResponseContains('{"Id":');
$jsonResponse = json_decode(substr($response, strpos($response,'{')));
$elementId = $jsonResponse->Id;

$I->wantTo('Retrieve created SecurityGroup '.$elementId);
$I->sendGET('securityGroups/'.$elementId);
$I->seeResponseCodeIs(200);
$I->seeResponseContains('{"Id":');
$I->seeResponseContains('"owner":');

$I->wantTo('Update created securityGroup '.$elementId);
$I->sendPUT('securityGroups/'.$elementId,'{"DescriPtion":" Updated Test group descriptionr'.$randomNumber.'"}');
$I->seeResponseCodeIs(200);
$I->seeResponseContains('"Description":" Updated ');

$I->wantTo('Delete created SecurityGroups'.$elementId);
$I->sendDELETE('securityGroups/'.$elementId);
$I->seeResponseCodeIs(200);

$I->wantTo('Check if deleted SecurityGroup'.$elementId);
$I->sendGET('securityGroups/'.$elementId);
$I->seeResponseCodeIs(403);
?>

