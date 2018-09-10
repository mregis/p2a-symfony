<?php
/**
 * Created by PhpStorm.
 * User: Marcos Regis
 * Date: 03/09/2018
 * Time: 15:02
 */

namespace App\EventListener;

use App\Entity\Gefra\Ocorrencia;
use App\Entity\Gefra\Envio;
use App\Entity\Main\User;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class EnvioListener
{

    private $ocorrencias = [];

    private $tokenStorage;

    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    public function preUpdate(PreUpdateEventArgs $eventArgs)
    {
        if ($eventArgs->getObject() instanceof Envio) {
            if ($eventArgs->getEntityChangeSet() != null) {
                $changes = [];
                foreach ($eventArgs->getEntityChangeSet() as $field => $data) {
                    $oldval = $eventArgs->getOldValue($field);
                    $newval = $eventArgs->getNewValue($field);
                    if ($oldval == $newval) continue; // avoiding troubles with scales
                    if ($newval instanceof \DateTime ) {
                        $newval = $newval->format('d/m/Y');
                    }
                    if ($oldval instanceof \DateTime ) {
                        $oldval = $oldval->format('d/m/Y');
                    }
                    $changes[] = sprintf('%s de [%s] para [%s]', $field, $oldval, $newval) ;
                }
                /* @var $user User */
                $user = $this->tokenStorage->getToken()->getUser();

                $ocorrencia = new Ocorrencia();
                $ocorrencia->setEnvio($eventArgs->getObject())
                    ->setDescricao("Informações alteradas: " . implode("\n", $changes))
                    ->setTipo(Ocorrencia::TIPO_UPDATE)
                    ->setUsuario($user->getId())
                ;
                $this->ocorrencias[] = $ocorrencia;
            }
        }
    }

    public function postFlush(PostFlushEventArgs $args)
    {
        if (! empty($this->ocorrencias)) {
            $em = $args->getEntityManager();

            foreach ($this->ocorrencias as $ocorrencia) {
                $em->persist($ocorrencia);
            }

            $this->ocorrencias = [];
            $em->flush();
        }
    }

}