<?php
/**
 * Created by PhpStorm.
 * Eleve: Ndjeunou
 * Date: 13/10/2016
 * Time: 23:25
 */

use Projet\Database\Cours;
use Projet\Database\Enseignant;
use Projet\Model\App;
use Projet\Model\DateParser;
use Projet\Model\Encrypt;
use Projet\Model\FileHelper;
use Projet\Model\Paginator;
use Projet\Model\Privilege;
use Projet\Model\StringHelper;

$url = substr(explode('?',$_SERVER["REQUEST_URI"])[0],1);
$laPage = isset($_GET['page'])?$_GET['page']:1;
$paginator = new Paginator($url,$laPage,$nbrePages,$_GET,$_GET);
App::setTitle("Le staff");
App::setNavigation("Le Staff");
App::setBreadcumb('<li class="active">Classes</li>');
App::addStyle('assets/css/bootstrap-multiselect.css',true);
App::addScript('assets/js/bootstrap-multiselect.js',true);
App::addScript('assets/js/admin.js',true);
?>
<div class="row">
    <div class="col-md-12">
        <div class="panel divBox panel-white">
            <div class="panel-heading">
                <h5 class="panel-title">
                    Membres du Personnel <small>(<?= thousand($nbre->Total); ?>)</small>
                </h5>
                <div class="panel-control">
                    <?php if(Privilege::canView(Privilege::$ecoleAddPersonnel,$user->privilege)){ ?>
                        <a href="javascript:void(0);" data-toggle="tooltip" class="new" data-original-title="Nouveau membre du personnel">
                            <i class="icon-plus text-success fa-2x"></i>
                        </a>
                    <?php } ?>
                    <a href="javascript:void(0);" data-toggle="tooltip"class="panel-collapse" data-original-title="Reduire/Agrandir">
                        <i class="icon-arrow-down fa-2x"></i>
                    </a>
                    <a href="<?= App::url('admin') ?>" data-toggle="tooltip" class="panel-reload" data-original-title="Rafraichir">
                        <i class="icon-reload fa-2x"></i>
                    </a>
                </div>
            </div>
            <div class="panel-body">
                <div class="row m-t-sm">
                    <div class="col-md-12">
                        <form action="<?= App::url('admin') ?>" method="get">
                            <div class="row">
                                <div class="col-md-3 form-group">
                                    <input type="text" class="form-control" <?= (isset($_GET['login_debut'])&&!empty($_GET['login_debut']))?'value="'.$_GET['login_debut'].'"':''; ?>
                                           data-toggle="tooltip" data-original-title="Chercher par date connexion min" name="login_debut" id="login_debut" placeholder="Chercher par date connexion min">
                                </div>
                                <div class="col-md-3 form-group">
                                    <input type="text" class="form-control" <?= (isset($_GET['login_end'])&&!empty($_GET['login_end']))?'value="'.$_GET['login_end'].'"':''; ?>
                                           data-toggle="tooltip" data-original-title="Chercher par date connexion max" name="login_end" id="login_end" placeholder="Chercher par date connexion max">
                                </div>
                                <div class="col-md-3 form-group">
                                    <input type="text" class="form-control" <?= (isset($_GET['debut'])&&!empty($_GET['debut']))?'value="'.$_GET['debut'].'"':''; ?>
                                           data-toggle="tooltip" data-original-title="Chercher par date ajout min" name="debut" id="debut" placeholder="Chercher par date ajout min">
                                </div>
                                <div class="col-md-3 form-group">
                                    <input type="text" class="form-control" <?= (isset($_GET['end'])&&!empty($_GET['end']))?'value="'.$_GET['end'].'"':''; ?>
                                           data-toggle="tooltip" data-original-title="Chercher par date ajout max" name="end" id="end" placeholder="Chercher par date ajout max">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-9 form-group">
                                    <input type="text" class="form-control" <?= (isset($_GET['search'])&&!empty($_GET['search']))?'value="'.$_GET['search'].'"':''; ?>
                                           data-toggle="tooltip" data-original-title="Chercher par nom,prénom,numéro CNI, numéro téléphone, email, fonction ou matricule" name="search" placeholder="Chercher par nom,prénom,numéro CNI, numéro téléphone, email, fonction ou matricule">
                                </div>
                                <div class="col-md-3 form-group">
                                    <select class="form-control" name="sexe" data-toggle="tooltip" data-original-title="Chercher par sexe">
                                        <option value="">Chercher par sexe</option>
                                        <option value="Masculin" <?= (isset($_GET['sexe']) && $_GET['sexe'] == 'Masculin') ? 'selected' : ''; ?>>Masculin</option>
                                        <option value="Feminin" <?= (isset($_GET['sexe']) && $_GET['sexe'] == 'Feminin') ? 'selected' : ''; ?>>Feminin</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-offset-10 col-md-2">
                                    <button class="btn btn-block btn-warning" type="submit">Chercher</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="row m-t-sm">
                    <div class="col-md-12">
                        <div class="table-responsive project-stats">
                            <table class="table table-striped table-hover">
                                <thead>
                                <tr>
                                    <th>Photo</th>
                                    <th>Matricule</th>
                                    <th>Noms</th>
                                    <th>Numéro</th>
                                    <th>Fonction</th>
                                    <th>Sexe</th>
                                    <th>Etat</th>
                                    <th class="text-center">#</th>
                                    <th class="text-center">Connexion</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php
                                if(!empty($personnels)){
                                    foreach ($personnels as $personnel) {
                                        $sta1 = "";
                                        $sta2 = "";
                                        if($personnel->etat==0||$personnel->etat==2){
                                            $sta1 = '<li>
                                                            <a href="javascript:void(0);" data-url="'.App::url('enseignant/activate').'" 
                                                            class="activate" data-etat="'.$personnel->etat.'" data-id="'.$personnel->id.'">Activer l\'enseignant</a>
                                                        </li>';
                                        }else{
                                            $sta1 = '<li>
                                                            <a href="javascript:void(0);" data-url="'.App::url('enseignant/activate').'" 
                                                            class="activate" data-etat="'.$personnel->etat.'" data-id="'.$personnel->id.'">Désactiver l\'enseignant</a>
                                                        </li>';
                                        }
                                        if($personnel->etat!=2){
                                            $sta2 = '<li>
                                                            <a href="javascript:void(0);" data-url="'.App::url('admin/activate').'" 
                                                            class="activate" data-id="'.$personnel->id.'">Supprimer l\'enseignant</a>
                                                        </li>';
                                        }
                                        $stat1 = $stat2 = $stat3 = "";
                                        if(Privilege::canView(Privilege::$ecoleEditPersonnel,$user->privilege)){
                                            $stat3 = '<li>
                                                                <a href="javascript:void(0);" data-id="'.$personnel->id.'" data-profil="'.$personnel->idProfile.'"
                                                                 data-nom="'.$personnel->nom.'"  data-prenom="'.$personnel->prenom.'"
                                                                 data-sexe="'.$personnel->sexe.'"  data-naissance="'.date(DATE_FORMAT,strtotime($personnel->naissance)).'"
                                                                 data-cni="'.$personnel->cni.'"  data-diplome="'.$personnel->diplome.'" data-classes="'.$personnel->idClasses.'"
                                                                 data-numero="'.$personnel->numero.'"  data-email="'.$personnel->email.'"
                                                                 data-ville="'.$personnel->ville.'"  data-quartier="'.$personnel->quartier.'"
                                                                 data-adresse="'.$personnel->adresse.'"  data-minibio="'.$personnel->minibio.'"
                                                                class="edit" data-fonction="'.$personnel->fonction.'">Modifier mes infos</a>
                                                            </li>'.$sta1.$sta2.'
                                                            <li class="divider"></li>
                                                            <li>
                                                                <a href="javascript:void(0);" class="editPhoto" 
                                                                data-id="'.$personnel->id.'">Modifier ma photo</a>
                                                            </li>
                                                            <li>
                                                                <a href="javascript:void(0);" class="editCni" 
                                                                data-id="'.$personnel->id.'">Modifier mon fichier de CNI</a>
                                                            </li>
                                                            <li>
                                                                <a href="javascript:void(0);" class="editDiplome" 
                                                                data-id="'.$personnel->id.'">Modifier mon fichier du diplôme</a>
                                                            </li>';
                                        }
                                        if(Privilege::canView(Privilege::$ecoleResetPersonnel,$user->privilege)){
                                            $stat2 = '<li>
                                                                <a href="javascript:void(0);" data-url="'.App::url('admin/resetPass').'" 
                                                                class="reset" data-id="'.$personnel->id.'">Réinitialiser mon mot de passe</a>
                                                            </li>';
                                        }
                                        if(Privilege::canView(Privilege::$ecolePersonnelDetail,$user->privilege)){
                                            $stat1 = '<li>
                                                                <a href="'.App::url('admin/detail?id='.Encrypt::crypter($personnel->id)).'">Détail</a>
                                                            </li>';
                                        }
                                        echo
                                            '
                                            <tr>
                                                <td><img src="'.FileHelper::url($personnel->photo).'" class="img-circle img-xs" alt="Img"></td>
                                                <th scope="row">'.$personnel->matricule.'</th>
                                                <td>'.StringHelper::getShortName($personnel->nom,$personnel->prenom).' ('.DateParser::calculAge($personnel->naissance).')</td>
                                                <td>'.thousand($personnel->numero).'</td>
                                                <td>'.$personnel->fonction.'</td>
                                                <td>'.$personnel->sexe.'</td>
                                                <td>'.StringHelper::$tabState[$personnel->etat].'</td>
                                                <td class="text-center">
                                                    <div class="btn-group">
                                                        <button type="button" class="btn btn-default btn-xs dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                                                            Actions <span class="caret"></span>
                                                        </button>
                                                        <ul class="dropdown-menu" role="menu">
                                                            '.$stat1.$stat2.$stat3.'
                                                        </ul>
                                                    </div>
                                                </td>
                                                <td class="text-center">'.DateParser::DateShort($personnel->last_login,1).'</td>
                                            </tr>
                                            ';
                                    }}else{
                                    echo '<tr><td colspan="9" class="text-danger text-center">Liste des membres du staff vide ...</td></tr>';
                                }
                                ?>
                                </tbody>
                                <?php
                                if(!empty($personnels)){ ?>
                                    <tfoot>
                                    <tr>
                                        <td colspan="9">
                                            <?php $paginator->paginateTwo(); ?>
                                        </td>
                                    </tr>
                                    </tfoot>
                                <?php } ?>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if(Privilege::canView(Privilege::$ecoleAddPersonnel,$user->privilege)||Privilege::canView(Privilege::$ecoleEditPersonnel,$user->privilege)){ ?>
    <div class="modal fade newModal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content divBox">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h2 class="modal-title titleForm">Titre</h2>
                </div>
                <form action="<?= App::url('admin/save') ?>" id="newForm" method="post">
                    <div class="modal-body">
                        <input type="hidden" id="action">
                        <input type="hidden" id="idElement">
                        <p class="textRed text-right"><small>*: Champs obligatoires</small></p>
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label for="nom">Nom <b>*</b></label>
                                <input type="text" class="form-control" id="nom" placeholder="Nom">
                            </div>
                            <div class="col-md-6 form-group">
                                <label for="prenom">Prénom</label>
                                <input type="text" class="form-control" id="prenom" placeholder="Prénom">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label for="sexe">Sexe  <b>*</b></label>
                                <select name="sexe" id="sexe" class="form-control">
                                    <option value="">............</option>
                                    <option value="Masculin">Masculin</option>
                                    <option value="Feminin">Feminin</option>
                                </select>
                            </div>
                            <div class="col-md-6 form-group">
                                <label for="naissance">Date de naissance  <b>*</b></label>
                                <input type="text" class="form-control" id="naissance" placeholder="Date de naissance">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label for="numero">Numéro detéléphone  <b>*</b></label>
                                <input type="tel" class="form-control" id="numero" placeholder="Numéro detéléphone">
                            </div>
                            <div class="col-md-6 form-group">
                                <label for="email">Adresse email</label>
                                <input type="email" class="form-control" id="email" placeholder="Adresse email">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label for="cni">Numéro de CNI  <b>*</b></label>
                                <input type="text" class="form-control" id="cni" placeholder="Numéro carte d'identité nationale">
                            </div>
                            <div class="col-md-6 form-group">
                                <label for="ville">Ville  <b>*</b></label>
                                <input type="text" class="form-control" id="ville" placeholder="Ville d'habitation">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label for="quartier">Quartier <b>*</b></label>
                                <input type="text" class="form-control" id="quartier" placeholder="Quartier">
                            </div>
                            <div class="col-md-6 form-group">
                                <label for="adresse">Adresse</label>
                                <input type="text" class="form-control" id="adresse" placeholder="Adresse">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label for="profil">Profil administrateur  <b>*</b></label>
                                <select name="profil" id="profil" class="form-control">
                                    <option value="">............</option>
                                    <?php
                                    foreach ($profiles as $profile){
                                        echo '<option value="'.$profile->id.'">'.$profile->nom.'</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-6 form-group">
                                <label for="classes">Classes sous la charge </label>
                                <select name="classes" id="classes" multiple class="form-control">
                                    <?php
                                    foreach ($classes as $classe){
                                        echo '<option value="'.$classe->id.'">'.$classe->nom.'</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12 form-group">
                                <label for="minibio">Mini biographie</label>
                                <textarea class="form-control" id="minibio" rows="3" placeholder="Dites un peu plus sur vous"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="newBtn btn btn-success">Ajouter</button>
                        <button type="button" class="btn btn-danger" data-dismiss="modal">Annuler</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php } ?>

<?php if(1){ ?>
    <div class="modal fade photoModal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content divBox">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h2 class="modal-title">METTRE LA PHOTO A JOUR</h2>
                </div>
                <form action="<?= App::url('admin/setPhoto') ?>" id="formPhoto" method="post" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" id="idPhoto" name="idPhoto">
                        <div class="row">
                            <div class="col-md-12 form-group">
                                <label for="photoImage">Photo</label>
                                <input type="file" class="form-control" accept="image/*" id="photoImage" name="image">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="photoBtn btn btn-success">MISE A JOUR</button>
                        <button type="button" class="btn btn-danger" data-dismiss="modal">Annuler</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal fade cniModal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content divBox">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h2 class="modal-title">METTRE LE FICHIER DE LA CNI</h2>
                </div>
                <form action="<?= App::url('admin/setCni') ?>" id="formCni" method="post" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" id="idCni" name="idCni">
                        <div class="row">
                            <div class="col-md-12 form-group">
                                <label for="photoImage">Images de la CNI</label>
                                <input type="file" multiple class="form-control" accept="image/*" id="photoImage" name="image[]">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="cniBtn btn btn-success">MISE A JOUR</button>
                        <button type="button" class="btn btn-danger" data-dismiss="modal">Annuler</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

<?php } ?>
