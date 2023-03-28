<?php

declare(strict_types = 1);

namespace VasekPurchart\Phing\CopyFiles;

use PHPUnit\Framework\Assert;
use Project;
use VasekPurchart\Phing\PhingTester\PhingTester;

class CopyFilesTaskIntegrationTest extends \PHPUnit\Framework\TestCase
{

	private const TEMP_DIRECTORY_PATH = __DIR__ . '/temp';

	public function testCopyFile(): void
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

		Assert::assertFileEquals($sourceFilePath, $targetFilePath);
		$tester->assertLogMessageRegExp('~Copying.+/foo.+->.+/foo-copy~', $target, Project::MSG_INFO);
	}

	public function testCopyFileWithAbsolutePath(): void
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

		Assert::assertFileEquals($sourceFilePath, $targetFilePath);
		$tester->assertLogMessageRegExp('~Copying.+/foo.+->.+/foo-copy~', $target, Project::MSG_INFO);
	}

	public function testTargetFileExists(): void
	{
		$sourceFilePath = self::TEMP_DIRECTORY_PATH . '/new';
		file_put_contents($sourceFilePath, 'NEW');
		$targetFilePath = self::TEMP_DIRECTORY_PATH . '/existing';
		file_put_contents($targetFilePath, 'EXISTING');

		$tester = new PhingTester(__DIR__ . '/copy-files-task-integration-test.xml', self::TEMP_DIRECTORY_PATH);
		$target = __FUNCTION__;
		$tester->executeTarget($target);

		Assert::assertFileExists($targetFilePath);
		Assert::assertSame('EXISTING', file_get_contents($targetFilePath));
		$tester->assertLogMessageRegExp('~/existing.+already exists.+SKIPPING~', $target, Project::MSG_INFO);
	}

	public function testTargetFileExistsSkip(): void
	{
		$sourceFilePath = self::TEMP_DIRECTORY_PATH . '/new';
		file_put_contents($sourceFilePath, 'NEW');
		$targetFilePath = self::TEMP_DIRECTORY_PATH . '/existing';
		file_put_contents($targetFilePath, 'EXISTING');

		$tester = new PhingTester(__DIR__ . '/copy-files-task-integration-test.xml', self::TEMP_DIRECTORY_PATH);
		$target = __FUNCTION__;
		$tester->executeTarget($target);

		Assert::assertFileExists($targetFilePath);
		Assert::assertSame('EXISTING', file_get_contents($targetFilePath));
		$tester->assertLogMessageRegExp('~/existing.+already exists.+SKIPPING~', $target, Project::MSG_INFO);
	}

	public function testTargetFileExistsReplace(): void
	{
		$sourceFilePath = self::TEMP_DIRECTORY_PATH . '/new';
		file_put_contents($sourceFilePath, 'NEW');
		$targetFilePath = self::TEMP_DIRECTORY_PATH . '/existing';
		file_put_contents($targetFilePath, 'EXISTING');

		$tester = new PhingTester(__DIR__ . '/copy-files-task-integration-test.xml', self::TEMP_DIRECTORY_PATH);
		$target = __FUNCTION__;
		$tester->executeTarget($target);

		Assert::assertFileEquals($sourceFilePath, $targetFilePath);
		$tester->assertLogMessageRegExp('~/existing.+already exists~', $target, Project::MSG_VERBOSE);
	}

	public function testTargetFileExistsFail(): void
	{
		$sourceFilePath = self::TEMP_DIRECTORY_PATH . '/new';
		file_put_contents($sourceFilePath, 'NEW');
		$targetFilePath = self::TEMP_DIRECTORY_PATH . '/existing';
		file_put_contents($targetFilePath, 'EXISTING');

		$tester = new PhingTester(__DIR__ . '/copy-files-task-integration-test.xml', self::TEMP_DIRECTORY_PATH);
		$target = __FUNCTION__;

		$tester->expectFailedBuild($target);

		Assert::assertFileExists($targetFilePath);
		Assert::assertSame('EXISTING', file_get_contents($targetFilePath));
		$tester->assertLogMessageRegExp('~/existing.+already exists~', $target, Project::MSG_ERR);
	}

	public function testCopyMultipleFiles(): void
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

		Assert::assertFileEquals($sourceFooFilePath, $targetFooFilePath);
		$tester->assertLogMessageRegExp('~Copying.+/foo.+->.+/foo-copy~', $target, Project::MSG_INFO);
		Assert::assertFileEquals($sourceBarFilePath, $targetBarFilePath);
		$tester->assertLogMessageRegExp('~Copying.+/bar.+->.+/bar-copy~', $target, Project::MSG_INFO);
	}

	public function testReplaceMultipleFilesWithExistingTargets(): void
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

		Assert::assertFileEquals($sourceFooFilePath, $targetFooFilePath);
		$tester->assertLogMessageRegExp('~Copying.+/foo-new.+->.+/foo-existing~', $target, Project::MSG_INFO);
		$tester->assertLogMessageRegExp('~/foo-existing.+already exists~', $target, Project::MSG_VERBOSE);

		Assert::assertFileEquals($sourceBarFilePath, $targetBarFilePath);
		$tester->assertLogMessageRegExp('~Copying.+/bar-new.+->.+/bar-existing~', $target, Project::MSG_INFO);
		$tester->assertLogMessageRegExp('~/bar-existing.+already exists~', $target, Project::MSG_VERBOSE);
	}

	public function testCopyMultipleFilesWithExistingTargetsUsingDifferentModes(): void
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

		Assert::assertFileExists($targetSkipFilePath);
		Assert::assertSame('SKIP-EXISTING', file_get_contents($targetSkipFilePath));

		Assert::assertFileEquals($sourceReplaceFilePath, $targetReplaceFilePath);
		$tester->assertLogMessageRegExp('~Copying.+/replace-new.+->.+/replace-existing~', $target, Project::MSG_INFO);
		$tester->assertLogMessageRegExp('~/replace-existing.+already exists~', $target, Project::MSG_VERBOSE);

		Assert::assertFileExists($targetDefaultFilePath);
		Assert::assertSame('DEFAULT-EXISTING', file_get_contents($targetDefaultFilePath));
	}

	public function testCopyMultipleFilesWithExistingTargetsUsingDifferentModesWithReplaceFallback(): void
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

		Assert::assertFileExists($targetSkipFilePath);
		Assert::assertSame('SKIP-EXISTING', file_get_contents($targetSkipFilePath));

		Assert::assertFileEquals($sourceReplaceFilePath, $targetReplaceFilePath);
		$tester->assertLogMessageRegExp('~Copying.+/replace-new.+->.+/replace-existing~', $target, Project::MSG_INFO);
		$tester->assertLogMessageRegExp('~/replace-existing.+already exists~', $target, Project::MSG_VERBOSE);

		Assert::assertFileEquals($sourceDefaultFilePath, $targetDefaultFilePath);
		$tester->assertLogMessageRegExp('~Copying.+/default-new.+->.+/default-existing~', $target, Project::MSG_INFO);
		$tester->assertLogMessageRegExp('~/default-existing.+already exists~', $target, Project::MSG_VERBOSE);
	}

	public function testCopyNonExistentFile(): void
	{
		$tester = new PhingTester(__DIR__ . '/copy-files-task-integration-test.xml');
		$target = __FUNCTION__;

		$tester->expectFailedBuild($target);

		$tester->assertLogMessageRegExp('~XXX.+does not exist~', $target, Project::MSG_ERR);
	}

	public function testCopyMultipleNonExistentFiles(): void
	{
		$tester = new PhingTester(__DIR__ . '/copy-files-task-integration-test.xml');
		$target = __FUNCTION__;

		$tester->expectFailedBuild($target);

		$tester->assertLogMessageRegExp('~FOO.+does not exist~', $target, Project::MSG_ERR);
		$tester->assertLogMessageRegExp('~BAR.+does not exist~', $target, Project::MSG_ERR);
	}

	public function testCopyFileToNonExistingDirectory(): void
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

	public function testMissingCopyFileElement(): void
	{
		$tester = new PhingTester(__DIR__ . '/copy-files-task-integration-test.xml');
		$target = __FUNCTION__;

		$this->expectException(\BuildException::class);
		$this->expectExceptionMessageMatches('~one.+<file>.+expected~');

		$tester->executeTarget($target);
	}

	public function testMissingSource(): void
	{
		$tester = new PhingTester(__DIR__ . '/copy-files-task-integration-test.xml');
		$target = __FUNCTION__;

		$this->expectException(\BuildException::class);
		$this->expectExceptionMessageMatches('~<file>.+`source`~');

		$tester->executeTarget($target);
	}

	public function testMissingTarget(): void
	{
		$tester = new PhingTester(__DIR__ . '/copy-files-task-integration-test.xml');
		$target = __FUNCTION__;

		$this->expectException(\BuildException::class);
		$this->expectExceptionMessageMatches('~<file>.+`target`~');

		$tester->executeTarget($target);
	}

	public function testInvalidFileExistsMode(): void
	{
		$tester = new PhingTester(__DIR__ . '/copy-files-task-integration-test.xml');
		$target = __FUNCTION__;

		$this->expectException(\BuildException::class);
		$this->expectExceptionMessageMatches('~invalid.+mode~i');

		$tester->executeTarget($target);
	}

}
