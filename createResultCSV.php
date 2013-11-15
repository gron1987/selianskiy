<?php
/**
 * Created by PhpStorm.
 * User: vlogvinskiy
 * Date: 11/7/13
 * Time: 9:35 AM
 */

$dbh = new PDO("sqlite:".__DIR__."/base.db");
$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

if(is_readable(__DIR__."/from.csv")){
    $fileHandler = fopen(__DIR__."/from.csv","r");
    if(file_exists(__DIR__."/out.csv")){
        unlink(__DIR__."/out.csv");
    }
    $fileResultHandler = fopen(__DIR__."/out.csv","w+");
    $line = 0;
    echo "START FINDING MATCHES".PHP_EOL;
    if($fileHandler){
        $line = 0;
        while(($buffer = fgets($fileHandler,4096)) !== false ){
            $line++;
            if($line === 1){
                // skip header
                continue;
            }

            $data = str_getcsv(str_replace("\n","",$buffer));
            $name = $data[2];
            preg_match("/(.*?)\d+_\d+.*?\./",$name,$name);
            $name = str_replace("_"," ",$name[1]);
            $name = trim(preg_replace("/(A MH|B MH|MH|A KPM|KPM|JM)$/","",trim($name)));
            $name = addslashes(strtoupper($name));
            $sqlName = "SELECT * FROM base WHERE title like '%$name%'";
            $sth = $dbh->query($sqlName);
            $res = $sth->fetchAll(PDO::FETCH_ASSOC);
            if(!empty($res)){
                echo "FIND BY NAME LINE $line ".$res[0]['title'].PHP_EOL;
                $csvLine = str_getcsv($buffer);
                $resultData = array(
                    '',
                    '',
                    '',
                    $res[0]['title'],
                    $res[0]['composer'],
                    $res[0]['composer'],
                    $csvLine[3],
                    'да',
                    'нет',
                    'Comp Music Publishing',
                    'Comp Music Publishing',
                );
                fputcsv($fileResultHandler,$resultData);
            }else{
                preg_match("/.*?(\d+)_(\d+).*?\./",$data[2],$trackID);
                $trackIDFull = $trackID[1]."_".$trackID[2];
                $trackIDWithZero = $trackID[1]."_0".$trackID[2];
                $sqlTrackID = "SELECT * FROM base WHERE trackID like '%$trackIDFull%' or trackID like '%$trackIDWithZero%'";
                $sthTrackID = $dbh->query($sqlTrackID);
                $resultTrackID = $sthTrackID->fetchAll(PDO::FETCH_ASSOC);
                if(!empty($resultTrackID)){
                    echo "FIND BY ID $line ".$resultTrackID[0]['title'].PHP_EOL;
                    $csvLine = str_getcsv($buffer);
                    $resultData = array(
                        '',
                        '',
                        '',
                        $resultTrackID[0]['title'],
                        $resultTrackID[0]['composer'],
                        $resultTrackID[0]['composer'],
                        $csvLine[3],
                        'да',
                        'нет',
                        'Comp Music Publishing',
                        'Comp Music Publishing',
                    );
                    fputcsv($fileResultHandler,$resultData);
                }else{
                    echo "!!! NOT FOUND $line !!! $buffer".PHP_EOL;
                }
            }
        }
    }
}