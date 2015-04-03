<?php
/**
 * Класс для отправки СМС через smspro.nikita.kg
 * http://smspro.nikita.kg/documents/smspro.nikita.kg-XML-api.pdf
 * @author Величко Константин <zoxon.box@gmail.com>
 * @version 1.0
 * @package files
 */
class Vendors_NikitaSms
{
	/**
	 * Адресс API
	 * @var string
	 */
	private $url = "http://smspro.nikita.kg/api/message";


	/**
	 * Логин для доступа к платформе smspro.nikita.kg
	 * @var string
	 */
	private $login = "demo";


	/**
	 * Пароль для доступа к платформе smspro.nikita.kg
	 * @var string
	 */
	private $password = "demoPass";


	/**
	 * Имя отправителя.
	 * Может быть либо текстом на литинице либо цифрами или номером телефона
	 * (по согласованию с smspro.nikita.kg)
	 * @var string
	 */
	private $sender_id = "nikita.kg";


	/**
	 * Вкл./Выкл тестовый режим
	 * @var boolean
	 */
	public $test = false;


	/**
	 * Устанавливает параметры для подключения к API
	 * @param string $login     Имя пользователя
	 * @param string $password  Пароль
	 * @param string $sender_id Имя отправителя
	 */
	public function setLoginData($login, $password, $sender_id) {
		if (isset($login) && isset($password) && isset($sender_id)) {
			if (!empty($login) && !empty($password) && !empty($sender_id)) {
				$this->login     = $login;
				$this->password  = $password;
				$this->sender_id = $sender_id;
			}
		}

	}


	/**
	 * Отправка СМС
	 * @param  string $message Текст сообщения
	 * @param  array  $numbers Массив с номерами телефонов
	 * @return array  Ответ сервера
	 */
	public function send($message, $numbers) {
		$xml = $this->generateSendXml($message, $numbers);

		try {
			$result = $this->postContent( $this->url, $xml );
			// Ответ сервера smspro.nikita.kg
			return $result;
		}
		catch(Exception $e) {
			echo 'Caught exception: ',  $e->getMessage(), "\n";
			var_dump($e->getTrace());
		}
	}


	/**
	 * Генерация xml для отправки sms
	 * @param  string $message Текст сообщения
	 * @param  array  $numbers Массив с номерами телефонов
	 * @return sting           xml для запроса
	 */
	public function generateSendXml($text, $numbers) {

		$xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><message/>');

		$login      = $xml->addChild('login', $this->login);
		$password   = $xml->addChild('pwd', $this->password);
		$id         = $xml->addChild('id', substr(number_format(time() * rand(),0,'',''),0,12) );
		$senderId   = $xml->addChild('sender', $this->sender_id);
		$text       = $xml->addChild('text', $text);
		$phones     = $xml->addChild('phones');

		if ($this->test){
			$test   = $xml->addChild('test', 1);
		}

		if (is_array($numbers)) {
			$length = count($numbers);

			if ($length > 50) {
				throw new Exception("Максимальное число получателей в одном пакете – 50 номеров");
			}

			for ($i = 0; $i < $length; $i++) {
				if (!empty($numbers[$i])) {
					$phones->addChild('phone', $numbers[$i]);
				}
			}
		}
		else {
			$phones->addChild('phone', $numbers);
		}

		// Header('Content-type: text/xml');
		return $xml->asXML();
	}


	/**
	 * Отправка данных на API
	 * @param  string $url      Адресс API
	 * @param  sting  $postdata  XML для отправки
	 * @return array  Результаты запроса
	 */
	private function postContent($url, $postdata) {
		$useragent = "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 1.1.4322)";

		$curl = curl_init( $url );
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_HEADER, 0);
		// curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($curl, CURLOPT_ENCODING, "");
		curl_setopt($curl, CURLOPT_USERAGENT, $useragent);
		curl_setopt($curl, CURLOPT_TIMEOUT, 120);
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $postdata);
		// curl_setopt($curl, CURLOPT_COOKIEJAR, "c://coo.txt");
		// curl_setopt($curl, CURLOPT_COOKIEFILE,"c://coo.txt");

		$content = curl_exec( $curl );
		$err     = curl_errno( $curl );
		$errmsg  = curl_error( $curl );
		$header  = curl_getinfo( $curl );
		curl_close( $curl );

		$header['errno']   = $err;
		$header['errmsg']  = $errmsg;
		$header['content'] = $content;
		return $header;

	}


}
