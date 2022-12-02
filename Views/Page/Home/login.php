<?php

use Projet\Model\App;
use Projet\Model\FileHelper;

$auth = App::getDBAuth();
App::setTitle("Se connecter à l'administration");
App::addScript("assets/js/login.js",true);
?>
<div class="page-inner" style="background: transparent !important">
    <div id="main-wrapper" style="margin-top: 0">
        <div class="row">
            <div class="col-md-3 center divBox" style="background: #fff;padding: 20px;margin-top: 40px">
                <div class="login-box">
                    <a href="<?= App::url(''); ?>" class="logo-name text-center">
                        <img src="<?= FileHelper::url('assets/img/logo5.png') ?>" style="height: 150px"  alt="">
                    </a>
<!--                    <p class="text-center no-m" style="color: #E37A14;">Brancos BACKEND WEB APP</p>-->
                    <form class="m-t-md text-center" action="<?= App::url('ajax/log') ?>" id="loginForm">
                        <div class="form-group">
                            <input type="text" class="form-control" id="login" placeholder="Email ou Téléphone" required>
                        </div>
                        <div class="form-group">
                            <input type="password" class="form-control" id="password" placeholder="Mot de passe" required>
                        </div>
                        <button type="submit" style="background: #E37A14;" class="sendBtn btn btn-success btn-lg btn-rounded">Connexion</button>
                    </form>
                    <p class="text-center text-sm" style="margin-top: 20px">2020 &copy; Brancos par <a href="mailto:ousmanou@uisolution.net">heriol
                  </a></p>
                </div>
            </div>
        </div>
    </div>
</div>
