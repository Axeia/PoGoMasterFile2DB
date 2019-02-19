<?php

/**
 * Downloads file it doesn't exist yet or is considered outdated.
 */
class FileDownloader
{
    private $messages = [];
    protected $downloadUrl = '';
    public static $downloadToDirectory    = 'static_assets/data/';
    protected $downloadedFileLocation = '';
    protected $fileName               = '';
    

    /**
     * Downloads the file if there isn't one yet or if isOutdated() returns true.
     * 
     * @param string $from
     * @param string $fileName if no filename isn't given it will be extracted from the URL
     */
    public function __construct($from, $fileName = null)
    {
        $this->downloadUrl = $from;
        $this->fileName = is_null($fileName) 
            ? basename($from)
            : $fileName;
        $this->downloadedFileLocation = self::$downloadToDirectory.$fileName;

        $this->downloadIfNeeded();
    }

    protected function downloadIfNeeded()
    {
        //If we don't have have the game master file or if it's outdated download it.
        if(!file_exists($this->downloadedFileLocation) || $this->isOutdated())
        {
            $this->downloadFile();
        }
        //else the file exists - do nothing.
    }

    /**
     * Checks if the file is considered outdated.
     *
     * @return boolean
     */
    protected function isOutdated()
    {
        /** Check if the file is older than 7 days */
        $dtNow = new DateTime();
        $dtFileLastModified = new DateTime();
        $fileLastModifiedTimeStamp = filemtime($this->downloadedFileLocation);
        $dtFileLastModified->setTimestamp($fileLastModifiedTimeStamp);
        $dateDifference = date_diff($dtFileLastModified, $dtNow);

        return intval($dateDifference->format('%d'))>= 7;
    }

    /**
     * Checks if PHP has write permission on self::downloadToDirectory
     * if it doesn't a message is written to $this->messages.
     *
     * @return boolean
     */
    protected function hasWritePermission()
    {
        if(!is_writable(self::$downloadToDirectory))
        {
            $processUserName = exec('whoami');
            $this->messages[] = "[Error Downloading $this->fileName] ".$processUserName." doesn't have write permissions on self::$downloadToDirectory. Try resolving this by running <code>chmod 775 self::$downloadToDirectory</code>";

            return false;
        }
        
        return true;
    }

    /**
     * Downloads the file
     * 
     * May write a message to $messages if lacking permissions
     *
     * @return void
     */
    protected function downloadFile()
    {        
        //Check if we have permissions to create files.
        if($this->hasWritePermission())
        {
            $fileContent = file_get_contents($this->downloadUrl);
            $this->messages[] = file_exists($this->downloadedFileLocation) 
                ? "Downloaded a new gamemaster file as the current one was over a week old."
                : "Downloaded $this->fileName to '$this->downloadedFileLocation' as you did not have one yet.";
            file_put_contents($this->downloadedFileLocation, $fileContent);
        }
    }

    /**
     * Get the value of messages
     */ 
    public function getMessages()
    {
        return $this->messages;
    }

    public function getContent()
    {
        return file_get_contents($this->downloadedFileLocation);
    }
}

?>