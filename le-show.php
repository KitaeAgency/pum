<?php
use Pum\Core\Definition\Beam;
use Pum\Core\Definition\ObjectDefinition;
use Pum\Core\Definition\Project;
use Pum\Core\Exception\BeamNotFoundException;
use Pum\Core\Exception\ProjectNotFoundException;

require_once __DIR__.'/app/bootstrap.php.cache';
require_once __DIR__.'/app/AppKernel.php';

$kernel = new AppKernel('dev', true);
$kernel->boot();

// schema manager
$sm = $kernel->getContainer()->get('pum_core.schema_manager');

// delete old beam
try {
    $beam = $sm->getBeam('haras');
    $sm->deleteBeam($beam);
} catch (BeamNotFoundException $e) {
}

// delete old project
try {
    $project = $sm->getProject('tournoi-2013');
    $sm->deleteProject($project);
} catch (ProjectNotFoundException $e) {
}

$beam = Beam::create('haras')
    ->addObject(ObjectDefinition::create('poney')
        ->createField('name', 'text')
        ->createField('score', 'integer')
        ->createField('above_avg', 'boolean')
    )
    ->addObject(ObjectDefinition::create('horse')
        ->createField('name', 'text')
        ->createField('score', 'integer')
        ->createField('above_avg', 'boolean')
    )
;

$sm->saveBeam($beam);

$project = Project::create('tournoi-2013')
    ->addBeam($beam)
;

$sm->saveProject($project);

// schema is done, now usage

$em = $kernel->getContainer()->get('pum.em_factory')->getManager('project-A');

// Poney data
$scores  = array('Albator' => 48,'Cunegonde' => 22,'Jeremiade' => 11,'Cariolite' => 0);
$moyenne = floor(array_sum($scores) / count($scores));

foreach ($scores as $name => $score) {
    $poney = $em->createObject('poney');
    $poney->set('name', $name);
    $poney->set('score', $score);
    $poney->set('above_avg', $score >= $moyenne);

    $em->persist($poney);
    $em->flush();
}

$q = $em->getRepository('poney')
    ->createQueryBuilder('p')
    ->orderBy('p.score', 'DESC')
    ->getQuery()
;

echo sprintf('%-30s %-5s %s'."\n", 'Name', 'Score', 'Better than average');
echo sprintf('%-30s %-5s %s'."\n", '----', '-----', '-------------------');

$poneys = $q->execute();

foreach ($poneys as $poney) {
    echo sprintf('%-30s %-5s %s'."\n", $poney->get('name'), $poney->get('score'), $poney->get('above_avg') ? 'YES': 'NO');
}
