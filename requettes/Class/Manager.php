<?php 
//___Connection a la base de donnee
session_start(); 
$dbconn = new PDO('mysql:host=localhost;dbname=ceo','root',''); 
interface Services
{
    public function ajouterEnregistrement($dbconn, $idMandataire);
    public function ajouterMandataire($dbconn,$matricule, $nomMandataire, $profession,$statut,$dateEmission);
    public function ajouterTransaction($dbconn, $idMandataire, $paysEmission, $origineFonds,$dateEmission);
    public function ajouterBillet($dbconn, $idMandataire, $depart, $arrive, $destination);
    public function getMandataireById($dbconn, $matricule);
    public function supprimerEnregistrement($dbconn, $session, $idMandataire,$montant);
} 





interface User
{
    public function connexion($dbconn, $login, $motDePasse);
    public function setMessage($message);
    public function displayMessage();
    public function deleteMessage();
}






interface Administration
{
    //___Session manage part
    public function ajouterSession($dbconn,$dateDeCreation, $limite,$intitule);
    public function supprimerSession($dbconn, $nomSession);
    public function modifierSession($dbconn, $idSession,$titre,$dateDeCreation,$limite);
    //public function activerSession($dbconn, $idSession);//__

    //___User manage part
    public function ajouterUtilisateur($dbconn, $login, $motDePasse);
    public function supprimerUtilisateur($dbconn,$idUser);
    public function modifierUtilisateur($dbconn, $idUser, $login, $motDePasse); 
    
    //___Dashbord
    //public function countManager();//__
    //public function CountSessions();//__
    //public function countJustif();//__
}



interface justif
{
    public function totalJustifParManager($dbconn, $idUser);
    public function totalJustif($dbconn);
    public function justifRedigePar($dbconn, $idManager); 
    public function totalJustifParManagerSession($dbconn, $nomSession);

    public function activerSession($dbconn, $idSession);

}









class Session implements Justif
{
    /**
     * [totalJustifParManager RETOURNE LE NOMBRE TOTAL D'ENREGISTREMENT PAR UTILISATEUR]
     * @param  [PDO] $dbconn [description]
     * @param  [int] $idUser [identifiant du manager]
     * @return [int]         [description]
     */
    public function totalJustifParManager($dbconn, $idUser)
    {
        $req = 'SELECT * FROM billet WHERE idUtilisateur='.$idUser;
        $stmt = $dbconn->query($req);
        $count = $stmt->rowcount();
        return intval($count);
    }
    /**
     * [totalJustif RETOURNE LE NOMBRE TOTAL D'ENREGISTREMENT DANS LE SYSTEME]
     * @param  [PDO] $dbconn [-]
     * @return [int]         [nombre d'enregistrement]
     */
    public function totalJustif($dbconn)
    {
        $req = 'SELECT * FROM enregistrement';
        $stmt = $dbconn->query($req);
        $count = $stmt->rowcount();

        return intval($count);
    }
    /**
     * [justifRedigePar RETOURNE LE NOM DE L'UTILISATEUR QUI A ENREGISTRE LE BILLET]
     * @param  [pdo] $dbconn [-]
     * @param  [int] $idUser [identifiant du manager]
     * @return [string]         [nom d'un manager]
     */
    public  function justifRedigePar($dbconn, $idUser){
        $req = 'SELECT login FROM utilisateur WHERE idUtilisateur='.$idUser;
        $stmt = $dbconn->query($req);
        $find = $stmt->fetch();
        return $find['login']; 
    }
    /**
     * [totalJustifParManagerSession RETOURNE LE NOMBRE TOTAL DE JUSTIF PAR SESSION]
     * @param  [pdo] $dbconn    [-]
     * @param  [int] $idSession [identifiant de la session]
     * @return [int]            [nombre de justif de la session passe en parametre]
     */
    public function totalJustifParManagerSession($dbconn, $idSession)
    {
        $req = 'SELECT COUNT(*) FROM enregistrement WHERE idSession="'.$idSession.'"';
        $stmt = $dbconn->query($req);
        $count = $stmt->fetch();  

        return $count[0];
    }
    /**
     * [getStatut RETOURNE L'ETAT D'UNE SESSION(0 ou 1)]
     * @param  [type] $dbconn    [-]
     * @param  [int or string] $idSession [identifiant de la session ou nom de la session]
     * @return [string or int]            [le statut de la session]
     */
    public function getStatut($dbconn,$idSession){
        if (is_int($idSession)) {
            $req = 'SELECT statut FROM session WHERE idSession='.$idSession;
            $find = $dbconn->query($req)->fetch();
            
            if (is_array($find) && !empty($find)) {
                return intval($find[0]);
            }
            else {
                return 'ERROR : getStatut($,$int)';
            }
        }
        elseif (is_string($idSession)) {
            $req = 'SELECT statut FROM session WHERE nomSession="'.$idSession.'"';
            $find = $dbconn->query($req)->fetch();
            
            if ($find) {
                return intval($find[0]);
            }
            else {
                return 'ERROR : getStatut($,$str)';
            }
        }
    }
/**
 * [activerSession ACTIVE OU DESACTIVE UNE SESSION]
 * @param  [pdo] $dbconn    [-]
 * @param  [int] $idSession [identifiant de la session]
 * @return [bool ou string]            [confirmation]
 */
    public function activerSession($dbconn, $idSession)
    {
        $statut = 0;
        $idSession = intval($idSession);
        $etat = $this->getStatut($dbconn,$idSession);
        
        if($etat == 0)
        {
            $statut = 1;
        }

        $req = 'UPDATE session SET statut='.$statut.' WHERE idSession='.$idSession;
        $stmt = $dbconn->query($req);
        if ($stmt) {
            return true;
        }
        else{
            if ($etat) {
                $_SESSION['msg'] = 'une erreur s\'est produit durant la désactivation !!!';
            }
            else{
                $_SESSION['msg'] =  'une erreur s\'est produit durant l\'activation !!!';
            }
        }
        
    }

}














class Manager implements Services
{
    /**
     * @var $dbconn (PDO) 
     * @var $idMandataire (int) : identifiant du mandataire 
     * @var $paysEmission(string) : pays du mandataire
     * @var $origineFonds(string) : origine des fonds du mandataire
     * @var $dateEmission(date) : date d'emission du passport
     */
    public function ajouterTransaction($dbconn, $idMandataire, $paysEmission, $origineFonds,$dateEmission){
        $idUser = $_SESSION['idUser'];
        $montant = $_SESSION['limite'];
        
        $createdAt = date('d-m-Y H:i:s');
        //__requete d'insertion
        $req = "INSERT INTO transaction VALUES('','".$idMandataire."','".$idUser."',".$montant.",'".$paysEmission."','".$origineFonds."','".$dateEmission."')";
        $dbconn->query($req);//__execution de la requette
        //__requete de selection de l'Id de la session
        $req = "SELECT idTransaction FROM transaction WHERE paysEmission='".$paysEmission."' AND origineDesFonds='".$origineFonds."'";
        $stmt = $dbconn->query($req);//__execution de la requette
        $find = $stmt->fetch();
        
        return true;
    }
    /**
     * @var $dbconn(PDO)
     * @var $idMandataire(int)
     * @var $depart(date) : date de depart du billet
     * @var $arrive(date) : date d'arrive du billet
     * @var $destination : pays de destination
     * @return true 
     */
    public function ajouterBillet($dbconn, $idMandataire, $depart, $arrive, $destination){
        $idUser = $_SESSION['idUser'];
        
        $createdAt = date('Y-m-d');
        //__requete d'insertion
        $req = "INSERT INTO billet VALUES('','".$idMandataire."','".$idUser."','".$depart."','".$arrive."','".$destination."','".$createdAt."')";
        $dbconn->query($req);//__execution de la requette 
    
        return true;
    }
    /**
     * @var $dbconn (PDO)
     * @var $matricule(string) : numero du passport du mandataire
     * @var $profession (string) : profession du mandataire
     * @var $statut (string) : situation du mandataire(R/RN)
     * @var $dateEmission(string) : date d'emission du passport du mandataire
     * @return $find(string) : numero identifiant le mandataire
     */
    public function ajouterMandataire($dbconn, $matricule, $nomMandataire, $profession,$statut,$dateEmission){
        $createdAt = date('d-m-Y H:i:s');
        //__requete d'insertion
        $req = "INSERT INTO mandataire VALUES('','".$matricule."','".$nomMandataire."','".$profession."','".$statut."','".$dateEmission."')";
        $dbconn->query($req);//__execution de la requette
        //__requete de selection de l'Id de la session
        $req = "SELECT idMandataire FROM mandataire WHERE matricule='".$matricule."'"; 
        $stmt = $dbconn->query($req);//__execution de la requette
        $find = $stmt->fetch();
        
       return $find['idMandataire'];
       
    }
    /**
     * SUPPRIME UN ENREGISTREMENT EFFECTUE PAR UN OPERATEUR
     * @var $
     * @return [type] [description]
     */
    public function supprimerEnregistrement($dbconn, $session, $idMandataire,$montant){
        echo $req = "SELECT limite FROM session WHERE nomSession='".$session."'";
        $stmt = $dbconn->query($req)->fetch();
        $limite = intval($stmt['limite']);
        $limite += $montant; //___ajout du montant a la limite
        $req = "UPDATE session set limite=".$limite." WHERE nomSession='".$session."'";
        $stmt = $dbconn->query($req); //__mise a jour de la limite de la session

        $req = 'DELETE FROM mandataire WHERE idMandataire='.$idMandataire;
        $stmt = $dbconn->query($req);
        if ($stmt) {
            return true;
        }
        else{
            return $req;
        }
    }
    /** ENREGISTRER UNE NOUVELLE INSERTION
     * @var $dbconn(PDO) 
     * @var $idMandataire(int) : identifiant du mandataire
     * @return true si l'enregistrement se passe bien
     * @return false en cas d'erreur
     */
    public function ajouterEnregistrement($dbconn, $idMandataire){
        $nomSession = $_SESSION['sessionName'];
        $idUser = $_SESSION['idUser'];
        $createdAt = date('d-m-Y H:i:s');
        //__selection de l'id de la session
        $req = "SELECT idSession FROM session WHERE nomSession='".$nomSession."'";
        $stmt = $dbconn->query($req);
        $find = $stmt->fetch();
        $idSession = $find['idSession'];
        //__requete d'insertion
        $req = "INSERT INTO enregistrement VALUES('','".$idUser."','".$idMandataire."','".$idSession."')";
        $stmt = $dbconn->query($req);//__execution de la requette 
        if ($stmt) {
            return true;
        }
        else{
            return false;
        }
    }
    
    /** RENVOYER LES INFORMATIONS SUR UN MANDATAIRE A PARTIR DE SON MATRICULE
     * @var $dbconn (PDO)
     * @var $matricule (string) : numero du passport du mandataire
     */
    public function getMandataireById($dbconn, $matricule){
        $req = "SELECT * FROM mandataire WHERE matricule='".$matricule."'";
        $stmt = $dbconn->query($req);
        $find = $stmt->fetch();
        
        return $find;
    }
    
    /** EFFECTUER UN NOUVEL ENREGISTREMENT
     * @var $dbconn(PDO) 
     * @var $matricule(string) : numero de passport du mandataire
     * @var $nomMandataire(string) : nom du mandataire
     * @var $profession (string) : profession du mandataire
     * @var $statut(string) : statut du resident(resident/nonResident)
     * @var $depart(date) : date de depart
     * @var $arrive(date) : date d'arrive 
     * @var $destination(string) : pays de destination
     * @var $paysEmission(string) : pays d'emission du passport
     * @var $origineFonds(string) : origine des fonds du mandataire
     * @var $dateEmission(date) : date d'emission du passport
     * @return string : messages
     */
    public function effectuerEnregistrement($dbconn,$matricule,$nomMandataire,$profession,$statut,$depart,$arrive,$destination,$paysEmission,$origineFonds,$dateEmission)
    {
        $session = new Session;
        $statut = $session->getStatut($dbconn, $_SESSION['sessionName']);
        if (is_int($statut) && $statut == 1 ) 
        {
               //___Si le mandataire est enregistré dans cette session avec le meme matricule
            $mandataire = $this->getMandataireById($dbconn, $matricule);
            if (empty($mandataire)) 
            {
                /**
                 * verification du nombre limite des billets
                 */
                $req = "SELECT limite FROM session WHERE nomSession='".$_SESSION['sessionName']."'";
                $stmt = $dbconn->query($req);//___execution de la requete
                $limite = $stmt->fetch();//___recuperation du nombre limite(string)
                $limite = $limite['limite']; //___recuperation int
                $limite = intval($limite); //___convertion, string vers int
                //___si la limite est superieur a 0
                if ($limite > $_SESSION['limite'] || $limite >= $_SESSION['limite']) 
                { //___si le quota est supperieur ou egale au montant definit par l'operateur
                    /**
                     * Ajout du mandataire
                     */
                    $idMandataire = $this->ajouterMandataire($dbconn, $matricule, $nomMandataire, $profession,$statut,$dateEmission);
                    /**
                     * Ajout de la transaction
                     */
                    $this->ajouterTransaction($dbconn, $idMandataire, $paysEmission, $origineFonds,$dateEmission);
                    /**
                     * Ajout du billet
                     */
                    $this->ajouterBillet($dbconn, $idMandataire, $depart, $arrive, $destination);
                    /**
                     * Ajout de l'enregistrement
                     */
                    $this->ajouterEnregistrement($dbconn,$idMandataire);  
                    /**
                     * Decrementation de la limite
                     */
                    $limite = $limite-($_SESSION['limite']); //___decrementation et mise a jour
                    $req = "UPDATE session SET limite=".$limite." WHERE nomSession='".$_SESSION['sessionName']."'";
                    $dbconn->query($req);

                    return true;
                }
                elseif($limite!=0 && $limite<$_SESSION['limite'])
                {//__si le quota qui reste est iniferieur au montant definit par l'operateur
                    $_SESSION['limite'] = $limite;
                 /**
                     * Ajout du mandataire
                     */
                    $idMandataire = $this->ajouterMandataire($dbconn, $matricule, $nomMandataire, $profession,$statut,$dateEmission);
                    /**
                     * Ajout de la transaction
                     */
                    $this->ajouterTransaction($dbconn, $idMandataire, $paysEmission, $origineFonds,$dateEmission);
                    /**
                     * Ajout du billet
                     */
                    $this->ajouterBillet($dbconn, $idMandataire, $depart, $arrive, $destination);
                    /**
                     * Ajout de l'enregistrement
                     */
                    $this->ajouterEnregistrement($dbconn,$idMandataire);  
                    /**
                     * Annulation de la limite
                     */
                    $limite = 0;

                    $req = "UPDATE session SET limite=".$limite." WHERE nomSession='".$_SESSION['sessionName']."'";
                    $dbconn->query($req);
                    return $_SESSION['msg'] = true;
                }
                else
                {
                   return $_SESSION['msg'] = 'LA LIMITE DE BILLETS POUR CETTE OPERATION A ETE ATTEINT';
                }           
            }
            else
            {
                return $_SESSION['msg'] = 'UN ENREGISTREMENT A ETE DETECTE DANS CETTE SESSION PAR LE SYSTEME';
            }  
        }
        else
        {
            return $_SESSION['msg'] = "ERREUR : VOUS NE POUVEZ PAS ENREGISTRER CE BILLET, CAR CETTE SESSION A ETE DESACTIVE !!!"; 

        }
        
    }

    /** LISTER LES ENREGISTREMENTS D'UNE OPERATION
     * @var $dbconn(PDO)
     * @var $nomSession(string) : operation en cours
     */
    public function afficherDonnees($dbconn, $nomSession){
        $req = "SELECT m.idMandataire, m.nomMandataire, m.profession, m.statut, m.matricule, m.dateEmission,b.idUtilisateur, b.depart, b.arrive, b.destination, b.createdAt, t.montant, t.paysEmission, t.origineDesFonds,e.idEnregistrement FROM mandataire m, billet b, transaction t, enregistrement e, session s WHERE m.idMandataire = b.idMandataire AND m.idMandataire = t.idMandataire AND m.idMandataire = e.idMandataire AND e.idSession = s.idSession AND s.nomSession ='".$nomSession."'"; 
        //$req = "SELECT m.nomMandataire, m.profession, m.statut FROM mandataire m, session s, enregistrement e WHERE e.idMandataire=m.idMandataire AND s.idSession = e.idSession AND s.nomSession ='".$nomSession."'";
    //echo $_SESSION['loginUser'];

            return  $dbconn->query($req)->fetchAll(); 
    }


}


































/**
*   CLASSE UTILISATEUR
**/ 
class Utilisateur extends Manager implements User
{
    
    public $_idUtilisateur;
    public $_login;
    public $_role;
    private $_pwd;

    /*function __construct(argument)
    {
        # code...
    }*/

    /** PERMETTRE A UN UTILISATEUR DE SE CONNECTER
     * @var $dbconn(PDO) 
     * @var $login (string) : identifiant utilisateur
     * @var $motDePasse(string) : mot de passe utilisateur
     * @return true : si tout se passe comme prevu
     * @return string : si il ya une erreur durant l'execution
     */
     public function connexion($dbconn, $login, $motDePasse){
//        $req = "INSERT INTO utilisateur VALUES('','".$login."','".$motDePasse."')";
        $req = "SELECT * FROM  utilisateur WHERE login='".$login."' AND motDePasse='".$motDePasse."'";
        //__statement
        $stmt = $dbconn->query($req);
        //fetch
        $fetch = $stmt->fetch();
        //__si il ya au moins un resultat
        if ($stmt->rowCount() ==1) {
            if ($login == $fetch['login'] && $motDePasse == $fetch['motDePasse']) {
                //creation des session utilisateur
                $this->_idUtilisateur = $_SESSION['idUser'] = $fetch['idUtilisateur'];
                $this->_login = $_SESSION['loginUser'] = $login;
                $this->_role = $_SESSION['roleUser'] = $fetch['role']; 
            }
        }
        else{

            return $_SESSION['msg'] = 'ATTENTION : login ou mot de passe incorrecte...Veuillez reéssayer!';
        }
     }
    /**
     * @var $message(string) : message a afficher
     */
    public function setMessage($message){
        $_SESSION['msg'] = $message;
    }
    public function deleteMessage(){
        unset($_SESSION['msg']);
        return '';
    }
    public function displayMessage(){
        if (isset($_SESSION['msg'])) {
            $msg = $_SESSION['msg'];
            $this->deleteMessage();
            return $msg;
        }
    }
}





































/**
 * 
 */
class Administrator extends Utilisateur implements Administration
{
    
    /*function __construct(argument)
    {
        # code...
    }*/

    /** AJOUTER UNE NOUVELLE SESSION
     * @var $dbconn (PDO) : connexion a la base de donnee
     * @var $dateDeCreation (date) : date de creation des Operations
     * @var $limite (int) : nombre de billet a inserer dans la session
     * @return true (bool)
     ***/
    public function ajouterSession($dbconn,$dateDeCreation,$limite,$titre){
        $statut = 0;
        $nomSession = '' ; $i=1;
        //__selection du nombre de session enregistré
        $req = "SELECT * FROM session";
        $stmt = $dbconn->query($req);//__execution de la requette
        $count = $stmt->rowCount();
        if ($count==0) {
             $nomSession = 'session'.$i;
         }else{
            $i = $count+$i;
            $nomSession = 'session'.$i;
            $req = "SELECT * FROM session WHERE nomSession='".$nomSession."'";
            $find = $dbconn->query($req)->fetch();
            if (empty($find)) {
                //__requete d'insertion
                $req = "INSERT INTO session VALUES('','".$nomSession."','".$dateDeCreation."','".$limite."','".$titre."','".$statut."')"; 
                $dbconn->query($req); 
            }
         }

        

        return true;
    }
    /** SUPPRIMER UNE SESSION
     * @var $dbConnn(PDO)
     * @var $nomSession(string) : identifiant de la session a supprimer
     * @return true : si tout se passe bien
     * @return false : en cas d'exception
     */
    public function supprimerSession($dbconn, $idSession){
        $req = "DELETE FROM session WHERE idSession='".$idSession."'";
        $stmt = $dbconn->query($req);
        if ($stmt) {
            return true;
        }
        else{
            return false;
        }


    }
    /** EFFECTUER LES MODIFICATIONS SUR LES SESSIONS
     * @var $dbconn(PDO)
     * @var $idSession(int) : identifiant de la session a modifier 
     * @var $titre(int) : identifiant de l'utilisateur
     * @var $dateDeCreation(date) : date de creation de la session a modifier
     * @var $limite(int) : nombre limite des enregistrement
     * @return true : si tout c'est bien passe
     * @return false : en cas de probleme durant l'execution du code
     */
    public function modifierSession($dbconn, $idSession, $titre,$dateDeCreation,$limite){
        $req = "UPDATE session SET createdAt='".$dateDeCreation."', intitule='".$titre."', limite=".$limite." WHERE idSession='".$idSession."'";
        $stmt = $dbconn->query($req);
        if ($stmt) {
            return true;
        }
        else{
            return true;
        }

    }
    /** AJOUTER UN UTILISATEUR
     * @var $dbconn (PDO) 
     * @var $login(string) : identifiant de l'utilisateur
     * @var $motDePasse(string) : mot de passe de l'utilisateur
     */
    public function ajouterUtilisateur($dbconn, $login, $motDePasse)
    {
        $role = "manager";
        $dateCreation = date('Y-m-d h:i:s');
        $req = "INSERT INTO utilisateur VALUES('','".$login."','".$motDePasse."','".$role."','".$dateCreation."')";
        $stmt = $dbconn->query($req);
        if ($stmt) {
            return 'UTILISATEUR AJOUTE AVEC SUCCESS';
        }
        else{
            return 'UN PROBLEME S\'EST PRODUIT LORS DE L\'ENREGISTREMENT';
        }
        
    }
    /** SUPPRIMER UN UTILISATEUR
     * @var $dbConnn(PDO)
     * @var $idUser(int) : identifiant de l'utilisateur a supprimer
     * @return true : si tout se passe bien
     * @return false : en cas d'exception
     */
    public function supprimerUtilisateur($dbconn,$idUser)
    {
        $req = "DELETE FROM utilisateur WHERE idUtilisateur=".$idUser;
        $stmt = $dbconn->query($req);
        if ($stmt) {
            return true;
        }
        else{
            return false;
        }
    }
    /** MODIFIER LES INFORMATIONS D'UN UTILISATEUR
     * @var $dbconn(PDO) 
     * @var $idUser(int) : identifiant de l'utilisateur
     * @var $login(string) : nouvel identifiant de l'utilisateur
     * @var $motDePasse(string) : nouveau mot de passe de l'utilisateur
     * @return true : si tout c'est bien passe
     * @return false : en cas de probleme durant l'execution du code
     */
    public function modifierUtilisateur($dbconn, $idUser, $login, $motDePasse)
    {
        $req = "UPDATE utilisateur SET login='".$login."',motDePasse='".$motDePasse."' WHERE idUtilisateur='".$idUser."'"; 
        $stmt = $dbconn->query($req);
        if ($stmt) {
            return true;
        }
        else{
            return false;
        }
    }

    public function afficherUtilisateur($dbconn){
        $req = "SELECT * FROM utilisateur WHERE role ='manager'"; 
        //$req = "SELECT m.nomMandataire, m.profession, m.statut FROM mandataire m, session s, enregistrement e WHERE e.idMandataire=m.idMandataire AND s.idSession = e.idSession AND s.nomSession ='".$nomSession."'";
    //echo $_SESSION['loginUser'];

            return  $dbconn->query($req)->fetchAll(); 
    }
    public function getUtilisateurById($dbconn,$idUser){
        $req = "SELECT * FROM utilisateur WHERE role ='manager' AND idUtilisateur=".$idUser; 
        //$req = "SELECT m.nomMandataire, m.profession, m.statut FROM mandataire m, session s, enregistrement e WHERE e.idMandataire=m.idMandataire AND s.idSession = e.idSession AND s.nomSession ='".$nomSession."'";
    //echo $_SESSION['loginUser'];

            return  $dbconn->query($req)->fetch(); 
    }

    /**
     * @var $dbconn(PDO)
     * @return array : liste des session
     */
    public function afficherSession($dbconn){
        $req = "SELECT * FROM session"; 
        //$req = "SELECT m.nomMandataire, m.profession, m.statut FROM mandataire m, session s, enregistrement e WHERE e.idMandataire=m.idMandataire AND s.idSession = e.idSession AND s.nomSession ='".$nomSession."'";
    //echo $_SESSION['loginUser'];

            return  $dbconn->query($req)->fetchAll(); 
    }
    /** RENVOYER LES INFORMATIONS SUR UNE SESSION
     * @var $dbconn (PDO)
     * @var $idSession (int) : identifiant de la session requis
     * @return array
     */
    public function getSessionById($dbconn, $idSession){
        $req = "SELECT * FROM session WHERE idSession=".$idSession;
        $stmt = $dbconn->query($req);
        $find = $stmt->fetch();
        
        return $find;
    } 
}

