<?php


class AitSendEmailAjax extends AitFrontendAjax
{

	/**
	 * @WpAjax
	 */
	public function send()
	{
		$captcha = new ReallySimpleCaptcha();
		$captcha->tmp_dir = aitPaths()->dir->cache . '/captcha';

		$matches = array();
		preg_match_all('/{([^}]*)}/', $_POST['response-email-content'], $matches);

		foreach($matches[1] as $i => $match){
			$_POST['response-email-content'] = str_replace($matches[0][$i], $_POST[$match], $_POST['response-email-content']);
		}
		
		$_POST['response-email-content'] = str_ireplace(array("\r\n", "\n"), "<br />", $_POST['response-email-content']);

		// unescape all escaped quotes .. not safe .. probably remove
		//$_POST['response-email-content'] = str_ireplace(array("\'", '\"'), array("'", '"'), $_POST['response-email-content']);

		$headers = array(
			'Content-Type: text/html; charset=UTF-8',
		);
		if(isset($_POST['email'])){
			array_push($headers, 'Reply-To: '.$_POST['email'].' <'.$_POST['email'].'>');
		}

		if(!empty($_POST['response-email-sender'])){
			array_push($headers, 'From: '.'<'.$_POST['response-email-sender'].'>');
		}

		if(!empty($_POST['captcha-check'])){
			if($captcha->check('ait-captcha-'.$_POST['response-email-check'], $_POST['captcha-check'])){
				wp_mail($_POST['response-email-address'], $_POST['response-email-subject'], $_POST['response-email-content'], $headers);
				$this->sendJson(array('message' => sprintf(__("Mail sent to %s", 'ait'), $_POST['response-email-address'])));
			}else{
				$this->sendErrorJson(array('message' => __("Captcha check failed", 'ait')));
			}
		} else {
			wp_mail($_POST['response-email-address'], $_POST['response-email-subject'], $_POST['response-email-content'], $headers);
			$this->sendJson(array('message' => sprintf(__("Mail sent to %s", 'ait'), $_POST['response-email-address'])));
		}
	}
}
