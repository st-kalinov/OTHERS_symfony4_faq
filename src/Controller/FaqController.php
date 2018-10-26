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
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use RecursiveArrayIterator;
use RecursiveIteratorIterator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Cookie;
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
        if (!$entities) {
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
        $c = $request->get('c');
        //get the subcategories objects if there are any
        $entities = $categoryRepository->findBy(['parent_id' => $c]);
        $reactionsAsCategories = $reactionReason->getReactionsObjAsCategories();
        if (!$entities) {
            return $this->render('404_not_found.html.twig', [
                'message' => 'Category not found'
            ]);
        }
        $response = new Response($this->renderView('faqMainCategories.html.twig', [
            'main' => $main,
            'entities' => $entities,
            'reactions' => $reactionsAsCategories,
        ]), 200);
        $cookies = $request->cookies;
        if(!$cookies->has('voted'))
        {
            $cookie = new Cookie('voted', serialize([]));
            $response->headers->setCookie($cookie);
        }
        //if ($this->get('session')->get('voted') === null) {
        //    $this->get('session')->set('voted', []);
        //}
        $response->headers->set('Cache-Control', ['no-store']);
        return $response;
    }
    /**
     * @Route("/faq/reaction", name="question_reaction", methods={"POST"})
     */
    public function questionReaction(Request $request, QuestionAnswerRepository $qa, ReactionReasonRepository $reactionReason)
    {
        $cookies = $request->cookies;
        $data = json_decode($request->getContent(), true);
        if ($data === null || empty($data) || count($data) < 2) {
            throw new BadRequestHttpException('Invalid JSON');
        }
        $question = $qa->find($data['questionId']);
        if (!$question) {
            throw $this->createNotFoundException('Invalid question');
        }
        $votedQuestions = [];
        if($cookies->has('voted'))
        {
            $votedQuestions = unserialize($cookies->get('voted'));
            if(!in_array($data['questionId'], $votedQuestions))
            {
                $votedQuestions[] = $data['questionId'];
            }
        }
        //if ($this->get('session')->get('voted') !== null) {
        //    $votedQuestions = $this->get('session')->get('voted');
        //    if (!in_array($data['questionId'], $votedQuestions)) {
        //        $votedQuestions[] = $data['questionId'];
        //    }
        //}
        // $this->get('session')->set('voted', $votedQuestions);
        unset($data['questionId']);
        $questionReaction = new QuestionReaction();
        foreach ($data as $inputValue) {
            $reaction = $reactionReason->find($inputValue);
            $questionReaction->setQuestion($question)->setReaction($reaction);
            $this->getDoctrine()->getManager()->persist($questionReaction);
        }
        $this->getDoctrine()->getManager()->flush();

        $newCookie = new Cookie('voted', serialize($votedQuestions));
        $response = new JsonResponse(['message' => 'Thanks'], 200);
        $response->headers->setCookie($newCookie);

        return $response;
    }
    /**
     * @Route("/faq/statistic/question-statistic", name="question_statistic", methods={"POST"})
     */
    public function reaction(Request $request, QuestionAnswerRepository $qa, ReactionReasonRepository $reasonRepository)
    {
        $data = json_decode($request->getContent(), true);
        if ($data === null || empty($data)) {
            throw new BadRequestHttpException('Invalid JSON');
        }
        $question = $qa->find($data['questionId']);
        if (!$question) {
            throw $this->createNotFoundException('Invalid question');
        }
        $questionReactions = $question->getQuestionReactions();
        $statistic = $reasonRepository->getReasonsNamesAsCategories();
        foreach ($questionReactions as $reaction) {
            $statistic[$reaction->getReaction()->getReactionCategory()][$reaction->getReaction()->getReason()]++;
        }
        return $this->json([
            'statistic' => $statistic
        ]);
    }
    /**
     * @Route("/faq/search/{main}", name="search", methods={"POST"})
     */
    public function search($main, Request $request)
    {
        $searchedValue = $request->request->get('search_box');
        $searchedValue = base64_encode($searchedValue);
        return $this->redirectToRoute('asd', ['main' => $main, 'searched' => $searchedValue]);
        //$subs = $this->testRec($c, $categoryRepository);
        //dd($questions);
    }
    /**
     * @Route("/faq/search/{main}/s:{searched}", name="asd")
     */
    public function redi($main, $searched, CategoryRepository $categoryRepository, QuestionAnswerRepository $qaRepo)
    {
        $searchedValue = base64_decode($searched);
        $questions = [];
        $subcategories = $categoryRepository->findBy(['main_category' => $main]);
        foreach ($subcategories as $subcategory) {
            $founded = $qaRepo->search($searchedValue, $subcategory->getId());
            foreach ($founded as $found) {
                $questions[] = $found;
            }
        }
        return $this->render('statistic.html.twig', [
            'questions' => $questions
        ]);
    }
    /**
     * @param Category[] $categories
     * @return Category[]|array
     */
    public function testRec($categories, CategoryRepository $categoryRepository, $subs)
    {
        foreach ($categories as $subcategory) {
            if (count($categoryRepository->findBy(['parent_id' => $subcategory->getId()])) > 0) {
                $subcate = $categoryRepository->findBy(['parent_id' => $subcategory->getId()]);
                $this->testRec($subcate, $categoryRepository, $subs);
            } else {
                $subs[] = $subcategory;
            }
        }
        /** @var Category[] $subs */
        return $subs;
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