<?php
header('Content-Type: application/json');

if (isset($_GET['key']) && isset($_GET['id'])) {
	$key = $_GET['key'];
	$id = $_GET['id'];

	/* If cloudflare is being used, then use the Cloudflare provided Client IP */
	if (isset($_SERVER["HTTP_CF_CONNECTING_IP"])) {
	  $_SERVER['REMOTE_ADDR'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
	}

	if (isset($_GET['timezone']))
		date_default_timezone_set($_GET['timezone']);
	else
		date_default_timezone_set("Europe/London"); // Default Timezone

	// Extract the From Number from URL, if it doesn't exist use default
	if (isset($_GET['from']))
		$from = $_GET['from'];
	else
		$from = "7367125"; // Default from number

	// Extract the overflow value from URL, if it doesn't exist use default
	if (isset($_GET['overflow']))
		$overflow = $_GET['overflow'];
	else
		$overflow = "multiple"; // Default message split type

	// Extract the Body message from URL, if it doesn't exist use default
	if (isset($_GET['body']) || isset($_GET['title'])){
		// Prefix the body message with Title value (if it's been specified)
		if (isset($_GET['title'])) {
			if(isset($_GET['body'])) {
				if (substr($_GET['title'], -6) == "{\$nl\$}")
					$body = "[" . date("H:i") . "] " . substr($_GET['title'], 0, -6) . ":{\$nl\$}" . $_GET['body'];
				else
					$body = "[" . date("H:i") . "] " . $_GET['title'] . ": " . $_GET['body'];
			} else {
				if (substr($_GET['title'], -6) == "{\$nl\$}")
					$body = "[" . date("H:i") . "] " . substr($_GET['title'], 0, -6) . ":{\$nl\$}";
				else
					$body = "[" . date("H:i") . "] " . $_GET['title'];
			}
		} else {
			$body = $_GET['body'];
		}
		// Evaluate body variables
			$find 	 = array( "{\$time\$}"	, "{\$time2\$}"	, "{\$pre\$}"				, "{\$ip\$}"			 , "{\$nl\$}" , "{\$nl2\$}" , "{\$nl3\$}", "`");
			$replace = array( date("H:i")	, date("H:i:s")	, "[" . date("H:i") . "]: "	, $_SERVER['REMOTE_ADDR'], "\\n"		  , "\\n\\n"		, "\n\n\n", "'");
			$body 	 = str_replace($find, $replace, $body);

		// Remove all special characters (that are not supported in Hologram's SMS)
			$body = preg_replace("/[^ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!# \"%&\\'^|()*,.?+-\/\[\]{};:<=>¡¿_@\$£¥\\\u00A4èéùìòÇØøÆæßÉÅåÄÖÑÜ§äöñüà\\\u0394\\\u03A6\\\u0393\\\u039B\\\u03A9\\\u03A0\\\u03A8\\\u03A3\\\u0398\\\u039E\n\r]/", "", $body);

		// If the message is more the 160 characters (Max SMS length)
		if (strlen($body) > 160) {
			// TRIM: Trim off the excess
			if ($overflow == "trim") {
				$body = substr($body,0,160);
			} else if ($overflow == "fail") {
				// FAIL: Don't send the message, show an error and exit
				$responce = array("success" => false, "error" => 2, "info" => "Message exceeded 160 characters!");
				echo json_encode($responce);
				exit();
			} else {
				// Create an Array each value containing 160 characters of the message
				$body_array = str_split($body, 160);
			}
		}
	} else {
		$body = "[" . date("H:i") . "] Hello world! (No message specified)"; // Default body message
	}

	// Skip if the sandbox parameter specified
	if (!isset($_GET['sandbox'])) {
		// If the body is an array (ie. It needs to be sent in multiple messages)
		if ($body_array) {
			foreach($body_array as $sms) {
				$data = array("deviceid" => $id, "fromnumber" => $from, "body" => $sms);
				$data_string = json_encode($data);
				$data_string = str_replace("\\n", 'n', $data_string);
				$ch = curl_init('https://dashboard.hologram.io/api/1/sms/incoming?apikey=' . $key);
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");   
				curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);    
				curl_setopt($ch, CURLOPT_HTTPHEADER, array(        
				    'Content-Type: application/json',              
				    'Content-Length: ' . strlen($data_string))     
				);                                                 
				$result = curl_exec($ch);
			}
			$times = count($body_array);
		// Else send a single SMS
		} else {
			$data = array("deviceid" => $id, "fromnumber" => $from, "body" => $body);
			$data_string = json_encode($data);
			$data_string = str_replace("\\n", 'n', $data_string);
			$ch = curl_init('https://dashboard.hologram.io/api/1/sms/incoming?apikey=' . $key);
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");   
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);    
			curl_setopt($ch, CURLOPT_HTTPHEADER, array(        
			    'Content-Type: application/json',              
			    'Content-Length: ' . strlen($data_string))     
			);                                                 
			$result = curl_exec($ch);
			$times = 1;
		}
		// If the footer parameter was specified then send another SMS with it's value
		if (isset($_GET['footer'])) {
			// Remove all special characters (that are not supported in Hologram's SMS)
			$footer = preg_replace("[^ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!# \"%&\\'^|()*,.?+-\/;:<=>¡¿_@$£¥\\u00A4èéùìòÇØøÆæßÉÅåÄÖÑÜ§äöñüà\u0394\u03A6 \u0393\u039B\u03A9\u03A0\u03A8\u03A3\u0398\u039E\\n\\r]", "", $_GET['footer']);
			$data = array("deviceid" => $id, "fromnumber" => $from, "body" => $footer);
			$data_string = json_encode($data);
			$data_string = str_replace("\\n", 'n', $data_string);
			$ch = curl_init('https://dashboard.hologram.io/api/1/sms/incoming?apikey=' . $key);
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");   
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);    
			curl_setopt($ch, CURLOPT_HTTPHEADER, array(        
			    'Content-Type: application/json',              
			    'Content-Length: ' . strlen($data_string))     
			);                                                 
			$result2 = curl_exec($ch);
		}
	// Show example output if ?sandbox parameter specified
	} else {
		if ($body_array)
			$times = count($body_array);
		else
			$times = 1;
		$responce = array("success" => true, "error" => 0, "info" => "Message delivered! [" . $times . " sent]", "message" => $body, "testing" => true);
		$example = json_encode($responce);
		$example = str_replace("\\\\n", '\\n', $example);
		echo $example;
		exit();
	}

	// If the CURL output is empty
	if (!$result) {
		$responce = array("success" => false, "error" => 3, "info" => "Failed to connect to the Hologram API!");
		echo json_encode($responce);
		exit();
	}

	// Decode the CURL output to an array
	$result_array = json_decode($result, true);
	if ($result_array[0]["success"] == true) {
		$responce = array("success" => true, "error" => 0, "info" => "Message delivered! [" . $times . " sent]", "message" => $body);
	} else {
		$responce = array("success" => false, "error" => 4, "info" => $result_array["error"], "message" => $body);
	}
	echo str_replace("\\n", 'n', json_encode($responce));

// Callback if API key or ID not specified
} else {
	$responce = array("success" => false, "error" => 1, "info" => "No API key or device ID specified!");
	echo json_encode($responce);
}
?>