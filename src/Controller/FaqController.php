<?php
/**
 * Created by PhpStorm.
 * User: stoyan.kalinov
 * Date: 11.10.2018 г.
 * Time: 9:17
 */

namespace App\Controller;


use App\Entity\Category;
use App\Form\CategoryFormType;
use App\Repository\CategoryRepository;
use App\Repository\QuestionAnswerRepository;
use Doctrine\ORM\EntityManagerInterface;
use RecursiveArrayIterator;
use RecursiveIteratorIterator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FaqController extends AbstractController
{
    /**
     * @Route("/faq", name="faq")
     */
    public function index()
    {
        $entity = $this->getDoctrine()->getManager()->getRepository(Category::class);
        $categories = $entity->findBy(['parent_id' => null]);

        return $this->render('faq.html.twig', [
            'categories' => $categories
        ]);
    }

    /**
     * @Route("/faq/{main}/{subcategory1}", name="subcategories1")
     * @return Response
     */
    public function subcategories1($subcategory1, $main, CategoryRepository $categoryRepository, Request $request)
    {

        //normalize category name
        $subcategory1 = ucwords(str_replace('-', ' ', $subcategory1));
        $c = $request->get('c');

        //get questions and answers for that category, if there are any
        $questionsAndAnswers = $categoryRepository->findOneBy(['id' => $c])->getQuestionAnswers();

        //get the subcategories objects if there are any
        $entities = $categoryRepository->findBy(['parent_id' => $c]);


       //dd($entities);
       //foreach ($entities as $entity) {
       //    //$subentities = $em->getRepository(Category::class)->findBy(['parent_id' => $entity->getId()]);
       //    //$subcategories[] = $subentities;
       //}

       // $obj = new RecursiveIteratorIterator(new RecursiveArrayIterator($subcategories, RecursiveArrayIterator::CHILD_ARRAYS_ONLY));
       // foreach ($obj as $key => $item)
       // {
        //
       //     $id = $obj->current()->getId();
       //     dump($item->getId());
       //     dump($key);
        //
       //     ##$sub = $em->getRepository(Category::class)->findBy(['parent_id' => $id]);
##//
       //     ##if(!empty($sub))
       //     ##{
       //     ##    dump("ima");
       //     ##}
       //     //dump($item);
       // }
        return $this->render('faqMainCategories.html.twig', [
            'main' => $main,
            'subcategory1' => $subcategory1,
            'entities' => $entities,
            'qas' => $questionsAndAnswers,
        ]);
    }

    /**
     * @Route("/faq/{main}/{subcategory1}/{subcategory2}", name="subcategories2")
     */
    public function subcategories2($main, $subcategory1, $subcategory2, CategoryRepository $categoryRepository, Request $request)
    {
       //$attr = $request->attributes->all();
        $c = $request->get('c');
       //$wildcard = [];
       //foreach ($attr['_route_params'] as $route_param)
       //{
       //    $wildcard[] = $route_param;
       //}
       //array_shift($wildcard);
       //dd($wildcard);
        $subcategory1 = ucwords(str_replace('-', ' ', $subcategory1));
        $subcategory2 = ucwords(str_replace('-', ' ', $subcategory2));

        //$categoryEntity =  $categoryRepository->findOneBy(['main_category' => $main, 'name' => $subcategory]);
        $entities = $categoryRepository->findBy(['parent_id' => $c]);

        //$questionsAndAnswers = $categoryEntity->getQuestionAnswers();

        return $this->render('faqSubCategories.html.twig', [
            'main' => $main,
            'subcategory1' => $subcategory1,
            'subcategory2' => $subcategory2,
            'entities' => $entities,
            //'qas' => $questionsAndAnswers,
        ]);
    }

    /**
     * @Route("/faq/{main}/{subcategory1}/{subcategory2}/{subcategory3}", name="subcategories3")
     */
    public function subcategories3($main, $subcategory1, $subcategory2, $subcategory3, CategoryRepository $categoryRepository, Request $request)
    {
        //$attr = $request->attributes->all();
        $c = $request->get('c');
        //$wildcard = [];
        //foreach ($attr['_route_params'] as $route_param)
        //{
        //    $wildcard[] = $route_param;
        //}
        //array_shift($wildcard);
        //dd($wildcard);
        $subcategory1 = ucwords(str_replace('-', ' ', $subcategory1));
        $subcategory2 = ucwords(str_replace('-', ' ', $subcategory2));
        $subcategory3 = ucwords(str_replace('-', ' ', $subcategory3));

        $entities = $categoryRepository->findBy(['parent_id' => $c]);

        //$questionsAndAnswers = $categoryEntity->getQuestionAnswers();

        return $this->render('faqMainCategories.html.twig', [
            'subcategory1' => $subcategory1,
            'subcategory2' => $subcategory2,
            'subcategory3' => $subcategory3,
            'entities' => $entities,
            //'qas' => $questionsAndAnswers
        ]);
    }
    /**
     * @Route("/faq/new", name="client_newsub_category")
     * @Route("/faq/new", name="notclient_newsub_category")
     */
    public function neww()
    {
        $form = $this->createForm(CategoryFormType::class);

        return $this->render('formtest.html.twig', [
           'formtest' => $form->createView()
        ]);
    }
}