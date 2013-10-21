<?php

namespace Pum\Core\Extension\View;

use Pum\Core\Extension\View\Template\Template;
use Symfony\Component\Finder\Finder;

class ViewFeature extends AbstractViewFeature
{
    public function importFieldViewFromFilessystem()
    {
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

    public function importObjectViewFromFilessystem()
    {
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
                    $this->view->storeTemplate(Template::create($pumPath, $file->getContents(), Template::TYPE_OBJECT, $file->getMTime()), $erase = true);
                }
            }
        }
    }

    public function importBeamViewFromFilessystem()
    {
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
                    $this->view->storeTemplate(Template::create($pumPath, $file->getContents(), Template::TYPE_BEAM, $file->getMTime()), $erase = true);
                }
            }
        }
    }
}
