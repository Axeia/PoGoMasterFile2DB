<?php

class GameMasterFileParser
{
    /**
     * Contains the raw master file content as string
     *
     * @var string
     */
    protected $masterFileContent      = '';
    /**
     * Contains the json_decoded master file
     *
     * @var array
     */
    protected $masterFileJson         = [];
    
    /**
     * Will contain all table names, column names and cell values in the format
     * $tableName = [
     *   'table_name' => [
     *     ['column_name' => 'cell_value'],
     *     ['column_name' => 'cell_value']
     *   ]
     * ]
     *
     * @var array
     */
    protected $tableInfo = [
        'types'                 => [],
        'type_strengths'        => [],
        'pokemons'              => [],
        'evolution_branches'    => [],
        'fast_moves'            => [],
        'charged_moves'         => [],
        'pokemon_fast_moves'    => [], //multiple to multiple
        'pokemon_charged_moves' => []  //multiple to multiple
    ];
  
    /**
     * Decodes the master file and iterates over it parsing its data into $this->tableInfo
     *
     * @param string $masterFileContent
     */
    public function __construct(string $masterFileContent)
    {
        $this->masterFileContent = $masterFileContent;
        $this->masterFileJson = json_decode($this->masterFileContent);
        $this->parseAllInfo();
    }

    protected function parseAllInfo()
    {
        /**
         * First loop over all item templates.
         */
        foreach($this->masterFileJson->itemTemplates as $itemTemplate)
        {
            if(property_exists($itemTemplate, 'pokemonSettings'))
            {
                //If pokémon has the same stats under a different name we skip this variant.
                if(GameMasterFileParserData::isDuplicatePokemonVariant($itemTemplate->templateId))
                    continue;
                
                //else
                $this->parsePokemon($itemTemplate);

                if(property_exists($itemTemplate->pokemonSettings, 'evolutionBranch'))
                {
                    $this->parseEvolutionBranch($itemTemplate->templateId, $itemTemplate->pokemonSettings->evolutionBranch);
                }

                //If Pokémon has multiple forms
                if(property_exists($itemTemplate, 'formSettings'))
                {

                }
            }

            //Get all the types
            if(property_exists($itemTemplate, 'typeEffective'))
            {
                $this->parseType($itemTemplate);
                //attacktype = what's attacking.
            }

            //Get all the moves
            if(property_exists($itemTemplate, 'moveSettings'))
            {
                $this->parseMove($itemTemplate);
            }

            //Pokémon Let's Go tradable pokémon.
            if($itemTemplate->templateId === 'BELUGA_POKEMON_WHITELIST')
            {

            }
        }

        /**
         * Second loop - parse data that relies on some info in $tableInfo already having been filled in during the first loop.
         */
        foreach($this->masterFileJson->itemTemplates as $itemTemplate)
        {
            //Genders belong to Pokémon so belong will need to have been filled in already.
            if(property_exists($itemTemplate, 'genderSettings'))
            {
                $pokemonTemplateId = substr($itemTemplate->templateId, 6);

                //Skipped them the first time, which means they definitely won't have any data belonging to them.
                if(GameMasterFileParserData::isDuplicatePokemonVariant($pokemonTemplateId))
                    continue;
                                
                if(property_exists($itemTemplate->genderSettings->gender, 'genderLessPercent'))
                {                    
                    $this->tableInfo['pokemons'][$pokemonTemplateId]['male_ratio'] = 'null';
                    $this->tableInfo['pokemons'][$pokemonTemplateId]['female_ratio'] = 'null';
                }
                else
                {
                    $this->tableInfo['pokemons'][$pokemonTemplateId]['male_ratio'] = 
                        property_exists($itemTemplate->genderSettings->gender, 'malePercent')
                        ? $itemTemplate->genderSettings->gender->malePercent
                        : 0;
                    $this->tableInfo['pokemons'][$pokemonTemplateId]['female_ratio'] = 
                        property_exists($itemTemplate->genderSettings->gender, 'femalePercent')
                        ? $itemTemplate->genderSettings->gender->femalePercent
                        : 0;
                }
            }
        }

        $this->parseImageFiles();
    }

    /**
     * Checks whether the move is a fast or charged move and then
     * sends it further along to the approperiate method.
     *
     * @param stdClass $itemTemplate
     * @return void
     */
    protected function parseMove(stdClass $itemTemplate)
    {
        if(substr($itemTemplate->templateId, -5) === '_FAST')
        {
            $this->parseFastMove($itemTemplate);
        }
        else
        {
            $this->parseChargedMove($itemTemplate);
        }
    }

    
    /**
     * Parses the given fast move and adds it to the 
     * $this->tableInfo['fast_moves'] array
     *
     * @param stdClass $itemTemplate
     * @return void
     */
    protected function parseFastMove(stdClass $itemTemplate)
    {

        $moveSettings = $itemTemplate->moveSettings;
        $moveGenerics = $this->parseMoveGenerics($itemTemplate);
        //Add name to the start of the array
        $moveGenerics = array_merge(['move_name' => '"'.$this->getFastMoveName($moveSettings->movementId).'"'], $moveGenerics);

        //Add energy gain to end of the array
        $moveGenerics['energy_gain'] = property_exists($moveSettings, 'energyDelta') 
            ? $moveSettings->energyDelta
            : 0;

        $this->tableInfo['fast_moves'][] = $moveGenerics;
    }
    
    /**
     * Parses the given charged move and adds it to the 
     * $this->tableInfo['charged_moves'] array
     *
     * @param stdClass $itemTemplate
     * @return void
     */
    protected function parseChargedMove(stdClass $itemTemplate)
    {
        $moveSettings = $itemTemplate->moveSettings;
        $moveGenerics = $this->parseMoveGenerics($itemTemplate);
        $moveGenerics = array_merge(['move_name' => '"'.$this->getMoveName($moveSettings->movementId).'"'], $moveGenerics);

        $moveGenerics['energy_cost'] = property_exists($moveSettings, 'energyDelta') 
            ? $moveSettings->energyDelta
            : 0;

        $this->tableInfo['charged_moves'][] = $moveGenerics;
    }

    protected function parseMoveGenerics(stdClass $itemTemplate)
    {
        // "movementId": "HEART_STAMP",
        // "animationId": 5,
        // "pokemonType": "POKEMON_TYPE_PSYCHIC",
        // "power": 40,
        // "accuracyChance": 1,
        // "criticalChance": 0.05000000074505806,
        // "staminaLossScalar": 0.05999999865889549,
        // "trainerLevelMin": 1,
        // "trainerLevelMax": 100,
        // "vfxName": "heart_stamp",
        // "durationMs": 1900,
        // "damageWindowStartMs": 1100,
        // "damageWindowEndMs": 1600,
        // "energyDelta": -33

        $moveSettings = $itemTemplate->moveSettings;

        return [
            'typing'              => '"'.$this->convertTypeIdToType($moveSettings->pokemonType).'"',
            'power'               => property_exists($moveSettings, 'power')
                ? $moveSettings->power
                : 0,
            'hits_for'            => property_exists($moveSettings, 'staminaLossScalar')
                ? $moveSettings->staminaLossScalar
                : 0,
            'animation_length_ms' => $moveSettings->durationMs,
            'damage_start_ms'     => $moveSettings->damageWindowStartMs,
            'damage_end_ms'       => $moveSettings->damageWindowEndMs
        ];
    }

    /**
     * Strips off the fast bit and then feeds to getMoveName
     *
     * @param string $moveId
     * @return string
     */
    protected function getFastMoveName($moveId)
    {
        return $this->getMoveName(substr($moveId, 0, -5));
    }

    /**
     * Returns the name of the move based on the moveId
     * (basically lower cases it, replaces underscores with spaces
     * and start each word with a capital letter)
     * 
     * @param string $moveId
     * @return string
     */
    protected function getMoveName($moveId)
    {
        $moveIdParts = explode('_', $moveId);
        $moveName = '';
        foreach($moveIdParts as $moveIdPart)
        {
            $moveName .= ucfirst(strtolower($moveIdPart)).' ';
        }

        return trim($moveName);
    }

    /**
     * Fills the types array
     *
     * @param stdClass $itemTemplate
     * @return void
     */
    protected function parseType(stdClass $itemTemplate)
    {
        $attackingType = $this->convertTypeIdToType($itemTemplate->templateId);
        $this->tableInfo['types'][] = [
            'typing' => '"'.$attackingType.'"'
        ];

        $attackScalars = $itemTemplate->typeEffective->attackScalar;
        $attackScalarLabels = GameMasterFileParserData::getAttackScalarLabels();
        foreach($attackScalars as $key => $value)
        {
            $this->tableInfo['type_strengths'][] = [
                'attacking_type' => '"'.$attackingType.'"',
                'receiving_type' => '"'.$attackScalarLabels[$key].'"',
                'scalar'         => $value
            ];
        }

    }

    /**
     * @param stdClass $itemTemplate stdClass representing a Pokémon.
     * @return void
     */
    protected function parsePokemon(stdClass $itemTemplate)
    {
        /** @var stdClass $pokemonSettings */
        $pokemonSettings = $itemTemplate->pokemonSettings;
        /** @var stdClass $pokemonSettings */
        $pokemonStats = $pokemonSettings->stats;
        /** @var stdClass $encounter */
        $encounter = $pokemonSettings->encounter;

        $pokemonName = $this->convertTemplateIdToPokemonName($itemTemplate->templateId);
        $this->tableInfo['pokemons'][$itemTemplate->templateId] = [
            'name'              => '"'.$pokemonName.'"',
            'pokedex_number'    => intval(substr($itemTemplate->templateId, 1, 4)),
            'type1'             => '"'.$this->convertTypeIdToType($pokemonSettings->type).'"',
            'type2'             => property_exists($pokemonSettings, 'type2')
                ? '"'.$this->convertTypeIdToType($pokemonSettings->type2).'"'
                : 'null',
            'base_attack'       => $pokemonStats->baseAttack,
            'base_defense'      => $pokemonStats->baseDefense,
            'base_stamina'      => $pokemonStats->baseStamina,
            //Baby form exclusively from eggs if not present
            'base_capture_rate' => property_exists($encounter, 'baseCaptureRate')
                ? $encounter->baseCaptureRate
                : 0,
            //Quest reward if not present
            'base_flee_rate'    => property_exists($encounter, 'baseFleeRate')
                ? $encounter->baseFleeRate
                : 0,
            //'evolves_from'      => $pokemonSettings->evolve,
            'buddy_distance'    => $pokemonSettings->kmBuddyDistance,
            'released'          => intval(GameMasterFileParserData::isReleasedPokemon($pokemonName)),
            'shiny'             => intval(GameMasterFileParserData::isShinyPokemon($pokemonName)),
            //Legendary or Mythical if not
            'gym_defender'      => property_exists($pokemonSettings, 'isDeployable')
                ? $pokemonSettings->isDeployable
                : 0,
            'tradable'          => property_exists($pokemonSettings, 'isTransferable')
                ? $pokemonSettings->isTransferable
                : 0
        ];

        if(property_exists($pokemonSettings, 'quickMoves'))
        {
            foreach($pokemonSettings->quickMoves as $quickMove)
            {
                $this->tableInfo['pokemon_fast_moves'][] = [
                    'pokemon_name' => '"'.$pokemonName.'"',
                    'move_name'    => '"'.$quickMove.'"',
                    'legacy'       => 0
                ];
            }
        }

        if(property_exists($pokemonSettings, 'cinematicMoves'))
        {
            {
                foreach($pokemonSettings->cinematicMoves as $chargedMove)
                $this->tableInfo['pokemon_charged_moves'][] = [
                    'pokemon_name' => '"'.$pokemonName.'"',
                    'move_name'    => '"'.$chargedMove.'"',
                    'legacy'       => 0
                ];
            }
        }
    }

    /**
     * Parses the evolutionBranch (what Pokémon involves into which other one and at what cost)
     *
     * @param string $templateId
     * @param array  $evolutionBranches
     * @return void
     */
    protected function parseEvolutionBranch($templateId, array $evolutionBranches)
    {
        foreach($evolutionBranches as /** @var stdClass $evolutionBranch */ $evolutionBranch)
        {
            $evolutionBranchValues = [
                'evolves_from' => '"'.$this->convertTemplateIdToPokemonName($templateId).'"',
                'evolves_into' => '"'.$this->convertIdToPokemonName($evolutionBranch).'"',
                'candy_costs'  => $evolutionBranch->candyCost
            ];
            
            $evolutionBranchValues['buddy_distance'] = property_exists($evolutionBranch, 'kmBuddyDistanceRequirement')
                ? $evolutionBranch->kmBuddyDistanceRequirement
                : 'null';
            $evolutionBranchValues['evolution_item'] = property_exists($evolutionBranch, 'evolutionItemRequirement')
                ? '"'.$this->getEnglishEvolutionItemName($evolutionBranch->evolutionItemRequirement).'"'
                : 'null';
            
            $evolutionBranchValues['gender'] = GameMasterFileParserData::isGenderBasedEvolution($evolutionBranch->evolution)
                ? GameMasterFileParserData::getGenderRequiredForEvolution($evolutionBranch->evolution)
                : 'null';

            $this->tableInfo['evolution_branches'][] = $evolutionBranchValues;
        }
    }

    /**
     * Takes a item reference and returns the name of it.
     *
     * @param string $evolutionItemRequirement
     * @return void
     */
    protected function getEnglishEvolutionItemName(string $evolutionItemRequirement)
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
     *
     * @return string
     */
    public function getMasterFileContent()
    {
        return $this->masterFileContent;
    }

    /**
     * Convert the templateId to the English name of the Pokémon.
     *
     * @param string $str
     * @return string
     */
    public function convertTemplateIdToPokemonName(string $str)
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
                $namePartOfId .= '♀';
                break;
            case 32: //Male Nidoran
                $namePartOfId .= '♂';
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

    /**
     *
     * @param string $id
     * @return string
     */
    public function convertIdToPokemonName(stdClass $id)
    {
        if(property_exists($id, 'form'))
        {
            return $this->getEnglishPokemonNameForForm($id->form);
        }
        else
        {
            return $this->getEnglishPokemonNameForId($id->evolution);
        }
    }
    
    /**
     * Returns the name for Pokémon that have multiple forms
     *
     * @param string $idName
     * @return string
     */
    protected function getEnglishPokemonNameForForm(string $idName)
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
                return ucfirst(array_shift($parts)).' ('.implode(' ', $parts).')';
            default:
                return ucfirst(strtolower($idName));
        }
    }

    /**
     * Returns name for the given id
     *
     * @param string $idName
     * @return string
     */
    protected function getEnglishPokemonNameForId(string $idName)
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

    /**
     * If we have messed with a name already this fixes it.
     *
     * @param string $idName
     * @return string
     */
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

    /**
     * Converts type ID to a the english type name
     *
     * @param string $str
     * @return string
     */
    protected function convertTypeIdToType(string $str)
    {
        return strtolower(substr($str, 13));
    }    

    /**
     * Returns all the SQL insert queries as one big string
     *
     * @return string
     */
    public function getSqlInsertQueries()
    {
        $insertQueries = '';
        foreach($this->tableInfo as $tableName => $rows)
        {
            $insertQueries .= Helpers::arrayToInsertQuery($tableName, $rows)."\n\n";
        }
        return $insertQueries;
    }

    /**
     * Iterates over tableInfo - figures out if columns are nullable or not and finds their
     * smallest and largest values and their datatype.
     * 
     * Creates a table create statement based on these values
     *
     * @return void
     */
    public function getSqlCreateQueries()
    {
        $tableConstraints = [];
        //Iterate over all tables, all columns and all values to figure out
        //table names, column names and analyse the column valuies to figure out to which
        //mysql type they should map to.
        foreach($this->tableInfo as $tableName => $rows)
        {
            foreach($rows as $rowValues)
            {
                foreach($rowValues as $columnName => $cellValue) 
                {
                    if(!isset($tableConstraints[$tableName][$columnName]))
                    {
                        $tableConstraints[$tableName][$columnName] = 
                            $columnContraints = [
                                'type' => 'string|numeric',
                                'nullable'  => false,
                                'min_value' => 0,
                                'max_value' => 0,
                            ];
                    }
                    
                    if($cellValue === 'null')
                    {
                        $tableConstraints[$columnName]['nullable'] = true;
                        continue;
                    }
                    else if(is_int($cellValue) && $tableConstraints[$tableName][$columnName]['type'] !== 'decimal')
                    {
                        $tableConstraints[$tableName][$columnName]['type'] = 'int';                            
                        $tableConstraints[$tableName][$columnName]['min_value'] = min($cellValue, $tableConstraints[$tableName][$columnName]['min_value']);
                        $tableConstraints[$tableName][$columnName]['max_value'] = max($cellValue, $tableConstraints[$tableName][$columnName]['max_value']);
                    }
                    //Might pick up ints if the column was considered a decimal at some point.
                    else if(is_numeric($cellValue))
                    {
                        $tableConstraints[$tableName][$columnName]['type'] = 'decimal';                        
                        $tableConstraints[$tableName][$columnName]['min_value'] = min(strlen($cellValue.''), $tableConstraints[$tableName][$columnName]['min_value']);
                        $tableConstraints[$tableName][$columnName]['max_value'] = max(strlen($cellValue.''), $tableConstraints[$tableName][$columnName]['max_value']);
                    }
                    else//String
                    {
                        $tableConstraints[$tableName][$columnName]['type'] = 'string';
                        $tableConstraints[$tableName][$columnName]['min_value'] = min(strlen($cellValue), $tableConstraints[$tableName][$columnName]['min_value']);
                        $tableConstraints[$tableName][$columnName]['max_value'] = max(strlen($cellValue), $tableConstraints[$tableName][$columnName]['min_value']);
                    }
                }
            }
        }
        foreach($tableConstraints as $tableName => $constraints)
        {
            $createTable = 'CREATE TABLE '.$tableName."\n(";
            foreach($constraints as $columnName => $cellConstraints)
            {
                $createTable .= "\n\t".$columnName;

                if($cellConstraints['type'] === 'string')
                {
                    $createTable .= ' VARCHAR(255) COLLATE utf8mb4_unicode_ci';
                }

                else if($cellConstraints['type'] === 'numeric')
                {
                    if(is_int($cellConstraints['max_value']))
                    {
                        $createTable .= $cellConstraints['min_value'] >= -127 && $cellConstraints['max_value'] <= 127 
                            ? ' TINYINT'
                            : ' INT';
                    }
                    else
                    {
                        $createTable .= ' DECIMAL';
                    }
                }

                $createTable .= $cellConstraints['nullable'] ? ' NULL,' : ' NOT NULL,';
            }
            $createTable .= "\n);\n";
        }

        return $createTable;
    }

    /**
     * Returns all the SQL inserts queries in a seperate textarea HTML tag.
     *
     * @return string
     */
    public function getSqlInsertTextAreas()
    {
        $textAreas = '';
        foreach($this->tableInfo as $tableName => $rows)
        {
            $textAreas .= '<h3>'.$tableName.'</h3><textarea spellcheck="false" id="sql-insert-'.$tableName.'" id="sql-insert-'.$tableName.'" class="sql-insert">'
            .Helpers::arrayToInsertQuery($tableName, $rows)
            .'</textarea>';
        }

        return $textAreas;        
    }
    
    /**
     * Gets all the Laravel seeders as one big string
     *
     * @return string
     */
    public function getLaravelSeeders()
    {
        $laravelCode = '';
        foreach($this->tableInfo as $tableName => $rows)
        {
            $laravelCode .= Helpers::arrayToLaravelSeeder($tableName, $rows)."\n\n";
        }

        return $laravelCode;
    }

    /**
     * Returns all the laravel seeders in a seperate textarea HTML tag
     *
     * @return string
     */
    public function getLaravelSeederTextAreas()
    {
        $laravelCode = '';        
        foreach($this->tableInfo as $tableName => $rows)
        {
            $laravelCode .= '<h3>'.$tableName.'</h3><textarea id="laravel-seeder-'.$tableName.'" name="laravel-seeder-'.$tableName.'" class="laravel-seeder">'
            .Helpers::arrayToLaravelSeeder($tableName, $rows)
            .'</textarea>';
        }

        return $laravelCode;
    }

    /**
     * Returns array where the key is the filename and the value is the files content
     *
     * @return array
     */
    public function getLaravelZipArray()
    {
        $zipArray = [];
        foreach($this->tableInfo as $tableName => $rows)
        {
            $zipArray[Helpers::camelCaseString($tableName, false).'TableSeeder.php'] = 
                Helpers::arrayToLaravelSeeder($tableName, $rows);
        }

        return $zipArray;
    }

    /**
     * Returns array where the key is the filename and the value is the files content
     *
     * @return array
     */
    public function getSqlZipArray()
    {
        $zipArray = [];
        foreach($this->tableInfo as $tableName => $rows)
        {
            $zipArray[$tableName.'.sql'] = 
                Helpers::arrayToInsertQuery($tableName, $rows);
        }
        
        return $zipArray;
    }

    /**
     * Get the template ids for all the Pokémon
     *
     * @return void
     */
    public function getPokemonTemplateIds()
    {
        return array_keys($this->tableInfo['pokemons']);
    }

    public function getTableNames()
    {
        return array_keys($this->tableInfo);
    }

    /**
     * parses all the image files and adds them to $this->tableInfo
     *
     * @return void
     */
    protected function parseImageFiles()
    {
        $fileNames = explode("\r\n", file_get_contents(FileDownloader::$downloadToDirectory.'pokemon_icon_file_names.txt'));

        /* These Pokémon are a special case, due to having multiple forms */
        $specialCases = [
            'V0351_POKEMON_CASTFORM_NORMAL'=> '11',
            'V0351_POKEMON_CASTFORM_RAINY' => '13',
            'V0351_POKEMON_CASTFORM_SNOWY' => '14',
            'V0351_POKEMON_CASTFORM_SUNNY' => '12',
            'V0386_POKEMON_DEOXYS_NORMAL'  => '11',
            'V0386_POKEMON_DEOXYS_ATTACK'  => '12',
            'V0386_POKEMON_DEOXYS_DEFENSE' => '13',
            'V0386_POKEMON_DEOXYS_SPEED'   => '14',
            
            'V0421_POKEMON_CHERRIM'          => '11',
            'V0487_POKEMON_GIRATINA_ALTERED' => '11',
            'V0487_POKEMON_GIRATINA_ORIGIN'  => '12',
            'V0492_POKEMON_SHAYMIN_LAND'     => '11',
        ];
        /*
        * These pokémon we'll just skip for the image part as they don't have an image yet.
        * This is due to them having multiple forms and niantic probably not having figured out yet how to implement that.
        * 
        * If an image does show up for them move them to $specialCases
        */
        $overwriteCase = [
            'V0413_POKEMON_WORMADAM'            => '',
            'V0413_POKEMON_WORMADAM_PLANT'      => '',
            'V0413_POKEMON_WORMADAM_SANDY'      => '',
            'V0413_POKEMON_WORMADAM_TRASH'      => '',
            'V0421_POKEMON_CHERRIM_OVERCAST'    => '',
            'V0421_POKEMON_CHERRIM_SUNNY'       => '',
            'V0422_POKEMON_SHELLOS'             => '',
            'V0422_POKEMON_SHELLOS_EAST_SEA'    => '',
            'V0422_POKEMON_SHELLOS_WEST_SEA'    => '',
            'V0423_POKEMON_GASTRODON'           => '',
            'V0423_POKEMON_GASTRODON_EAST_SEA'  => '',
            'V0423_POKEMON_GASTRODON_WEST_SEA'  => '',
            'V0479_POKEMON_ROTOM'               => '',
            'V0479_POKEMON_ROTOM_FAN'           => '',
            'V0479_POKEMON_ROTOM_FROST'         => '',
            'V0479_POKEMON_ROTOM_HEAT'          => '',
            'V0479_POKEMON_ROTOM_MOW'           => '',
            'V0479_POKEMON_ROTOM_WASH'          => '',
            //Equal to SPAWN_V0487_POKEMON_GIRATINA_ALTERED which is more accurate.
            'V0487_POKEMON_GIRATINA'            => '',
            //Equal to V0492_POKEMON_SHAYMIN_LAND which is more accurate
            'V0492_POKEMON_SHAYMIN'             => '',
            //Doesn't have an image yet
            'V0492_POKEMON_SHAYMIN_SKY'         => '',
            'V0493_POKEMON_ARCEUS'              => '',
            'V0493_POKEMON_ARCEUS_BUG'          => '',
            'V0493_POKEMON_ARCEUS_DARK'         => '',
            'V0493_POKEMON_ARCEUS_DRAGON'       => '',
            'V0493_POKEMON_ARCEUS_ELECTRIC'     => '',
            'V0493_POKEMON_ARCEUS_FAIRY'        => '',
            'V0493_POKEMON_ARCEUS_FIGHTING'     => '',
            'V0493_POKEMON_ARCEUS_FIRE'         => '',
            'V0493_POKEMON_ARCEUS_FLYING'       => '',
            'V0493_POKEMON_ARCEUS_GHOST'        => '',
            'V0493_POKEMON_ARCEUS_GRASS'        => '',
            'V0493_POKEMON_ARCEUS_GROUND'       => '',
            'V0493_POKEMON_ARCEUS_ICE'          => '',
            'V0493_POKEMON_ARCEUS_POISON'       => '',
            'V0493_POKEMON_ARCEUS_PSYCHIC'      => '',
            'V0493_POKEMON_ARCEUS_ROCK'         => '',
            'V0493_POKEMON_ARCEUS_STEEL'        => '',
            'V0493_POKEMON_ARCEUS_WATER'        => '',
        ];

        /** 
         * For some reason Raichu comes with a whole bunch of different image files that are all the same image.
         * This must be for some reason in the game code but for the database it's irrelevant skip them.
         **/
        $raichuDuplicateForms = [
            'pokemon_icon_026_61_01.png' => '',
            'pokemon_icon_026_61_01_shiny.png' => '',
            'pokemon_icon_026_61_02.png' => '',
            'pokemon_icon_026_61_02_shiny.png' => '',
            'pokemon_icon_026_61_03.png' => '',
            'pokemon_icon_026_61_03_shiny.png' => '',
            'pokemon_icon_026_61_04.png' => '',
            'pokemon_icon_026_61_04_shiny.png' => '',
            'pokemon_icon_026_61_05.png' => '',
            'pokemon_icon_026_61_05_shiny.png' => ''
        ];

        $templateIds = array_keys($this->tableInfo['pokemons']);

        //Iterate over all pokemon (by templateId) and figure out which image files belong to them
        //
        foreach($templateIds as $pbId)
        {
            $isAlolan         = strpos($pbId, 'ALOLA') !== false;
            //file name is pokemon_icon_[pokedex_number], add pokedex_number from the id.
            //the file name template is the part of the file that we know the file name will start with.
            $filenameTemplate = 'pokemon_icon_'.substr($pbId, 2, 3); 
            
            //Get the file names for this pokemon by iterating over all of them and see if the
            //start matches our template.
            $iconsForThisPokemon = []; 
            if(!isset($specialCases[$pbId]) && !isset($overwriteCase[$pbId]))
                $iconsForThisPokemon = $this->getArrayValuesThatStartWith($filenameTemplate, $fileNames);
            else if(isset($specialCases[$pbId]) )
                $iconsForThisPokemon = $this->getArrayValuesThatStartWith($filenameTemplate.'_'.$specialCases[$pbId], $fileNames);
            
            foreach($iconsForThisPokemon as $filename)
            {
                //Skip identical Raichu images.
                if(in_array($filename, $raichuDuplicateForms))
                    continue;
                
                $pokemon        = $this->tableInfo['pokemons'][$pbId];
                $filenameParts  = explode('_', $filename);
                $strGender      = strtok($filenameParts[3], '.png');
                $isGenderless   = $pokemon['male_ratio'] === 'null' && $pokemon['female_ratio'] === 'null';
                //If the file name has more than 4 dataparts (or 5 for shinies) we know that it is an alt_form
                $isAltForm      = strpos($filename, 'shiny') !== false 
                    ? count($filenameParts) > 5
                    : count($filenameParts) > 4;

                //Since alolan pokémon share the same pokedex number make sure they're not added to their baseform
                //or vice versa. Alolan form pokémon are always without gender differences so $strGender is actually $altForm 
                if(!$isAlolan && intval($strGender) < 61 || $isAlolan && intval($strGender)>= 61)
                {
                    $this->tableInfo['pokemon_images'][] = [
                        'name'      => $pokemon['name'],
                        'male'      =>  
                            //Male - if the species can even be male then 00 = male, Alolan Pokémon do have a gender but no gender differences
                            intval($isAlolan || !$isGenderless && $strGender === '00' && $pokemon['male_ratio'] != 1), 
                        'female'    =>
                            //Female - Females are typically '01' but for female exclusive species it can be 00.
                            //If there's no female specific form  it's the same as the male form.
                            intval(
                                !$isGenderless && $strGender === '01' 
                                || $pokemon['female_ratio'] == 1
                                || ($pokemon['female_ratio'] > 0 && !$this->checkIfFemaleVariantExists($fileNames, $filenameParts))
                                || $isAlolan //Alolan Pokémon do have a gender but no gender differences
                            ),
                        'alt_form'  => $isAltForm ? '"'.strtok($filenameParts[4], '.png').'"' : 'null',
                        'shiny'     => intval(strpos($filename, 'shiny')!==false),
                        'file_name' => '"'.$filename.'"'
                    ];
                }
            }
        }
    }

    /**
     * Iterates over all values of $values and checks if their value (which should be a string)
     * starts with $startsWith.
     * 
     * If it does then it returns a new array with just those values. If it didn't find any it 
     * returns an empty array
     *
     * @param string $startsWith
     * @param array $values
     * @return array
     */
    protected function getArrayValuesThatStartWith(string $startsWith, array $values)
    {
        $filteredValues = [];
        foreach($values as $value)
        {
            if(strpos($value, $startsWith) === 0)
                $filteredValues[] = $value;
        }

        return $filteredValues;
    }

    /**
     * If $maleForm is actually a maleForm it checks if there's a matching female form
     * and returns true if there is.
     * 
     * @param array $fileNames
     * @param array $maleForm
     * @return boolean
     */
    protected function checkIfFemaleVariantExists(array $fileNames, array $maleForm)
    {
        if(isset($maleForm[3]))
        {
            //00 = male form, replace it with 01 ( = female form) 
            $maleForm[3] = str_replace('00', '01', $maleForm[3]); 
            //If the male to female transformed filename exists return true
            return in_array(implode('_', $maleForm), $fileNames);
        }
        return false;
    }
}

?>