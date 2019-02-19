<?php

class SqlCreates
{

    /**
     * Returns the contents of all .sql files in /sql_templates/creates
     *
     * @return void
     */
    static public function getCreateStatements()
    {
        $sqlFileNames = glob(__DIR__.'/../sql_templates/creates/*.sql');
        $sqlCreates = '';

        foreach($sqlFileNames as $sqlFileName)
        {
            $sqlCreates .= file_get_contents($sqlFileName)."\n\n\n";
        }

        return $sqlCreates;
    }
}

?>