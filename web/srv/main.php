<?php

if (isset($_POST['pullUserData']))
    $sUsername = $_POST['pullUserData'];

if ($sUsername)
    $sFeedback = pullUserData ($sUsername);


echo $sFeedback;

function pullUserData ($sUsername) {
    $dbhost = 'localhost';
    $dbuser = 'jake_network';
    $dbpass = 'vvVN0EEADb4ZI';
    $db = "Network";
    $dbconnect = new mysqli($dbhost, $dbuser, $dbpass, $db);

    $stmt = $dbconnect->prepare("SELECT * FROM Users WHERE username=?");
    $stmt->bind_param("s", $sUsername);
    $stmt->execute();
    $tResult = $stmt->get_result();
    $row = $tResult->fetch_assoc();
    $stmt->close();
    $dbconnect->close();

    $objUserData = new stdClass();
    $objUserData->info = $row["info"];
    $objUserData->lastlogin = $row["lastlogin"];
    $objUserData->created = $row["created"];
    $objUserData->username = $row["username"];
    return json_encode($objUserData);
}


?>
