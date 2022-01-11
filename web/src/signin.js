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
    sPage += "<div class='si'>";
    sPage += "<div class='si_text si_header'>Sign Up</div>";
    sPage += "<div id='feedback' class='si_text si_subHead'>to continue to site</div>";
    sPage += "<div class='si_innerContainer'>";
    sPage += "<input id='username' type='input' class='si_textBox' placeholder='Username' maxlength=40 title='username' />";
    sPage += "<input id='password' type='password' class='si_textBox' placeholder='Password' title='password' />";
    sPage += "<input id='confirm' type='password' class='si_textBox' placeholder='Confirm Password' title='confirm password' />";
    sPage += "<div id='Memory' class='si_text si_subHead si_checkbox' onClick='toggleMemory()' title='green remembers you'>Remember Me</div>";
    sPage += "<button class='si_textBox si_button' onClick='checkNewAccount()'>Get Started</button>";
    sPage += "<div class='si_text'>Have an account? ";
    sPage += "<a href=\"javascript:LoginFrame()\" class='si_link'>Log in</a>";
    sPage += "</div>";
    sPage += "</div>";
    sPage += "</div>";
    document.getElementById('Main').innerHTML = sPage;
}

function LoginFrame() {
    let sPage = "";
    sPage += "<div class='si' style='height: 338px;'>";
    sPage += "<div class='si_text si_header'>Log in</div>";
    sPage += "<div id='feedback' class='si_text si_subHead'>to continue to site</div>";
    sPage += "<div class='si_innerContainer'>";
    sPage += "<input id='username' type='input' class='si_textBox' placeholder='Username' maxlength=40 title='username' />";
    sPage += "<input id='password' type='password' class='si_textBox' placeholder='Password' title='password' />";
    sPage += "<div id='Memory' class='si_text si_subHead si_checkbox' onClick='toggleMemory()' title='green remembers you'>Remember Me</div>";
    sPage += "<button class='si_textBox si_button' onClick='checkLogin()'>Log in</button>";
    sPage += "<div class='si_text'>Don't have an account? ";
    sPage += "<a href=\"javascript:SignUpFrame()\" class='si_link'>Sign up</a>";
    sPage += "</div>";
    sPage += "</div>";
    sPage += "</div>";
    sPage += "<div id='Toast' class='Toast'></div>";
    document.getElementById('Main').innerHTML = sPage;
}

function toggleMemory() {
    let bOff = document.getElementById('Memory').style.color === "rgb(185, 9, 11)";
    document.getElementById('Memory').style.color = bOff ? "#A4C639" : "#B9090B";
}

function checkNewAccount() {
    let sUsername = document.getElementById('username').value.trim();
    let sPassword = document.getElementById('password').value.trim();
    let sConfirm = document.getElementById('confirm').value.trim();

    if (!sUsername || !sPassword || !sConfirm) {
        document.getElementById('feedback').style.color = "rgb(185, 9, 11)";
        document.getElementById('feedback').innerHTML = "please fill out all fields";
        return;
    }
    if (sPassword !== sConfirm) {
        document.getElementById('feedback').style.color = "rgb(185, 9, 11)";
        document.getElementById('feedback').innerHTML = "password and confirm don't match";
        return;
    }

    g_objUserData.password = sPassword;

    postFileFromServer("srv/signin.php", "uniqueUN=" + encodeURIComponent(sUsername), uniqueUNCallback);
    function uniqueUNCallback(data) {
        if (!data) {
            document.getElementById('feedback').style.color = "rgb(185, 9, 11)";
            document.getElementById('feedback').innerHTML = "username taken, sorry";
            g_objUserData = {};
            return;
        }
        else
            createAccount(data);
    }
}

function createAccount(sUsername) {
    g_objUserData.password = HashThis(g_objUserData.password, 10000);

    if (document.getElementById('Memory').style.color !== "rgb(185, 9, 11)") {
        setCookie('UN', sUsername, 999);
        setCookie('PW', g_objUserData.password, 999);
    }

    g_objUserData.username = sUsername;

    let jsonUserData = JSON.stringify(g_objUserData);
    postFileFromServer("srv/signin.php", "createAccount=" + encodeURIComponent(jsonUserData), createAccountCallback);
    function createAccountCallback(data) {
        if (data) {
            g_objUserData.id = data;
            MainFrame();
            Toast("Account Created");
        }
        else
            Toast("Failed to create account");
    }
}

function checkLogin() {
    let sUsername = document.getElementById('username').value;
    let sPassword = document.getElementById('password').value;
    if (!sUsername || !sPassword) {
        document.getElementById('feedback').style.color = "rgb(185, 9, 11)";
        document.getElementById('feedback').innerHTML = "please fill out all fields";
        return;
    }
    sPassword = HashThis(sPassword, 10000);

    if (document.getElementById('Memory').style.color !== "rgb(185, 9, 11)")
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
            g_objUserData.id = data;

            if (g_objUserData.rememberMe) {
                setCookie('UN', g_objUserData.username, 999);
                setCookie('PW', g_objUserData.password, 999);
                g_objUserData.rememberMe = null;
            }
            MainFrame();
        }
        else {
            Toast("Login failed");
            g_objUserData = {};
        }
    }
}

function MainFrame() {
    alert(g_objUserData.id);
}


