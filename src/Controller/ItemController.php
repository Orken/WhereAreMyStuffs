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
                13
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
    public function show(ItemRepository $repo, Request $request, PaginatorInterface $paginator, Item $item = null)
    {
        if (is_null($item)) {
            $item = $repo->findOneBy(['parent' => null]);
        }
        $q = $request->query->get('q');

        $items = $repo
                ->childrenQueryBuilder($item, false, 'lvl')
                ->andWhere('node.name LIKE :q OR node.comment LIKE :q')
                ->setParameter('q', '%' . $q . '%');

        $pagination = $paginator->paginate(
                $items,
                $request->query->getInt('page', 1),
                10
        );
        return $this->render('item/show.html.twig', [
                'item' => $item,
                'pagination' => $pagination,
                'path' => $repo->getPath($item)
        ]);
    }

    /**
     * @Route("/item/delete/{slug}", name="item_delete")
     */
    public function delete(Item $item)
    {
        if ($item->isBox()) {
            $this->addFlash('error', 'This is a box');
            return $this->redirectToRoute('item_show', ['slug' => $item->getSlug()]);

        }
        return $this->render('item/delete.html.twig', ['item' => $item]);
    }

    /**
     * @Route("/item/edit/{slug}", name="item_edit")
     */
    public function edit(Item $item, EntityManagerInterface $em, Request $request, ItemRepository $repository, $slug = false)
    {
        $form = $this->createForm(ItemFormType::class, $item);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Item $item */
            $item = $form->getData();
            $em->persist($item);
            $em->flush();

            $this->addFlash('success', 'Item created');
            if ($slug) {
                return $this->redirectToRoute('item_show', ['slug' => $slug]);
            } else {
                return $this->redirectToRoute('items_list');
            }

        }
        return $this->render('item/edit.html.twig', [
                'item' => $item,
                'itemForm' => $form->createView(),
        ]);
    }
}
