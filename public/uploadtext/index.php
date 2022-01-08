<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ultraupload | Upload text</title>
    <link href="../style.css" rel="stylesheet">
    <link rel="icon" href="../favicon.ico">
    <link rel="apple-touch-icon" href="../favicon.ico">
</head>
<body class="darkblue">
<div id="wrapper">
    <form method="POST" action="" enctype="multipart/form-data" class="lightblue">
        <label for="user">Benutzername*</label><br>
        <input type="text" id="user" name="user" required>
        <div class="spacer"></div>
        <label for="pass">Kennwort*</label><br>
        <input type="password" id="pass" name="pass" required>
        <div class="spacer"></div>
        <label for="pass">Ebene*</label><br>
        <select name="level" id="level" required>
            <option value="level1">Ebene 1 - Öffentlich</option>
            <option value="level2">Ebene 2 - Bestimmte sehen</option>
            <option value="level3">Ebene 3 - Administrator sieht</option>
            <option value="level4">Ebene 4 - Reserviert</option>
        </select>
        <div class="spacer"></div>
        <label for="text">Text*</label><br>
        <textarea name="text" id="text" required></textarea>
        <div class="spacer"></div>
        <label for="filename">Dateiname</label><br>
        <input type="text" name="filename" id="filename"></input>
        <div class="spacer"></div>
        <input autofocus type="submit" name="submit" value="Hochladen"></input>
    </form>
<?php
    if(isset($_POST['user'])){ $user = $_POST['user']; }
    if(isset($_POST['pass'])){ $pass = $_POST['pass']; }
    if(isset($_POST['level'])){ $level = $_POST['level']; }
    if(isset($_POST['text'])){ $text = $_POST['text']; }
    if(isset($_POST['filename'])){ $filename = $_POST['filename']; }
    
    $user_arr = json_decode(file_get_contents('../../users.json'), true)['users'];

    function Authenticate($arr, $user, $pwd, $right){
        foreach($arr as $lol){
            if($lol['username'] == $user && $lol['password'] == $pwd && in_array($right, $lol['rights'])){
                return true;
            }
        }
        return false;
    }

    if(isset($pass, $user, $text, $level)) {
        switch($level){
            case "level1":
                if(Authenticate($user_arr, $user, $pass, "admin")){
                    UploadText("../../uploads/".$level."/", $text);
                } else {
                    exit("<div class='error'>Keine Berechtigung</div>");
                }
                break;
            case "level2":
                if(Authenticate($user_arr, $user, $pass, "admin")){
                    UploadText("../../uploads/".$level."/", $text);
                } else {
                    exit("<div class='error'>Keine Berechtigung</div>");
                }
                break;
            case "level3":
                if(Authenticate($user_arr, $user, $pass, "upload")){
                    UploadText("../../uploads/".$level."/", $text);
                } else {
                    exit("<div class='error'>Keine Berechtigung</div>");
                }
                break;
            case "level4":
                if(Authenticate($user_arr, $user, $pass, "admin")){
                    UploadText("../../uploads/".$level."/", $text);
                } else {
                    exit("<div class='error'>Keine Berechtigung</div>");
                }
                break;
            default:
                exit("<div class='error'>Interner Systemfehler</div>");
        }
    }

    function UploadText($target_dir, $text){
        global $filename;
        
        if(iterator_count(new FilesystemIterator("../../uploads/level1"))+iterator_count(new FilesystemIterator("../../uploads/level2"))+iterator_count(new FilesystemIterator("../../uploads/level3"))+iterator_count(new FilesystemIterator("../../uploads/level4")) > 100){
            exit("<div class='error'>Es existieren bereits mehr als 100 Uploads. Kontaktieren Sie den Administrator.</div>");
        }
        if(strlen($text)>9999){
            exit("<div class='error'>Text zu lang: ".strlen($text)."/9999</div>");
        }

        if(isset($filename) & !empty($filename)) {
            $computedFilePath = "$target_dir/$filename.txt";
            $computedFilename = "$filename.txt";
        } else {
            $computedFilePath = "$target_dir/".time().".txt";
            $computedFilename = time().".txt";
        }

        $myfile = fopen($computedFilePath, "w") or die("Konnte Textdatei nicht erstellen");
        
        fwrite($myfile, $text);
        fclose($myfile);
        print("<div class='success'>$computedFilename wurde erstellt</div><br>");
    }
?>     
</div>   
</body>
</html>