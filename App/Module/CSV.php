<?php

namespace App\Module;

final class CSV
{

    public static function read($filePath, $encoding = 'windows-1251')
    {
        $result = [];
        $file = fopen($filePath, "r");

        if ($file === false) {
            IO::writeLn('ошибка чтения файла ' . $filePath);
            return [];
        }

        while (($csvRow = fgetcsv($file, 1000, ";")) !== false) {
            $modifyCsvRow = [];
            foreach ($csvRow as $csvColKey => $scvCell) {
                $modifyCsvRow[] = iconv($encoding, 'utf-8', $scvCell);
            }
            $result[] = $modifyCsvRow;
        }
        fclose($file);
        return $result;
    }

    public static function write($filePath, $csvArray, $encoding = 'windows-1251')
    {
        $file = fopen($filePath, 'w');

        if ($file === false) {
            IO::writeLn('не удается записать в файл ' . $filePath);
            return;
        }

        foreach ($csvArray as $csvRow) {
            $modifyCsvRow = [];
            foreach ($csvRow as $csvColKey => $csvCell) {
                $modifyCsvRow[] = iconv('utf-8', $encoding, $csvCell);
            }
            fputcsv($file, $modifyCsvRow, ";");
        }
        fclose($file);
    }

    public static function readWithNames($filePath, $encoding = 'windows-1251')
    {
        $csv = self::read($filePath, $encoding);
        $names = [];
        $result = [];
        foreach ($csv as $key => $row) {
            $namedRow = [];
            if ($key == 0) {
                $names = $row;
                if (self::hasDupes($names)) {
                    echo('В csv ' . $filePath . ' названия столбцов не уникальны.');
                    exit;
                }
            } else {
                foreach ($names as $key => $name) {
                    if (isset($row[$key])) {
                        $namedRow[$name] = $row[$key];
                    } else {
                        $namedRow[$name] = '';
                    }
                }
                $result[] = $namedRow;
            }
        }
        return $result;
    }

    public static function writeWithNames($filePath, $csvWithNamesArray, $encoding = 'windows-1251')
    {
        $csv = [];
        $names = array_keys($csvWithNamesArray[0]);
        $csv[] = $names;

        foreach ($csvWithNamesArray as $row) {
            $rowWithoutNames = [];

            foreach ($names as $key => $name) {
                $rowWithoutNames[$key] = $row[$name];
            }

            $csv[] = $rowWithoutNames;
        }

        self::write($filePath, $csv, $encoding);
    }

    private static function hasDupes(array $input_array)
    {
        return count($input_array) !== count(array_flip($input_array));
    }


}