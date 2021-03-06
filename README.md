# PHP-Full-Backup

First, you need to initialize `Backup` class.

```php
include 'Backups/Backup.php';
$backup = new Backup();
```

This place is entirely up to you.

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
        //success message
        echo '<div class="alert alert-warning"><i class="fa fa-info-circle"></i> ' . $pathMySql . ' isimli mysql yedeği alındı.</div>'; //success message in Turkish
    }
} catch (Exception $e) {
    die($e->getMessage());
}

```

For folder backup;
```php
try {
    $folderBackup = $backup->folder(array(
        'dir' => __DIR__,
        'file' => $pathFolder . '/files.zip',
        'exclude' => ['plugins', 'dist']
    ));
    if ($folderBackup) {
        //success message
        echo '<div class="alert alert-warning"><i class="fa fa-info-circle"></i> ' . $pathFolder . ' isimli klasör yedeği alındı.</div>'; //success message in Turkish
    }
} catch (Exception $e) {
    die($e->getMessage());
}

```

# Screenshot
![Screenshot](img/screenshot.jpg)
