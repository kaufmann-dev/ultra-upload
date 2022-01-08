<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="style.css" rel="stylesheet">
    <link rel="icon" href="favicon.ico">
    <link rel="apple-touch-icon" href="favicon.ico">
    <title>Ultraupload</title>
    <script src="./howler.min.js"></script>
</head>
<body>
<?php
    $settings = json_decode(file_get_contents('../settings.json'), true);
    if($settings['power'] == 'on'){
        if(($settings['log'] == 'gets' && count($_GET) > 0) || $settings['log'] == 'everything'){
            $str = file_get_contents('../visitors.json');
            $arr = json_decode($str, true);
            $datetime = $arrne['datetime'] = date('d/m/Y G:i:s');
            $ip = $arrne['ip'] = $_SERVER['REMOTE_ADDR'];
            $ua = $arrne['user agent'] = $_SERVER['HTTP_USER_AGENT'];
            $data = "$datetime -- IP: [$ip] UA: [$ua]";
            $bruh = [];
            foreach ($_GET as $key => $value) {
                if(preg_match("/^[A-Za-z0-9]+$/", $value) && preg_match("/^[A-Za-z0-9]+$/", $key)) {
                    $bruh[$key] = $value;
                    $data .= " $key: [$value]";
                }
            }
            $data .= PHP_EOL;
            if(!empty($bruh)) {
                $arrne['gets'] = $bruh;
            }
            array_push( $arr['logs'], $arrne);
            $str = json_encode($arr, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);

            $logFile = fopen('../visitors.log','a');
            fwrite($logFile, $data);

            if (json_decode($str) != null)
            {
                $jsonFile = fopen('../visitors.json','w');
                fwrite($jsonFile, $str);
                fclose($jsonFile);
            }
            else
            {
                throw new ErrorException("invalid JSON");
            }
        }
    }
    if(isset($bruh['person'])) {
        echo "<div id='wrapperRick'><div id='bruh'>" . $bruh['person'] . " is a fucking idiot and just got rick rolled</div></div>";
    }
?>
<img src="rick.webp">
<script>
    var sound = new Howl({
        src: ['rickroll.opus'],
        onplayerror: function() {
            sound.once('unlock', function() {
            sound.play();
            });
        }
    });
    sound.play();
    //document.getElementById("myCheck").click(function(){ sound.play(); });
</script>
</body>
</html>
