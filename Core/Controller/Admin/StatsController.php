<?php
/**
 * Created by IntelliJ IDEA.
 * User: Fabrice
 * Date: 05/06/2017
 * Time: 12:37
 */

namespace Projet\Controller\Admin;


use Exception;
use Mpdf\Mpdf;
use Mpdf\MpdfException;
use Projet\Database\affiliate_portfolio_profile;
use Projet\Database\affiliate_user;
use Projet\Database\category;
use Projet\Database\checkout_orders;
use Projet\Database\orders;
use Projet\Database\products;
use Projet\Database\Profil;
use Projet\Database\schedule_meeting;
use Projet\Database\subcategory;
use Projet\Database\users;
use Projet\Database\wallet;
use Projet\Database\withdraw_request;
use Projet\Model\App;
use Projet\Model\DateParser;
use Projet\Model\FileHelper;
use Projet\Model\Privilege;
use Projet\Model\StringHelper;

class StatsController extends AdminController {

    public function produits(){
        Privilege::hasPrivilege(Privilege::$eshopProductEtat,$this->user->privilege);
        $s_search = (isset($_GET['search'])&&!empty($_GET['search'])) ? $_GET['search'] : null;
        $s_cat = (isset($_GET['cat'])&&!empty($_GET['cat'])) ? $_GET['cat'] : null;
        $s_categorie = (isset($_GET['categorie'])&&!empty($_GET['categorie'])) ? $_GET['categorie'] : null;
        $s_etat = (isset($_GET['etat'])&&!empty($_GET['etat'])) ? $_GET['etat']-1 : null;
        $s_stock = (isset($_GET['stock'])&&!empty($_GET['stock'])) ? $_GET['stock'] : null;
        $articles = products::searchType(null,null,$s_search,$s_cat,$s_categorie,$s_stock,$s_etat);
        try{
            $mpdf = new Mpdf(['mode' => 'utf-8', 'format' => 'A4-L','margin_left' => 5,'margin_right' => 5,'margin_top' => 15,'margin_bottom' => 20,'margin_header' => 15,'margin_footer' => 10]);
            $mpdf->SetProtection(array('print'));
            $mpdf->SetTitle('Liste des produits');
            $mpdf->SetAuthor("Dikla lucien");
            $mpdf->SetWatermarkText('Produits | Brancos');
            $mpdf->showWatermarkText = true;
            $mpdf->watermarkTextAlpha = 0.1;
            $mpdf->SetDisplayMode('fullpage');
            $mpdf->useAdobeCJK = true;
            $mpdf->autoScriptToLang = true;
            $mpdf->autoLangToFont = true;
            ob_start();
            $transac = ['articles'=>$articles];
            $header = '';
            $head = ['headerTab'=>$header];
            $variables = array_merge($transac,$head);
            extract($variables);
            require 'Views/Prints/produits.php';
            $html = ob_get_contents();
            ob_end_clean();
            $mpdf->WriteHTML($html);
            $mpdf->Output('Pdf_Liste_Des_Produits'.date('Y-m-d_i:s').'_'.time().'.pdf', 'I'); # D
        }catch (MpdfException $e){}
    }

    public function produitsExcell(){
        Privilege::hasPrivilege(Privilege::$eshopProductEtat,$this->user->privilege);
        require 'Excel_XML.php';
        $s_search = (isset($_GET['search'])&&!empty($_GET['search'])) ? $_GET['search'] : null;
        $s_cat = (isset($_GET['cat'])&&!empty($_GET['cat'])) ? $_GET['cat'] : null;
        $s_categorie = (isset($_GET['categorie'])&&!empty($_GET['categorie'])) ? $_GET['categorie'] : null;
        $s_etat = (isset($_GET['etat'])&&!empty($_GET['etat'])) ? $_GET['etat'] : null;
        $s_stock = (isset($_GET['stock'])&&!empty($_GET['stock'])) ? $_GET['stock'] : null;
        $articles = products::searchType(null,null,$s_search,$s_cat,$s_categorie,$s_stock,$s_etat);
        $datas = [];
        $i = 0;
        try{
            $datas[0] = array('SKU','Intitulé','Supplier Code','Type','Catégorie','Sous Catégorie','Prix','Prix Offert','Reduction','En Stock');
            foreach ($articles as $article) {
                $cat = category::find($article->category_id);
                $sub = subcategory::find($article->sub_category);
                $i++;
                $datas[]=array($article->sku,$article->productname,$article->supplier_code,$article->package_type,
                    $cat->category_name,$sub->subcategory_name,thousand($article->price),thousand($article->offer_price)
                ,$article->discount,$article->qty);
            }
            $name = 'Excel_Liste_Des_Produits_'.date('Y-m-d_i:s').'_'.time();
            $xls = new \Excel_XML();
            $xls->addWorksheet('Produits', $datas);
            $xls->sendWorkbook($name.'.xls');
        }catch (Exception $e){}
    }

    public function commandes(){
        Privilege::hasPrivilege(Privilege::$eshopCommandEtat,$this->user->privilege);
        $id = (isset($_GET['id'])&&!empty($_GET['id'])) ? $_GET['id'] : null;
        $s_etat = (isset($_GET['etat'])&&!empty($_GET['etat'])) ? $_GET['etat'] : null;
        $s_ref = (isset($_GET['ref'])&&!empty($_GET['ref'])) ? $_GET['ref'] : null;
        $s_debut = (isset($_GET['debut'])&&!empty($_GET['debut'])) ? date(MYSQL_DATE_FORMAT, strtotime($_GET['debut'])) : null;
        $s_end = (isset($_GET['end'])&&!empty($_GET['end'])) ? date(MYSQL_DATE_FORMAT, strtotime($_GET['end'])) : null;
        $commandes = orders::searchType(null,null,$id,$s_ref,$s_etat,$s_debut,$s_end);
        $nbre = orders::countBySearchType(null,$s_ref,$s_etat,$s_debut,$s_end);
        try{
            $mpdf = new Mpdf(['mode' => 'utf-8', 'format' => 'A4-L','margin_left' => 5,'margin_right' => 5,'margin_top' => 15,'margin_bottom' => 20,'margin_header' => 15,'margin_footer' => 10]);
            $mpdf->SetProtection(array('print'));
            $mpdf->SetTitle('Liste des Commandes');
            $mpdf->SetAuthor("Baba & Ousmanou");
            $mpdf->SetWatermarkText('Commandes | PLUMERS');
            $mpdf->showWatermarkText = true;
            $mpdf->watermarkTextAlpha = 0.1;
            $mpdf->SetDisplayMode('fullpage');
            $mpdf->useAdobeCJK = true;
            $mpdf->autoScriptToLang = true;
            $mpdf->autoLangToFont = true;
            ob_start();
            $transac = ['commandes'=>$commandes];
            $header = '';
            if(!is_null($id)){
                $client = users::find($id);
                $header .= '<tr><th style="border: 0;text-align: right">Client :</th><th style="border: 0; color: #00008B;text-align: left" class="meta-head">'.$client->username.'</th></tr>';
            }
            if(!is_null($s_etat)){
                $header .= '<tr><th style="border: 0;text-align: right">Etat :</th><td style="border: 0;" class="meta-head">'.$s_etat.'</td></tr>';
            }
            if(!is_null($s_ref)){
                $header .= '<tr><th style="border: 0;text-align: right">Reférence :</th><td style="border: 0;" class="meta-head">'.$s_ref.'</td></tr>';
            }
            if(!is_null($s_debut)){
                $header .= '<tr><th style="border: 0;text-align: right">De :</th><td style="border: 0;" class="meta-head">'.DateParser::DateShort($s_debut).'</td></tr>';
            }
            if(!is_null($s_end)){
                $header .= '<tr><th style="border: 0;text-align: right">A :</th><td style="border: 0;" class="meta-head">'.DateParser::DateShort($s_debut).'</td></tr>';
            }
            $header .= '<tr><th style="border: 0;text-align: right">Total :</th><th style="border: 0; color: #00008B;text-align: left" class="meta-head"><small>$</small>'.thousand($nbre->somme).'</th></tr>';
            $head = ['headerTab'=>$header];
            $variables = array_merge($transac,$head);
            extract($variables);
            require 'Views/Prints/commandes.php';
            $html = ob_get_contents();
            ob_end_clean();
            $mpdf->WriteHTML($html);
            $mpdf->Output('Pdf_Liste_Des_Commandes'.date('Y-m-d_i:s').'_'.time().'.pdf', 'I'); # D
        }catch (MpdfException $e){}
    }

    public function commandesExcell(){
        Privilege::hasPrivilege(Privilege::$eshopCommandEtat,$this->user->privilege);
        require 'Excel_XML.php';
        $id = (isset($_GET['id'])&&!empty($_GET['id'])) ? $_GET['id'] : null;
        $s_etat = (isset($_GET['etat'])&&!empty($_GET['etat'])) ? $_GET['etat'] : null;
        $s_ref = (isset($_GET['ref'])&&!empty($_GET['ref'])) ? $_GET['ref'] : null;
        $s_debut = (isset($_GET['debut'])&&!empty($_GET['debut'])) ? date(MYSQL_DATE_FORMAT, strtotime($_GET['debut'])) : null;
        $s_end = (isset($_GET['end'])&&!empty($_GET['end'])) ? date(MYSQL_DATE_FORMAT, strtotime($_GET['end'])) : null;
        $commandes = orders::searchType(null,null,$id,$s_ref,$s_etat,$s_debut,$s_end);
        $datas = [];
        $i = 0;
        try{
            $datas[0] = array('Date','Client','Reférence','Prix','Etat','Livrée le','Date livraison');
            foreach ($commandes as $commande)  {
                $client = users::find($commande->user_id);
                $nom = "";
                if($client) {
                    $nom = $client->username;
                }
                $i++;
                $datas[]=array(DateParser::DateShort($commande->created_date,1), $nom, $commande->order_id,
                    thousand($commande->paid_amount), $commande->transit_status,DateParser::DateShort($commande->delivery_date,1),DateParser::DateShort($commande->date_delivery));
                }
            $name = 'Excel_Liste_Des_Commandes_'.date('Y-m-d_i:s').'_'.time();
            $xls = new \Excel_XML();
            $xls->addWorksheet('Commandes ', $datas);
            $xls->sendWorkbook($name.'.xls');
        }catch (Exception $e){}

    }

    public function meetings(){
        Privilege::hasPrivilege(Privilege::$eshopMeetingEtat,$this->user->privilege);
        $s_etat = (isset($_GET['etat'])&&!empty($_GET['etat'])) ? $_GET['etat'] : null;
        $s_mode = (isset($_GET['mode'])&&!empty($_GET['mode'])) ? $_GET['mode'] : null;
        $s_debut = (isset($_GET['debut'])&&!empty($_GET['debut'])) ? date(MYSQL_DATE_FORMAT, strtotime($_GET['debut'])) : null;
        $s_end = (isset($_GET['end'])&&!empty($_GET['end'])) ? date(MYSQL_DATE_FORMAT, strtotime($_GET['end'])) : null;
        $meetings = schedule_meeting::searchType(null,null,null,null,$s_mode,$s_etat,$s_debut,$s_end);
        try{
            $mpdf = new Mpdf(['mode' => 'utf-8', 'format' => 'A4-L','margin_left' => 5,'margin_right' => 5,'margin_top' => 15,'margin_bottom' => 20,'margin_header' => 15,'margin_footer' => 10]);
            $mpdf->SetProtection(array('print'));
            $mpdf->SetTitle('Liste des Rendez-vous');
            $mpdf->SetAuthor("Baba & Ousmanou");
            $mpdf->SetWatermarkText('Rendez-vous | PLUMERS');
            $mpdf->showWatermarkText = true;
            $mpdf->watermarkTextAlpha = 0.1;
            $mpdf->SetDisplayMode('fullpage');
            $mpdf->useAdobeCJK = true;
            $mpdf->autoScriptToLang = true;
            $mpdf->autoLangToFont = true;
            ob_start();
            $transac = ['meetings'=>$meetings];
            $header = '';

            if(!is_null($s_etat)){
                $header .= '<tr><th style="border: 0;text-align: right">Etat :</th><td style="border: 0;" class="meta-head">'.$s_etat.'</td></tr>';
            }
            if(!is_null($s_mode)){
                $header .= '<tr><th style="border: 0;text-align: right">Mode :</th><td style="border: 0;" class="meta-head">'.$s_mode.'</td></tr>';
            }
            if(!is_null($s_debut)){
                $header .= '<tr><th style="border: 0;text-align: right">De :</th><td style="border: 0;" class="meta-head">'.DateParser::DateShort($s_debut).'</td></tr>';
            }
            if(!is_null($s_end)){
                $header .= '<tr><th style="border: 0;text-align: right">A :</th><td style="border: 0;" class="meta-head">'.DateParser::DateShort($s_debut).'</td></tr>';
            }
            $head = ['headerTab'=>$header];
            $variables = array_merge($transac,$head);
            extract($variables);
            require 'Views/Prints/meetings.php';
            $html = ob_get_contents();
            ob_end_clean();
            $mpdf->WriteHTML($html);
            $mpdf->Output('Pdf_Liste_Des_Rendez_vous'.date('Y-m-d_i:s').'_'.time().'.pdf', 'I'); # D
        }catch (MpdfException $e){}

    }

    public function meetingsExcell(){
        Privilege::hasPrivilege(Privilege::$eshopMeetingEtat,$this->user->privilege);
        require 'Excel_XML.php';
        $s_etat = (isset($_GET['etat'])&&!empty($_GET['etat'])) ? $_GET['etat'] : null;
        $s_mode = (isset($_GET['mode'])&&!empty($_GET['mode'])) ? $_GET['mode'] : null;
        $s_debut = (isset($_GET['debut'])&&!empty($_GET['debut'])) ? date(MYSQL_DATE_FORMAT, strtotime($_GET['debut'])) : null;
        $s_end = (isset($_GET['end'])&&!empty($_GET['end'])) ? date(MYSQL_DATE_FORMAT, strtotime($_GET['end'])) : null;
        $meetings = schedule_meeting::searchType(null,null,null,null,$s_mode,$s_etat,$s_debut,$s_end);
        $datas = [];
        $i = 0;
        try{
            $datas[0] = array('Date','Client','Affilié','Function','Mode','Statut','Demandé le');
            foreach ($meetings as $meeting) {
                $client = users::find($meeting->customer_id);
                $affilie = affiliate_user::byId($meeting->affiliate_id);
                $affilie_profile = affiliate_portfolio_profile::find($meeting->affiliate_id);
                $i++;
                $datas[]=array(DateParser::DateShort($meeting->date.' '.$meeting->time,1),$client->username,$affilie->username,
                    $affilie_profile->category,$meeting->mode_of_meeting,
                    $meeting->status,DateParser::DateShort($meeting->created_date,1)
                );
            }
            $name = 'Excel_Liste_Des_Rendez_vous_'.date('Y-m-d_i:s').'_'.time();
            $xls = new \Excel_XML();
            $xls->addWorksheet('Rendez-vous ', $datas);
            $xls->sendWorkbook($name.'.xls');
        }catch (Exception $e){}
    }

    public function withdrawals(){
        Privilege::hasPrivilege(Privilege::$eshopDemandeRetraitEtat,$this->user->privilege);
        $etat = (isset($_GET['etat'])&&is_numeric($_GET['etat'])) ? $_GET['etat']-1 : null;
        $debut = (isset($_GET['debut'])&&!empty($_GET['debut'])) ? date(MYSQL_DATE_FORMAT, strtotime($_GET['debut'])) : null;
        $end = (isset($_GET['end'])&&!empty($_GET['end'])) ? date(MYSQL_DATE_FORMAT, strtotime($_GET['end'])) : null;
        $withdrawals = withdraw_request::searchType(null,null,null,$etat,$debut,$end);
        try{
            $mpdf = new Mpdf(['mode' => 'utf-8', 'format' => 'A4-L','margin_left' => 5,'margin_right' => 5,'margin_top' => 15,'margin_bottom' => 20,'margin_header' => 15,'margin_footer' => 10]);
            $mpdf->SetProtection(array('print'));
            $mpdf->SetTitle('Liste des Demandes de Retrait ');
            $mpdf->SetAuthor("Baba & Ousmanou");
            $mpdf->SetWatermarkText('Demandes de Retrait | PLUMERS');
            $mpdf->showWatermarkText = true;
            $mpdf->watermarkTextAlpha = 0.1;
            $mpdf->SetDisplayMode('fullpage');
            $mpdf->useAdobeCJK = true;
            $mpdf->autoScriptToLang = true;
            $mpdf->autoLangToFont = true;
            ob_start();

            $transac = ['withdrawals '=>$withdrawals ];
            $header = '';

            if(!is_null($etat)){
                $header .= '<tr><th style="border: 0;text-align: right">Etat :</th><td style="border: 0;" class="meta-head">'.StringHelper::$tabEtatWithdraw[$etat].'</td></tr>';
            }
            if(!is_null($debut)){
                $header .= '<tr><th style="border: 0;text-align: right">De :</th><td style="border: 0;" class="meta-head">'.DateParser::DateShort($debut).'</td></tr>';
            }
            if(!is_null($end)){
                $header .= '<tr><th style="border: 0;text-align: right">A :</th><td style="border: 0;" class="meta-head">'.DateParser::DateShort($end).'</td></tr>';
            }
            $head = ['headerTab'=>$header];
            $variables = array_merge($transac,$head);
            extract($variables);
            require 'Views/Prints/withdrawals.php';
            $html = ob_get_contents();
            ob_end_clean();
            $mpdf->WriteHTML($html);
            $mpdf->Output('Pdf_Liste_Des_Demandes_de_retrait'.date('Y-m-d_i:s').'_'.time().'.pdf', 'I'); # D
        }catch (MpdfException $e){}
    }

    public function withdrawalsExcell(){
        Privilege::hasPrivilege(Privilege::$eshopDemandeRetraitEtat,$this->user->privilege);
        require 'Excel_XML.php';
        $etat = (isset($_GET['etat'])&&is_numeric($_GET['etat'])) ? $_GET['etat']-1 : null;
        $debut = (isset($_GET['debut'])&&!empty($_GET['debut'])) ? date(MYSQL_DATE_FORMAT, strtotime($_GET['debut'])) : null;
        $end = (isset($_GET['end'])&&!empty($_GET['end'])) ? date(MYSQL_DATE_FORMAT, strtotime($_GET['end'])) : null;
        $withdrawals = withdraw_request::searchType(null,null,null,$etat,$debut,$end);
        $datas = [];
        $i = 0;
        try{
            $datas[0] = array( 'Date','Affilié','Montant','Etat');
            foreach ($withdrawals as $item) {
                $client = users::find($item->user_id);
                $i++;
                $datas[]=array(DateParser::DateShort($item->date),$client->username,float_value($item->amount),$item->status);
            }
            $name = 'Excel_Liste_Des_Demandes_de_retrait'.date('Y-m-d_i:s').'_'.time();
            $xls = new \Excel_XML();
            $xls->addWorksheet('Demandes de Retrait', $datas);
            $xls->sendWorkbook($name.'.xls');
        }catch (Exception $e){}
    }

    public function clients(){
        Privilege::hasPrivilege(Privilege::$eshopUserClientEtat,$this->user->privilege);
        $s_search = (isset($_GET['search'])&&!empty($_GET['search'])) ? $_GET['search'] : null;
        $s_role = (isset($_GET['role'])&&!empty($_GET['role'])) ? $_GET['role'] : null;
        $s_etat = (isset($_GET['etat'])&&!empty($_GET['etat'])) ? $_GET['etat']-1 : null;
        $s_sexe = (isset($_GET['sexe'])&&!empty($_GET['sexe'])) ? $_GET['sexe'] : null;
        $s_debut = (isset($_GET['debut'])&&!empty($_GET['debut'])) ? date(MYSQL_DATE_FORMAT, strtotime($_GET['debut'])) : null;
        $s_end = (isset($_GET['end'])&&!empty($_GET['end'])) ? date(MYSQL_DATE_FORMAT, strtotime($_GET['end'])) : null;
        $clients = users::searchType(null,null,$s_search,$s_sexe,$s_etat,$s_role,$s_debut,$s_end);
        try{
            $mpdf = new Mpdf(['mode' => 'utf-8', 'format' => 'A4-L','margin_left' => 5,'margin_right' => 5,'margin_top' => 15,'margin_bottom' => 20,'margin_header' => 15,'margin_footer' => 10]);
            $mpdf->SetProtection(array('print'));
            $mpdf->SetTitle('Liste des Clients');
            $mpdf->SetAuthor("Baba & Ousmanou");
            $mpdf->SetWatermarkText('Clients | PLUMERS');
            $mpdf->showWatermarkText = true;
            $mpdf->watermarkTextAlpha = 0.1;
            $mpdf->SetDisplayMode('fullpage');
            $mpdf->useAdobeCJK = true;
            $mpdf->autoScriptToLang = true;
            $mpdf->autoLangToFont = true;
            ob_start();
            $transac = ['clients '=>$clients ];
            $header = '';
            if(!is_null($s_search)){
                $header .= '<tr><th style="border: 0;text-align: right">Nom :</th><td style="border: 0;" class="meta-head">'.$s_search.'</td></tr>';
            }
            if(!is_null($s_etat)){
                $header .= '<tr><th style="border: 0;text-align: right">Etat :</th><td style="border: 0;" class="meta-head">'.StringHelper::$tabState[$s_etat].'</td></tr>';
            }
            if(!is_null($s_role)){
                $header .= '<tr><th style="border: 0;text-align: right">Role :</th><td style="border: 0;" class="meta-head">'.$s_role.'</td></tr>';
            }
            if(!is_null($s_sexe)){
                $header .= '<tr><th style="border: 0;text-align: right">Genre :</th><td style="border: 0;" class="meta-head">'.$s_sexe.'</td></tr>';
            }
            if(!is_null($s_debut)){
                $header .= '<tr><th style="border: 0;text-align: right">De :</th><td style="border: 0;" class="meta-head">'.DateParser::DateShort($s_debut).'</td></tr>';
            }
            if(!is_null($s_end)){
                $header .= '<tr><th style="border: 0;text-align: right">A :</th><td style="border: 0;" class="meta-head">'.DateParser::DateShort($s_end).'</td></tr>';
            }

            $head = ['headerTab'=>$header];
            $variables = array_merge($transac,$head);
            extract($variables);
            require 'Views/Prints/clients.php';
            $html = ob_get_contents();
            ob_end_clean();
            $mpdf->WriteHTML($html);
            $mpdf->Output('Pdf_Liste_Des_Clients'.date('Y-m-d_i:s').'_'.time().'.pdf', 'I'); # D
        }catch (MpdfException $e){}
    }

    public function clientsExcell(){
        require 'Excel_XML.php';
        $s_search = (isset($_GET['search'])&&!empty($_GET['search'])) ? $_GET['search'] : null;
        $s_role = (isset($_GET['role'])&&!empty($_GET['role'])) ? $_GET['role'] : null;
        $s_etat = (isset($_GET['etat'])&&!empty($_GET['etat'])) ? $_GET['etat']-1 : null;
        $s_sexe = (isset($_GET['sexe'])&&!empty($_GET['sexe'])) ? $_GET['sexe'] : null;
        $s_debut = (isset($_GET['debut'])&&!empty($_GET['debut'])) ? date(MYSQL_DATE_FORMAT, strtotime($_GET['debut'])) : null;
        $s_end = (isset($_GET['end'])&&!empty($_GET['end'])) ? date(MYSQL_DATE_FORMAT, strtotime($_GET['end'])) : null;
        $clients = users::searchType(null,null,$s_search,$s_sexe,$s_etat,$s_role,$s_debut,$s_end);
        $datas = [];
        $i = 0;
        try{
            $datas[0] = array( 'Photo','Noms','Téléphone','Email','Role','Etat','Ajouté le');

            foreach ($clients as $profil) {
                $i++;
                $datas[]=array(FileHelper::url($profil->profileimg),$profil->username,
                    $profil->mobile,$profil->email, $profil->role,$profil->status,
                    DateParser::DateShort($profil->created_on,1));
            }
            $name = 'Excel_Liste_Des_Clients'.date('Y-m-d_i:s').'_'.time();
            $xls = new \Excel_XML();
            $xls->addWorksheet('Clients', $datas);
            $xls->sendWorkbook($name.'.xls');
        }catch (Exception $e){}
    }

    public function produits_commandes(){
        Privilege::hasPrivilege(Privilege::$eshopProductSaleEtat,$this->user->privilege);
        if(isset($_GET['id'])&&!empty($_GET['id'])){
            $id = $_GET['id'];
            $article = products::find($id);
            if($article){
                $etat = (isset($_GET['etat'])&&!empty($_GET['etat'])) ? $_GET['etat'] : null;
                $debut = (isset($_GET['debut'])&&!empty($_GET['debut'])) ? date(MYSQL_DATE_FORMAT, strtotime($_GET['debut'])) : null;
                $end = (isset($_GET['end'])&&!empty($_GET['end'])) ? date(MYSQL_DATE_FORMAT, strtotime($_GET['end'])) : null;
                $lignes = checkout_orders::searchType(null,null,$id,null,$etat,$debut,$end);
                try{
                    $mpdf = new Mpdf(['mode' => 'utf-8', 'format' => 'A4-L','margin_left' => 5,'margin_right' => 5,'margin_top' => 15,'margin_bottom' => 20,'margin_header' => 15,'margin_footer' => 10]);
                    $mpdf->SetProtection(array('print'));
                    $mpdf->SetTitle('Commandes de $article->productname');
                    $mpdf->SetAuthor("Baba & Ousmanou");
                    $mpdf->SetWatermarkText("$article->productname");
                    $mpdf->showWatermarkText = true;
                    $mpdf->watermarkTextAlpha = 0.1;
                    $mpdf->SetDisplayMode('fullpage');
                    $mpdf->useAdobeCJK = true;
                    $mpdf->autoScriptToLang = true;
                    $mpdf->autoLangToFont = true;
                    ob_start();
                    $transac = ['lignes'=>$lignes];
                    $art = ['article'=>$article];
                    $header = '';
                    if(!is_null($etat)){
                        $header .= '<tr><th style="border: 0;text-align: right">Etat :</th><td style="border: 0;" class="meta-head">'.$etat.'</td></tr>';
                    }
                    if(!is_null($debut)){
                        $header .= '<tr><th style="border: 0;text-align: right">De :</th><td style="border: 0;" class="meta-head">'.DateParser::DateShort($debut).'</td></tr>';
                    }
                    if(!is_null($end)){
                        $header .= '<tr><th style="border: 0;text-align: right">A :</th><td style="border: 0;" class="meta-head">'.DateParser::DateShort($end).'</td></tr>';
                    }
                    $head = ['headerTab'=>$header];
                    $variables = array_merge($transac,$head,$art);
                    extract($variables);
                    require 'Views/Prints/lignes.php';
                    $html = ob_get_contents();
                    ob_end_clean();
                    $mpdf->WriteHTML($html);
                    $lenom = $this->collerleNom($article->productname);
                    $mpdf->Output('Pdf_Liste_Des_Commandes_Produit_'.$lenom.'_'.date('Y-m-d_i:s').'_'.time().'.pdf', 'I'); # D
                }catch (MpdfException $e){}
            }else{
                App::error();
            }
        }else{
            App::error();
        }
    }

    public function produits_commandesExcell(){
        require 'Excel_XML.php';
        if(isset($_GET['id'])&&!empty($_GET['id'])){
            $id = $_GET['id'];
            $article = products::find($id);
            if($article){
                $datas = [];
                $etat = (isset($_GET['etat'])&&!empty($_GET['etat'])) ? $_GET['etat'] : null;
                $debut = (isset($_GET['debut'])&&!empty($_GET['debut'])) ? date(MYSQL_DATE_FORMAT, strtotime($_GET['debut'])) : null;
                $end = (isset($_GET['end'])&&!empty($_GET['end'])) ? date(MYSQL_DATE_FORMAT, strtotime($_GET['end'])) : null;
                $lignes = checkout_orders::searchType(null,null,$id,null,$etat,$debut,$end);
                $i = 0;
                try{
                    $datas[0] = array('Order Id','Client','Adresse','Prix produit','Quantité','Prix payé','Status','Date');
                    foreach ($lignes as $ligne) {
                        $i++;
                        $datas[]=array($ligne->order_id,$ligne->fName.' '.$ligne->lName,$ligne->phoneNumber.', '.$ligne->provice.', '.$ligne->city,
                            $ligne->product_total_price,$ligne->qty,$ligne->total_paid_price,$ligne->status,DateParser::DateShorts($ligne->date,1));
                    }
                    $lenom = $this->collerleNom($article->productname);
                    $name = 'Excel_Liste_Des_Commandes_Produit_'.$lenom.'_'.date('Y-m-d_i:s').'_'.time();
                    $xls = new \Excel_XML();
                    $xls->addWorksheet($article->productname, $datas);
                    $xls->sendWorkbook($name.'.xls');
                }catch (Exception $e){}
            }else{
                App::error();
            }
        }else{
            App::error();
        }
    }

    function collerleNom($nom){
        return str_replace(' ','_',$nom);
    }

}