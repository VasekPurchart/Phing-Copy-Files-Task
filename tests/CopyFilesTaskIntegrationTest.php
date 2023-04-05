<?php

declare(strict_types = 1);

namespace VasekPurchart\Phing\CopyFiles;

use Generator;
use PHPUnit\Framework\Assert;
use Project;
use VasekPurchart\Phing\PhingTester\PhingTester;

class CopyFilesTaskIntegrationTest extends \PHPUnit\Framework\TestCase
{

	private const TEMP_DIRECTORY_PATH = __DIR__ . '/temp';

	/**
	 * @return mixed[][]|\Generator
	 */
	public function copyFileDataProvider(): Generator
	{
		yield 'copy file' => [
			'target' => 'testCopyFile',
			'sourceFileName' => '/foo',
			'targetFileName' => '/foo-copy',
			'existingSourceFileContents' => 'FOO',
			'existingTargetFileContents' => null,
			'expectedTargetFileContents' => 'FOO',
			'logMessageRegExp' => '~Copying.+/foo.+->.+/foo-copy~',
			'logMessagePriority' => Project::MSG_INFO,
		];

		yield 'copy file with absolute path' => [
			'target' => 'testCopyFileWithAbsolutePath',
			'sourceFileName' => '/foo',
			'targetFileName' => '/foo-copy',
			'existingSourceFileContents' => 'FOO',
			'existingTargetFileContents' => null,
			'expectedTargetFileContents' => 'FOO',
			'logMessageRegExp' => '~Copying.+/foo.+->.+/foo-copy~',
			'logMessagePriority' => Project::MSG_INFO,
		];

		yield 'target file exists' => [
			'target' => 'testTargetFileExists',
			'sourceFileName' => '/new',
			'targetFileName' => '/existing',
			'existingSourceFileContents' => 'NEW',
			'existingTargetFileContents' => 'EXISTING',
			'expectedTargetFileContents' => 'EXISTING',
			'logMessageRegExp' => '~/existing.+already exists.+SKIPPING~',
			'logMessagePriority' => Project::MSG_INFO,
		];

		yield 'target file exists skip' => [
			'target' => 'testTargetFileExistsSkip',
			'sourceFileName' => '/new',
			'targetFileName' => '/existing',
			'existingSourceFileContents' => 'NEW',
			'existingTargetFileContents' => 'EXISTING',
			'expectedTargetFileContents' => 'EXISTING',
			'logMessageRegExp' => '~/existing.+already exists.+SKIPPING~',
			'logMessagePriority' => Project::MSG_INFO,
		];

		yield 'target file exists replace' => [
			'target' => 'testTargetFileExistsReplace',
			'sourceFileName' => '/new',
			'targetFileName' => '/existing',
			'existingSourceFileContents' => 'NEW',
			'existingTargetFileContents' => 'EXISTING',
			'expectedTargetFileContents' => 'NEW',
			'logMessageRegExp' => '~/existing.+already exists~',
			'logMessagePriority' => Project::MSG_VERBOSE,
		];
	}

	/**
	 * @dataProvider copyFileDataProvider
	 *
	 * @param string $target
	 * @param string $sourceFileName
	 * @param string $targetFileName
	 * @param string $existingSourceFileContents
	 * @param string|null $existingTargetFileContents
	 * @param string $expectedTargetFileContents
	 * @param string $logMessageRegExp
	 * @param int $logMessagePriority
	 */
	public function testCopyFile(
		string $target,
		string $sourceFileName,
		string $targetFileName,
		string $existingSourceFileContents,
		?string $existingTargetFileContents,
		string $expectedTargetFileContents,
		string $logMessageRegExp,
		int $logMessagePriority
	): void
	{
		$sourceFilePath = self::TEMP_DIRECTORY_PATH . $sourceFileName;
		file_put_contents($sourceFilePath, $existingSourceFileContents);

		$targetFilePath = self::TEMP_DIRECTORY_PATH . $targetFileName;
		if ($existingTargetFileContents !== null) {
			file_put_contents($targetFilePath, $existingTargetFileContents);
		} elseif (file_exists($targetFilePath)) {
			unlink($targetFilePath);
		}

		$tester = new PhingTester(__DIR__ . '/copy-files-task-integration-test.xml', self::TEMP_DIRECTORY_PATH);
		$tester->executeTarget($target);

		Assert::assertFileExists($targetFilePath);
		Assert::assertSame($expectedTargetFileContents, file_get_contents($targetFilePath));
		$tester->assertLogMessageRegExp($logMessageRegExp, $target, $logMessagePriority);
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
		$buildFilePath = __DIR__ . '/copy-files-task-integration-test.xml';
		$tester = new PhingTester($buildFilePath);
		$target = __FUNCTION__;

		try {
			$tester->executeTarget($target);
			Assert::fail('Exception expected');
		} catch (\BuildException $e) {
			Assert::assertStringStartsWith($buildFilePath, $e->getLocation()->toString());
			Assert::assertRegExp('~one.+<file>.+expected~', $e->getMessage());
		}
	}

	public function testMissingSource(): void
	{
		$buildFilePath = __DIR__ . '/copy-files-task-integration-test.xml';
		$tester = new PhingTester($buildFilePath);
		$target = __FUNCTION__;

		try {
			$tester->executeTarget($target);
			Assert::fail('Exception expected');
		} catch (\BuildException $e) {
			Assert::assertStringStartsWith($buildFilePath, $e->getLocation()->toString());
			Assert::assertRegExp('~<file>.+`source`~', $e->getMessage());
		}
	}

	public function testMissingTarget(): void
	{
		$buildFilePath = __DIR__ . '/copy-files-task-integration-test.xml';
		$tester = new PhingTester($buildFilePath);
		$target = __FUNCTION__;

		try {
			$tester->executeTarget($target);
			Assert::fail('Exception expected');
		} catch (\BuildException $e) {
			Assert::assertStringStartsWith($buildFilePath, $e->getLocation()->toString());
			Assert::assertRegExp('~<file>.+`target`~', $e->getMessage());
		}
	}

	public function testInvalidFileExistsMode(): void
	{
		$buildFilePath = __DIR__ . '/copy-files-task-integration-test.xml';
		$tester = new PhingTester($buildFilePath);
		$target = __FUNCTION__;

		try {
			$tester->executeTarget($target);
			Assert::fail('Exception expected');
		} catch (\BuildException $e) {
			Assert::assertStringStartsWith($buildFilePath, $e->getLocation()->toString());
			Assert::assertRegExp('~invalid.+mode~i', $e->getMessage());
		}
	}

}
