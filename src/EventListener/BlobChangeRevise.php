<?php

namespace App\EventListener;

use App\Entity\Blob;
use App\Entity\Revision;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Symfony\Component\Uid\Uuid;

class BlobChangeRevise
{
    public function onFlush(OnFlushEventArgs $args): void
    {
        $em = $args->getEntityManager();
        $uow = $em->getUnitOfWork();

        foreach ($uow->getScheduledEntityUpdates() as $entity) {
            if ($entity instanceof Blob) {
                $entityChangeSet = $uow->getEntityChangeSet($entity);
                if (key_exists('content', $entityChangeSet)) {
                    $revision = new Revision();
                    $revision->setUuid(Uuid::v4());
                    $revision->setBlob($entity);
                    $revision->setContent($entityChangeSet['content'][0]);
                    $em->persist($revision);

                    $classMetadata = $em->getClassMetadata(Revision::class);
                    $uow->computeChangeSet($classMetadata, $revision);
                }
            }
        }
    }
}
