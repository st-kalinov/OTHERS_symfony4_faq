<?php
/**
 * Created by PhpStorm.
 * User: stoyan.kalinov
 * Date: 11.10.2018 г.
 * Time: 9:17
 */

namespace App\Controller;


use App\Entity\Category;
use App\Entity\QuestionAnswer;
use App\Entity\QuestionReaction;
use App\Entity\ReactionReason;
use App\Form\CategoryFormType;
use App\Repository\CategoryRepository;
use App\Repository\QuestionAnswerRepository;
use App\Repository\QuestionReactionRepository;
use App\Repository\ReactionReasonRepository;
use Doctrine\DBAL\DBALException;
use Doctrine\ORM\EntityManagerInterface;
use RecursiveArrayIterator;
use RecursiveIteratorIterator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;


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
     * @Route("/faq/{main}/{c}", name="subcategories1", methods={"GET"})
     * @return Response
     */
    public function subcategories1($main, CategoryRepository $categoryRepository, Request $request, ReactionReasonRepository $reactionReason)
    {
        if(!$this->get('session')->get('voted'))
        {
            $this->get('session')->set('voted', []);
        }

        $c = $request->get('c');

        //get the subcategories objects if there are any
        $entities = $categoryRepository->findBy(['parent_id' => $c]);

        $reactionsAsCategories = $reactionReason->getReactionsObjAsCategories();

        if(!$entities)
        {
            return $this->render('404_not_found.html.twig', [
                'message' => 'Category not found'
            ]);
        }

       $response = new Response($this->renderView('faqMainCategories.html.twig',[
           'main' => $main,
           'entities' => $entities,
           'reactions' => $reactionsAsCategories,
           'c' => $c
       ]), 200);

       $response->headers->set('Cache-Control', ['no-store']);

       return $response;
    }

    /**
     * @Route("/faq/search/{main}/{c}", name="search", methods={"POST"})
     */
    public function search($c, CategoryRepository $categoryRepository, Request $request)
    {
        //$searchedValue = $request->request->get('search_box');

        $subs = [];
        $asd = true;
        $subcategories = $categoryRepository->findBy(['parent_id' => $c]);
        $subs = $this->testRec($subcategories, $categoryRepository, $subs);

        dd($subs);

    }

    /**
     * @param Category[] $categories
     * @return Category[]|array
     */
    public function testRec($categories, CategoryRepository $categoryRepository, $subs)
    {
        foreach ($categories as $subcategory)
        {
            if(count($categoryRepository->findBy(['parent_id' => $subcategory->getId()])) > 0)
            {
                $subcate = $categoryRepository->findBy(['parent_id' => $subcategory->getId()]);
                $this->testRec($subcate, $categoryRepository, $subs);
            }
            else {
                $subs[] = $subcategory;
            }

        }

        /** @var Category[] $subs */
        return $subs;
    }

    /**
     * @Route("/faq/statistic/question-statistic", name="question_statistic", methods={"POST"})
     */
    public function reaction(Request $request, QuestionAnswerRepository $qa, ReactionReasonRepository $reasonRepository)
    {
        $data = json_decode($request->getContent(), true);

        if($data === null || empty($data))
        {
            throw new BadRequestHttpException('Invalid JSON');
        }

        $question = $qa->find($data['questionId']);
        if(!$question)
        {
            throw $this->createNotFoundException('Invalid question');
        }

        $questionReactions = $question->getReactions();
        $statistic = $reasonRepository->getReasonsNamesAsCategories();
        foreach ($questionReactions as $reaction)
        {
            $statistic[$reaction->getReactionCategory()][$reaction->getReason()]++;
        }

        return $this->json([
            'statistic' => $statistic
        ]);
    }

    /**
    * @Route("/faq/reaction", name="question_reaction", methods={"POST"})
    */
    public function questionReaction(Request $request, QuestionAnswerRepository $qa, ReactionReasonRepository $reactionReason)
    {

        $data = json_decode($request->getContent(), true);

        if($data === null || empty($data) || count($data) < 2)
        {
            throw new BadRequestHttpException('Invalid JSON');
        }

        if(!in_array($data['questionId'],$this->get('session')->get('voted')))
        {
            $votedQuestions = $this->get('session')->get('voted');
            $votedQuestions[] = $data['questionId'];
            $this->get('session')->set('voted', $votedQuestions);
        }

        $question = $qa->find($data['questionId']);
        if(!$question)
        {
            throw $this->createNotFoundException('Invalid question');
        }
        unset($data['questionId']);

        foreach ($data as $inputName => $inputValue)
        {
            $question->addReaction($reactionReason->find($inputValue));
            $this->getDoctrine()->getManager()->persist($question);
        }
        $this->getDoctrine()->getManager()->flush();


       // $headers =
       // $response = new JsonResponse(['message' => 'Thanks'], 200);
        return $this->json([
            'message' => 'Thanks'
        ]);
    }

    /**
     * @Route("/faq/test")
     */
    public function test(QuestionAnswerRepository $qa, ReactionReasonRepository $reasonRepository)
    {


        $question = $qa->find(80);
        $reasonsAsCategories = $reasonRepository->getReasonsNamesAsCategories();
        $reactions = $question->getReactions();
        foreach ($reactions as $reaction)
        {
            $reasonsAsCategories[$reaction->getReactionCategory()][$reaction->getReason()]++;
        }

    }


  //  /**
  //   * @Route("/faq/reaction", name="question_reaction", methods={"POST"})
  //   */
  //  public function questionReaction(Request $request, QuestionAnswerRepository $qa, ReactionReasonRepository $reactionReason)
  //  {
  //      $data = json_decode($request->getContent(), true);
//
  //      if($data === null || empty($data) || count($data) < 2)
  //      {
  //          throw new BadRequestHttpException('Invalid JSON');
  //      }
//
  //      $question = $qa->find(['id' => $data['questionId']]);
  //      if(!$question)
  //      {
  //          throw $this->createNotFoundException('Invalid question');
  //      }
  //      unset($data['questionId']);
  //      $reasons = [];
  //      foreach ($data as $key => $id) {
  //          $reasons[] = $reactionReason->find(['id' => $data[$key]]);
  //      }
//
  //      if(empty($reasons))
  //      {
  //          throw $this->createNotFoundException('Invalid data');
  //      }
//
  //      foreach ($reasons as $reason) {
  //          $qaReaction = new QuestionReaction();
  //          $qaReaction->setQuestion($question)->setReaction($reason);
  //          $this->getDoctrine()->getManager()->persist($qaReaction);
  //      }
//
  //      $this->getDoctrine()->getManager()->flush();
  //      return $this->json([
  //          'message' => 'Thanks'
  //      ]);
//
  //  }

  // /**
  //  * @Route("/faq/statistic/question-statistic", name="question_statistic", methods={"POST"})
  //  */
  // public function statisticForQuestion(Request $request, QuestionAnswerRepository $qaRepo, ReactionReasonRepository $reactionReason)
  // {
  //     $data = json_decode($request->getContent(), true);

  //     if($data === null || empty($data))
  //     {
  //         throw new BadRequestHttpException('Invalid JSON');
  //     }
  //     $questionObj = $qaRepo->findOneBy(['id' => $data['id']]);
  //     if ($questionObj === null)
  //     {
  //         throw $this->createNotFoundException('Invalid question');
  //     }


  //     $reasons = $reactionReason->getReasonsNamesAsCategories();

  //     foreach ($questionReactions as $questionReactionObj)
  //     {
  //         $qaReactionMainCategory = $questionReactionObj->getReaction()->getReactionCategory();
  //         $qaReactionReason = $questionReactionObj->getReaction()->getReason();

  //         $reasons[$qaReactionMainCategory][$qaReactionReason]++;
  //     }

  //     return $this->json([
  //         'statistic' => $reasons
  //     ]);
  // }

}