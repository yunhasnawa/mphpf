<?php

namespace m;

class Util
{
    public static function removeUtf8Bom($text)
    {
        $bom = pack('H*','EFBBBF');

        $text = preg_replace("/^$bom/", '', $text);

        return $text;
    }

    public static function prePrint($data)
    {
        echo '<pre>';
        print_r($data);
        echo '</pre>';
    }

    public static function hasFirstOccurrence($search, $str)
    {
        $pos = strpos($str, $search);

        if($pos !== 0)
        {
            return false;
        }

        return true;
    }

    public static function isLocalhost()
    {
        return strpos($_SERVER['HTTP_HOST'], 'localhost') > -1;
    }

    public static function removeNonAlphaNumeric($str)
    {
        return preg_replace("/[^A-Za-z0-9 ]/", '', $str);
    }

    public static function splitFileExtension($fileName)
    {
        return explode('.', $fileName);
    }

    public static function fileChangeNameOnly($newNameWithNoExt, $oldNameWithExt)
    {
        $split = Util::splitFileExtension($oldNameWithExt);

        return $newNameWithNoExt . ".{$split[1]}";
    }

    public static function sanitizeFileName($originalName, $replacement = '_')
    {
        $split = Util::splitFileExtension($originalName);

        $name = preg_replace("/[^\da-z]/i", $replacement, $split[0]);

        return $name . '.' . $split[1];
    }

    public static function strRemoveLastSegment($delimiter, $str, $alsoRemoveLastDelimiter = false)
    {
        $split = explode($delimiter, $str);

        $result = '';

        $lastIndex = count($split) - 1;

        // TODO: WARNING! This line is changed in the middle of production.
        $lastDelimiterIndex = $alsoRemoveLastDelimiter ? $lastIndex - 1 : $lastIndex;

        for($i = 0; $i < $lastIndex; $i++)
        {
            $result .= $split[$i];

            if ($i < $lastDelimiterIndex) $result .= $delimiter;
        }

        return $result;
    }

    public static function strFormatTableColumnName($tableColumnName)
    {
        $format = strtolower($tableColumnName);
        $format = str_replace('_', ' ', $format);
        $format = ucfirst($format);

        return $format;
    }

    public static function sanitizeSqlInjection($str)
    {
        $str = str_replace("'", "\'", $str);
        $str = str_replace("--", '', $str);
        $str = str_replace('"', '', $str);
        $str = str_replace('/*', '', $str);
        $str = str_replace('*/', '', $str);
        //$str = str_replace("\\'", "\'", $str);

        return $str;
    }

    public static function sanitizeSqlInjectionArray(array $data)
    {
        $cleaned = array();

        foreach ($data as $key => $value)
        {
            $cleaned[$key] = self::sanitizeSqlInjection($value);
        }

        return $cleaned;
    }

    /**
     * Delete 1 or more character(s) from behind
     * @param string $string the processed string
     * @param int $count How many char(s) should be removed from behind?
     * @return false|string|null Null if input is not valid
     */
    public static function strRemoveLastChars($string, $count = 1)
    {
        if($count < 1)
            return null;

        $len = strlen($string);

        $subLen = ($len - $count);

        if($subLen < 0)
            return null;

        return substr($string, 0, $subLen);
    }

    public static function arrayContains($array, $value)
    {
        return (array_search($value, $array) !== false);
    }

    public static function strReplaceWithSpace($str, $search = '_', $ucwords = true)
    {
        $str = str_replace($search, ' ', $str);

        if($ucwords)
            $str = ucwords($str);

        return $str;
    }

    public static function arrayTableGetHeaders(array $tableArray, $beautify = true)
    {
        if(count($tableArray) < 1)
            return null;

        $headers = array();

        foreach ($tableArray[0] as $key => $value)
        {
            $header = $key;

            if($beautify)
                $header = self::strReplaceWithSpace($header);

            $headers[] = $header;
        }

        return $headers;
    }

    public static function arrayTableSelectSomeKeys(array $tableArray, array $keyList)
    {
        $result = array();

        foreach ($tableArray as $row)
        {
            $selectedRow = array();

            foreach ($keyList as $key)
            {
                $selectedRow[$key] = $row[$key];
            }

            $result[] = $selectedRow;
        }

        return $result;
    }

    public static function arrayTableAddKeyToAll(array $tableArray, $key, $value)
    {
        $result = array();

        foreach ($tableArray as $row)
        {
            $row[$key] = $value;

            $result[] = $row;
        }

        return $result;
    }

    public static function arrayTableToFlat1Dimension(array $tableArray)
    {
        $flat = array();

        foreach ($tableArray as $row)
        {
            foreach ($row as $key => $value)
            {
                $flat[] = $value;
            }
        }

        return $flat;
    }

    public static function csvRead($fileCompletePath, $fieldDelimiter = ';')
    {
        $row = 1;

        if (($handle = fopen($fileCompletePath, "r")) !== FALSE)
        {
            $fields = array();

            $converts = array();

            while (($data = fgetcsv($handle, 1000, $fieldDelimiter)) !== FALSE)
            {
                $num = count($data);

                if($row == 1)
                {
                    for ($c = 0; $c < $num; $c++)
                    {
                        // Replaces '<U+FEFF>' char. Can only be visible on textmate/notepad++
                        $fields[] = self::removeUtf8Bom($data[$c]);
                    }
                }
                else
                {
                    $record = array();

                    for ($c = 0; $c < $num; $c++)
                    {
                        $key = $fields[$c];

                        $record[$key] = $data[$c];
                    }

                    $converts[] = $record;
                }

                $row++;
            }

            fclose($handle);

            return $converts;
        }

        return null;
    }

    public static function strReplaceSingleQuoteWithMsWordQuote($str)
    {
        return str_replace("'", '’', $str);
    }

    public static function arrayFlat1DimensionToTableArray(array $array1Dimension, $indexColumnName, $valueColumnName)
    {
        $tableArray = array();

        foreach ($array1Dimension as $index => $value)
        {
            $tableArray[] = array(
                $indexColumnName => $index,
                $valueColumnName => $value
            );
        }

        return $tableArray;
    }

    public static function arrayTableAddNumbering(array $tableArray, $columnName = 'no', $startsFrom = 1)
    {
        $new = array();

        foreach ($tableArray as $row)
        {
            $numbering = array($columnName => $startsFrom);

            // Array union (+) operator.
            // See: https://stackoverflow.com/questions/11276313/php-move-associative-array-element-to-beginning-of-array
            $new[] = ($numbering + $row);

            $startsFrom++;
        }

        return $new;
    }

    public static function arrayTableRemoveSomeColumns(array $tableArray, array $columnNames)
    {
        $new = array();

        foreach ($tableArray as $row)
        {
            foreach ($columnNames as $name)
            {
                unset($row[$name]);
            }

            $new[] = $row;
        }

        return $new;
    }

    public static function arrayDeleteElementByValue($deletedValue, array $array1Dimension)
    {
        if (($key = array_search($deletedValue, $array1Dimension)) !== false)
        {
            unset($array1Dimension[$key]);
        }

        return $array1Dimension;
    }

    public static function arrayDeleteElementsByValues(array $deletedValues, array $array1Dimension)
    {
        foreach ($deletedValues as $deletedValue)
        {
            if (($key = array_search($deletedValue, $array1Dimension)) !== false) {
                unset($array1Dimension[$key]);
            }
        }

        return $array1Dimension;
    }

    public static function arrayAssocCollectEmptyFields(array $assocArray, $treatFalseAsEmpty = false)
    {
        $emptyFields = array();

        foreach ($assocArray as $key => $value)
        {
            $isEmpty = $value == null || $value == '';

            if($treatFalseAsEmpty)
                $isEmpty = $isEmpty || $assocArray == false;

            if($isEmpty)
                $emptyFields[] = $key;
        }

        return $emptyFields;
    }

    public static function arrayTableToKeyValuePairs($keyColumnName, $valueColumnName, array $tableArray, $defaultOptionKey = '', $defaultOptionValue = '')
    {
        $result = array(
            $defaultOptionKey => $defaultOptionValue
        );

        foreach ($tableArray as $row)
        {
            $key = $row[$keyColumnName];
            $value = $row[$valueColumnName];

            $result[$key] = $value;
        }

        return $result;
    }

    public static function arrayReplaceElementByValue($search, $replace, array $array1Dimension)
    {
        $new = array();

        foreach ($array1Dimension as $element)
        {
            if($element != $search)
                $new[] = $element;
            else {
                $new[] = $replace;
            }
        }

        return $new;
    }

    public static function arrayTableAddLinkToEmail(array $tableLikeArray, $emailColumnName = 'email')
    {
        $new = array();

        foreach ($tableLikeArray as $row)
        {
            $email = $row[$emailColumnName];

            $emailLink = <<< PHREDOC
<a href="mailto:$email">$email</a>
PHREDOC;
            $row[$emailColumnName] = $emailLink;

            $new[] = $row;
        }

        return $new;
    }

    public static function arrayAssocRemoveElementsByValue($searchValue, array &$assocArray)
    {
        foreach ($assocArray as $key => $value)
        {
            if($value == $searchValue)
                unset($assocArray[$key]);
        }
    }

    public static function arrayTableAddEmptyColumn(array $tableLikeArray, $columnName)
    {
        for($i = 0; $i < count($tableLikeArray); $i++)
        {
            $tableLikeArray[$i][$columnName] = '';
        }

        return $tableLikeArray;
    }

    public static function arrayAssocRemoveEmptyElements(array &$assocArray, $treatFalseAsEmpty = false)
    {
        foreach ($assocArray as $key => $value)
        {
            if($treatFalseAsEmpty)
                $shouldRemove = $value == null || $value == '' || $value == false;
            else
                $shouldRemove = $value == null || $value == '';

            if($shouldRemove)
            {
                unset($assocArray[$key]);
            }
        }
    }

    public static function arrayAssocRemoveElementByKeyIfEmpty(array &$assocArray, $checkedKey, $treatFalseAsEmpty = false)
    {
        foreach ($assocArray as $key => $value)
        {
            //pre_print($key . ' <-> ' . $checkedKey);

            if($key != $checkedKey)
                continue;

            if($treatFalseAsEmpty)
                $shouldRemove = $value == null || $value == '' || $value == false;
            else
                $shouldRemove = $value == null || $value == '';

            if($shouldRemove)
            {
                //pre_print('Removing key: ' . $checkedKey);
                unset($assocArray[$key]);
            }

            break;
        }
    }

    public static function arrayTableTake1ColumnAsArray1Dimension(array &$arrTable, $selectedColumnName)
    {
        $result = array();

        foreach ($arrTable as $row)
        {
            if(isset($row[$selectedColumnName]))
                $result[] = $row[$selectedColumnName];
        }

        return $result;
    }

    public static function arrayAssocChangeKey($oldKey, $newKey, array $arrAssoc)
    {
        $arrAssoc[$newKey] = $arrAssoc[$oldKey];

        unset($arrAssoc[$oldKey]);

        return $arrAssoc;
    }

    public static function arrayMultipleNamedInputFormTo2DArray($data)
    {
        /*
         * Array
           (
               [id_event] => Array
                   (
                       [0] => 9
                       [1] => 9
                   )

               [id_proposal] => Array
                   (
                       [0] => 370
                       [1] => 370
                   )

               [nim] => Array
                   (
                       [0] => 1731710056
                       [1] => 1731710028
                   )
           )
         */

        $normalized2D = array();

        foreach ($data as $key => $value)
        {
            foreach ($value as $idx => $value)
            {
                $normalized2D[$idx][$key] = $value;
            }
        }

        return $normalized2D;
    }

    public static function arrayAssocRemoveSomeKeys(array $nilaiAssoc, array $keys)
    {
        $new = array();

        foreach ($nilaiAssoc as $key => $value)
        {
            if(!in_array($key, $keys))
                $new[$key] = $value;
        }

        return $new;
    }

    public static function arrayAssocTakeSomeKeys(array $arrAssoc, array $keys)
    {
        $new = array();

        foreach ($arrAssoc as $key => $value)
        {
            if(in_array($key, $keys))
                $new[$key] = $value;
        }

        return $new;
    }

    public static function arrayAssocChangeCommaToPoint(array $keysToCheck, $arrAssoc)
    {
        foreach ($keysToCheck as $key)
        {
            if(isset($arrAssoc[$key]))
                $arrAssoc[$key] = str_replace(',', '.', $arrAssoc[$key]);
        }

        return $arrAssoc;
    }

    public static function arrayTableSortBasedOnAnotherArrayColumn(array $arrToSort, array $arrSortExample, $columnToCheck)
    {
        $sorted = array();

        foreach($arrSortExample as $exampleRow)
        {
            $valueToFind = $exampleRow[$columnToCheck];

            $find = self::arrayTableFindOneRowWithSpecificColumnValue('nim', $valueToFind, $arrToSort);

            $sorted[] = $find;
        }

        //pre_print($sorted);

        return $sorted;
    }

    public static function arrayTableFindOneRowWithSpecificColumnValue($columnName, $columnValue, $arrayTable)
    {
        foreach($arrayTable as $row)
        {
            if(!isset($row[$columnName]))
                return null;

            if($row[$columnName] == $columnValue)
                return $row;
        }
    }

    public static function csvWrite($tableArray, $outputFileName, $fieldSeparator = ',', $quote = '"')
    {
        if(count($tableArray) < 1)
            return false;

        $content = '';

        foreach ($tableArray[0] as $column => $value)
            $content .= "{$column}{$fieldSeparator}";

        $content = self::strRemoveLastOccurrencesOf("{$fieldSeparator}", $content);
        $content .= "\n";

        foreach ($tableArray as $row)
        {

            foreach ($row as $column => $value)
            {
                if(strpos($value, "\n") !== false
                || strpos($value, $fieldSeparator) !== false)
                    $value = "{$quote}$value{$quote}";

                $content .= "{$value}{$fieldSeparator}";
            }


            $content .= "\n";
        }

        file_put_contents($outputFileName, $content);
    }

    public static function strRemoveLastOccurrencesOf($stringToRemove, $originalString)
    {
        //pre_print($originalString);

        $expl = explode($stringToRemove, $originalString);

        //pre_print($expl);

        $explCount = count($expl);

        if($explCount < 2)
            return $originalString;

        // a,b,c,
        // [a][b][c]['']

        $result = '';

        for ($i = 0; $i < $explCount - 2; $i++)
        {
            $segment = ($expl[$i]);
            //pre_print($segment);
            $result .= $segment . $stringToRemove;
        }

        $result .= $expl[($explCount - 2)];

        if($expl[($explCount - 1)] != '')
            $result .= $expl[($explCount - 1)];

        return $result;
    }

    public static function triggerDownload($file)
    {
        header("Content-Disposition: attachment; filename=\"" . basename($file) . "\"");
        header("Content-Type: application/octet-stream");
        header("Content-Length: " . filesize($file));
        header("Connection: close");

        readfile($file);
    }

    public static function timestampMicroSeconds()
    {
        return round(microtime(true) * 1000 * 1000);
    }
}
