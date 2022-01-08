<?php
    if(isset($_POST['user'])){ $user = $_POST['user']; }
    if(isset($_POST['user'])){ $pass = $_POST['pass']; }
    if(isset($_POST['download'])){ $download = $_POST['download']; }
    if(isset($_POST['delete'])){ $delete = $_POST['delete']; }
    if(isset($_POST['power'])){ $turn_power = $_POST['power']; }
    if(isset($_POST['log'])){ $go_log = $_POST['log']; }

    $user_arr = json_decode(file_get_contents('../../users.json'), true)['users'];
    $settings = json_decode(file_get_contents('../../settings.json'), true);

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
        header('Content-Disposition: attachment; filename="'.basename("../../$page").'"');
        header('Content-Length: ' . filesize("../../$page"));
        header('Pragma: public');
        readfile("../../$page");
        exit;
    }

    if(isset($download, $user, $pass) && Authenticate($user_arr, $user, $pass, "admin")){
        if($download == "visitors.log"){
            Download("visitors.log");
        } elseif($download == "visitors.json"){
            Download("visitors.json");
        }
    }

    function ResetFile($page){
        if($page=="visitors.log"){
            file_put_contents("../../visitors.log", "");
            print("<div class='success'>Erfolg</div>");
            return;
        } elseif($page=="visitors.json") {
            file_put_contents("../../visitors.json", '{"logs":[]}');
            print("<div class='success'>Erfolg</div>");
            return;
        }
        print("<div class='error'>Fehler</div>");
    }

    function TurnPower($power){
        global $settings;
        if($power == "on" && $settings['power'] == "off"){
            file_put_contents('../../settings.json', json_encode(array('power' => 'on','log' => $settings['log'])));
            $settings = json_decode(file_get_contents('../../settings.json'), true);
            print("<div class='success'>Erfolg</div>");
            return;
        } elseif($power == "off" && $settings['power'] == "on"){
            file_put_contents('../../settings.json', json_encode(array('power' => 'off','log' => $settings['log'])));
            $settings = json_decode(file_get_contents('../../settings.json'), true);
            print("<div class='success'>Erfolg</div>");
            return;
        }
        print("<div class='error'>Fehler</div>");
    }

    function GoLog($log){
        global $settings;
        if($log == "everything" && $settings['log'] == "gets"){
            file_put_contents('../../settings.json', json_encode(array('power' => $settings['power'],'log' => 'everything')));
            $settings = json_decode(file_get_contents('../../settings.json'), true);
            print("<div class='success'>Erfolg</div>");
            return;
        } elseif($log == "gets" && $settings['log'] == "everything"){
            file_put_contents('../../settings.json', json_encode(array('power' => $settings['power'],'log' => 'gets')));
            $settings = json_decode(file_get_contents('../../settings.json'), true);
            print("<div class='success'>Erfolg</div>");
            return;
        }
        print("<div class='error'>Fehler</div>");
    }
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ultraupload | Admin</title>
    <link href="../style.css" rel="stylesheet">
    <link rel="icon" href="../favicon.ico">
    <link rel="apple-touch-icon" href="../favicon.ico">
</head>
<body class="lightblue">
<div id="wrapper">
<?php
    if(isset($user) && isset($pass) ){
        if(Authenticate($user_arr, $user, $pass, "admin")) {
            if(isset($turn_power)){
                TurnPower($turn_power, $settings);
            }
            if(isset($go_log)){
                GoLog($go_log, $settings);
            }
            if(isset($delete)){
                ResetFile($delete);
            }

            echo"<h2>Loggingeinstellungen</h2>";
            echo"<div class='upfile'>";
            if($settings['log'] == "everything"){
                echo"<form method='POST' action=''><input type='submit' value='GETs loggen'><input type='hidden' name='log' value='gets'><input type='hidden' name='user' value='".$user."'><input type='hidden' name='pass' value='".$pass."'></form>";
                echo" Derzeit: <b>Alles</b> wird geloggt";
            } else{
                echo"<form method='POST' action=''><input type='submit' value='Alles loggen'><input type='hidden' name='log' value='everything'><input type='hidden' name='user' value='".$user."'><input type='hidden' name='pass' value='".$pass."'></form>";
                echo' Derzeit: Nur Anfragen mit <b style="display: inline-block;">$_GET > 0</b> werden geloggt';
            }
            echo"</div><div class='upfile'>";
            if($settings['power'] == "on") {
                echo"<form method='POST' action=''><input type='submit' value='Deaktivieren'><input type='hidden' name='power' value='off'><input type='hidden' name='user' value='".$user."'><input type='hidden' name='pass' value='".$pass."'></form>";
                echo" Derzeit: Logging ist <b>aktiviert</b>";
            } else{
                echo"<form method='POST' action=''><input type='submit' value='Aktivieren'><input type='hidden' name='power' value='on'><input type='hidden' name='user' value='".$user."'><input type='hidden' name='pass' value='".$pass."'></form>";
                echo" Derzeit: Logging ist <b>deaktiviert</b>";
            }
            echo"</div>";

            echo"<h2>Logs</h2>";
            echo"<div class='logs' style='background: #9ac9ff;'><pre>";
            include("../../visitors.log");
            echo("</pre></div>");
            echo"<div class='upfile'><button onClick='window.location.href=window.location.href'>Aktualisieren</button></div>";

            echo"<h2>Dateien</h2>";
            echo"<div class='upfile'>visitors.log <form method='POST' action=''><input type='submit' value='Herunterladen'><input type='hidden' name='download' value='visitors.log'><input type='hidden' name='user' value='".$user."'><input type='hidden' name='pass' value='".$pass."'></form> <form method='POST' action=''><input type='submit' value='Zurücksetzen'><input type='hidden' name='delete' value='visitors.log'><input type='hidden' name='user' value='".$user."'><input type='hidden' name='pass' value='".$pass."'></form></div>";
            echo"<div class='upfile'>visitors.json <form method='POST' action=''><input type='submit' value='Herunterladen'><input type='hidden' name='download' value='visitors.json'><input type='hidden' name='user' value='".$user."'><input type='hidden' name='pass' value='".$pass."'></form> <form method='POST' action=''><input type='submit' value='Zurücksetzen'><input type='hidden' name='delete' value='visitors.json'><input type='hidden' name='user' value='".$user."'><input type='hidden' name='pass' value='".$pass."'></form></div>";
        } else{ ?>
            <form method="POST" action="" class="darkblue">
                <label for="user">Benutzername</label><br>
                <input type="text" id="user" name="user" required>
                <div class="spacer"></div>
                <label for="pass">Kennwort</label><br>
                <input type="password" id="pass" name="pass" required>
                <div class="spacer"></div>
                <input autofocus type="submit" name='submit' value='Identifizieren'></input>
            </form>
        <?php
            print("<div class='error'>Keine Berechtigung</div>");
        }
    } else { ?>
        <form method="POST" action="" class="darkblue">
            <label for="user">Benutzername</label><br>
            <input type="text" id="user" name="user" required>
            <div class="spacer"></div>
            <label for="pass">Kennwort</label><br>
            <input type="password" id="pass" name="pass" required>
            <div class="spacer"></div>
            <input autofocus type="submit" name='submit' value='Identifizieren'></input>
        </form>
    <?php } ?>
</div>
</body>
</html>