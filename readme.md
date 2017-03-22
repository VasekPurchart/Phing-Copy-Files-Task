Phing Copy Files Task
=====================

Copy files with better control over copying than the default Phing [`CopyTask`](https://www.phing.info/docs/guide/trunk/CopyTask.html).

The main disadvantage of the default task is that it ignores the `overwrite="false"` setting (which is also default) and if the source file is newer, it always rewrites the target file (see [phingofficial/phing#538](https://github.com/phingofficial/phing/issues/538)). This is unexpected and potentially very dangerous behaviour especially when copying configuration files etc.

`CopyFilesTask` has much narrower use-cases, but offers you more control about the copied files. It works only with files (not directories or filesets) and you can specify how exactly each of the files should be copied and what to do when the target already exists.

Usage
-----

To copy a file you can just write:

```xml
<target name="copy-configs">
	<copy-files>
		<file source="parameters.local.yml.dist" target="parameters.local.yml"/>
	</copy-files>
</target>
```

The paths are relative to the location of the buildfile. If you want to use absolute paths, you can write paths using `${project.basedir}`, which contains the location of the buildfile.

You can also copy multiple files within one task definition:

```xml
<target name="copy-configs">
	<copy-files>
		<file source="parameters.local.yml.dist" target="parameters.local.yml"/>
		<file source="${environment}/parameters.yml" target="parameters.yml"/>
	</copy-files>
</target>
```

### Target file exists mode

If the target file already exists, there are three modes how you can choose that the situation will be handled:

1) `skip` (default) - copying of the file is skipped (and logged to output)
2) `replace` - always replace target file, even if it exists
3) `fail` - do not overwrite the target file and fail the build

You can set the mode for all files at once with `existsmode` parameter on `<copy-files>`:

```xml
<target name="copy-configs">
	<copy-files existsmode="skip">
		<file source="parameters.local.yml.dist" target="parameters.local.yml"/>
		<file source="${environment}/parameters.yml" target="parameters.yml"/>
	</copy-files>
</target>
```

And also for each `<file>`, meaning it will override the `<copy-files>` setting:

```xml
<target name="copy-configs">
	<copy-files existsmode="skip">
		<file source="parameters.local.yml.dist" target="parameters.local.yml"/>
		<file source="${environment}/parameters.yml" target="parameters.yml" existsmode="replace"/>
	</copy-files>
</target>
```

Installation
------------

1) Install package [`vasek-purchart/phing-copy-files-task`](https://packagist.org/packages/vasek-purchart/phing-copy-files-task) with [Composer](https://getcomposer.org/):

```bash
composer require vasek-purchart/phing-copy-files-task
```

2) Register this task under a name of your choosing.

There are several ways how to register a task, see the `TaskDefTask` documentation. The recommended way is putting this in your `build.xml`:

```xml
<taskdef name="copy-files" classname="VasekPurchart\Phing\CopyFiles\CopyFilesTask"/>
```

You can pick any other name for the command if you would like to.
