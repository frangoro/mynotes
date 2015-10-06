<!DOCTYPE HTML>
<?php
//error_reporting(E_ERROR | E_PARSE);
include 'chromephp-master/ChromePhp.php';
ChromePhp::log('Hello console!');
$titleError = $noteError = $priorityError = $start_dateError = $end_dateError = $estimationError = "";
$title = $note = $priority = $start_date = $end_date = $estimation = "";

$servername = "localhost";
$username = "frangoro";
$password = "frangoro";
$dbname = "misnotas";

function sanitize($data) {
	$data = trim($data);
	$data = stripcslashes($data);
	$data = htmlspecialchars($data);
	return $data;
}

function deleteNote($id) {
	try {
		$stmt = $GLOBALS["stmt"];
		$conn = $GLOBALS["conn"];
		$stmt = $conn->prepare("DELETE FROM notes WHERE id = :id");
		$stmt->bindParam(":id",$id);
		$stmt->execute();
	} catch (PDOException $e){
		echo "Error MySQL: " . $e->getMessage();
	}
}
//TODO: BORRAR
/*function updateNote($id) {
	try {
		$stmt = $GLOBALS["stmt"];
		$conn = $GLOBALS["conn"];
		$stmt = $conn->prepare("UPDATE notes SET tile=:title, note=:note," +
			" priority=:priority, start_date=:start_date, end_date=:end_date, estimation=:estimation WHERE id = :id");
		$stmt->bindParam(":id",$id);
		$stmt->bindParam(":title",$title);
		$stmt->bindParam(":note",$note);
		$stmt->bindParam(":priority",$priority);
		$stmt->bindParam(":start_date",$start_date);
		$stmt->bindParam(":end_date",$end_date);
		$stmt->bindParam(":estimation",$estimation);
		$stmt->execute();
	} catch (PDOException $e){
		echo "Error MySQL: " . $e->getMessage();
	}
}*/

try {
	$conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
	$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	$stmt = $conn->prepare("SELECT * FROM notes");
	$stmt->execute();
	$result = $stmt->fetchAll();
		// Tal vez habría que poner esto en otro fichero
		if ($_REQUEST["op"] == "delete") {
			deleteNote($_GET['id']);
		}
// It's only executed if form is submited
if ($_SERVER["REQUEST_METHOD"] == "POST") {
	ChromePhp::log($_REQUEST["title"]);
	ChromePhp::log($_REQUEST["op"]);			
	// Validations
	if (empty($_POST["title"])) {
		$titleError = "Required.";
	} else {
		$title = sanitize($_POST["title"]);
	}
	// Retrive data from request
	$id = sanitize($_POST["id"]);
	$note = sanitize($_POST["note"]);
	$priority = sanitize($_POST["priority"]);
	$start_date = sanitize($_POST["start_date"]);
	$end_date = sanitize($_POST["end_date"]);
	$estimation = sanitize($_POST["estimation"]);
	// Operations
	if ($_POST["op"] == "update") {
		ChromePhp::log('UPDATE');
		ChromePhp::log($title);ChromePhp::log($id);
		$stmt = $conn->prepare("UPDATE notes SET title=:title, note=:note" .
			" ,priority=:priority, start_date=:start_date, end_date=:end_date, estimation=:estimation WHERE id = :id");
		//$stmt = $conn->prepare("UPDATE notes SET title=:title, note=:note, priority=:priority, start_date=:start_date, end_date=:end_date, estimation=:estimation WHERE id = :id");
		$stmt->bindParam(":id",$id);
	} else {
		ChromePhp::log("create");
		$stmt = $conn->prepare("INSERT INTO notes (title, note, priority, start_date, end_date, estimation) 
			VALUES (:title, :note, :priority, :start_date, :end_date, :estimation)");
	}
		$stmt->bindParam(":title",$title);
		$stmt->bindParam(":note",$note);
		$stmt->bindParam(":priority",$priority);
		$stmt->bindParam(":start_date",$start_date);
		$stmt->bindParam(":end_date",$end_date);
		$stmt->bindParam(":estimation",$estimation);
		$stmt->execute();
		ChromePhp::log($stmt->rowCount());
		/*$note = $_POST["note"];
		$priority = $_POST["priority"];
		$start_date = $_POST["start_date"];
		$end_date = $_POST["end_date"];
		$estimation = $_POST["estimation"];*/
}
?>
<html ng-app="myNotes">
<head>
	<meta charset="UTF-8">
	<title>My Notes</title>
	<link rel="stylesheet" href="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
	<link rel="stylesheet" type="text/css" href="myNotes.css">
	<script src="http://ajax.googleapis.com/ajax/libs/angularjs/1.3.14/angular.min.js"></script>
	<script src="myNotes.js"></script>
	<script src="myNotesController.js"></script>
</head>
<body>
<div class="container" ng-controller="myNotesCtrl">
<h2>Add Note:</h2>
<div class="jumbotron">
<form action="<?php htmlspecialchars($_SERVER["PHP_SELF"]) ?>" method="post">
<div class="error">Required *</div>
Title: <input type="text" name="title" title="Write down the note title." placeholder="Note title" autofocus required>
<span class="error">*<?php echo $titleError; ?></span><br>
Note: <textarea name="note" placeholder="¿What's up?..." cols="30" rows="10" ng-model="note"></textarea>
<span class="error">*<?php echo $noteError ?></span><br>
<p>Number of character left: {{getLeft()}}</p><span></span>
Priority: <input type="radio" name="priority" value="3">High
<input type="radio" name="priority" value="2">Medium
<input type="radio" name="priority" value="1">Low
<input type="radio" name="priority" value="0" checked>None<br>
Start: <input type="datetime" name="start_date" value="<?php echo date("2015-09-01 00:00:00")?>"><br>
End: <input type="datetime" name="end_date"><br>
Estimation: <input type="number" min="0" name="estimation"> hours<br><br>
<input type="submit" value="Submit">
<button ng-click="clear()">Clear</button>
</form>
</div>
<h2>My Notes:</h2>
<?php
	// Show all notes
	foreach ($result as $row) {
		$objaux = array('id'=>$row['id'],'title'=>$row['title'], 'note'=>$row['note'], 'priority'=>$row['priority'],
		 'start_date'=>$row['start_date'], 'end_date'=>$row['end_date'], 'estimation'=>$row['estimation']);
		echo("<div id=".$row['id']." class='note'><span><h1 class='noteTitle'>".$row['title'].
			"</h1></span><a href='' onclick='deleteNote(".$row['id'].")'>[X]</a>".//Poner botones en lugar de enlaces para no redirigir a #
			"<a href='#' onclick='editNote(".json_encode($objaux).")'>[Edit]</a>");
		echo("<p>".$row['note']."</p><br>");
		echo("Priority: ".$row['priority']."<br>");
		echo("Start: ".$row['start_date']."<br>");
		echo("End: ".$row['end_date']."<br>");
		echo("Estimation: ".$row['estimation']."</div>");
	}
} catch (PDOException $e){
	ChromePhp::log($e->getMessage());
	echo "Error MySQL: " . $e->getMessage();
}
?>
<script>
// Call to delteNote in PHP via AJAX
function deleteNote(id) {
	var xmlhttp = new XMLHttpRequest();
	xmlhttp.onreadystatechange = function() {
		if (xmlhttp.readySate == 4 && xmlhttp.status == 200) {
			console.log("Note with id: " + id + " was deleted.");
		}
	}
	xmlhttp.open("GET", "index.php?op=delete&id="+id, true);
	xmlhttp.send();
}

// Edit one note
function editNote(json){
	// Create a new note form
	console.log(json);
	var noteForm = "<div id='"+json.id+"' class='note'><form id='editForm'>" +
	"<span><h1 class='noteTitle'><input type='text' name='title' value='"+json.title+"'>" +
	"</h1><a href='' onclick='closeEditNote("+json.id+")'>[X]</a></span>" +
	"<p><input type='text' name='note' value='"+json.note+"'></p><br>" +
	"Priority: <input type='text' name='priority' value='"+json.priority+"'><br>" +
	"Start: <input type='text' name='start_date' value='"+json.start_date+"'><br>" +
	"End: <input type='text' name='end_date' value='"+json.end_date+"'><br>" +
	"Estimation: <input type='text' name='estimation' value='"+json.estimation+"'></form>" +
	"<button type='button' onclick='updateNote("+json.id+")'>Save changes</button>" +
	"</div>";
	// Replace selected note with new note form
	document.getElementById(json.id).innerHTML = noteForm;
	//TODO: closeEditNote debería volver a poner la nota en lectura--> ¿guardar estado previo en variable temporal?
	// O bien refrescar la página sin guardar cambios
}

// Call to updateNote in PHP via AJAX
function updateNote(id) {
	var editForm = document.getElementById('editForm');
	var xmlhttp = new XMLHttpRequest();
	xmlhttp.onreadystatechange = function() {
		if (xmlhttp.readySate == 4 && xmlhttp.status == 200) {
			console.log("Note with id: " + id + " was updated");
		}
	}
	// TODO: POST??
	xmlhttp.open("POST", "index.php", true);
	xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	xmlhttp.send("id="+id+"&title="+editForm.title.value+"&note="+editForm.note.value+"&priority="+
		editForm.priority.value+"&start_date="+editForm.start_date.value+"&end_date="+
		editForm.end_date.value+"&estimation="+editForm.estimation.value+"&op=update");
}
</script>
</div>
</body>
</html>