<?php

header("Access-Control-Allow-Origin: *");


// Permitir los métodos HTTP que se pueden utilizar
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");

header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, Origin, Accept");


// Si la solicitud es un preflight (OPTIONS), devolver un 200 sin procesar la solicitud
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
	http_response_code(200);
	exit();
}


require_once __DIR__ . "/../data/db.php";
require_once __DIR__ . "/cryptography.php";

class UserAuthenticator
{

	private $proxy;


	public function __construct()
	{
		$this->proxy = DbProxyBuilder::getProxy();
	}

	public function checkToken($token)
	{
		$response = $this->proxy->checkLicense($token);
		if (isset($response) && !empty($response) && $response[0]['active'] == 1 && (strtotime($response[0]['Expiration']) > time())) {
			if (count($response) != 1) {
				return new Exception("Not Found", 404);
			} else {
				return $response;
			}
		} else if (isset($response) && !empty($response) && $response[0]['active'] == 0) {
			return new Exception("License not active", 404);
		} else if (isset($response) && !empty($response) && (strtotime($response[0]['Expiration']) < time())) {
			return new Exception("Expired license", 404);
		} else {
			return new Exception("Bad Request", 400);
		}
	}

	public function checkEncToken($encToken)
	{
		if (ctype_xdigit($encToken)) {
			$encodedString = hex2bin($encToken);
			$decoded = decrypt($encodedString);
			$theData = explode(':', $decoded);
			$result = $this->loginEnc($theData[0], $theData[1]);
			return $result;
		} else
			return null;
	}

	public function getToken($userId, $token)
	{
		$user = $this->proxy->getUser($userId);
		if (isset($user) && sizeof($user) == 1) {
			return "#" . $user[0]["id"] . "#" . md5($user[0]["id"] . $user[0]["password"]);
		} else
			return null;
	}

	public function login($userId, $password)
	{
		$user = $this->proxy->getUser($userId);
		if (isset($user) && sizeof($user) == 1) {
			if (md5($password) == $user[0]["Password"]) {
				$this->code = $userId;
				$user[0]["LoggedPassword"] = $password;
				$LoggedUser = $user[0];
				$roles = $this->proxy->genericGetSubelements($LoggedUser["Id"], "Users_Id", "Roles_Id", "Id", "Users_has_roles", "Roles", null, 100, null, null, null, null);
				$LoggedUser["roles"] = array();
				foreach ($roles->data as $rol) {
					$LoggedUser["roles"][$rol["Key"]] = $rol["Id"];
					if ($LoggedUser["DefaultRol"] == $rol["Id"])
						$LoggedUser["rol"] = $rol["Key"];
				}
				return $LoggedUser;
			} else {
				$this->code = null;
				return null;
			}
		} else
			return null;
	}

	private function loginEnc($userId, $md5password)
	{
		$user = $this->proxy->getUser($userId);
		if (isset($user) && sizeof($user) == 1) {
			if ($md5password == $user[0]["Password"]) {
				$this->code = $userId;
				return $user[0];
			} else {
				$this->code = null;
				return null;
			}
		} else
			return null;
	}
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true); // Lee datos JSON enviados desde Axios
    $userId = $input['userId'] ?? null;
    $password = $input['password'] ?? null;

    if ($userId && $password) {
        $authenticator = new UserAuthenticator();
        $result = $authenticator->login($userId, $password);

        if ($result) {
            echo json_encode(['success' => true, 'data' => $result]);
        } else {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Invalid credentials']);
        }
    } else {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Missing userId or password']);
    }
}
?>