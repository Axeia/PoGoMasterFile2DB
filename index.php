<?php 
include('classes/ClassLoader.php');

$downloadGameMaster = new FileDownloader(
  'https://raw.githubusercontent.com/ZeChrales/PogoAssets/master/gamemaster/gamemaster.json',
  'gamemaster.json'
);
// $json2File = new Json2Sql($downloadGameMaster->getContent());
$parser = new GameMasterFileParser($downloadGameMaster->getContent());
$parser->getSqlCreateQueries();

$dowloadPokemonIconFileNames = new FileDownloaderSvnFileNames(
  'https://github.com/ZeChrales/PogoAssets/trunk/pokemon_icons/',
  'pokemon_icon_file_names.txt'
);

/**
 * [post -> download zip logic]
 * 
 * Creates a temporary zip file
 */
if(isset($_POST['download']))
{
    $zip = new ZipArchive();
    $zipFileName = '/tmp/xxx.zip';

    if ($zip->open($zipFileName, ZIPARCHIVE::CREATE)!==TRUE) 
    {
        throw new Exception();
    }

    $files = [];
    switch($_POST['download'])
    {
      case 'laravel':
        $files = $parser->getLaravelZipArray();
        break;
      case 'sql':
        $files = $parser->getSqlZipArray();
        break;        
    }
    
    foreach($files as $fileName => $fileContent)
    {
      $zip->addFromString($fileName, $fileContent);
    }

    $zip->close();
    $fileString = file_get_contents($zipFileName);
    unlink($zipFileName);
    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename='.$_POST['download'].'_db_stuff.zip');
    die($fileString);
}
 /**
  * [/post]
  */
//Helpers::PokemonImagesToInsertQuery($parser->getPokemonTemplateIds(), $dowloadPokemonIconFileNames->getImageFilesArray());

$messages = array_merge([], $downloadGameMaster->getMessages());
?>
<!doctype html>

<html lang="en">
<head>
  <meta charset="utf-8">

  <title>PoGoMasterFile2DB</title>
  <meta name="description" content="Laravel database file generator for Pokémon Go.">
  <meta name="author" content="Axeia">

  <link rel="stylesheet" href="/static_assets/style.css">
</head>

<body>
<div id="sidebar">
  <div id="h1-base"></div>
  <h1>PoGoMasterFile2DB</h1>
  <?php 
  if(count($messages)>0)
  {
    echo '<ul class="messages">';
    foreach($messages as $message)
    {
      echo '<li>'.$message.'</li>';
    }
    echo '</ul>';
  }
  ?>


  <!-- <h2 id="update-header">Update?</h2>
  <button id="update-button">Check for new files</button> -->


  <h2>Show </h2>
  <form>
      <label class="gamemaster-label">
        <input type="checkbox" class="view-toggle" id="show-gamemaster" checked />GameMaster
      </label>

      <fieldset>
        <legend>Right</legend>
        <label><input type="radio" class="view-toggle" id="sql-inserts" checked name="show-right" />SQL</label>
        <label><input type="radio" class="view-toggle" id="laravel-seeders" checked name="show-right" />Laravel</label>    
      </fieldset>
  </form>

  <h2>Download</h2>
  <form id="download-form" action="/" method="post">
    <fieldset>
      <legend>Files</legend>
      <button name="download" value="sql">SQL</button>
      <button name="download" value="laravel">Laravel</button>
    </fieldset>
  </form>
</div>
  <form id="main">
    <h2>gamemaster.json</h2>
    <textarea name="gamemasterfile" id="gamemasterfile" spellcheck="false"><?php echo $downloadGameMaster->getContent(); ?></textarea>

    <h2>SQL Creates</h2>
    <textarea name="sql-creates" id="sql-creates" spellcheck="false"><?php echo SqlCreates::getCreateStatements(); ?></textarea>

    <h2>SQL Inserts</h2>
    <?php echo $parser->getSqlInsertTextareas(); ?>

    <h2>Laravel Seeders</h2>
    <?php echo $parser->getLaravelSeederTextAreas(); ?>
  </form>


  <!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/ace/1.4.2/ace.js" type="text/javascript" charset="utf-8"></script> -->
  <script src="/static_assets/ace/ace.js"></script>
  <script src="/static_assets/javascript.js"></script>
</body>
</html>