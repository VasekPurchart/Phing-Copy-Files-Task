<?php

declare(strict_types = 1);

namespace VasekPurchart\Phing\CopyFiles;

use Project;

class CopyFilesTask extends \Task
{

	/** @var \VasekPurchart\Phing\CopyFiles\CopyFileElement[] */
	private $copyFiles = [];

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
