<?php

namespace Pum\Core\Tests\Extension\View;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\PDOSqlite\Driver as SqliteDriver;
use Pum\Core\Extension\View\Storage\MysqlViewStorage;
use Pum\Core\Extension\View\Template;

class MysqlViewStorageTest extends \PHPUnit_Framework_TestCase
{
    public function testGetAll()
    {
        $file   = $this->getTempFile();
        $view   = $this->getViewStorage($file);

        $this->assertEquals(array(), $view->getAllPaths());

        $view->storeTemplate(Template::create('Core:index.html.twig', 'Hello Pikatchu'));

        $this->assertEquals(array('Core:index.html.twig'), $view->getAllPaths());

        $view->storeTemplate(Template::create('Core:welcome.html.twig', 'Welcome home Carapuce'));

        $this->assertEquals(array('Core:index.html.twig', 'Core:welcome.html.twig'), $view->getAllPaths());
    }

    public function testStoreTemplate()
    {
        $file   = $this->getTempFile();
        $view   = $this->getViewStorage($file);

        $view->storeTemplate(Template::create('Core:index.html.twig', 'Hello Pikatchu'));

        $this->assertEquals(false, $view->storeTemplate(Template::create('Core:index.html.twig', 'Hello Pikatchu')));

        $this->assertEquals(true, $view->storeTemplate(Template::create('Core:index.html.twig', 'Hello Pikatchu Updated'), $erase = true));

        $this->assertEquals(array('Core:index.html.twig'), $view->getAllPaths());
    }

    public function testRemoveTemplate()
    {
        $file   = $this->getTempFile();
        $view   = $this->getViewStorage($file);

        $this->assertEquals(false, $view->removeTemplate(Template::create('Unknown:template.html.twig', 'unknow')));

        $template = Template::create('Known:template.html.twig', 'know');
        $view->storeTemplate($template);

        $this->assertEquals(true, $view->removeTemplate($template));
    }

    public function testRemoveAllTemplates()
    {
        $file   = $this->getTempFile();
        $view   = $this->getViewStorage($file);

        $view->storeTemplate(Template::create('Core:index.html.twig', 'Hello Pikatchu'));
        $view->storeTemplate(Template::create('Core:welcome.html.twig', 'Welcome home Carapuce'));

        $view->removeAllTemplates();

        $this->assertEquals(array(), $view->getAllPaths());
    }

    public function testGetTemplate()
    {
        $file   = $this->getTempFile();
        $view   = $this->getViewStorage($file);

        $template = Template::create('my:template.html.twig', 'my template');
        $view->storeTemplate($template);

        $obj = $view->getTemplate($template->getPath());

        $this->assertEquals('my:template.html.twig', $obj->getPath());
        $this->assertEquals('my template', $obj->getSource());
        $this->assertEquals(true, $obj->isEditable());
    }

    /**
     * @expectedException RuntimeException
     */
    public function testGetInvalidTemplate()
    {
        $file   = $this->getTempFile();
        $view   = $this->getViewStorage($file);

        $obj = $view->getTemplate('unknow.path');
    }

    public function testIsEditable()
    {
        $file   = $this->getTempFile();
        $view   = $this->getViewStorage($file);

        $template = Template::create('my:template.html.twig', 'editable template');

        $this->assertEquals(true, $template->isEditable());

        $template = Template::create('my:template.html.twig', '{# not editable #} not editable template');

        $this->assertEquals(false, $template->isEditable());
    }

    protected function getViewStorage($file)
    {
        $conn = new Connection(array('path' => $file), new SqliteDriver());

        return new MysqlViewStorage($conn);
    }

    protected function getTempFile()
    {
        $file = tempnam(sys_get_temp_dir(), 'gitonomy_');

        register_shutdown_function(function () use ($file) {
            // Skip windows message error on unwrittable file
            @unlink($file);
        });

        return $file;
    }
}
