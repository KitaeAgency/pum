Continuous Integration
======================

**Current server**: ``dev.testing.argosit.net``

**Current URL**: http://ci.dev.testing.argosit.net/

Installed Debian packages
-------------------------

.. code-block:: text

    apt-get install \
        git subversion \
        php5-common php5-cli php5-intl php5-mcrypt php5-mysql php5-sqlite php5-curl php5-imagick php5-xsl \
        mysql-server \
        apache2 libapache2-mod-php5 \
        sun-java6-bin \
        xvfb

        # Firefox
        # Chrome
        # Apache
        # Selenium server (+ ChromeDriver)

Debian is also installed as a debian package, as explained on `this page <http://pkg.jenkins-ci.org/debian/>`_.

Apache configuration
--------------------

Default *DocumentRoot* is ``/var/www/home``, containing an empty file ``index.html``. Aim is to make
``/var/www`` not accessible.

Next, we'll setup a dedicated VirtualHost for Jenkins:

.. code-block:: bash

    sudo a2enmod proxy
    sudo a2enmod proxy_http
    sudo service apache2 restart


Apache VirtualHost configuration (``/etc/apache2/sites-available/ci``):

.. code-block:: text

    <VirtualHost *:80>
        ServerName ci.dev.testing.argosit.net
        ProxyRequests Off
        <Proxy *>
            Order deny,allow
            Allow from all
        </Proxy>
        ProxyPreserveHost on
        ProxyPass / http://localhost:8080/
    </VirtualHost>
