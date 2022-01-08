<?php
    if(isset($_POST['user'])){ $user = $_POST['user']; }
    if(isset($_POST['pass'])){ $pass = $_POST['pass']; }
    if(isset($_POST['delete'])){ $delete = $_POST['delete']; }
    if(isset($_POST['n_usr'])){ $n_usr = $_POST['n_usr']; }
    if(isset($_POST['n_pwd'])){ $n_pwd = $_POST['n_pwd']; }
    if(isset($_POST['n_rdl'])){ $n_rdl = $_POST['n_rdl']; }
    if(isset($_POST['n_rup'])){ $n_rup = $_POST['n_rup']; }
    if(isset($_POST['n_right'])){ $n_right = $_POST['n_right']; }
    if(isset($_POST['n_single'])){ $n_single = $_POST['n_single']; }
    if(isset($_POST['n_all'])){ $n_all = $_POST['n_all']; }

    $user_arr = json_decode(file_get_contents('../../users.json'), true)['users'];
    $ini = "/etc/php/7.4/fpm/php.ini";

    function Authenticate($arr, $user, $pwd, $right){
        foreach($arr as $lol){
            if($lol['username'] == $user && $lol['password'] == $pwd && in_array($right, $lol['rights'])){
                return true;
            }
        }
        return false;
    }

    function DeleteUser($file, $user){
        $arr = json_decode(file_get_contents($file), true)['users'];
        foreach ($arr as $key => $value) {
            if (in_array($user, $value)) {
                unset($arr[$key]);
            }
        }
        $lol['users'] = $arr;
        file_put_contents($file,json_encode($lol, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
    }

    function CreateUser($file, $user, $pwd, $rights){
        $arr = json_decode(file_get_contents($file), true)['users'];
        $muh_user['username'] = $user;
        $muh_user['password'] = $pwd;
        $muh_user['rights'] = $rights;
        array_push($arr, $muh_user);
        $gringo = array('users'=>$arr);
        file_put_contents($file,json_encode($gringo, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
    }

    /*function PrintIniVariable($file, $var) {
        $lines = file($file, FILE_IGNORE_NEW_LINES);
        $sl = strlen($var);
        foreach ($lines as $key => $line) {
            if(substr(trim($line),0, $sl) == $var){
                return substr(trim($line),$sl + 3);
            }
        }
    }*/

    function UpdateUploadLimits($file, $right, $single, $all){
        global $ini;
        if($right == "system"){
            $lines = file($ini, FILE_IGNORE_NEW_LINES);
            foreach ($lines as $key => $line) {
                if(substr(trim($line),0, 19) == 'upload_max_filesize'){
                    $lines[$key] = "upload_max_filesize = ".$single * 1000000;
                }
                if(substr(trim($line),0, 13) == 'post_max_size'){
                    $lines[$key] = "post_max_size = ".$all * 1000000;
                }
            }
            $lol = implode("
", $lines);
            file_put_contents($ini,$lol);
        }
        $arr = json_decode(file_get_contents($file), true)['rights'];
        foreach($arr as $key => $field){
            if($field["right"] == $right){
                $arr[$key]["single_limit"] = $single * 1000000;
                $arr[$key]["all_limit"] = $all * 1000000;
            }
        }
        $gringo['rights'] = $arr;
        file_put_contents($file,json_encode($gringo, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));

        exec("systemctl reload php7.4-fpm");
        //exec("whoami", $output);
        //print_r($output);
    }

    function PrintUsers($file){
        global $user;
        global $pass;
        $arr = json_decode(file_get_contents($file), true)['users'];
        echo"<table><thead><tr><th>Benutzer Name</th><th>Kennwort</th><th>Rechte</th><th>Löschen</th></tr></thead><tbody>";
        foreach ($arr as $fortnite) {
            echo"<tr><td class='wb-ba'>".$fortnite['username']."</td><td class='td_pw wb-ba'>".$fortnite['password']."<td>";
            foreach($fortnite['rights'] as $right){
                echo $right." ";
            }
            echo"</td><td><form class='formbutton' method='POST' action=''><input type='submit' value='Löschen'><input type='hidden' name='user' value='".$user."'><input type='hidden' name='pass' value='".$pass."'><input type='hidden' name='delete' value='".$fortnite['username']."'></form></td></tr>";
        }
        echo"</tbody></table>";
    }

    function PrintUploadLimits($file){
        global $user;
        global $pass;
        global $ini;
        $arr = json_decode(file_get_contents($file), true)['rights'];
        echo"<table><thead><tr><th>Benutzer Gruppe</th><th>Datei Limit (MB)</th><th>Gesamtes Limit (MB)</th><th>Update</th></thead><tbody>";
        foreach($arr as $fortnite){
            echo "<tr><form method='POST' action=''><td>".$fortnite['right']."<input type='hidden' name='n_right' type='text' value='".$fortnite['right']."'></td><td><input name='n_single' type='number' value='".(($fortnite['single_limit'])/1000000)."'></input></td><td><input name='n_all' type='number' value='".(($fortnite['all_limit'])/1000000)."'></input></td><td><div class='formbutton'><input type='submit' value='Update'><input type='hidden' name='user' value='".$user."'><input type='hidden' name='pass' value='".$pass."'></div></form></tr>";
        }
        echo"</tbody></table>";
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
            if(isset($delete)){
                DeleteUser('../../users.json',$delete);
            }
            if(isset($n_usr) && isset($n_pwd)){
                if(isset($n_rdl) && isset($n_rup)){
                    CreateUser('../../users.json',$n_usr,$n_pwd,array("download","upload"));
                } elseif(isset($n_rdl)) {
                    CreateUser('../../users.json',$n_usr,$n_pwd,array("download"));
                } elseif(isset($n_rup)){
                    CreateUser('../../users.json',$n_usr,$n_pwd,array("upload"));
                } else{
                    CreateUser('../../users.json',$n_usr,$n_pwd,array());
                }
            }

            echo"<h2>Benutzer</h2>";
            PrintUsers('../../users.json'); 
            
            echo"<h2>Upload-Limits</h2>";
            PrintUploadLimits('../../upload_limits.json');

            ?>

            <h2>Benutzer erstellen</h2>
            <form method='POST' action='' class='darkblue m-0'>
                <input type='hidden' name='user' value='<?php echo $user; ?>'>
                <input type='hidden' name='pass' value='<?php echo $pass; ?>'>

                <label for='n_usr'>Benutzername</label><br>
                <input type='text' id='n_usr' name='n_usr' required autocomplete="off">
                <div class='spacer'></div>
                <label for='n_pwd'>Kennwort</label><br>
                <input type='password' id='n_pwd' name='n_pwd' required autocomplete="new-password">
                <div class='spacer'></div>
                <label for='new_rights'>Rechte</label><br>
                <input type='checkbox' name='n_rdl' value='true'>
                <label for='n_rdl'>Herunterladen</label><br>
                <input type='checkbox' name='n_rup' value='true'>
                <label for='n_rup'>Hochladen</label>
                <div class='spacer'></div>
                <input autofocus type='submit' name='submit' value='Erstellen'></input>
            </form> <?php

            if(isset($n_right)){
                UpdateUploadLimits('../../upload_limits.json', $n_right, $n_single, $n_all);
            }
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
