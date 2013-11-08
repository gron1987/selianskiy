<?php
/**
 * Created by PhpStorm.
 * User: vlogvinskiy
 * Date: 11/7/13
 * Time: 9:17 AM
 */

$baseCSV = ($argv[1]) ? __DIR__."/".$argv[1] : __DIR__."/base.csv";

if(!file_exists(__DIR__."/base.db")){
    $dbh = new PDO("sqlite:".__DIR__."/base.db");
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $createTableSql = "
    CREATE TABLE base(
      title TEXT,
      composer TEXT,
      trackID TEXT,
      PRIMARY KEY(trackID ASC)
    )
    ";
    echo PHP_EOL."CREATE BASE TABLE".PHP_EOL;
    $dbh->query($createTableSql);
}else{
    $dbh = new PDO("sqlite:".__DIR__."/base.db");
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}

if(is_readable($baseCSV)){
    $fileHandler = fopen($baseCSV,"r");
    $line = 0;
    echo "START ADD DATA INTO BASE".PHP_EOL;
    if($fileHandler){
        while(($buffer = fgets($fileHandler,4096)) !== false ){
            $line++;
            if($line % 100 === 0){
                echo "$line-s ADDED".PHP_EOL;
            }
            if($line === 1){
                // skip header section
                echo "SKIP HEADER".PHP_EOL;
                continue;
            }
            // Start add data into sqlite
            $dataFromBase = str_getcsv(str_replace("\n","",$buffer));
            $sqlInsert = "INSERT INTO base (title,composer,trackID) VALUES(?,?,?)";
            $sth = $dbh->prepare($sqlInsert);
            $sth->bindValue(1, $dataFromBase[0], PDO::PARAM_STR);
            $sth->bindValue(2, $dataFromBase[1], PDO::PARAM_STR);
            $sth->bindValue(3, $dataFromBase[sizeof($dataFromBase)-1], PDO::PARAM_STR);
            try{
                $sth->execute();
            }catch (PDOException $e){
                echo "SKIP ROW $buffer Exception ". $e->getMessage().PHP_EOL;
            }
        }
    }
    echo "END ADD DATA TO BASE".PHP_EOL;
}else{
    echo "[ERROR] FILE $baseCSV IS NOT READABLE !!!";
}