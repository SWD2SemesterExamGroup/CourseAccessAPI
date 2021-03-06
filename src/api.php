<?php
	class Database
	{
		const 
			// Database settings
			DB_HOST		= 'localhost',
			DB_USERNAME	= 'root',
			DB_PASSWORD	= '',
			DB_DATABASE	= 'courseaccess_db';
		
		private static $instance = null;
		
		private function __construct() {}
		private function __clone() {}

		public static function GetInstance()
		{
			if (!isset(self::$instance)) {
				$db = new mysqli(self::DB_HOST, self::DB_USERNAME, self::DB_PASSWORD, self::DB_DATABASE);
				
				if ($db->connect_error) {
					return null;
				}

				self::$instance = $db;
			}

			return self::$instance;
		}

		public static function CreateKey($password, $startdate, $expiredate)
		{
			$db = self::GetInstance();
			$sql = "INSERT INTO `generatedkeys` (`GeneratedPassword`, `StartTimeStamp`, `ExpirationTimeStamp`) VALUES (?, ?, ?)";
			$stmt = $db->prepare($sql);
			$stmt->bind_param('sss', $password, $startdate, $expiredate);
			
			$stmt->execute();
			$insertid = $stmt->insert_id;
			$stmt->close();

			return $insertid;
		}
		
		public static function CreateCourse($courseid, $classid, $teacherid)
		{
			$db = self::GetInstance();
			$sql = "INSERT INTO `coursepasswords` (`CourseID`, `ClassID`, `TeacherID`) VALUES (?, ?, ?)";
			$stmt = $db->prepare($sql);
			$stmt->bind_param('sss', $courseid, $classid, $teacherid);
			
			$stmt->execute();
			$insertid = $stmt->insert_id;
			$stmt->close();

			return $insertid;
		}
		
		public static function CreateKeyCoursePair($courseid, $keyid)
		{
			$db = self::GetInstance();
			$sql = "INSERT INTO `gkcps` (`CPID`, `GKID`) VALUES (?, ?)";
			$stmt = $db->prepare($sql);
			$stmt->bind_param('ii', $courseid, $keyid);
			
			$success = $stmt->execute();
			$stmt->close();

			return $success;
		}

		public static function GetKeyByID($keyid)
		{
			$db = self::GetInstance();
			$sql = "SELECT `ID`, `GeneratedPassword`, `StartTimeStamp`, `ExpirationTimeStamp` FROM `generatedkeys` WHERE `ID` = ? LIMIT 1";
			$key = null;

			$stmt = $db->prepare($sql);
			$stmt->bind_param('i', $keyid);

			if ($stmt->execute()) {
				$stmt->bind_result($key['ID'], $key['GeneratedPassword'], $key['StartTimeStamp'], $key['ExpirationTimeStamp']);

				if (!$stmt->fetch())
					$key = null;
			}

			$stmt->close();

			return $key;
		}
		
		public static function IsPasswordValid($password)
		{
			$db = self::GetInstance();
			$sql = "SELECT `coursepasswords`.`CourseID`
				FROM `generatedkeys`, `coursepasswords`, `gkcps`
				WHERE `GeneratedPassword` = ? AND NOW() BETWEEN `StartTimeStamp` AND `ExpirationTimeStamp`
				AND `gkcps`.`GKID` = `generatedkeys`.`ID`
				AND `gkcps`.`CPID` = `coursepasswords`.`ID`
				LIMIT 1";
			
			$key = null;

			$stmt = $db->prepare($sql);
			$stmt->bind_param('s', $password);

			if ($stmt->execute()) {
				$stmt->bind_result($key['CourseID']);

				if (!$stmt->fetch())
					$key = null;
			}

			$stmt->close();

			return $key;
		}
	}
	
	//  Usage example:
	//  api.php/generatedkeys/[id]
	//  api.php/keycheck/[password]
	//  api.php/post
	//     Post fields required: "password=XX&startdate=XX&expiredate=XX&courseid=XX&classid=XX&teacherid=XX"
	
	$request = explode('/', trim($_SERVER['PATH_INFO'] ?? '', '/'));
	$table = preg_replace('/[^a-z0-9_]+/i', '', $request[0] ?? null);
	$key = $request[1] ?? 0;
	
	if ($table) {
		if ($table == 'post') {
			if (!isset($_POST['password']) || !isset($_POST['startdate']) || !isset($_POST['expiredate']) || !isset($_POST['courseid']) || !isset($_POST['classid']) || !isset($_POST['teacherid'])) {
				echo json_encode(array('success' => 'false'));
				exit;
			}
			
			$keyid = Database::CreateKey($_POST['password'], $_POST['startdate'], $_POST['expiredate']);
			$courseid = Database::CreateCourse($_POST['courseid'], $_POST['classid'], $_POST['teacherid']);
			
			echo json_encode(array('success' => Database::CreateKeyCoursePair($courseid, $keyid) ? 'true' : 'false'));
		} elseif ($table == 'generatedkeys' && $key > 0) {
			echo json_encode(Database::GetKeyByID($key) ?? array('success' => 'false'));
		} elseif ($table == 'keycheck') {
			$ary = array();
			$coursekey = Database::IsPasswordValid($key);
			
			if (isset($coursekey)) {
				$ary['success'] = 'true';
				$ary['CourseID'] = $coursekey['CourseID'];
			} else {
				$ary['success'] = 'false';
			}
			
			echo json_encode($ary);
		} elseif ($table == 'health') {
			
			$ary['success'] = 'true';
			$ary['db'] = 'true';
			$ary['message'] = 'Both the service and database are running.';
			
			if (!Database::GetInstance()) {
				$ary['db'] = 'false';
				$ary['message'] = 'The service is running but the database is not.';
			}
			
			echo json_encode($ary);
		} else {
			echo json_encode(array('success' => 'false'));
		}
	}
?>