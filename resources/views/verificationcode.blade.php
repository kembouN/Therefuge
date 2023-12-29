<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vérification Mail</title>

    <!--STYLE POUR LE MAIL DE VERIFICATION D'E-MAIL-->

    <style>

        .ensemble{
            width: 50%;
            position: absolute;
            margin-left: 30%;
            background-color: rgb(235, 235, 235);
            border: 0px, white;
            padding: 1%;
        }
        .titre{
            text-align: center;
            font-size: x-large;
            font-size: 1.7em;
            font-family: Georgia, 'Times New Roman', Times, serif;
            font-weight: bolder;
            border-radius: 10px;
            background-color: midnightblue;
            color: white;
            margin: 5% 0% 10% 20%;
            height: 10%;
            padding: 2%;
            width: 60%;
        }

        .entete_section{
            text-align: center;
            margin-bottom: 10%;
        }

        .titre_code{
            color: rgb(63, 63, 63) ;
            font-size: 2em;
            font-family: 'Times New Roman', Times, serif;
        }

        .code{
            font-size: xx-large;
            color: midnightblue;
        }

        .description{
            font-family: 'Times New Roman', Times, serif;
            font-size: 15px;
        }

        .description span{
            font-weight: bold;
            font-family: Verdana;
        }

        .verifier{
            border-radius: 5px;
            width: 20%;
            height: 10%;
            display: block;
            text-align: center;
            background-color:midnightblue;
            text-decoration: none;
            color: white;
            margin-left: 5%;
        }

    </style>

</head>
<body>
    <div class="ensemble">
        <header class="titre">
            Nous vérifions si c'est bien vous
        </header>
        
        <section class="mail_content">

            <header class="entete_section">
                <p>
                    <span class="titre_code">Votre code de vérification:</span>
                    <br>
                    <br> 
                    <span class="code">{{$mailData['user']->trust_email}}</span>
                </p>
            </header>

            <div class="description">

                <p>
                    Nous sommes heureux que vous nous rejoigniez, <span>{{$mailData['username']}}</span>.
                </p>

                <p>
                    La création de votre compte nécessite une vérification de vos coordonnées à travers un code unique. Sa validité est limitée à soixante(60) minutes, vérifiez vos coordonnées dans les temps. 😉<br><br>
                    Si vous n'êtes pas à l'origine de ce message, veuillez l'ignorer.
                </p>
    
            </div>
        </section>

        <footer>
            <p>
                <a href="{{route('verifiemail', [$mailData['user']->id,$mailData['user']->trust_email])}}" class="verifier">Vérifiez votre adresse e-mail</a>
            </p>
        </footer>
    </div>
</body>
</html>