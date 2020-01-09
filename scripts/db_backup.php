<?php

/*
WHAT IS THIS?
Automated backup script that pulls the database, compresses, and syncs that plus
any user uploaded files over to an S3 bucket. Designed for Platform.sh

SETUP:
- Ensure that monolog is available, if not, add via composer.json
- Add the AWS PHP SDK (aws/aws-sdk-php) via composer.json
- Ensure that AWS AMI user is created and has access to read/write the ftusa-site-backups S3 bucket
- Add backups directory to .platform.app.yaml
    mounts:
        "/backups": "shared:files/backups"
- Add environmental variables in Platform.sh
    - env:AWS_ACCESS_KEY_ID
    - env:AWS_SECRET_ACCESS_KEY
    - env:LOGGLY_TOKEN (note: get from loggly > source setup > tokens)
    - env:FILES_TO_BACKUP (optional: only add if you have user uploaded files to back up -- if added use, full path [e.g. /app/storage/app/uploads])
- Deploy and test using: php ./jobs/db_backup.php
- Add cron task to .platform.app.yml
    db_backup:
        spec: "0 0 * * *"
        cmd: "php /app/scripts/db_backup.php"

Adapted by https://github.com/kaypro4 from an example by https://github.com/JGrubb - Thanks John!
*/

$home_dir = getenv('PLATFORM_DIR');

require_once __DIR__ . '/../vendor/autoload.php';

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

$log = new Logger('adventure-com');
$log->pushHandler(new StreamHandler('/var/log/app.log', Logger::INFO));

$bucket = 'adventure-com-backup';
$fixedBranch = strtolower(preg_replace('/[\W\s\/]+/', '-', getenv('PLATFORM_BRANCH')));
if ($fixedBranch != "master") {
	exit;
}

$baseDirectory = 'platform/' . getenv('PLATFORM_APPLICATION_NAME') . '/' . $fixedBranch;
$branchAndProject = getenv('PLATFORM_APPLICATION_NAME') . ' > ' . $fixedBranch;

$psh = new Platformsh\ConfigReader\Config();
if ($psh->isAvailable()) {
	//backup the db
	try {
		$sql_filename = date('Y-m-d_H:i:s') . '.gz';
		$backup_path = $home_dir . "/backups/";

		$database = $psh->relationships['database'][0];

		putenv("MYSQL_PWD={$database['password']}");
		exec("mysqldump --opt -h {$database['host']} -u {$database['username']} {$database['path']} | gzip > $backup_path$sql_filename");

		$s3 = new Aws\S3\S3Client([
			'version' => 'latest',
			'region' => 'ap-southeast-2',
			'credentials' => [
				'key' => getenv('AWS_ACCESS_KEY_ID'),
				'secret' => getenv('AWS_SECRET_ACCESS_KEY')
			]
		]);

		$s3->putObject([
			'Bucket' => $bucket,
			'Key' => "$baseDirectory/database/$sql_filename",
			'Body' => fopen($backup_path . $sql_filename, 'r')
		]);

		//remove local backup files that are older than 3 days
		$fileSystemIterator = new FilesystemIterator($backup_path);
		$now = time();
		foreach ($fileSystemIterator as $file) {
			if ($now - $file->getCTime() >= 60 * 60 * 24 * 3)
				unlink($backup_path . $file->getFilename());
		}

		$log->info("Successfully backed up database $sql_filename for $branchAndProject");
	} catch (Exception $e) {
		$log->error("Database backup error for $branchAndProject: " . $e->getMessage());
	}

	if (getenv('FILES_TO_BACKUP') !== false) {
		//backup any user uploaded files using sync if the environmental variable
		//exists for the environment
		try {

			$s3 = new Aws\S3\S3Client([
				'version' => 'latest',
				'region' => 'ap-southeast-2',
				'credentials' => [
					'key' => getenv('AWS_ACCESS_KEY_ID'),
					'secret' => getenv('AWS_SECRET_ACCESS_KEY')
				]
			]);

			//sync the files from one directory
			$s3->uploadDirectory(getenv('FILES_TO_BACKUP'), "$bucket/$baseDirectory/files");

			$log->info("Successfully backed up files " . getenv('FILES_TO_BACKUP') . " for $branchAndProject");
		} catch (Exception $e) {
			$log->error("Files backup error for $branchAndProject: " . $e->getMessage());
		}
	}

}
