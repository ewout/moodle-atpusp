/**
 * selection.js - Defines functions used by email.html in quickmail package
 *
 * Edited by: Philip Cali
 * 2008/02/15 15:03:00
 *
 * @author: Bibek Bhattarai and Wen Hao Chuang
 * 2007/03/29 09:00:00 
 * @package quickmailv2
 **/

//Global variables used for pointing to members and mailto selection list
var selectedList;
var availableList;
var availablegroups;
var availableRoles;
var oldGroupList;
var newList;
var selectedGroupidList;

/** This function initializes selectedlist and availablelist with members and mailto selection lists respectively.
  * This function is called everytime html form is reloaded 
  **/
function createListObjects(){
    //availableRoles points to the role filter list, that holds a list of "filters"
    availableRoles = document.getElementById("roles");
    //availablegroups points to the groups list, that holds a list of groups/sections in a course
    availablegroups = document.getElementById("groups");
	//availablelist points to members list, that holds list of users (Teachers and Students) present in the class
	availableList = document.getElementById("members");
	//Selectedlist points to mailto list, that holds list of users (Teachers and Students) to which email has to be sent
	selectedList = document.getElementById("mail_to");
	
  // Sort both lists in ascending order
  sortSelect(availableList);
  sortSelect(selectedList);

    oldGroupList = singlePassPreviousList(availablegroups);
}

function singlePassPreviousList(passedList) {
    selectedGroupidList = new Array();
    list = new Array(passedList.options.length);
    for (var i=0; i< passedList.options.length; i++) {
        list[i] = passedList.options[i].selected;
        if (list[i]) {
            var val = passedList.options[i].value;
            var vararr = val.split(" ");
            selectedGroupidList.push(vararr[0]);
        }
    }
    
    selectedGroupidList.push("");
    return list;
}

//apply filter to select_users
function special_select_users() {
      if (!availableRoles){
        createListObjects();
      }
      select_users(availablegroups, availableRoles.options[availableRoles.selectedIndex].value);
}

//abstract select users
function select_users(list, filter) {
    // select all the users that belong to that group
    if (list == null) {
        return;
    }    

    newList = singlePassPreviousList(list);
    differentIndexes = oldGroupList.diff(newList);    
    

    if (differentIndexes != null) {
      for (var index =0; index < differentIndexes.length; index++) {
        if (list.options[differentIndexes[index]]){
            var selected = list.options[differentIndexes[index]].selected;
            var interestarr = list.options[differentIndexes[index]].value.split(" ");
            var interest = interestarr[0];
        }
        var role_filter = (filter==null) ? "none" : filter;

        if (selected) {
            select_list(availableList, interest, role_filter);
            select_list(selectedList, interest, role_filter);
        } else {
            deselect_list(availableList, interest, role_filter);
            deselect_list(selectedList, interest, role_filter);
        }

      }
    }

    oldGroupList = newList;
}

function deselect_list(list, interest, filter) {
    for (var j=list.length-1; j>=0; j--) {
        var val = list.options[j].value;
        var vararr = val.split(" ");
        var groups = vararr[2].split(",");
        var roles = vararr[3].split(",");
        if (groups.contains(interest)) {
            if (groups.length ==2) {
                list.options[j].selected = false;
            } else if (!checkAllGroups(groups, selectedGroupidList)){
                list.options[j].selected = false;
            } else if (filter != "none" && !roles.contains(filter)) {
                list.options[j].selected = false;
            }
        }
    }
}

function checkAllGroups(list1, list2) {
    for (var i =0; i< list1.length; i++) {
        if (list2.contains(list1[i])) {
            return true;
        }
    }
    return false;
}

function select_list(list, interest, filter) {
    for (var j=list.length-1; j>=0; j--) {
        var val = list.options[j].value;
        var vararr = val.split(" ");
        var groups = vararr[2].split(",");
        var roles = vararr[3].split(",");
        if (groups.contains(interest)) {
            if (filter == "none" || roles.contains(filter)) {
                list.options[j].selected = true;
            }    
        }
    }
}

/** This function is used to remove user from selected list and add it back to available list */
function remove_user(){
	//for all items in selected list
	for(var i=selectedList.length-1; i>=0; i--){
		//if selected, append the user to available list. 
		//Append will automatically remove it from selected list
		if(selectedList.options[i].selected == true){
		    availableList.appendChild(selectedList.options[i]);
		}		
	}
	
  // Sort availableList in ascending order
  sortSelect(availableList);
	
	//call selectnone function to remove selection/highlight after move
	selectNone(selectedList, availableList, availablegroups);	
}

/** This function is used to add user to selectedlist and remove it from available list */
function add_user(){
	//for all items in available list
	for(var i=availableList.length-1; i>=0; i--){
		//if selected, append the user to selected list.
		//Append will automatically remove it from available list
		if(availableList.options[i].selected == true){
			selectedList.appendChild(availableList.options[i]);
		}
	}
	
  // Sort selectedList in ascending order
  sortSelect(selectedList);

	//call selectnone function to remove selection/hightlight after move
	selectNone(selectedList, availableList, availablegroups);	
}

/** This function is used to remove all users from selectionlist and add them to available list */
function removeAll(){
	var len = selectedList.length-1;
	//Select all users in selected list and append them to available list
	for(i=len; i>=0; i--){
		availableList.appendChild(selectedList.options[i]);	
	}

  // Sort availableList in ascending order
  sortSelect(availableList);

	//De-select all users after move
	selectNone(selectedList, availableList, availablegroups);	
}

/** This function is used to add all users from availablelist to selectionlist*/
function addAll(){
	var len = availableList.length - 1;
	//Select all users from availablelist and append them to selectedlist
	for(i=len; i>=0; i--){
		selectedList.appendChild(availableList.options[i]);
	}
	
  // Sort selectedList in ascending order
  sortSelect(selectedList);

	//De-select all users after move
	selectNone(selectedList, availableList, availablegroups);
}

/** This function is used to deselect users in availablelist, selectedlist after move */
function selectNone(list1, list2, list3){
	//Set all elements on list1 to selected false
	for(var i=list1.length-1; i>=0 ; i--){
		list1.options[i].selected = false;		
	}
	//Set all elements on list2 to selected false
	for(var i=list2.length-1; i>=0; i--){
		list2.options[i].selected = false;
	}
    //Set all elements on list3 to selected false
    for (var i=list3.length-1; i>=0; i--){
        list3.options[i].selected = false;
    }
}

/** This function is used to construct the list of user to whom email has to be sent */
function updateList(){
	var ids = '';
	// add user id of all elements in seleted list to string email as comma seperated value
	for(var i=selectedList.length-1; i>=0 ; i--){
		var val = selectedList.options[i].value;
		var valarr = val.split(" ");
		val = valarr[0];
		ids = ids+val;
		//do not add "," after last element
		if(i!=0){
			ids = ids+',';		
		}
	}
	//set hidden input value mailuser as email
	document.getElementById("mailuser").value = ids ;	
}

/** This function is used to construct the list of user to whom email has to be sent.
	This function builds list of emails to be sent through external client*/
function mail_to_ext_client(){
	var emails = '';
	// add user id of all elements in seleted list to string email as comma seperated value
	for(var i=selectedList.length-1; i>=0 ; i--){
		var val = selectedList.options[i].value;
		var valarr = val.split(" ");
		val = valarr[1];
		emails = emails+val;
		//do not add "," after last element
		if(i!=0){
			emails = emails+',';		
		}
	}
	from_email = document.getElementById("fromemail").value;
	//Redirects to external client with list of emails as bcc recievers
	location.href='mailto:'+from_email+'?bcc='+emails;
}



Array.prototype.contains = function(element) {
    if (element == null || element == '') {
        return false;
    }

    for (var i =0; i< this.length; i++) {
        if (this[i] == element) {
            return true;
        }
    }
    return false;
}

Array.prototype.diff = function(secondArray) {
    if (secondArray == null) {
        return null;
    }

    if (this.length != secondArray.length) {
        return null;
    }

    diffList = new Array();

    for (var i=0; i < secondArray.length ; i++) {
        if (this[i] != secondArray[i]) {
            diffList.push(i);
        }
    }

    return diffList;
}

// Sort functions

// sort function, by text - ascending (case-insensitive)
function sortFunctionAscending(record1, record2) {
    var value1 = record1.optText.toLowerCase();
    var value2 = record2.optText.toLowerCase();
    if (value1 > value2) return(1);
    if (value1 < value2) return(-1);
    return(0);
}

// sort function, by text - descending (case-insensitive)
function sortFunctionDescending(record1, record2) {
    var value1 = record1.optText.toLowerCase();
    var value2 = record2.optText.toLowerCase();
    if (value1 > value2) return(-1);
    if (value1 < value2) return(1);
    return(0);
}

function sortSelect(selectToSort, ascendingOrder) {
    if (arguments.length == 1) ascendingOrder = true;    // default to ascending sort

    // copy options into an array
    var myOptions = [];
    for (var loop = 0; loop < selectToSort.options.length; loop++) {
        myOptions[loop] = { optText:selectToSort.options[loop].text, optValue:selectToSort.options[loop].value };
    }

    // sort array by text
    if (ascendingOrder) {
        myOptions.sort(sortFunctionAscending);
    } else {
        myOptions.sort(sortFunctionDescending);
    }

    // copy sorted options from array back to select box
    selectToSort.options.length = 0;
    for (var loop = 0; loop < myOptions.length; loop++) {
        var optObj = document.createElement('option');
        optObj.text = myOptions[loop].optText;
        optObj.value = myOptions[loop].optValue;
        selectToSort.options.add(optObj);
    }
}
