<?php
/**
 * Created by PhpStorm.
 * User: DIKLA
 * Date: 19/07/2020
 * Time: 15:43
 */
return
    [
        ""=>'\Projet\Controller\Page\AuthController#login',
        "ajax/log"=>'\Projet\Controller\Page\HomeController#log',
        "error"=>'\Projet\Controller\Error\HomeController#error',
        "error_db"=>'\Projet\Controller\Error\HomeController#error_db',
        "unauthorize"=>'\Projet\Controller\Error\HomeController#unauthorize',
        "expired"=>'\Projet\Controller\Error\HomeController#expired',
        "logout"=>'\Projet\Controller\Page\HomeController#logout',

        "users/pdf"=>'\Projet\Controller\Admin\StatsController#users',
        "users/excell"=>'\Projet\Controller\Admin\StatsController#usersExcell',

        "clients/pdf"=>'\Projet\Controller\Admin\StatsController#clients',
        "clients/excell"=>'\Projet\Controller\Admin\StatsController#clientsExcell',


        "produits/pdf"=>'\Projet\Controller\Admin\StatsController#produits',
        "produits/excell"=>'\Projet\Controller\Admin\StatsController#produitsExcell',

        "produits/commandes/pdf"=>'\Projet\Controller\Admin\StatsController#produits_commandes',
        "produits/commandes/excell"=>'\Projet\Controller\Admin\StatsController#produits_commandesExcell',

        "commandes/pdf"=>'\Projet\Controller\Admin\StatsController#commandes',
        "commandes/excell"=>'\Projet\Controller\Admin\StatsController#commandesExcell',


        "home"=>'\Projet\Controller\Admin\HomeController#index',
        "withdrawals"=>'\Projet\Controller\Admin\HomeController#withdrawals',
        "home/charts"=>'\Projet\Controller\Admin\HomeController#charts',
        "home/loader"=>'\Projet\Controller\Admin\HomeController#load',
        "transactions"=>'\Projet\Controller\Admin\HomeController#transactions',
        "transactions/active"=>'\Projet\Controller\Admin\HomeController#delete',
        "loader"=>'\Projet\Controller\Admin\HomeController#loader',
        "visites"=>'\Projet\Controller\Admin\HomeController#visites',
        "suggestions"=>'\Projet\Controller\Admin\HomeController#suggestions',
        "suggestions/detail"=>'\Projet\Controller\Admin\HomeController#detailSuggestion',
        "password"=>'\Projet\Controller\Admin\HomeController#password',
        "password/change"=>'\Projet\Controller\Admin\HomeController#changePassword',

        "commandes"=>'\Projet\Controller\Admin\CommandeController#index',
        "commandes/detail"=>'\Projet\Controller\Admin\CommandeController#detail',
        "commandes/setEtat"=>'\Projet\Controller\Admin\CommandeController#setEtat',
        "commandes/setEtats"=>'\Projet\Controller\Admin\CommandeController#setEtats',
        "commandes/setState "=>'\Projet\Controller\Admin\CommandeController#setState',
        "commandes/setLivraison"=>'\Projet\Controller\Admin\CommandeController#setLivraison',
        "commandes/disponible"=>'\Projet\Controller\Admin\CommandeController#disponible',
        "commandes/livraison/made"=>'\Projet\Controller\Admin\CommandeController#livraison',
        "commandes/setPayer"=>'\Projet\Controller\Admin\CommandeController#setPayer',
        "commandes/setReceive"=>'\Projet\Controller\Admin\CommandeController#setReceive',
        "commandes/setRembourser"=>'\Projet\Controller\Admin\CommandeController#setRembourser',
        "commandes/details"=>'\Projet\Controller\Admin\CommandeController#details',
        "commandes/lignes/valid"=>'\Projet\Controller\Admin\CommandeController#valid',
        "commandes/facture"=>'\Projet\Controller\Admin\CommandeController#facture',
        "commandes/bon"=>'\Projet\Controller\Admin\CommandeController#bon',
        "commandes/etat/change"=>'\Projet\Controller\Admin\CommandeController#change',
        "commandes/dateDelivery"=>'\Projet\Controller\Admin\CommandeController#dateDelivery',


        "admins"=>'\Projet\Controller\Admin\AdminsController#index',
        "admins/save"=>'\Projet\Controller\Admin\AdminsController#save',
        "admins/activate"=>'\Projet\Controller\Admin\AdminsController#delete',
        "admins/reset"=>'\Projet\Controller\Admin\AdminsController#reset',
        "admins/setPhoto"=>'\Projet\Controller\Admin\AdminsController#setPhoto',

        "coupons"=>'\Projet\Controller\Admin\CarteController#coupons',
        "coupons/add"=>'\Projet\Controller\Admin\CarteController#saveCoupon',
        "coupons/delete"=>'\Projet\Controller\Admin\CarteController#deleteCoupon',
        "coupons/detail"=>'\Projet\Controller\Admin\CarteController#detailCoupon',
        "users/coupons/add"=>'\Projet\Controller\Admin\CarteController#addCoupon',
        "users/coupons"=>'\Projet\Controller\Admin\CarteController#couponsCustomer',
        "users/setSolde" => '\Projet\Controller\Admin\ProfilController#setSolde',
        "users/solde/diminuer" => '\Projet\Controller\Admin\ProfilController#delSolde',
        "users/comptes/historique" => '\Projet\Controller\Admin\ProfilController#historiques',

        "profiles" => '\Projet\Controller\Admin\ProfileController#index',
        "profiles/delete" => '\Projet\Controller\Admin\ProfileController#delete',
        "profiles/save" => '\Projet\Controller\Admin\ProfileController#save',

        "article" => '\Projet\Controller\Admin\NewsController#index',
        "article/delete" => '\Projet\Controller\Admin\NewsController#delete',
        "article/save" => '\Projet\Controller\Admin\NewsController#save',
        "article/change" => '\Projet\Controller\Admin\NewsController#change',

        "users"=>'\Projet\Controller\Admin\ProfilController#index',
        "users/commandes"=>'\Projet\Controller\Admin\ProfilController#commandes',
        "users/activate"=>'\Projet\Controller\Admin\ProfilController#delete',
        "users/reset"=>'\Projet\Controller\Admin\ProfilController#reset',
        "users/setPhoto"=>'\Projet\Controller\Admin\ProfilController#setPhoto',
        "users/detail"=>'\Projet\Controller\Admin\ProfilController#detail',
        "users/projects"=>'\Projet\Controller\Admin\ProfilController#projects',
        "users/projects/detail"=>'\Projet\Controller\Admin\ProfilController#detailProject',
        "users/projects/details"=>'\Projet\Controller\Admin\ProfilController#detailsProject',
        

        "produits" => '\Projet\Controller\Admin\ArticleController#index',
        "produits/detail" => '\Projet\Controller\Admin\ArticleController#detail',
        "produits/setStock" => '\Projet\Controller\Admin\ArticleController#setStock',
        "produits/stock/diminuer" => '\Projet\Controller\Admin\ArticleController#delStock',
        "produits/setEtat" => '\Projet\Controller\Admin\ArticleController#setEtat',
        "produits/save" => '\Projet\Controller\Admin\ArticleController#save',
        "produits/setImage" => '\Projet\Controller\Admin\ArticleController#setImage',
        "produits/setPhoto" => '\Projet\Controller\Admin\ArticleController#setPhoto',
        "produits/images/delete" => '\Projet\Controller\Admin\ArticleController#deleteImage',
        "produits/commandes" => '\Projet\Controller\Admin\ArticleController#commandes',
        "produits/reviews" => '\Projet\Controller\Admin\ArticleController#reviews',
        "produits/stock/historique" => '\Projet\Controller\Admin\ArticleController#historique',

        "deals" => '\Projet\Controller\Admin\DealController#index',
        "deals/detail" => '\Projet\Controller\Admin\DealController#detail',
        "deals/setEtat" => '\Projet\Controller\Admin\DealController#setEtat',

        "cat" => '\Projet\Controller\Admin\CatController#index',
        "cat/delete" => '\Projet\Controller\Admin\CatController#delete',
        "cat/detail" => '\Projet\Controller\Admin\CatController#detail',
        "cat/save" => '\Projet\Controller\Admin\CatController#save',
        "cat/setImage" => '\Projet\Controller\Admin\CatController#setImage',


        "categories" => '\Projet\Controller\Admin\CategorieController#index',
        "categories/delete" => '\Projet\Controller\Admin\CategorieController#delete',
        "categories/detail" => '\Projet\Controller\Admin\CategorieController#detail',
        "categories/save" => '\Projet\Controller\Admin\CategorieController#save',
        "categories/setImage" => '\Projet\Controller\Admin\CategorieController#setImage',

        "boutique"=>'\Projet\Controller\Admin\BoutiqueController#index',
        "boutique/save"=>'\Projet\Controller\Admin\BoutiqueController#save',
        "boutique/setCni"=>'\Projet\Controller\Admin\BoutiqueController#setCni',
        "boutique/setPhoto"=>'\Projet\Controller\Admin\BoutiqueController#setPhoto',
        "boutique/detail"=>'\Projet\Controller\Admin\BoutiqueController#detail',
        "boutique/resetPass"=>'\Projet\Controller\Admin\BoutiqueController#resetPass',
        "boutique/activate"=>'\Projet\Controller\Admin\BoutiqueController#delete',
        "boutique/classes"=>'\Projet\Controller\Admin\BoutiqueController#classes',
        "boutique/classes/add"=>'\Projet\Controller\Admin\BoutiqueController#addClasse',
        "boutique/classes/eleves"=>'\Projet\Controller\Admin\BoutiqueController#eleves',
        "boutique/classe/cours"=>'\Projet\Controller\Admin\BoutiqueController#cours',

        "homes"=>'\Projet\Controller\Boutique\BoutiqueController#index',
    ];