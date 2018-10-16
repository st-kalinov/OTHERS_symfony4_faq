<?php
/**
 * Created by PhpStorm.
 * User: stoyan.kalinov
 * Date: 11.10.2018 г.
 * Time: 9:17
 */

namespace App\Controller;


use App\Entity\Category;
use App\Entity\ReactionReason;
use App\Form\CategoryFormType;
use App\Repository\CategoryRepository;
use App\Repository\QuestionAnswerRepository;
use App\Repository\QuestionReactionRepository;
use App\Repository\ReactionReasonRepository;
use Doctrine\ORM\EntityManagerInterface;
use RecursiveArrayIterator;
use RecursiveIteratorIterator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;


class FaqController extends AbstractController
{
    /**
     * @Route("/faq", name="faq")
     */
    public function index()
    {
        $entity = $this->getDoctrine()->getManager()->getRepository(Category::class);
        $entities = $entity->findBy(['parent_id' => null]);


        if(!$entities)
        {
            return $this->render('404_not_found.html.twig', [
                'message' => 'No MAIN Categories'
            ]);
        }

        return $this->render('faq.html.twig', [
            'entities' => $entities
        ]);
    }

    /**
     * @Route("/faq/{main}/{c}", name="subcategories1")
     * @return Response
     */
    public function subcategories1($main, CategoryRepository $categoryRepository, Request $request)
    {

        $c = $request->get('c');

        //get the subcategories objects if there are any
        $entities = $categoryRepository->findBy(['parent_id' => $c]);

        if(!$entities)
        {
            return $this->render('404_not_found.html.twig', [
                'message' => 'Category not found'
            ]);
        }

        return $this->render('faqMainCategories.html.twig', [
            'main' => $main,
            'entities' => $entities,
        ]);
    }

  // /**
  //  * @Route("/faq/{main}/{subcategory1}/{subcategory2}", name="subcategories2")
  //  */
  // public function subcategories2($main, CategoryRepository $categoryRepository, Request $request)
  // {
  //     $c = $request->get('c');



  //     $entities = $categoryRepository->findBy(['parent_id' => $c]);


  //     return $this->render('faqMainCategories.html.twig', [
  //         'main' => $main,
  //         'entities' => $entities,
  //     ]);
  // }

  // /**
  //  * @Route("/faq/{main}/{subcategory1}/{subcategory2}/{subcategory3}", name="subcategories3")
  //  */
  // public function subcategories3($main, $subcategory1, $subcategory2, $subcategory3, CategoryRepository $categoryRepository, Request $request)
  // {
  //     //$attr = $request->attributes->all();
  //     $c = $request->get('c');
  //     //$wildcard = [];
  //     //foreach ($attr['_route_params'] as $route_param)
  //     //{
  //     //    $wildcard[] = $route_param;
  //     //}
  //     //array_shift($wildcard);
  //     //dd($wildcard);
  //     $subcategory1 = ucwords(str_replace('-', ' ', $subcategory1));
  //     $subcategory2 = ucwords(str_replace('-', ' ', $subcategory2));
  //     $subcategory3 = ucwords(str_replace('-', ' ', $subcategory3));

  //     $entities = $categoryRepository->findBy(['parent_id' => $c]);

  //     //$questionsAndAnswers = $categoryEntity->getQuestionAnswers();

  //     return $this->render('faqSubCategories.html.twig', [
  //         'main' => $main,
  //         'subcategory1' => $subcategory1,
  //         'subcategory2' => $subcategory2,
  //         'subcategory3' => $subcategory3,
  //         'entities' => $entities,

  //         //'qas' => $questionsAndAnswers
  //     ]);
  // }

    /**
     * @Route("/faq/reaction", name="question_reaction", methods={"POST"})
     */
    public function like(Request $request)
    {
        $data = json_decode($request->getContent(), true);


        if($data === null)
        {
            throw new BadRequestHttpException('Invalid JSON');
        }
        return $this->json([
            'id' => $data['id'],
            'message' => 'Thanks for liking'
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


    /**
     * @Route("/faq/statistic/questions/{question_id}", name="statistic")
     */
    public function statistic($question_id, QuestionAnswerRepository $qaRepo, ReactionReasonRepository $reactionReason)
    {
        $reasons = $reactionReason->getReasonsNames();

        $questionObj = $qaRepo->findOneBy(['id' => $question_id]);
        $reactionObj = $questionObj->getQuestionReactions();


        $reactions = [];
        foreach ($reactionObj as $reaction)
        {
            $reactions[] = $reaction->getReaction()->getReason();
        }
        dd($reactions);
    }

}