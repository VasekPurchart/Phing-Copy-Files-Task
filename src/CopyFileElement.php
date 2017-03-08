<?php

declare(strict_types = 1);

namespace VasekPurchart\Phing\CopyFiles;

class CopyFileElement
{

	/** @var string|null */
	private $source;

	/** @var string|null */
	private $target;

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

	public function validateAttributes()
	{
		if ($this->getSource() === null) {
			throw new \BuildException('<file> must have the `source` attribute');
		}
		if ($this->getTarget() === null) {
			throw new \BuildException('<file> must have the `target` attribute');
		}
	}

}
