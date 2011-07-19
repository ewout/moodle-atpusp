var maxwidth = 85;  //maximum width of a studentbox in px. initialised as the minimum possible width.
var initstate;      //initial state of unassigned box. used to restore when syncing between model/view

const TWO_JOBS_PRIORITY = 1;
const MISSED_OUT_PRIORITY = 2;

//teams are identified by their index in these arrays
var teamAssignments = [];
var commonAssignments = [];
var teamNames = [];
var teamGroupIds = [];
var selectedStudents = [];

var preventStudentClick = 0;


// dict to sortables
sortdict = {
    connectWith:".sortable",
    start:function(evt) { preventStudentClick = true; },
};

$(function() {
    // add select role function
    $("#roleselect").change(function() {
        update_unassigned_students();
    });

    $("#groupselect").change(function() {
        update_unassigned_students();
    });

    var numGroups = parseInt($("#stepper").text());
 
	$(".stepper").each(function() {
		var val = $(this).html();
		var buttons = $("<div><div class='ui-stepper-down'></div><span id='stepperval'>"+val+"</span><div class='ui-stepper-up'></div></div>");
		buttons.find(".ui-stepper-up").click(function() {
			var x = parseInt(buttons.find("span").html());
			x++;
                buttons.find("span").html(x);
                updateTeams(x);
            });
            buttons.find(".ui-stepper-down").click(function() {
                var x = parseInt(buttons.find("span").html());
                x--;
                if(x<1) x=1;
                buttons.find("span").html(x);
                updateTeams(x);
            });
            $(this).empty();
            $(this).append(buttons);
        });
        
        
        $(".student").each(function() {
            if($(this).width() > maxwidth)
                maxwidth = $(this).width();
        });

        $(".student").each(function() {
            $(this).width(maxwidth);
        });

        
        $(".student").live("mouseup",function(evt) {
            
            if(preventStudentClick) {
                preventStudentClick = false;
                return;
            }
            
            var details = $('<div class="studentResponse ui-corner-all"></div>');
            
            var studentID = /student-(\d+)/.exec($(this).attr("id"));
            var mdevent = function(evt){
                if(evt.target!=details.get(0)) {
                    details.remove();
                    $(document).unbind('mousedown',mdevent);
                }
            }
            $(document).mousedown(mdevent);
        });
        
        $(".team > h2[readonly!='true']").live("dblclick",function(evt) {
            var teamHeader = $(evt.target);
            var teamName = teamHeader.html();
            var teamTextBox = $('<input type="text" value="'+teamName+'" />');
            teamTextBox.css('font-size',teamHeader.css('font-size'));
            teamTextBox.width(teamHeader.width());
            teamTextBox.height(teamHeader.height());
            teamTextBox.css('border-width','0px');
            
            function textBoxDone() {
                var teamHeader = $("<h2>"+teamTextBox.val()+"</h2>");
                teamHeader.width(teamTextBox.width());
                teamHeader.height(teamTextBox.height());
                teamTextBox.replaceWith(teamHeader);
                teamNames[teamHeader.parent().index()] = teamHeader.html();
            }
            
            //conditionally attach the textBoxDone event
            //if you click outside the textbox
            var mdevent = function(evt){
                if(evt.target!=teamTextBox.get(0)) {
                    textBoxDone();
                    $(document).unbind('mousedown',mdevent);
                }
            }
            $(document).mousedown(mdevent);
            //if you press return
            teamTextBox.keypress(function(evt){
                if(evt.keyCode==13) { // key return 
                    textBoxDone();
                    $(document).unbind('mousedown',mdevent);
                }
            });
            
            teamHeader.replaceWith(teamTextBox);
            teamTextBox.focus();
            teamTextBox.select();
        });

        $("#teams .sortable").sortable(sortdict);

        initstate = $("#unassigned").html();
        updateTeams(numGroups);
        
        //$("#commonteam .sortable").sortable(sortdict);
        $("#unassigned .sortable").sortable(sortdict);
    });

    function update_unassigned_students() {
        var selector = "";
        $("#unassigned .student").hide();
        if ($("#roleselect").val()!=0) selector += "[roles*='"+$("#roleselect").val()+",']";
        if ($("#groupselect").val()!=0) selector += "[groups*='"+$("#groupselect").val()+",']";
        $("#unassigned .student"+selector).show();
        if ($("#groupselect").val()==0 && $("#roleselect").val()==0) $("#unassigned .student").show();
    }

    function getUrlVars() {
        var vars = [], hash;
        var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
        for(var i = 0; i < hashes.length; i++) {
            hash = hashes[i].split('=');
            vars.push(hash[0]);
            vars[hash[0]] = hash[1];
        }
        return vars;
    }

    function updateTeams(numTeams) {

        //update models
        synchroniseViewToModel();

        //slice off the end of the teams array if needed
        if(teamAssignments.length > numTeams) {
            teamAssignments = teamAssignments.slice(0, numTeams);
        }
        
        //slice off the end of the names array if needed
        if(teamNames.length > numTeams) {
            teamNames = teamNames.slice(0, numTeams);
            teamGroupIds = teamGroupIds.slice(0, numTeams);
        } else if (teamNames.length < numTeams) {
            for(i = teamNames.length-1; i < numTeams; i++) {
                if (teamNames[i] == null || teamNames[i] == '') {
                    teamNames[i] = "Team "+(i+1);
                    teamGroupIds[i] = 0;    //default value for groupid
                }
            }
        }
        //alert(teamGroupIds.length);
        synchroniseModelToView();
    }

    function resetTeams() {
        selectedStudents = [];
        for(i = 0; i < teamAssignments.length; i++)
        {
            teamAssignments[i] = [];
        }
        synchroniseModelToView();
    }

    function synchroniseViewToModel() {
        //first clear out our model
        teamAssignments = [];
        commonAssignments = [];
        teamNames = [];
        teamGroupIds = [];
        $(".team[id!='commonteam']").each(function() {
            var teamDiv = $(this);
            var teamIndex = $(this).index()-1; // index() - 1 is index = 0 is commonteam
            var assignments = [];
            teamDiv.find(".student").each(function() {
                var studentDiv = $(this);
                var studentID = /student-(\d+)/.exec($(this).attr("id"));
                assignments.push(studentID[1]);
            });
            teamAssignments[teamIndex] = assignments;
            var name = $(this).find("h2").text();
            teamNames[teamIndex] = name;
            teamGroupIds[teamIndex] = $(this).attr("groupid");
        });
        $("#commonteam").each(function() {
            $(this).find(".student").each(function() {
                var studentID = /student-(\d+)/.exec($(this).attr("id"));
                commonAssignments.push(studentID[1]);
            }); 
        });
        //__debug(teamAssignments);
        //__debug(commonAssignments);
        //__debug(teamNames);
    }

    function synchroniseModelToView() {
        //reset our view of students
        $(".team > * > .student").each(function() {
            var studentDiv = $(this);
            $("#unassigned > div.sortable").append(studentDiv);
        });
        $(".team > * > .student").detach();

        $(".team").each(function() {
            if ($(this).attr("id") != "commonteam") {
                var teamId = /team-(\d+)/.exec($(this).attr("id"));
                if (parseInt(teamId[1]) >= teamNames.length) {
                    $(this).detach();
                }
            }
        });
        for (var i = $(".team").length-1; i < teamNames.length; i++) {
            var teamDiv = $('<div class="team" id="team-'+i+'" groupid="'+
                          teamGroupIds[i]+'" />');
            teamDiv.append("<h2>"+teamNames[i]+"</h2>");
            teamDiv.width(maxwidth + 30);
            teamDiv.append('<div class="sortable ui-sortable"></div>');
            $('#teams').append(teamDiv);
            $('#team-'+i+' > .sortable').sortable(sortdict);
        }

        //now to move our students to our teams
        for(i in commonAssignments) {
            var studentID = commonAssignments[i];
            var studentDiv = $("#student-"+studentID);
            studentDiv.detach();
            $("#commonteam > div.sortable").append(studentDiv);
        }

        for(i in teamAssignments) {
            var team = teamAssignments[i];
            var teamDiv = $("#team-"+i+" div.sortable");
            for(j in team) {
                var studentID = team[j];
                var studentDiv = $("#student-"+studentID);
                studentDiv.detach();
                teamDiv.append(studentDiv);
                if($.inArray(studentID,selectedStudents)!=-1) {
                    studentDiv.css("color","green");
                } else {
                    studentDiv.css("color","");
                }
            }
        }
    }

    function assignRandomly()
    {
        synchroniseViewToModel();
        var unassignedStudents = [];
        $("#unassigned .student:visible").each(function() {
                rslt = /student-(\d+)/.exec(this.id);
                unassignedStudents.push(rslt[1]);
        });
        unassignedStudents = randomiseArray(unassignedStudents);
        
        while(unassignedStudents.length > 0)
        {
            //get the team(s) with the lowest numbers
            var lowestTeam = 0; var lowestTeams = [];
            
            //skip the 0th team since otherwise we compare it to itself
            for(i = 1; i < teamAssignments.length; i++)
            {
                t = teamAssignments[i];
                lt = teamAssignments[lowestTeam];
                
                if(t.length < lt.length)
                {
                    lowestTeam = i;
                    lowestTeams = [];
                }
                else if(t.length == lt.length)
                    lowestTeams.push(i);
            }
            lowestTeams.push(lowestTeam);

            //pick a random team from the list of lowest teams
            do { randomTeam = Math.floor(Math.random() * lowestTeams.length); } while (randomTeam >= lowestTeams.length) //on the OFF CHANCE that Math.random() produces 1
            teamAssignments[lowestTeams[randomTeam]].push(unassignedStudents.pop());
        }
        
        synchroniseModelToView();
    }

    function createGroups() {
        //How this works is, we're going to create an invisible form and submit it
        //acutally i don't know if we can do that but we'll try
        
        synchroniseViewToModel();
    
    var params = getUrlVars();
    var action = 'create';
    var htmlParams = 'course='+ params['course'];
    if (params['grouping'] != null && params['grouping'] != '') {
        htmlParams += '&grouping=' + params['grouping'];
        action = 'update';
    } else if ($('#isupdate').val() != null) {
        htmlParams += '&grouping=' + $('#isupdate').val();
        //alert(htmlParams);
        action = 'update';
    }
    //alert('parans: '+ htmlParams);
    var form = $('<form action="?' + htmlParams + '" method="POST"></form>');
	
    // build for teams
	for (i = 0; i < teamNames.length; i++) {
		var tn = teamNames[i];
		var assign = teamAssignments[i];
        var gi = teamGroupIds[i];
        
        var input = $('<input type="hidden" name="grouping_form" value="is_submitted" />');
        form.append(input);
        var input = $('<input type="hidden" name="teams['+i+'][name]" value="'+tn+'" />');
        form.append(input);
        var input = $('<input type="hidden" name="teams['+i+'][groupid]" value="'+gi+'" />');
        form.append(input);
        
		for(j = 0; j < assign.length; j++) {
			var ta = assign[j];
			var input = $('<input type="hidden" name="teams['+i+'][members]['+j+']" value="'+ta+'" />');
			form.append(input);
		}
	}
    
    // build for common groups
    for (k = 0; k < commonAssignments.length; k++) {
        ca = commonAssignments[k];
        form.append('<input type="hidden" name="commonteam[]" value="' + ca + '" />');
    }

	var action = $('<input type="hidden" name="action" value="' + action + '" />');
	var name = $('<input type="hidden" name="groupingname" value="'+$('#groupingname').val()+'" />');
    form.append(action);
    form.append(name);

    if ($('#inherit').attr("checked")) {
    	var inherit = $('<input type="hidden" name="inherit" value="'+ $('#inherit').val() +'" />');
        form.append(inherit);
    }
	
	$("#createGroupsForm").append(form);
	form.submit();
}

function addSlashes(str) {
    str=str.replace(/\\/g,'\\\\');
    str=str.replace(/\'/g,'\\\'');
    str=str.replace(/\"/g,'\\"');
    str=str.replace(/\0/g,'\\0');
    return str;
}

function stripSlashes(str) {
    str=str.replace(/\\'/g,'\'');
    str=str.replace(/\\"/g,'"');
    str=str.replace(/\\0/g,'\0');
    str=str.replace(/\\\\/g,'\\');
    return str;
}

function __debug(val) {
	//$("#debug").append("<div> "+val+" :: "+JSON.stringify(val)+"</div>");
    alert(val + ' :: ' + JSON.stringify(val));
}

function randomiseArray(inArray) {
	//much more random than sort()
	var ret = [];
	var array = inArray.slice(0);
	for(i = array.length; i > 0; i--)
	{
		index = Math.floor(Math.random()*i);
		ret.push(array[index]);
		array.splice(index,1);
	}
	return ret;
}

