<?php

namespace App\Controller;

use App\Entity\Item;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ItemAdminController extends AbstractController
{

    /**
     * @Route("/admin/item/new")
     *
     * @return void
     */
    public function new(EntityManagerInterface $em)
    {
        $item = new Item();
        $item->setName('boite ' . uniqid())
            ->setSlug('boite-livebox-' . uniqid())
            ->setCreatedAt(new \DateTime());
        $em->persist($item);
        $em->flush();
        return new Response(sprintf(
            'Cool box id #%d with slug %s',
            $item->getId(),
            $item->getSlug()
        ));
    }
}
