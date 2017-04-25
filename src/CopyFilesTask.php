<?php

declare(strict_types = 1);

namespace VasekPurchart\Phing\CopyFiles;

use Project;

class CopyFilesTask extends \Task
{

	const EXISTS_MODE_FAIL = 'fail';
	const EXISTS_MODE_REPLACE = 'replace';
	const EXISTS_MODE_SKIP = 'skip';

	/** @var \VasekPurchart\Phing\CopyFiles\CopyFileElement[] */
	private $copyFiles = [];

	/** @var string|null */
	private $existsMode;

	/**
	 * @param string $mode
	 */
	public function setExistsMode(string $mode)
	{
		$this->existsMode = $mode;
	}

	public function main()
	{
		$this->validateAttributes();

		$errors = [];
		foreach ($this->copyFiles as $copyFile) {
			$sourcePath = $this->project->resolveFile($copyFile->getSource())->getAbsolutePath();
			if (!file_exists($sourcePath)) {
				$errors[] = sprintf('Source file "%s" does not exist.', $sourcePath);
				continue;
			}
			$targetPath = $this->project->resolveFile($copyFile->getTarget())->getAbsolutePath();
			if (file_exists($targetPath)) {
				$fileExistsMode = $this->getCopyFileFileExistsMode($copyFile);
				$message = sprintf('Target file %s already exists.', $targetPath);
				switch ($fileExistsMode) {
					case self::EXISTS_MODE_FAIL:
						$errors[] = $message;
						continue 2;
					case self::EXISTS_MODE_SKIP:
						$this->log($message . ' -> SKIPPING', Project::MSG_INFO);
						continue 2;
					case self::EXISTS_MODE_REPLACE:
						$this->log($message, Project::MSG_VERBOSE);
						break;
					default:
						// @codeCoverageIgnoreStart
						// should be unreachable
						throw new \Exception('Unexpected existsMode');
						// @codeCoverageIgnoreEnd
				}
			}

			$this->log(sprintf('Copying file: %s -> %s', $sourcePath, $targetPath), Project::MSG_INFO);
			if (!@copy($sourcePath, $targetPath)) {
				$errors[] = sprintf('Source file "%s" cannot be copied to target "%s".', $sourcePath, $targetPath);
			}
		}

		$this->handleErrors($errors);
	}

	public function createFile(): CopyFileElement
	{
		$copyFile = new CopyFileElement();
		$this->copyFiles[] = $copyFile;

		return $copyFile;
	}

	private function validateAttributes()
	{
		if (count($this->copyFiles) === 0) {
			throw new \BuildException('At least one <file> element expected');
		}
		foreach ($this->copyFiles as $copyFile) {
			$copyFile->validateAttributes();
		}
		if ($this->existsMode !== null) {
			static::checkAllowedExistsMode($this->existsMode);
		}
	}

	/**
	 * @param string $mode
	 */
	public static function checkAllowedExistsMode(string $mode)
	{
		$allowed = static::getAllowedExistsModes();
		if (!in_array($mode, $allowed, true)) {
			throw new \BuildException('Invalid CopyFiles mode "' . $mode . '" (choices: ' . implode('/', $allowed) . ')');
		}
	}

	/**
	 * @return string[]
	 */
	public static function getAllowedExistsModes(): array
	{
		return [
			static::EXISTS_MODE_FAIL,
			static::EXISTS_MODE_REPLACE,
			static::EXISTS_MODE_SKIP,
		];
	}

	private function getCopyFileFileExistsMode(CopyFileElement $copyFile): string
	{
		if ($copyFile->getExistsMode() !== null) {
			return $copyFile->getExistsMode();
		}
		if ($this->existsMode !== null) {
			return $this->existsMode;
		}

		return self::EXISTS_MODE_SKIP;
	}

	/**
	 * @param string[] $errors
	 */
	private function handleErrors(array $errors)
	{
		if (count($errors) > 0) {
			foreach ($errors as $error) {
				$this->log($error, Project::MSG_ERR);
			}
			throw new \ExitStatusException(1);
		}
	}

}
