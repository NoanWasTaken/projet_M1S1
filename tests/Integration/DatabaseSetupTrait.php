<?php

namespace App\Tests\Integration;

use Doctrine\ORM\Tools\SchemaTool;

trait DatabaseSetupTrait
{
    protected function setUpDatabase(): void
    {
        $em = static::getContainer()->get('doctrine.orm.entity_manager');

        $connection = $em->getConnection();
        $connection->executeStatement('DROP SCHEMA IF EXISTS public CASCADE');
        $connection->executeStatement('CREATE SCHEMA public');

        $schemaTool = new SchemaTool($em);
        $classes = $em->getMetadataFactory()->getAllMetadata();
        $schemaTool->createSchema($classes);
    }
}
