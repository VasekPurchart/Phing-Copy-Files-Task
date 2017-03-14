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

	public function testTargetFileExists()
	{
		$sourceFilePath = self::TEMP_DIRECTORY_PATH . '/new';
		file_put_contents($sourceFilePath, 'NEW');
		$targetFilePath = self::TEMP_DIRECTORY_PATH . '/existing';
		file_put_contents($targetFilePath, 'EXISTING');

		$tester = new PhingTester(__DIR__ . '/copy-files-task-integration-test.xml', self::TEMP_DIRECTORY_PATH);
		$target = __FUNCTION__;
		$tester->executeTarget($target);

		$this->assertFileExists($targetFilePath);
		$this->assertSame('EXISTING', file_get_contents($targetFilePath));
		$tester->assertLogMessageRegExp('~/existing.+already exists.+SKIPPING~', $target, Project::MSG_INFO);
	}

	public function testTargetFileExistsSkip()
	{
		$sourceFilePath = self::TEMP_DIRECTORY_PATH . '/new';
		file_put_contents($sourceFilePath, 'NEW');
		$targetFilePath = self::TEMP_DIRECTORY_PATH . '/existing';
		file_put_contents($targetFilePath, 'EXISTING');

		$tester = new PhingTester(__DIR__ . '/copy-files-task-integration-test.xml', self::TEMP_DIRECTORY_PATH);
		$target = __FUNCTION__;
		$tester->executeTarget($target);

		$this->assertFileExists($targetFilePath);
		$this->assertSame('EXISTING', file_get_contents($targetFilePath));
		$tester->assertLogMessageRegExp('~/existing.+already exists.+SKIPPING~', $target, Project::MSG_INFO);
	}

	public function testTargetFileExistsReplace()
	{
		$sourceFilePath = self::TEMP_DIRECTORY_PATH . '/new';
		file_put_contents($sourceFilePath, 'NEW');
		$targetFilePath = self::TEMP_DIRECTORY_PATH . '/existing';
		file_put_contents($targetFilePath, 'EXISTING');

		$tester = new PhingTester(__DIR__ . '/copy-files-task-integration-test.xml', self::TEMP_DIRECTORY_PATH);
		$target = __FUNCTION__;
		$tester->executeTarget($target);

		$this->assertFileEquals($sourceFilePath, $targetFilePath);
		$tester->assertLogMessageRegExp('~/existing.+already exists~', $target, Project::MSG_VERBOSE);
	}

	public function testTargetFileExistsFail()
	{
		$sourceFilePath = self::TEMP_DIRECTORY_PATH . '/new';
		file_put_contents($sourceFilePath, 'NEW');
		$targetFilePath = self::TEMP_DIRECTORY_PATH . '/existing';
		file_put_contents($targetFilePath, 'EXISTING');

		$tester = new PhingTester(__DIR__ . '/copy-files-task-integration-test.xml', self::TEMP_DIRECTORY_PATH);
		$target = __FUNCTION__;

		$tester->expectFailedBuild($target);

		$this->assertFileExists($targetFilePath);
		$this->assertSame('EXISTING', file_get_contents($targetFilePath));
		$tester->assertLogMessageRegExp('~/existing.+already exists~', $target, Project::MSG_ERR);
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

	public function testReplaceMultipleFilesWithExistingTargets()
	{
		$sourceFooFilePath = self::TEMP_DIRECTORY_PATH . '/foo-new';
		file_put_contents($sourceFooFilePath, 'FOO-NEW');
		$targetFooFilePath = self::TEMP_DIRECTORY_PATH . '/foo-existing';
		file_put_contents($targetFooFilePath, 'FOO-EXISTING');

		$sourceBarFilePath = self::TEMP_DIRECTORY_PATH . '/bar-new';
		file_put_contents($sourceBarFilePath, 'BAR-NEW');
		$targetBarFilePath = self::TEMP_DIRECTORY_PATH . '/bar-existing';
		file_put_contents($targetBarFilePath, 'BAR-EXISTING');

		$tester = new PhingTester(__DIR__ . '/copy-files-task-integration-test.xml', self::TEMP_DIRECTORY_PATH);
		$target = __FUNCTION__;
		$tester->executeTarget($target);

		$this->assertFileEquals($sourceFooFilePath, $targetFooFilePath);
		$tester->assertLogMessageRegExp('~Copying.+/foo-new.+->.+/foo-existing~', $target, Project::MSG_INFO);
		$tester->assertLogMessageRegExp('~/foo-existing.+already exists~', $target, Project::MSG_VERBOSE);

		$this->assertFileEquals($sourceBarFilePath, $targetBarFilePath);
		$tester->assertLogMessageRegExp('~Copying.+/bar-new.+->.+/bar-existing~', $target, Project::MSG_INFO);
		$tester->assertLogMessageRegExp('~/bar-existing.+already exists~', $target, Project::MSG_VERBOSE);
	}

	public function testCopyMultipleFilesWithExistingTargetsUsingDifferentModes()
	{
		$sourceSkipFilePath = self::TEMP_DIRECTORY_PATH . '/skip-new';
		file_put_contents($sourceSkipFilePath, 'SKIP-NEW');
		$targetSkipFilePath = self::TEMP_DIRECTORY_PATH . '/skip-existing';
		file_put_contents($targetSkipFilePath, 'SKIP-EXISTING');

		$sourceReplaceFilePath = self::TEMP_DIRECTORY_PATH . '/replace-new';
		file_put_contents($sourceReplaceFilePath, 'REPLACE-NEW');
		$targetReplaceFilePath = self::TEMP_DIRECTORY_PATH . '/replace-existing';
		file_put_contents($targetReplaceFilePath, 'REPLACE-EXISTING');

		$sourceDefaultFilePath = self::TEMP_DIRECTORY_PATH . '/default-new';
		file_put_contents($sourceDefaultFilePath, 'DEFAULT-NEW');
		$targetDefaultFilePath = self::TEMP_DIRECTORY_PATH . '/default-existing';
		file_put_contents($targetDefaultFilePath, 'DEFAULT-EXISTING');

		$tester = new PhingTester(__DIR__ . '/copy-files-task-integration-test.xml', self::TEMP_DIRECTORY_PATH);
		$target = __FUNCTION__;
		$tester->executeTarget($target);

		$this->assertFileExists($targetSkipFilePath);
		$this->assertSame('SKIP-EXISTING', file_get_contents($targetSkipFilePath));

		$this->assertFileEquals($sourceReplaceFilePath, $targetReplaceFilePath);
		$tester->assertLogMessageRegExp('~Copying.+/replace-new.+->.+/replace-existing~', $target, Project::MSG_INFO);
		$tester->assertLogMessageRegExp('~/replace-existing.+already exists~', $target, Project::MSG_VERBOSE);

		$this->assertFileExists($targetDefaultFilePath);
		$this->assertSame('DEFAULT-EXISTING', file_get_contents($targetDefaultFilePath));
	}

	public function testCopyMultipleFilesWithExistingTargetsUsingDifferentModesWithReplaceFallback()
	{
		$sourceSkipFilePath = self::TEMP_DIRECTORY_PATH . '/skip-new';
		file_put_contents($sourceSkipFilePath, 'SKIP-NEW');
		$targetSkipFilePath = self::TEMP_DIRECTORY_PATH . '/skip-existing';
		file_put_contents($targetSkipFilePath, 'SKIP-EXISTING');

		$sourceReplaceFilePath = self::TEMP_DIRECTORY_PATH . '/replace-new';
		file_put_contents($sourceReplaceFilePath, 'REPLACE-NEW');
		$targetReplaceFilePath = self::TEMP_DIRECTORY_PATH . '/replace-existing';
		file_put_contents($targetReplaceFilePath, 'REPLACE-EXISTING');

		$sourceDefaultFilePath = self::TEMP_DIRECTORY_PATH . '/default-new';
		file_put_contents($sourceDefaultFilePath, 'DEFAULT-NEW');
		$targetDefaultFilePath = self::TEMP_DIRECTORY_PATH . '/default-existing';
		file_put_contents($targetDefaultFilePath, 'DEFAULT-EXISTING');

		$tester = new PhingTester(__DIR__ . '/copy-files-task-integration-test.xml', self::TEMP_DIRECTORY_PATH);
		$target = __FUNCTION__;
		$tester->executeTarget($target);

		$this->assertFileExists($targetSkipFilePath);
		$this->assertSame('SKIP-EXISTING', file_get_contents($targetSkipFilePath));

		$this->assertFileEquals($sourceReplaceFilePath, $targetReplaceFilePath);
		$tester->assertLogMessageRegExp('~Copying.+/replace-new.+->.+/replace-existing~', $target, Project::MSG_INFO);
		$tester->assertLogMessageRegExp('~/replace-existing.+already exists~', $target, Project::MSG_VERBOSE);

		$this->assertFileEquals($sourceDefaultFilePath, $targetDefaultFilePath);
		$tester->assertLogMessageRegExp('~Copying.+/default-new.+->.+/default-existing~', $target, Project::MSG_INFO);
		$tester->assertLogMessageRegExp('~/default-existing.+already exists~', $target, Project::MSG_VERBOSE);
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

	public function testInvalidFileExistsMode()
	{
		$tester = new PhingTester(__DIR__ . '/copy-files-task-integration-test.xml');
		$target = __FUNCTION__;

		$this->expectException(\BuildException::class);
		$this->expectExceptionMessageRegExp('~invalid.+mode~i');

		$tester->executeTarget($target);
	}

}
