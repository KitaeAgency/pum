Installing phpunit using pear
    Run cmd as administrator
    Better reinstall pear
        Get the last go-pear.phar : http://pear.php.net/go-pear.phar and install
    Make sure the pear configuration PHP settings are correct : pear config-show
        When changing a config setting, it does not immediately get applied to already installed packages. This is especially true when changing variables like data_dir. 
        Use $ pear upgrade --force to reinstall all packages in such cases.
    Run the following commands (they may take a while to update, be patient):
        pear channel-update pear.php.net
        pear upgrade-all
        pear channel-discover pear.phpunit.de
        pear channel-discover components.ez.no
        pear channel-discover pear.symfony-project.com
        pear update-channels
        pear clear-cache

    To install PHPUnit, run
        pear install --alldeps --force phpunit/PHPUnit
            Issues : Unknown remote channel: pear.symfony.com
                Solved creating an alias:
                pear channel-alias pear.symfony-project.com pear.symfony.com

    To test that PHPUnit was successfully installed, run
        phpunit -V