<?php
use Pum\Core\Definition\ObjectDefinition;
use Pum\Core\Exception\DefinitionNotFoundException;

require_once __DIR__.'/app/bootstrap.php.cache';
require_once __DIR__.'/app/AppKernel.php';

$kernel = new AppKernel('dev', true);
$kernel->boot();

$pum = $kernel->getContainer()->get('pum');

try {
    $def = $pum->getDefinition('poney');
    $pum->deleteDefinition($def);
} catch (DefinitionNotFoundException $e) {
}

$pum->saveDefinition(ObjectDefinition::create('poney')
    ->createField('name', 'text')
    ->createField('score', 'integer')
    ->createField('above_avg', 'boolean')
);

$scores = array(
    'Albator' => 48,
    'Cunegonde' => 22,
    'Jeremiade' => 11,
    'Cariolite' => 0,
);

$moyenne = floor(array_sum($scores) / count($scores));

foreach ($scores as $name => $score) {
    $poney = $pum->createObject('poney');
    $poney->set('name', $name);
    $poney->set('score', $score);
    $poney->set('above_avg', $score >= $moyenne);

    $pum->persist($poney);
    $pum->flush();
}

// now, scoreboard :)

$q = $pum->getRepository('poney')
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
