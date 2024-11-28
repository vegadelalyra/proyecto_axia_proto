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

$I->wantTo('Assign sympton to element');
$I->sendPOST('elements/'.$elementId.'/symptoms',''.$symptomId.'');
$response = $I->grabResponse();
$I->seeResponseCodeIs(200);
$I->seeResponseContains('"symptoms_id":'.$symptomId);
$I->seeResponseContains('"elements_id":'.$elementId);

$I->wantTo('Retrieve assigned symptom ');
$I->sendGET('elements/'.$elementId.'/symptoms');
$I->seeResponseCodeIs(200);
$I->seeResponseContains('"total":1');


$I->wantTo('Unassgngn symptoms '.$symptomId);
$I->sendDELETE('elements/'.$elementId.'/symptoms/'.$symptomId);
$I->seeResponseCodeIs(200);


$I->wantTo('Delete created symptom'.$symptomId);
$I->sendDELETE('symptoms/'.$symptomId);
$I->seeResponseCodeIs(200);


$I->wantTo('Delete created element'.$elementId);
$I->sendDELETE('elements/'.$elementId);
$I->seeResponseCodeIs(200);

$I->wantTo('Delete created family'.$elementId);
$I->sendDELETE('families/'.$familyId);
$I->seeResponseCodeIs(200);

$I->wantTo('Check if deleted element'.$elementId);
$I->sendGET('elements/'.$elementId);
$I->seeResponseCodeIs(404);
?>

