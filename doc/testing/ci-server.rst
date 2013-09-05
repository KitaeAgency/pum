Continuous Integration
======================

**Current server**: ``dev.testing.argosit.net``

**Current URL**: http://ci.dev.testing.argosit.net/

Installed Debian packages
-------------------------

Verify those packages, should not be installed: ``php5-xcache``

.. code-block:: text

    apt-get install \
        curl zip unzip \
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

Also installed through Debian package, Chrome:

.. code-block:: bash

    wget https://dl.google.com/linux/direct/google-chrome-stable_current_amd64.deb
    dpkg -i google-chrome-stable_current_amd64.deb

XVFB
----

File ``/etc/init.d/xvfb``, and when filled, run ``update-rc.d xvfb default`` to start on boot:

.. code-block:: text

    XVFB=/usr/bin/Xvfb
    XVFBARGS=":1 -screen 0 1024x768x24 -ac +extension GLX +render -noreset"
    PIDFILE=/var/run/xvfb.pid
    case "$1" in
      start)
        echo -n "Starting virtual X frame buffer: Xvfb"
        start-stop-daemon --start --quiet --pidfile $PIDFILE --make-pidfile --background --exec $XVFB -- $XVFBARGS
        echo "."
        ;;
      stop)
        echo -n "Stopping virtual X frame buffer: Xvfb"
        start-stop-daemon --stop --quiet --pidfile $PIDFILE
        echo "."
        ;;
      restart)
        $0 stop
        $0 start
        ;;
      *)
            echo "Usage: /etc/init.d/xvfb {start|stop|restart}"
            exit 1
    esac

    exit 0

Selenium server
---------------

Install:

.. code-block:: bash

    mkdir /usr/lib/selenium/
    cd /usr/lib/selenium/
    wget http://selenium.googlecode.com/files/selenium-server-standalone-2.35.0.jar
    mv selenium-server-standalone-2.35.0.jar selenium.jar
    mkdir -p /var/log/selenium/
    chmod a+w /var/log/selenium/

Init script in ``/etc/init.d/selenium-server``, and run ``update-rc.d selenium defaults`` once it's installed:

.. code-block:: bash

    #!/bin/bash

    export DISPLAY=":1"

    case "${1:-''}" in
            'start')
                    if test -f /tmp/selenium.pid
                    then
                            echo "Selenium is already running."
                    else
                            java -jar /usr/lib/selenium/selenium.jar -port 4443 > /var/log/selenium/selenium-output.log 2> /var/log/selenium/selenium-error.log & echo $! > /tmp/selenium.pid
                            echo "Starting Selenium..."

                            error=$?
                            if test $error -gt 0
                            then
                                    echo "${bon}Error $error! Couldn't start Selenium!${boff}"
                            fi
                    fi
            ;;
            'stop')
                    if test -f /tmp/selenium.pid
                    then
                            echo "Stopping Selenium..."
                            PID=`cat /tmp/selenium.pid`
                            kill -3 $PID
                            if kill -9 $PID ;
                                    then
                                            sleep 2
                                            test -f /tmp/selenium.pid && rm -f /tmp/selenium.pid
                                    else
                                            echo "Selenium could not be stopped..."
                                    fi
                    else
                            echo "Selenium is not running."
                    fi
                    ;;
            'restart')
                    if test -f /tmp/selenium.pid
                    then
                            kill -HUP `cat /tmp/selenium.pid`
                            test -f /tmp/selenium.pid && rm -f /tmp/selenium.pid
                            sleep 1
                            java -jar /usr/lib/selenium/selenium.jar -port 4443 > /var/log/selenium/selenium-output.log 2> /var/log/selenium/selenium-error.log & echo $! > /tmp/selenium.pid
                            echo "Reload Selenium..."
                    else
                            echo "Selenium isn't running..."
                    fi
                    ;;
            *)      # no parameter specified
                    echo "Usage: $SELF start|stop|restart|reload|force-reload|status"
                    exit 1
            ;;
    esac

Install ChromeDriver:

.. code-block:: bash

    wget https://chromedriver.googlecode.com/files/chromedriver_linux64_2.3.zip
    unzip chromedriver_linux64_2.3.zip
    chmod a+x chromedriver
    mv chromedriver /usr/bin


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

And now, we'll configure two virtual hosts for CI slots:

.. code-block:: text

    <VirtualHost *:80>
        ServerName ci-pum-01.dev.testing.argosit.net
        DocumentRoot /var/lib/jenkins/www/ci-01/web
    </VirtualHost>
    <VirtualHost *:80>
        ServerName ci-pum-02.dev.testing.argosit.net
        DocumentRoot /var/lib/jenkins/www/ci-02/web
    </VirtualHost>

Folders in ``/var/lib/jenkins/www`` should be published by Jenkins user


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

* PostbuildScript

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
* *Before test*: pum-prepare.sh (as shown below)
* *After test*: pum-finish.sh (as shown below)

.. code-block:: bash

    #!/bin/bash
    # Command: pum-prepare.sh (to be ran to prepare a workspace)
    set -e

    FILE="`/var/lib/jenkins/slots/get.sh pum`"
    tar -xzf "$FILE"

    SLOT=`cat behat.yml | grep base_url | sed -e 's/.*ci-pum-\([0-9][0-9]*\).*/\1/g'`

    if [ -e /var/lib/jenkins/www/ci-$SLOT ]; then
        rm /var/lib/jenkins/www/ci-$SLOT
    fi

    ln -s `pwd` /var/lib/jenkins/www/ci-$SLOT


**After test script**

.. code-block:: bash

    #!/bin/bash
    # Command: pum-finish.sh (to be ran after test)
    set -e

    FILE="`cat __conf_file__`"
    /var/lib/jenkins/slots/free.sh pum "$FILE"





Slot system
-----------

To ease testing, we provide a simple tool to manage multiple configuration for testing.

A small tool, called "slot" can be used like this:

.. code-block:: bash

    $ cd /var/lib/jenkins/slot
    $ ./get.sh pum
    /some/path/to/a/config_32.tgz
    $ ./free.sh pum /some/path/to/a/config_32.tgz

Those two commands ``get.sh`` and ``free.sh`` are the following:

``get.sh``

.. code-block:: bash

    #!/bin/bash
    set -e
    cd "`dirname $0`"
    APP="$1"
    if [ "$APP" == "" -o ! -d "$APP" ]; then
        echo "Application \"$APP\" was not found."
        exit 1
    fi

    cd "$APP/available"

    while [ "$FILE" == "" ]; do
        FILE="`find . -type f -print -quit`"
        if [ "$FILE" == "" ]; then
            sleep 5
        fi
    done

    mv "$FILE" ../used
    cd ../..
    echo "`readlink -f \"$APP/used/$FILE\"`"


``free.sh``

.. code-block:: bash

    #!/bin/bash
    set -e
    cd "`dirname $0`"

    APP="$1"

    if [ "$APP" == "" -o ! -d "$APP" ]; then
        echo "Application \"$APP\" was not found"
    fi

    cd "$APP/available"

    while [ "$FILE" == "" ]; do
        FILE="`find . -type f -print -quit`"
        if [ "$FILE" == "" ]; then
            sleep 5
        fi
    done

    mv "$FILE" ../used
    cd ../..
    echo "`readlink -f \"$APP/used/$FILE\"`"
