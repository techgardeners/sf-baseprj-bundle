sfBaseprjBundle
===============

Base bundle for project based on Symfony 2


Install

Add to your composer.json

    "require": {
        "mobiledetect/mobiledetectlib": "dev-master", 
        "suncat/mobile-detect-bundle": "dev-master",
        "techgardeners/sf-baseprj-bundle": "dev-master"      
        
    },
    
add in appKernel:

            // Base bundle for project based on techgardeners/sfbaseprj
            new TechG\Bundle\SfBaseprjBundle\TechGSfBaseprjBundle(),
            new SunCat\MobileDetectBundle\MobileDetectBundle(),  // https://github.com/suncat2000/MobileDetectBundle      
    
add in config.yml 

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