<?php

namespace aki\telegram;

use Exception;
use yii\base\Component;

class FileOutput extends Component implements IOutput {

	public $fileName;

	public $dateFormat = 'Y-m-d H:i:s';

	protected function write(string $text) {
		$hFile = fopen($this->fileName, "a");

		if (!$hFile) {
			throw new Exception('Unable to open log file');
		}

		fwrite($hFile, $text);
		fclose($hFile);
	}

	protected function dateFormat(): string {
		if (empty($this->dateFormat)) {
			return '';
		}

		return date($this->dateFormat);
	}

	public function info(string $text): void {
		$this->write("{$this->dateFormat()} INFO:\t{$text}\n");
	}

	public function error(string $text): void {
		$this->write("{$this->dateFormat()} ERROR:\t{$text}\n");

		exit(0);
	}

	public function warning(string $text): void {
		$this->write("{$this->dateFormat()} WARNING:\t{$text}\n");
	}
}