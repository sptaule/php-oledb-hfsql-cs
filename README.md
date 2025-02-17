# Exemple de connexion à une base de données HFSQL Client/Serveur avec le provider OLE DB et PHP

## Ce que c'est

Un exemple de connexion à une base de données de type HFSQL Client/Serveur (PCSoft) avec PHP en utilisant le provider OLE DB.  
J'ai rencontré pas mal de difficulté à me connecter, c'est pourquoi je propose ce code d'exemple.  
Ce n'est qu'à titre informatif et **ce n'est pas voué à être utilisé en production**.

## Pré-requis

- Etre sur **Windows**
- Avoir installé le provider OLE DB fourni par PCSoft
- Un serveur **Apache/Nginx**
- **PHP**
- L'extension **com_dotnet** pour PHP activée permettant d'intéragir avec les objets COM

## Comment activer l'extension com_dotnet

1. Vérifier que l'extension **php_com_dotnet.dll** est bien présente dans le dossier `ext` de PHP.

2. Ajouter dans le fichier **php.ini** pour activer l'extension
```ini
extension=com_dotnet
```

3. Vérifier que ce code est bien actif dans la section COM du fichier **php.ini**

```ini
[COM]
com.allow_dcom = true
```

## Variables pour la classe Database

<ins>HOSTNAME</ins>  
L'adresse du serveur HFSQL.  
Si c'est en local, il s'agit probablement de **localhost:4900**.

<ins>DATABASE</ins>  
Le nom de la base de données HFSQL (attention à bien respecter la casse).

<ins>USERNAME</ins>  
L'identifiant utilisé pour se connecter au serveur HFSQL.

<ins>PASSWORD</ins>  
Le mot de passe associé à l'identifiant HFSQL.

<ins>FILE_PASSWORD_STRING</ins>  
Il s'agit ici de préciser, uniquement si vos fichiers (.fic) sont protégés par mot de passe, le mot de passe à utiliser.  
Il est indiqué dans la documentation que l'on peut simplement écrire `*:lemotdepasse` si tous les fichiers ont le même mot de passe.  
J'ai rencontré des problèmes en utilisant cette notation bien que tous mes fichiers ont le même mot de passe.  
C'est pourquoi j'ai plutôt écris `Password=CLIENTS:lemotdepasse;FONDATIONS:lemotdepasse;PAYS:lemotdepasse;FACTURES:lemotdepasse;` (où CLIENTS, FONDATIONS, PAYS, FACTURES sont les noms des fichiers de la base de données).

<ins>LANGUAGE</ins>  
C'est la langue utilisée pour le traitement des chaînes par le provider OLE DB.  
Pour la France, vous pouvez très probablement mettre `ISO-8859-1`.

<ins>COMPRESSION</ins>  
Si vous souhaitez que les données transmises par le provider OLE DB soient compressées ou non.  
Valeurs possibles : `Vrai` ou `Faux`.

<ins>ENCRYPTION</ins>  
A adapter selon vos besoins et votre configuration.  

<ins>Exemple</ins>  

```php
const string HOSTNAME = "localhost:4900";
const string DATABASE = "my_app_db";
const string USERNAME = "my_server_user";
const string PASSWORD = "my_server_password";
const string FILE_PASSWORD_STRING = "Password=CLIENTS:file_pw;FONDATIONS:file_pw;PAYS:file_pw;FACTURES:file_pw;";
const string LANGUAGE = "ISO-8859-1";
const string COMPRESSION = "Vrai";
const string ENCRYPTION = "rc5_16";
const bool AUTO_CONVERT_TYPES = true;
```

Pour plus d'informations, suivre la [documentation officielle](https://doc.pcsoft.fr/fr-fr/?9000059).

## TypeManager pour convertir les types automatiquement

Globalement :  
- les types entiers seront convertis en int
- les types décimaux seront convertis en float
- les booléens seront convertis en bool
- les dates (date et datetime) seront converties en DateTimeImmutable
- les textes seront traités afin de ne pas avoir de soucis avec les accents

Pour activer cette option, définir la variable `AUTO_CONVERT_TYPES` (qui est utilisée dans la classe TypeManager) à `true`.

## Exemples

```php
// get() pour récupérer un enregistrement
$user = Database::get("SELECT id, name, email, password FROM CLIENTS WHERE email = ?", [$email]);

// getAll() pour récupérer plusieurs enregistrements
$factures = Database::getAll("SELECT id, reference FROM FACTURES WHERE date_expiration BETWEEN ? AND ?", [
  $dateFrom,
  $dateTo
]);

// update() pour mettre à jour un enregistrement
Database::update('CLIENTS', ["email = $newEmail"], ["name = ?"], [$name]);

// insert() pour ajouter un enregistrement
Database::insert("FACTURES", [
  "reference" => $reference,
  "user_id" => $userId,
  "date_expiration" = $date
]);

// delete() pour supprimer un enregistrement
Database::delete('CLIENTS', "id = ?", [$id]);
```

## Problème rencontrés

Évidemment, les types propres à HFSQL ne sont pas (à ma connaissance) récupérables.  
Je pense notamment aux rubriques de type mémo image ou mémo binaire...  
Aussi, il est bon de noter que ce n'est pas très performant même si largement supportable.
