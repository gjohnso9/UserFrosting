<?php
    require_once "../userfrosting/config-userfrosting.php";

    use UserFrosting as UF;
   
    // Front page
    $app->get('/', function () use ($app) {
        // Forward to installation if not complete
        if (!isset($app->site->install_status) || $app->site->install_status == "pending"){
            $app->redirect($app->urlFor('uri_install'));
        }        
        // Forward to the user's landing page (if logged in), otherwise take them to the home page
        if ($app->user->isGuest()){
            $controller = new UF\AccountController($app);
            $controller->pageHome();
        // If this is the first the root user is logging in, take them to site settings
        } else if ($app->user->id == $app->config('user_id_master') && $app->site->install_status == "new"){
            $app->site->install_status = "complete";
            $app->site->store();
            $app->alerts->addMessage("success", "Congratulations, you've successfully logged in for the first time.  Please take a moment to customize your site settings.");
            $app->redirect($app->urlFor('uri_settings'));  
        } else {
            $app->redirect($app->user->landing_page);        
        }
    })->name('uri_home');

    // Miscellaneous pages
    $app->get('/dashboard/?', function () use ($app) {    
        // Access-controlled page
        if (!$app->user->checkAccess('uri_dashboard')){
            $app->notFound();
        }
        
        $page_schema = UF\PageSchema::load("user", $app->config('schema.path') . "/pages/pages.json");
        
        $app->render('dashboard.html', [
            'page' => [
                'author' =>         $app->site->author,
                'title' =>          "Dashboard",
                'description' =>    "Your user dashboard.",
                'alerts' =>         $app->alerts->getAndClearMessages(), 
                'schema' =>         $page_schema
            ]
        ]);          
    });
    
    $app->get('/zerg/?', function () use ($app) {    
        // Access-controlled page
        if (!$app->user->checkAccess('uri_zerg')){
            $app->notFound();
        }
        
        $page_schema = UF\PageSchema::load("starcraft", $app->config('schema.path') . "/pages/pages.json");
        
        $app->render('zerg.html', [
            'page' => [
                'author' =>         $app->site->author,
                'title' =>          "Zerg",
                'description' =>    "Dedicated to the pursuit of genetic perfection, the zerg relentlessly hunt down and assimilate advanced species across the galaxy, incorporating useful genetic code into their own.",
                'alerts' =>         $app->alerts->getAndClearMessages(), 
                'schema' =>         $page_schema
            ]
        ]); 
    });    
       
    // Account management pages
    $app->get('/account/:action/?', function ($action) use ($app) {    
        // Forward to installation if not complete
        if (!isset($app->site->install_status) || $app->site->install_status == "pending"){
            $app->redirect($app->urlFor('uri_install'));
        }
        
        $controller = new UF\AccountController($app);
    
        switch ($action) {
            case "login":               return $controller->pageLogin();
            case "logout":              return $controller->logout(); 
            case "register":            return $controller->pageRegister();
            case "resend-activation":   return $controller->pageResendActivation();
            case "forgot-password":     return $controller->pageForgotPassword($app->request()->get('token'));
            case "captcha":             return $controller->captcha();
            case "settings":            return $controller->pageAccountSettings();
            default:                    return $controller->page404();   
        }
    });

    $app->post('/account/:action/?', function ($action) use ($app) {            
        $controller = new UF\AccountController($app);
    
        switch ($action) {
            case "login":               return $controller->login();     
            case "register":            return $controller->register();
            case "resend-activation":   return $controller->resendActivation();
            case "forgot-password":     return $controller->forgotPassword($app->request()->get('token'));
            case "settings":            return $controller->accountSettings();
            default:                    $app->notFound();
        }
    });    
    
    // User management pages
    $app->get('/users/?', function () use ($app) {
        $controller = new UF\UserController($app);
        return $controller->pageUsers();
    });    

    // User info page
    $app->get('/users/u/:user_id/?', function ($user_id) use ($app) {
        $controller = new UF\UserController($app);
        return $controller->pageUser($user_id);
    });       

    // Update user info
    $app->post('/users/u/:user_id/?', function ($user_id) use ($app) {
        $controller = new UF\UserController($app);
        return $controller->updateUser($user_id);
    });       
    
        
    // Admin tools
    $app->get('/config/settings/?', function () use ($app) {
        $controller = new UF\AdminController($app);
        return $controller->pageSiteSettings();
    })->name('uri_settings');   
    
    $app->post('/config/settings/?', function () use ($app) {
        $controller = new UF\AdminController($app);
        return $controller->siteSettings();        
    });
    
    // Installation pages
    $app->get('/install/?', function () use ($app) {
        $controller = new UF\InstallController($app);
        if (isset($app->site->install_status)){
            // If tables have been created, move on to master account setup
            if ($app->site->install_status == "pending"){
                $app->redirect($app->site->uri['public'] . "/install/master");
            } else {
                // Everything is set up, so go to the home page!
                $app->redirect($app->urlFor('uri_home'));
            }
        } else {
            return $controller->pageSetupDB();
        }
    })->name('uri_install');
    
    $app->get('/install/master/?', function () use ($app) {
        $controller = new UF\InstallController($app);
        if (isset($app->site->install_status) && ($app->site->install_status == "pending")){
            return $controller->pageSetupMasterAccount();
        } else {
            $app->redirect($app->urlFor('uri_install'));
        }
    });

    $app->post('/install/:action/?', function ($action) use ($app) {
        $controller = new UF\InstallController($app);
        switch ($action) {
            case "master":            return $controller->setupMasterAccount();      
            default:                  $app->notFound();
        }   
    });
    
    // Slim info page
    $app->get('/sliminfo/?', function () use ($app) {
        // Access-controlled page
        if (!$app->user->checkAccess('uri_slim_info')){
            $app->notFound();
        }
        echo "<pre>";
        print_r($app->environment());
        echo "</pre>";
    });

    // PHP info page
    $app->get('/phpinfo/?', function () use ($app) {
        // Access-controlled page
        if (!$app->user->checkAccess('uri_php_info')){
            $app->notFound();
        }    
        echo "<pre>";
        print_r(phpinfo());
        echo "</pre>";
    });

    // PHP info page
    $app->get('/errorlog/?', function () use ($app) {
        // Access-controlled page
        if (!$app->user->checkAccess('uri_error_log')){
            $app->notFound();
        }
        $log = $app->site->getLog();
        echo "<pre>";
        echo implode("<br>",$log['messages']);
        echo "</pre>";
    });      
    
    // Alert stream
    $app->get('/alerts/?', function () use ($app) {
        $controller = new UF\BaseController($app);
        $controller->alerts();
    });
    
    // JS Config
    $app->get('/js/config.js', function () use ($app) {
        $controller = new UF\BaseController($app);
        $controller->configJS();
    });
    
    // Theme CSS
    $app->get('/css/theme.css', function () use ($app) {
        $controller = new UF\BaseController($app);
        $controller->themeCSS();
    });
    
    // Not found page (404)
    $app->notFound(function () use ($app) {
        if ($app->request->isGet()) {
            $controller = new UF\BaseController($app);
            $controller->page404();
        } else {
            $app->alerts->addMessageTranslated("danger", "SERVER_ERROR");
        }
    });     
    
    $app->get('/test/auth', function() use ($app){
        if (0 == "php")
            echo "0 = php";
        
        $params = [
            "user" => [
                "id" => 1
            ],
            "post" => [
                "id" => 7
            ]
        ];
        
        $conditions = "(equals(self.id,user.id)||hasPost(self.id,post.id))&&subset(post, [\"id\", \"title\", \"content\", \"subject\", 3])";
        
        $ace = new UF\AccessConditionExpression($app);
        $result = $ace->evaluateCondition($conditions, $params);
        
        if ($result){
            echo "Passed $conditions";
        } else {
            echo "Failed $conditions";
        }
    });
    
    $app->run();
?>
