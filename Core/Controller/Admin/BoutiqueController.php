<?php

namespace Projet\Controller\Admin;


use DateTime;
use Exception;
use Projet\Database\Annee;
use Projet\Database\Boutique;
use Projet\Database\Classe;
use Projet\Database\Cours;
use Projet\Database\Enseignant;
use Projet\Database\Groupe;
use Projet\Database\Matiere;
use Projet\Database\Profile;
use Projet\Model\App;
use Projet\Model\DataHelper;
use Projet\Model\Encrypt;
use Projet\Model\FileHelper;
use Projet\Model\Privilege;
use Projet\Model\Random;
use Projet\Model\Sms;
use Projet\Model\StringHelper;

class BoutiqueController extends AdminController{

    public function index(){
       // Privilege::hasPrivilege(Privilege::$ecoleViewEnseignant,$this->user->privilege);
        $user = $this->user;
        $nbreParPage = 20;
        $profiles = Profile::searchType();
       if(isset($_GET['sexe'])&&isset($_GET['search'])&&isset($_GET['login_debut'])&&isset($_GET['login_end'])&&isset($_GET['debut'])&&isset($_GET['end'])){
            $search = (!empty($_GET['search'])) ? $_GET['search'] : null;
            $sexe = (!empty($_GET['sexe'])) ? $_GET['sexe'] : null;
            $login_debut = (isset($_GET['login_debut'])&&!empty($_GET['login_debut'])) ? date(MYSQL_DATE_FORMAT, strtotime($_GET['login_debut'])) : null;
            $login_end = (isset($_GET['login_end'])&&!empty($_GET['login_end'])) ? date(MYSQL_DATE_FORMAT, strtotime($_GET['login_end'])) : null;
            $debut = (isset($_GET['debut'])&&!empty($_GET['debut'])) ? date(MYSQL_DATE_FORMAT, strtotime($_GET['debut'])) : null;
            $end = (isset($_GET['end'])&&!empty($_GET['end'])) ? date(MYSQL_DATE_FORMAT, strtotime($_GET['end'])) : null;
            $nbre = Boutique::countBySearchType(1,null,$search,$sexe,$debut,$end,$login_debut,$login_end);
            $nbrePages = ceil($nbre->Total / $nbreParPage);
            if (isset($_GET['page']) && $_GET['page'] > 0 && $_GET['page'] <= $nbrePages) {
                $pageCourante = $_GET['page'];
            } else {
                $pageCourante = 1;
                $params['page'] = $pageCourante;
            }
            $enseignants = Boutique::searchType($nbreParPage,$pageCourante,1,null,$search,$sexe,$debut,$end,$login_debut,$login_end);
        }else{
            $nbre = Boutique::countBySearchType(1);
            $nbrePages = ceil($nbre->Total / $nbreParPage);
            if (isset($_GET['page']) && $_GET['page'] > 0 && $_GET['page'] <= $nbrePages) {
                $pageCourante = $_GET['page'];
            } else {
                $pageCourante = 1;
                $params['page'] = $pageCourante;
            }
            $enseignants = Boutique::searchType($nbreParPage,$pageCourante,1);
        }
        $this->render('admin.boutique.index',compact('groupes','profiles','enseignants','user','nbre','nbrePages','matieres','classes'));
    }

    public function enseignants(){
        if(isset($_GET['id'])&&!empty($_GET['id'])){
            $id = $_GET['id'];
            $classe = Classe::find($id);
            if($classe){
                $user = $this->user;
                $nbreParPage = 20;
                if(isset($_GET['sexe'])&&isset($_GET['search'])&&isset($_GET['login_debut'])&&isset($_GET['login_end'])&&isset($_GET['debut'])&&isset($_GET['end'])){
                    $search = (!empty($_GET['search'])) ? $_GET['search'] : null;
                    $sexe = (!empty($_GET['sexe'])) ? $_GET['sexe'] : null;
                    $login_debut = (isset($_GET['login_debut'])&&!empty($_GET['login_debut'])) ? date(MYSQL_DATE_FORMAT, strtotime($_GET['login_debut'])) : null;
                    $login_end = (isset($_GET['login_end'])&&!empty($_GET['login_end'])) ? date(MYSQL_DATE_FORMAT, strtotime($_GET['login_end'])) : null;
                    $debut = (isset($_GET['debut'])&&!empty($_GET['debut'])) ? date(MYSQL_DATE_FORMAT, strtotime($_GET['debut'])) : null;
                    $end = (isset($_GET['end'])&&!empty($_GET['end'])) ? date(MYSQL_DATE_FORMAT, strtotime($_GET['end'])) : null;
                    $nbre = Boutique::countBySearchType($classe->id,$search,$sexe,$debut,$end,$login_debut,$login_end);
                    $nbrePages = ceil($nbre->Total / $nbreParPage);
                    if (isset($_GET['page']) && $_GET['page'] > 0 && $_GET['page'] <= $nbrePages) {
                        $pageCourante = $_GET['page'];
                    } else {
                        $pageCourante = 1;
                        $params['page'] = $pageCourante;
                    }
                    $enseignants = Boutique::searchType($nbreParPage,$pageCourante,$classe->id,$search,$sexe,$debut,$end,$login_debut,$login_end);
                }else{
                    $nbre = Boutique::countBySearchType($classe->id);
                    $nbrePages = ceil($nbre->Total / $nbreParPage);
                    if (isset($_GET['page']) && $_GET['page'] > 0 && $_GET['page'] <= $nbrePages) {
                        $pageCourante = $_GET['page'];
                    } else {
                        $pageCourante = 1;
                        $params['page'] = $pageCourante;
                     }
                    $enseignants = Boutique::searchType($nbreParPage,$pageCourante,$classe->id);
                }
                $this->render('admin.enseignant.enseignants',compact('enseignants','user','nbre','nbrePages'));
            }else{
                App::error();
            }
        }else{
            App::error();
        }
    }

    public function save(){
        header('content-type: application/json');
        $return = [];
        $tab = ["add", "edit"];
        if (isset($_POST['nom']) && !empty($_POST['nom']) &&isset($_POST['prenom']) && !empty($_POST['prenom'])
            &&isset($_POST['sexe']) && !empty($_POST['sexe']) &&isset($_POST['naissance']) && !empty($_POST['naissance'])
            &&isset($_POST['cni']) && !empty($_POST['cni'])
            &&isset($_POST['numero']) && !empty($_POST['numero']) &&isset($_POST['ville']) && !empty($_POST['ville'])
            &&isset($_POST['quartier']) &&isset($_POST['adresse']) &&isset($_POST['email'])&&isset($_POST['profil'])
            && isset($_POST['action']) && !empty($_POST['action']) && isset($_POST['id']) && in_array($_POST["action"], $tab)) {
            $nom = $_POST['nom'];
            $prenom = $_POST['prenom'];
            $sexe = $_POST['sexe'];
            $naissance = $_POST['naissance'];
            $numero = str_replace(' ','',$_POST['numero']);
            $email = $_POST['email'];
            $cni = $_POST['cni'];
            $ville = $_POST['ville'];
            $quartier = $_POST['quartier'];
            $adresse = $_POST['adresse'];
            $action = $_POST['action'];
            $id = $_POST['id'];
            $errorEmail = "Cette adresse email existe déjà, veuillez le changer";
            $errorTel = "Ce numéro de téléphone existe déjà, veuillez le changer";
            $errorCni = "Ce numéro de Carte Nationale d'Identité existe déjà, veuillez le changer";
            $idProfil = $_POST['profil'];
            $bol = true;
            if(!empty($idProfil)){
                $profile = Profile::find($idProfil);
                if(!$profile){
                    $bol = false;
                }
            }
            if($bol){
                if($action == "edit") {
                    1;
                    if (!empty($id)){
                        $enseignant = Boutique::find($id);
                        if ($enseignant) {
                            $bool = true;
                            $bool1 = true;
                            $bool2 = true;
                            if(!empty($email) && $enseignant->email!=$email){
                                $cla = Boutique::byEmail($email);
                                if($cla)
                                    $bool1 = false;
                            }
                            if($enseignant->numero!=$numero){
                                $cla = Boutique::byNumero($numero);
                                if($cla)
                                    $bool = false;
                            }
                            if($enseignant->cni!=$cni){
                                $cla = Boutique::byCni($cni);
                                if($cla)
                                    $bool2 = false;
                            }
                            if($bool){
                                if($bool1){
                                    if($bool2){
                                        $pdo = App::getDb()->getPDO();
                                        try{
                                            $pdo->beginTransaction();
                                            $naissance = new DateTime($naissance);
                                            Boutique::save($enseignant->type,$nom,$prenom,$naissance->format(MYSQL_DATE_FORMAT),$cni,$sexe,$ville,$quartier,$adresse,$email,$numero,$enseignant->matricule,$enseignant->password,$id);
                                            if(!empty($profile)){
                                                Boutique::setProfile($idProfil,$profile->nom,$profile->privilege,$id);
                                            }
                                            $message = "Le marchand a été mis à jour avec succès";
                                            $this->session->write('success',$message);
                                            $pdo->commit();
                                            $return = array("statuts" => 0, "mes" => $message);
                                        }catch (Exception $e){
                                            $pdo->rollBack();
                                            $message = $this->error;
                                            $return = array("statuts" => 1, "mes" => $message);
                                        }
                                    }else{
                                        $return = array("statuts" => 1, "mes" => $errorCni);
                                    }
                                }else{
                                    $return = array("statuts" => 1, "mes" => $errorEmail);
                                }
                            }else{
                                $return = array("statuts" => 1, "mes" => $errorTel);
                            }
                        } else {
                            $message = $this->error;
                            $return = array("statuts" => 1, "mes" => $message);
                        }
                    } else {
                        $message = $this->error;
                        $return = array("statuts" => 1, "mes" => $message);
                    }
                } else {
                    1;
                    $bool = true;
                    $bool1 = true;
                    $bool2 = true;
                    if(!empty($email)){
                        $cla1 = Boutique::byEmail($email);
                        if($cla1)
                            $bool1 = false;
                    }
                    $cla = Boutique::byNumero($numero);
                    if($cla)
                        $bool = false;
                    $cla2 = Boutique::byCni($cni);
                    if($cla2)
                        $bool2 = false;
                    if($bool){
                        if($bool1){
                            if($bool2){
                                $pdo = App::getDb()->getPDO();
                                try{
                                    $naissance = new DateTime($naissance);
                                    $matricule = Random::generateMatricule(2);
                                    while (Boutique::byMatricule($matricule)){
                                        $matricule = Random::generateMatricule(2);
                                    }
                                    $pdo->beginTransaction();
                                    Boutique::save(1,$nom,$prenom,$naissance->format(MYSQL_DATE_FORMAT),$cni,$sexe,$ville,$quartier,$adresse,$email,$numero,$matricule,sha1('0000'));
                                    $lastid = Boutique::lastId();
                                    if(!empty($profile)){
                                        Boutique::setProfile($idProfil,$profile->nom,$profile->privilege,$lastid);
                                    }
                                    $message = "Le marchand a été ajouté avec succès";
                                    $this->session->write('success',$message);
                                    $pdo->commit();
                                    $return = array("statuts" => 0, "mes" => $message);
                                }catch (Exception $e){
                                    $pdo->rollBack();
                                    $message = $this->error;
                                    $return = array("statuts" => 1, "mes" => $message);
                                }
                            }else{
                                $return = array("statuts" => 1, "mes" => $errorCni);
                            }
                        }else{
                            $return = array("statuts" => 1, "mes" => $errorEmail);
                        }
                    }else{
                        $return = array("statuts" => 1, "mes" => $errorTel);
                    }
                }
            }else{
                $return = array("statuts" => 1, "mes" => $this->error);
            }
        } else {
            $message = "Veiullez renseigner tous les champs requis";
            $return = array("statuts" => 1, "mes" => $message);
        }
        echo json_encode($return);
    }

    public function addClasse(){
        $this->checking();
        Privilege::hasPrivilege(Privilege::$ecoleEnseignantAddClasse,$this->user->privilege);
        header('content-type: application/json');
        $return = [];
        if(isset($_POST['id']) && !empty($_POST['id']) && isset($_POST['classe']) && !empty($_POST['classe']) && isset($_POST['groupe']) && !empty($_POST['groupe'])
        && isset($_POST['cours']) && !empty($_POST['cours']) && isset($_POST['coefficient']) && $_POST['coefficient']>0){
            $idEnseignant = $_POST['id'];
            $coefficient = $_POST['coefficient'];
            $idMatiere = $_POST['cours'];
            $idClasse = $_POST['classe'];
            $idGroupe = $_POST['groupe'];
            $classe = Classe::find($idClasse);
            $matiere = Matiere::find($idMatiere);
            $enseignant = Boutique::find($idEnseignant);
            $groupe = Groupe::find($idGroupe);
            $annee = Annee::current();
            if($enseignant&&$matiere&&$classe&&$groupe&&$annee){
                $cour = Cours::exist($idClasse,$idMatiere,1);
                if(!$cour){
                    $cours = Cours::byEnseignant($idClasse,$idEnseignant,$idMatiere);
                    if(!$cours){
                        $pdo = App::getDb()->getPDO();
                        try{
                            $pdo->beginTransaction();
                            $c = Cours::exist($idClasse,$idMatiere,0);
                            if($c){
                                if(!empty($c->idEnseignant)){
                                    Cours::save($annee->id,$idEnseignant,$idClasse,$idGroupe,$idMatiere,$matiere->intitule,$classe->nom,$coefficient);
                                    Cours::setEtat($c->id,0);
                                }else{
                                    Cours::save($annee->id,$idEnseignant,$idClasse,$idGroupe,$idMatiere,$matiere->intitule,$classe->nom,$coefficient,$c->id);
                                    Cours::setEtat($c->id,1);
                                }
                            }else{
                                Cours::save($annee->id,$idEnseignant,$idClasse,$idGroupe,$idMatiere,$matiere->intitule,$classe->nom,$coefficient);
                            }
                            $message = "La classe a été ajoutée avec succès";
                            $pdo->commit();
                            $return = array("statuts" => 0, "mes" => $message);
                        }catch (Exception $e){
                            $pdo->rollBack();
                            $message = $this->error;
                            $return = array("statuts" => 1, "mes" => $message);
                        }
                    }else{
                        $message = "l'enseignant donne déjà ce cours dans cette classe";
                        $return = array("statuts" => 1, "mes" => $message);
                    }
                }else{
                    $message = "Cette classe a déjà un enseignant dans cette matière";
                    $return = array("statuts" => 1, "mes" => $message);
                }
            }else{
                $message = $this->error;
                $return = array("statuts" => 1, "mes" => $message);
            }
        }else{
            $message = $this->error;
            $return = array("statuts" => 1, "mes" => $message);
        }
        echo json_encode($return);
    }

    public function classes(){
        Privilege::hasPrivilege(Privilege::$ecoleEnseignantDetail,$this->user->privilege);
        if(isset($_GET['id'])&&!empty($_GET['id'])){
            $id = $_GET['id'];
            $enseignant = Boutique::find($id);
            if($enseignant){
                $user = $this->user;
                $nbreParPage = 20;
                $annee = Annee::current();
                if(isset($_GET['search'])&&isset($_GET['debut'])&&isset($_GET['end'])){
                    $search = (!empty($_GET['search'])) ? $_GET['search'] : null;
                    $debut = (isset($_GET['debut'])&&!empty($_GET['debut'])) ? date(MYSQL_DATE_FORMAT, strtotime($_GET['debut'])) : null;
                    $end = (isset($_GET['end'])&&!empty($_GET['end'])) ? date(MYSQL_DATE_FORMAT, strtotime($_GET['end'])) : null;
                    $nbre = Classe::countBySearchType($annee->id,$enseignant->id,$search,1,null,$debut,$end);
                    $nbrePages = ceil($nbre->Total / $nbreParPage);
                    if (isset($_GET['page']) && $_GET['page'] > 0 && $_GET['page'] <= $nbrePages) {
                        $pageCourante = $_GET['page'];
                    } else {
                        $pageCourante = 1;
                        $params['page'] = $pageCourante;
                    }
                    $classes = Classe::searchType($nbreParPage,$pageCourante,$annee->id,$enseignant->id,$search,1,null,$debut,$end);
                }else{
                    $nbre = Classe::countBySearchType($annee->id,$enseignant->id);
                    $nbrePages = ceil($nbre->Total / $nbreParPage);
                    if (isset($_GET['page']) && $_GET['page'] > 0 && $_GET['page'] <= $nbrePages) {
                        $pageCourante = $_GET['page'];
                    } else {
                        $pageCourante = 1;
                        $params['page'] = $pageCourante;
                    }
                    $classes = Classe::searchType($nbreParPage,$pageCourante,$annee->id,$enseignant->id);
                }
                $this->render('admin.enseignant.classes',compact('classes','annee','user','nbre','enseignant','nbrePages'));
            }else{
                App::error();
            }
        }else{
            App::error();
        }
    }

    public function cours(){
        Privilege::hasPrivilege(Privilege::$ecoleEnseignantDetail,$this->user->privilege);
        if(isset($_GET['id'])&&!empty($_GET['id'])&&isset($_GET['idUser'])&&!empty($_GET['idUser'])){
            $id = Encrypt::decrypter($_GET['id']);
            $idUser = $_GET['idUser'];
            $enseignant = Boutique::find($idUser);
            $classe = Classe::find($id);
            if($enseignant&&$classe){
                $user = $this->user;
                $annee = Annee::current();
                $nbre = Cours::countBySearchType($annee->id,$enseignant->id,$classe->id);
                $cours = Cours::searchType(null,null,$annee->id,$enseignant->id,$classe->id);
                $this->render('admin.enseignant.cours',compact('classe','annee','user','nbre','enseignant','cours'));
            }else{
                App::error();
            }
        }else{
            App::error();
        }
    }

    public function resetPass(){
        $this->checking();
        Privilege::hasPrivilege(Privilege::$ecoleResetEnseignant,$this->user->privilege);
        header('content-type: application/json');
        $return = [];
        if(isset($_POST['id']) && !empty($_POST['id'])){
            $id = $_POST['id'];
            $enseignant = Boutique::find($id);
            if ($enseignant){
                $pdo = App::getDb()->getPDO();
                try{
                    $pdo->beginTransaction();
                    $code = Random::number(4);
                    Boutique::setPassword(sha1($code),$id);
                    Sms::resultSms($enseignant->numero,"Hi $enseignant->nom, Votre mot de passe a ete reinitialise.\n Nouveau mot de passe: $code\nNotre equipe","Ecole");
                    $message = "Le mot de passe a été changé avec succès";
                    $this->session->write('success',$message);
                    $pdo->commit();
                    $return = array("statuts" => 0, "mes" => $message);
                }catch (Exception $e){
                    $pdo->rollBack();
                    $message = $this->error;
                    $return = array("statuts" => 1, "mes" => $message);
                }
            }else{
                $message = $this->error;
                $return = array("statuts" => 1, "mes" => $message);
            }
        }else{
            $message = $this->error;
            $return = array("statuts" => 1, "mes" => $message);
        }
        echo json_encode($return);
    }

    public function delete(){
        $this->checking();
        Privilege::hasPrivilege(Privilege::$ecoleEditEnseignant,$this->user->privilege);
        header('content-type: application/json');
        $return = [];
        if(isset($_POST['id']) && !empty($_POST['id'])&&isset($_POST['etat']) && in_array($_POST['etat'],[0,1,2])){
            $id = $_POST['id'];
            $etat = $_POST['etat'];
            $enseignant = Boutique::find($id);
            if($enseignant){
                $pdo = App::getDb()->getPDO();
                try{
                    $pdo->beginTransaction();
                    Boutique::setEtat($etat,$id);
                    $message = "L'opération s'est passée avec succès";
                    $this->session->write('success',$message);
                    $pdo->commit();
                    $return = array("statuts" => 0, "mes" => $message);
                }catch (Exception $e){
                    $pdo->rollBack();
                    $message = $this->error;
                    $return = array("statuts" => 1, "mes" => $message);
                }
            }else{
                $message = $this->error;
                $return = array("statuts" => 1, "mes" => $message);
            }
        }else{
            $message = $this->error;
            $return = array("statuts" => 1, "mes" => $message);
        }
        echo json_encode($return);
    }

    public function setDiplome(){
        $this->checking();
        Privilege::hasPrivilege(Privilege::$ecoleEditEnseignant,$this->user->privilege);
        header('content-type: application/json');
        $id = DataHelper::post('idDiplome');
        if(isset($_FILES['image']['tmp_name']) && !empty($_FILES['image']['tmp_name'])&& !is_null($id) ){
            if ($_FILES['image']['error'] == 0){
                $extensions_valides = array('jpg','jpeg','png','JPG','JPEG','PNG');
                $extension_upload = strtolower(  substr(  strrchr($_FILES['image']['name'], '.')  ,1)  );
                if(in_array($extension_upload,$extensions_valides)){
                    if($_FILES['image']['size']<=2000000){
                        $enseignant = Boutique::find($id);
                        if($enseignant){
                            $pdo = App::getDb()->getPDO();
                            try{
                                $pdo->beginTransaction();
                                $root = FileHelper::moveImage($_FILES['image']['tmp_name'],"identity","png","",true);
                                if (!empty($enseignant->imageDiplome) && strpos($enseignant->imageDiplome, 'file') === false){
                                    FileHelper::deleteImage('Public/'.$enseignant->imageDiplome);
                                }
                                if($root){
                                    Boutique::setImageDiplome($root,$id);
                                    $success = "Le fichier du diplôme a été mis à jour avec succès";
                                    $this->session->write('success',$success);
                                    $pdo->commit();
                                    $result = array("statuts"=>0, "mes"=>$success);
                                }else{
                                    $erreur = $this->error;
                                    $result = array("statuts"=>1, "mes"=>$erreur);
                                }
                            } catch(Exception $e){
                                $pdo->rollBack();
                                $erreur = $this->error;
                                $result = array("statuts"=>1, "mes"=>$erreur);
                            }
                        } else{
                            $erreur =  $this->error;
                            $result = array("statuts"=>1, "mes"=>$erreur);
                        }
                    }else{
                        $erreur =  'Le fichier doit avoir une taille inférieur à 2M';
                        $result = array("statuts"=>1, "mes"=>$erreur);
                    }
                }else{
                    $message = "Le fichier doit être une image";
                    $result = array("statuts"=>1, "mes"=>$message);
                }
            }else{
                $message = $this->error;
                $result = array("statuts"=>1, "mes"=>$message);
            }
        }else{
            $message = "Vous devez uploader un fichier";
            $result = array("statuts"=>1, "mes"=>$message);
        }
        echo json_encode($result);
    }

    public function setCni(){
        $this->checking();
        1;
        header('content-type: application/json');
        $id = DataHelper::post('idCni');
        if(isset($_FILES['image']['tmp_name']) && !empty($_FILES['image']['tmp_name'])&& !is_null($id) ){
            if ($_FILES['image']['error'] == 0){
                $extensions_valides = array('jpg','jpeg','png','JPG','JPEG','PNG');
                $extension_upload = strtolower(  substr(  strrchr($_FILES['image']['name'], '.')  ,1)  );
                if(in_array($extension_upload,$extensions_valides)){
                    if($_FILES['image']['size']<=2000000){
                        $enseignant = Boutique::find($id);
                        if($enseignant){
                            $pdo = App::getDb()->getPDO();
                            try{
                                $pdo->beginTransaction();
                                $root = FileHelper::moveImage($_FILES['image']['tmp_name'],"identity","png","",true);
                                if (!empty($enseignant->imageDiplome) && strpos($enseignant->imageDiplome, 'file') === false){
                                    FileHelper::deleteImage('Public/'.$enseignant->imageDiplome);
                                }
                                if($root){
                                    Boutique::setImageCni($root,$id);
                                    $success = "Le fichier de la CNI a été mis à jour avec succès";
                                    $this->session->write('success',$success);
                                    $pdo->commit();
                                    $result = array("statuts"=>0, "mes"=>$success);
                                }else{
                                    $erreur = $this->error;
                                    $result = array("statuts"=>1, "mes"=>$erreur);
                                }
                            } catch(Exception $e){
                                $pdo->rollBack();
                                $erreur = $this->error;
                                $result = array("statuts"=>1, "mes"=>$erreur);
                            }
                        } else{
                            $erreur =  $this->error;
                            $result = array("statuts"=>1, "mes"=>$erreur);
                        }
                    }else{
                        $erreur =  'Le fichier doit avoir une taille inférieur à 2M';
                        $result = array("statuts"=>1, "mes"=>$erreur);
                    }
                }else{
                    $message = "Le fichier doit être une image";
                    $result = array("statuts"=>1, "mes"=>$message);
                }
            }else{
                $message = $this->error;
                $result = array("statuts"=>1, "mes"=>$message);
            }
        }else{
            $message = "Vous devez uploader un fichier";
            $result = array("statuts"=>1, "mes"=>$message);
        }
        echo json_encode($result);
    }

    public function setPhoto(){
        $this->checking();
        1;
        header('content-type: application/json');
        $id = DataHelper::post('idPhoto');
        if(isset($_FILES['image']['tmp_name']) && !empty($_FILES['image']['tmp_name'])&& !is_null($id) ){
            if ($_FILES['image']['error'] == 0){
                $extensions_valides = array('jpg','jpeg','png','JPG','JPEG','PNG');
                $extension_upload = strtolower(  substr(  strrchr($_FILES['image']['name'], '.')  ,1)  );
                if(in_array($extension_upload,$extensions_valides)){
                    if($_FILES['image']['size']<=2000000){
                        $enseignant = Boutique::find($id);
                        if($enseignant){
                            $pdo = App::getDb()->getPDO();
                            try{
                                $pdo->beginTransaction();
                                $root = FileHelper::moveImage($_FILES['image']['tmp_name'],"identity","png","",true);
                                //FileHelper::deleteImage($enseignant->photo);
                                if($root){
                                    Boutique::setPhoto($root,$id);
                                    $success = "La photo a été mise à jour avec succès";
                                    $this->session->write('success',$success);
                                    $pdo->commit();
                                    $result = array("statuts"=>0, "mes"=>$success);
                                }else{
                                    $erreur = $this->error;
                                    $result = array("statuts"=>1, "mes"=>$erreur);
                                }
                            } catch(Exception $e){
                                $pdo->rollBack();
                                $erreur = $this->error;
                                $result = array("statuts"=>1, "mes"=>$erreur);
                            }
                        } else{
                            $erreur =  $this->error;
                            $result = array("statuts"=>1, "mes"=>$erreur);
                        }
                    }else{
                        $erreur =  'Le fichier doit avoir une taille inférieur à 2M';
                        $result = array("statuts"=>1, "mes"=>$erreur);
                    }
                }else{
                    $message = "Le fichier doit être une image";
                    $result = array("statuts"=>1, "mes"=>$message);
                }
            }else{
                $message = $this->error;
                $result = array("statuts"=>1, "mes"=>$message);
            }
        }else{
            $message = "Vous devez uploader un fichier";
            $result = array("statuts"=>1, "mes"=>$message);
        }
        echo json_encode($result);
    }

    public function loader(){
        $return = [];
        $this->checking();
        header('content-type: application/json');
        if(isset($_POST['val'])&&!empty($_POST['val'])){
            $val = $_POST['val'];
            $cat = Classe::find($val);
            if($cat){
                $sous = Groupe::searchType(null,null,$val);
                $content = "";
                foreach ($sous as $sou) {
                    $content .= '<option value="'.$sou->id.'">'.$sou->nom.'</option>';
                }
                $return = array("statuts" => 0, "con"=>$content);
            }else{
                $return = array("statuts" => 1);
            }
        }else{
            $return = array("statuts" => 1);
        }
        echo json_encode($return);
    }

    public function detail(){
        1;
        $tab = [1=>11,2=>12,3=>13];
        if(isset($_GET['id'])&&!empty($_GET['id'])){
            $id = Encrypt::decrypter($_GET['id']);
            $enseignant = Boutique::find($id);
            if($enseignant&&$enseignant->type==1){
                $user = $this->user;
                $cours = Boutique::searchType(null,null,$id);
                $this->render('admin.boutique.detail',compact('cours','user','enseignant','annee'));
            }else{
                App::error();
            }
        }else{
            App::error();
        }
    }

}