<?php

declare(strict_types = 1);

namespace VasekPurchart\Phing\CopyFiles;

class CopyFileElement
{

	/** @var string|null */
	private $source;

	/** @var string|null */
	private $target;

	/** @var string|null */
	private $existsMode;

	public function getSource(): ?string
	{
		return $this->source;
	}

	public function setSource(string $source): void
	{
		$this->source = $source;
	}

	public function getTarget(): ?string
	{
		return $this->target;
	}

	public function setTarget(string $target): void
	{
		$this->target = $target;
	}

	public function getExistsMode(): ?string
	{
		return $this->existsMode;
	}

	public function setExistsMode(string $mode): void
	{
		$this->existsMode = $mode;
	}

	public function validateAttributes(): void
	{
		if ($this->getSource() === null) {
			throw new \BuildException('<file> must have the `source` attribute');
		}
		if ($this->getTarget() === null) {
			throw new \BuildException('<file> must have the `target` attribute');
		}
		if ($this->getExistsMode() !== null) {
			CopyFilesTask::checkAllowedExistsMode($this->getExistsMode());
		}
	}

}
