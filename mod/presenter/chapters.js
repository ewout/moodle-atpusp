/*
This file is part of the Presenter Activity Module for Moodle

The Presenter Activity Module for Moodle software package is Copyright © 2008 onwards NetSapiensis AB and is provided under the terms
of the GNU GENERAL PUBLIC LICENSE Version 3 (GPL). This program is free software: you can redistribute it and/or modify it under the terms
of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any
later version.
This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY
or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with this program. If not, see <http://www.gnu.org/licenses/>.

The Presenter Activity Module for Moodle includes Flowplayer free version. For more information on Flowplayer see http://www.flowplayer.org

The Flowplayer Free version is released under the GNU GENERAL PUBLIC LICENSE Version 3 (GPL).
The GPL requires that you not remove the Flowplayer copyright notices from the user interface. See section 5.d below.
Commercial licenses are available. The commercial player version does not require any Flowplayer notices or texts and also provides some
additional features.

ADDITIONAL TERM per GPL Section 7 for Flowplayer
If you convey this program (or any modifications of it) and assume contractual liability for the program to recipients of it, you agree to
indemnify Flowplayer, Ltd. for any liability that those contractual assumptions impose on Flowplayer, Ltd.

Except as expressly provided herein, no trademark rights are granted in any trademarks of Flowplayer, Ltd. Licensees are granted a limited,
non-exclusive right to use the mark Flowplayer and the Flowplayer logos in connection with unmodified copies of the Program and the copyright
notices required by section 5.d of the GPL license. For the purposes of this limited trademark license grant, customizing the Flowplayer by
skinning, scripting, or including PlugIns provided by Flowplayer, Ltd. is not considered modifying the Program.

Licensees that do modify the Program, taking advantage of the open-source license, may not use the Flowplayer mark or Flowplayer logos and must
change the fullscreen notice (and the non-fullscreen notice, if that option is enabled), the copyright notice in the dialog box, and the notice
on the Canvas as follows:

the full screen (and non-fullscreen equivalent, if activated) noticeshould read: "Based on Flowplayer source code"; in the context menu
(right-click menu), the link to "About Flowplayer free version #.#.#" can remain. The copyright notice can remain, but must be supplemented
with an additional notice, stating that the licensee modified the Flowplayer. A suitable notice might read
"Flowplayer Source code modified by ModOrg 2009"; for the canvas, the notice should read "Based on Flowplayer source code".
In addition, licensees that modify the Program must give the modified Program a new name that is not confusingly similar to Flowplayer
and may not distribute it under the name Flowplayer.

This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the
Free Software Foundation, either version 3 of the License, or (at your option) any later version. This program is distributed in the hope that
it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
See the GNU General Public License for more details. You should have received a copy of the GNU General Public License along with this program.
If not, see <http://www.gnu.org/licenses/>.
 */
ch = getElementsByClassName(document, "fieldset", "clearfix");
x = 0;
chapters = new Array();
var injectedFiles = new Array();
for (i = 0; i < ch.length; i++) {
	kk = ch[i].id.split("_");
	if (kk[0] == 'chapter') {
		injectedFiles[x] = document.getElementById('id_video_link_' + kk[1] + '_value').value;
		chapters[x] = ch[i];
		x++;
	}
}
radio = getElementsByClassName(document, 'input', 'radioBut');
nr = radio.length;
for (i = 0; i < nr; i++) {
	radio[i].id = radio[i].id + i;
	radio[i].nextSibling.setAttribute('for', radio[i].id);
}
b = false;
for (i = 0; i < nr; i++) {
	if ((i % 6 == 0 && i != 0) || i == nr-1) {
		if (b == false) {
			if (i != nr-1) {
				radio[i-6].checked = true;
			} else {
				radio[nr-6].checked = true;
			}
		}
		b = false;
	}
	if (radio[i].checked == true) {
		b = true;
	}	
}

for (i = 0; i < nr; i++) {
	if (radio[i].checked == true) {
		enableDisable(radio[i]);
	}
}

//order_id
order = getElementsByClassName(document, 'input', 'order_id');

for (i = 0; i < order.length; i++) {
	aux = i + 1;
	order[i].setAttribute('value', aux);
}

del = getElementsByClassName(document, 'input', 'delete');

for (i = 0; i < del.length; i++) {
	if (del[i].getAttribute('value') == 'true') {
		document.getElementById('chapter_' + i).style.display = 'none';	
	}
}

//show only/show all stuff
elems = getElementsByClassName(document, "input", "showOnlyThis");
b = false;
for (i = 0; i < elems.length; i++) {
	if (elems[i].value == 'true') {
		elems[i].value = 'false';
		b = true;
		break;
	}	
}

if (b) {
	for (i = 0; i < chapters.length - 1; i++) {
		kk = chapters[i].id.split("_");
		chapters[i].style.display = 'none';	
	}
	elems[elems.length - 1].value = 'true';
	y = getYpos(chapters[chapters.length - 1]);
	window.scrollTo(0, y + 100);
	imgs = getElementsByClassName(document, 'input', 'flag');
	imgs[imgs.length - 1].style.display = 'none';
	imgs[imgs.length - 1].nextSibling.style.display = 'block';
}

function enableDisable(radioBut)
{
	ids = radioBut.id.split("_radio");
	id = parseInt(ids[1]/6);
	switch (radioBut.value) {
		case '3':
		case '4':
			document.getElementById('id_slide_image_' + id + '_value').setAttribute('disabled', "disabled");
			document.getElementById('id_slide_image_' + id + '_popup').disabled = "disabled";
			document.getElementById('id_video_link_' + id + '_value').disabled = "";
			document.getElementById('id_video_link_' + id + '_popup').disabled = "";
			document.getElementById('id_video_start_' + id).disabled = "";
			document.getElementById('id_video_end_' + id).disabled = "";
			document.getElementById('id_audio_track_' + id + '_value').disabled = true;
			document.getElementById('id_audio_track_' + id + '_popup').disabled = true;
			/*document.getElementById('id_audio_start_' + id).disabled = true;*/
			document.getElementById('id_audio_end_' + id).disabled = true;
			break;
		case '5':
		case '6':
			document.getElementById('id_slide_image_' + id + '_value').disabled = false;
			document.getElementById('id_slide_image_' + id + '_popup').disabled = false;
			document.getElementById('id_video_link_' + id + '_value').disabled = true;
			document.getElementById('id_video_link_' + id + '_popup').disabled = true;
			document.getElementById('id_video_start_' + id).disabled = "disabled";
			document.getElementById('id_video_end_' + id).disabled = "disabled";
			document.getElementById('id_audio_track_' + id + '_value').disabled = false;
			document.getElementById('id_audio_track_' + id + '_popup').disabled = false;
			/*document.getElementById('id_audio_start_' + id).disabled = false;*/
			document.getElementById('id_audio_end_' + id).disabled = false;
			break;
		case '1':
		case '2':
			document.getElementById('id_video_start_' + id).disabled = "";
			document.getElementById('id_video_end_' + id).disabled = "";
			document.getElementById('id_slide_image_' + id + '_value').disabled = false;
			document.getElementById('id_slide_image_' + id + '_popup').disabled = false;
			document.getElementById('id_video_link_' + id + '_value').disabled = "";
			document.getElementById('id_video_link_' + id + '_popup').disabled = "";
			document.getElementById('id_audio_track_' + id + '_value').disabled = true;
			document.getElementById('id_audio_track_' + id + '_popup').disabled = true;
			/*document.getElementById('id_audio_start_' + id).disabled = true;*/
			document.getElementById('id_audio_end_' + id).disabled = true;
	}		
}

function remove(elem)
{
	par = elem.parentNode.parentNode;
	del = getElementsByClassName(document, 'input', 'delete');
	ids = par.id.split('_');
	var goodID = parseInt(ids[1]);
	for (i = 0; i < del.length; i++) {
		if (i == goodID) {
			par.style.display = 'none';
			del[i].setAttribute('value', 'true');
		}
	}
}

function reShowChapters(el)
{
	for (i = 0; i < chapters.length; i++)
		chapters[i].style.display = 'none';
	nam = el.name;
	eles = nam.split('[');
	ids = eles[1].split(']');
	id = ids[0];
	document.getElementById('chapter_' + id).style.display = 'block';
	imgs = getElementsByClassName(document, 'input', 'flag');
	for (i = 0; i < imgs.length; i++) {
		imgs[i].style.display = 'block';
		imgs[i].nextSibling.style.display = 'none';
	}
	imgs[id].style.display = 'none';
	imgs[id].nextSibling.style.display = 'block';
}

function moveUp(elem)
{
	//g = id of the chapter from above
	par = elem.parentNode.parentNode;
	ids = par.id.split('_');
	var goodID = parseInt(ids[1]);
	g = goodID;
	del = getElementsByClassName(document, 'input', 'delete');
	do {
		g--;
		if (g == -1) break;
	} while (del[g].value == 'true' && g >= 0);

	if (document.getElementById('chapter_' + g)) {
	
		//if showOnlyThis Chapter
		
		elems = getElementsByClassName(document, "input", "showOnlyThis");
		if (elems[goodID].value == 'true') {
			elems[goodID].value = 'false';
			elems[g].value = 'true';
			reShowChapters(elems[g]);
		}
		
		ind = 0;ind1 = 0;
		for (i = 0; i < 6; i++) {
			id = 6 * goodID + i;id1 = 6 * g + i;
			if (document.getElementById('id_radio' + id1).checked) {
				ind = i;
			}
			if (document.getElementById('id_radio' + id).checked) {
				ind1 = i;
			}
		}
		a = (6*goodID) + ind;
		document.getElementById('id_radio' + a).checked = 'true';
		a = (6*g) + ind1;
		document.getElementById('id_radio' + a).checked = 'true';
	
		aux = document.getElementById('id_chapter_name_' + g).value;
		document.getElementById('id_chapter_name_' + g).value = document.getElementById('id_chapter_name_' + goodID).value;
		document.getElementById('id_chapter_name_' + goodID).value = aux;
		
		aux = document.getElementById('id_video_link_' + g + '_value').value;
		document.getElementById('id_video_link_' + g + '_value').value = document.getElementById('id_video_link_' + goodID + '_value').value;
		document.getElementById('id_video_link_' + goodID + '_value').value = aux;
		
		aux = document.getElementById('id_video_start_' + g).value;
		document.getElementById('id_video_start_' + g).value = document.getElementById('id_video_start_' + goodID).value;
		document.getElementById('id_video_start_' + goodID).value = aux;
		
		aux = document.getElementById('id_video_end_' + g).value;
		document.getElementById('id_video_end_' + g).value = document.getElementById('id_video_end_' + goodID).value;
		document.getElementById('id_video_end_' + goodID).value = aux;
		
		aux = document.getElementById('id_audio_track_' + g + '_value').value;
		document.getElementById('id_audio_track_' + g + '_value').value = document.getElementById('id_audio_track_' + goodID + '_value').value;
		document.getElementById('id_audio_track_' + goodID + '_value').value = aux;
		
		/*aux = document.getElementById('id_audio_start_' + g).value;
		document.getElementById('id_audio_start_' + g).value = document.getElementById('id_audio_start_' + goodID).value;
		document.getElementById('id_audio_start_' + goodID).value = aux;*/
		
		aux = document.getElementById('id_audio_end_' + g).value;
		document.getElementById('id_audio_end_' + g).value = document.getElementById('id_audio_end_' + goodID).value;
		document.getElementById('id_audio_end_' + goodID).value = aux;
		
		aux = document.getElementById('id_slide_image_' + g + '_value').value;
		document.getElementById('id_slide_image_' + g + '_value').value = document.getElementById('id_slide_image_' + goodID + '_value').value;
		document.getElementById('id_slide_image_' + goodID + '_value').value = aux;
		
		var lower = 'editor_'+hex_md5('summary[' + g + ']');
		var upper = 'editor_'+hex_md5('summary[' + goodID + ']');
		
		try {
			aux1 = eval(lower).getHTML();
			eval(lower).setHTML(eval(upper).getHTML());
			eval(upper).setHTML(aux1);
		} catch (e) {
			aux1 = document.getElementById('id_summary_' + g).value;
			document.getElementById('id_summary_' + g).value = document.getElementById('id_summary_' + goodID).value; 
			document.getElementById('id_summary_' + goodID).value = aux1;
		}
	}
}

function moveDown(elem)
{
	par = elem.parentNode.parentNode;
	ids = par.id.split('_');
	var goodID = parseInt(ids[1]);
	g = goodID + 1;
	del = getElementsByClassName(document, 'input', 'delete');
	
	while (g < del.length && del[g].value == 'true') {
		g++;
	}
	
	if (document.getElementById('chapter_' + g)) {
	
		//show only this
		elems = getElementsByClassName(document, "input", "showOnlyThis");
		if (elems[goodID].value == 'true') {
			elems[goodID].value = 'false';
			elems[g].value = 'true';
			reShowChapters(elems[g]);
		}
	
		//radio buttons
		ind = 0;ind1 = 0;
		for (i = 0; i < 6; i++) {
			id = 6 * goodID + i;id1 = 6 * g + i;
			if (document.getElementById('id_radio' + id1).checked) {
				ind = i;
			}
			if (document.getElementById('id_radio' + id).checked) {
				ind1 = i;
			}
		}
		a = (6*goodID) + ind;
		document.getElementById('id_radio' + a).checked = 'true';
		a = (6*g) + ind1;
		document.getElementById('id_radio' + a).checked = 'true';
		
		aux = document.getElementById('id_chapter_name_' + g).value;
		document.getElementById('id_chapter_name_' + g).value = document.getElementById('id_chapter_name_' + goodID).value;
		document.getElementById('id_chapter_name_' + goodID).value = aux;
		
		aux = document.getElementById('id_video_link_' + g + '_value').value;
		document.getElementById('id_video_link_' + g + '_value').value = document.getElementById('id_video_link_' + goodID + '_value').value;
		document.getElementById('id_video_link_' + goodID + '_value').value = aux;
		
		aux = document.getElementById('id_video_start_' + g).value;
		document.getElementById('id_video_start_' + g).value = document.getElementById('id_video_start_' + goodID).value;
		document.getElementById('id_video_start_' + goodID).value = aux;
		
		aux = document.getElementById('id_video_end_' + g).value;
		document.getElementById('id_video_end_' + g).value = document.getElementById('id_video_end_' + goodID).value;
		document.getElementById('id_video_end_' + goodID).value = aux;
		
		aux = document.getElementById('id_audio_track_' + g + '_value').value;
		document.getElementById('id_audio_track_' + g + '_value').value = document.getElementById('id_audio_track_' + goodID + '_value').value;
		document.getElementById('id_audio_track_' + goodID + '_value').value = aux;
		
		/*aux = document.getElementById('id_audio_start_' + g).value;
		document.getElementById('id_audio_start_' + g).value = document.getElementById('id_audio_start_' + goodID).value;
		document.getElementById('id_audio_start_' + goodID).value = aux;*/
		
		aux = document.getElementById('id_audio_end_' + g).value;
		document.getElementById('id_audio_end_' + g).value = document.getElementById('id_audio_end_' + goodID).value;
		document.getElementById('id_audio_end_' + goodID).value = aux;
		
		aux = document.getElementById('id_slide_image_' + g + '_value').value;
		document.getElementById('id_slide_image_' + g + '_value').value = document.getElementById('id_slide_image_' + goodID + '_value').value;
		document.getElementById('id_slide_image_' + goodID + '_value').value = aux;
		
		var lower = 'editor_'+hex_md5('summary[' + g + ']');
		var upper = 'editor_'+hex_md5('summary[' + goodID + ']');

		try {
			aux1 = eval(lower).getHTML();
			eval(lower).setHTML(eval(upper).getHTML());
			eval(upper).setHTML(aux1);
		} catch (e) {
			aux1 = document.getElementById('id_summary_' + g).value;
			document.getElementById('id_summary_' + g).value = document.getElementById('id_summary_' + goodID).value; 
			document.getElementById('id_summary_' + goodID).value = aux1;
		}
	}
	return false;
}

function checkValues()
{
	for (i = 0; i < chapters.length; i++) {
		kk = chapters[i].id.split("_");
		if (kk[0] == 'chapter' && chapters[i].style.display != 'none') {
			var variable = 'editor_' + hex_md5('summary[' + kk[1] + ']');
			try {
				document.getElementById('id_summary_' + kk[1]).innerHTML = eval(variable).getHTML();
			} catch (e) {
			}
			
			try {
				document.getElementById('id_summary_' + kk[1]).value = eval(variable).getHTML();
			} catch (e) {
			}
		}
	}
	b = true;
	if (skipClientValidation == false) {

        var width1 = document.getElementById("id_presentation_width1");
        var height1 = document.getElementById("id_presentation_height1");
        var player_width1 = document.getElementById("id_player_width1");
        var player_height1 = document.getElementById("id_player_height1");

        var width2 = document.getElementById("id_presentation_width2");
        var height2 = document.getElementById("id_presentation_height2");
        var player_width2 = document.getElementById("id_player_width2");
        var player_height2 = document.getElementById("id_player_height2");

        w1 = parseInt(width1.value);
        h1 = parseInt(height1.value);
        pw1 = parseInt(player_width1.value);
        ph1 = parseInt(player_height1.value);

        w2 = parseInt(width2.value);
        h2 = parseInt(height2.value);
        pw2 = parseInt(player_width2.value);
        ph2 = parseInt(player_height2.value);

        if (ph1 > h1) {
            player_height1.style.border = '1px solid red';
            y = getYpos(player_height1);
            window.scroll(0, y - 200);
            alert('Player height must be smaller than the presentation height');
            return false;
        }

        if (ph2 > h2) {
            player_height2.style.border = '1px solid red';
            y = getYpos(player_height2);
            window.scroll(0, y - 200);
            alert('Player height must be smaller than the presentation height');
            return false;
        }

        if (pw1 > w1) {
            player_width1.style.border = '1px solid red';
            y = getYpos(player_width1);
            window.scroll(0, y - 200);
            alert('Player width must be smaller than the presentation height');
            return false;
        }

        if (pw2 > w2) {
            player_width2.style.border = '1px solid red';
            y = getYpos(player_width2);
            window.scroll(0, y - 200);
            alert('Player width must be smaller than the presentation height');
            return false;
        }

		names = getElementsByClassName(document, "input", "names");
		for (i = 0; i < names.length; i++) {
			if (names[i].parentNode.parentNode.parentNode.parentNode.style.display != 'none') {
				if (names[i].value == '') {
					names[i].style.border = '1px solid red';
					y = getYpos(names[i]);
					window.scroll(0, y-200);
					alert('Please enter a chapter name');
					return false;
				}
				var ids = names[i].id.split("_");
				var id = ids[3];
				var x = document.getElementById('id_video_link_' + id + '_value');
				if (x.disabled == false && x.value == '') {
					x.style.border = '1px solid red';
					y = getYpos(x);
					window.scroll(0, y-200);
					b = false;
					alert('You must enter a video link in order to continue.');
					return false;
				}
				x = document.getElementById('id_audio_track_' + id + '_value');
				if (x.disabled == false && x.value == '') {
					x.style.border = '1px solid red';
					y = getYpos(x);
					window.scroll(0, y-200);
					if (b)
						b = confirm('Are you sure you want to save this chapter without an audio file?');
				}
				x = document.getElementById('id_slide_image_' + id + '_value');
				if (x.disabled == false && x.value == '') {
					x.style.border = '1px solid red';
					y = getYpos(x);
					window.scroll(0, y-200);
					if (b)
						b = confirm('Are you sure you want to save this chapter without a slide image?');
				}
				
				st = document.getElementById('id_video_start_' + id);
				en = document.getElementById('id_video_end_' + id);
				if (st.value != '' && en.value != '') {
					if (en.value < st.value && en.value != "0") {
						en.style.border = '1px solid red';
						y = getYpos(en);
						window.scroll(0, y-200);
						alert('Please enter a value bigger than "Video start" or 0 to play the movie to the end.');
						en.value = "0";
						return false;
					}
				}
				
			}
		}
		
		for (i = 0; i < chapters.length; i++) {
			kk = chapters[i].id.split("_");
			if (kk[0] == 'chapter' && chapters[i].style.display != 'none') {
				video_link = document.getElementById('id_video_link_' + kk[1] + '_value');
				if (video_link.value != '') {
					val = video_link.value;
					youtube = "youtube";
					if (val.indexOf(youtube) == -1 && val.substring(val.length - 4) != '.flv') {
						video_link.style.border = '1px solid red';
						y = getYpos(video_link);
						window.scroll(0, y-200);
						alert('Please choose a .flv video or a youtube link');
						return false;
					}
				}
				
				image = document.getElementById('id_slide_image_' + kk[1] + '_value');
				if (image.value != '') {
					val = image.value.toLowerCase();
					if (val.substring(val.length - 4) != '.jpg' && val.substring(val.length - 4) != '.gif' && val.substring(val.length - 4) != '.png' && val.substring(val.length - 5) != '.jpeg' && val.substring(val.length - 4) != '.bmp') {
						image.style.border = '1px solid red';
						y = getYpos(image);
						window.scroll(0, y-200);
						alert('Allowed extensions for slide images are .jpg, .jpeg, .gif, .png, .bmp');
						return false;
					}
				}
				
			}
		}
		if (b == false)
			return false;
		return true;
	} else {
		return true;
	}
}

function getYpos(elem) {
   if (!elem) {
      return 0;
   }
   var y = elem.offsetTop
   var par = getYpos(elem.offsetParent);
   y += par;
   return y;
}

function openURL( url )
{
	try	{
		var popup = window.open( url );
		if ( popup == null )
			return false;
		if ( window.opera )
			if (!popup.opera)
				return false;
		
	} catch(err) {
		return false;
	}
	return popup;
}

/*function check(select)
{
	if (canOpen == false && select.selectedIndex == 1) {
		alert('You have to disable popup blocker to view this presenter in a new window');
		select.selectedIndex = 0;
	}
}*/


function showOnly(elem)
{
	for (i = 0; i < chapters.length; i++) {
		kk = chapters[i].id.split("_");
			chapters[i].style.display = 'none';
	}

	chapter = elem.parentNode.parentNode;
	ids = chapter.id.split('_');
	id = ids[1];
	elems = document.getElementsByName('showOnly[' + id + ']');
	for (i = 0; i < elems.length; i++) {
		elems[i].value = 'true';
	}
	
	elem.parentNode.parentNode.style.display = 'block';
	
	y = getYpos(elem);
	window.scroll(0, y-200);
	elem.style.display = 'none';

	elem.nextSibling.style.display = 'block';
	return false;
}

function showAll(elem)
{
	for (i = 0; i < chapters.length; i++) {
		kk = chapters[i].id.split("_");
			id = kk[1];
			del = document.getElementsByName('deleted[' + id + ']');
			if (del[0].value == 'false')
				chapters[i].style.display = 'block';
	}
	
	elems = getElementsByClassName(document, "input", "showOnlyThis");
	for (i = 0; i < elems.length; i++)
		elems[i].value = 'false';
	
	y = getYpos(elem);
	window.scroll(0, y-200);
	elem.style.display = 'none';
	elem.previousSibling.style.display = 'block';
	return false;
}
if (injector_path != "")
	setInterval('listenForChanges()', 100);
var oldValues = new Array();
for (i = 0; i < chapters.length; i++) {
	kk = chapters[i].id.split("_");
	oldValues[i] = $('id_video_link_' + kk[1] + '_value').value;
}

function listenForChanges()
{
	for (i = 0; i < chapters.length; i++) {
		kk = chapters[i].id.split("_");
		if (document.getElementById('id_video_link_' + kk[1] + '_value').value != oldValues[i]) {
			val = document.getElementById('id_video_link_' + kk[1] + '_value').value;
			oldValues[i] = val;
			if (val.substring(val.length - 4) == '.flv') {
				inject($('id_video_link_' + kk[1] + '_value'), kk[1]);
			}
		}
	}
}

function trim(str) 
{ 
	str = str.replace(/^\s+/, "").replace(/\s+$/, "");
	return str;
}

var stillInjecting = false;

//setInterval ("enableDisableButtons()", 1000);

function inject(elem, id)
{
	if (!in_array(injectedFiles, elem.value)) {
		xmlHttp = null;
		var val = elem.value;
		var parent = elem.parentNode;
		if ($(id)) $(id).parentNode.removeChild($(id));
		var div = document.createElement('span');
		var bold = document.createElement('b');
		var italic = document.createElement('i');
		bold.appendChild(italic);
		div.appendChild(bold);
		div.style.color = 'green';
		div.style.padding = '0px 0px 0px 5px';
		div.id = id;
		div.style.font = '12px Arial bold';
		parent.appendChild(div);
		div.innerHTML = 'Injecting metadata ...';
		stillInjecting = true;
		enableDisableButtons(id);
		xmlHttp = GetXmlHttpObject();
		
		if (xmlHttp == null) {
			 alert ("Browser does not support HTTP Request");
			 return;
		}
		
		var url = wwwroot + "/mod/presenter/inject.php?file=" + val + "&courseID=" + courseID;
		xmlHttp.onreadystatechange = function() {
			if (xmlHttp.readyState==4 || xmlHttp.readyState=="complete") { 
				var r = xmlHttp.responseText;
				stillInjecting = false;
				enableDisableButtons(id);
				if (r == "error") {
					div.style.color = 'red';
					div.innerHTML = 'Error injecting metadata.';
				} else {
					div.innerHTML = 'Metadata injected';
				}
				injectedFiles[injectedFiles.length] = val;
			 }
		};
		xmlHttp.open("GET",url,true);
		xmlHttp.send(null);
	} else {
		return;
	}
}

function enableDisableButtons(id)
{
	$('id_video_link_' + id + '_value').disabled = stillInjecting;
	$('id_video_link_' + id + '_popup').disabled = stillInjecting;
	$('id_add_chapters').disabled = stillInjecting;
	$('id_submitbutton2').disabled = stillInjecting;
	$('id_submitbutton').disabled = stillInjecting;
	$('id_cancel').disabled = stillInjecting;
} 

function $(id)
{
	return document.getElementById(id);
}
 
function GetXmlHttpObject()
{
	var xmlHttp=null;
	try
	 {
	 // Firefox, Opera 8.0+, Safari
	 	xmlHttp=new XMLHttpRequest();
	 }
	catch (e)
	 {
	 //Internet Explorer
	 try
	  {
	 	 xmlHttp=new ActiveXObject("Msxml2.XMLHTTP");
	  }
	 catch (e)
	  {
	 	 xmlHttp=new ActiveXObject("Microsoft.XMLHTTP");
	  }
	 }
	return xmlHttp;
}

function in_array(haystack, needle)
{
	for (key in haystack) {
		if (haystack[key] === needle)
			return true;
	}
	return false;
}

