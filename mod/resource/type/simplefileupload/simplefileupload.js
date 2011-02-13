// This file is part of SimpleFileUpload
//
// SimpleFileUpload provides a simpler mechanism for adding file resources to
// a Moodle Course and link them on the course page than the standard Moodle mechanism.
//
// SimpleFileUpload is (C) Copyright 2010 by John Ennew and Steve Coppin
// of the University of Kent, Canterbury, UK http://www.kent.ac.uk/
// Contact info: John Ennew; J.Ennew@kent.ac.uk
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.

/**
 * This is the Javascript layer which adds ajax functionality to 
 * the file upload form so that the list of current resource items
 * in the section is displayed and the save button submits the form
 * in the background so you can quickly submit another file
 * 
 * Makes use of the AJAX file upload library from webtoolkit:
 * http://www.webtoolkit.info/ajax-file-upload.html
 *
 * @package simplefileupload
 * @copyright 2010 John Ennew, Steve Coppin
 * @copyrigth 2010 University of Kent, Canterbury, UK http://www.kent.ac.uk
 * @author John Ennew
 *
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
var post_url = '../mod/resource/type/simplefileupload/rest.php';
var controls = null;

// When the DOM is ready, run the setup function
YAHOO.util.Event.onDOMReady(simplefileupload_setupJavascriptEnabledItems);

/**
 *  Setup Function
 *  simplefileupload_setupJavascriptEnabledItems
 *  This function adds the javascript elements to the SimpleFileUpload form
 */
function simplefileupload_setupJavascriptEnabledItems() {

    // hide Moodle elements
    var simplefileupload_cms = document.getElementById("modstandardelshdr");
    var simplefileupload_save_and_display_button = document.getElementById("id_submitbutton");
    var cancel_button = document.getElementById("id_cancel");
    var save_and_return_button = document.getElementById("id_submitbutton2");

    if (simplefileupload_cms!=null) {
        simplefileupload_cms.style.display = "none";
    }

    if (simplefileupload_save_and_display_button!=null) {
        simplefileupload_save_and_display_button.style.display = "none";
    }

    
    if (save_and_return_button!=null) {
        save_and_return_button.style.display = "none";
    }

    // create a SimpleFileUploadObject called controls
    var fileinput = document.getElementById("id_simplefileupload_FILE");
    var nameinput = document.getElementById("id_name");
    var section = document.getElementsByName("section")[0];
    var course = document.getElementsByName("course")[0];
    if (fileinput!=null && nameinput!=null) {
        controls = new SimpleFileUpload(fileinput, nameinput, section.value, course.value); // instantiate the controls object - there is only 1
    } else throw "Configuration fault";

    if (cancel_button!=null) {
        cancel_button.setAttribute("value", "Close");
        cancel_button.onclick = function() {
            if (fileinput.value.length==0) {
                skipClientValidation = true;
                return true;
            } else {
                var answer = confirm("You have not saved the file '" + fileinput.value + "' to your module. Click cancel to go back to save or OK to continue to your module");
                if (answer) {
                    skipClientValidation = true;
                    return true;
                } else {
                    return false;
                }
            }
        }
    }


}

function SimpleFileUpload(fileinput, nameinput, sectionnumber, courseid) {
    this.fileinput = fileinput;
    this.nameinput = nameinput;
    this.sectionnumber = sectionnumber;
    this.courseid = courseid;
    this.form = document.getElementById('mform1');
    this.oldformaction = this.form.action;
    this.container = this.createContainer();
    this.saveButton = this.createSaveButton();
    this.spinner = this.createAjaxSpinner();
    this.filesInSectionViewer = this.createFilesInSectionViewer();
    this.setupFileinput(); 
}        

SimpleFileUpload.prototype.createContainer = function () {
    var container = document.createElement('div');
    container.setAttribute('class', 'simplefileuploadcontrol');
    var filecontainer = this.fileinput.parentNode.parentNode;
    var namecontainer = this.nameinput.parentNode.parentNode;
    var toplevel = filecontainer.parentNode;

    toplevel.removeChild(filecontainer);
    toplevel.removeChild(namecontainer);
    container.appendChild(filecontainer);
    container.appendChild(namecontainer);
    toplevel.appendChild(container);
    return container;
}

SimpleFileUpload.prototype.createSaveButton = function() {
    var addExtraBut = document.createElement('input');
    addExtraBut.setAttribute('type', 'button');
    addExtraBut.setAttribute("name", "addExtraFiles");
    addExtraBut.setAttribute('value','Save file to module');
    this.nameinput.parentNode.appendChild(addExtraBut);
    
    var yui_save_but = new YAHOO.util.Element(addExtraBut);
    yui_save_but.controls = this;
    yui_save_but.on('click', function(e) {

        var callback = new Object();
        callback.onStart = function() { 
                                if (controls.fileinput.length==0) return false;
                                controls.form.submit();
                                controls.fileinput.disabled = true;
                                controls.nameinput.disabled = true;
                                controls.saveButton.disabled = true;
                                controls.saveButton.parentNode.replaceChild(controls.spinner, controls.saveButton);
                                return true;
                           };
        callback.onComplete = function(response) {
                    controls.form.setAttribute('target', "_self");

                    if (controls.nameinput.value.length>0 && controls.fileinput.value.length>0) {
                        var opt = document.createElement('li');
                        opt.appendChild(document.createTextNode(controls.nameinput.value));
                        controls.filesInSectionViewer.appendChild(opt);
                        controls.nameinput.value = '';
                        controls.fileinput.form.reset();
                        controls.fileinput.value = '';                        
                        controls.filesInSectionViewer.scrollTop = controls.filesInSectionViewer.scrollHeight;
                    }
                    controls.fileinput.disabled = false;
                    controls.nameinput.disabled = false;
                    controls.saveButton.disabled = false;
                    controls.spinner.parentNode.replaceChild(controls.saveButton, controls.spinner);
                    controls.fileinput.focus();
        };

        AIM.submit(this.controls.form, callback);                       
    });
    
    return addExtraBut;
}

SimpleFileUpload.prototype.createAjaxSpinner = function() {
    var spinner = document.createElement('img');
    spinner.name = 'ajax_loader';
    spinner.alt = 'Loading data from server, please wait...';
    spinner.src = '../mod/resource/type/simplefileupload/ajax-loader.gif';
    spinner.width="18";
    spinner.height="18";
    return spinner;
}

SimpleFileUpload.prototype.createFilesInSectionViewer = function() {
    var filesinsection = document.createElement('ul');
    filesinsection.setAttribute('class','simplefileuploadfilesinsection');
    this.container.parentNode.appendChild(filesinsection);

    var opt = document.createElement('li');
    opt.setAttribute('class', 'firstElement');
    opt.appendChild(document.createTextNode('Files already in section '+this.sectionnumber + '...'));
    filesinsection.appendChild(opt);

    // AJAX: Get the current list of files in this section
    var callback = {
        success: function(o) {
            
            var response = YAHOO.lang.JSON.parse(o.responseText).Response;
            for (var i=0; i<response.length; i++) {
                var opt = document.createElement('li');
                opt.appendChild(document.createTextNode(response[i].reference));
                o.argument[0].appendChild(opt);
            }

        },
        failure: function(o) {
        },
        argument: [filesinsection]
    }

    YAHOO.util.Connect.asyncRequest('POST', post_url, callback, 'action=read&courseid='+this.courseid+'&sectionnumber='+this.sectionnumber);
    
    return filesinsection;
}

SimpleFileUpload.prototype.setupFileinput = function() {
    YAHOO.util.Event.addListener(this.fileinput, "change", function(e, me) {
        if (me.fileinput.value.length>0) {
            me.nameinput.value = me.generateLabel();
        }
    }, this);
}

SimpleFileUpload.prototype.generateLabel = function() {
    var x = this.fileinput.value; 
    x = x.replace(/\\/g, '/');
    var temp = x.split('/');
    x = temp[temp.length-1]; // last element is the file name

    // remove the file extension
    x = x.substring(0, x.lastIndexOf('.'));

    // replace underscores and hyphens with spaces
    x = x.replace(/_/g, " ");
    x = x.replace(/-/g, " ");
    x = x.replace(/   /g, " ");
    x = x.replace(/  /g, " ");        

    // capitalise the first letter and lower case the rest
    var letter = x.substr(0,1);
    x = letter.toUpperCase() + x.substr(1).toLowerCase();

    return x; 
}

/**
*
*  AJAX IFRAME METHOD (AIM)
*  http://www.webtoolkit.info/
*
**/
 
AIM = {
 
	frame : function(c) {
        
		var n = 'f' + Math.floor(Math.random() * 99999);
		var d = document.createElement('DIV');
		d.innerHTML = '<iframe style="display:none" src="about:blank" id="'+n+'" name="'+n+'" onload="AIM.loaded(\''+n+'\')"></iframe>';
		document.body.appendChild(d);
 
		var i = document.getElementById(n);
		if (c && typeof(c.onComplete) == 'function') {
			i.onComplete = c.onComplete;
		}
        
		return n;
	},
 
	form : function(f, name) {
		f.setAttribute('target', name);
	},
 
	submit : function(f, c) {
		AIM.form(f, AIM.frame(c));
		if (c && typeof(c.onStart) == 'function') {
			return c.onStart();
		} else {
			return true;
		}
	},
 
	loaded : function(id) {
		var i = document.getElementById(id);
		if (i.contentDocument) {
			var d = i.contentDocument;
		} else if (i.contentWindow) {
			var d = i.contentWindow.document;
		} else {
			var d = window.frames[id].document;
		}
		if (d.location.href == "about:blank") {
			return;
		}
 
		if (typeof(i.onComplete) == 'function') {
			i.onComplete(d.body.innerHTML);
		}
	}
 
}
