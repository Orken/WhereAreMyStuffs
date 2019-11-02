<?php

namespace App\Controller;

use App\Entity\Item;
use App\Form\ItemFormType;
use App\Repository\ItemRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ItemController extends AbstractController
{

    /**
     * @Route("/item/", name="items_list")
     */
    public function index(ItemRepository $repository, PaginatorInterface $paginator, Request $request)
    {
        $q = $request->query->get('q');
        $queryBuilder = $repository->getWithSearchQueryBuilder($q);
        $pagination = $paginator->paginate(
            $queryBuilder,
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('item/index.html.twig', [
            'pagination' => $pagination
        ]);
    }

    /**
     * @Route("/item/new", name="item_add")
     *
     * @param EntityManagerInterface $em
     * @return Response
     */
    public function add(EntityManagerInterface $em, Request $request, ItemRepository $repository)
    {
        $form = $this->createForm(ItemFormType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Item $item */
            $item = $form->getData();

            $parent = $repository->getBySlug('vb-g');
            $item->setParent($parent);
            $em->persist($item);
            $em->flush();

            $this->addFlash('success', 'Item created');
            return $this->redirectToRoute('items_list');

        }
        return $this->render('item/new.html.twig', [
            'itemForm' => $form->createView()
        ]);
    }

    /**
     * @Route("/item/{slug}", name="item_show")
     */
    public function show($slug, EntityManagerInterface $em)
    {
        $repo = $em->getRepository(Item::class);
        /** @var Item $box */
        $item = $repo->findOneBy(['slug' => $slug]);
        if (!$item) {
            throw new $this->createNotFoundException('lol');
        }

        return $this->render('item/show.html.twig', [
            'item' => $item,
        ]);
    }
}
