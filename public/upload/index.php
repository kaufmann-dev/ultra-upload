<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ultraupload | Upload</title>
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
        <label for="upload">Datei(en)*</label><br>
        <input type="file" name="upload[]" id="upload" required multiple>
        <div class="spacer"></div>
        <label for="filename">Dateiname(n)</label><br>
        <input type="text" name="filename" id="filename">
        <div class="spacer"></div>
        <label for="zip">ZIP erstellen</label><br>
        <input type="checkbox" name="zip" id="zip" value="on">
        <div class="spacer"></div>
        <div id="zipname" style="display: none;">
            <label for="zipname">ZIP Name</label><br>
            <input type="text" name="zipname" id="zipname">
            <div class="spacer"></div>
        </div>
        <input autofocus type="submit" name="submit" value="Hochladen"></input>
    </form>
<?php
    /*if(!extension_loaded("zip")){
        print("error");
    }*/

    if(isset($_POST['user'])){ $user = $_POST['user']; }
    if(isset($_POST['pass'])){ $pass = $_POST['pass']; }
    if(isset($_POST['level'])){ $level = $_POST['level']; }
    if(isset($_POST['zip'])){ $zip = $_POST['zip']; }
    if(isset($_POST['filename'])){ $filename = $_POST['filename']; }
    if(isset($_POST['zipname'])){ $zipname = $_POST['zipname']; }
    if(isset($_FILES['upload'])){ $files = array_filter($_FILES['upload']); }

    $user_arr = json_decode(file_get_contents('../../users.json'), true)['users'];
    $upload_limit_arr = json_decode(file_get_contents('../../upload_limits.json'), true)['rights'];

    function Authenticate($arr, $user, $pwd, $right){
        foreach($arr as $lol){
            if($lol['username'] == $user && $lol['password'] == $pwd && in_array($right, $lol['rights'])){
                return true;
            }
        }
        return false;
    }

    function GetUploadLevel($user){
        global $user_arr;
        foreach($user_arr as $pubg){
            if($pubg["username"] == $user){
                if(in_array("admin", $pubg["rights"])){
                    return "admin";
                } elseif (in_array("upload", $pubg["rights"])){
                    return "upload";
                }
            }
        }
        exit("<div class='error'>ERROR</div>");
    }

    function GetUploadLimit($user, $level){
        global $upload_limit_arr;
        if($user == "system"){
            if($level == "single"){
                return $upload_limit_arr[2]["single_limit"];
            } else if($level == "all") {
                return $upload_limit_arr[2]["all_limit"];
            }
            exit("<div class='error'>ERROR</div>");
        }
        $bruh = GetUploadLevel($user);
        switch($bruh){
            case "upload":
                if($level == "single"){
                    return $upload_limit_arr[0]["single_limit"];
                } else if($level == "all") {
                    return $upload_limit_arr[0]["all_limit"];
                }
                exit("<div class='error'>ERROR</div>");
            case "admin":
                if($level == "single"){
                    return $upload_limit_arr[1]["single_limit"];
                } else if($level == "all"){
                    return $upload_limit_arr[1]["all_limit"];
                }
                exit("<div class='error'>ERROR</div>");
            default:
                exit("<div class='error'>ERROR</div>");
        }
    }

    if(isset($pass, $user, $level, $files)) {
        switch($level){
            case "level1":
                if(Authenticate($user_arr, $user, $pass, "admin")){
                    UploadMultiple("../../uploads/".$level."/", $files);
                } else {
                    exit("<div class='error'>Keine Berechtigung</div>");
                }
                break;
            case "level2":
                if(Authenticate($user_arr, $user, $pass, "admin")){
                    UploadMultiple("../../uploads/".$level."/", $files);
                } else {
                    exit("<div class='error'>Keine Berechtigung</div>");
                }
                break;
            case "level3":
                if(Authenticate($user_arr, $user, $pass, "upload")){
                    UploadMultiple("../../uploads/".$level."/", $files);
                } else {
                    exit("<div class='error'>Keine Berechtigung</div>");
                }
                break;
            case "level4":
                if(Authenticate($user_arr, $user, $pass, "admin")){
                    UploadMultiple("../../uploads/".$level."/", $files);
                } else {
                    exit("<div class='error'>Keine Berechtigung</div>");
                }
                break;
            default:
                exit("<div class='error'>Interner Systemfehler</div>");
        }
    }

    function UploadMultiple($target_dir, $files){
        global $zip;
        global $filename;
        global $zipname;
        global $user;
        if(iterator_count(new FilesystemIterator("../../uploads/level1"))+iterator_count(new FilesystemIterator("../../uploads/level2"))+iterator_count(new FilesystemIterator("../../uploads/level3"))+iterator_count(new FilesystemIterator("../../uploads/level4")) > 100){
            exit("<div class='error'>Es existieren bereits mehr als 100 Uploads. Kontaktieren Sie den Administrator.</div>");
        }
        $file_count = count($files['name']);

        // Check total file size (post_max_size)
        if(array_sum($files["size"]) > GetUploadLimit($user, "all")){
            exit("<div class='error'>Ihr Uploadwunsch ist mit ".(array_sum($files["size"])/1000000)."MB zu groß.</div>");
        }
        if(array_sum($files["size"]) > GetUploadLimit("system", "all")){
            exit("<div class='error'>Ihr Uploadwunsch ist mit ".(array_sum($files["size"])/1000000)."MB zu groß.</div>");
        }

        if($zip == "on"){
            $newzip = new ZipArchive();
            if(isset($zipname) & !empty($zipname)){
                $zip_name = $target_dir . $zipname . ".zip";
                $zip_name_short = $zipname . ".zip";
            } else {
                $zip_name = $target_dir . time().".zip";
                $zip_name_short = time().".zip";
            }
            if($newzip->open($zip_name, ZIPARCHIVE::CREATE)!==TRUE) {
                exit("<div class='error'>Es gab einen Fehler beim Erstellen des ZIP Archives.</div><br>");
            }
        }

        // Loop through every file
        for( $i=0 ; $i < $file_count ; $i++ ) {
            if(isset($filename) && !empty($filename)){
                if($file_count == 1) {
                    $target_file = $target_dir . $filename . "." . pathinfo($files["name"][0], PATHINFO_EXTENSION);
                    $target_file_name = $filename . "." . pathinfo($files["name"][0], PATHINFO_EXTENSION);
                } else {
                    $target_file = $target_dir . $filename . "_$i" . "." . pathinfo($files["name"][$i], PATHINFO_EXTENSION);
                    $target_file_name = $filename . "_$i" . "." . pathinfo($files["name"][$i], PATHINFO_EXTENSION);
                }
            } else {
                $target_file = $target_dir . basename($files["name"][$i]);
                $target_file_name = basename($files["name"][$i]);
            }
            

            // Check if file already exists (no ZIP)
            if ($zip != "on" && file_exists($target_file)) {
                print("<div class='error'>".htmlspecialchars(basename($files["name"][$i]))." existiert bereits</div><br>");
                continue;
            }
            
            // Check individual file size (upload_max_filesize)
            if($files["size"][$i] > GetUploadLimit($user, "single")){
                print("<div class='error'>".htmlspecialchars(basename($files["name"][$i]))." ist mit ".($files["size"][$i]/1000000)."MB zu groß.</div><br>");
                continue;
            }
            if($files["size"][$i] > GetUploadLimit("system", "single")){
                print("<div class='error'>".htmlspecialchars(basename($files["name"][$i]))." ist mit ".($files["size"][$i]/1000000)."MB zu groß.</div><br>");
                continue;
            }
            
            // Upload (no ZIP)
            if($zip != "on") {
                if (move_uploaded_file($files["tmp_name"][$i], $target_file)) {
                    //print("post_max_size: ".ini_get('post_max_size')." upload_max_filesize: ".ini_get('upload_max_filesize')." tmp: ".$files["tmp_name"][$i]." target: ".$target_file);
                    print("<div class='success'>".htmlspecialchars($target_file_name)." wurde hochgeladen</div><br>");
                } else {
                    //print("post_max_size: ".ini_get('post_max_size')." upload_max_filesize: ".ini_get('upload_max_filesize')." tmp: ".$files["tmp_name"][$i]." target: ".$target_file);
                    print("<div class='error'>Es gab einen Fehler beim Upload von ".htmlspecialchars($target_file_name)."</div><br>");
                }
            }

            // ZIP
            else {
                $newzip->addFromString($target_file_name, file_get_contents($files["tmp_name"][$i]));
                $newzip->close();
                print("<div class='success'>".htmlspecialchars($zip_name_short)." wurde hochgeladen</div><br>");
            }
        }
    }
?>     
</div>
<script type="text/javascript">
    var zipname = document.getElementById("zipname");
    document.getElementById("zip").addEventListener("click", function(){
        if(this.checked){
            zipname.style.display = 'block';
        } else {
            zipname.style.display = 'none';
        }
    });
</script>
</body>
</html>