<?php

namespace aki\telegram;

interface IOutput {

	public function info(string $text): void;

	public function error(string $text): void;

	public function warning(string $text): void;
}