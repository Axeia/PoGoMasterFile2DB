<?php
/**
 * This class abstracts the data that we have to enter manually as we can't extract
 * all data from the game.
 */
class GameMasterFileParserData
{
    /** 
     * @var array Index is the pokémon name 
     * the value serves no purpose in the code it's just for documentation 
     **/
    private static $shinyPokemonNames = [
        //Gen 1
        'Bulbasaur'     => '0001',
        'Ivysaur'       => '0002',
        'Venusaur'      => '0003',
        'Charmander'    => '0004',
        'Charmeleon'    => '0005',
        'Charizard'     => '0006',
        'Squirtle'      => '0007',
        'Wartortle'     => '0008',
        'Blastoise'     => '0009',
        'Caterpie'      => '0010',
        'Metapod'       => '0011',
        'Butterfree'    => '0012',
        'Pichu'         => '0013',
        'Pikachu'       => '0025',
        'Raichu'        => '0026',
        'Alolan Raichu' => '0026',
        'Pichu'         => '0172',
        'Nidoran♀'      => '0029',
        'Nidorina'      => '0030',
        'Nidoqueen'     => '0031',
        'Growlithe'     => '0058',
        'Arcanine'      => '0059',
        'Geodude'       => '0074',
        'Graveler'      => '0075',
        'Golem'         => '0076',
        'Ponyta'        => '0077',
        'Rapidash'      => '0078',
        'Magnemite'     => '0081',
        'Magneton'      => '0082',
        'Grimer'        => '0088',
        'Muk'           => '0089',
        'Shellder'      => '0090',
        'Cloyster'      => '0091',
        'Gastly'        => '0092',
        'Haunter'       => '0093',
        'Gengar'        => '0094',
        'Drowzee'       => '0096',
        'Hypno'         => '0097',
        'Krabby'        => '0098',
        'Kingler'       => '0099',
        'Cubone'        => '0104',
        'Marowak'       => '0105',
        'Alolan Marowak'=> '0105',
        'Electabuzz'    => '0125',
        'Magmar'        => '0126',
        'Pinsir'        => '0127',
        'Magikarp'      => '0129',
        'Gyarados'      => '0130',
        'Eevee'         => '0133',
        'Vaporeon'      => '0134',
        'Jolteon'       => '0135',
        'Flareon'       => '0136',
        'Espeon'        => '0196',
        'Umbreon'       => '0197',
        'Omanyte'       => '0138',
        'Omastar'       => '0139',
        'Kabuto'        => '0140',
        'Kabutops'      => '0141',
        'Aerodactyl'    => '0142',
        'Articuno'      => '0144',
        'Zapdos'        => '0145',
        'Moltres'       => '0146',
        'Dratini'       => '0147',
        'Dragonair'     => '0148',
        'Dragonite'     => '0149',
        //Gen 2
        'Chikorita'     => '0152',
        'Bayleef'       => '0153',
        'Meganium'      => '0154',
        'Cyndaquil'     => '0155',
        'Quilava'       => '0156',
        'Typhlosion'    => '0157',
        'Togepi'        => '0175',
        'Togetic'       => '0176',
        'Natu'          => '0177',
        'Xatu'          => '0178',
        'Mareep'        => '0179',
        'Flaaffy'       => '0180',
        'Ampharos'      => '0181',
        'Marill'        => '0183',
        'Azumarill'     => '0184',
        'Sunkern'       => '0191',
        'Sunflora'      => '0192',
        'Murkrow'       => '0198',
        'Wynaut'        => '0167',
        'Wobbuffet'     => '0202',
        'Pineco'        => '0204',
        'Forretress'    => '0205',
        'Snubbull'      => '0209',
        'Granbull'      => '0210',
        'Delibird'      => '0225',
        'Houndour'      => '0228',
        'Houndoom'      => '0229',
        'Elekid'        => '0239',
        'Magby'         => '0240',
        'Larvitar'      => '0246',
        'Pupitar'       => '0247',
        'Tyranitar'     => '0248',
        'Lugia'         => '0249',
        'Ho-Oh'         => '0250',
        //Gen 3
        'Poochyena'     => '0261',
        'Mightyena'     => '0262',
        'Wingull'       => '0278',
        'Pelipper'      => '0279',
        'Azurill'       => '0298',
        'Makuhita'      => '0296',
        'Hariyama'      => '0297',
        'Sableye'       => '0302',
        'Mawile'        => '0303',
        'Aron'          => '0304',
        'Lairon'        => '0305',
        'Aggron'        => '0306',
        'Meditite'      => '0307',
        'Medicham'      => '0308',
        'Plusle'        => '0311',
        'Minun'         => '0312',
        'Roselia'       => '0315',
        'Wailmer'       => '0320',
        'Wailord'       => '0321',
        'Swablu'        => '0333',
        'Altaria'       => '0334',
        'Shuppet'       => '0353',
        'Banette'       => '0354',
        'Duskull'       => '0355',
        'Dusclops'      => '0356',
        'Absol'         => '0359',
        'Snorunt'       => '0361',
        'Glalie'        => '0362',
        'Luvdisc'       => '0370',
        'Beldum'        => '0374',
        'Metang'        => '0375',
        'Metagross'     => '0376',
        'Kyogre'        => '0382',
        //Gen 4
        'Shinx'         => '0403',
        'Luxio'         => '0404',
        'Luxray'        => '0405',
        'Budew'         => '0406',
        'Roserade'      => '0407',
        'Drifloon'      => '0425',
        'Drifblim'      => '0426',
        'Honchkrow'     => '0430',
        'Electivire'    => '0466',
        'Electivire'    => '0466',
        'Magmortar'     => '0467',
        'Togekiss'      => '0468',
        'Dusknoir'      => '0477',
    ];

    
    private static $notReleased = [
        //Gen 2 Johto
        'Smeargle'              => '',
        //Gen 3 Hoehn
        'Kecleon'               => '',
        'Clamperl'              => '',
        'Gorebyss'              => '',
        'Jirachi'               => '',
        //Gen 4
        'Burmy'                 => '',
        'Wormadam (plant)'      => '',
        'Wormadam (sandy)'      => '',
        'Wormadam (trash)'      => '',
        'Cherubi' => '',
        'Cherrim (overcast)'    => '',
        'Cherrim (sunny)'       => '',
        'Shellos (east sea)'    => '',
        'Shellos (west sea)'    => '',
        'Gastrodon (east sea)'  => '',
        'Gastrodon (west sea)'  => '',
        'Gible'                 => '',
        'Gabite'                => '',
        'Garchomp'              => '',
        'Hippopotas'            => '',
        'Hippowdon'             => '',
        'Magnezone'             => '',
        'Leafeon'               => '',
        'Glaceon'               => '',
        'Probopass'             => '',
        'Rotom (fan)'           => '',
        'Rotom (frost)'         => '',
        'Rotom (heat)'          => '',
        'Rotom (mow)'           => '',
        'Rotom (normal)'        => '',
        'Rotom (wash)'          => '',
        'Rotom (sky)'           => '',
        'Uxie'                  => '',
        'Mesprit'               => '',
        'Azelf'                 => '',
        'Dialga'                => '',
        'Regigigas'             => '',
        'Giratina (origin)'     => '',
        'Phione'                => '',
        'Manaphy'               => '',
        'Darkrai'               => '',
        'Shaymin (land)'        => '',
        'Shaymin (sky)'         => '',
        'Arceus (poison)'       => '',
        'Arceus (fire)'         => '',
        'Arceus (flying)'       => '',
        'Arceus (water)'        => '',
        'Arceus (bug)'          => '',
        'Arceus (normal)'       => '',
        'Arceus (dark)'         => '',
        'Arceus (electric)'     => '',
        'Arceus (psychic)'      => '',
        'Arceus (ground)'       => '',
        'Arceus (ice)'          => '',
        'Arceus (steel)'        => '',
        'Arceus (fairy)'        => '',
        'Arceus (fighting)'     => '',
        'Arceus (rock)'         => '',
        'Arceus (ghost)'        => '',
        'Arceus (grass)'        => '',
        'Arceus (dragon)'       => '',
    ];

    /**
     * Contains all the template_ids of Pokémon we don't want in the database, 
     * basically the Pokémon that are in the game file data but that only serve a purpose in the game. 
     * For the database they'd basically be duplicate data under a different Pokémon name.
     *
     * @var array
     */
    private static $unusedPokemonTemplateIds = [
        /* 
        * For some reason Pokémon that have an Alolan form come in 3 flavours.
        * 1) Original form (e.g. RATTATA)
        * 2) Alolan form   (e.g. RATTATA_ALOLA)
        * 3)'Normal' form  (e.g. RATTATA_NORMAL)
        * For our database we really only need #2 and either #1 or #3. Since we don't want "normal" displaying in the name.
        * we'll ignore #3 
        */
        'V0019_POKEMON_RATTATA_NORMAL'      => '',
        'V0020_POKEMON_RATICATE_NORMAL'     => '',
        'V0026_POKEMON_RAICHU_NORMAL'       => '',
        'V0027_POKEMON_SANDSHREW_NORMAL'    => '',
        'V0028_POKEMON_SANDSLASH_NORMAL'    => '',
        'V0037_POKEMON_VULPIX_NORMAL'       => '',
        'V0038_POKEMON_NINETALES_NORMAL'    => '',
        'V0050_POKEMON_DIGLETT_NORMAL'      => '',
        'V0051_POKEMON_DUGTRIO_NORMAL'      => '',
        'V0052_POKEMON_MEOWTH_NORMAL'       => '',
        'V0053_POKEMON_PERSIAN_NORMAL'      => '',
        'V0074_POKEMON_GEODUDE_NORMAL'      => '',
        'V0075_POKEMON_GRAVELER_NORMAL'     => '',
        'V0076_POKEMON_GOLEM_NORMAL'        => '',
        'V0088_POKEMON_GRIMER_NORMAL'       => '',
        'V0089_POKEMON_MUK_NORMAL'          => '',
        'V0103_POKEMON_EXEGGUTOR_NORMAL'    => '',
        'V0105_POKEMON_MAROWAK_NORMAL'      => '',

        //All of the following template_ids belong to a Pokémon that have multiple forms, one of the forms
        //is equal to the baseform (usually postfixed by _NORMAL) to keep _NORMAL we consider the baseform unused.
        'V0351_POKEMON_CASTFORM'            => '',
        'V0386_POKEMON_DEOXYS'              => '',
        'V0413_POKEMON_WORMADAM'            => '',
        'V0421_POKEMON_CHERRIM'             => '',
        'V0422_POKEMON_SHELLOS'             => '',
        'V0423_POKEMON_GASTRODON'           => '',
        'V0479_POKEMON_ROTOM'               => '',
        'V0487_POKEMON_GIRATINA'            => '',
        'V0492_POKEMON_SHAYMIN'             => '',
        'V0493_POKEMON_ARCEUS'              => '',
    ];

    private static $genderBasedEvolutions = [
        //male = 1,  2 = female
        'GALLADE'   => 1,
        'FROSLASS'  => 2,
        'MOTHIM'    => 1,
        'WORMADAM'  => 2,
        'VESPIQUEN' => 2,
    ];

    /**
     * The 'typeffective' objects in the gamemaster file have an
     * attackscalar attribute which holds an array with unlabeled values.
     * Consider these to be the labels (order matters!)
     *
     * @var array
     */
    private static $attackScalarLabels = [
        'normal', 
        'fighting', 
        'flying', 
        'poison', 
        'rock', 
        'ground', 
        'bug', 
        'ghost', 
        'steel', 
        'fire', 
        'water', 
        'grass', 
        'electric' ,
        'psychic',
        'ice',
        'dragon',
        'dark',
        'fairy'
    ];

    /**
     * If the Pokémons shiny form has been released return true.
     *
     * @param string $name
     * @return boolean
     */
    public static function isShinyPokemon($name)
    {
        return isset(self::$shinyPokemonNames[$name]);
    }

    /**
     * If the Pokémon is obtainable in the game return ture.
     *
     * @param string $name
     * @return boolean
     */
    public static function isReleasedPokemon($name)
    {
        return !isset(self::$notReleased[$name]);
    }
    
    /**
     * Returns true if the Pokémon is a variant that has the same stats but a different name,
     * there seems to be a few of these floating around that likely only have a use in the games code
     * for legacy reasons. (e.g. they changed the template_id scheme but didn't want to break code elsewhere)
     * 
     * For the database this is irrelevant.
     */
    public static function isDuplicatePokemonVariant($protobuffId)
    {
        return isset(self::$unusedPokemonTemplateIds[$protobuffId]);
    }

    public static function isGenderBasedEvolution($evolution)
    {
        return isset(self::$genderBasedEvolutions[$evolution]);
    }

    public static function getGenderRequiredForEvolution($evolution)
    {
        return self::$genderBasedEvolutions[$evolution];
    }
    
    public static function getAttackScalarLabels()
    {
        return self::$attackScalarLabels;
    }
}

?>