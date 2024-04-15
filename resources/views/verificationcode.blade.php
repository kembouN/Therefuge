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

        .alerte{
            color: yellow;
            font-weight: bold;
        }

    </style>

</head>
<body>
    <div class="ensemble">
        <header class="titre">
            @if($mailData['subject'] == 'verification')
                Nous vérifions si c'est bien vous
            @elseif($mailData['subject'] == 'reinitialisation')
                Votre mot de passe est renouvellé
            @endif
        </header>
        
        <section class="mail_content">

            <header class="entete_section">
                <p>
                    <span class="titre_code">

                        @if($mailData['subject'] == 'verification')
                            Votre code de vérification:
                        @elseif($mailData['subject'] == 'reinitialisation')
                            Votre nouveau mot de passe:
                        @endif

                    </span>
                    <br>
                    <br> 
                    <span class="code">
                        @if($mailData['subject'] == 'verification')
                            {{$mailData['code_verifcation']}}
                        @elseif($mailData['subject'] == 'reinitialisation')
                            {{$mailData['passe']}}
                        @endif
                    </span>
                </p>
            </header>

            <div class="description">

                @if($mailData['subject'] == 'verification')
                    <p>
                        Nous sommes heureux que vous nous rejoigniez, <span>{{$mailData['nom'].' '.$mailData['prenom']}}</span>.
                    </p>

                    <p>
                        La création de votre compte nécessite une vérification de vos coordonnées à travers un code unique de validité limitée à soixante(60) minutes, vérifiez vos coordonnées dans les temps. 😉<br><br>
                        <span class="alerte">Si vous n'êtes pas à l'origine de ce message, veuillez l'ignorer.</span>
                    </p>
                @elseif($mailData['subject'] == 'reinitialisation')
                    <p>
                        Votre mot de passe à été réinitialisé suite à la perte du précédent et vous en obtenez un nouveau. <br><br>

                        <span class="alerte">Il vous est conseillé de changer le mot de passe fournit pour en instaurer un plus robuste et dont vous vous rapellerez facilement.</span>
                    </p>
                @endif

            </div>
        </section>

        <footer>
            <p>
                <a href="{{route('verifiemail', [$mailData['id_user'],$mailData['code_verifcation']])}}" class="verifier">Vérifiez votre adresse e-mail</a>
            </p>
        </footer>
    </div>
</body>
</html>