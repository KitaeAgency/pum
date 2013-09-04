Continuous Integration
======================

**Current server**: ``dev.testing.argosit.net``

**Current URL**: http://ci.dev.testing.argosit.net/

Installed Debian packages
-------------------------

.. code-block:: text

    apt-get install \
        curl \
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

Jenkins
-------

Jenkins is installed through deb, see `debian page on jenkins website <http://pkg.jenkins-ci.org/debian/>`_.

**Security**

Configured on matrix based.

**Plugins**

* Green Balls
* Github Plugin
* Git Client Plugin
* Github Pull Request Builder (see `instructions <https://wiki.jenkins-ci.org/display/JENKINS/GitHub+pull+request+builder+plugin>`_)

  * Install plugin
  * Configure the plugin in global configuration

**User system**

Some administrative tasks:

.. code-block:: bash

    sudo -u jenkins ssh-keygen # generate ssh keys
    sudo -u jenkins git config --global user.name lesargonautes-ci
    sudo -u jenkins git config --global user.email ci@lesargonautes.fr

**Credentials**

In credentials, add home SSH key.

**Configure job**

In Jenkins, create a new job.

Configure it as follow:

* **Github project**: http://github.com/alexandresalome/pum
* **Repository URL**: git@github.com:alexandresalome/pum.git
* **Branch to build**: master
* **Repository browser**: githubweb (URL: http://github.com/alexandresalome/pum)
* **Build Triggers**: *Build when a change is pushed to Github*, *Github pull request builder*
* *Prune remote branches before build*
* *Clean after checkout*
