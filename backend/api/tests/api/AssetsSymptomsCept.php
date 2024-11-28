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

$I->wantTo('Create element');
$I->sendPOST('elements','{"Name":"TestElement'.$randomNumber.'", "family":'.$familyId.'}');
$response = $I->grabResponse();
$I->seeResponseCodeIs(200);
$I->seeResponseContains('{"Id":');
$I->seeResponseContains('"Family":');    
$jsonResponse = json_decode(substr($response, strpos($response,'{')));
$elementId = $jsonResponse->Id;

$I->wantTo('Create Symptom');
$I->sendPOST('symptoms','{"Name":"TestElement'.$randomNumber.'", "family":'.$familyId.'}');
$response = $I->grabResponse();
$I->seeResponseCodeIs(200);
$I->seeResponseContains('{"Id":');
$I->seeResponseContains('"Family":');    
$jsonResponse = json_decode(substr($response, strpos($response,'{')));
$symptomId = $jsonResponse->Id;

$I->wantTo('Create Symptom2');
$I->sendPOST('symptoms','{"Name":"TestElement'.$randomNumber.'", "family":'.$familyId.'}');
$response = $I->grabResponse();
$I->seeResponseCodeIs(200);
$I->seeResponseContains('{"Id":');
$I->seeResponseContains('"Family":');    
$jsonResponse = json_decode(substr($response, strpos($response,'{')));
$symptomId2 = $jsonResponse->Id;


$I->wantTo('Create assets');
$I->sendPOST('assets','{"Code":"TCodeAsset'.$randomNumber.'", "Name":"TestAsset'.$randomNumber.'", "family":'.$familyId.'}');
$response = $I->grabResponse();
$I->seeResponseCodeIs(200);
$I->seeResponseContains('{"Id":');
$I->seeResponseContains('"Family":');    
$I->seeResponseContains('"Securitygroup":');
$jsonResponse = json_decode(substr($response, strpos($response,'{')));
$assetId = $jsonResponse->Id;

$I->wantTo('Create Alert');
$I->sendPOST('alerts','{"Origin":"Test", "Asset":"'.$assetId.'", "Notes":"Test notes'.$randomNumber.'", "details":[{"Symptom":'.$symptomId.'},{"Symptom":'.$symptomId.', "Element":'.$elementId.'},{"Symptom":'.$symptomId2.'}]}');
$response = $I->grabResponse();
$I->seeResponseCodeIs(200);
$I->seeResponseContains('{"Id":');
$I->seeResponseContains('"owner":');    
$jsonResponse = json_decode(substr($response, strpos($response,'{')));
$alertId = $jsonResponse->Id;

$I->wantTo('Retrieve active alerts from asset');
$I->sendGET('assets/'.$assetId.'/symptoms');
$I->seeResponseCodeIs(200);
$I->seeResponseContains('"total":3');
$I->seeResponseContains('"Status":');  
$I->seeResponseContains('"Symptom":'.$symptomId);

$I->wantTo('Update created Alert detail '.$alertId);
$I->sendPUT('alerts/'.$alertId,'{"details":[{"Status":3, "Symptom":'.$symptomId2.', "Element":'.$elementId.'}]}');
$I->seeResponseCodeIs(200);
$I->seeResponseContains('"Symptom":'.$symptomId2);
$I->cantSeeResponseContains('"Symptom":'.$symptomId);

$I->wantTo('Retrieve empty alerts from asset');
$I->sendGET('assets/'.$assetId.'/symptoms');
$I->seeResponseCodeIs(200);
$I->seeResponseContains('"total":0');

    
$I->wantTo('Delete created alert'.$alertId);
$I->sendDELETE('alerts/'.$alertId);
$I->seeResponseCodeIs(200);

$I->wantTo('Delete created asset'.$assetId);
$I->sendDELETE('assets/'.$assetId);
$I->seeResponseCodeIs(200);

$I->wantTo('Delete created element'.$elementId);
$I->sendDELETE('elements/'.$elementId);
$I->seeResponseCodeIs(200);

$I->wantTo('Delete created symptom'.$symptomId);
$I->sendDELETE('symptoms/'.$symptomId);
$I->seeResponseCodeIs(200);

$I->wantTo('Delete created symptom'.$symptomId2);
$I->sendDELETE('symptoms/'.$symptomId2);
$I->seeResponseCodeIs(200);

$I->wantTo('Delete created family'.$familyId);
$I->sendDELETE('families/'.$familyId);
$I->seeResponseCodeIs(200);

$I->wantTo('Check if deleted alert'.$alertId);
$I->sendGET('alerts/'.$alertId);
$I->seeResponseCodeIs(404);
?>

