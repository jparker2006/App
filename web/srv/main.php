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
if (isset($_POST['sendFriendRequest']))
    $jsonIds = $_POST['sendFriendRequest'];
if (isset($_POST['acceptRequest']))
    $jsonNewFriendship = $_POST['acceptRequest'];

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
else if ($jsonIds)
    $sFeedback = sendFriendRequest ($jsonIds);
else if ($jsonNewFriendship)
    $sFeedback = acceptRequest ($jsonNewFriendship);

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
    $dbhost = 'localhost';
    $dbuser = 'jake_network';
    $dbpass = 'vvVN0EEADb4ZI';
    $db = "Network";
    $dbconnect = new mysqli($dbhost, $dbuser, $dbpass, $db);

    $sSQL = "SELECT * FROM Friends WHERE a=" . $nId . " OR b=". $nId;
    $tResult = $dbconnect->query($sSQL);
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
            $tResultFriend = $dbconnect->query($sFriendPull);
            $friendsrow = $tResultFriend->fetch_assoc();

            $objFriends[$x] = new stdClass();
            $objFriends[$x]->lastlogin = $friendsrow["lastlogin"];
            $objFriends[$x]->info = $friendsrow["info"];
            $objFriends[$x]->username = $friendsrow["username"];
        }
    }
    $dbconnect->close();
    return json_encode($objFriends);
}

function checkFriendship ($jsonFriendship) {
    $objFriendship = json_decode($jsonFriendship);

    $dbhost = 'localhost';
    $dbuser = 'jake_network';
    $dbpass = 'vvVN0EEADb4ZI';
    $db = "Network";
    $dbconnect = new mysqli($dbhost, $dbuser, $dbpass, $db);

    $sSQL = "SELECT * FROM Friends WHERE a=" . $objFriendship->userId . " AND b=" . $objFriendship->friendId . " OR a=" . $objFriendship->friendId . " AND b=" . $objFriendship->userId;
    $tResult = $dbconnect->query($sSQL);

    $objData = new stdClass();
    $objData->id = $objFriendship->friendId;
    $objData->rows = $tResult->num_rows;
    if (1 == $objData->rows)
        return json_encode($objData);
    $sRequstCheck = "SELECT * FROM Inbox WHERE type=0 AND user=" . $objFriendship->userId;
    $tResult = $dbconnect->query($sRequstCheck);
    $objData->requested = $tResult->num_rows;
    $dbconnect->close();
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
            $objInbox[$x]->otherID = $row["other"];
            $objInbox[$x]->received = $row["created"];
            $objInbox[$x]->type = $row["type"];
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
    $dbhost = 'localhost';
    $dbuser = 'jake_network';
    $dbpass = 'vvVN0EEADb4ZI';
    $db = "Network";
    $dbconnect = new mysqli($dbhost, $dbuser, $dbpass, $db);

    $sSQL = "SELECT a, b FROM Friends WHERE a=" . $nExploreId . " OR b=". $nExploreId;
    $tResult = $dbconnect->query($sSQL);
    $nRows = $tResult->num_rows;
    $objData = [];
    $aFriends = getFriendsIDs ($nExploreId);
    $nIndex = 0;
    if ($nRows > 0) {
        for ($x=0; $x<$nRows; $x++) {
            $row = $tResult->fetch_assoc();
            if ($row["a"] == $nExploreId)
                $nCurr = $row["b"];
            else
                $nCurr = $row["a"];
            $sFriendPull = "SELECT a, b FROM Friends WHERE a=" . $nCurr . " OR b=". $nCurr;
            $tResultFriend = $dbconnect->query($sFriendPull);
            $nFriendRows = $tResultFriend->num_rows;
            if ($nFriendRows > 0) {
                for ($y=0; $y < $nFriendRows; $y++) {
                    $friendsrow = $tResultFriend->fetch_assoc();
                    $a = $friendsrow["a"];
                    $b = $friendsrow["b"];

                    if ($a == $nCurr && !in_array($b, $aFriends))
                        $currentFriendId = $b;
                    else if (!in_array($a, $aFriends))
                        $currentFriendId = $a;
                    else
                        continue;

                    if ($currentFriendId == $nExploreId) continue;

                    $bIn = false;
                    for ($z=0; $z<count($objData); $z++) {
                        if ($objData[$z]->id == $currentFriendId) {
                            $objData[$z]->n++;
                            $bIn = true;
                            break;
                        }
                    }

                    if ($bIn) continue;

                    $sFriendPullData = "SELECT username, info, lastlogin FROM Users WHERE id=" . $currentFriendId;
                    $tResultFriendData = $dbconnect->query($sFriendPullData);
                    $friendsdatarow = $tResultFriendData->fetch_assoc();

                    $objCurrData = new stdClass();
                    $objCurrData->lastlogin = $friendsdatarow["lastlogin"];
                    $objCurrData->info = $friendsdatarow["info"];
                    $objCurrData->username = $friendsdatarow["username"];

                    $objData[$nIndex] = new stdClass();
                    $objData[$nIndex]->id = $currentFriendId;
                    $objData[$nIndex]->json = json_encode($objCurrData);
                    $objData[$nIndex]->n = 1;
                    $nIndex++;
                }
            }
        }
    }
    $dbconnect->close();
    return json_encode($objData);
}

function sendFriendRequest ($jsonIds) {
    $objIds = json_decode($jsonIds);
    $sSQL = "INSERT INTO Inbox(user, data, type, other) VALUES(" . $objIds->otherUser . ", '" . $objIds->data . "', 0, " . $objIds->id . ")";
    return QueryDB ($sSQL);
}

function acceptRequest ($jsonNewFriendship) {
    $objNewFriendship = json_decode($jsonNewFriendship);
    $sSQL = "INSERT INTO Friends(a, b) VALUES(" . $objNewFriendship->a . ", " . $objNewFriendship->b . ")";
    QueryDB($sSQL);
    $sSQL = "DELETE FROM Inbox WHERE type=0 AND user=" . $objNewFriendship->a . " OR ". $objNewFriendship->b;
    return QueryDB($sSQL);
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
