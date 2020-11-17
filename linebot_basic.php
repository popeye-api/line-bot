<?php

$channelAccessToken = 'Q5OMib+GGlGuElOssf6lDOxqS+sgOS77Xjt1EBKX+qGLCSlMIgINJVJBNroZEJZV4CcLQT9if8oSYrAfJhZUv5caGjuDg9uVMxdSXUv3nj/ywQNjkzaYiC+dwVAIMWkeGlElPYdRNB8n+B7YVN2+SAdB04t89/1O/w1cDnyilFU='; // Access Token ค่าที่เราสร้างขึ้น

$request = file_get_contents('php://input');   // Get request content

$request_json = json_decode($request, true);   // Decode JSON request

foreach ($request_json['events'] as $event)
{
	if ($event['type'] == 'message') 
	{
		if($event['message']['type'] == 'text')
		{
			$text = $event['message']['text'];
			$test = explode(" ", $text);
			$reply_message = 'ฉันได้รับข้อความ '. $text.' ของคุณแล้ว!';   
			$reply_message = $test[1]." ".'Popeye'." ".$test[2]; 
			//$reply_message = mySQL_selectAll('http://bot.kantit.com/json_select_users.php');
			
			if($test[1] == "ฉันต้องการค้นหาข้อมูลนิสิตทั้งหมด"){
				$reply_message = mySQL_selectAll('http://bot.kantit.com/json_select_users.php');
			}
			if($test[1] == "ฉันต้องการค้นหาข้อมูลนิสิตชื่อ"){
				$reply_message = mySQL_select('http://bot.kantit.com/json_select_users.php',$test[2]);
			}
			
			
		} else {
			$reply_message = 'ฉันได้รับ '.$event['message']['type'].' ของคุณแล้ว!';
		}
				
	} else {
		$reply_message = 'ฉันได้รับ Event '.$event['type'].' ของคุณแล้ว!';
	}
	
	// reply message
	$post_header = array('Content-Type: application/json', 'Authorization: Bearer ' . $channelAccessToken);
	$data = ['replyToken' => $event['replyToken'], 'messages' => [['type' => 'text', 'text' => $reply_message]]];
	$post_body = json_encode($data);
	$send_result = replyMessage('https://api.line.me/v2/bot/message/reply', $post_header, $post_body);
	//$send_result = send_reply_message('https://api.line.me/v2/bot/message/reply', $post_header, $post_body);
}

function replyMessage($url, $post_header, $post_body)
{
        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => $post_header,
                'content' => $post_body,
            ],
        ]);
	
	$result = file_get_contents($url, false, $context);

	return $result;
}

function send_reply_message($url, $post_header, $post_body)
{
	$ch = curl_init($url);	
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $post_header);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $post_body);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	
	$result = curl_exec($ch);
	
	curl_close($ch);
	
	return $result;
}

function mySQL_selectAll($url)
{
	$result = file_get_contents($url);
	
	$result_json = json_decode($result, true); //var_dump($result_json);
	
	$data = "ผลลัพธ์:\r\n";
		
	foreach($result_json as $values) {
		$data .= $values["user_stuid"] . " " . $values["user_firstname"] . " " . $values["user_lastname"] . "\r\n";
	}
	
	return $data;
}

function mySQL_select($url, $word)
{
	$result = file_get_contents($url);
	
	$result_json = json_decode($result, true); //var_dump($result_json);
	$data = $word.":\r\n";
	
	
		
	foreach($result_json as $values) {
		$pos = strpos($values["user_firstname"], "นาย");
		if($pos === true){
		$first = str_replace("นาย","",$values["user_firstname"]);
		}
		else{
		$first = str_replace("นางสาว","",$values["user_firstname"]);
		}
		
		
		if($word == $values["user_stuid"] || $word == $first || $word == $values["user_lastname"]){
		$data = "พบ:\r\n";	
		$data .= $values["user_stuid"] . " " . $values["user_firstname"] . " " . $values["user_lastname"] . "\r\n";
		}
		else{
		$data .= $first.":\r\n";
		$data .= "ไม่พบ:\r\n";
		}
		
	}	
	
	return $data;
}

?>
