<?php

namespace Pum\Core\Extension\View;

use Pum\Core\Extension\View\Template\Template;
use Symfony\Component\Finder\Finder;

class FieldViewFeature extends AbstractViewFeature
{
    public function getViewTemplates()
    {
        return $this->view->getAllPaths($type = Template::TYPE_FIELD);
    }

    public function updateViewTemplates()
    {
        /* Delete old templates */
        $this->view->removeAllTemplates($type = Template::TYPE_FIELD);

        /* Store new templates */
        $folders = $this->getPumTemplatesFolders();

        if (!empty($folders)) {
            $finder = new Finder();
            $finder->in($folders);
            $finder->path('/^(field)(\/[a-zA-Z0-9-_]+)+/');
            $finder->path('/^(project)(\/[a-zA-Z0-9-_]+)\/(field)(\/[a-zA-Z0-9-_]+)+/');
            $finder->files()->name('*.twig');

            foreach ($finder as $file) {
                $realPath = $file->getRealPath();
                if (false !== $pumPath = $this->guessPumPath($realPath)) {
                    $this->view->storeTemplate(Template::create($pumPath, $file->getContents(), Template::TYPE_FIELD, $file->getMTime()), $erase = true);
                }
            }
        }
    }
}
