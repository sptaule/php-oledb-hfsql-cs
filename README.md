# Exemple de connexion à une base de données HFSQL Client/Serveur avec le provider OLE DB et PHP

## Pré-requis

- Etre sur **Windows**
- Un serveur **Apache/Nginx**
- **PHP**
- L'extension **com_dotnet** pour PHP activée permettant d'intéragir avec les objets COM

## Activer l'extension com_dotnet

1. Vérifier que l'extension **php_com_dotnet.dll** est bien présente dans le dossier `ext` de PHP.

2. Ajouter dans le fichier **php.ini** pour activer l'extension
```ini
extension=com_dotnet
```

3. Vérifier que ce code est bien présent dans la section COM du fichier **php.ini**

```ini
[COM]
com.allow_dcom = true
```

## Variables pour la classe Database

HOSTNAME :  
L'adresse du serveur HFSQL.
Si c'est en local, il s'agit probablement de **localhost:4900**.

DATABASE :  
Le nom de la base de données HFSQL (attention à bien respecter la casse).

USERNAME :  
L'identifiant utilisé pour se connecter au serveur HFSQL.

PASSWORD :  
Le mot de passe associé à l'identifiant HFSQL.

FILE_PASSWORD_STRING :  
Il s'agit ici de préciser, uniquement si vos fichiers (.fic) sont protégés par mot de passe, le mot de passe à utiliser.
Il est indiqué dans la documentation que l'on peut simplement écrire `*:lemotdepasse` si tous les fichiers ont le même mot de passe.
J'ai rencontré des problèmes en utilisant cette notation bien que tous mes fichiers ont le même mot de passe.
C'est pourquoi j'ai plutôt écris `Password=CLIENTS:lemotdepasse;FONDATIONS:lemotdepasse;PAYS:lemotdepasse;FACTURES:lemotdepasse;` (où CLIENTS, FONDATIONS, PAYS, FACTURES sont les noms des fichiers de la base de données).

LANGUAGE :  
C'est la langue utilisée pour le traitement des chaînes par le provider OLE DB.
Pour la France, vous pouvez très probablement mettre `ISO-8859-1`.

COMPRESSION :  
Si vous souhaitez que les données transmises par le provider OLE DB soient compressées ou non.
Valeurs possibles : `Vrai` ou `Faux`.

ENCRYPTION :  
A adapter selon vos besoins et votre configuration.
Pour plus d'informations, suivre la [documentation officielle](https://doc.pcsoft.fr/fr-fr/?9000059).

