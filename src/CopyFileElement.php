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

	/**
	 * @return string|null
	 */
	public function getSource()
	{
		return $this->source;
	}

	public function setSource(string $source)
	{
		$this->source = $source;
	}

	/**
	 * @return string|null
	 */
	public function getTarget()
	{
		return $this->target;
	}

	public function setTarget(string $target)
	{
		$this->target = $target;
	}

	/**
	 * @return string|null
	 */
	public function getExistsMode()
	{
		return $this->existsMode;
	}

	/**
	 * @param string $mode
	 */
	public function setExistsMode(string $mode)
	{
		$this->existsMode = $mode;
	}

	public function validateAttributes()
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
