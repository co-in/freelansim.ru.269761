<?php

namespace aki\telegram;

use Exception;
use yii\base\Component;

class FileOutput extends Component implements IOutput {

	public $fileName;

	protected function write(string $text) {
		$hFile = fopen($this->fileName, "w");

		if (!$hFile) {
			throw new Exception('Unable to open log file');
		}

		fwrite($hFile, $text);
		fclose($hFile);
	}

	public function info(string $text): void {
		$this->write("INFO:\t{$text}\n");
	}

	public function error(string $text): void {
		$this->write("ERROR:\t{$text}\n");

		exit(0);
	}

	public function warning(string $text): void {
		$this->write("WARNING:\t{$text}\n");
	}
}