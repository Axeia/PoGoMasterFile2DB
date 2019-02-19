<?php

class Helpers
{
    /**
     * Takes a tablename string and an array and gives back an insert query string.
     * The array should contain arrays itself with the sub-arrays having the column name as 
     * the key and the value as the cell value
     *
     * @param string $tableName Name of the table you want to insert the values on
     * @param array $values 
     * @return string
     */
    public static function arrayToInsertQuery(string $tableName, array $values) 
    {        
        $columnTitles = array_keys(reset($values));

        //Create array with all column names as the keys and 0 as the initial longest value
        $maxLengths = array_combine($columnTitles, array_fill(0, count($columnTitles), 0) );
        
        //Set maxlength to the length of the longest value
        foreach($values as $row)
            foreach($row as $columnTitle => $columnValue)
                $maxLengths[$columnTitle] = max( mb_strlen($columnValue, 'UTF-8'), $maxLengths[$columnTitle] );

        //Set maxlength to the length of the column title if it exceeds the length of the longest value
        foreach($columnTitles as $columnTitle)
            $maxLengths[$columnTitle] = max( strlen($columnTitle), $maxLengths[$columnTitle] );

        //Add two extra spaces (one for a comma and one for a space)
        foreach($maxLengths as $key => $value)
            $maxLengths[$key]+= 2; 
        
        //Create the query
        $insertQuery  = 'INSERT INTO '.$tableName.'('."\n ";
        foreach($columnTitles as $columnTitle)
            $insertQuery .= str_pad($columnTitle.', ', $maxLengths[$columnTitle]);

        $insertQuery .= "\n) VALUES";

        //Add the values to the query
        foreach($values as $rowKey => $row)
        {
            $insertQuery .= "\n(";
            foreach($row as $columnTitle => $cellValue)
            {
                $padTo = $maxLengths[$columnTitle];
                $insertQuery .= self::mb_str_pad($cellValue.', ', $padTo, ' ');
            }
            $insertQuery = rtrim($insertQuery, ', ');
            $insertQuery .= "),";
        }
        $insertQuery = rtrim($insertQuery, ', ').';';

        return $insertQuery;
    }    

    public static function arrayToLaravelSeeder(string $tableName, array $values)
    {
        $className = self::camelCaseString($tableName).'TableSeeder';
        $insertCode = self::arrayToLaravelInsertCode($tableName, $values);
        
        $laravelTemplate = file_get_contents(__DIR__.'/../static_assets/laravel_seeder_template.php');
        $laravelTemplate = str_replace('LaravelSeeder', self::camelCaseString($tableName.'TableSeeder',false), $laravelTemplate);
        $laravelTemplate = str_replace('//inserts', $insertCode, $laravelTemplate);

        return $laravelTemplate;
    }
    
    private static function arrayToLaravelInsertCode(string $tableName, array $values, $padding = '    ')
    {
        $columnTitles = array_keys(reset($values));
        $variableName = self::camelCaseString($tableName);

        //Create array with all column names as the keys and 0 as the initial longest value
        $maxLengths = array_combine($columnTitles, array_fill(0, count($columnTitles), 0) );
        
        //Set maxlength to the length of the longest value
        foreach($values as $row)
            foreach($row as $columnTitle => $columnValue)
                $maxLengths[$columnTitle] = max( mb_strlen($columnValue, 'UTF-8'), $maxLengths[$columnTitle] );

        //Add two extra spaces (one for a comma and one for a space)
        foreach($maxLengths as $key => $value)
            $maxLengths[$key]+= 2; 
        
        //Create the query
        $insertCode  = '$'.$variableName.' = [';

        //Add the values to the query
        foreach($values as $rowKey => $row)
        {
            $insertCode .= "\n".str_repeat($padding, 3)."[";
            foreach($row as $columnTitle => $cellValue)
            {
                $padTo = $maxLengths[$columnTitle];
                $insertCode .= '"'.$columnTitle.'" => '.self::mb_str_pad($cellValue.', ', $padTo, ' ');
            }
            $insertCode = rtrim($insertCode, ', ');
            $insertCode .= "],";
        }
        $insertCode .= "\n".str_repeat($padding, 2)."];";
        

        $insertCode .= "\n\n"
        .str_repeat($padding, 2)."\DB::table('$tableName')->insert($$variableName)".";\n"
        .str_repeat($padding, 2)."echo 'Inserted '.count($$variableName).' rows into table ".$tableName."'.\"\\n\";";
        // $insertCode = rtrim($insertCode, ', ').';';

        return $insertCode;
    }

    /**
     * Multibyte version of str_pad, Nidoran♀ and Nidoran♂ got padded to the wrong length.
     *
     * @param  mixed $input
     * @param  mixed $pad_length
     * @param  mixed $pad_string
     * @param  mixed $pad_style
     * @param  mixed $encoding
     *
     * @return string
     */
    public static function mb_str_pad(string $input, int $pad_length, string $pad_string = " ", int $pad_style = STR_PAD_RIGHT, string $encoding="UTF-8")
    {
        return str_pad(
            $input,
            strlen($input) - mb_strlen($input,$encoding) + $pad_length,
            $pad_string,
            $pad_style
        );
    }

    /**
     * Takes a 
    */
    public static function camelCaseString(string $string, $lcFirst = true)
    {
        $string = ucwords($string, '_');
        $string = $lcFirst ?  lcfirst($string) :  $string;
        return str_replace('_', '', $string);
    }

    
    /**
     * Parses given list of templateIds, tries to match them against the filenames
     * and produces an image list based upon that.
     *
     * @param array $templateIds
     * @param array $fileNames
     * @return void
     */
    public static function PokemonImagesToInsertQuery(array $templateIds, array $fileNames)
    {
        
    }

}
?>