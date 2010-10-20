/*
    Author: Philip Cali
    Date: 2/15/2008
    Louisiana State University

    java script for applying multiple attachments
*/

//place holder for all attachment input tags
var attachArray = new Array("attach_1");

//function adds a remove link, and builds a new attachment field
function create_new_attachment(input_id) {
    //main table is the display table
    main_table = document.getElementById("maintable");

    //loaded attachment: an attachment field that has been loaded
    input_tag = document.getElementById(input_id);
    //table row associated with attachment
    table_row = document.getElementById(input_id+"row");

    var new_cell = table_row.insertCell(table_row.cells.length);
    //create the remove link
    new_cell.innerHTML = "<a onclick=\"remove_attachment('"+ input_id +"')\" href=\"javascript:void\">Remove</a>";

    //time to create the new attachment field
    var new_row = main_table.insertRow(main_table.findRow("attach") +1 );
    var new_id = input_id.split("_");

    var id_number = parseInt(new_id[1]) + 1;
    new_row.id = new_id[0] + "_" + id_number + "row";

    new_row.insertCell(0);
    var input_cell = new_row.insertCell(1);
    input_cell.colSpan = "2";

    var new_input_id = new_id[0] + "_" + id_number;
    input_cell.innerHTML = "<input id=\""+ new_input_id+ "\" name=\""+new_input_id+"\" type=\"file\" size=\"45\" onchange=\"javascript:create_new_attachment('"+ new_input_id +"')\"/>";
    
    //keep track of our attachments
    attachArray.push(new_input_id); 
    
    //store in hidden field to process in email.php
    document.getElementById("attachids").value=attachArray.toString();
}

//this is executed when the remove link is clicked
function remove_attachment(input_id) {
   //get the main display table
   main_table = document.getElementById("maintable");
   
   //get table row associated with the remove link
   table_row = document.getElementById(input_id + "row");
    
   //remove it
   main_table.deleteRow(table_row.rowIndex);

   //keep track
   attachArray.remove(input_id);
   
   document.getElementById("attachids").value = attachArray.toString();
}


/** Function added to the table class that returns the index 
 * matching the id of the row in question
 */
HTMLTableElement.prototype.findRow = function(id) {
    for (i=this.rows.length-1; i>=0; i--) {
        rowid = this.rows[i].id;
        
        if (rowid && id.search(rowid)) {
            return i;
        }
    }
    return -1;
}

//loaded prototype for array index removal
Array.prototype.remove = function(s) {
    for (i=0;i<this.length;i++) {
        if (s==this[i]) this.splice(i, 1);
    }
}

