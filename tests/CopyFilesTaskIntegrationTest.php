<?php

declare(strict_types = 1);

namespace VasekPurchart\Phing\CopyFiles;

use Project;

use VasekPurchart\Phing\PhingTester\PhingTester;

class CopyFilesTaskIntegrationTest extends \PHPUnit\Framework\TestCase
{

	const TEMP_DIRECTORY_PATH = __DIR__ . '/temp';

	public function testCopyFile()
	{
		$sourceFilePath = self::TEMP_DIRECTORY_PATH . '/foo';
		file_put_contents($sourceFilePath, 'FOO');
		$targetFilePath = self::TEMP_DIRECTORY_PATH . '/foo-copy';
		if (file_exists($targetFilePath)) {
			unlink($targetFilePath);
		}

		$tester = new PhingTester(__DIR__ . '/copy-files-task-integration-test.xml', self::TEMP_DIRECTORY_PATH);
		$target = __FUNCTION__;
		$tester->executeTarget($target);

		$this->assertFileEquals($sourceFilePath, $targetFilePath);
		$tester->assertLogMessageRegExp('~Copying.+/foo.+->.+/foo-copy~', $target, Project::MSG_INFO);
	}

	public function testCopyFileWithAbsolutePath()
	{
		$sourceFilePath = self::TEMP_DIRECTORY_PATH . '/foo';
		file_put_contents($sourceFilePath, 'FOO');
		$targetFilePath = self::TEMP_DIRECTORY_PATH . '/foo-copy';
		if (file_exists($targetFilePath)) {
			unlink($targetFilePath);
		}

		$tester = new PhingTester(__DIR__ . '/copy-files-task-integration-test.xml', self::TEMP_DIRECTORY_PATH);
		$target = __FUNCTION__;
		$tester->executeTarget($target);

		$this->assertFileEquals($sourceFilePath, $targetFilePath);
		$tester->assertLogMessageRegExp('~Copying.+/foo.+->.+/foo-copy~', $target, Project::MSG_INFO);
	}

	public function testCopyMultipleFiles()
	{
		$sourceFooFilePath = self::TEMP_DIRECTORY_PATH . '/foo';
		file_put_contents($sourceFooFilePath, 'FOO');
		$targetFooFilePath = self::TEMP_DIRECTORY_PATH . '/foo-copy';
		if (file_exists($targetFooFilePath)) {
			unlink($targetFooFilePath);
		}
		$sourceBarFilePath = self::TEMP_DIRECTORY_PATH . '/bar';
		file_put_contents($sourceBarFilePath, 'BAR');
		$targetBarFilePath = self::TEMP_DIRECTORY_PATH . '/bar-copy';
		if (file_exists($targetBarFilePath)) {
			unlink($targetBarFilePath);
		}

		$tester = new PhingTester(__DIR__ . '/copy-files-task-integration-test.xml', self::TEMP_DIRECTORY_PATH);
		$target = __FUNCTION__;
		$tester->executeTarget($target);

		$this->assertFileEquals($sourceFooFilePath, $targetFooFilePath);
		$tester->assertLogMessageRegExp('~Copying.+/foo.+->.+/foo-copy~', $target, Project::MSG_INFO);
		$this->assertFileEquals($sourceBarFilePath, $targetBarFilePath);
		$tester->assertLogMessageRegExp('~Copying.+/bar.+->.+/bar-copy~', $target, Project::MSG_INFO);
	}

	public function testCopyNonExistentFile()
	{
		$tester = new PhingTester(__DIR__ . '/copy-files-task-integration-test.xml');
		$target = __FUNCTION__;

		$tester->expectFailedBuild($target);

		$tester->assertLogMessageRegExp('~XXX.+does not exist~', $target, Project::MSG_ERR);
	}

	public function testCopyMultipleNonExistentFiles()
	{
		$tester = new PhingTester(__DIR__ . '/copy-files-task-integration-test.xml');
		$target = __FUNCTION__;

		$tester->expectFailedBuild($target);

		$tester->assertLogMessageRegExp('~FOO.+does not exist~', $target, Project::MSG_ERR);
		$tester->assertLogMessageRegExp('~BAR.+does not exist~', $target, Project::MSG_ERR);
	}

	public function testCopyFileToNonExistingDirectory()
	{
		$sourceFilePath = self::TEMP_DIRECTORY_PATH . '/foo';
		file_put_contents($sourceFilePath, 'FOO');
		$targetDirectoryPath = self::TEMP_DIRECTORY_PATH . '/non-existing-directory';
		if (file_exists($targetDirectoryPath)) {
			rmdir($targetDirectoryPath);
		}

		$tester = new PhingTester(__DIR__ . '/copy-files-task-integration-test.xml', self::TEMP_DIRECTORY_PATH);
		$target = __FUNCTION__;

		$tester->expectFailedBuild($target);

		$tester->assertLogMessageRegExp('~/foo.+cannot be copied.+/foo-copy~', $target, Project::MSG_ERR);
	}

	public function testMissingCopyFileElement()
	{
		$tester = new PhingTester(__DIR__ . '/copy-files-task-integration-test.xml');
		$target = __FUNCTION__;

		$this->expectException(\BuildException::class);
		$this->expectExceptionMessageRegExp('~one.+<file>.+expected~');

		$tester->executeTarget($target);
	}

	public function testMissingSource()
	{
		$tester = new PhingTester(__DIR__ . '/copy-files-task-integration-test.xml');
		$target = __FUNCTION__;

		$this->expectException(\BuildException::class);
		$this->expectExceptionMessageRegExp('~<file>.+`source`~');

		$tester->executeTarget($target);
	}

	public function testMissingTarget()
	{
		$tester = new PhingTester(__DIR__ . '/copy-files-task-integration-test.xml');
		$target = __FUNCTION__;

		$this->expectException(\BuildException::class);
		$this->expectExceptionMessageRegExp('~<file>.+`target`~');

		$tester->executeTarget($target);
	}

}
