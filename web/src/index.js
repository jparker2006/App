"use strict";

var g_objUserData = {};

var onload = function() {
    let UN = getCookie('UN');
    let PW = getCookie('PW');
    if (UN && PW) {
        Login(UN, PW);
        return;
    }
    SignUpFrame();
}

function SignUpFrame() {
    let sPage = "";
    sPage += "<p class='si_topBar'>";
    sPage += "<span class='si_topBarName'>parkerchat</span> - Create a New Account";
    sPage += "</p>";
    sPage += "<div class='si'>";
    sPage += "<div class='si_LogoContainer'>";
    sPage += "<img src='images/windowicon.png' class='si_logo'</img>";
    sPage += "</div>";
    sPage += "<div class='si_textContainer'>";
    sPage += "<div class='si_container'>";
    sPage += "<span class='si_text'>CREATE AN ACCOUNT</span>";
    sPage += "<a href=\"javascript:LoginFrame()\" class='si_text' style='float: right; font-size: 12px; margin-top: 4px;'>or login</a>"
    sPage += "<input id='first' type='input' class='si_text si_textBox' placeholder='First Name' maxlength=40 title='first name' /><br>";
    sPage += "<input id='last' type='input' class='si_text si_textBox' placeholder='Last Name' maxlength=40 title='last name' /><br>";
    sPage += "<input id='username' type='input' class='si_text si_textBox' placeholder='Username' maxlength=40 title='username' /><br>";
    sPage += "<input id='password' type='password' class='si_text si_textBox' placeholder='Password' title='password' /><br>";
    sPage += "<input id='confirm' type='password' class='si_text si_textBox' placeholder='Confirm Password' title='confirm password' />";
    sPage += "<div class='si_checkContainer'>"
    sPage += "<input id='Memory' class='si_checkbox' type='checkbox' checked=true>";
    sPage += "<label class='si_text si_rememberMe' for='Memory'>Stay Logged In</label>"
    sPage += "</div>";
    sPage += "<button class='si_createButton' onClick='checkNewAccount()'>Create Account</button>";
    sPage += "</div>";
    sPage += "</div>";
    sPage += "</div>";
    document.getElementById('Main').innerHTML = sPage;
}

function LoginFrame() {
    let sPage = "";
    sPage += "<p class='si_topBar'>";
    sPage += "<span class='si_topBarName'>parkerchat</span> - Login";
    sPage += "</p>";
    sPage += "<div class='si'>";
    sPage += "<div class='si_LogoContainer'>";
    sPage += "<img src='images/windowicon.png' class='si_logo'</img>";
    sPage += "</div>";
    sPage += "<div class='si_textContainer'>";
    sPage += "<div class='si_container'>";
    sPage += "<span class='si_text'>LOGIN</span>";
    sPage += "<a href=\"javascript:SignUpFrame()\" class='si_text' style='float: right; font-size: 12px; margin-top: 4px;'>or sign up</a>"
    sPage += "<input id='username' type='input' class='si_text si_textBox' placeholder='Username' maxlength=40 title='username' /><br>";
    sPage += "<input id='password' type='password' class='si_text si_textBox' placeholder='Password' title='password' /><br>";
    sPage += "<div class='si_checkContainer'>"
    sPage += "<input id='Memory' class='si_checkbox si_loginButton si_loginCheckbox' type='checkbox' checked=true>";
    sPage += "<label class='si_text si_rememberMe si_loginRemember' for='Memory'>Stay Logged In</label>"
    sPage += "</div>";
    sPage += "<button class='si_createButton si_loginButton' onClick='checkLogin()'>Login</button>";
    sPage += "</div>";
    sPage += "</div>";
    sPage += "</div>";
    document.getElementById('Main').innerHTML = sPage;
}

function checkNewAccount() {
    let sFirst = document.getElementById('first').value.trim();
    let sLast = document.getElementById('last').value.trim();
    let sUsername = document.getElementById('username').value.trim();
    let sPassword = document.getElementById('password').value.trim();
    let sConfirm = document.getElementById('confirm').value.trim();

    if (!sUsername) {
        document.getElementById('username').placeholder = "pick a username";
        return;
    }
    if (!sPassword) {
        document.getElementById('password').placeholder = "pick a password";
        return;
    }
    if (sPassword !== sConfirm) {
        document.getElementById('confirm').value = "";
        document.getElementById('confirm').placeholder = "password and confirm don't match";
        return;
    }

    g_objUserData.info = {
        first: sFirst,
        last: sLast
    };
    g_objUserData.password = sPassword;

    postFileFromServer("srv/signin.php", "uniqueUN=" + encodeURIComponent(sUsername), uniqueUNCallback);
    function uniqueUNCallback(data) {
        if (!data) {
            document.getElementById('username').value = "";
            document.getElementById('username').placeholder = "username taken, sorry";
            g_objUserData = {};
            return;
        }
        else
            createAccount(data);
    }
}

function createAccount(sUsername) {
    g_objUserData.password = HashThis(g_objUserData.password, 10000);

    if (document.getElementById('Memory').checked) {
        setCookie('UN', sUsername, 999);
        setCookie('PW', g_objUserData.password, 999);
    }

    g_objUserData.username = sUsername;

    let jsonUserData = JSON.stringify(g_objUserData);
    postFileFromServer("srv/signin.php", "createAccount=" + encodeURIComponent(jsonUserData), createAccountCallback);
    function createAccountCallback(data) {
        if (data) {
            g_objUserData.id = data;
            Login(g_objUserData.username, g_objUserData.password);
        }
    }
}

function checkLogin() {
    let sUsername = document.getElementById('username').value;
    let sPassword = document.getElementById('password').value;
    if (!sUsername) {
        document.getElementById('username').placeholder = "fill out username";
        return;
    }
    if (!sPassword) {
        document.getElementById('password').placeholder = "fill out password";
        return;
    }
    sPassword = HashThis(sPassword, 10000);

    if (document.getElementById('Memory').checked)
        g_objUserData.rememberMe = true;

    Login(sUsername, sPassword);
}

function Login(UN, PW) {
    g_objUserData.username = UN;
    g_objUserData.password = PW;

    let jsonCredentials = JSON.stringify(g_objUserData);
    postFileFromServer("srv/signin.php", "login=" + encodeURIComponent(jsonCredentials), LogInCallback);
    function LogInCallback(data) {
        if (data) {
            let objUserData = JSON.parse(data);
            g_objUserData.id = objUserData.id;
            let objInfo = JSON.parse(objUserData.info);
            g_objUserData.first = objInfo.first;
            g_objUserData.last = objInfo.last;

            if (g_objUserData.rememberMe) {
                setCookie('UN', g_objUserData.username, 999);
                setCookie('PW', g_objUserData.password, 999);
                g_objUserData.rememberMe = null;
            }
            MainFrame();
        }
        else
            g_objUserData = {};
    }
}

function MainFrame() {
    let sPage = "";
    sPage += "<div class='h_topBar'>";
    sPage += "<div class='h_topBarName'>parkerchat</div>"; // put messages icon on this bar
    sPage += "<img class='h_searchIcon' src='images/searchicon.png' onClick='search()'>";
    sPage += "<input type='text' id='search' class='h_searchBox' placeholder='Search'>";
    sPage += "</div>";

    sPage += "<div class='h_chatContainer'>";
    sPage += "<div class='h_chatterboxContainer'>";
    sPage += "<input id='chatterbox' class='h_chatterbox' placeholder='Chat!'>";
    sPage += "<img class='h_sendIcon' src='images/sendicon.png'>";
    sPage += "</div>";
    sPage += "</div>";

    sPage += "<div class='h_container'>";

    sPage += "<div class='h_accountContainer'>";
    sPage += "<button onClick='AccountInfoSetup(" + " g_objUserData.username " + ")'>Edit Profile</button>";
    sPage += "</div>";

    sPage += "<div class='h_feedContainer'>";
    sPage += "</div>";

    sPage += "<div class='h_friendsContainer'>";
    sPage += "</div>";

    sPage += "</div>";
    document.getElementById('Main').innerHTML = sPage;
}

function AccountInfoSetup(sUsername) {
    postFileFromServer("srv/main.php", "pullUserData=" + encodeURIComponent(sUsername), pullUserDataCallback);
    function pullUserDataCallback(data) {
        AccountInfoFrame(data);
    }
}

function AccountInfoFrame(jsonUserData) {
    let objUserData = JSON.parse(jsonUserData);
    let objUserInfo = JSON.parse(objUserData.info);

    let sPage = "";
    sPage += "<div class='h_topBar' style='margin-bottom: 20px;'>";
    sPage += "<div class='h_topBarName'>parkerchat</div>";
    sPage += "<button style='width: 64px; height: 100%; float: right;' onClick='MainFrame()'>BACK</button>";
    sPage += "</div>";


    sPage += "<div class='a_main'>";

    sPage += "<div class='a_photo'>";
    sPage += "<img src='images/defaultPFP.jpg' class='a_profilePicture'>";
    sPage += "</div>";

    sPage += "<div class='a_mutualFriends'>Friends:";
    sPage += "</div>";

    sPage += "<div class='a_frame'>";

    sPage += "<div class='a_title'>";

    const sFirst = objUserInfo.first.charAt(0).toUpperCase() + objUserInfo.first.slice(1);
    const sLast = objUserInfo.last.charAt(0).toUpperCase() + objUserInfo.last.slice(1);
    sPage +=  sFirst + " " + sLast;
    sPage += "</div>";
    sPage += "<button class='a_addFriend'>+</button>";

    sPage += "</div>";

    sPage += "</div>";
//
//
//     sPage += "<span style='font-size: 25px; font-weight: 700;'>" + g_objUserData.username + "</span><br>";
//     sPage += "<span style='font-size: 20px; float: left;'>Account Info</span><br>";
//     if (undefined != g_objUserData.info.first || undefined != g_objUserData.info.last)
//         sPage += "<span style='font-size: 18px; float: left;'>Name: " + g_objUserData.info.first + " " + g_objUserData.info.last + "</span><br>";
//     sPage += "<span style='font-size: 18px; float: left;'>Member Since: " + g_objUserData.info.created.substring(0, 10) + "</span><br>";
//     sPage += "<span style='font-size: 18px; float: left;'>Last Login: " + g_objUserData.info.lastlogin.substring(0, 10) + "</span><br>";
//     sPage += "<span style='font-size: 20px; float: left;'>Basic Info</span><br>";
//     if (undefined != g_objUserData.info.sex)
//         sPage += "<span style='font-size: 18px; float: left;'>Sex:</span><br>";
//     if (undefined != g_objUserData.info.school)
//         sPage += "<span style='font-size: 18px; float: left;'>School:</span><br>";
//     if (undefined != g_objUserData.info.birthday)
//         sPage += "<span style='font-size: 18px; float: left;'>Birthday:</span><br>";
//     if (undefined != g_objUserData.info.hometown)
//         sPage += "<span style='font-size: 18px; float: left;'>Hometown:</span><br>";

    document.getElementById('Main').innerHTML = sPage;
}

function search() {
    alert(document.getElementById('search').value);
}

