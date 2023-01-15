<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <link rel="stylesheet" type="text/css" href="assets/styles/style.css">
        <title>Deine Wahl - Login</title>
        <link rel="icon" type="image/x-icon" href="assets/images/icons/logo.png">
    </head>

    <body> 

        <!-- Header and title of website-->
        <header>
            <h1><u><b>Deine Wahl</b></u></h1>
        </header>

        <!-- Login form-->
        <div id="loginBackground">
            <div id="loginFieldBorder">
                <div style="text-align: center;">
                    <!--action-attribute manages where the user is forwarded to-->
                    <form action="index.php"  method="post">
                        <!--label-elements are like text elements except they attach to the first element with their same ID-->
                        <label for="sNum" class="loginLabel"><h3>Schulnummer: </h3></label>
                        <br>
                        <input type="text" id="sNum" name="sNum" required>
                        <br>
                        <label for="uName" class="loginLabel"><h3>Benutzername: </h3></label>
                        <br>
                        <input type="text" id="uName" name="uName" required>
                        <br>
                        <label for="pWord" class="loginLabel"><h3>Passwort: </h3></label>
                        <br>
                        <input type="password" id="pWord" name="pWord" required>
                        <br>
                        <input type="submit" value="Anmelden" class="loginButton">
                    </form>   
                </div>
            </div>
        </div>

        <div class="infoBar">
            <div class="infoStandard"> 
                <a href="impressum.php" id="imprintLink">Impressum</a>
            </div>
        </div>
    </body>
</html>
