<?php 
/*
	***********************************************
	TAGBOND Software Development Kit 


	***********************************************

*/
session_start();
/*
	Set your Credentials 
*/
define('CLIENT_ID', 'YOUR_CLIENT_ID'); // your client id 
define('CLIENT_SECRET', 'YOUR_CLIENT_SECRET'); // your client secret
define('EMAIL', 'EMAIL_OR_ID'); // your Tagbond email / id
define('PASSWORD', 'PASSWORD'); // your Tagbond Password
define('COMMUNITY_ID', 0); // define your Communty ID on Tagbond ( 0 for example )
/*
	END
*/
class TagbondSDK {
	/*
		Get Profile
	*/
	public static function Profile(){

		$curl = curl_init();
		curl_setopt_array($curl, array(
		    CURLOPT_RETURNTRANSFER => 1,
		    CURLOPT_URL => 'https://api.tagbond.com/user/profile?access_token='.$_SESSION['access_token'],
		));

		$response = curl_exec($curl);
		$data = json_decode($response);
		if($data->status == 'success'){
			return $data->result->user_firstname." ".$data->result->user_lastname." - ".$data->result->id;
		}
		return 'not found';
	}
	/*
		Login the user.
	*/
	public static function Login(){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,"https://api.tagbond.com/oauth/accesstoken");
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS,array(
												'client_id'=>CLIENT_ID,// your client id 
												'client_secret'=>CLIENT_SECRET, // your client secret
												// 'grant_type'=>'password', // authentication
												'grant_type'=>'client_credentials', // authentication
												'user_email'=>EMAIL,
												'user_password'=>PASSWORD));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$response = curl_exec ($ch);
		$data = json_decode($response);
		if($data->result->access_token){
			unset($_SESSION['access_token']);
			$_SESSION['access_token'] = $data->result->access_token;
			return true;
		}else{
			return false;
		}

	}
	/*
		Register a user to TAGBOND
	*/
	public static function Register($email,$firstname,$lastname,$password){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,"http://api.tagbond.com/registration");
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS,array(
												'client_id'=>CLIENT_ID,// your client id 
												'client_secret'=>CLIENT_SECRET, // your client secret
												'grant_type'=>'authorization_code',
												'user_email'=>$email,
												'user_firstname'=>$firstname,
												'user_lastname'=>$lastname,
												'user_password'=>$password));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$response = curl_exec ($ch);
		$data = json_decode($response);

		if($data->result->access_token){
			$_SESSION['access_token'] = $data->result->access_token;
			if(!self::Join($data->result->access_token)){
				echo 'Error on joining to community.'; 
			}else{
				echo 'Successfully joined to Tagbond.';
			}
		}else{
			echo $data->error; 
		}
	}
	/*
		This function will automatically join to the user after 
		signing up

	*/
	public static function Join($accesstoken){

		$curl = curl_init();
		curl_setopt_array($curl, array(
		    CURLOPT_RETURNTRANSFER => 1,
		    CURLOPT_URL => 'http://api.tagbond.com/community/join/'.COMMUNITY_ID.'?access_token='.$accesstoken,
		));

		$response = curl_exec($curl);
		$data = json_decode($response);
		if($data->status == 'success'){
			return true;
		}
		return false;
	}  
	/*
		Give Reward to TAGBOND
	*/
	public static function Reward($action_id,$id){
		if(!isset($_SESSION['access_token'])){
			return 'Error Occured';
		}
		$accesstoken = $_SESSION['access_token'];
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,"https://api.tagbond.com/community/offsiteReward/".COMMUNITY_ID);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS,array(
												'access_token' => $accesstoken,// your access token 
												'action_id' => $action_id,
												'userId' => $id,
												));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$response = curl_exec ($ch);
		$data = json_decode($response);
		if($data->status == 'success'){
			return 'You have been rewarded';
		}
		return 'Reward Taken / Invalid Request';
	}

}
	/*
		$_POST['btnTagbond-signup'] -> when sign up
	*/
	$Tagbond = new TagbondSDK();

	if(isset($_POST)){
		extract($_POST); //break down all data

		/* login to tagbond */
		if(isset($Tagbond_SignIn)){
			echo $Tagbond->Login();
		}

		/* sign up */
		if(isset($Tagbond_SignUp)){
			$Tagbond->Register($email,$firstname,$lastname,$password);
		}

		/* transfer reward */
		if(isset($tagbondTransfer)){
			echo $Tagbond->Reward($action_id,$id);
		}

		/* get profile */
		if(isset($getprofile)){
			echo $Tagbond->Profile();
		}
	}


?>