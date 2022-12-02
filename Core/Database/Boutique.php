<?php

namespace Projet\Database;


use Projet\Model\Table;

class Boutique extends Table{

    protected static $table = 'boutique';

    public static function save($type,$nom,$prenom,$naissance,$cni,$sexe,$ville,$quartier,
                                $adresse,$email,$numero,$matricule,$password,$id=null){
        $sql = 'INSERT INTO ';
        $baseSql = self::getTable().' SET naissance = :naissance,prenom = :prenom,nom = :nom,sexe = :sexe,
        quartier = :quartier,adresse = :adresse,email = :email,
            numero = :numero,matricule = :matricule,password = :password,cni = :cni,ville = :ville,type = :type';
        $baseParam = [':naissance' => $naissance,':prenom' => ucwords($prenom),':sexe' => $sexe,
            ':ville' => $ville,':nom' => ucwords($nom),':quartier' => $quartier,':adresse' => $adresse,
            ':type' => $type,':email' => $email,':numero' => $numero,':matricule' => $matricule,':password' => $password,':cni' => $cni];
        if(isset($id)){
            $sql = 'UPDATE ';
            $baseSql .= ' WHERE id = :id';
            $baseParam [':id'] = $id;
        }
        return self::query($sql.$baseSql, $baseParam, true, true);
    }



    public static function setProfile($idProfile,$libProfile,$privilege,$id){
        $sql = 'UPDATE '.self::getTable().' SET idProfile = :idProfile,libProfile = :libProfile,privilege = :privilege WHERE id = :id';
        $param = [':idProfile'=>($idProfile),':libProfile'=>($libProfile),':privilege'=>($privilege),':id'=>($id)];
        return self::query($sql,$param,true,true);
    }

    public static function setPrivilege($privilege,$id){
        $sql = 'UPDATE '.self::getTable().' SET privilege = :privilege WHERE id = :id ';
        $param = [':privilege'=>($privilege),':id'=>($id)];
        return self::query($sql,$param,true,true);
    }

    public static function setLast_login($date,$id){
        $sql = 'UPDATE '.self::getTable().' SET last_login = :date WHERE id = :id';
        $param = [':date'=>($date),':id'=>($id)];
        return self::query($sql,$param,true,true);
    }

    public static function setEtat($etat,$id){
        $sql = 'UPDATE '.self::getTable().' SET etat = :etat WHERE id = :id';
        $param = [':etat'=>($etat),':id'=>($id)];
        return self::query($sql,$param,true,true);
    }

    public static function setPhoto($photo,$id){
        $sql = 'UPDATE '.self::getTable().' SET photo = :photo WHERE id = :id';
        $param = [':photo'=>($photo),':id'=>($id)];
        return self::query($sql,$param,true,true);
    }

    public static function setImageCni($file,$id){
        $sql = 'UPDATE '.self::getTable().' SET imageCni = :file WHERE id = :id';
        $param = [':file'=>($file),':id'=>($id)];
        return self::query($sql,$param,true,true);
    }


    public static function setPassword($password,$id){
        $sql = 'UPDATE '.self::getTable().' SET password = :password WHERE id = :id';
        $param = [':password'=>($password),':id'=>($id)];
        return self::query($sql,$param,true,true);
    }

    public static function byMatricule($matricule){
        $sql = self::selectString() . ' WHERE matricule = :matricule';
        $param = [':matricule' => $matricule];
        return self::query($sql, $param,true);
    }

    public static function byNumero($numero){
        $sql = self::selectString() . ' WHERE numero = :numero';
        $param = [':numero' => $numero];
        return self::query($sql, $param,true);
    }

    public static function byCni($cni){
        $sql = self::selectString() . ' WHERE cni = :cni';
        $param = [':cni' => $cni];
        return self::query($sql, $param,true);
    }

    public static function byEmail($email){
        $sql = self::selectString() . ' WHERE email = :email';
        $param = [':email' => $email];
        return self::query($sql, $param,true);
    }

    public static function countBySearchType($type = null,$idClasse = null,$search = null, $sexe = null,$debut=null,$fin=null,$conDebut = null, $conFin = null, $idProfile = null){
        $count = 'SELECT COUNT(boutique.id) AS Total FROM '.self::getTable();
        $where = ' WHERE 1 = 1';
        $tab = [];
        $left = '';
        if(isset($search)){
            $tSearch = ' AND (nom LIKE :search OR prenom LIKE :search OR numero LIKE :search OR cni LIKE :search OR email LIKE :search OR matricule LIKE :search)';
            $tab[':search'] = '%'.$search.'%';
        }else{
            $tSearch = '';
        }
        if(isset($idClasse)){
            $left = ' LEFT JOIN cours ON cours.idEnseignant = boutique.id';
            $tidClasse = ' AND cours.idClasse = :idClasse';
            $tab[':idClasse'] = $idClasse;
        }else{
            $tidClasse = '';
        }
        if(isset($debut)){
            $tDebut = ' AND DATE(boutique.created_at) >= :debut';
            $tab[':debut'] = $debut;
        }else{
            $tDebut = '';
        }
        if(isset($fin)){
            $tFin = ' AND DATE(boutique.created_at) <= :fin';
            $tab[':fin'] = $fin;
        }else{
            $tFin = '';
        }
        if(isset($conDebut)){
            $tDebutCon = ' AND DATE(last_login) >= :debutCon';
            $tab[':debutCon'] = $conDebut;
        }else{
            $tDebutCon = '';
        }
        if(isset($conFin)){
            $tFinCon = ' AND DATE(last_login) <= :finCon';
            $tab[':finCon'] = $conFin;
        }else{
            $tFinCon = '';
        }
        if(isset($sexe)){
            $tSexe = ' AND sexe = :sexe';
            $tab[':sexe'] = $sexe;
        }else{
            $tSexe = '';
        }
        if(isset($type)){
            $ttype = ' AND (type = :type OR type = 3)';
            $tab[':type'] = $type;
        }else{
            $ttype = '';
        }
        if(isset($idProfile)){
            $tidProfile = ' AND idProfile = :idProfile';
            $tab[':idProfile'] = $idProfile;
        }else{
            $tidProfile = '';
        }
        return self::query($count.$left.$where.$tSearch.$tidProfile.$tDebut.$ttype.$tidClasse.$tFin.$tDebutCon.$tFinCon.$tSexe,$tab,true);
    }

    public static function searchType($nbreParPage=null,$pageCourante=null,$type = null,$idClasse = null,$search = null, $sexe = null,$debut=null,$fin=null,$conDebut = null, $conFin = null, $idProfile = null){
        $sql = 'SELECT boutique.* FROM '.self::getTable();
        $limit = ' ORDER BY last_login DESC,boutique.created_at DESC';
        $limit .= (isset($nbreParPage)&&isset($pageCourante))?' LIMIT '.(($pageCourante-1)*$nbreParPage).','.$nbreParPage:'';
        $where = ' WHERE 1 = 1';
        $tab = [];
        $left = '';
        if(isset($idProfile)){
            $tidProfile = ' AND idProfile = :idProfile';
            $tab[':idProfile'] = $idProfile;
        }else{
            $tidProfile = '';
        }
        if(isset($search)){
            $tSearch = ' AND (nom LIKE :search OR prenom LIKE :search OR numero LIKE :search OR cni LIKE :search OR email LIKE :search OR matricule LIKE :search)';
            $tab[':search'] = '%'.$search.'%';
        }else{
            $tSearch = '';
        }
        if(isset($idClasse)){
            $left = ' LEFT JOIN cours ON cours.idEnseignant = boutique.id';
            $tidClasse = ' AND cours.idClasse = :idClasse';
            $tab[':idClasse'] = $idClasse;
        }else{
            $tidClasse = '';
        }
        if(isset($debut)){
            $tDebut = ' AND DATE(boutique.created_at) >= :debut';
            $tab[':debut'] = $debut;
        }else{
            $tDebut = '';
        }
        if(isset($fin)){
            $tFin = ' AND DATE(boutique.created_at) <= :fin';
            $tab[':fin'] = $fin;
        }else{
            $tFin = '';
        }
        if(isset($conDebut)){
            $tDebutCon = ' AND DATE(last_login) >= :debutCon';
            $tab[':debutCon'] = $conDebut;
        }else{
            $tDebutCon = '';
        }
        if(isset($conFin)){
            $tFinCon = ' AND DATE(last_login) <= :finCon';
            $tab[':finCon'] = $conFin;
        }else{
            $tFinCon = '';
        }
        if(isset($sexe)){
            $tSexe = ' AND sexe = :sexe';
            $tab[':sexe'] = $sexe;
        }else{
            $tSexe = '';
        }
        if(isset($type)){
            $ttype = ' AND (type = :type OR type = 3)';
            $tab[':type'] = $type;
        }else{
            $ttype = '';
        }
        return self::query($sql.$left.$where.$tSearch.$tDebut.$ttype.$tidProfile.$tFin.$tDebutCon.$tidClasse.$tFinCon.$tSexe.$limit,$tab);
    }


}