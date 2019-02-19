function FormatPage()
{
    this.allTextAreas = {
        'gamemaster': '',
        'sql-inserts': {},
        'laravel-seeders': {}
    };
    
    //Dom Nodes
    this.paneLeft           = null;
    this.paneRight          = null;
    this.paneMain           = null;
    this.selectDocumentTab  = null;
    this.selectGmFilter     = null;

    //Ace Editors
    this.editorLeft  = null;
    this.editorRight = null;

    //Dom Node List
    this.radioButtonsDocumentType = null;

    this.templateIds = new Array();

    this.init = function() 
    {        
        this.radioButtonsDocumentType = document.getElementsByName('show-right');    

        this.harvestData();
        this.emptypaneMain();
        this.createNewLayout();
        this.addAceEditors();
        this.addpaneLeftSizeChangelistener();
        this.addGameMasterFilterListener();
        this.addEventListenerForShowGameMaster();
        this.addEventListenersForDocumentType();
        this.addEventListenerForDocumentSelection();

        this.setpaneRightValue();
    };

    /**
     * Writes all relevant data on the page to this object.
     */
    this.harvestData = function()
    {
        var textAreas = document.getElementsByTagName('textarea');
        for(var i = 0; i < textAreas.length; i++)
        {
            if(textAreas[i].id && textAreas[i].id === 'gamemasterfile')
            {
                this.allTextAreas['gamemaster'] = textAreas[i].value;
            }
            
            if(textAreas[i].classList.contains('laravel-seeder'))
            {
                this.allTextAreas['laravel-seeders'][textAreas[i].id.substring(15)] = textAreas[i].value;
            }
            
            if(textAreas[i].classList.contains('sql-insert'))
            {
                this.allTextAreas['sql-inserts'][textAreas[i].id.substring(11)] = textAreas[i].value;
            }
        }

        //Find all 'unique' templates in the gamemaster json.
        var masterGameFileContent = JSON.parse(this.allTextAreas['gamemaster']);
        for(var itemTemplateKey in masterGameFileContent.itemTemplates)
        {
            var itemTemplate = masterGameFileContent.itemTemplates[itemTemplateKey];
            var templateIdPart = this.getRelevantIdPart(itemTemplate.templateId);

            //Collect all unique values
            if(!this.templateIds.includes(templateIdPart))
            {
                this.templateIds.push(templateIdPart);
            }
        }
    }

    this.emptypaneMain = function()
    {
        this.paneMain = document.getElementById('main');
        while(this.paneMain.lastChild)
        {
            this.paneMain.removeChild(this.paneMain.lastChild);
        }
    }

    this.createNewLayout = function()
    {
        //Left pane
        this.paneLeft    = document.createElement('div');
        this.paneLeft.id = 'left-pane';

        if(localStorage.getItem("pane-left-width") !== null)
            this.paneLeft.style.width = localStorage.getItem('pane-left-width')+'%';

        this.paneMain.appendChild(this.paneLeft);

        var leftTextArea = document.createElement('textarea');
        leftTextArea.id = 'left-text-area';
        leftTextArea.value = this.allTextAreas.gamemaster;
        this.paneLeft.appendChild(leftTextArea);

        var ulpaneLeft = document.createElement('ul');
        ulpaneLeft.classList.add('tab-bar');

        var leftLi = document.createElement('li');
        leftLi.appendChild(document.createTextNode('gamemaster.json'));
        ulpaneLeft.appendChild(leftLi);
        this.paneLeft.appendChild(ulpaneLeft);

        //Left filter dropdown
        this.selectGmFilter = document.createElement('select');
        var clearSelectGmFilterOtpion = document.createElement('option');
        clearSelectGmFilterOtpion.value = '';
        clearSelectGmFilterOtpion.innerText = 'Filter on:';
        this.selectGmFilter.appendChild(clearSelectGmFilterOtpion);
        for(var i = 0; i < this.templateIds.length; i++)
        {
            var filterOption = document.createElement('option');
            filterOption.innerText = this.templateIds[i];
            this.selectGmFilter.appendChild(filterOption);
        }
        var leftLi2 = document.createElement('li');
        leftLi2.appendChild(this.selectGmFilter);
        leftLi2.id = 'filter';
        ulpaneLeft.appendChild(leftLi2);

        //Right pane
        this.paneRight = document.createElement('div');
        this.paneRight.id = 'right-pane';        
        this.paneMain.appendChild(this.paneRight);
        var rightTextArea = document.createElement('textarea');
        rightTextArea.id = 'right-text-area';
        this.paneRight.appendChild(rightTextArea);
        
        var ulpaneRight = document.createElement('ul');
        ulpaneRight.classList.add('tab-bar');

        var rightLi = document.createElement('li');
        rightLi.id = 'right-li';
        ulpaneRight.appendChild(rightLi);
        this.paneRight.appendChild(ulpaneRight);
        
        this.selectDocumentTab = document.createElement('select');
        this.selectDocumentTab.id = 'select-document';
        document.getElementById('right-li').innerText = 'File: ';
        document.getElementById('right-li').appendChild(this.selectDocumentTab);
        if(localStorage.getItem('document') !== null)
        {
            this.selectDocumentTab.value = localStorage.getItem('document-title');
        }
        this.createselectDocumentTabOptions();

        
        if(localStorage.getItem('left-pane') !== null)
        {
            document.getElementById('show-gamemaster').checked = 
                localStorage.getItem('left-pane') === 'true';
            document.getElementById('left-pane').style.display = 
                localStorage.getItem('left-pane') === 'true' ? 'block' : 'none';
        }
    }

    this.addAceEditors = function()
    {        
        this.editorLeft = ace.edit("left-text-area");
        this.editorLeft.setOption("showPrintMargin", false)
        this.editorLeft.setReadOnly(true);
        this.editorLeft.setTheme("ace/theme/ambiance");
        this.editorLeft.session.setUseWrapMode(true);
        this.editorLeft.session.setMode("ace/mode/json");
        
        this.editorRight = ace.edit("right-text-area");
        this.editorRight.setOption("showPrintMargin", false)
        this.editorRight.setReadOnly(true);
        this.editorRight.setTheme("ace/theme/ambiance");

        this.setRightEditorMode();
    }

    this.setRightEditorMode = function()
    {        
        if(this.whatsRight() === 'laravel-seeders')
            this.editorRight.session.setMode("ace/mode/php");
        else
            this.editorRight.session.setMode("ace/mode/mysql");
    }

    this.addGameMasterFilterListener = function()
    {
        var _this = this;
        this.selectGmFilter.addEventListener('change', function(){
            var masterGameFileCopy = JSON.parse(_this.allTextAreas['gamemaster']);
            var masterfileCopyText = ''; 
            if(this.value !== '')
            {
                for(var itemTemplateKey in masterGameFileCopy.itemTemplates)
                {
                    var itemTemplate = masterGameFileCopy.itemTemplates[itemTemplateKey];              
                    if(_this.getRelevantIdPart(itemTemplate.templateId) !== this.value)
                    {
                        delete masterGameFileCopy.itemTemplates[itemTemplateKey];
                    }
                }

                var regexRemoveNull = /[ \t]*null,[\n\r]/gm;
                masterfileCopyText = JSON.stringify(masterGameFileCopy, null, 2).replace(regexRemoveNull, '');
            }
            else
            {
                masterfileCopyText = JSON.stringify(masterGameFileCopy, null, 2)
            }

            _this.editorLeft.setValue(masterfileCopyText);
            _this.editorLeft.clearSelection();
        });
    }

    this.addpaneLeftSizeChangelistener = function()
    {
        //paneLeft sizing magic.
        var _this = this;
        new MutationObserver(function() 
        {
            //Calculate percentage width
            var paneLeftWidthPercentage = _this.paneLeft.offsetWidth / _this.paneMain.offsetWidth * 100;
            //Save percentage to localStorage
            window.localStorage.setItem('left-pane-width', paneLeftWidthPercentage);
        }).observe(this.paneLeft, {
            attributes: true, attributeFilter: [ "style" ]
        });
    }

    this.addEventListenerForShowGameMaster = function()
    {
        var _this = this;
        document.getElementById('show-gamemaster').addEventListener('change', function(){
            _this.paneLeft.style.display = this.checked ? 'block' : 'none';
            localStorage.setItem('left-pane', this.checked);
        });
    }

    this.addEventListenersForDocumentType = function()
    {
        var _this = this;
        for(var i = 0; i < this.radioButtonsDocumentType.length; i++)
        {
            _this.radioButtonsDocumentType[i].addEventListener('change', function(){
                _this.createselectDocumentTabOptions();
                _this.setpaneRightValue();
            });
        }
    }

    this.addEventListenerForDocumentSelection = function()
    {
        var _this = this;
        this.selectDocumentTab.addEventListener('change', function(){ 
            _this.setpaneRightValue(); 
            //Save value to localStorage on change.
            localStorage.setItem('document-title', this.value); 
        });

    }

    this.setpaneRightValue = function()
    {
        var selectionType = this.whatsRight();
        var documentTitle = document.getElementById('select-document').value;
        var documentContent = this.allTextAreas[selectionType][documentTitle];
        this.editorRight.setValue(documentContent);
        this.editorRight.clearSelection();
        this.setRightEditorMode();
    }    

    this.getFileName = function(type, name)
    {
        if(type === 'sql-inserts')
            return name+'.sql';
        else
            return camelCaseString(name)+'Seeder.php';
    }

    this.whatsRight = function()
    {
        var rbs = document.getElementsByName('show-right');
        for(var i = 0; i < rbs.length; i++)
        {
            if(rbs[i].checked)
                return rbs[i].id;
        }
    }
   

    this.camelCaseString = function(str)
    {
        var returnString = '';
        var strParts = str.split('_');
        for(var i = 0; i < strParts.length; i++)
        {
            returnString += this.capitalizeFirstLetter(strParts[i]);
        }

        return returnString;
    }

    this.capitalizeFirstLetter = function(string) 
    {
        return string.charAt(0).toUpperCase() + string.slice(1);
    }

    this.getFileName = function(name)
    {
        switch(this.whatsRight())
        {
            case 'sql-inserts':
                return name+'.sql'
            default:            
                return this.camelCaseString(name)+'Seeder.php';
        }
    }

    this.createselectDocumentTabOptions = function()
    {
        //Remove all options
        var selectDocument = document.getElementById('select-document');
        while(selectDocument.lastChild)
        {
            selectDocument.removeChild(selectDocument.lastChild);
        }

        //Create new options
        var what = this.whatsRight();

        Object.keys(this.allTextAreas[what]).forEach(key => {
            var option       = document.createElement('option');
            option.value     = key;
            option.innerText = this.getFileName(key);
            selectDocument.appendChild(option);

            if(localStorage.getItem('document-title') === key)
            {
                selectDocument.value = key;
            }
        });
    }

    this.getRelevantIdPart = function(itemTemplateId)
    {
        var templateIdParts = itemTemplateId.split('_');
        var templateIdPart = templateIdParts[0];

        //If the first bit of the template_id is a pokedex number, get the value behind it instead.
        var regex = /V[0-9]{4}/g;
        var pokemonNumberMatch = templateIdPart.match(regex);
        return pokemonNumberMatch instanceof Array
            ? templateIdParts[1]
            : templateIdParts[0];
    }
}

var formatPage = new FormatPage();
formatPage.init();