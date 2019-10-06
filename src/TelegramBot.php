<?php

namespace aki\telegram;

use CURLFile;
use ReflectionClass;
use yii\base\Component;
use Yii;

/**
 * @method object getMe()
 * @method object sendMessage(array $params)
 * @method object forwardMessage(array $params)
 * @method object sendPhoto(array $params)
 * @method object sendAudio(array $params)
 * @method object sendDocument(array $params)
 * @method object sendSticker(array $params)
 * @method object sendVideo(array $params)
 * @method object sendLocation(array $params)
 * @method object sendChatAction(array $params)
 * @method object getUserProfilePhotos(array $params)
 * @method object getUpdates(array $params)
 * @method object setWebhook(array $params)
 * @method object getChat(array $params)
 * @method object getChatAdministrators(array $params)
 * @method object getChatMembersCount(array $params)
 * @method object getChatMember(array $params)
 * @method object answerCallbackQuery(array $params)
 * @method object editMessageText(array $params)
 * @method object editMessageCaption(array $params)
 * @method object sendGame(array $params)
 * @method object getGameHighScores(array $params)
 * @method object answerInlineQuery(array $params)
 * @method object kickChatMember(array $params)
 * @method object restrictChatMember(array $params)
 * @method object promoteChatMember(array $params)
 * @method object exportChatInviteLink(array $params)
 * @method object deleteChatPhoto(array $params)
 * @method object setChatTitle(array $params)
 * @method object setChatDescription(array $params)
 * @method object unpinChatMessage(array $params)
 * @method object pinChatMessage(array $params)
 * @method object leaveChat(array $params)
 * @method object setChatStickerSet(array $params)
 * @method object deleteChatStickerSet(array $params)
 * @method object getFile(array $params)
 * @method object sendMediaGroup(array $params)
 *
 * @method object Game(array $params)
 * @method object Animation(array $params)
 * @method object CallbackGame(array $params)
 * @method object GameHighScore(array $params)
 */
class TelegramBot extends Component {

	const ATTACHMENT_PHOTO = 'photo';

	const ATTACHMENT_STICKER = 'sticker';

	const ATTACHMENT_AUDIO = 'audio';

	const ATTACHMENT_DOCUMENT = 'document';

	const ATTACHMENT_VIDEO = 'video';

	const ATTACHMENT_MEDIA = 'media';

	public $botToken;

	public $botUsername;

	public $proxy;

	/**@var IOutput $output */
	public $output;

	/**@var string[] $attachments */
	protected $attachments;

	/**@var string[] $attachmentsGroup */
	protected static $attachmentsGroup = [
		self::ATTACHMENT_PHOTO,
		self::ATTACHMENT_VIDEO,
	];

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
		$this->output = Yii::createObject($this->output);
		$this->attachments = self::getConstantGroup("ATTACHMENT_", false);
	}

	public function hook() {
		$json = file_get_contents('php://input');

		return json_decode($json);
	}

	public function __call($name, $params) {
		if (empty($params)) {
			return $this->request($name, $params);
		}

		return $this->request($name, $params[0]);
	}

	protected function convertFile($attach) {
		if (is_array($attach) && array_key_exists('file_id', $attach)) {
			//Если передали file_id в массиве
			$attach = (string)$attach['file_id'];
		} elseif (file_exists($attach)) {
			//Если передали имя файла
			$attach = realpath($attach);

			if (!$attach) {
				$this->output->error("File RealPath failed");
			}

			//Создаем СurlFile из файла
			$attach = new CURLFile($attach);
			$attach->postname = uniqid("file_");
		} else {
			//Передали file_id
			$attach = (string)$attach;
		}

		return $attach;
	}

	protected function attachMedia(array &$params, array &$media): bool {
		$file = $this->convertFile($media['media']);

		if ($file instanceof CURLFile) {
			$params[$file->postname] = $file;
			$media['media'] = "attach://{$file->postname}";
		} else {
			$media['media'] = $file;
		}

		return true;
	}

	protected function attachFile(array &$params, string $attachment): bool {
		if (isset($params[$attachment])) {
			$params[$attachment] = $this->convertFile($params[$attachment]);

			return true;
		}

		return false;
	}

	protected function replacePostFieldsArray($params): array {
		foreach (array_keys($params) as $key) {
			if (is_array($params[$key])) {
				$params[$key] = json_encode($params[$key]);
			}
		}

		return $params;
	}

	protected function buildPostFields($data, $existingKeys = '', &$returnArray = []) {
		if (($data instanceof CURLFile) or !(is_array($data) or is_object($data))) {
			$returnArray[$existingKeys] = $data;

			return $returnArray;
		} else {
			foreach ($data as $key => $item) {
				self::buildPostFields($item, $existingKeys ? $existingKeys . "[$key]" : $key, $returnArray);
			}

			return $returnArray;
		}
	}

	protected function request($method, array $params) {
		$ch = curl_init("https://api.telegram.org/bot{$this->botToken}/{$method}");

		if (!$ch) {
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

		$isRawFields = false;

		foreach (array_keys($this->attachments) as $attachment) {
			if ($attachment === self::ATTACHMENT_MEDIA) {
				if (!array_key_exists($attachment, $params)) {
					continue;
				}

				$groupCount = count($params[$attachment]);

				if ($groupCount < 2 || $groupCount > 10) {
					$this->output->error('Group attachment allowed 2-10 items');
				}

				foreach (array_keys($params[$attachment]) as $index) {
					if (!array_key_exists('type', $params[$attachment][$index]) || !in_array($params[$attachment][$index]['type'], self::$attachmentsGroup)) {
						$this->output->error("Group must contains type 'photo' or 'video'");
					}

					$isRawFields |= $this->attachMedia($params, $params[$attachment][$index]);
				}
			} else {
				$isRawFields |= $this->attachFile($params, $attachment);
			}
		}

		if ($isRawFields) {
			curl_setopt($ch, CURLOPT_HTTPHEADER, [
				'multipart/form-data',
			]);

			curl_setopt($ch, CURLOPT_POSTFIELDS, self::replacePostFieldsArray($params));
		} else {
			curl_setopt($ch, CURLOPT_HTTPHEADER, [
				'Content-Type: application/json',
			]);
			curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
		}

		$response = curl_exec($ch);

		if ($response === false) {
			$this->output->error(curl_error($ch));
		}

		curl_close($ch);

		return json_decode($response);
	}
}