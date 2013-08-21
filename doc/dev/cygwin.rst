Develop with Windows
====================

If you are using Windows, then you don't know what it feels to have colors in
a shell. Sorry for that...

Hopefully, some people created `Cygwin <http://www.cygwin.com/>`_ to allow
you to benefit of a bash environment under Windows.

But this comes with pitfalls...

Error "bin/behat" not found
---------------------------

Windows never loved symbolic link. In this case, you must call behat with his
explicit path: ``vendor/behat/behat/bin/behat``.

Error "Unable to connect to github"
-----------------------------------

If this case occurs, you need to *rebaseall your Cygwin". To do so:

* Use the setup file of cygwin to make sure *rebase* package is present
* Shutdown all cygwin processes
* Go to the Cygwin folder where Cygwin is installed. Execute ``dash.exe``
* Do the following: ``./rebaseall -v``
