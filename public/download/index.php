<?php
    if(isset($_POST['user'])){ $user = $_POST['user']; }
    if(isset($_POST['pass'])){ $pass = $_POST['pass']; }
    if(isset($_POST['delete'])){ $delete = $_POST['delete']; }
    if(isset($_POST['download'])){ $download = $_POST['download']; }
    
    $user_arr = json_decode(file_get_contents('../../users.json'), true)['users'];

    function Authenticate($arr, $user, $pwd, $right){
        foreach($arr as $lol){
            if($lol['username'] == $user && $lol['password'] == $pwd && in_array($right, $lol['rights'])){
                return true;
            }
        }
        return false;
    }

    function Download($page){
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header("Cache-Control: no-cache, must-revalidate");
        header("Expires: 0");
        header('Content-Disposition: attachment; filename="'.basename($page).'"');
        header('Content-Length: ' . filesize($page));
        header('Pragma: public');
        readfile($page);
        exit;
    }

    function PrintDirs($level){
        global $user;
        global $pass;
        $dirs = array_filter(glob("../../uploads/".$level."/*"), 'is_file');
        if(empty($dirs)) {
            echo("<div class='upfile'>Keine Uploads vorhanden</div>");
        } else{
            foreach ($dirs as $dirname) {
                echo "<div class='upfile'>
                    ".basename($dirname)."<br>
                    <span class='datespan'>".date("d.m.Y - H:i:s", filemtime($dirname))."</span><br>
                    <form method='POST' action=''><input type='submit' value='Herunterladen'><input type='hidden' name='user' value='".$user."'><input type='hidden' name='pass' value='".$pass."'><input type='hidden' name='download' value='../../uploads/".$level."/".basename($dirname)."'></form>
                    ".((pathinfo($dirname)['extension']=="txt")?"<button onclick='updateClipboard(`".file_get_contents($dirname)."`)'>Kopieren</button>":"")."
                    <form method='POST' action=''><input type='submit' value='Löschen'><input type='hidden' name='user' value='".$user."'><input type='hidden' name='pass' value='".$pass."'><input type='hidden' name='delete' value='../../uploads/".$level."/".basename($dirname)."'></form>
                </div>";
            }
        }
    }

    if(isset($download)){
        switch(dirname($download)){
            case "../../uploads/level1":
                Download($download);
            case "../../uploads/level2":
                if(isset($user, $pass) && Authenticate($user_arr, $user, $pass, "download")){
                    Download($download);
                } else {
                    exit("<div class='error'>Keine Berechtigung</div>");
                }
            case "../../uploads/level3":
            case "../../uploads/level4":
                if(isset($user, $pass) && Authenticate($user_arr, $user, $pass, "admin")){
                    Download($download);
                } else {
                    exit("<div class='error'>Keine Berechtigung</div>");
                }
            default:
                exit("<div class='error'>Interner Systemfehler</div>");
        }
    }
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ultraupload | Download</title>
    <link href="../style.css" rel="stylesheet">
    <link rel="icon" href="../favicon.ico">
    <link rel="apple-touch-icon" href="../favicon.ico">
</head>
<body class="lightblue"><div id="wrapper">
<?php
    if(!isset($user) || !isset($pass) || ($pass == "" && $user == "")) {?>
        <form method="POST" action="" class="darkblue">
            <label for="user">Benutzername</label><br>
            <input type="text" id="user" name="user" required>
            <div class="spacer"></div>
            <label for="pass">Kennwort</label><br>
            <input type="password" id="pass" name="pass" required>
            <div class="spacer"></div>
            <input autofocus type='submit' name='submit' value='Identifizieren'></input>
        </form>
    <?php }

    if(isset($delete, $user, $pass)){
        if(Authenticate($user_arr, $user, $pass, "admin")){
            unlink($delete);
        } else{
            print("<div class='error'>Keine Berechtigung</div>");
        }
    }

    echo"<h2>Ebene 1 - Öffentlich</h2>";
    PrintDirs("level1");

    if(isset($user, $pass) && Authenticate($user_arr, $user, $pass, "download")){
        echo"<h2>Ebene 2 - Berechtigte sehen</h2>";
        PrintDirs("level2");
    }
    
    if(isset($user, $pass) && Authenticate($user_arr, $user, $pass, "admin")){
        echo"<h2>Ebene 3 - Administrator sieht</h2>";
        PrintDirs("level3");

        echo"<h2>Ebene 4 - Reserviert</h2>";
        PrintDirs("level4");
    }
?>
</div>
<script>
    function updateClipboard(newClip) {
        navigator.clipboard.writeText(newClip).then(function() {
            /* clipboard successfully set */
        }, function() {
            /* clipboard write failed */
        });
    }
</script>
</body>
</html>
