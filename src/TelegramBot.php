<?php

namespace aki\telegram;

use CURLFile;
use ReflectionClass;
use yii\base\Component;

/**
 * @method getMe(): string without json_decode
 */
class TelegramBot extends Component {

	const ATTACHMENT_PHOTO = 'photo';

	const ATTACHMENT_STICKER = 'sticker';

	const ATTACHMENT_AUDIO = 'audio';

	const ATTACHMENT_DOCUMENT = 'document';

	const ATTACHMENT_VIDEO = 'video';

	public $botToken;

	public $botUsername;

	public $proxy;

	/**@var IOutput $output */
	public $output;

	/**@var string[] $attachments */
	protected $attachments;

	/**@var string[] $attachments */
	protected $methods;

	protected $botName = "PostManGoBot 1.0";

	protected static function getConstantGroup(string $needle, bool $upperCase): array {
		$class = new ReflectionClass(self::class);
		$constants = [];
		$needleLen = strlen($needle);

		foreach ($class->getConstants() as $constant => $value) {
			if (strpos($constant, $needle) === 0) {
				$text = substr($constant, $needleLen);

				if (!$upperCase) {
					$text = ucwords(strtolower(str_replace('_', ' ', $text)));
				} else {
					$text = strtoupper($text);
				}

				$constants[$value] = $text;;
			}
		}

		return $constants;
	}

	public function init() {
		parent::init();

		//TODO Сделать разные выводы и имя файла
		$this->output = new FileOutput();

		$this->attachments = self::getConstantGroup("ATTACHMENT_", false);
	}

	public function sendMessage(array $params) {

	}

	public function forwardMessage(array $params) {

	}

	public function sendPhoto(array $option) {

	}

	public function sendAudio(array $option) {

	}

	public function sendDocument(array $option) {

	}

	public function sendSticker(array $option) {

	}

	public function sendVideo(array $option) {

	}

	public function sendLocation(array $option) {

	}

	public function sendChatAction(array $option) {

	}

	public function getUserProfilePhotos($option) {

	}

	public function getUpdates(array $option = []) {

	}

	public function setWebhook(array $option = []) {

	}

	public function getChat(array $option = []) {

	}

	public function getChatAdministrators(array $option = []) {

	}

	public function getChatMembersCount(array $option = []) {

	}

	public function getChatMember(array $option = []) {

	}

	public function answerCallbackQuery(array $option = []) {

	}

	public function editMessageText(array $option = []) {

	}

	public function editMessageCaption(array $option = []) {

	}

	public function sendGame(array $option = []) {

	}

	public function Game(array $option = []) {

	}

	public function Animation(array $option = []) {

	}

	public function CallbackGame(array $option = []) {

	}

	public function getGameHighScores(array $option = []) {

	}

	public function GameHighScore(array $option = []) {

	}

	public function answerInlineQuery(array $option = []) {

	}

	public function kickChatMember(array $option = []) {

	}

	public function restrictChatMember(array $option = []) {

	}

	public function promoteChatMember(array $option = []) {

	}

	public function exportChatInviteLink(array $option = []) {

	}

	public function deleteChatPhoto(array $option = []) {

	}

	public function setChatTitle(array $option = []) {

	}

	public function setChatDescription(array $option = []) {

	}

	public function unpinChatMessage(array $option = []) {

	}

	public function pinChatMessage(array $option = []) {

	}

	public function leaveChat(array $option = []) {

	}

	public function setChatStickerSet(array $option = []) {

	}

	public function deleteChatStickerSet(array $option = []) {

	}

	public function __call($name, $params) {
		return $this->request($name, ...$params);
	}

	protected function request($method, array $params): string {
		$ch = curl_init("https://api.telegram.org/bot{$this->botToken}/{$method}");

		if ($ch) {
			$this->output->error('curl init');
		}

		curl_setopt($ch, CURLOPT_USERAGENT, $this->botName);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POST, true);

		//TODO небезопасно, возможна MITM атака
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

		if ($this->proxy !== null) {
			//TODO может быть не только SOCKS5 прокси
			curl_setopt($ch, CURLOPT_PROXY, "socks5://{$this->proxy}");
		}

		foreach ($this->attachments as $attachment) {
			if (isset($params[$attachment])) {
				$attach = $params[$attachment];

				if (is_array($attach) && array_key_exists('file_id', $attach)) {
					//Если передали file_id в массиве
					$attach = (string)$attach['file_id'];
				} elseif (file_exists($attach)) {
					//Если передали имя файла
					$attach = realpath($attach);

					if (!$attach) {
						$this->output->error("File RealPath failed");
					}

					//Проверяем доступность класса CURLFile
					if (class_exists('CURLFile')) {
						$attach = new CURLFile($attach);
					} else {
						$attach = "@{$attach}";
					}
				} else {
					//Передали file_id
					$attach = (string)$attach;
				}

				$params[$attachment] = $attach;

				break;
			}
		}

		curl_setopt($ch, CURLOPT_POSTFIELDS, $params);

		$response = curl_exec($ch);

		if ($response === false) {
			$this->output->error(curl_error($ch));
		}

		curl_close($ch);

		return $response;
	}
}