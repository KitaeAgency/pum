How to use Behat
================

To test the application, we are using `Behat <http://behat.org>`_ with `Selenium <http://docs.seleniumhq.org/>`_.

Writing new steps
-----------------

If you read `Behat documentation <http://docs.behat.org/>`_, now you know that you use
**steps** to express your tests.

When writing such steps, you sometimes need to create new sentences. In PUM project, 4 contexts are
available by now:


* Vendor contexts

  * ``WebDriver\Behat\WebDriverContext`` - Manipulation of the web browser
  * ``Alex\MailCatcher\Behat\MailCatcherContext`` - Manipulation of the mail server (mailcatcher)

* PUM contexts

  * ``Pum\QA\Context\NavigationContext`` - Specific navigation steps for PUM
  * ``Pum\QA\Context\ApiContext`` - Manipulation of PUM data

You can get a list of all available steps:

.. code-block:: bash

    bin/behat -dl


Frequently asked questions
--------------------------

**Can I launch a given set of tests instead of the whole suite?**

    Suppose you only want to test Acme bundles, just run:

    .. code-block:: bash

        bin/behat features/acme

    ``features/acme`` is relative path to the folder you want to test.

**I get an error "bin/behat" not found under Windows**

    Yeah, Windows never really appreciated symbolic links. For this
    reason, you need to target the binary manually. Replace all
    ``bin/behat`` instructions with ``vendor/behat/behat/bin/behat``:

    .. code-block:: bash

        # returns definition list
        php vendor/behat/behat/bin/behat -dl

        # run all test suite
        php vendor/behat/behat/bin/behat
