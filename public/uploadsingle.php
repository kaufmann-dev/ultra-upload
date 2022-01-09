<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HappyBook | Upload</title>
    <link href="../style.css" rel="stylesheet">
    <link rel="icon" href="../favicon.ico">
    <link rel="apple-touch-icon" href="../favicon.ico">
</head>
<body class="darkblue">
<div id="wrapper">
    <form method="POST" action="" enctype="multipart/form-data" class="lightblue">
        <label for="user">Benutzername</label><br>
        <input type="text" id="user" name="user" required>
        <div class="spacer"></div>
        <label for="pass">Kennwort</label><br>
        <input type="password" id="pass" name="pass" required>
        <div class="spacer"></div>
        <label for="pass">Ebene</label><br>
        <select name="level" id="level" required>
            <option value="level4">Ebene 4 - Reserviert</option>
            <option value="level3">Ebene 3 - Administrator sieht</option>
            <option value="level2">Ebene 2 - Bestimmte sehen</option>
            <option value="level1">Ebene 1 - Öffentlich</option>
        </select>
        <div class="spacer"></div>
        <label for="fileToUpload">Datei</label><br>
        <input type="file" name="fileToUpload" id="fileToUpload" required>
        <div class="spacer"></div>
        <input autofocus type="submit" name="submit" value="Hochladen"></input>
    </form>
<?php
    $user = $_POST['user'];
    $pass = $_POST['pass'];
    $level = $_POST['level'];
    $file = $_FILES["fileToUpload"];
    $user_arr = json_decode(file_get_contents('../../users.json'), true)['users'];

    function Authenticate($arr, $user, $pwd, $right){
        foreach($arr as $lol){
            if($lol['username'] == $user && $lol['password'] == $pwd && in_array($right, $lol['rights'])){
                return true;
            }
        }
        return false;
    }

    if(isset($user) && isset($pass)) {
        switch($level){
            case "level1":
                if(Authenticate($user_arr, $user, $pass, "admin")){
                    Upload("../../uploads/".$level."/", $file);
                } else {
                    exit("<div class='error'>Keine Berechtigung</div>");
                }
                break;
            case "level2":
                if(Authenticate($user_arr, $user, $pass, "admin")){
                    Upload("../../uploads/".$level."/", $file);
                } else {
                    exit("<div class='error'>Keine Berechtigung</div>");
                }
                break;
            case "level3":
                if(Authenticate($user_arr, $user, $pass, "upload")){
                    Upload("../../uploads/".$level."/", $file);
                } else {
                    exit("<div class='error'>Keine Berechtigung</div>");
                }
                break;
            case "level4":
                if(Authenticate($user_arr, $user, $pass, "admin")){
                    Upload("../../uploads/".$level."/", $file);
                } else {
                    exit("<div class='error'>Keine Berechtigung</div>");
                }
                break;
            default:
                exit("<div class='error'>Interner Systemfehler</div>");
        }
    }

    function Upload($target_dir, $file){
        $target_file = $target_dir . basename($file["name"]);
        $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));

        // Check if file already exists
        if (file_exists($target_file)) {
            exit("<div class='error'>Datei existiert bereits</div>");
        }
        
        // Check file size
        if ($file["size"] > 10000000) {
            exit("<div class='error'>Datei ist zu groß. Limit: 10MB</div>");
        }
        
        // Upload
        if (move_uploaded_file($file["tmp_name"], $target_file)) {
            exit("<div class='success'>Datei ".htmlspecialchars( basename( $file["name"]))." wurde hochgeladen</div>");
        } else {
            exit("<div class='error'>Es gab einen Fehler beim Upload</div>");
        }
    }
?>     
</div>   
</body>
</html>