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
     * @Route("/items/", name="items_list")
     */
    public function index(ItemRepository $repository, PaginatorInterface $paginator, Request $request)
    {
        $q = $request->query->get('q');
        $queryBuilder = $repository->getWithSearchQueryBuilder($q);
        $pagination = $paginator->paginate(
                $queryBuilder,
                $request->query->getInt('page', 1),
                6
        );
        return $this->render('item/index.html.twig', [
                'pagination' => $pagination
        ]);
    }

    /**
     * @Route("/item/recover", name="items_recover")
     */
    public function recover(EntityManagerInterface $em)
    {
        $em->getRepository(Item::class)->recover();
        $em->flush();

        return $this->redirectToRoute('items_list');

    }

    /**
     * @Route("/item/new/{slug}", name="item_add")
     *
     * @param EntityManagerInterface $em
     * @return Response
     */
    public function add(EntityManagerInterface $em, Request $request, ItemRepository $repository, $slug = false)
    {
        $form = $this->createForm(ItemFormType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Item $item */
            $item = $form->getData();
            if ($slug) {
                $parent = $repository->getBySlug($slug);
            } else {
                $parent = $repository->findOneBy(['parent' => null]);
            }
            $item->setParent($parent);
            $em->persist($item);
            $em->flush();

            $this->addFlash('success', 'Item created');
            if ($slug) {
                return $this->redirectToRoute('item_show', ['slug' => $slug]);
            } else {
                return $this->redirectToRoute('items_list');
            }

        }
        return $this->render('item/new.html.twig', [
                'itemForm' => $form->createView()
        ]);
    }

    /**
     * @Route("/item/{slug}", name="item_show")
     * @Route("/", name="item_list")
     */
    public function show($slug = false, EntityManagerInterface $em, Request $request, PaginatorInterface $paginator)
    {
        $repo = $em->getRepository(Item::class);
        /** @var Item $item */
        if ($slug) {
            $item = $repo->findOneBy(['slug' => $slug]);
        } else {
            $item = $repo->findOneBy(['parent' => null]);
        }
        if (!$item) {
            throw new $this->createNotFoundException('lol');
        }
        $items = $em->getRepository(Item::class)->children($item, false, 'lvl');
        $pagination = $paginator->paginate(
                $items,
                $request->query->getInt('page', 1),
                6
        );
        return $this->render('item/show.html.twig', [
                'item' => $item,
                'pagination' => $pagination,
                'path' => $repo->getPath($item)
        ]);
    }
}
