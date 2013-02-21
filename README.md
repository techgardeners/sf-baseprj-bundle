sfBaseprjBundle
===============

Base bundle for project based on Symfony 2


Install

Add to your composer.json

    "require": {
        "jms/serializer": ">=0.11.0",
        "egeloen/google-map-bundle": "1.0.0",
        "friendsofsymfony/user-bundle": "*",
        "avalanche123/imagine-bundle": "v2.1",
        "mobiledetect/mobiledetectlib": "dev-master", 
        "suncat/mobile-detect-bundle": "dev-master",
        "techgardeners/sf-baseprj-bundle": "dev-master"   
        
    },
    
add in appKernel:

            // Base bundle for project based on techgardeners/sfbaseprj
            new FOS\UserBundle\FOSUserBundle(), // https://github.com/FriendsOfSymfony/FOSUserBundle
            new Doctrine\Bundle\MigrationsBundle\DoctrineMigrationsBundle(), // https://github.com/doctrine/DoctrineMigrationsBundle
            new Avalanche\Bundle\ImagineBundle\AvalancheImagineBundle(), // https://github.com/avalanche123/AvalancheImagineBundle           
            new SunCat\MobileDetectBundle\MobileDetectBundle(),  // http://knpbundles.com/egeloen/IvoryGoogleMapBundle
            new Ivory\GoogleMapBundle\IvoryGoogleMapBundle(),   // https://github.com/egeloen/IvoryGoogleMapBundle         
            new TechG\Bundle\SfBaseprjBundle\TechGSfBaseprjBundle(),  

By importing the routing files you will have ready made pages for things such as
logging in, creating users, etc.

In YAML:

``` yaml
# app/config/routing.yml
tech_g_baseprj_bundle:
    resource: "@TechGSfBaseprjBundle/Resources/config/routing.yml"
    prefix:   /
```
                   
add in config.yml 

``` yaml
# app/config/config.yml

mobile_detect:
    redirect:
        mobile: ~
        tablet: ~
    switch_device_view: ~
    
#mobile_detect:
#    redirect:
#        mobile:
#            is_enabled: true            # default false
#            host: http://m.site.com     # with scheme (http|https), default null, url validate
#            status_code: 301            # default 302
#            action: redirect            # redirect, no_redirect, redirect_without_path 
#        tablet:
#            is_enabled: true            # default false
#            host: http://t.site.com     # with scheme (http|https), default null, url validate
#            status_code: 301            # default 302
#            action: redirect            # redirect, no_redirect, redirect_without_path 
#    switch_device_view:
#        save_referer_path: false        # default true
                                        # true  redirectUrl = http://site.com/current/path
                                        # false redirectUrl = http://site.com  
                                        
tech_g_sf_baseprj:
    debug:
        enable: true
    log:
        enable: true
        level: 1000
        queue: true
        savesession: true
        saverequest: true
        keepalive: true
        skippattern: /^\/gPanel/
    guesslocale:
        enable: true
        savesession: true        
        onlyfirstrequest: true        
    mobiledetect:
        enable: true
    geodecode:
        enable: true
        savesession: true        
    whitelist:
        enable: false
    blacklist:
        enable: false                                        
                                        
                                        
```
