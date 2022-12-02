<?php
/**
 * Created by PhpStorm.
 * Eleve: Ndjeunou
 * Date: 13/10/2016
 * Time: 23:25
 */

use Projet\Database\Vues;
use Projet\Database\Worker;
use Projet\Model\App;
use Projet\Model\DateParser;
use Projet\Model\FileHelper;
use Projet\Model\Paginator;
use Projet\Model\Privilege;
use Projet\Model\StringHelper;

$url = substr(explode('?',$_SERVER["REQUEST_URI"])[0],1);
$laPage = isset($_GET['page'])?$_GET['page']:1;
$paginator = new Paginator($url,$laPage,$nbrePages,$_GET,$_GET);
App::setTitle("La boutique de $profil->username");
App::setNavigation("La boutique de $profil->username");
App::setBreadcumb('<li><a href="javascript:void(0);" onclick="history.go(-1);return false;">Clients</a></li><li class="active">Boutique</li>');
App::addScript('assets/js/commande.js',true);
?>
<div class="row">
    <div class="col-md-12">
        <div class="panel panel-dark">
            <div class="panel-heading">
                <h5 class="panel-title">
                    <?= "Boutique de $profil->username <small>(".thousand($nbre->Total).")</small>" ?>
                </h5>
                <div class="panel-control">
                    <a href="javascript:void(0);" data-toggle="tooltip" class="panel-collapse" data-original-title="Reduire/Agrandir">
                        <i class="icon-arrow-down fa-2x"></i>
                    </a>
                    <a href="<?= App::url('users/boutique?id='.$_GET['id']) ?>" data-toggle="tooltip" class="panel-reload" data-original-title="Rafraichir">
                        <i class="icon-reload fa-2x"></i>
                    </a>
                    <?php if(Privilege::canView(Privilege::$eshopCommandEtat,$user->privilege)){ ?>
                        <a target="_blank" href="<?= App::url('commandes/pdf?id='.$id.'&etat='.$s_etat.'&ref ='.$s_ref .'&debut='.$s_debut.'&end='.$s_end) ?>"
                           data-toggle="tooltip" data-original-title="Generer le fichier PDF des commandes" ><i class="fa fa-file-pdf-o fa-2x text-primary"></i></a>
                        <a target="_blank" href="<?= App::url('commandes/excell?id='.$id.'&etat='.$s_etat.'&ref ='.$s_ref .'&debut='.$s_debut.'&end='.$s_end) ?>"
                           data-toggle="tooltip" data-original-title="Generer le fichier Excel des commandes" ><i class="fa fa-file-excel-o fa-2x text-info"></i></a>
                    <?php } ?>

                </div>
            </div>
            <div class="panel-body">
                <div class="row m-t-sm">
                    <div class="col-md-12">
                        <form action="<?= App::url('produits') ?>" method="get">
                            <div class="row">
                                <div class="col-md-4 form-group">
                                    <input type="text" class="form-control" <?= (isset($_GET['search'])&&!empty($_GET['search']))?'value="'.$_GET['search'].'"':''; ?>
                                           data-toggle="tooltip" data-original-title="Chercher par nom, sku ou supplier code" name="search" placeholder="Chercher par nom, sku ou supplier code">
                                </div>
                                <div class="col-md-4 form-group">
                                    <select class="form-control" name="cat" id="catId" data-toggle="tooltip" data-original-title="Chercher par la catégorie">
                                        <option value="">Chercher par la catégorie</option>
                                        <?php
                                        foreach ($categories as $categorie){
                                            $is = isset($_GET['cat'])&&$_GET['cat']==$categorie->id?' selected':'';
                                            echo '<option value="'.$categorie->id.'"'.$is.'>'.$categorie->category_name.'</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="col-md-4 form-group">
                                    <select class="form-control" name="categorie" id="sousId" data-toggle="tooltip" data-original-title="Chercher par la sous catégorie">
                                        <option value="">Chercher par la sous catégorie</option>
                                        <?php
                                        foreach ($sousCat as $sou){
                                            $is = isset($_GET['categorie'])&&$_GET['categorie']==$sou->id?' selected':'';
                                            echo '<option value="'.$sou->id.'"'.$is.'>'.$sou->subcategory_name.'</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="col-md-4 form-group">
                                    <select class="form-control" name="stock" data-toggle="tooltip" data-original-title="Chercher par disponibilité du stock">
                                        <option value="">Chercher par disponibilité du stock</option>
                                        <option value="1" <?= (isset($_GET['stock']) && $_GET['stock'] == 1) ? 'selected' : ''; ?>>Stock disponible</option>
                                        <option value="2" <?= (isset($_GET['stock']) && $_GET['stock'] == 2) ? 'selected' : ''; ?>>Stock épuisé</option>
                                    </select>
                                </div>
                                <div class="col-md-4 form-group">
                                    <select class="form-control" name="etat" data-toggle="tooltip" data-original-title="Chercher par etat">
                                        <option value="">Chercher par etat</option>
                                        <option value="2" <?= (isset($_GET['etat']) && $_GET['etat'] == 2) ? 'selected' : ''; ?>>Actif</option>
                                        <option value="1" <?= (isset($_GET['etat']) && $_GET['etat'] == 1) ? 'selected' : ''; ?>>Inactif</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-offset-10 col-md-2">
                                    <button class="btn btn-block btn-default" type="submit">Chercher</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="row m-t-sm">
                    <div class="col-md-12">
                        <div class="table-responsive project-stats">
                            <table class="table table-striped">
                                <thead>
                                <tr>
                                    <th class="text-center" style="width: 15%">Image</th>
                                    <th class="">Produit</th>
                                    <th class="">Catégorie / Sous</th>
                                    <th class="text-center">Prix</th>
                                    <th class="text-center">Réduction</th>
                                    <th class="text-center">Etat</th>
                                    <th class="text-center">#</th>
                                    <th class="text-right">En Stock</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php
                                if(!empty($articles)){
                                    foreach ($articles as $article) {
                                        $stat1 = $stat2 = $stat3 = $stat01 = $stat02 = $stat03 = $stat04 = $stat05 = $stat06 = $stat07 = $stat08 = $stat09 = $stat19 = "";
                                        if($article->status == 0&&Privilege::canView(Privilege::$eshopProductActivation,$user->privilege)){
                                            $stat09 = '<li>
                                                            <a href="javascript:void(0);" data-url="'.App::url('produits/setEtat').'" 
                                                            class="activate" data-nom="'.$article->productname.'" data-etat="'.$article->status.'" data-id="'.$article->productid.'">Activer le produit</a>
                                                        </li>';
                                        }
                                        if($article->status != 0&&Privilege::canView(Privilege::$eshopProductDesactivation,$user->privilege)){
                                            $stat09 = '<li>
                                                            <a href="javascript:void(0);" data-url="'.App::url('produits/setEtat').'" 
                                                            class="activate" data-nom="'.$article->productname.'" data-etat="'.$article->status.'" data-id="'.$article->productid.'">Désactiver le produit</a>
                                                        </li>';
                                        }
                                        if(Privilege::canView(Privilege::$eshopProductChangeImg,$user->privilege)){
                                            $stat05 = '
                                                            <li>
                                                                <a href="javascript:void(0);" class="editPhoto" 
                                                                data-id="'.$article->productid.'" data-nom="'.$article->productname.'">Ajouter des images</a>
                                                            </li>';
                                            $stat3 = '<li class="divider"></li>
                                                            <li>
                                                                <a href="javascript:void(0);" class="editImage" 
                                                                data-id="'.$article->productid.'" data-nom="'.$article->productname.'">Changer l\'image principale</a>
                                                            </li>';
                                        }
                                        if(Privilege::canView(Privilege::$eshopProductEdit,$user->privilege)){
                                            $stat01 = '<li>
                                                            <a href="javascript:void(0);"  data-id="'.$article->id.'" data-length="'.$article->length.'"
                                                             data-nom="'.$article->productname.'"  data-cat="'.$article->category_id.'" data-prix="'.$article->price.'"
                                                             data-offre="'.$article->offer_price.'" data-sous="'.$article->sub_category.'" data-width="'.$article->width.'"
                                                             data-height="'.$article->height.'"  data-weight="'.$article->weight.'"
                                                             data-slug="'.$article->slug.'"  data-deal="'.$article->deal.'"data-best_sellers="'.$article->best_sellers.'"
                                                             data-new_arrival="'.$article->new_arrival.'"  data-special_offer="'.$article->special_offer.'"
                                                             class="edit"> Modifier </a>
                                                            <div class="bibio hide">'.$article->description.'</div>
                                                        </li>';
                                        }
                                        if(Privilege::canView(Privilege::$eshopProductAddToDeal,$user->privilege)){
                                            $stat02 = '<li class="divider"></li><li>
                                                            <a href="javascript:void(0);" class="deal" data-prix="'.$article->price.'"
                                                            data-id="'.$article->productid.'" data-nom="'.$article->productname.'">Ajouter comme Deal du jour</a>
                                                        </li>';
                                        }
                                        if(Privilege::canView(Privilege::$eshopProductAddToStock,$user->privilege)){
                                            $stat02 .= '<li>
                                                                <a href="javascript:void(0);" class="editStock" 
                                                                data-id="'.$article->productid.'" data-nom="'.$article->productname.'">Augmenter le stock</a>
                                                            </li>';
                                        }
                                        if(Privilege::canView(Privilege::$eshopProductMoveToStock,$user->privilege)){
                                            $stat02 .= '<li>
                                                            <a href="javascript:void(0);" class="delStock" 
                                                                data-id="'.$article->productid.'" data-nom="'.$article->productname.'">Diminuer le stock</a>
                                                            </li>';
                                        }
                                        $nbreLigne = checkout_orders::countBySearchType($article->productid);
                                        $nbreNote = product_review::countBySearchType(null,$article->productid);
                                        if(Privilege::canView(Privilege::$eshopProductSaleView,$user->privilege)){
                                            $stat07 = '<li class="divider"></li>
                                                            <li><a href="'.App::url('produits/commandes?id='.$article->productid).'">Ventes ('.thousand($nbreLigne->Total).')</a></li>';
                                        }
                                        if(Privilege::canView(Privilege::$eshopProductNoteView,$user->privilege)){
                                            $stat07 = '<li class="divider"></li>
                                                            <li><a href="'.App::url('produits/reviews?id='.$article->productid).'">Notes ('.thousand($nbreNote->Total).')</a></li>';
                                        }
                                        $cat = category::find($article->category_id);
                                        $sub = subcategory::find($article->sub_category);
                                        echo
                                            '
                                            <tr>
                                                <td class="text-center"><img src="'.FileHelper::url($article->image).'" class="img-ld" alt="Img"></td>
                                                <td class="">
                                                    <b>'.ucfirst($article->productname).'</b> <br> 
                                                </td>
                                                <td class="">'.$cat->category_name.'<br><i class="glyphicon glyphicon-arrow-down"></i><br>'.$sub->subcategory_name.'</td>
                                                <td class="text-center">
                                                    <div class="style-1">
                                                      <del>
                                                        <span class="amount">$'.float_value($article->price).'</span>
                                                      </del>
                                                      <ins>
                                                        <span class="amount">$'.float_value($article->offer_price).'</span>
                                                      </ins>
                                                    </div>
                                                </td>
                                                <td class="text-center">'.$article->discount.'%</td>
                                                <td class="text-center">'.StringHelper::$tabArticleState[$article->status].'</td>
                                                <td class="text-center">
                                                    <div class="btn-group">
                                                        <button type="button" class="btn btn-default btn-xs dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                                                            Actions <span class="caret"></span>
                                                        </button>
                                                        <ul class="dropdown-menu" role="menu">
                                                            <li>
                                                                <a href="javascript:void(0);" class="detail" data-url="'.App::url('produits/detail').'" data-id="'.$article->productid.'">Détail</a>
                                                            </li>
                                                            '.$stat01.$stat09.$stat02.$stat04.$stat3.$stat05.$stat06.$stat07.$stat08.'
                                                        </ul>
                                                    </div>
                                                </td>
                                                <td class="text-right color-primary"><b>'.thousand($article->qty).'</b></td>
                                            </tr>
                                            ';
                                    }}else{
                                    echo '<tr><td colspan="8" class="text-danger text-center">Liste des produits vide ...</td></tr>';
                                }
                                ?>
                                </tbody>
                                <?php
                                if(!empty($articles)){ ?>
                                    <tfoot>
                                    <tr>
                                        <td colspan="8">
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
<div class="modal fade messageModal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h2 class="modal-title titleForm">VALIDATION DE LA COMMANDE</h2>
            </div>
            <div class="modal-body">
                <div class="loader">
                    <p class="text-center m-t-lg"><img class="img-xs" src="<?= FileHelper::url('assets/images/load.gif') ?>" alt=""></p>
                </div>
                <div class="contenus hide">

                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade newModal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h2 class="modal-title titleForm">Titre</h2>
            </div>
            <form action="<?= App::url('commandes/dateDelivery') ?>" id="newForm" method="post" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" id="action" name="action">
                    <input type="hidden" id="idElement" name="id">
                    <p class="mainColor text-right">* Champs obligatoires</p>
                    <div class="row">
                        <div class="col-md-12 ">
                            <label for="date_delivery">Date de livraison<b>*</b></label>
                            <input type="text" class="form-control" id="date_delivery" name="date_delivery">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="newBtn1 btn btn-default">Ajouter</button>
                    <button type="button" class="btn btn-warning" data-dismiss="modal">Annuler</button>
                </div>
            </form>
        </div>
    </div>
</div>