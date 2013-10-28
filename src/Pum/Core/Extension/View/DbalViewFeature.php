<?php

namespace Pum\Core\Extension\View;

use Pum\Core\Extension\View\Template\Template;
use Symfony\Component\Finder\Finder;

class DbalViewFeature extends AbstractViewFeature
{
    public function importFieldViewFromFilessystem()
    {
        $folders = $this->getPumTemplatesFolders();
        $nb      = 0;

        if (!empty($folders)) {
            $finder = new Finder();
            $finder->in($folders);
            $finder->path('/(field)/');
            $finder->path('/(project)(\/[a-zA-Z0-9-_]+)\/(field)/');
            $finder->files()->name('*.twig');

            foreach ($finder as $file) {
                $realPath = $file->getRealPath();
                if (false !== $pumPath = $this->guessPumPath($realPath)) {
                    $nb++;
                    $this->view->storeTemplate(Template::create($pumPath, $file->getContents(), Template::TYPE_FIELD, $file->getMTime()), $erase = true);
                }
            }
        }

        return $nb;
    }

    public function importObjectViewFromFilessystem()
    {
        $folders = $this->getPumTemplatesFolders();
        $nb      = 0;

        if (!empty($folders)) {
            $finder = new Finder();
            $finder->in($folders);
            $finder->path('/(object)/');
            $finder->path('/(project)(\/[a-zA-Z0-9-_]+)\/(object)/');
            $finder->files()->name('*.twig');

            foreach ($finder as $file) {
                $realPath = $file->getRealPath();
                if (false !== $pumPath = $this->guessPumPath($realPath)) {
                    $nb++;
                    $this->view->storeTemplate(Template::create($pumPath, $file->getContents(), Template::TYPE_OBJECT, $file->getMTime()), $erase = true);
                }
            }
        }

        return $nb;
    }

    public function importBeamViewFromFilessystem()
    {
        $folders = $this->getPumTemplatesFolders();
        $nb      = 0;

        if (!empty($folders)) {
            $finder = new Finder();
            $finder->in($folders);
            $finder->path('/(beam)/');
            $finder->path('/(project)(\/[a-zA-Z0-9-_]+)\/(beam)/');
            $finder->files()->name('*.twig');

            foreach ($finder as $file) {
                $realPath = $file->getRealPath();
                if (false !== $pumPath = $this->guessPumPath($realPath)) {
                    $nb++;
                    $this->view->storeTemplate(Template::create($pumPath, $file->getContents(), Template::TYPE_BEAM, $file->getMTime()), $erase = true);
                }
            }
        }

        return $nb;
    }

    public function importTemplateViewFromFilessystem()
    {
        $folders = $this->getPumTemplatesFolders();
        $nb      = 0;

        if (!empty($folders)) {
            $finder = new Finder();
            $finder->in($folders);
            $finder->notPath('/(field)\//');
            $finder->notPath('/(project)(\/[a-zA-Z0-9-_]+)\/(field)/');
            $finder->notPath('/(object)\//');
            $finder->notPath('/(project)(\/[a-zA-Z0-9-_]+)\/(object)/');
            $finder->notPath('/(beam)\//');
            $finder->notPath('/(project)(\/[a-zA-Z0-9-_]+)\/(beam)/');
            $finder->files()->name('*.twig');

            foreach ($finder as $file) {
                $realPath = $file->getRealPath();
                if (false !== $pumPath = $this->guessPumPath($realPath)) {
                    $nb++;
                    $this->view->storeTemplate(Template::create($pumPath, $file->getContents(), Template::TYPE_DEFAULT, $file->getMTime()), $erase = true);
                }
            }
        }

        return $nb;
    }

    public function importAllViewFromFilessystem()
    {
        $nb  = $this->importFieldViewFromFilessystem();
        $nb += $this->importObjectViewFromFilessystem();
        $nb += $$this->importBeamViewFromFilessystem();
        $nb += $$this->importTemplateViewFromFilessystem();

        return $nb;
    }
}
