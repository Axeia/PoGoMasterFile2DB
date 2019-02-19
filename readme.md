# PoGoMasterFile2DB

The aim of this project is to turn the [gamemaster.json from ZeChrales/PoGoAssets/gamemaster](https://github.com/ZeChrales/PogoAssets/tree/master/gamemaster) into MySQL and Laravel PHP code.

It works as follows:
1) First it pulls in ZeChrales's gamemaster.json and if you have SVN installed on the server machine it pulls in a list of image file names.
2) GameMasterFileParser extracts the json and filename data to an array in PHP
3) index.php presents you all the data like in the image below and you can download the SQL or Laravel files

As a picture says a thousand words, after installing it this should show up in your browser
![Example Output](https://i.imgur.com/5I5bfKR.png)

## How to install
1) Just unzip in the root of a host folder of your server that runs PHP7+ 
2) Ensure static/assets/data has write permissions
3) Visit the URL of your host

## WebKit browsers are super slow loading index.php

For some reason I have yet to determine why index.php may take a minute to load in WebKit based browsers (tested in Chrome and Opera). 
I suggest you use Mozilla Firefox or even Microsoft Edge as either one one of those should render the page in about 3 seconds depending on the speed of your server and the PC visiting the page.

## History
This project is the successor in a sense to [https://github.com/Axeia/GenerateSQLiteOpenHelper](GenerateSQLiteOpenHelper). 
The difference is that I dropped that project as it was getting too messy trying to add Laravel support to it and there were too many steps.

With GenerateSQLiteOpenHelper the steps were roughly
1. *manual* Browse for gamemaster file and send it from the phone to the PC 
2. *manual* Use [pogo-game-master-decoder](https://github.com/apavlinovic/pogo-game-master-decoder)  to decode the file to protobuff format 
3. Through the use of Regular Expressions turn the protobuff file into something resembling JSON so that PHP's json_decode could parse it (data was definitely lost in this conversion as it did not handle arrays properly)
4. Extract the data from the JSON in PHP
5. Convert it to SQL
6. Have PHP-SQL-Parser extract the data once again (I intended to use the code for other conversions as well)
7. Convert the data into Android code (and later on Laravel code as well)

The workflow of this project is more along the lines of
1. The code downloads an already JSONified gamemaster file (so that that's step 1-3 covered, fully automated and without dataloss)
2. Extract data from the JSON in PHP
3. Output SQL or Laravel files

#Credits
[ZeChrales](https://github.com/ZeChrales/PogoAssets/tree/master/gamemaster) for providing the gamemaster.json 
The rest is my own data so far, contributions are welcomed.
[Ace](https://github.com/ajaxorg/ace-builds) editor for providing Syntax highlighting

#Optimizations
If you're looking to contribute. I could use help with figuring out why the page is so slow to load.
My theory is that something is overloading the main thread. 
I thought it might be the spellcheck but even with spellcheck="false" on the textareas it's still slow.
So the remaining theory now is that Javascript is being processed rather poorly, it could be the Ace Editor or it could be my own Javascript code.

#To-do list
* Split different outputs away from GameMasterFileParser it should just fill the array. Outputting SQL or Laravel code should be the task of dedicated classes (<abbr title="Keep it simple, stupid">KISS</abbr>)
* Finalize table creation code (and remove unneeded sql_templates)
* Show messages in index

#Alpha
*-Warning-* This software is very much a work in progress in alpha state. Things will change in breaking ways.