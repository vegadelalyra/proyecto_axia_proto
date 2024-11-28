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

$I->sendPOST('Locations','{"Name":"TestLocation'.$randomNumber.'","lat":43.67 ,"lng":4.67}');
$response = $I->grabResponse();
$I->seeResponseCodeIs(200);
$I->seeResponseContains('{"Id":');
$I->seeResponseContains('"Securitygroup":');    
$jsonResponse = json_decode(substr($response, strpos($response,'{')));
$locationId = $jsonResponse->Id;

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

$I->wantTo('Create cause');
$I->sendPOST('Causes','{"Name":"TestCause'.$randomNumber.'"}');
$response = $I->grabResponse();
$I->seeResponseCodeIs(200);
$I->seeResponseContains('{"Id":');
$I->seeResponseContains('"Securitygroup":');    
$jsonResponse = json_decode(substr($response, strpos($response,'{')));
$causeId = $jsonResponse->Id;

$I->wantTo('Create Alert');
$I->sendPOST('alerts','{"Origin":"Test", "Asset":"'.$assetId.'", "Notes":"Test notes'.$randomNumber.'", "details":[{"Symptom":'.$symptomId.'},{"Symptom":'.$symptomId.', "Element":'.$elementId.'},{"Symptom":'.$symptomId2.'}]}');
$response = $I->grabResponse();
$I->seeResponseCodeIs(200);
$I->seeResponseContains('{"Id":');
$I->seeResponseContains('"owner":');    
$jsonResponse = json_decode(substr($response, strpos($response,'{')));
$alertId = $jsonResponse->Id;
$alertDetailId=$jsonResponse->details[0]->Id;



$I->wantTo('Create Order');
$I->sendPOST('orders','{"Destination":"'.$locationId.'", "Asset":"'.$assetId.'", "Notes":"Test notes'.$randomNumber.'", "details":[{"Symptom":'.$symptomId.',"Cause":'.$causeId.'},{"Symptom":'.$symptomId.', "Element":'.$elementId.',"Cause":'.$causeId.'},{"Symptom":'.$symptomId2.',"Cause":'.$causeId.'}]}');
$response = $I->grabResponse();
$I->seeResponseCodeIs(200);
$I->seeResponseContains('{"Id":');
$I->seeResponseContains('"owner":');    
$jsonResponse = json_decode($response);
$orderId = $jsonResponse->Id;

$I->wantTo('Retrieve created order '.$orderId);
$I->sendGET('orders/'.$orderId);
$I->seeResponseCodeIs(200);
$I->seeResponseContains('{"Id":');
$I->seeResponseContains('"owner":');  
$I->seeResponseContains('"details":');
$I->seeResponseContains('"created":');
$I->seeResponseContains('"Symptom":'.$symptomId);

$I->wantTo('Retrieve orders ');
$I->sendGET('orders/');
$I->seeResponseCodeIs(200);
$I->seeResponseContains('total');
$I->seeResponseContains('"owner":');  
$I->seeResponseContains('"details":[{');
$I->seeResponseContains('"Symptom":'.$symptomId);

$I->wantTo('Update created order '.$orderId);
$I->sendPUT('orders/'.$orderId,'{"notes":"updated"}');
$I->seeResponseCodeIs(200);
$I->seeResponseContains('"Notes":"updated"');

$I->wantTo('Retrieve created order '.$orderId);
$I->sendGET('orders/'.$orderId);
$I->seeResponseCodeIs(200);
$I->seeResponseContains('{"Id":');
$I->seeResponseContains('"owner":');  
$I->seeResponseContains('"details":[{');
$I->seeResponseContains('"created":');
$I->seeResponseContains('"Symptom":'.$symptomId);

$I->wantTo('Update created Order detail '.$orderId);
$I->sendPUT('orders/'.$orderId,'{"details":[{"Symptom":'.$symptomId2.', "Element":'.$elementId.',"Cause":'.$causeId.',"AlertDetail":'.$alertDetailId.'}]}');
$I->seeResponseCodeIs(200);
$I->seeResponseContains('"Symptom":'.$symptomId2);
$I->seeResponseContains('"AlertDetail":'.$alertDetailId);
$I->cantSeeResponseContains('"Symptom":'.$symptomId);

$I->wantTo('Retrieve created order '.$orderId);
$I->sendGET('orders/'.$orderId);
$I->seeResponseCodeIs(200);
$I->seeResponseContains('{"Id":');
$I->seeResponseContains('"owner":');  
$I->seeResponseContains('"details":');
$I->seeResponseContains('"created":');
$I->seeResponseContains('"AlertDetail":'.$alertDetailId);



$I->wantTo('Retrieve user');
$I->sendGET('users/0/');
$response = $I->grabResponse();
$I->seeResponseCodeIs(200);
$I->seeResponseContains('{"Id":'); 
$jsonResponse = json_decode($response);
$userId = $jsonResponse->Id;

$I->wantTo('Change rol to curator');
$I->sendPUT('users/'.$userId,'{"DefaultRol":3}');
$I->seeResponseCodeIs(200);
$I->seeResponseContains('"DefaultRol":3');


$I->wantTo('Retrieve created orders as curator');
$I->sendGET('orders/');
$I->seeResponseCodeIs(200);
$I->seeResponseContains('{"Id":');
$I->seeResponseContains('"owner":');  
$I->seeResponseContains('"details":');
$I->seeResponseContains('"created":');
$I->seeResponseContains('"AlertDetail":'.$alertDetailId);
$I->seeResponseContains('{"Id":'.$orderId);



$I->wantTo('Change rol to repair planner');
$I->sendPUT('users/'.$userId,'{"DefaultRol":4}');
$I->seeResponseCodeIs(200);
$I->seeResponseContains('"DefaultRol":4');


$I->wantTo('Retrieve created orders as repair planer');
$I->sendGET('orders/');
$I->seeResponseCodeIs(200);
$I->seeResponseContains('{"Id":');
$I->seeResponseContains('"owner":');  
$I->seeResponseContains('"details":');
$I->seeResponseContains('"created":');
$I->seeResponseContains('"AlertDetail":'.$alertDetailId);


$I->wantTo('Change rol to quality supervisor');
$I->sendPUT('users/'.$userId,'{"DefaultRol":7}');
$I->seeResponseCodeIs(200);
$I->seeResponseContains('"DefaultRol":7');


$I->wantTo('Retrieve created orders as as quality supervisor');
$I->sendGET('orders/');
$I->seeResponseCodeIs(200);
$I->seeResponseContains('{"Id":');
$I->seeResponseContains('"owner":');  
$I->seeResponseContains('"details":');
$I->seeResponseContains('"created":');
$I->seeResponseContains('"AlertDetail":'.$alertDetailId);

$I->wantTo('Retrieve created orders as as quality supervisor');
$I->sendGET('orders/');
$I->seeResponseCodeIs(200);
$I->seeResponseContains('"owner":');  
$I->seeResponseContains('"details":');
$I->seeResponseContains('"created":');
$I->seeResponseContains('"AlertDetail":'.$alertDetailId);

$I->wantTo('Change rol to operator');
$I->sendPUT('users/'.$userId,'{"DefaultRol":5}');
$I->seeResponseCodeIs(200);
$I->seeResponseContains('"DefaultRol":5');

$I->wantTo('Retrieve unasigned activities as Operator');
$I->sendGET('activities/');
$I->seeResponseCodeIs(200); 
$I->cantSeeResponseContains('"AlertDetail":'.$alertDetailId);

$I->wantTo('Change rol to repair planner');
$I->sendPUT('users/'.$userId,'{"DefaultRol":4}');
$I->seeResponseCodeIs(200);
$I->seeResponseContains('"DefaultRol":4');


$I->wantTo('Asign activitie '.$orderId);
$I->sendPUT('orders/'.$orderId,'{"details":[{"Symptom":'.$symptomId2.', "Status": 1, "Solver": 2, "Element":'.$elementId.',"Cause":'.$causeId.',"AlertDetail":'.$alertDetailId.'}]}');
$I->seeResponseCodeIs(200);
$I->seeResponseContains('"Symptom":'.$symptomId2);
$I->seeResponseContains('"AlertDetail":'.$alertDetailId);
$I->seeResponseContains('"Symptom":'.$symptomId2);


$I->wantTo('Change rol to operator');
$I->sendPUT('users/'.$userId,'{"DefaultRol":5}');
$I->seeResponseCodeIs(200);
$I->seeResponseContains('"DefaultRol":5');

$I->wantTo('Retrieve asigned activities as Operator');
$I->sendGET('activities/');
$I->seeResponseCodeIs(200);
$I->seeResponseContains('{"Id":');
$I->seeResponseContains('"AlertDetail":'.$alertDetailId);



$I->wantTo('Delete created order'.$orderId);
$I->sendDELETE('orders/'.$orderId);
$I->seeResponseCodeIs(200);
    
$I->wantTo('Delete created alert'.$alertId);
$I->sendDELETE('alerts/'.$alertId);
$I->seeResponseCodeIs(200);

$I->wantTo('Delete created cause'.$causeId);
$I->sendDELETE('Causes/'.$causeId);
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

$I->wantTo('Delete created location'.$locationId);
$I->sendDELETE('locations/'.$locationId);
$I->seeResponseCodeIs(200);

$I->wantTo('Delete created family'.$familyId);
$I->sendDELETE('families/'.$familyId);
$I->seeResponseCodeIs(200);

$I->wantTo('Check if deleted order'.$orderId);
$I->sendGET('alerts/'.$orderId);
$I->seeResponseCodeIs(404);
?>

