<?php

if (isset($_POST['pullUserData']))
    $sUsername = $_POST['pullUserData'];
if (isset($_POST['editAccountData']))
    $jsonAccountData = $_POST['editAccountData'];
if (isset($_POST['pullFriends']))
    $nId = $_POST['pullFriends'];
if (isset($_POST['checkFriendship']))
    $jsonFriendship = $_POST['checkFriendship'];
if (isset($_POST['removeFriendship']))
    $jsonRemoveFriendship = $_POST['removeFriendship'];

if ($sUsername)
    $sFeedback = pullUserData ($sUsername);
else if ($jsonAccountData)
    $sFeedback = editAccountData ($jsonAccountData);
else if ($nId)
    $sFeedback = pullFriends ($nId);
else if ($jsonFriendship)
    $sFeedback = checkFriendship ($jsonFriendship);
else if ($jsonRemoveFriendship)
    $sFeedback = removeFriendship ($jsonRemoveFriendship);

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
    $objUserData->id = $row["id"];
    $objUserData->info = $row["info"];
    $objUserData->lastlogin = $row["lastlogin"];
    $objUserData->created = $row["created"];
    $objUserData->username = $row["username"];
    return json_encode($objUserData);
}

function editAccountData ($jsonAccountData) {
    $objData = json_decode($jsonAccountData);
    $sSQL = "UPDATE Users SET info='" . $objData->info . "' WHERE id=" . $objData->id;
    return QueryDB ($sSQL);
}

function pullFriends ($nId) {
    $sSQL = "SELECT * FROM Friends WHERE a=" . $nId . " OR b=". $nId;
    $tResult = QueryDB ($sSQL);
    $nRows = $tResult->num_rows;
    $objFriends = [];
    if ($nRows > 0) {
        for ($x=0; $x < $nRows; $x++) {
            $row = $tResult->fetch_assoc();
            if ($row["a"] == $nId)
                $nCurr = $row["b"];
            else
                $nCurr = $row["a"];

            $sFriendPull = "SELECT * FROM Users WHERE id=" . $nCurr;
            $tResultFriend = QueryDB ($sFriendPull);
            $friendsrow = $tResultFriend->fetch_assoc();

            $objFriends[$x] = new stdClass();
            $objFriends[$x]->lastlogin = $friendsrow["lastlogin"];
            $objFriends[$x]->info = $friendsrow["info"];
            $objFriends[$x]->username = $friendsrow["username"];
        }
    }
    return json_encode($objFriends);
}

function checkFriendship ($jsonFriendship) {
    $objFriendship = json_decode($jsonFriendship);
    $sSQL = "SELECT * FROM Friends WHERE a=" . $objFriendship->userId . " AND b=" . $objFriendship->friendId . " OR a=" . $objFriendship->friendId . " AND b=" . $objFriendship->userId;
    $tResult = QueryDB ($sSQL);
    return $tResult->num_rows;
}

function removeFriendship ($jsonRemoveFriendship) {
    $objFriendship = json_decode($jsonRemoveFriendship);
    $sSQL = "DELETE FROM Friends WHERE a=" . $objFriendship->userId . " AND b=" . $objFriendship->friendId . " OR a=" . $objFriendship->friendId . " AND b=" . $objFriendship->userId;
    return QueryDB ($sSQL);
}

function QueryDB ($sSQL) {
    $dbhost = 'localhost';
    $dbuser = 'jake_network';
    $dbpass = 'vvVN0EEADb4ZI';
    $db = "Network";
    $dbconnect = new mysqli($dbhost, $dbuser, $dbpass, $db);
    $Result = $dbconnect->query($sSQL);
    $dbconnect->close();
    return $Result;
}


?>
