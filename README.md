# PHP-Full-Backup

First, you need to initialize `Backup` class.

```php
include 'Backups/Backup.php';
$backup = new Backup();
```


```php
$pathMySql = "backups/MySql/Backup-" . date("Y-m-d-H-i-s");
$pathFolder = "backups/Folder/Backup-" . date("Y-m-d-H-i-s");
```

For mysql backup;
```php
try {
    $mysqlBackup = $backup->mysql(array(
        'host' => '{host}',
        'user' => '{user}',
        'pass' => '{pass}',
        'dbname' => '{database}',
        'file' => $pathMySql . '/database.sql'
    ));
    if ($mysqlBackup) {
        echo 'success';
    }
} catch (Exception $e) {
    die($e->getMessage());
}

```

For folder backup;
```php
try {
    $folderBackup = $backup->folder([
        'dir' => __DIR__,
        'file' => $pathFolder . '/files.zip',
        'exclude' => ['plugins', 'dist']
    ]);
    if ($folderBackup) {
        echo 'success';
    }
} catch (Exception $e) {
    die($e->getMessage());
}

```
