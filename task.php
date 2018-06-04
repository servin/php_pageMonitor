<?php
$version = "1.00";
#params
$address = "http://erickservin.com/";
$notificationSMS = "2245720211@messaging.sprintpcs.com";
$notificationEmail2 = "me@erickservin.com";



$handle = fopen($address, "rb");
$contents = stream_get_contents($handle);
fclose($handle);

$newChecksum = md5($contents);

echo ("starting page change monitor \n");
echo ("version: " . $version . "\n");
$date = date('Y-m-d H:i:s');
echo ("Running Date Time: " . $date . "\n");

#database conection
	$db_host = "localhost";
	$db_name = "dbname";
	$db_user = "dbuer";
    $db_pass = "password";
    

    try{
					
        $db_con = new PDO("mysql:host={$db_host};dbname={$db_name}",$db_user,$db_pass);
        $db_con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        echo("-> Stablished connection \n");
    }
    catch(PDOException $e){
        echo $e->getMessage();
        echo("[!] Connection error");
    }
    try {
        echo ("-->Reading from database \n");
        $address_clean = clean($address); 
        $q = ("select * from changes where address = '$address_clean'  order by timestamp desc limit 1");
        $stmt = $db_con->prepare($q);
        $stmt->execute();
        $row = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $count = $stmt->rowCount();
        
       

    }
    catch(PDOException $e){
        echo $e->getMessage();
        echo("[!] Connection error");
        exit();
    }




    if($count <> 1){
       
        try{
            echo("->Writing to Database");
    
            $q = ("INSERT INTO changes (address ,regex) 
                    VALUES (:address, :regex);");
            $stmt = $db_con->prepare($q);
            $stmt->bindParam(":address", $address_clean, PDO::PARAM_STR);
            $stmt->bindParam(":regex", $newChecksum, PDO::PARAM_STR);
            $stmt->execute();
            echo ('-->page monitor has been started\n');
            
            exit();
    
        }
        catch(PDOException $e){
            echo $e->getMessage();
        }
               
      

        }
    else{
        $oldChecksum =  $row[0]['regex'];
        echo ("--->previous record found \n");
    }




echo("---->new Regex " . $newChecksum ."\n" );
echo("---->old Regex " . $oldChecksum . "\n");


if($newChecksum == $oldChecksum) {
    echo "Result: ---WEBSITE HAS NOT BEEN UPDATED--- \n";
} else {
    echo "Result:---WEBSITE UPDATED--- \n";
    echo ("[!] go to " . $address . " to see what's new.");



try{
        
        
        
        $from = "SMS <sms@erickservin.com>";
        $message = "Changes on the website \n" . $address;
        $headers = 'From: '. $from . "\r\n";
        mail($notificationSMS , '', $message, $headers); 
        echo("sms Sent\n");
        
       

        $headers =  'MIME-Version: 1.0' . "\r\n";
        $headers .= 'From: '. $from . "\r\n";
        $headers .= 'Content-type: text/html; charset=UTF-8' . "\r\n";  
        $message = "Changes on the website 🐶 </br> " . $address; 
        mail($notificationEmail2 , 'Changes on web', $message, $headers);
        echo("mail Sent\n");


        
    }
    catch (Exception $e) {
        echo 'error mailing: ',  $e->getMessage(), "\n";

    };



    try{
        echo("\n--->Writing new regex to database\n");

        $q = ("INSERT INTO changes (address ,regex) 
                VALUES (:address, :regex);");
        $stmt = $db_con->prepare($q);
        $stmt->bindParam(":address", $address_clean, PDO::PARAM_STR);
        $stmt->bindParam(":regex", $newChecksum, PDO::PARAM_STR);
        $stmt->execute();
        echo ('-->page monitor has been started\n');
        

    }
    catch(PDOException $e){
        echo $e->getMessage();
    }
           
    ## insert last checksum

}


function clean($string) {
    $string = str_replace(' ', '-', $string); // Replaces all spaces with hyphens.
    $string = preg_replace('/[^A-Za-z0-9\-]/', '-', $string); // Removes special chars.
 
    return preg_replace('/-+/', '-', $string); // Replaces multiple hyphens with single one.
 }



?>