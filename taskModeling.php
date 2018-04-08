<!doctype html>
<html lang="us">
<head>
	<meta charset="utf-8">
	<title>Test Modeling</title>
	<link href="js/jquery-ui.css" rel="stylesheet">
	<style>
		@import url('CSS/taskModeling.css');
	</style>
	
	<?php
		include 'Classes/contentClass.php';
		ContentSnippets::showFavicon();
	?>
	
    <script src="js/external/jquery/jquery.js"></script>
    <script>
	


    $(document).ready(function(){
        
        jQuery.fn.sortElements = (function(){
     
        var sort = [].sort;
     
        return function(comparator, getSortable) {
     
            getSortable = getSortable || function(){return this;};
     
            var placements = this.map(function(){
     
                var sortElement = getSortable.call(this),
                    parentNode = sortElement.parentNode,
     
                    // Since the element itself will change position, we have
                    // to have some way of storing its original position in
                    // the DOM. The easiest way is to have a 'flag' node:
                    nextSibling = parentNode.insertBefore(
                        document.createTextNode(''),
                        sortElement.nextSibling
                    );
     
                return function() {
     
                    if (parentNode === this) {
                        throw new Error(
                            "You can't sort elements if any one is a descendant of another."
                        );
                    }
     
                    // Insert before flag:
                    parentNode.insertBefore(this, nextSibling);
                    // Remove flag:
                    parentNode.removeChild(nextSibling);
     
                };
     
            });
     
            return sort.call(this, comparator).each(function(i){
                placements[i].call(getSortable.call(this));
            });
     
        };
     
    })();        
        
        function loadSubtasks(task_id) {
            $.post("PHPScripts/getSubtasks.php", {
                taskId: task_id
            }, function(data){
                $("#"+task_id+"").find(".subtask").remove();
                $.each(data, function(key,value){
                    var html = "<div id="+value.id+" class='subtask' data-number="+value.number+">";
                    html += "<input type='text' class='subtask_order_input' name='sub_task_order' value="+value.number+"></input>";                
                    html += "<input type='text' class='subtask_input' name='subtask' value='"+value.name+"'></input>";
                    html += "<textarea class='subtask_description_input' name='subtask_description' value='"+value.description+"'>"+value.description+"</textarea>"                    
                    html += "<button class='saveSubtaskButton'>Save Sub-Task</button>";
                    html += "<button class='deleteSubtaskButton'>Delete Sub-Task</button>";                    
                    html += "</div>";
                   $("#"+task_id+"").find(".addSubTaskButton").before(html); 
                   bindSaveSubtaskEvent();
                   bindDeleteSubtaskEvent();                
                });
                $("#"+task_id+"").find(".subtask").sortElements(function(a, b){
                    return $(a).attr("data-number") > $(b).attr("data-number") ? 1 : -1;
                });                 
                                
            }, "json");
        }
        
        function loadTasks(phase_id){
			$.post("PHPScripts/getTasks.php", {
    			phaseId: phase_id
			}, function(data){
    			$("#"+phase_id+"").find(".task").remove();
                $.each(data, function(key,value){                                                
                    var html = "<div id="+value.id+" class='task' data-number="+value.number+">";
                    html += "<input type='text' class='task_order_input' name='task_order' value="+value.number+"></input>";                
                    html += "<input type='text' class='task_input' name='task' value='"+value.name+"'></input>";
                    html += "<textarea class='task_description_input' name='task_description' value='"+value.description+"'>"+value.description+"</textarea>"
                    html += "<button class='saveTaskButton'>Save Task</button>";
                    html += "<button class='deleteTaskButton'>Delete Task</button>";                    
                    html += "<button class='addSubTaskButton'>+ Sub-Task</button>";
                    html += "</div>";                
                    $("#"+phase_id+"").find(".addTaskButton").before(html);
                      
                    bindAddSubTaskEvent();
                    bindSaveTaskEvent();
                    bindDeleteTaskEvent();
                    loadSubtasks(value.id); 
                }); 
                $("#"+phase_id+"").find(".task").sortElements(function(a, b){
                    return $(a).attr("data-number") > $(b).attr("data-number") ? 1 : -1;
                });                 
                
			}, "json");               
        }
        
        function loadPhases(){
         
			$.post("PHPScripts/getPhases.php", function(data){
    			$("div .phase").remove();
                $.each(data, function(key,value){
                    var html = "<div id="+value.id+" class='phase' data-number="+value.number+">";
                    html += "<input type='text' class='phase_order_input' name='phase_order' value="+value.number+"></input>";
                    html += "<input type='text' class='phase_input' name='phase' value='"+value.name+"'></input>";
                    html += "<button class='savePhaseButton'>Save Phase</button>";
                    html += "<button class='deletePhaseButton'>Delete Phase</button>";                    
                    html += "<button class='addTaskButton'>+ Task</button>";
                    html += "</div>";    
                    $(".addPhaseButton").before(html);     
                    bindAddTaskButtonEvent();
                    bindSavePhaseEvent();    
                    bindDeletePhaseEvent();                                                                   
                    loadTasks(value.id);                    
                });
                $("div .phase").sortElements(function(a, b){
                    return $(a).attr("data-number") > $(b).attr("data-number") ? 1 : -1;
                });
			}, "json");         
        
            return false;
        }
        
        function bindDeleteSubtaskEvent(){
            $(".deleteSubtaskButton").off().click(function(){

                var id = $(this).parent().attr("id");
                $(this).parent().remove();
                
    			$.post("PHPScripts/deleteSubtask.php", {
        			subtaskId: id
    			}, function(data){
        			console.log(data);
    			});                       
                                
     
            });             
        }
        
        function bindSaveSubtaskEvent(){
            $(".saveSubtaskButton").off().click(function(){
                var task_id = $(this).parent().parent().attr('id');
                var subtask_number = $(this).siblings(".subtask_order_input").val();
                var subtask_name = $(this).siblings(".subtask_input").val();
                var subtask_description = $(this).siblings(".subtask_description_input").val();
                var anId = $(this).parent().attr("id");

                if(typeof anId === "undefined"){
                    $.post("PHPScripts/createSubtask.php", {
                        taskId: task_id,
                        number: subtask_number,
                        name: subtask_name,
                        description: subtask_description
                    }, function(data){
                        loadSubtasks(task_id);
                    });
                }
                
                else {
    				$.post("PHPScripts/updateSubtask.php", {
        				subtaskId: anId,
        				number: subtask_number,
        				name: subtask_name,
        				description: subtask_description
    				}, function(data){
                        loadSubtasks(task_id);
    				});                      
                }

                
            })
        }
        
        function bindDeleteTaskEvent(){
            $(".deleteTaskButton").off().click(function(){
                var subtasks = $(this).parent().find(".subtask").length;
                
                if(subtasks>0){
                    // do not delete a phase with any children tasks
                    console.log('children: '+ subtasks);
                    return false;
                }
                
                else {
                    var id = $(this).parent().attr("id");
                    $(this).parent().remove();
                    console.log("id of task to delete: " + id);
        			$.post("PHPScripts/deleteTask.php", {
            			taskId: id
        			}, function(data){
            			console.log(data);
        			});                       
                                    
                }
            });            
        }
        
        function bindSaveTaskEvent(){
            $(".saveTaskButton").off().click(function(){
                var phase_id = $(this).parent().parent().attr('id');
                var task_number = $(this).siblings(".task_order_input").val();
                var task_name = $(this).siblings(".task_input").val();
                var task_description = $(this).siblings(".task_description_input").val();
                var anId = $(this).parent().attr("id");
                
                if(typeof anId === "undefined"){
                    //task has not been saved
                    $.post("PHPScripts/createTask.php", {
                        phaseId: phase_id,
                        number: task_number,
                        name: task_name,
                        description: task_description
                    }, function(data){
                        loadTasks(phase_id);
                    });
                }
                else {
    				$.post("PHPScripts/updateTask.php", {
        				taskId: anId,
        				number: task_number,
        				name: task_name,
        				description: task_description
    				}, function(data){
                        loadTasks(phase_id);
    				});                     
                }
                

            })
        }
        
        function bindDeletePhaseEvent(){
            $(".deletePhaseButton").off().click(function(){
                var tasks = $(this).parent().find(".task").length;
                if(tasks>0){
                    // do not delete a phase with any children tasks
                    return false;
                }
                
                else {
                    var id = $(this).parent().attr("id");
                    $(this).parent().remove();
                    console.log("id of phase to delete: " + id);
        			$.post("PHPScripts/deletePhase.php", {
            			phaseId: id
        			}, function(data){
            			console.log('');
        			});                       
                                    
                }
            });
        }
        
        function bindSavePhaseEvent(){
            $(".savePhaseButton").off().click(function(){
                var phase_number = $(this).siblings(".phase_order_input").val()
                var phase_name = $(this).siblings(".phase_input").val();
                var anId = $(this).parent().attr("id");

                if (typeof anId === "undefined") {
                    // phase has not yet been saved.
    				$.post("PHPScripts/createPhase.php", {
        				number: phase_number,
        				name: phase_name
    				}, function(data){
                        loadPhases();
    				});                    
                }
                
                else {
                    // update the phase
    				$.post("PHPScripts/updatePhase.php", {
        				phaseId: anId,
        				number: phase_number,
        				name: phase_name
    				}, function(data){
                        loadPhases();
    				}); 
                }

            })
        }

        function bindAddSubTaskEvent(){
            $(".addSubTaskButton").off().click(function(){
                var html = "<div class='subtask'>";
                html += "<input type='text' class='subtask_order_input' name='sub_task_order' placeholder=''></input>";                
                html += "<input type='text' class='subtask_input' name='subtask' placeholder='A Sub-Task'></input>";
                html += "<textarea class='subtask_description_input' name='subtask_description'></textarea>"
                html += "<button class='saveSubtaskButton'>Save Sub-Task</button>";
                html += "<button class='deleteSubtaskButton'>Delete Sub-Task</button>";
                html += "</div>";
               $(this).before(html);
               bindSaveSubtaskEvent();
               bindDeleteSubtaskEvent();
            });
        }    
                
        function bindAddTaskButtonEvent(){
            $(".addTaskButton").off().click(function(){
                var html = "<div class='task'>";
                html += "<input type='text' class='task_order_input' name='task_order' placeholder=''></input>";                
                html += "<input type='text' class='task_input' name='task' placeholder='A Task'></input>";
                html += "<textarea class='task_description_input' name='task_description'></textarea>"                
                html += "<button class='saveTaskButton'>Save Task</button>";
                html += "<button class='deleteTaskButton'>Delete Task</button>";
                html += "<button class='addSubTaskButton'>+ Sub-Task</button>";
                html += "</div>";                
                $(this).before(html);
                bindAddSubTaskEvent();
                bindSaveTaskEvent();
                bindDeleteTaskEvent();
            });            
        }        

        $(".addPhaseButton").click(function(){
            var html = "<div class='phase'>";
            html += "<input type='text' class='phase_order_input' name='phase_order' placeholder=''></input>";
            html += "<input type='text' class='phase_input' name='phase' placeholder='A Phase'></input>";
            html += "<button class='savePhaseButton'>Save Phase</button>";
            html += "<button class='deletePhaseButton'>Delete Phase</button>";
            html += "<button class='addTaskButton'>+ Task</button>";
            html += "</div>";
            $(this).before(html);
             bindAddTaskButtonEvent();
             bindSavePhaseEvent();
             bindDeletePhaseEvent();
        });
     
        loadPhases();
    })
    
    
	</script>
    
    
    </head>
    	
	
</head>
<body>
		<?php
			ContentSnippets::doHeader();
			ContentSnippets::doNavigationBar();
		?>
	

    <div id='tree'>      
        <button class='addPhaseButton' name='button' text='button' value='button'> + phase</button>                      
    </div>



</body>
</html>
