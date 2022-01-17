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
if (isset($_POST['pullInbox']))
    $nInboxId = $_POST['pullInbox'];
if (isset($_POST['exploreFriends']))
    $nExploreId = $_POST['exploreFriends'];

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
else if ($nInboxId)
    $sFeedback = pullInbox ($nInboxId);
else if ($nExploreId)
    $sFeedback = exploreFriends ($nExploreId);

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

    $objData = new stdClass();
    $objData->id = $objFriendship->friendId;
    $objData->rows = $tResult->num_rows;
    return json_encode($objData);
}

function removeFriendship ($jsonRemoveFriendship) {
    $objFriendship = json_decode($jsonRemoveFriendship);
    $sSQL = "DELETE FROM Friends WHERE a=" . $objFriendship->userId . " AND b=" . $objFriendship->friendId . " OR a=" . $objFriendship->friendId . " AND b=" . $objFriendship->userId;
    return QueryDB ($sSQL);
}

function pullInbox ($nInboxId) {
    $sSQL = "SELECT * FROM Inbox WHERE user=" . $nInboxId;
    $tResult = QueryDB ($sSQL);
    $nRows = $tResult->num_rows;
    $objInbox = [];
    if ($nRows > 0) {
        for ($x=0; $x < $nRows; $x++) {
            $row = $tResult->fetch_assoc();
            $objInbox[$x] = new stdClass();
            $objInbox[$x]->data = $row["data"];
            $objInbox[$x]->received = $row["created"];
        }
    }
    return json_encode($objInbox);
}

function getFriendsIDs ($nId) {
    $sSQL = "SELECT * FROM Friends WHERE a=" . $nId . " OR b=". $nId;
    $tResult = QueryDB ($sSQL);
    $nRows = $tResult->num_rows;
    $objFriends = [];
    if ($nRows > 0) {
        for ($x=0; $x < $nRows; $x++) {
            $row = $tResult->fetch_assoc();
            if ($row["a"] == $nId)
                $objFriends[] = $row["b"];
            else
                $objFriends[] = $row["a"];
        }
    }
    return $objFriends;
}

function exploreFriends ($nExploreId) {
    $sSQL = "SELECT * FROM Friends WHERE a=" . $nExploreId . " OR b=". $nExploreId;
    $tResult = QueryDB ($sSQL);
    $nRows = $tResult->num_rows;
    $objFriends = [];
    $aFriends = getFriendsIDs ($nExploreId);
    $nIndex = 0;
    if ($nRows > 0) {
        for ($x=0; $x < $nRows; $x++) {
            $row = $tResult->fetch_assoc();
            if ($row["a"] == $nExploreId)
                $nCurr = $row["b"];
            else
                $nCurr = $row["a"];
            $sFriendPull = "SELECT * FROM Friends WHERE a=" . $nCurr . " OR b=". $nCurr . " AND NOT a=" . $nExploreId . " AND NOT b=" . $nExploreId;
            $tResultFriend = QueryDB ($sFriendPull);
            $nFriendRows = $tResultFriend->num_rows;
            if ($nFriendRows > 0) {
                for ($y=0; $y < $nFriendRows; $y++) {
                    $friendsrow = $tResultFriend->fetch_assoc();
                    if ($friendsrow["a"] == $nCurr && !in_array($friendsrow["b"], $aFriends)) { // you dotn have to do this for each friend (could be faster)
                        $sFriendPullData = "SELECT * FROM Users WHERE id=" . $friendsrow["b"];
                        $tResultFriendData = QueryDB ($sFriendPullData);
                        $friendsdatarow = $tResultFriendData->fetch_assoc();
                        $objFriends[$nIndex] = new stdClass();
                        $objFriends[$nIndex]->lastlogin = $friendsdatarow["lastlogin"];
                        $objFriends[$nIndex]->info = $friendsdatarow["info"];
                        $objFriends[$nIndex]->username = $friendsdatarow["username"];
                        $objFriends[$nIndex] = json_encode($objFriends[$nIndex]);
                        $nIndex++;
                    }
                    else if (!in_array($friendsrow["a"], $aFriends)) {
                        $sFriendPullData = "SELECT * FROM Users WHERE id=" . $friendsrow["a"];
                        $tResultFriendData = QueryDB ($sFriendPullData);
                        $friendsdatarow = $tResultFriendData->fetch_assoc();
                        $objFriends[$nIndex] = new stdClass();
                        $objFriends[$nIndex]->lastlogin = $friendsdatarow["lastlogin"];
                        $objFriends[$nIndex]->info = $friendsdatarow["info"];
                        $objFriends[$nIndex]->username = $friendsdatarow["username"];
                        $objFriends[$nIndex] = json_encode($objFriends[$nIndex]);
                        $nIndex++;
                    }
                }
            }
        }
    }
    return json_encode($objFriends);
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
