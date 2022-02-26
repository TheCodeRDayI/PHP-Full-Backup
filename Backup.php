<?php
/*
* MySql Full Backup
*
* @author     TheCodeRDayI
* @copyright  2021 TheCodeRDayI
* @mail       thecoderdayi@gmail.com
*
*/
class Backup
{
    private $config = [];
    private $db;
    private $sql;
    public function mysql($config = [])
    {
        if ($config) $this->config['mysql'] = $config;
        try {
            $this->db = new PDO('mysql:host=' . $this->config['mysql']['host'] . ';dbname=' . $this->config['mysql']['dbname'] . ';charset=utf8', $this->config['mysql']['user'], $this->config['mysql']['pass']);
        } catch (PDOException $e) {
            die($e->getMessage());
        }
        $tables = $this->getAll('SHOW TABLES');
        foreach ($tables as $table) {
            $tableName = current($table);
            $rows = $this->getAll('SELECT * FROM %s', [$tableName]);
            $this->sql .= '-- Tablo Adı: ' . $tableName . "\n-- Satır Sayısı: " . count($rows) . str_repeat(PHP_EOL, 2);
            $tableDetail = $this->getFirst('SHOW CREATE TABLE %s', [$tableName]);
            $this->sql .= $tableDetail['Create Table'] . ';' . str_repeat(PHP_EOL, 3);
            if (count($rows) > 0) {
                $columns = $this->getAll('SHOW COLUMNS FROM %s', [$tableName]);
                $columns = array_map(function ($column) {
                    return $column['Field'];
                }, $columns);
                $this->sql .= 'INSERT INTO `' . $tableName . '` (`' . implode('`,`', $columns) . '`) VALUES ' . PHP_EOL;
                $columnsData = [];
                foreach ($rows as $row) {
                    $row = array_map(function ($item) {
                        return $this->db->quote($item);
                    }, $row);
                    $columnsData[] = '(' . implode(',', $row) . ')';
                }
                $this->sql .= implode(',' . PHP_EOL, $columnsData) . ';' . str_repeat(PHP_EOL, 2);
                $this->sql .= '-- Create By TheCodeRDayI' . str_repeat(PHP_EOL, 2);
            }
        }
        $this->dumpTriggers();
        $this->dumpFunctions();
        $this->dumpProcedures();

        return $this->file_force_contents($this->config['mysql']['file'], $this->sql);
    }
    private function file_force_contents($fullPath, $contents)
    {
        $parts = explode('/', $fullPath);
        array_pop($parts);
        $dir = implode('/', $parts);

        if (!is_dir($dir))
            mkdir($dir, 0777, true);

        return file_put_contents($fullPath, $contents);
    }
    private function getFirst($query, $params = [])
    {
        return $this->db->query(vsprintf($query, $params))->fetch(PDO::FETCH_ASSOC);
    }
    private function getAll($query, $params = [])
    {
        return $this->db->query(vsprintf($query, $params))->fetchAll(PDO::FETCH_ASSOC);
    }
    private function dumpTriggers()
    {
        $triggers = $this->getAll('SHOW TRIGGERS');
        if (count($triggers) > 0) {
            $this->sql .= '-- TRIGGERS (' . count($triggers) . ')' . str_repeat(PHP_EOL, 2);
            $this->sql .= 'DELIMITER //' . PHP_EOL;
            foreach ($triggers as $trigger) {
                $query = $this->getFirst('SHOW CREATE TRIGGER %s', [$trigger['Trigger']]);
                $this->sql .= $query['SQL Original Statement'] . '//' . PHP_EOL;
            }
            $this->sql .= 'DELIMITER ;' . str_repeat(PHP_EOL, 5);
        }
    }
    private function dumpFunctions()
    {
        $functions = $this->getAll('SHOW FUNCTION STATUS WHERE Db = "%s"', [$this->config['mysql']['dbname']]);
        if (count($functions) > 0) {
            $this->sql .= '-- FUNCTIONS (' . count($functions) . ')' . str_repeat(PHP_EOL, 2);
            $this->sql .= 'DELIMITER //' . PHP_EOL;
            foreach ($functions as $function) {
                $query = $this->getFirst('SHOW CREATE FUNCTION %s', [$function['Name']]);
                $this->sql .= $query['Create Function'] . '//' . PHP_EOL;
            }
            $this->sql .= 'DELIMITER ;' . str_repeat(PHP_EOL, 5);
        }
    }
    private function dumpProcedures()
    {
        $procedures = $this->getAll('SHOW PROCEDURE STATUS WHERE Db = "%s"', [$this->config['mysql']['dbname']]);
        if (count($procedures) > 0) {
            $this->sql .= '-- PROCEDURES (' . count($procedures) . ')' . str_repeat(PHP_EOL, 2);
            $this->sql .= 'DELIMITER //' . PHP_EOL;
            foreach ($procedures as $procedure) {
                $query = $this->getFirst('SHOW CREATE PROCEDURE %s', [$procedure['Name']]);
                $this->sql .= $query['Create Procedure'] . '//' . PHP_EOL;
            }
            $this->sql .= 'DELIMITER ;' . str_repeat(PHP_EOL, 5);
        }
    }
    private function getDirectory($dir)
    {
        static $files = [];
        foreach (glob($dir . '/*') as $file) {
            $notInclude = !in_array(str_replace($this->config['folder']['dir'] . '/', null, $file), $this->config['folder']['exclude']);
            if (is_dir($file) && $notInclude) {
                call_user_func([$this, 'getDirectory'], $file);
            } else {
                if ($notInclude) $files[] = $file;
            }
        }
        return $files;
    }
    public function folder($config = [])
    {
        if (!extension_loaded('zip')) {
            throw new \Exception('Bu işlemi yapabilmek için ZipArchive extensionu yüklü olmalı!');
        }
        if ($config) $this->config['folder'] = $config;
        $files = $this->getDirectory($this->config['folder']['dir']);

        $parts = explode('/', $this->config['folder']['file']);
        array_pop($parts);
        $dir = implode('/', $parts);
        if (!is_dir($dir))
            mkdir($dir, 0777, true);

        $zip = new ZipArchive();
        $zip->open($this->config['folder']['file'], ZipArchive::CREATE);
        foreach ($files as $file) {
            $zip->addFile($file);
        }
        if (isset($this->config['db']['file'])) {
            $zip->addFile($this->config['db']['file'], basename($this->config['db']['file']));
        }
        $zip->close();
        $result = file_exists($this->config['folder']['file']);
        if ($result) {
            if (isset($this->config['db']['file'])) {
                @unlink($this->config['db']['file']);
            }
        }
        return $result;
    }
}