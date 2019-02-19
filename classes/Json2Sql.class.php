<?php
class Json2Sql
{
    protected $json;
    public $tableInfo = [];


    /**
     * $strJson
     *
     * @param string $strJson
     */
    public function __construct(string $strJson)
    {
        $this->json = json_decode($strJson);

        // $this->parseMoves();
        $this->parseEvolutionBranches();
        // $this->parseMoves();

        die(print_r($this->tableInfo));
        // $this->parsePokemon();

        // return Helpers::arrayToInsertQuery('typing', $this->types)
        // ."\n\n".Helpers::arrayToInsertQuery('type_strengths', $this->typeStrengths)
        // ."\n\n".Helpers::arrayToInsertQuery('fast_moves', $this->fastMoves)
        // ."\n\n".Helpers::arrayToInsertQuery('charged_moves', $this->chargedMoves)
        // ."\n\n".Helpers::arrayToInsertQuery('pokemons', $this->pokemons)
        // ."\n\n".Helpers::arrayToInsertQuery('evolution_branches', $this->evolutionBranches)
        // ."\n\n".Helpers::arrayToInsertQuery('pokemon_fast_moves', $this->pokemon_fast_moves)
        // ."\n\n".Helpers::arrayToInsertQuery('pokemon_charged_moves', $this->pokemon_charged_moves)
        // ."\n\n".Helpers::arrayToInsertQuery('pokemon_images', $this->pokemonImages);
    }
    
    public function parseMoves()
    {
        $propertyColumnMappings = [
            'move_name'  => ['jsonPath' => 'moveSettings.movementId',  'parser' => 'parseMoveName'],
            'typing'     => ['jsonPath' => 'moveSettings.pokemonType', 'parser' => 'convertTypeIdToType'],
            'hits_for'   => ['jsonPath' => 'moveSettings.staminaLossScalar', 'nullable' => 0],
            'power'      => ['jsonPath' => 'moveSettings.power', 'nullable' => 0 ],
            'animation_length_ms'       => 'moveSettings.durationMs',
            'damage_start_ms'           => 'moveSettings.durationMs',
            'damage_end_ms'             => 'moveSettings.durationMs',
        ];
        
        $this->parseJsonObject('moves', 'moveSettings', $propertyColumnMappings);
    }

    /**
     * Searches for the uniqueProperty and then attempts to map all propertyMappings 
     * to $this->tableInfo
     *
     * @param string $tableName        Name of the table these properties will be set to as values
     * @param string $uniqueProperty   Each gamemaster template has a unique property, we use this to distinquish templates.
     * @param array  $propertyMappings Which properties to extract to which columnNames
     * @return void
     */
    protected function parseJsonObject(string $tableName, string $uniqueProperty, array $propertyColumnMappings)
    {

        foreach($this->json->itemTemplates as $template)
        {
            if($this->hasProperty($template, $uniqueProperty))
            {
                //die('hai');
            }
        }

        $arrayIndicatorPosition = strpos($uniqueProperty, '!');
        if($arrayIndicatorPosition !== false)
        {
            foreach($this->json->itemTemplates as $template)
            {
                if($this->hasProperty($template, str_replace('!', '', $uniqueProperty)))
                {
                    $arrayJsonPath = str_replace(
                        '!',
                        '',
                        substr(
                            $uniqueProperty,
                            0, 
                            strpos($uniqueProperty, '.', $arrayIndicatorPosition)
                        )
                    );
                    $array = $this->getArray($template, $arrayJsonPath);

                    foreach($array as $arrayValues)
                    {
                        $values = [];
                        foreach($propertyColumnMappings as $columnName => $propertyColumnMapping)
                        {
                            $jsonPath = is_array($propertyColumnMapping) ? $propertyColumnMapping['jsonPath'] : $propertyColumnMapping;

                            if(strpos($jsonPath, '!')!==false)
                            {
                                $jsonPath = str_replace('!', '',$jsonPath);
                                $jsonPath = str_replace($arrayJsonPath.'.', '', $jsonPath);
                                $values[$columnName] = $this->parseMatchedObject($arrayValues, $jsonPath);
                            }
                            else
                            {
                                $values[$columnName] = $this->parseMatchedObject($template, $propertyColumnMapping);
                            }
                        }

                        $this->tableInfo[$tableName][] = $values;
                    }
                }
                else
                {
                    foreach($this->json->itemTemplates as /** @param stdClass $template */ $template)
                    {
                        if(property_exists($template, $uniqueProperty) || $this->hasProperty($template, $uniqueProperty))
                        {
                            $values = [];
                            foreach($propertyColumnMappings as $columnName => $extractInstructions)
                            {
                                $values[$columnName] = $this->parseMatchedObject($template, $extractInstructions);    
                            }
                            $this->tableInfo[$tableName][] = $values;
                        }
                    }
                }
            }
        }




        // //find value with most array based values (occurences with exclamation mark)
        // $this->tableInfo[$tableName] = [];
        // foreach($this->json->itemTemplates as /** @param stdClass $template */ $template)
        // {
        //     if(property_exists($template, $uniqueProperty) || $this->hasProperty($template, $uniqueProperty))
        //     {
        //         $values = [];
        //         foreach($propertyColumnMappings as $columnName => $extractInstructions)
        //         {
        //             $values[$columnName] = $this->parseMatchedObject($template, $extractInstructions);    
        //         }
        //         $this->tableInfo[$tableName][] = $values;
        //     }
        // }
        // // print_r($this->tableInfo);

        // foreach($this->tableInfo as $tableName => $row)
        // {
        //     foreach($row as $columns)
        //     {
        //         $newColumns = [];
        //         foreach($columns as $columnTitle => $cell)
        //         {
        //             if(is_array($cell))
        //             {
        //                 foreach($cell as $columnTitleNew => $cellNew)
        //                 {
        //                     // print_r($propertyColumnMappings[$columnTitle]);
        //                     // die();
        //                     // $newColumns[$columnTitle] = $cellNew;
        //                 }
        //             }
        //         }
        //         // print_r($newColumns);
        //     }
        // }
        // print_r($this->tableInfo);
        // die();
    }

    protected function getArray($template, $string)
    {
        $stringParts = explode('.', $string);
        foreach($stringParts as $stringPart)
        {
            $template = $template->$stringPart;
        }

        return $template;
    }

    public function parseEvolutionBranches()
    {
        $propertyColumnMappings = [
            'evolves_from'  => ['jsonPath' => 'templateId', 'parser' => 'convertTemplateIdToPokemonName'],
            'evolves_into'  => ['jsonPath' => 'pokemonSettings.!evolutionBranch.evolution', 'parser' => 'getEnglishPokemonNameForId'],
            'candy_costs'   => 'pokemonSettings.!evolutionBranch.candyCost'
        ];
        
        $this->parseJsonObject('evolution_branches', 'pokemonSettings.!evolutionBranch.evolution', $propertyColumnMappings);
    }

    /**
     * Checks the $jsonPath on the stdClass $template to see if it exists or not.
     *
     * @param stdClass      $template       stdClass - json_decode returned objects
     * @param string|array  $jsonPath       If it's a string it will be converted to an array (split on '.' (periods))
     * @param integer       $currentDepth   Current depth is really just intended for its recursive use
     * @return boolean
     */
    protected function hasProperty(stdClass $template, $jsonPath, $currentDepth = 0, $arrValue = false)
    {
        if(is_string($jsonPath))
            $jsonPath = explode('.', $jsonPath);
        
        $property = $jsonPath[$currentDepth];

        
        if(property_exists($template, $property))
        {
            if(is_array($template->$property))
            {
                foreach($template->$property as $arrValue)//$arrValue = stdClass?
                {
                    if($this->hasProperty($arrValue, $jsonPath, $currentDepth+1))
                    {
                        return true;
                    }
                }
                return false; //Finished loop without finding it.
            }
            return $template->$property instanceof stdClass
                ? $this->hasProperty($template->$property, $jsonPath, $currentDepth+1)
                : $currentDepth === count($jsonPath) -1; //If max depth is reached true.
        }

        return false;
    }


    /**
     * $whatToExtract can be either a string or an array. 
     * If it's a string it will be considered like a xpath of sorts but for JSON, let's call it jsonPath. 
     * 
     * If it's an array it should have this format [
     *  'jsonPath' => 'your.json.path' 
     *  'nullable' => null,
     *  'parser'   => call_user_func_array() 
     * ]
     *
     * You can set the value of nullable to whatever value you'd like it to default to.
     * 
     * @param stdClass      $matchedObject
     * @param string|array  $whatToExtract
     * @return void
     */
    protected function parseMatchedObject(stdClass $matchedObject, $whatToExtract)
    {
        $targetParts = is_array($whatToExtract)
            ? explode('.', $whatToExtract['jsonPath'])
            : explode('.', $whatToExtract);

        return $this->digOutValue($matchedObject, $targetParts, 0, $whatToExtract);
    }

    /**
     * Undocumented function
     *
     * @param stdClass     $object
     * @param array        $targetParts
     * @param string|array $whatToExtract
     * @param integer      $currentStep
     * @return void
     */
    protected function digOutValue(stdClass $object, array $targetParts, int $currentStep = 0, $extractInstructions = null)
    {
        $target = $targetParts[$currentStep];

        if(property_exists($object, $target))
        {
            //If it's a stdClass, keep digging deeper
            if($object->$target instanceof stdClass)
            {
                return $this->digOutValue($object->$target, $targetParts, $currentStep+1, $extractInstructions);
            }
            else
            {
                $value = $object->$target;
                if(is_array($extractInstructions) && isset($extractInstructions['parser']))
                {
                    //var_dump(get_class_methods($this));
                    return call_user_func_array([$this, $extractInstructions['parser']], [$value]);
                }
                else if(is_numeric($value))
                {
                    return $value;
                }
                else if(is_string($value))
                {
                    return '"'.$value.'"';
                }
                else if(is_array($value))
                {
                    return $value;
                }
                else
                {
                    echo '<h1>'.var_export($targetParts,true).' || '.$currentStep.'</h1>';
                    die('Unknown value type: '.var_export($value, true));
                }
            }
        }
        else
        {
            if(is_array($extractInstructions) && isset($extractInstructions['nullable']))
            {
                return $this->parseNullable($extractInstructions['nullable']);
            }
            else
            {
                echo '<br/>';
                die('Property ."'.$target.'" doesnt exist on '.var_export($object, true).'<hr/>'.'Failed to match path <code>'.implode('.', $targetParts)).'</code>';
            }
        }
    }

    protected function parseNullable($nullable)
    {
        if(is_null($nullable))
            return '"null"';
        else
            return $nullable;
    }

    public function parseMoveName($moveId)
    {
        return ucfirst(strtolower($moveId));
    }
        
    protected function convertTypeIdToType($str)
    {
        return strtolower(substr($str, 13));
    }

    protected function getEnglishEvolutionItemName($evolutionItemRequirement)
    {
        switch($evolutionItemRequirement)
        {
            case 'ITEM_METAL_COAT':
                return 'Metal Coat';
            case 'ITEM_DRAGON_SCALE':
                return 'Dragon Scale';
            case 'ITEM_UP_GRADE':
                return 'Up-Grade';
            case 'ITEM_SUN_STONE':
                return 'Sun Stone';
            case 'ITEM_KINGS_ROCK':
                return 'King\'s Rock';
            case 'ITEM_GEN4_EVOLUTION_STONE':
                return 'Sinnoh Stone';
            default:
                return $evolutionItemRequirement;
        }
    }

    /**
     * Get the value of masterFileContent
     */ 
    public function getMasterFileContent()
    {
        return $this->masterFileContent;
    }

    /**
     * Convert the templateId to the English name of the Pokémon.
     *
     * @param [type] $str
     * @return void
     */
    public function convertTemplateIdToPokemonName($str)
    {
        //Get the Pokédex ID.
        $pokedexId    = intval(substr($str, 1, 4));
        $namePartOfId = substr($str, 14);
        $nameParts    = explode('_', $namePartOfId);

        //Fix unique cases
        //Do note that you may need to manipulate $parts[0] instead of $name
        switch($pokedexId)
        {
            case 29: //Female Nidoran
                $namePartOfId .= '?';
                break;
            case 32: //Male Nidoran
                $namePartOfId .= '?';
                break;
        }

        //If the Pokémon is an Alolan one, prepend  Alolan
        if(count($nameParts)>1 && $nameParts[1] === 'ALOLA')
        {
            //Alolan Pokémon don't have a space in their name so can return now.
            return 'Alolan '.$this->getEnglishPokemonName($nameParts[0]);
        }

        else if(count($nameParts) > 1)
        {
            if(strpos($namePartOfId, '_') !== false)
            {
                $partsArrayObject = new ArrayObject($nameParts);
                // create a copy of the array
                $nonNameParts = $partsArrayObject->getArrayCopy();
                // Get rid of the name
                array_shift($nonNameParts);
                
                return $this->getEnglishPokemonName($nameParts[0].' ('.implode(' ', $nonNameParts).')');
            }
        }

        return $this->getEnglishPokemonName($namePartOfId);
    }

    public function convertIdToPokemonName($id)
    {
        $id = $id[0];
        if(property_exists($id, 'form'))
        {
            return $this->getEnglishPokemonNameForForm($id->form);
        }
        else
        {
            return $this->getEnglishPokemonNameForId($id->evolution);
        }
    }
    
    protected function getEnglishPokemonNameForForm($idName)
    {
        switch($idName)
        {            
            case 'RATICATE_NORMAL':
            case 'SANDSLASH_NORMAL':
            case 'NINETALES_NORMAL':
            case 'DUGTRIO_NORMAL':
            case 'PERSIAN_NORMAL':
            case 'GRAVELER_NORMAL':
            case 'GOLEM_NORMAL':
                return ucfirst(explode('_', strtolower($idName))[0]);
            
            case 'RATICATE_ALOLA':
            case 'SANDSLASH_ALOLA':
            case 'NINETALES_ALOLA':
            case 'DUGTRIO_ALOLA':
            case 'PERSIAN_ALOLA':
            case 'MUK_ALOLA':
            case 'GRAVELER_ALOLA':
            case 'GOLEM_ALOLA':
                return 'Alolan '.ucfirst(explode('_', strtolower($idName))[0]);

            case 'WORMADAM_PLANT':
            case 'WORMADAM_SANDY':
            case 'WORMADAM_TRASH':
            case 'CHERRIM_OVERCAST':
            case 'CHERRIM_SUNNY':
            case 'GASTRODON_EAST_SEA':
            case 'GASTRODON_WEST_SEA':
                $parts = explode('_', strtolower($idName));
                return ucfirst($parts[0]).' ('.$parts[1].')';
            default:
                return ucfirst(strtolower($idName));
        }
    }

    protected function getEnglishPokemonNameForId($idName)
    {
        switch($idName)
        {        
            case 'MR_MIME':
                return 'Mr. Mime';
            case 'HO_OH':
                return 'Ho-Oh';
            case 'MIME_JR':
                return 'Mime jr.';
            case 'PORGYGON_Z':
                return 'Porygon-Z';
            default:
                return ucfirst(strtolower($idName));
        }
    }

    protected function getEnglishPokemonName($idName)
    {
        switch($idName)
        {
            case 'MR (MIME)':
                return 'Mr. Mime';
            case 'HO (OH)':
                return 'Ho-Oh';
            case 'MIME (JR)':
                return 'Mime jr.';
            case 'PORYGON (Z)':
                return 'Porygon-Z';
            default:
                return ucfirst(strtolower($idName));
        }
    }

}
?>